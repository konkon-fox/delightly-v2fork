<?php
$file = "../".$_REQUEST['bbs']."/index.json";
// TLが存在しない場合
if (!is_file($file)) Finish('<b>タイムラインがありません</b>');
// スレッド取得
$LOG = json_decode(file_get_contents($file), true);
if ($_POST['del']) {
	if (!$_POST['kakunin']) {
			for ($i = 0; $i < count($LOG); $i++) {
    			 if ($_POST[$i] == "checked") {
				 $LOG[$i] = ["name"=>'',
				 	  "mail"=>'',
 					  "date"=>'',
 					  "id"=>'',
 					  "comment"=>$SETTING['DELETED_TEXT'],
 					 ];
			 }
			}
			file_put_contents($file, json_encode($LOG, JSON_UNESCAPED_UNICODE), LOCK_EX);
			$result = "実行しました";
	}else $result = "確認画面(削除されるレスにチェックが入っています。宜しければ「実行」をクリック)";
}
?><!DOCTYPE HTML>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<title>タイムライン管理</title>
	<link href="/static/a.css" rel="stylesheet" type="text/css">
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
<b><?=$result?></b>
<form class="form-basic" method="POST" accept-charset="UTF-8" action=""><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"><input type="hidden" name="bbs" value="<?=$_REQUEST['bbs']?>"><input type="hidden" name="del" value="true"><div class="contents"><button type="submit" class="btn btn-primary btn-block">実行</button></div>
<?php
$n = $i = 0;
foreach($LOG as $tmp) {
	$n++;
	if ($_POST['kakunin'] && $_POST[$i] == "checked") $d = ' checked';
	else $d = '';
	$tmp['name'] = str_replace(array('<b>', '</b>'), "", $tmp['name']);
	if ($tmp['title']) echo "スレッドタイトル:".$tmp['title'];
	if ($tmp['key']) echo "スレッド番号:".$tmp['key'];
	$name=$tmp['name'];$mail=$tmp['mail'];$dateid=$tmp['date']." ".$tmp['id'];$comment=$tmp['comment'];$key=$tmp['thread'];$title=$tmp['title'];
	echo "<dt>削除<input type=\"checkbox\" name=\"".$i."\" value=\"checked\"".$d."> ".$n."：".$name."[".$mail."]：".$dateid."</dt><dd>".$comment."</dd><hr>";
	$i++;
}
echo "<div>確認(削除が実行されるレスを確認できます。一括削除用)<input type=\"checkbox\" name=\"kakunin\" value=\"checked\"></div>";
exit('<div class="contents"><button type="submit" class="btn btn-primary btn-block">実行</button></div><div>一度操作を行うと復元できません</div></body></html>');