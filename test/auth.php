<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);
# Cloudflare Turnstile sitekey,secretkey
$sitekey = '1x00000000000000000000AA';
$SECRET_KEY = '1x0000000000000000000000000000000AA';

$FORCESSL = true; #https未対応の場合はfalseにすること
if (getenv('SKIP_VERIFICATION')) {
    // 開発環境ではhttp可
    $FORCESSL = false;
}
$NOWTIME = time();
if (file_exists(__DIR__ . '/.use_cloudflare') && isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}
$IP = $_SERVER['REMOTE_ADDR'];
$HOST = gethostbyaddr($IP);
$area = [];
$area['district'] = $area['proxy'] = $area['hosting'] = $area['regionName'] = $area['city'] = $area['countryCode'] = $area['mobile'] = $area['asname'] = '';

include './utils/safe-file-get-contents.php';

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
$HOST = gethostbyaddr($IP);

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
    $range = bin2hex(substr($binaryIp, 0, 2));
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
    // 以上なUAを拒否
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

    // smart phone marks
    $admin = false;
    $SLIP_NAME = 'JP';
    $slip = '0';
    $SLIP_SP = $MM = $WF = false;
    @include './extend/smartphonemarks.php';

    // --------------------------------------------
    // ip-api.comのAPIへアクセス　始まり
    // --------------------------------------------
    $options = [
            'http' => [
                    'method' => 'GET',
                    ],
            ];
    $url = 'http://ip-api.com/json/'.$IP.'?fields=countryCode,regionName,city,asname,mobile,proxy,hosting&lang=ja';
    $cp = curl_init();
    /*オプション:リダイレクトされたらリダイレクト先のページを取得する*/
    curl_setopt($cp, CURLOPT_RETURNTRANSFER, 1);
    /*オプション:URLを指定する*/
    curl_setopt($cp, CURLOPT_URL, $url);
    /*オプション:タイムアウト時間を指定する*/
    curl_setopt($cp, CURLOPT_TIMEOUT, 2000);
    /*オプション:ユーザーエージェントを指定する*/
    curl_setopt($cp, CURLOPT_USERAGENT, 'Mozilla/5.0 P2/2.5 (iPad; CPU OS 13_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/87.0.4280.77 Mobile/15E148 Safari/604.1');
    curl_setopt($cp, CURLOPT_HEADER, true);
    $source = curl_exec($cp);
    $curlInfo = curl_getinfo($cp);
    // ヘッダを一緒に出力したときは分割させる
    $headerSize = false;
    if (isset($curlInfo['header_size']) && $curlInfo['header_size'] !== '') {
        $headerSize = $curlInfo['header_size'];
    }
    $head = substr($source, 0, $headerSize); // ヘッダ部
    $head = str_replace(["\r\n", "\r", "\n"], "\n", $head);
    $header = explode("\n", $head);
    foreach ($header as $tmp) {
        list($key, $value) = explode(': ', $tmp);
        $HTTP[$key] = $value;
    }
    $data = substr($source, $headerSize);    // ボディ部
    curl_close($cp);
    $area = json_decode($data, true);
    // 国名取得(CFを通さないサーバの場合)
    if (empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
        if ($area['countryCode']) {
            $_SERVER['HTTP_CF_IPCOUNTRY'] = $area['countryCode'];
        } else {
            $_SERVER['HTTP_CF_IPCOUNTRY'] = 'JP';
        }
    }
    // モバイルを検出
    if (
        $area['mobile'] === true &&
        $slip === '0' &&
        strpos($HOST, 'bbtec.net') === false &&
        strpos($HOST, 'ocn.ne.jp') === false &&
        strpos($HOST, 'dion.ne.jp') === false
    ) {
        $slip = 'S';
        $SLIP_SP = true;
        $SLIP_NAME = $area['asname'];
    }
    // --------------------------------------------
    // ip-api.comのAPIへアクセス　ここまで
    // --------------------------------------------

    // User-Agent Client Hints
    if ($_SERVER['HTTP_SEC_CH_UA_FULL_VERSION_LIST']) {
        $_SERVER['HTTP_SEC_CH_UA'] = $_SERVER['HTTP_SEC_CH_UA_FULL_VERSION_LIST'];
    }
    $CH_UA = $_SERVER['HTTP_SEC_CH_UA'].$_SERVER['HTTP_SEC_CH_UA_PLATFORM'].$_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'].$_SERVER['HTTP_SEC_CH_UA_BITNESS'].$_SERVER['HTTP_SEC_CH_UA_ARCH'].$_SERVER['HTTP_SEC_CH_UA_MODEL'].$_SERVER['HTTP_SEC_CH_UA_MOBILE'];
    if (!$CH_UA) {
        $CH_UA = $_SERVER['HTTP_USER_AGENT'];
    }
    $ACCEPT = $_SERVER['HTTP_ACCEPT'].$_SERVER['HTTP_ACCEPT_LANGUAGE'].$_SERVER['CONTENT_TYPE'];

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
        // hostが.jpではない
        !preg_match("/\.jp$/i", $HOST) &&
        // hostが.bbtec.netではない
        !preg_match("/\.bbtec\.net$/", $HOST) &&
        // スマホ回線ではない
        !$SLIP_SP &&
        // MVNOではない
        !$MM &&
        // フリーwifiではない
        !$WF
    ) {
        $slip = 'H';
    }

    // ホスティング判定された回線からの認証を拒否
    if (file_exists(__DIR__ . '/.use_strict_auth') && $slip === 'H') {
        exit('【認証エラー】ご使用の回線（海外/VPN/データセンター等）からの認証は現在制限されています。家庭用回線またはモバイル回線からお試しください。');
    }

    # 鍵を生成する(uuid上8桁の英数字)
    $WrtAgreementKey = substr(uniqid(), 0, 8);
    # 記録ファイルが設置された場所。
    $HAP_PATH = './HAP/';

    $fingerprint =
        $ipNetworkPart.
        $area['asname'];
    // 以下を混ぜると範囲が狭くなる
    // $os
    // $CH_UA
    // $ACCEPT

    // ユーザー環境のハッシュ
    $environmentHash = hash('sha256', $fingerprint);
    // 環境控えファイル
    $enFile = $HAP_PATH.'en_'.$environmentHash.'.cgi';

    // クッキーがある場合はそれを返す
    if (isset($_COOKIE['WrtAgreementKey'])) {
        $WrtAgreementKey = $_COOKIE['WrtAgreementKey'];
    } elseif (is_file($enFile)) {
        // 環境控えファイル更新が7日間以内なら同一キーを返す
        if (filemtime($enFile) + 7 * 24 * 60 * 60 > $NOWTIME) {
            $WrtAgreementKey = trim(safe_file_get_contents($enFile));
        }
    }
    // 環境控えファイルを更新
    file_put_contents($enFile, $WrtAgreementKey, LOCK_EX);

    // クライアントID算出
    $clientid = hash('sha256', hash('sha256', md5($WrtAgreementKey).preg_replace('/[^0-9]/', '', md5($WrtAgreementKey))));

    // ユーザーファイル作成
    $file = $HAP_PATH.$clientid.'.cgi';
    if (!is_file($file)) {
        $HAP = ['first' => $NOWTIME,
          'last' => '',
          'comment' => '',
          'HOST' => $HOST,
          'REMOTE_ADDR' => $IP,
          'USER_AGENT' => $_SERVER['HTTP_USER_AGENT'],
          'CH_UA' => $CH_UA,
          'ACCEPT' => $ACCEPT,
          'range' => $range,
          'provider' => $area['asname'],
          'country' => $_SERVER['HTTP_CF_IPCOUNTRY'],
          'region' => $area['regionName'].$area['city'].$area['district'],
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
    exit('認証に成功しました。Web版をご利用の場合はそのまま投稿できます<br>2ch専用ブラウザでの投稿時やCookie失効時は以下のキーをE-mail欄に入力してご利用ください<br>※E-mail欄は外部には表示されません<input name="mcode" onfocus="this.select()" value="#'.$WrtAgreementKey.'" style="display:block;margin:auto;width:95%;" readonly=""><hr><a href="#" onclick="window.history.go(-1);">前ページに戻る</a><br><a href="#" onclick="window.history.go(-2);">2つ前のページに戻る</a>');

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
<form method="POST" accept-charset="Shift_JIS" id="postForm" action=""><b><div>投稿を行うには下記に同意し、「同意する」をクリックする必要があります。</div>
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
  <input type=hidden name=time value=<?php echo time(); ?>>
  <input type=hidden name=HOST value=<?=$HOST;?>>
  <div class="cf-turnstile" data-sitekey="<?=$sitekey;?>"></div>
  <button type="submit" value="Submit">上記全てに同意する</button>
</div>

</body>
</html>
