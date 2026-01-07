<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 if (strlen($_POST['PASS']) > 0) file_put_contents($passfile, password_hash($_POST['PASS'], PASSWORD_DEFAULT));
}
$bbs = basename($_REQUEST['bbs']);
$safeBbs = htmlspecialchars($bbs, ENT_QUOTES, 'UTF-8');
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
<form action="?bbs=<?= $safeBbs; ?>" method="post" style="margin-bottom: 4px;">
  <input type="hidden" name="password" value="<?=htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');?>">
  <button type="submit">← 管理ページへ戻る</button>
</form>
<form action="?bbs=<?= $safeBbs; ?>&mode=boardsetting" method="post" style="margin-bottom: 16px;">
  <input type="hidden" name="password" value="<?=htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');?>">
  <button type="submit">← 掲示板設定へ戻る</button>
</form>
<form method="post" action="/test/admin.php?bbs=<?=$_REQUEST['bbs']?>&mode=security">
<input type="hidden" name="password" value="<?=$_REQUEST['password']?>">
<div><b>パスワード</b><small class="notice mt5">空欄の場合は変更されません</small></div>
<div><input type="text" name="PASS"></div>
<hr><div class="contents"><input type="submit" name="Submit" class="btn btn-primary btn-block" value="適用"></div>
</form>
</div>
</body>
</html>
<?php exit;