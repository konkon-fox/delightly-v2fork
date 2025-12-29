<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);
header('Content-Type: text/html; charset=UTF-8');

include './utils/safe-file-get-contents.php';

// パラメータ取得
$bbs = basename(trim($_POST['bbs'] ?? $_GET['bbs'] ?? ''));
$safeBbs = htmlspecialchars($bbs, ENT_QUOTES, 'UTF-8');
$mode = basename(trim($_GET['mode'] ?? ''));
$safeMode = htmlspecialchars($mode, ENT_QUOTES, 'UTF-8');
$threadKey = basename(trim($_GET['key'] ?? ''));
$safeThreadKey = htmlspecialchars($threadKey, ENT_QUOTES, 'UTF-8');

if (!$bbs || !$_POST['password']) {
    ?><!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content>
<meta name="author" content>
<title>管理ページ</title>
<link rel="stylesheet" href="/static/a.css">
</head>
<body>
<form class="form-signin" method="post" action="?bbs=<?= $safeBbs; ?>&mode=<?= $safeMode; ?>&key=<?= $safeThreadKey; ?>">
<h1 class="font-weight-normal mb-3">ログイン</h1>
<input name="bbs" class="form-control" placeholder="ディレクトリ名" value="<?= $safeBbs; ?>" required="">
<input type="password" name="password" class="form-control" placeholder="パスワード" value="" required="">
<p class="mb-3"></p>
<input name="login_be_normal_user" class="btn btn-primary btn-block" type="submit" value="ログイン">
</form>
</body>
</html>
<?php
    exit;
}

// パスワードハッシュ取得
$PATH = '../' . $bbs . '/';
$passfile = $PATH . 'passfile.cgi';
$admin = safe_file_get_contents($passfile);
if ($admin === false) {
    Finish('<b>パスワードファイルの取得に失敗しました。</b>');
}
if (!isset($_POST['password'])) {
    $_POST['password'] = '';
}
// パスワード照合
if (!password_verify($_POST['password'], $admin)) {
    Finish('<b>パスワードが違います。</b>');
}

// 設定ファイルを読む
$setfile = $PATH . 'setting.json';
$settxt = $PATH . 'SETTING.TXT';
$SET = '';
$setContent = safe_file_get_contents($setfile);
if ($setContent === false) {
    Finish('<b>設定ファイルがありません</b>');
}
$SETTING = json_decode($setContent, true);

// 設定の一覧ページ
require './operate/settinglist.php';

function Finish($value)
{
    ?><!DOCTYPE HTML>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <link href="/static/a.css" rel="stylesheet" type="text/css">
</head>
<body>
  <?=$value;?>
</body>
</html>
<?php exit;
}
