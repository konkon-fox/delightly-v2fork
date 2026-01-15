<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['edit'] === 'yes') {
    mb_substitute_character('entity');
    $newHeadText = mb_convert_encoding($_POST['head'], 'SJIS-win', 'UTF-8');
    file_put_contents($PATH . 'head.txt', $newHeadText);
    $newKokutiText = $_POST['head2'];
    file_put_contents($PATH . 'kokuti.txt', $newKokutiText);
}
$headText = file_get_contents($PATH . 'head.txt');
$headText = mb_convert_encoding($headText, 'UTF-8', 'SJIS-win');
$kokutiText = file_get_contents($PATH . 'kokuti.txt');
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
<title>ヘッダー・告知欄</title>
<link rel="stylesheet" href="/static/a.css">
</head>
<body>
<div class="main">
<form action="?bbs=<?= $safeBbs; ?>" method="post" style="margin-bottom: 16px;">
  <input type="hidden" name="password" value="<?=htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');?>">
  <button type="submit">← 管理ページへ戻る</button>
</form>
<form method="post" action="/test/admin.php?bbs=<?=$_REQUEST['bbs'];?>&mode=header">
<input type="hidden" name="password" value="<?=$_REQUEST['password'];?>">
<input type="hidden" name="edit" value="yes">
<p>
  htmlタグが利用可能です。
</p>
<div><b>ヘッダー</b></div>
<div><textarea style="font-size:9pt" rows="10" cols="70" name="head" wrap="OFF"><?=$headText;?></textarea></div>
<div><b>告知欄</b></div>
<div><textarea style="font-size:9pt" rows="10" cols="70" name="head2" wrap="OFF"><?=$kokutiText;?></textarea></div>
<hr><div class="contents"><input type="submit" name="Submit" class="btn btn-primary btn-block" value="適用"></div>
</form>
</div>
</body>
</html>
