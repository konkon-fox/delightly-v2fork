<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['edit'] == "yes") {
  $newHeadText = mb_convert_encoding($_POST['head'], 'SJIS-win', 'UTF-8');
  file_put_contents($PATH."head.txt", $newHeadText);
  $newKokutiText = $_POST['head2'];
  file_put_contents($PATH."kokuti.txt", $newKokutiText);
}
$headText = file_get_contents($PATH."head.txt");
$headText = mb_convert_encoding($headText, 'UTF-8', 'SJIS-win');
$kokutiText = file_get_contents($PATH."kokuti.txt");
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
<form method="post" action="/test/admin.php?bbs=<?=$_REQUEST['bbs']?>&mode=header">
<input type="hidden" name="password" value="<?=$_REQUEST['password']?>">
<input type="hidden" name="edit" value="yes">
<div class="back"><a href="./admin.php">← 管理ページへ戻る</a></div>
<p>
  htmlタグが利用可能です。
</p>
<div><b>ヘッダー</b></div>
<div><textarea style="font-size:9pt" rows="10" cols="70" name="head" wrap="OFF"><?=$headText?></textarea></div>
<div><b>告知欄</b></div>
<div><textarea style="font-size:9pt" rows="10" cols="70" name="head2" wrap="OFF"><?=$kokutiText?></textarea></div>
<hr><div class="contents"><input type="submit" name="Submit" class="btn btn-primary btn-block" value="適用"></div>
</form>
</div>
</body>
</html>
<?php exit;