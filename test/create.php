<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);
header('Content-Type: text/html; charset=UTF-8');
$file = "createcode.cgi";
$code = @file_get_contents($file);
?><!DOCTYPE HTML>
<html lang="ja">
<head>
<meta charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>新規掲示板作成</title>
<link href="/static/a.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="container">
<section>
<h1 class="section-title">新規掲示板作成</h1>
<form class="form-basic" method="POST" accept-charset="UTF-8" action="kanri2.php">
<h2 class="section-subtitle">ディレクトリ名</h2>
<div class="contents">
<input type="text" class="form-control" id="registerMailAddressCheck" name="directory">
<p class="notice mt5">掲示板のディレクトリ名(フォルダ名)を16文字以下（半角英数小文字）で記入してください。</p>
</div>
<h2 class="section-subtitle">管理パスワード</h2>
<div class="contents">
<input type="text" class="form-control" id="password" name="password">
<p class="notice mt5">掲示板の管理画面で使用するパスワードを入力してください。</p>
</div>
<?php if (strlen($code) > 0) { ?>
<h2 class="section-subtitle">作成コード</h2>
<div class="contents">
<input type="text" class="form-control" id="code" name="code">
<p class="notice mt5 mt20">掲示板を作成するためには作成コードを記入する必要があります</p>
</div>
<?php } ?>
<div class="contents">
<button type="submit" class="btn btn-primary btn-block">新規掲示板作成</button>
<p class="notice mt5 mt20">詳細な設定は掲示板作成後、管理メニューから行えます。</p>
</div>
</form>
</section>
</div>
</body>
</html>