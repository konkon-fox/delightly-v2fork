<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content>
<meta name="author" content>
<title>エラーログ</title>
<link rel="stylesheet" href="/static/a.css">
<style>
dd {
	color: black;
	margin-left: 40px;
	clear: both;
	font-size: 16px;
}
</style>
</head>
<body>
<div class="main">
<div class="back"><a href="<?=$_SERVER['HTTP_REFERER']?>">← 管理ページへ戻る</a></div>
<div><b>エラーログ</b></div>
<?php
// ログ
$LOGFILE = "../".$_REQUEST['bbs']."/errors.cgi";
$LOG = array();
$n = 0;
// ログ取得
if (is_file($LOGFILE)) $LOG = file($LOGFILE);
else Finish('<b>エラーログファイルがありません</b><div class="back"><a href="'.$_SERVER['HTTP_REFERER'].'">← 戻る</a></div>');
foreach($LOG as $tmp) {
	$n++;
	$tmp = rtrim($tmp);
	list($error,$name,$mail,$dateid,$comment,$title,$key,$number,$HOST,$IP,$UA,$CH_UA,$ACCEPT,$accountid,$LV,$PORT,$CF_IPCOUNTRY,,$ken,$slip,) = explode("<>", $tmp);
	echo "<dt>".$n."[$d] ：".$name."[".$mail."]：".$dateid." 発信元:".$IP."<".$PORT."> HOST:".$HOST."<dd>".$comment."<hr>ClientID:".$accountid."<Lv".$LV."><br>User-Agent:".$UA."<br>Sec-CH-UA:".$CH_UA."<br>ACCEPT:".$ACCEPT."<br>IPCOUNTRY:".$CF_IPCOUNTRY." 認証時データ:".$ken." ".$slip."<br>URL:<a href='https://".$_SERVER['HTTP_HOST']."/?st=".$number."#".$_REQUEST['bbs']."/".$key."/' target='_new'>https://".$_SERVER['HTTP_HOST']."/?st=".$number."#".$_REQUEST['bbs']."/".$key."/</a><hr><b>".$error."</b></dd>\n";
}
?></div>
</body>
</html>