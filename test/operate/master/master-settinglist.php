<?php
if (isset($_GET['mode'])) {
    if ($_GET['mode'] === 'update-password') {
        require './operate/master/update-password.php';
        exit;
    }
    if ($_GET['mode'] === 'system-settings') {
        require './operate/master/system-settings.php';
        exit;
    }
    if ($_GET['mode'] === 'auth-settings') {
        require './operate/master/auth-settings.php';
        exit;
    }
    if ($_GET['mode'] === 'auth-logs') {
        require './operate/master/auth-logs.php';
        exit;
    }
    if ($_GET['mode'] === 'migration-list') {
        require './operate/master/migration-list.php';
        exit;
    }

    // migration-list
    if ($_GET['mode'] === 'migration3to4') {
        require './operate/master/migration3to4.php';
        exit;
    }
    if ($_GET['mode'] === 'migration2to4') {
        require './operate/master/migration2to4.php';
        exit;
    }
    if ($_GET['mode'] === 'migration-post-log') {
        require './operate/master/migration-post-log.php';
        exit;
    }
    if ($_GET['mode'] === 'migration-error-log') {
        require './operate/master/migration-error-log.php';
        exit;
    }
    if ($_GET['mode'] === 'migration-auth-log') {
        require './operate/master/migration-auth-log.php';
        exit;
    }
}
?><!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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
      <div class="list-group d-flex align-items-start">
        <form action="?mode=update-password" method="POST" class="list-group-item list-group-item-action">
          <input type="hidden" name="code" value="<?= htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8'); ?>">
          <button class="btn stretched-link">本パスワード更新</button>
        </form>
        <form action="?mode=system-settings" method="POST" class="list-group-item list-group-item-action">
          <input type="hidden" name="code" value="<?= htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8'); ?>">
          <button class="btn stretched-link">システム設定</button>
        </form>
        <form action="?mode=auth-settings" method="POST" class="list-group-item list-group-item-action">
          <input type="hidden" name="code" value="<?= htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8'); ?>">
          <button class="btn stretched-link">認証設定</button>
        </form>
        <form action="?mode=auth-logs" method="POST" class="list-group-item list-group-item-action">
          <input type="hidden" name="code" value="<?= htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8'); ?>">
          <button class="btn stretched-link">認証ログ閲覧</button>
        </form>
        <form action="?mode=migration-list" method="POST" class="list-group-item list-group-item-action">
          <input type="hidden" name="code" value="<?= htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8'); ?>">
          <button class="btn stretched-link">システム移行</button>
        </form>
      </div>
    </main>
  </div>
</body>
</html>