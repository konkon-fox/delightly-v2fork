<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['edit'] == "yes") {
 foreach ($SETTING as $name => $value) {
  if (isset($_POST[$name])) $SETTING[$name] = $_POST[$name];
  $SET .= $name."=".$SETTING[$name]."\n";
 }
 file_put_contents($setfile, json_encode($SETTING, JSON_UNESCAPED_UNICODE), LOCK_EX);
 file_put_contents($settxt, mb_convert_encoding($SET, "SJIS-win", "UTF-8"), LOCK_EX);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content>
<meta name="author" content>
<title>表示設定</title>
<link rel="stylesheet" href="/static/a.css">
</head>
<body>
<div class="main">
<form method="post" action="/test/admin.php?bbs=<?=$_REQUEST['bbs']?>&mode=view">
<input type="hidden" name="password" value="<?=$_REQUEST['password']?>">
<input type="hidden" name="edit" value="yes">
<div class="back"><a href="./admin.php">← 管理ページへ戻る</a></div>
<div><b>掲示板タイトル</b></div>
<div><input type="text" name="BBS_TITLE" value="<?=$SETTING['BBS_TITLE']?>"></div>
<div><b>背景</b></div>
<div><input type="text" name="background" value="<?=$SETTING['background']?>"></div>
<div><b>名前未入力時の名前</b><small class="notice mt5">(省略可)</small></div>
<div><input type="text" name="BBS_NONAME_NAME" value="<?=$SETTING['BBS_NONAME_NAME']?>"></div>
<div><b>削除された投稿に表示される内容</b><small class="notice mt5">(省略可)</small></div>
<div><input type="text" name="DELETED_TEXT" value="<?=$SETTING['DELETED_TEXT']?>"></div>
<hr><div class="contents"><input type="submit" name="Submit" class="btn btn-primary btn-block" value="適用"></div>
</form>
</div>
</body>
</html>
<?php exit;