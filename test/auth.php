<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);

require './utils/get-json-file.php';
require './utils/safe-file-get-contents.php';
require './extend/SystemDB.php';

$settingFile = './operate/auth-settings.json';
if (is_file($settingFile)) {
    $settings = getJsonFile($settingFile);
} else {
    $settings = [];
}
if ($settings === false) {
    exit('認証設定の取得に失敗しました。');
}

# Cloudflare Turnstile sitekey,secretkey
# コード内での編集は非推奨化。システム総管理ページで編集してください。
$sitekey = '1x00000000000000000000AA';
$SECRET_KEY = '1x0000000000000000000000000000000AA';
if (!empty($settings['turnstile-sitekey'] ?? '')) {
    $sitekey = $settings['turnstile-sitekey'];
}
if (!empty($settings['turnstile-secretkey'] ?? '')) {
    $SECRET_KEY = $settings['turnstile-secretkey'];
}

$FORCESSL = true; #https未対応の場合はfalseにすること
if (getenv('SKIP_VERIFICATION')) {
    // 開発環境ではhttp可
    $FORCESSL = false;
}
$NOWTIME = time();

// cloudflare使用チェック
$useCloudflare = ($settings['use-cloudflare'] ?? 'checked') === 'checked';
if ($useCloudflare && isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}

$IP = $_SERVER['REMOTE_ADDR'];
$HOST = $IP;
$authStatus = 'failed';

/**
 * 認証ログを記録する関数
 * @param array{
 *   status: string,
 *   wrt_agreement_key: string,
 *   ip: string,
 *   host: string,
 *   isp: string,
 *   asname: string,
 *   ua: string,
 *   ch_ua: string,
 *   client_id: string,
 *   en_file: string,
 *   posted_at: int,
 * } $data 認証時データ
 */
function recordLog($data)
{
    $db = new SystemDB();
    if ($db->isSQLiteMode()) {
        $nowDate = date('Y-m-d H:i:s', $data['posted_at']);
        $accountId = $data['wrt_agreement_key'] === 'null' ? 'null' : hash('sha256', hash('sha256', md5($data['wrt_agreement_key']) . preg_replace('/[^0-9]/', '', md5($data['wrt_agreement_key']))));
        $data['date'] = $nowDate;
        $data['account_id'] = $accountId;
        $db->addToAuthLog($data);
    } else {// ファイル形式
        // 定数定義
        $LOG_LIMITS = 10000;
        // ログを追記
        $nowDate = date('Y-m-d H:i:s', $data['posted_at']);
        $log = $nowDate . '<>' . $data['status'] . '<>' . $data['wrt_agreement_key'] . '<>' . $data['ip'] . '<>' . $data['host'] . '<>' . $data['isp'] . '<>' . $data['asname'] . '<>' . $data['ua'] . '<>' . $data['ch_ua'] . '<>' . $data['client_id'] . '<>' . $data['en_file'] . "\n";
        $logFile = './HAP/log.cgi';
        file_put_contents($logFile, $log, FILE_APPEND | LOCK_EX);
        // ログの数チェック
        $fp = fopen($logFile, 'c+b');
        if (!$fp) {
            return;
        }
        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            return;
        }
        $lines = [];
        while (($line = fgets($fp)) !== false) {
            $lines[] = $line;
        }
        // 規定数未満なので終了
        if (count($lines) < $LOG_LIMITS) {
            flock($fp, LOCK_UN);
            fclose($fp);
            return;
        }
        // 古いlogを削除する処理
        $offset = -($LOG_LIMITS - 100);
        $newLines = array_slice($lines, $offset);
        // ファイルに書き込み
        rewind($fp);
        ftruncate($fp, 0);
        fwrite($fp, implode('', $newLines));
        // ファイル閉じる
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

// UA初期化
if (!isset($_SERVER['HTTP_SEC_CH_UA'])) {
    $_SERVER['HTTP_SEC_CH_UA'] = '';
}
if (!isset($_SERVER['HTTP_SEC_CH_UA_PLATFORM'])) {
    $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] = '';
}
if (!isset($_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'])) {
    $_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'] = '';
}
if (!isset($_SERVER['HTTP_SEC_CH_UA_BITNESS'])) {
    $_SERVER['HTTP_SEC_CH_UA_BITNESS'] = '';
}
if (!isset($_SERVER['HTTP_SEC_CH_UA_ARCH'])) {
    $_SERVER['HTTP_SEC_CH_UA_ARCH'] = '';
}
if (!isset($_SERVER['HTTP_SEC_CH_UA_MODEL'])) {
    $_SERVER['HTTP_SEC_CH_UA_MODEL'] = '';
}
if (!isset($_SERVER['HTTP_SEC_CH_UA_MOBILE'])) {
    $_SERVER['HTTP_SEC_CH_UA_MOBILE'] = '';
}
if (!isset($_SERVER['HTTP_SEC_CH_UA_FULL_VERSION_LIST'])) {
    $_SERVER['HTTP_SEC_CH_UA_FULL_VERSION_LIST'] = '';
}

// POSTデータを取得
$token = isset($_POST['cf-turnstile-response']) ? $_POST['cf-turnstile-response'] : '';

// IPv6かIPv4か判定
$binaryIp = inet_pton($IP);
$isIpv6 = strlen($binaryIp) === 16;
if ($isIpv6) {
    // IPv6の場合
    // 先頭8バイト (64bit) を抽出して16進数に戻す
    $ipNetworkPart = bin2hex(substr($binaryIp, 0, 8));
    // 先頭1バイト (8bit) を抽出して16進数に戻す
    $range = bin2hex(substr($binaryIp, 0, 2));
} else {
    // IPv4の場合
    // 先頭3バイト (24bit) を抽出して16進数に戻す
    $ipNetworkPart = bin2hex(substr($binaryIp, 0, 3));
    // 先頭1バイト (8bit) を抽出して16進数に戻す
    $range = bin2hex(substr($binaryIp, 0, 1));
}

// httpsの確認
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// https強制かつhttpsじゃない場合リダイレクト
if ($FORCESSL && empty($_SERVER['HTTPS'])) {
    header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
    exit;
}

// ブラウザに各種情報要求
header('Accept-CH: Sec-CH-UA-Arch, Sec-CH-UA-Bitness, Sec-CH-UA-Full-Version-List, Sec-CH-UA-Mobile, Sec-CH-UA-Model, Sec-CH-UA-Platform, Sec-CH-UA-Platform-Version');
// 文字化け防止
header('Content-type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 異常なUAを拒否
    if (
        strlen($_SERVER['HTTP_USER_AGENT']) !== mb_strlen($_SERVER['HTTP_USER_AGENT'], 'UTF-8') ||
        strlen($_SERVER['HTTP_USER_AGENT']) < 7 ||
        strlen($_SERVER['HTTP_USER_AGENT']) > 384 ||
        preg_match("/[^a-zA-Z0-9\-\/\.\(\):;,_\s]/", $_SERVER['HTTP_USER_AGENT'])
    ) {
        exit('認証エラー');
    }

    // 専ブラを拒否
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla/5.0') === false) {
        exit('専ブラからは認証できません。Webブラウザを使用してください');
    }

    // refererが無い
    // 開発環境ではスキップ
    if (!getenv('SKIP_VERIFICATION')) {
        if (empty($_SERVER['HTTP_REFERER'])) {
            exit('認証エラー');
        } else {
            if (!stristr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) {
                exit('認証エラー');
            }
            if ($_SERVER['HTTP_HOST'] !== $_SERVER['SERVER_NAME']) {
                exit('認証エラー');
            }
        }
    }

    // 簡易PROXYチェック
    $PROXY = false;
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $IP !== $_SERVER['HTTP_X_FORWARDED_FOR']) {
        $PROXY = true;
    }
    if (isset($_SERVER['HTTP_VIA'])) {
        $PROXY = true;
    }
    if (isset($_SERVER['HTTP_FORWARDED'])) {
        $PROXY = true;
    }
    if (isset($_SERVER['HTTP_CACHE_INFO'])) {
        $PROXY = true;
    }
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $PROXY = true;
    }
    if (isset($_SERVER['HTTP_PROXY_CONNECTION'])) {
        $PROXY = true;
    }
    if (isset($_SERVER['HTTP_SP_HOST'])) {
        $PROXY = true;
    }
    if (isset($_SERVER['HTTP_X_LOCKING'])) {
        $PROXY = true;
    }

    // turnstileチェック
    if (isset($_POST['cf-turnstile-response'])) {
        // フォームデータを準備
        $post_data = [
            'secret' => $SECRET_KEY,
            'response' => $token,
            'remoteip' => $IP,
        ];
        // cURLセッション初期化
        $ch = curl_init();
        // cURLのオプションを設定
        curl_setopt($ch, CURLOPT_URL, 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        // リクエストを実行し、レスポンスを取得
        $responseData = curl_exec($ch);
        // エラーがある場合はエラー情報を取得
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }
        // cURLセッションを閉じる
        curl_close($ch);

        // レスポンス
        $result = json_decode($responseData, true);
        $success = $result['success'];
        // $error = $result['error-codes'];

        if ($success === false) {
            print_r($success);
            exit('認証に失敗しました。再度やりなおしてください');
        }
    } else {
        exit('認証データがありません');
    }

    // クッキーがある場合はそれを返す
    if (isset($_COOKIE['WrtAgreementKey'])) {
        $wrtAgreementKey = $_COOKIE['WrtAgreementKey'];
        setcookie('WrtAgreementKey', $wrtAgreementKey, $NOWTIME + 31536000, '/');
        exit('認証に成功しました。Web版をご利用の場合はそのまま投稿できます<br>2ch専用ブラウザでの投稿時やCookie失効時は以下のキーをE-mail欄に入力してご利用ください<br>※E-mail欄は外部には表示されません<input name="mcode" onfocus="this.select()" value="#' . $wrtAgreementKey . '" style="display:block;margin:auto;width:95%;" readonly=""><hr><a href="#" onclick="window.history.go(-1);">前ページに戻る</a><br><a href="#" onclick="window.history.go(-2);">2つ前のページに戻る</a>');
    }

    // --------------------------------------------
    // proxycheck.io のAPIへアクセス　始まり
    // --------------------------------------------
    // 1. 初期化
    $ch = curl_init();

    // 2. オプションの設定
    $url = 'https://proxycheck.io/v2/' . $IP;
    $params = [
        'key' => $settings['proxycheck-apikey'] ?? '',
        'vpn' => 1,
        'asn' => 1,
        'risk' => 1,
    ];

    // GETパラメータ
    curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
    // 結果を文字列で返す
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // タイムアウト秒数
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    // ヘッダー指定
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
    ]);

    // 3. 実行
    $response = curl_exec($ch);

    // 4. エラーチェック
    if (curl_errno($ch)) {
        exit('APIへのアクセスに失敗しました。しばらく後にもう一度お試しください。');
    }

    // 5. 終了
    curl_close($ch);

    // responseデータをJSONへ
    $proxyCheckData = json_decode($response, true);
    if (($proxyCheckData['status'] ?? '') !== 'ok') {
        exit('APIへのアクセスに失敗しました。しばらく後にもう一度お試しください。');
    }
    if (!isset($proxyCheckData[$IP])) {
        exit('IP情報の取得に失敗しました。');
    }

    // --------------------------------------------
    // proxycheck.io のAPIへアクセス　ここまで
    // --------------------------------------------

    // HOST
    $HOST = $proxyCheckData[$IP]['hostname'] ?? $HOST;
    $reverse = $proxyCheckData[$IP]['hostname'] ?? '';
    $asname = $proxyCheckData[$IP]['asn'] ?? 'unknown';
    $provider = $proxyCheckData[$IP]['provider'] ?? 'unknown';
    $country = $proxyCheckData[$IP]['country'] ?? 'unknown';
    $ipType = $proxyCheckData[$IP]['type'] ?? 'unknown';
    $isProxy = ($proxyCheckData[$IP]['proxy'] ?? 'unknown') === 'yes';
    $risk = $proxyCheckData[$IP]['risk'] ?? 0;
    $region = $proxyCheckData[$IP]['region'] ?? 'unknown';
    $city = $proxyCheckData[$IP]['city'] ?? 'unknown';

    // 国名取得(CFを通さないサーバの場合)
    if (empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
        $_SERVER['HTTP_CF_IPCOUNTRY'] = $country;
    }

    // smart phone marks
    $admin = false;
    $SLIP_NAME = 'JP';
    $slip = '0';
    $SLIP_SP = $MM = $WF = false;
    @include './extend/smartphonemarks.php';

    // モバイルを検出
    if (
        $ipType === 'Wireless' &&
        $slip === '0' &&
        strpos($HOST, 'bbtec.net') === false &&
        strpos($HOST, 'ocn.ne.jp') === false &&
        strpos($HOST, 'dion.ne.jp') === false
    ) {
        $SLIP_SP = true;
        $SLIP_NAME = $asname;
    }

    // User-Agent Client Hints
    if ($_SERVER['HTTP_SEC_CH_UA_FULL_VERSION_LIST']) {
        $_SERVER['HTTP_SEC_CH_UA'] = $_SERVER['HTTP_SEC_CH_UA_FULL_VERSION_LIST'];
    }
    $CH_UA = $_SERVER['HTTP_SEC_CH_UA'] . $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] . $_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'] . $_SERVER['HTTP_SEC_CH_UA_BITNESS'] . $_SERVER['HTTP_SEC_CH_UA_ARCH'] . $_SERVER['HTTP_SEC_CH_UA_MODEL'] . $_SERVER['HTTP_SEC_CH_UA_MOBILE'];
    if (!$CH_UA) {
        $CH_UA = $_SERVER['HTTP_USER_AGENT'];
    }
    $ACCEPT = $_SERVER['HTTP_ACCEPT'] . $_SERVER['HTTP_ACCEPT_LANGUAGE'] . $_SERVER['CONTENT_TYPE'];

    // 仮で準備 同一環境チェックを緩めたい時に使用
    // // OS名取得
    // $os = $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] ?? '';
    // // 取れなかったら（Firefox/Safariなど）、従来のUAからOS名を推測する
    // if (!$os && isset($_SERVER['HTTP_USER_AGENT'])) {
    //     $ua = $_SERVER['HTTP_USER_AGENT'];
    //     if (preg_match('/android/i', $ua)) {
    //         $os = 'Android';
    //     } elseif (preg_match('/iphone|ipad|ipod/i', $ua)) {
    //         $os = 'iOS';
    //     } elseif (preg_match('/windows/i', $ua)) {
    //         $os = 'Windows';
    //     } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
    //         $os = 'macOS';
    //     } elseif (preg_match('/linux/i', $ua)) {
    //         $os = 'Linux';
    //     }
    // }

    // ホスティング判定
    if (
        $isProxy || $ipType === 'VPN'
    ) {
        $slip = 'H';
    }

    # 新規鍵を生成する
    $wrtAgreementKey = bin2hex(random_bytes(4));
    # 記録ファイルが設置された場所。
    $HAP_PATH = './HAP/';

    // ユーザー環境を生成
    $ipReverse = preg_replace('/[0-9]+/', '', $reverse);
    $fingerprint =
        $ipNetworkPart .
        $asname .
        $ipReverse;

    // ユーザー環境にブラウザ情報を追加
    if (($settings['use-browser-fingerprint'] ?? '') === 'checked') {
        $fingerprint .= $CH_UA . $ACCEPT;
    }

    // ユーザー環境のハッシュ
    $environmentHash = hash('sha256', $fingerprint);
    // 環境控えファイル
    $enFile = 'en_' . $environmentHash . '.cgi';
    $enPath = $HAP_PATH . $enFile;

    // ホスティング判定された回線からの認証を拒否
    $useStrictAuth = ($settings['use-strict-auth'] ?? 'checked') === 'checked';
    if (
        ($useStrictAuth && $slip === 'H') ||
        $risk >= 66
    ) {
        $data = [
            'status' => $authStatus,
            'wrt_agreement_key' => 'null',
            'ip' => $IP ?? 'unknown',
            'host' => $HOST ?? 'unknown',
            'isp' => $provider,
            'asname' => $asname,
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'ch_ua' => $CH_UA ?? 'unknown',
            'client_id' => 'null',
            'en_file' => 'null',
            'posted_at' => $NOWTIME,
        ];
        recordLog($data);
        exit('【認証エラー】ご使用の回線（海外/VPN/データセンター等）からの認証は現在制限されています。家庭用回線またはモバイル回線からお試しください。');
    }

    // 環境控えファイル更新が30日間以内なら同一キーを返す
    if (is_file($enPath)) {
        if (filemtime($enPath) + 30 * 24 * 60 * 60 > $NOWTIME) {
            $wrtAgreementKey = trim(safe_file_get_contents($enPath));
        }
    }
    // 環境控えファイルを更新
    file_put_contents($enPath, $wrtAgreementKey, LOCK_EX);

    // ログ記録
    $authStatus = 'success';
    $clientId = substr(md5($range . $asname . $CH_UA . $ACCEPT), 0, 7);
    $data = [
        'status' => $authStatus,
        'wrt_agreement_key' => $wrtAgreementKey,
        'ip' => $IP ?? 'unknown',
        'host' => $HOST ?? 'unknown',
        'isp' => $provider,
        'asname' => $asname,
        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'ch_ua' => $CH_UA ?? 'unknown',
        'client_id' => $clientId,
        'en_file' => $enFile,
        'posted_at' => $NOWTIME,
    ];
    recordLog($data);

    // アカウントID算出
    $accountId = hash('sha256', hash('sha256', md5($wrtAgreementKey) . preg_replace('/[^0-9]/', '', md5($wrtAgreementKey))));

    // ユーザーファイル作成
    $file = $HAP_PATH . $accountId . '.cgi';
    if (!is_file($file)) {
        $HAP = ['first' => $NOWTIME,
          'last' => '',
          'comment' => '',
          'HOST' => $HOST,
          'REMOTE_ADDR' => $IP,
          'ip_network_part' => $ipNetworkPart,
          'USER_AGENT' => $_SERVER['HTTP_USER_AGENT'],
          'CH_UA' => $CH_UA,
          'ACCEPT' => $ACCEPT,
          'range' => $range,
          'provider' => $provider,
          'country' => $_SERVER['HTTP_CF_IPCOUNTRY'],
          'region' => $region . ' ' . $city,
          'proxy' => $isProxy,
          'hosting' => $ipType === 'Hosting',
          'slip' => $slip,
          'SLIP_NAME' => $SLIP_NAME,
          'SLIP_SP' => $SLIP_SP,
          'MM' => $MM,
          'WF' => $WF,
         ];
        file_put_contents($file, json_encode($HAP, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
    setcookie('WrtAgreementKey', $wrtAgreementKey, $NOWTIME + 31536000, '/');
    exit('認証に成功しました。Web版をご利用の場合はそのまま投稿できます<br>2ch専用ブラウザでの投稿時やCookie失効時は以下のキーをE-mail欄に入力してご利用ください<br>※E-mail欄は外部には表示されません<input name="mcode" onfocus="this.select()" value="#' . $wrtAgreementKey . '" style="display:block;margin:auto;width:95%;" readonly=""><hr><a href="#" onclick="window.history.go(-1);">前ページに戻る</a><br><a href="#" onclick="window.history.go(-2);">2つ前のページに戻る</a>');

}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<META HTTP-EQUIV="pragma" CONTENT="no-cache">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>投稿前確認</title>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onloadTurnstileCallback" defer></script>
<script>
</script>
</head>
<body>
<h4>法的な投稿前確認画面</h4>
<form method="POST" id="postForm" action="">
<b>
<div>投稿を行うには下記に同意し、「同意する」をクリックする必要があります。</div>
<div>
・投稿者は、投稿に際して、利用する掲示板のローカルルールに同意することを承諾します。また、この投稿規定と利用する掲示板のローカルルールが相反する場合、利用する掲示板のローカルルールが優先されます。<br>
・投稿者は、全ての投稿に際し、発生する責任が投稿者に帰すことを承諾します。なお、本サービスでは利用する掲示板の削除規定に該当する場合などを除き、一度投稿したコンテンツを削除することはできません。<br>
・投稿者が本サービス上に投稿したコンテンツに関する知的財産権やその他の権利、義務については投稿者に帰属するものとします。<br>
・投稿者は、話題と無関係な広告の投稿に関して、相応の費用を支払うことを承諾します<br>
・投稿者は、掲示板運営者が本サービス上に投稿したコンテンツを自由に使用、コピー、複製、削除、処理、改変、修正、公衆送信、頒布、翻訳、表示および配信することに対し、無償かつ非独占的に使用することを許諾します。また、投稿内容を共有している掲示板の運営者が本サービス上に投稿したコンテンツを掲示板運営者が認める範囲内で削除、処理、改変、修正、公衆送信、頒布、翻訳、表示、配信などを行うことを許諾します。<br>
・投稿者は、掲示板運営者あるいはその指定する者および投稿内容を共有している掲示板の運営者に対して、掲示板運営者の指定が無い限り著作者人格権を一切行使しないことを承諾します。また、投稿者は掲示板運営者の指定が無い限り、第三者に対して、一切の権利（第三者に対して再許諾する権利を含みます）を許諾しないことを承諾します。 <br>
・投稿者は投稿時およびこの投稿前確認において、スパムや迷惑投稿の防止等を目的とし、掲示板運営者が次のデータを収集することに同意します。[発信元IPアドレス、Cookie、ユーザーエージェント、その他発信元を識別するための情報]<br>
・この投稿前確認では、スパム投稿を防止するためにCloudflare Turnstileを使用しています。
</div>
</b>
<div>上記に同意できない場合は前ページ等へ戻ってください。なお同意しない場合は投稿することはできません。</div>

<div class="#example-container">
  <input type="hidden" name="time" value=<?php echo time(); ?>>
  <input type="hidden" name="HOST" value=<?= htmlspecialchars($HOST, ENT_QUOTES, 'UTF-8'); ?>>
  <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars($sitekey, ENT_QUOTES, 'UTF-8'); ?>"></div>
  <button type="submit" value="Submit">上記全てに同意する</button>
</div>
</form>
</body>
</html>
