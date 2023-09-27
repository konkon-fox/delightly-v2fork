<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);
header('Content-Type: text/html; charset=UTF-8');
if (!isset($_REQUEST['bbs'])) $_REQUEST['bbs'] = '';
if (!isset($_REQUEST['mode'])) $_REQUEST['mode'] = '';
if (!isset($_REQUEST['key'])) $_REQUEST['key'] = '';
$PATH = "../".$_REQUEST['bbs']."/";
$passfile = $PATH."passfile.cgi";
$admin = @file_get_contents($passfile);
if (!isset($_POST['password'])) $_POST['password'] = '';
if (!$_REQUEST['bbs'] || !$_POST['password']) {
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
<form class="form-signin" method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=<?=$_REQUEST['mode']?>&key=<?=$_REQUEST['key']?>">
<h1 class="font-weight-normal mb-3">ログイン</h1>
<input name="bbs" class="form-control" placeholder="ディレクトリ名" value="<?=$_REQUEST['bbs']?>" required="">
<input type="password" name="password" class="form-control" placeholder="パスワード" value="<?=$_POST['password']?>" required="">
<p class="mb-3"></p>
<input name="login_be_normal_user" class="btn btn-primary btn-block" type="submit" value="ログイン">
</form>
</body>
</html>
<?php
  exit;
}elseif (!password_verify($_POST['password'], $admin)) {
 Finish('<b>パスワードが違います</b><div class="back"><a href="'.$_SERVER['HTTP_REFERER'].'">← 戻る</a></div>');
}

// 設定ファイルを読む
$setfile = $PATH."setting.json";
$settxt = $PATH."SETTING.TXT";
$SET = '';
if (is_file($setfile)) {
 $SETTING = json_decode(file_get_contents($setfile), true);
}else Finish('<b>設定ファイルがありません</b><div class="back"><a href="'.$_SERVER['HTTP_REFERER'].'">← 戻る</a></div>');

// 設定の一覧ページ
require './operate/settinglist.php';

function Finish($value) {
?><!DOCTYPE HTML>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <link href="/static/a.css" rel="stylesheet" type="text/css">
</head>
<body>
  <?=$value?>
</body>
</html>
<?php exit; }