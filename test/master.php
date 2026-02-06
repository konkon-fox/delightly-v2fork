<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);

include './utils/safe-file-get-contents.php';

$error = '';

// パスワードチェック
if (isset($_POST['code'])) {
    $createcodefile = 'createcode.cgi';
    $masterPasswordfile = './operate/master-password.txt';
    if (is_file($createcodefile)) {
        $code = safe_file_get_contents($createcodefile);
        if ($code === false) {
            exit('<b>パスワードファイルの取得に失敗しました。</b>');
        }
        $isAuthed = $_POST['code'] === $code;
    } elseif (is_file($masterPasswordfile)) {
        $code = safe_file_get_contents($masterPasswordfile);
        if ($code === false) {
            exit('<b>パスワードファイルの取得に失敗しました。</b>');
        }
        $isAuthed = password_verify($_POST['code'], $code);
    } else {
        $error = 'パスワードファイルが存在しません。<br>/test/createcode.cgi に仮パスワードを作成してください。';
    }
    if (empty($error)) {
        if ($isAuthed) {
            //設定の一覧ページ
            require './operate/master/master-settinglist.php';
            exit;
        } else {
            $error = 'パスワードが違います。';
        }
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
      <?php
if (isset($_GET['update-password'])) {
    echo '<p class="alert alert-primary" role="alert">パスワードを更新しました。</p>';
}
if (!empty($error)) {
    echo "<div class=\"alert alert-warning\" role=\"alert\">{$error}</div>";
}
?>
      <p>
        掲示板作成コード（仮パスワード）か本パスワードを記入してください。
      </p>
      <form action="" method="POST" class="d-flex flex-column align-items-start row-gap-1">
        <input type="password" name="code" class="form-control" required>
        <button type="submit" class="btn btn-primary">ログイン</button>
      </form>
    </main>
  </div>
</body>
</html>