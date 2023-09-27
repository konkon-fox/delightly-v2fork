<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);
header('Content-Type: text/html; charset=UTF-8');
$cap = false;
if (!isset($_REQUEST['bbs'])) $_REQUEST['bbs'] = '';
if (!isset($_REQUEST['mode'])) $_REQUEST['mode'] = '';
if (!isset($_REQUEST['key'])) $_REQUEST['key'] = '';
$PATH = "../".$_REQUEST['bbs']."/";
$passfile = $PATH."passfile.cgi";
$admin = @file_get_contents($passfile);
if (!isset($_POST['password'])) $_POST['password'] = '';
if (password_verify($_POST['password'], $admin)) $cap = true;
if (!$cap) {
 if (is_file($PATH."cap.cgi")) {
  $cap_str = file($PATH."cap.cgi");
  foreach ($cap_str as $tmp){
  $tmp = trim($tmp);
  if (!$tmp || strpos(substr($tmp, 0, 1), '#') !== false || strpos($tmp, '<>') === false) continue;
  list($name1,$pass1,$a1) = explode("<>", $tmp);
   if (($_POST['password'] == $pass1) && ($a1 == "saku" or $a1 == "sakud")) {
    $cap = true;
    $password = $pass1;
    if ($a1 == "sakud") $sakud = 1;
    break;
   }
  }
 }
}
if ($_POST['password'] && !$cap) $E = 'パスワードが無効です';
else $E = '';
if (!$_REQUEST['bbs'] || !$cap) {
?><!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content>
<meta name="author" content>
<title>削除ページ</title>
<link rel="stylesheet" href="/static/a.css">
</head>
<body>
<form class="form-signin" method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=<?=$_REQUEST['mode']?>&key=<?=$_REQUEST['key']?>">
<h1 class="font-weight-normal mb-3">ログイン</h1>
<b><?=$E?></b>
<input name="bbs" class="form-control" placeholder="ディレクトリ名" value="<?=$_REQUEST['bbs']?>" required="">
<input type="password" name="password" class="form-control" placeholder="パスワード" value="<?=$_POST['password']?>" required="">
<p class="mb-3"></p>
<input name="login_be_normal_user" class="btn btn-primary btn-block" type="submit" value="ログイン">
</form>
</body>
</html>
<?php
  exit;
}

// 設定ファイルを読む
$setfile = $PATH."setting.json";
$settxt = $PATH."SETTING.TXT";
$SET = '';
if (is_file($setfile)) {
 $SETTING = json_decode(file_get_contents($setfile), true);
}else Finish('<b>設定ファイルがありません</b><div class="back"><a href="'.$_SERVER['HTTP_REFERER'].'">← 戻る</a></div>');

// 設定一覧
if (!$_GET['mode']) {
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
<div id="lists">
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=authorization"><button class="link-style-btn">承認済ユーザ</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=log"><button class="link-style-btn">投稿ログ閲覧</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=control"><button class="link-style-btn">スレッド・レス管理</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=tl"><button class="link-style-btn">タイムライン管理</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=kisei"><button class="link-style-btn">規制設定</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=kisei2"><button class="link-style-btn">投稿規制</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=kisei3"><button class="link-style-btn">スレッド作成規制</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=error"><button class="link-style-btn">エラーログ</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
</div>
</body>
</html>
<?php exit;
}elseif ($_GET['mode'] == "authorization") {
 require './operate/authorization.php';
}elseif ($_GET['mode'] == "log") {
 require './operate/log.php';
}elseif ($_GET['mode'] == "control") {
 require './operate/control.php';
}elseif ($_GET['mode'] == "tl") {
 require './operate/tl.php';
}elseif ($_GET['mode'] == "error") {
 require './operate/error.php';
}elseif ($_GET['mode'] == "kisei") {
 require './operate/kisei.php';
}elseif ($_GET['mode'] == "kisei2") {
 require './operate/kisei2.php';
}elseif ($_GET['mode'] == "kisei3") {
 require './operate/kisei3.php';
}

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
<?php }