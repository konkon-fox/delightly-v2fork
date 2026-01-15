<?php
$MAX_LENGTH = 100000;
$overTheThreadFile = $PATH . 'over-the-thread.cgi';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['edit'] === 'yes') {
    mb_substitute_character('entity');

    $newHeadText = $_POST['head'] ?? '';
    $newKokutiText = $_POST['head2'] ?? '';
    $newOverTheThreadText = $_POST['over-the-thread'] ?? '';

    if (mb_strlen($newHeadText) > $MAX_LENGTH) {
        exit('ヘッダーの内容が長過ぎます。');
    }
    if (mb_strlen($newKokutiText) > $MAX_LENGTH) {
        exit('告知欄の内容が長過ぎます。');
    }
    if (mb_strlen($newOverTheThreadText) > $MAX_LENGTH) {
        exit('カスタム本文の内容が長過ぎます。');
    }

    $newHeadText = mb_convert_encoding($newHeadText, 'SJIS-win', 'UTF-8');
    file_put_contents($PATH . 'head.txt', $newHeadText);

    file_put_contents($PATH . 'kokuti.txt', $newKokutiText);

    file_put_contents($overTheThreadFile, $newOverTheThreadText);
}

$headText = safe_file_get_contents($PATH . 'head.txt');
$kokutiText = safe_file_get_contents($PATH . 'kokuti.txt');
if (!is_file($overTheThreadFile)) {
    $overTheThreadText = '';
} else {
    $overTheThreadText = safe_file_get_contents($overTheThreadFile);
}
if ($headText === false || $kokutiText === false || $overTheThreadText === false) {
    exit('ファイルの取得に失敗しました。');
}

$headText = mb_convert_encoding($headText, 'UTF-8', 'SJIS-win');

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

<h2>ヘッダー・告知欄</h2>
<p>
  htmlタグが利用可能です。
</p>
<div><b>ヘッダー</b></div>
<div><textarea style="font-size:9pt" rows="10" cols="70" name="head" wrap="OFF"><?= $headText; ?></textarea></div>
<div><b>告知欄</b></div>
<div><textarea style="font-size:9pt" rows="10" cols="70" name="head2" wrap="OFF"><?= $kokutiText; ?></textarea></div>

<h2>レス上限時のカスタム本文</h2>
<p>
  {$br}で区切ると複数のカスタム本文をランダムに表示します。
</p>
<div><b>カスタム本文</b></div>
<div><textarea style="font-size:9pt" rows="10" cols="70" name="over-the-thread" wrap="OFF"><?= $overTheThreadText; ?></textarea></div>

<hr><div class="contents"><input type="submit" name="Submit" class="btn btn-primary btn-block" value="適用"></div>
</form>
</div>
</body>
</html>
