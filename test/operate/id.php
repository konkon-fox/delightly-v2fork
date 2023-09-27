<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['edit'] == "yes") {
 if (!isset($_POST['disp_accountid'])) $_POST['disp_accountid'] = '';
 if (!isset($_POST['disp_slipname'])) $_POST['disp_slipname'] = '';
 if (!isset($_POST['slip'])) $_POST['slip'] = '';
 if (!isset($_POST['createid'])) $_POST['createid'] = '';
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
<title>基本設定</title>
<link rel="stylesheet" href="/static/a.css">
</head>
<body>
<div class="main">
<form method="post" action="/test/admin.php?bbs=<?=$_REQUEST['bbs']?>&mode=id">
<input type="hidden" name="password" value="<?=$_REQUEST['password']?>">
<input type="hidden" name="edit" value="yes">
<div class="back"><a href="./admin.php">← 管理ページへ戻る</a></div>
<div><b>ID</b></div>
<div><input type="radio" name="id" value="checked" <?php if ($SETTING['id']=="checked") echo "checked"; ?>>IDを表示<br><input type="radio" name="id" value="" <?php if (!$SETTING['id']) echo "checked"; ?>>IDを表示しない<br><input type="radio" name="id" value="siberia" <?php if ($SETTING['id']=="siberia") echo "checked"; ?>>発信元IPアドレスを表示</div>
<div><b>IDをリセットする間隔</b></div>
<div><input type="radio" name="ID_RESET" value="day" <?php if ($SETTING['ID_RESET']=="day") echo "checked"; ?>>1日<input type="radio" name="ID_RESET" value="10days" <?php if ($SETTING['ID_RESET']=="10days") echo "checked"; ?>>10日<input type="radio" name="ID_RESET" value="10hours" <?php if ($SETTING['ID_RESET']=="10hours") echo "checked"; ?>>10時間<input type="radio" name="ID_RESET" value="month" <?php if ($SETTING['ID_RESET']=="month") echo "checked"; ?>>1ヶ月<input type="radio" name="ID_RESET" value="year" <?php if ($SETTING['ID_RESET']=="year") echo "checked"; ?>>1年<input type="radio" name="ID_RESET" value="hour" <?php if ($SETTING['ID_RESET']=="hour") echo "checked"; ?>>1時間<input type="radio" name="ID_RESET" value="10minutes" <?php if ($SETTING['ID_RESET']=="10minutes") echo "checked"; ?>>10分<input type="radio" name="ID_RESET" value="minute" <?php if ($SETTING['ID_RESET']=="minute") echo "checked"; ?>>60秒間隔<br></div>
<div><b>回線別ニックネーム(ﾜｯﾁｮｲ)を表示</b><small class="notice mt5">承認済ユーザーは非表示</small></div>
<div><input type="checkbox" name="disp_slipname" value="checked"<?php if ($SETTING['disp_slipname']=="checked") echo " checked"; ?>>する</div>
<div><b>新規表示(Lv0以下のみ)と回線別末尾を追加</b></div>
<div><input type="checkbox" name="slip" value="checked"<?php if ($SETTING['slip']=="checked") echo " checked"; ?>>する</div>
<div><b>発信元表示</b></div>
<div><input type="radio" name="fusianasan" value="name" <?php if ($SETTING['fusianasan']=="name") echo "checked"; ?>>リモートホストを表示<br><input type="radio" name="fusianasan" value="id" <?php if ($SETTING['fusianasan']=="id") echo "checked"; ?>>ClientIDを表示<br><input type="radio" name="fusianasan" value="" <?php if ($SETTING['fusianasan']=="") echo "checked"; ?>>表示しない</div>
<div><b>スレッドタイトルにIDを追加</b></div>
<div><input type="checkbox" name="createid" value="checked"<?php if ($SETTING['createid']=="checked") echo " checked"; ?>>する</div>
<div><b>地域名表示</b></div>
<div><input type="radio" name="BBS_JP_CHECK" value="checked" <?php if ($SETTING['BBS_JP_CHECK']=="checked") echo "checked"; ?>>する<br><input type="radio" name="BBS_JP_CHECK" value="" <?php if (!$SETTING['BBS_JP_CHECK']) echo "checked"; ?>>しない</div>
<hr><div class="contents"><input type="submit" name="Submit" class="btn btn-primary btn-block" value="適用"></div>
</form>
</div>
</body>
</html>
<?php exit;