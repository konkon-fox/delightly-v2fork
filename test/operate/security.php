<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 if (strlen($_POST['PASS']) > 0) file_put_contents($passfile, password_hash($_POST['PASS'], PASSWORD_DEFAULT));
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content>
<meta name="author" content>
<title>セキュリティ設定</title>
<link rel="stylesheet" href="/static/a.css">
</head>
<body>
<div class="main">
<form method="post" action="/test/admin.php?bbs=<?=$_REQUEST['bbs']?>&mode=security">
<input type="hidden" name="password" value="<?=$_REQUEST['password']?>">
<div class="back"><a href="./admin.php">← 管理ページへ戻る</a></div>
<div><b>パスワード</b><small class="notice mt5">空欄の場合は変更されません</small></div>
<div><input type="text" name="PASS"></div>
<hr><div class="contents"><input type="submit" name="Submit" class="btn btn-primary btn-block" value="適用"></div>
</form>
</div>
</body>
</html>
<?php exit;