<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['edit'] == "yes") {
 file_put_contents($PATH."head.txt", $_POST['head']);
 file_put_contents($PATH."kokuti.txt", $_POST['head2']);
}
$text = implode('', file($PATH."head.txt"));
$text2 = implode('', file($PATH."kokuti.txt"));
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
<div><b>ヘッダー</b></div>
<div><textarea style="font-size:9pt" rows="10" cols="70" name="head" wrap="OFF"><?=$text?></textarea></div>
<div><b>告知欄</b></div>
<div><textarea style="font-size:9pt" rows="10" cols="70" name="head2" wrap="OFF"><?=$text2?></textarea></div>
<hr><div class="contents"><input type="submit" name="Submit" class="btn btn-primary btn-block" value="適用"></div>
</form>
</div>
</body>
</html>
<?php exit;