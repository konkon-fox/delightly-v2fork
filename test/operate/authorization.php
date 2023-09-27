<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['edit'] == "yes") {
 file_put_contents($PATH."authorize.cgi", $_POST['authorize']);
}
$authorize = implode('', file($PATH."authorize.cgi"));
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content>
<meta name="author" content>
<title>承認済ユーザ</title>
<link rel="stylesheet" href="/static/a.css">
</head>
<body>
<div class="main">
<form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=authorization">
<input type="hidden" name="password" value="<?=$_REQUEST['password']?>">
<input type="hidden" name="edit" value="yes">
<div class="back"><a href="<?=$_SERVER['HTTP_REFERER']?>">← 管理ページへ戻る</a></div>
<div><b>承認済ユーザ</b></div><div><small class="notice mt5">掲示板TOP画面に表示されます</small></div>
<div class="notice mt5">
・自動承認に関する設定は基本設定にあります<br>
・承認済ユーザーとしたい<b>ClientID</b>または<b>リモートホスト</b>または<b>IPアドレス</b>を記入してください<br>
・<b>ClientID</b>は完全一致のみ、<b>リモートホスト・IPアドレス</b>は部分一致も可(正規表現は使用不可)<br>
・一行につき一つとなります<br>
・行頭に半角の『#』を含んでいる行はコメント行として扱われます。<br>
・<b>ClientID</b>、<b>リモートホスト</b>、<b>IPアドレス</b>はそれぞれ別々の適用となります<br>
</div>
<div><textarea style="font-size:9pt" rows="10" cols="70" name="authorize" wrap="OFF"><?=$authorize?></textarea></div>
<hr><div class="contents"><input type="submit" name="Submit" class="btn btn-primary btn-block" value="適用"></div>
</form>
</div>
</body>
</html>
<?php exit;