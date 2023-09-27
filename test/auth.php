<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);
# Google reCAPTCHA sitekey,secretkey
$sitekey = '';
$secretkey = '';
$FORCESSL = true; #https未対応の場合はfalseにすること
$NOWTIME = time();
$HOST = gethostbyaddr($_SERVER['REMOTE_ADDR']);
$area = [];
$area["district"] = $area['proxy'] = $area['hosting'] = $area['regionName'] = $area['city'] = $area['countryCode'] = $area['mobile'] = $area["asname"] = '';
if (!isset($_SERVER['HTTP_SEC_CH_UA'])) $_SERVER['HTTP_SEC_CH_UA'] = '';
if (!isset($_SERVER['HTTP_SEC_CH_UA_PLATFORM'])) $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] = '';
if (!isset($_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'])) $_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'] = '';
if (!isset($_SERVER['HTTP_SEC_CH_UA_BITNESS'])) $_SERVER['HTTP_SEC_CH_UA_BITNESS'] = '';
if (!isset($_SERVER['HTTP_SEC_CH_UA_ARCH'])) $_SERVER['HTTP_SEC_CH_UA_ARCH'] = '';
if (!isset($_SERVER['HTTP_SEC_CH_UA_MODEL'])) $_SERVER['HTTP_SEC_CH_UA_MODEL'] = '';
if (!isset($_SERVER['HTTP_SEC_CH_UA_MOBILE'])) $_SERVER['HTTP_SEC_CH_UA_MOBILE'] = '';
if (!isset($_SERVER['HTTP_SEC_CH_UA_FULL_VERSION_LIST'])) $_SERVER['HTTP_SEC_CH_UA_FULL_VERSION_LIST'] = '';
// IPv6に対応したサーバ用
$count_semi = substr_count($_SERVER['REMOTE_ADDR'], ':');
$count_dot = substr_count($_SERVER['REMOTE_ADDR'], '.');
if ($count_semi > 0 && $count_dot == 0) $ipv6 = $_SERVER['REMOTE_ADDR'];
else $ipv6 = '';
// IPアドレス範囲
if ($ipv6) {
 $d = explode(":", $_SERVER['REMOTE_ADDR']);
 // IPv6では後半を切り捨て
 $IP_ADDR = $d[0].":".$d[1].":".$d[2].":".$d[3];
 $range = $d[0].":".$d[1].":".substr($d[2], 0, 2);
}else {
 $IP_ADDR = $_SERVER['REMOTE_ADDR'];
 $d = explode(".", $_SERVER['REMOTE_ADDR']);
 // IPアドレスの先端一部を取り出す
 $range = $d[0];
}
$target  = array(':', '.');
$fileipaddr = str_replace($target, '', $IP_ADDR);

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === "https") {
	$_SERVER['HTTPS'] = 'on';
}

if ($FORCESSL && empty($_SERVER['HTTPS'])) {
    header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
    exit;
}

header("Accept-CH: Sec-CH-UA-Arch, Sec-CH-UA-Bitness, Sec-CH-UA-Full-Version-List, Sec-CH-UA-Mobile, Sec-CH-UA-Model, Sec-CH-UA-Platform, Sec-CH-UA-Platform-Version");
header("Content-type: text/html; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
# UAチェック
if (strlen($_SERVER['HTTP_USER_AGENT']) != mb_strlen($_SERVER['HTTP_USER_AGENT'],"UTF-8") || strlen($_SERVER['HTTP_USER_AGENT']) < 7 || strlen($_SERVER['HTTP_USER_AGENT']) > 384 || preg_match("/[^a-zA-Z0-9\-\/\.\(\):;,_\s]/", $_SERVER['HTTP_USER_AGENT'])) exit('認証エラー');

if (strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla/5.0') === false) exit('専ブラからは認証できません。Webブラウザを使用してください');

// refererが無い
if (empty($_SERVER['HTTP_REFERER'])) exit('認証エラー');
else {
 if (!stristr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) exit('認証エラー');
 if ($_SERVER['HTTP_HOST'] != $_SERVER['SERVER_NAME']) exit('認証エラー');
}

// 簡易PROXYチェック
$PROXY = false;
if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['REMOTE_ADDR'] != $_SERVER['HTTP_X_FORWARDED_FOR']) $PROXY = true;
if (isset($_SERVER['HTTP_VIA'])) $PROXY = true;
if (isset($_SERVER['HTTP_FORWARDED'])) $PROXY = true;
if (isset($_SERVER['HTTP_CACHE_INFO'])) $PROXY = true;
if (isset($_SERVER['HTTP_CLIENT_IP'])) $PROXY = true;
if (isset($_SERVER['HTTP_PROXY_CONNECTION'])) $PROXY = true;
if (isset($_SERVER['HTTP_SP_HOST'])) $PROXY = true;
if (isset($_SERVER['HTTP_X_LOCKING'])) $PROXY = true;
#if ($PROXY) exit('認証エラー'); // 環境によっては誤判定が起きるので使わない

 if (isset($_POST['g-recaptcha-response'])) {
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $param = array(
      'secret' => $secretkey,
      'response' => $_POST['g-recaptcha-response']
    );
    $context = array(
      'http' => array(
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded\r\n',
        'content' => http_build_query($param)
      )
    );
    $json = file_get_contents($url, false, stream_context_create($context));
    $results = json_decode($json,true);
    $success = $results["success"];
    $error = $results["error-codes"];
    if (strlen($_POST['ClientID']) != 32 || $success == false || $error) {
	exit("認証に失敗しました。再度やりなおしてください");
    }

// smart phone marks
$admin = false;
$SLIP_NAME = 'JP';
$slip = "0";
$SLIP_SP = $MM = $WF = false;
@include './extend/smartphonemarks.php';

$options =array(
        'http' =>array(
                'method' => "GET",
                )
        );
 $url = "http://ip-api.com/json/".$_SERVER['REMOTE_ADDR']."?fields=countryCode,regionName,city,asname,mobile,proxy,hosting&lang=ja";
 $cp = curl_init();
 /*オプション:リダイレクトされたらリダイレクト先のページを取得する*/
 curl_setopt($cp, CURLOPT_RETURNTRANSFER, 1);
 /*オプション:URLを指定する*/
 curl_setopt($cp, CURLOPT_URL, $url);
 /*オプション:タイムアウト時間を指定する*/
 curl_setopt($cp, CURLOPT_TIMEOUT, 2000);
 /*オプション:ユーザーエージェントを指定する*/
curl_setopt($cp, CURLOPT_USERAGENT, "Mozilla/5.0 P2/2.5 (iPad; CPU OS 13_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/87.0.4280.77 Mobile/15E148 Safari/604.1");
 curl_setopt($cp, CURLOPT_HEADER, true);
 $source = curl_exec($cp);
 $curlInfo = curl_getinfo($cp);
   // ヘッダを一緒に出力したときは分割させる
   $headerSize = false;
   if ( isset($curlInfo["header_size"]) && $curlInfo["header_size"]!="" ) {
      $headerSize = $curlInfo["header_size"];
   }
   $head = substr($source, 0, $headerSize); // ヘッダ部
 $head = str_replace(["\r\n", "\r", "\n"], "\n", $head);
 $header = explode("\n", $head);
 foreach ($header as $tmp) {
 list($key, $value) = explode(": ", $tmp);
 $HTTP[$key] = $value;
 }
   $data = substr($source, $headerSize);    // ボディ部
 curl_close($cp);
 $area = json_decode($data, true);
 // 国名取得(CFを通さないサーバの場合)
 if (empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
  if ($area["countryCode"]) $_SERVER['HTTP_CF_IPCOUNTRY'] = $area["countryCode"];
  else $_SERVER['HTTP_CF_IPCOUNTRY'] = "JP";
 }
 // モバイルを検出
 if ($area['mobile'] == true && $slip == "0" && strpos($HOST, 'bbtec.net') === false && strpos($HOST, 'ocn.ne.jp') === false && strpos($HOST, 'dion.ne.jp') === false) {
  $slip = "S";
  $SLIP_SP = true;
  $SLIP_NAME = $area["asname"];
 }

// User-Agent Client Hints
if ($_SERVER['HTTP_SEC_CH_UA_FULL_VERSION_LIST']) $_SERVER['HTTP_SEC_CH_UA'] = $_SERVER['HTTP_SEC_CH_UA_FULL_VERSION_LIST'];
$CH_UA = $_SERVER['HTTP_SEC_CH_UA'].$_SERVER['HTTP_SEC_CH_UA_PLATFORM'].$_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'].$_SERVER['HTTP_SEC_CH_UA_BITNESS'].$_SERVER['HTTP_SEC_CH_UA_ARCH'].$_SERVER['HTTP_SEC_CH_UA_MODEL'].$_SERVER['HTTP_SEC_CH_UA_MOBILE'];
if (!$CH_UA) $CH_UA = $_SERVER['HTTP_USER_AGENT'];
$ACCEPT = $_SERVER['HTTP_ACCEPT'].$_SERVER['HTTP_ACCEPT_LANGUAGE'].$_SERVER['CONTENT_TYPE'];

if (!preg_match("/\.jp$/i", $HOST) && !preg_match("/\.bbtec\.net$/", $HOST) && !$ipv6 && !$SLIP_SP && !$MM && !$WF) $slip = "H";

# 鍵を生成する(ClientIDから生成される8桁の数値)
$WrtAgreementKey = substr(preg_replace("/[^0-9]/", "", md5($_POST['ClientID'])), 0, 8);
# 記録ファイルが設置された場所。
$HAP_PATH = './HAP/';
$ip_file = $HAP_PATH.'ip_'.hash('sha256', $fileipaddr).'.cgi';
$kr_file = $HAP_PATH.'kr_'.md5($range.$area["asname"].$CH_UA.$ACCEPT).'.cgi';
$nokr = $noip = false;
if (is_file($kr_file)) {
 if (filemtime($kr_file) + 2592000 > $NOWTIME) $WrtAgreementKey = file_get_contents($kr_file);
 else $nokr = true;
}else $nokr = true;
if (is_file($ip_file)) {
 if (filemtime($ip_file) + 2592000 > $NOWTIME) $WrtAgreementKey = file_get_contents($ip_file);
 else $noip = true;
}else $noip = true;

if ($noip) file_put_contents($ip_file, $WrtAgreementKey, LOCK_EX);
if ($nokr) file_put_contents($kr_file, $WrtAgreementKey, LOCK_EX);

$clientid = hash('sha256', hash('sha256', md5($WrtAgreementKey).preg_replace("/[^0-9]/", "", md5($WrtAgreementKey))));
$file = $HAP_PATH.$clientid.'.cgi';
if (!is_file($file)) {
    $HAP = ["first"=>$NOWTIME,
 	  "last"=>'',
 	  "comment"=>'',
   	  "HOST"=>$HOST,
   	  "REMOTE_ADDR"=>$IP_ADDR,
 	  "USER_AGENT"=>$_SERVER['HTTP_USER_AGENT'],
 	  "CH_UA"=>$CH_UA,
 	  "ACCEPT"=>$ACCEPT,
 	  "range"=>$range,
 	  "provider"=>$area["asname"],
 	  "country"=>$_SERVER['HTTP_CF_IPCOUNTRY'],
 	  "region"=>$area["regionName"].$area["city"].$area["district"],
 	  "proxy"=>$area["proxy"],
 	  "hosting"=>$area["hosting"],
 	  "slip"=>$slip,
 	  "SLIP_NAME"=>$SLIP_NAME,
 	  "SLIP_SP"=>$SLIP_SP,
 	  "MM"=>$MM,
 	  "WF"=>$WF,
 	 ];
    file_put_contents($file, json_encode($HAP, JSON_UNESCAPED_UNICODE), LOCK_EX);
}
	setcookie("WrtAgreementKey", $WrtAgreementKey, $NOWTIME+31536000, "/");
    exit('認証に成功しました。Web版をご利用の場合はそのまま投稿できます<br>2ch専用ブラウザでの投稿時やCookie失効時は以下のキーをE-mail欄に入力してご利用ください<br>※E-mail欄は外部には表示されません<input name="mcode" onfocus="this.select()" value="#'.$WrtAgreementKey.'" style="display:block;margin:auto;width:95%;" readonly=""><hr><a href="#" onclick="window.history.go(-1);">前ページに戻る</a><br><a href="#" onclick="window.history.go(-2);">2つ前のページに戻る</a>');
 }else exit("認証データがありません");
}
?>
<HTML>
<HEAD>
<title>投稿前確認</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META HTTP-EQUIV="pragma" CONTENT="no-cache">
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
<script src="/static/clientid.js"></script>
<script src="https://www.google.com/recaptcha/api.js"></script>
<script>
function onSubmit(token) {
 document.getElementById('postForm').submit();
}
</script>
</HEAD><body>
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
・この投稿前確認では、スパム投稿を防止するためにreCAPTCHAを使用しています。
</div>
</b>
<div>上記に同意できない場合は前ページ等へ戻ってください。なお同意しない場合は投稿することはできません。</div>
<input type=hidden name=time value=<?php echo time(); ?>>
<input type=hidden name=HOST value=<?=$HOST?>>
<input type=hidden name=recaptcha_challenge_field>
<input type=hidden name=recaptcha_response_field>
<input type=hidden name=ClientID id=ClientID>
<button class="g-recaptcha" data-sitekey="<?=$sitekey?>" data-callback="onSubmit" error-callback="onReCaptchaError">上記全てに同意する</button>
</form>
<script>
  const getclientid = clientid.load()
  getclientid
    .then(fp => fp.get())
    .then(result => document.getElementById('ClientID').value = result.visitorId)
</script>
</body>
</HTML>
<?
exit;	