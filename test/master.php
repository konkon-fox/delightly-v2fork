<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);

include './utils/safe-file-get-contents.php';

if (isset($_POST['code'])) {
    $file = 'createcode.cgi';
    $code = safe_file_get_contents($file);
    if ($code === false) {
        exit('<b>パスワードファイルの取得に失敗しました。</b>');
    }
    if ($_POST['code'] === $code) {
        //設定の一覧ページ
        require './operate/master-settinglist.php';
        exit;
    } else {
        exit('<b>パスワードが違います。</b>');
    }
}
?><!DOCTYPE HTML>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>システム総管理ページ</title>
	<link
		href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
		rel="stylesheet"
		integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM"
		crossorigin="anonymous"
	/>
</head>
<body>
  <div class="container">
    <header>
      <h1>システム総管理ページ</h1>
    </header>
    <main>
      <p>
        掲示板作成コードを記入してください。
      </p>
      <form action="" method="POST" class="d-flex flex-column align-items-start row-gap-1">
        <input type="password" name="code" class="form-control" required>
        <button type="submit" class="btn btn-primary">ログイン</button>
      </form>
    </main>
  </div>
</body>
</html>