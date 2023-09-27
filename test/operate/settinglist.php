<?php
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
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=boardsetting"><button class="link-style-btn">掲示板設定</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=header"><button class="link-style-btn">ヘッダー・告知欄</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=authorization"><button class="link-style-btn">承認済ユーザ</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=cap"><button class="link-style-btn">キャップ</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=log"><button class="link-style-btn">投稿ログ閲覧</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=control"><button class="link-style-btn">スレッド・レス管理</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=tl"><button class="link-style-btn">タイムライン管理</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=error"><button class="link-style-btn">エラーログ</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
</div>
</body>
</html>
<?php exit;
}elseif ($_GET['mode'] == "boardsetting") {
?><!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content>
<meta name="author" content>
<title>管理ページ</title>
<link rel="stylesheet" href="/static/a.css">
<style>
button.link-style-btn{
  cursor: pointer;
  border: none;
  background: none;
  color: #0033cc;
}
button.link-style-btn:hover{
  text-decoration: underline;
  color: #002080;
}
</style>
</head>
<body>
<div id="lists">
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=security"><button class="link-style-btn">セキュリティ</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=main"><button class="link-style-btn">基本設定</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=view"><button class="link-style-btn">表示設定</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=id"><button class="link-style-btn">ID・発信元等表示</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=kisei"><button class="link-style-btn">規制設定</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=kisei2"><button class="link-style-btn">投稿規制</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
<div class="list"><form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=kisei3"><button class="link-style-btn">スレッド作成規制</button><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"></form></div>
</div>
</body>
</html>
<?php exit;
}elseif ($_GET['mode'] == "header") {
 require './operate/header.php';
}elseif ($_GET['mode'] == "authorization") {
 require './operate/authorization.php';
}elseif ($_GET['mode'] == "cap") {
 require './operate/cap.php';
}elseif ($_GET['mode'] == "log") {
 require './operate/log.php';
}elseif ($_GET['mode'] == "control") {
 require './operate/control.php';
}elseif ($_GET['mode'] == "tl") {
 require './operate/tl.php';
}elseif ($_GET['mode'] == "security") {
 require './operate/security.php';
}elseif ($_GET['mode'] == "view") {
 require './operate/view.php';
}elseif ($_GET['mode'] == "main") {
 require './operate/main.php';
}elseif ($_GET['mode'] == "kako") {
 require './operate/kako.php';
}elseif ($_GET['mode'] == "kisei") {
 require './operate/kisei.php';
}elseif ($_GET['mode'] == "id") {
 require './operate/id.php';
}elseif ($_GET['mode'] == "kisei2") {
 require './operate/kisei2.php';
}elseif ($_GET['mode'] == "kisei3") {
 require './operate/kisei3.php';
}elseif ($_GET['mode'] == "error") {
 require './operate/error.php';
}