<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);
ob_start();
if (function_exists('sys_getloadavg')) {
 $loadavg = sys_getloadavg(); //LoadAverageを取得
 if ($loadavg !== false) {
  // LA200以上は拒否
  if ($loadavg[0] > 200) Error2("現在高負荷のため、bbs.cgiを一時的に停止しています。お手数ですが、Web版からの投稿をお願いします。 -> LoadAverage:".$loadavg[0]);
  if ($loadavg[0] > 50 && (empty($_POST['mail']) || strlen($_POST['mail']) !== 9) && (empty($_COOKIE['WrtAgreementKey']) || strlen($_COOKIE['WrtAgreementKey']) !== 8)) finish();
 }
}

// 専ブラ用なのでShift_JISで出力
header('Content-Type: text/html; charset=Shift_JIS');
if (!isset($_POST['bbs'])) $_POST['bbs'] = '';
if (!isset($_POST['key'])) $_POST['key'] = '';
if (!isset($_POST['MESSAGE'])) $_POST['MESSAGE'] = '';
if (!isset($_POST['FROM'])) $_POST['name'] = '';
if (!isset($_POST['mail'])) $_POST['mail'] = '';
if (!isset($_POST['subject'])) $_POST['subject'] = '';
$PATH = "../".$_POST['bbs']."/";
$NOWTIME = time();

// 一部特殊なアプリが有るためv2ではMonazilla以外のUAも許容する。

// 投稿先の掲示板設定を取得
$setfile = $PATH."setting.json";
if (!is_file($setfile)) Error2("This board does not exist.");
$SETTING = json_decode(file_get_contents($setfile), true);

// 専ブラ投稿が許可されていない場合はここで拒否
if ($SETTING['2ch_dedicate_browsers'] != "enable") Error2("invalid:2ch dedicate browsers is forbidden.");

// 専ブラなのにtimeなし
if (!$_POST['time']) Error2("invalid");

// Shift_JISからUTF-8へ
mb_convert_variables('UTF-8','SJIS-win',$_POST);

$_POST['comment'] = $_POST['MESSAGE'];
$_POST['title'] = $_POST['subject'];
$_POST['name'] = $_POST['FROM'];
$_POST['board'] = $_POST['bbs'];
$_POST['thread'] = $_POST['key'];

require "bbs-main.php";

// 投稿完了画面
function finish() {
?><html><head><title>書きこみました。</title><meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS"></head><body>書きこみが終わりました。<br><br>画面を切り替えるまでしばらくお待ち下さい。</body></html>
<?php exit;
}

// 各種規制など(専ブラ向けに整形)
function Error($error) {
 global $PATH,$HOST,$DATE,$ID,$WrtAgreementKey,$number,$CH_UA,$ACCEPT,$accountid,$LV,$info;
 // エラーログに保存
 if (is_file($PATH."errors.cgi")) $EROG = file($PATH."errors.cgi");
 else $EROG = [];
 array_unshift($EROG, $error."<>".$_POST['name']."<>".$_POST['mail']."<>".$DATE." ".$ID."<>".$_POST['comment']."<>".$_POST['title']."<>".$_POST['thread']."<>".$number."<>".$HOST."<>".$_SERVER['REMOTE_ADDR']."<>".$_SERVER['HTTP_USER_AGENT']."<>".$CH_UA."<>".$ACCEPT."<>".$WrtAgreementKey."<>".$LV."<>".$info."\n");
 // 500 個以内に調整して保存
 while (count($EROG) > 500) array_pop($EROG);
 $EROG = array_unique($EROG);
 $fp = @fopen($PATH."errors.cgi", "w");
 foreach($EROG as $tmp) fputs($fp, $tmp);
 fclose($fp); 
?><head>
<title>ＥＲＲＯＲ！</title>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
</head>
<body bgcolor="#FFFFFF">
<font size=+1 color=#FF0000><b>ERROR: <?php echo mb_convert_encoding($error, "SJIS-win", "UTF-8"); ?></b></font>
</body>
</html>
<?php exit;
}

// その他のエラー
function Error2($error) {
?><head>
<title>ＥＲＲＯＲ！</title>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
</head>
<body bgcolor="#FFFFFF">
<font size=+1 color=#FF0000><b>ERROR: <?=$error?></b></font>
</body>
</html>
<?php exit;
}

function makeDir($path) {
 return is_dir($path) || mkdir($path, 0777, true);
}	