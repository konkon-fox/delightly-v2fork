<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);

require './utils/get-json-file.php';
require './utils/safe-file-get-contents.php';

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
$useCloudflare = $settings['use-cloudflare'] ?? 'checked' === 'checked';
if ($useCloudflare && isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}

$IP = $_SERVER['REMOTE_ADDR'];
$HOST = $IP;
$area = [];
$area['district'] = $area['proxy'] = $area['hosting'] = $area['regionName'] = $area['city'] = $area['countryCode'] = $area['mobile'] = $area['asname'] = '';
$authStatus = 'failed';

/**
 * 認証ログを記録する関数
 *
 * @param 'success'|'failed' $authStatus 認証の成功判定
 * @param string $WrtAgreementKey 同意鍵
 * @param string $IP IPアドレス
 * @param string $HOST ホスト
 * @param string $isp プロバイダ
 * @param string $asname
 * @param string $UA UA
 * @param string $CH_UA Client Hints
 * @param string $clientId Client ID
 * @param string $enFile 環境ファイル名
 */
function recordLog(
    $authStatus,
    $WrtAgreementKey,
    $IP,
    $HOST,
    $isp,
    $asname,
    $UA,
    $CH_UA,
    $clientId,
    $enFile
) {
    // 定数定義
    $LOG_LIMITS = 10000;
    // ログを追記
    $nowDate = date('Y-m-d H:i:s');
    $log = $nowDate . '<>' . $authStatus . '<>' . $WrtAgreementKey . '<>' . $IP . '<>' . $HOST . '<>' . $isp . '<>' . $asname . '<>' . $UA . '<>' . $CH_UA . '<>' . $clientId . '<>' . $enFile . "\n";
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
        $response = curl_exec($ch);
        // エラーがある場合はエラー情報を取得
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }
        // cURLセッションを閉じる
        curl_close($ch);

        // レスポンス
        $result = json_decode($response, true);
        $success = $result['success'];
        $error = $result['error-codes'];

        if ($success === false) {
            print_r($success);
            exit('認証に失敗しました。再度やりなおしてください');
        }
    } else {
        exit('認証データがありません');
    }

    // クッキーがある場合はそれを返す
    if (isset($_COOKIE['WrtAgreementKey'])) {
        $WrtAgreementKey = $_COOKIE['WrtAgreementKey'];
        setcookie('WrtAgreementKey', $WrtAgreementKey, $NOWTIME + 31536000, '/');
        exit('認証に成功しました。Web版をご利用の場合はそのまま投稿できます<br>2ch専用ブラウザでの投稿時やCookie失効時は以下のキーをE-mail欄に入力してご利用ください<br>※E-mail欄は外部には表示されません<input name="mcode" onfocus="this.select()" value="#' . $WrtAgreementKey . '" style="display:block;margin:auto;width:95%;" readonly=""><hr><a href="#" onclick="window.history.go(-1);">前ページに戻る</a><br><a href="#" onclick="window.history.go(-2);">2つ前のページに戻る</a>');
    }

    // --------------------------------------------
    // ip-api.comのAPIへアクセス　始まり
    // --------------------------------------------
    $options = [
            'http' => [
                    'method' => 'GET',
                    ],
            ];
    $url = 'http://ip-api.com/json/' . $IP . '?fields=countryCode,regionName,city,isp,asname,reverse,mobile,proxy,hosting&lang=ja';
    $cp = curl_init();
    /*オプション:リダイレクトされたらリダイレクト先のページを取得する*/
    curl_setopt($cp, CURLOPT_RETURNTRANSFER, 1);
    /*オプション:URLを指定する*/
    curl_setopt($cp, CURLOPT_URL, $url);
    /*オプション:タイムアウト時間を指定する*/
    curl_setopt($cp, CURLOPT_TIMEOUT, 5);
    /*オプション:ユーザーエージェントを指定する*/
    curl_setopt($cp, CURLOPT_USERAGENT, 'Mozilla/5.0 P2/2.5 (iPad; CPU OS 13_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/87.0.4280.77 Mobile/15E148 Safari/604.1');
    curl_setopt($cp, CURLOPT_HEADER, true);
    $source = curl_exec($cp);
    $curlInfo = curl_getinfo($cp);

    $headerSize = $curlInfo['header_size'];
    $head = substr($source, 0, $headerSize);
    $data = substr($source, $headerSize);
    curl_close($cp);

    // --------------------------------------------
    // ip-api.comのAPIへアクセス　ここまで
    // --------------------------------------------

    // ヘッダーを解析して配列に格納
    $HTTP = [];
    $headLines = explode("\n", str_replace(["\r\n", "\r"], "\n", $head));
    foreach ($headLines as $line) {
        if (strpos($line, ': ') !== false) {
            list($key, $value) = explode(': ', $line, 2);
            $HTTP[strtolower(trim($key))] = trim($value); // キーを小文字で統一して保存
        }
    }

    // API制限（X-Rl）のチェック
    if (isset($HTTP['x-rl']) && (int) $HTTP['x-rl'] <= 5) {
        exit('【認証エラー】サーバーの認証リクエストが上限に達しました。1分ほど時間を置いてから再度お試しください。');
    }

    // $areaに結果を格納
    $area = json_decode($data, true);

    // HOST
    $HOST = $area['reverse'] ?? $HOST;

    // 国名取得(CFを通さないサーバの場合)
    if (empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
        if ($area['countryCode']) {
            $_SERVER['HTTP_CF_IPCOUNTRY'] = $area['countryCode'];
        } else {
            $_SERVER['HTTP_CF_IPCOUNTRY'] = 'JP';
        }
    }

    // smart phone marks
    $admin = false;
    $SLIP_NAME = 'JP';
    $slip = '0';
    $SLIP_SP = $MM = $WF = false;
    @include './extend/smartphonemarks.php';

    // モバイルを検出
    if (
        $area['mobile'] === true &&
        $slip === '0' &&
        strpos($HOST, 'bbtec.net') === false &&
        strpos($HOST, 'ocn.ne.jp') === false &&
        strpos($HOST, 'dion.ne.jp') === false
    ) {
        $SLIP_SP = true;
        $SLIP_NAME = $area['asname'];
    }

    // 新slip(末尾)に置き換え
    require './extend/get-end-char.php';
    $slip = getEndChar();

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
        $area['proxy'] || $area['hosting']
    ) {
        $slip = 'H';
    }

    # 新規鍵を生成する
    $WrtAgreementKey = bin2hex(random_bytes(4));
    # 記録ファイルが設置された場所。
    $HAP_PATH = './HAP/';

    // ユーザー環境を生成
    $ipReverse = preg_replace('/[0-9]+/', '', $area['reverse'] ?? '');
    $fingerprint =
        $ipNetworkPart .
        $area['asname']
        . $ipReverse;

    // ユーザー環境にブラウザ情報を追加
    if ($settings['use-browser-fingerprint'] ?? '' === 'checked') {
        $fingerprint .= $CH_UA . $ACCEPT;
    }

    // ユーザー環境のハッシュ
    $environmentHash = hash('sha256', $fingerprint);
    // 環境控えファイル
    $enFile = 'en_' . $environmentHash . '.cgi';
    $enPath = $HAP_PATH . $enFile;

    // ホスティング判定された回線からの認証を拒否
    $useStrictAuth = $settings['use-strict-auth'] ?? 'checked' === 'checked';
    if ($useStrictAuth && $slip === 'H') {
        recordLog(
            $authStatus,
            'null', // $WrtAgreementKey
            $IP,
            $HOST,
            $area['isp'],
            $area['asname'],
            $_SERVER['HTTP_USER_AGENT'],
            $CH_UA,
            'null', // $clientId
            'null' // $enFile
        );
        exit('【認証エラー】ご使用の回線（海外/VPN/データセンター等）からの認証は現在制限されています。家庭用回線またはモバイル回線からお試しください。');
    }

    // 環境控えファイル更新が30日間以内なら同一キーを返す
    if (is_file($enPath)) {
        if (filemtime($enPath) + 30 * 24 * 60 * 60 > $NOWTIME) {
            $WrtAgreementKey = trim(safe_file_get_contents($enPath));
        }
    }
    // 環境控えファイルを更新
    file_put_contents($enPath, $WrtAgreementKey, LOCK_EX);

    // ログ記録
    $authStatus = 'success';
    $clientId = substr(md5($range . $area['asname'] . $CH_UA . $ACCEPT), 0, 7);
    recordLog(
        $authStatus,
        $WrtAgreementKey,
        $IP,
        $HOST,
        $area['isp'],
        $area['asname'],
        $_SERVER['HTTP_USER_AGENT'],
        $CH_UA,
        $clientId,
        $enFile
    );

    // アカウントID算出
    $accountId = hash('sha256', hash('sha256', md5($WrtAgreementKey) . preg_replace('/[^0-9]/', '', md5($WrtAgreementKey))));

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
          'provider' => $area['asname'],
          'country' => $_SERVER['HTTP_CF_IPCOUNTRY'],
          'region' => $area['regionName'] . $area['city'] . $area['district'],
          'proxy' => $area['proxy'],
          'hosting' => $area['hosting'],
          'slip' => $slip,
          'SLIP_NAME' => $SLIP_NAME,
          'SLIP_SP' => $SLIP_SP,
          'MM' => $MM,
          'WF' => $WF,
         ];
        file_put_contents($file, json_encode($HAP, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
    setcookie('WrtAgreementKey', $WrtAgreementKey, $NOWTIME + 31536000, '/');
    exit('認証に成功しました。Web版をご利用の場合はそのまま投稿できます<br>2ch専用ブラウザでの投稿時やCookie失効時は以下のキーをE-mail欄に入力してご利用ください<br>※E-mail欄は外部には表示されません<input name="mcode" onfocus="this.select()" value="#' . $WrtAgreementKey . '" style="display:block;margin:auto;width:95%;" readonly=""><hr><a href="#" onclick="window.history.go(-1);">前ページに戻る</a><br><a href="#" onclick="window.history.go(-2);">2つ前のページに戻る</a>');

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
