<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['edit'] == "yes") {
 if (!isset($_POST['Samba24'])) $_POST['Samba24'] = '';
 if (!isset($_POST['timecheck'])) $_POST['timecheck'] = '';
 if (!isset($_POST['threadcheck'])) $_POST['threadcheck'] = '';
 if (!isset($_POST['newthread_check'])) $_POST['newthread_check'] = '';
 if (!isset($_POST['authorized_denypass'])) $_POST['authorized_denypass'] = '';
 if (!isset($_POST['change_sakujyo'])) $_POST['change_sakujyo'] = '';
 if (!isset($_POST['NANASHI_CHECK'])) $_POST['NANASHI_CHECK'] = '';
 if (!isset($_POST['BBS_PROXY_CHECK'])) $_POST['BBS_PROXY_CHECK'] = '';
 if (!isset($_POST['JAPANESE_CHECK'])) $_POST['JAPANESE_CHECK'] = '';
 if (!isset($_POST['NOPIC'])) $_POST['NOPIC'] = '';
 if (!isset($_POST['DISABLE_LINK'])) $_POST['DISABLE_LINK'] = '';
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
<title>規制・制限設定</title>
<link rel="stylesheet" href="/static/a.css">
</head>
<body>
<div class="main">
<form method="post" action="?bbs=<?=$_REQUEST['bbs']?>&mode=kisei">
<input type="hidden" name="password" value="<?=$_REQUEST['password']?>">
<input type="hidden" name="edit" value="yes">
<div class="back"><a href="./admin.php">← 管理ページへ戻る</a></div>
<div><b>UNICODE</b>(絵文字等)</div>
<div><input type="radio" name="BBS_UNICODE" value="deny" <?php if ($SETTING['BBS_UNICODE']=="deny") echo "checked"; ?>>禁止<br><input type="radio" name="BBS_UNICODE" value="change" <?php if ($SETTING['BBS_UNICODE']=="change") echo "checked"; ?>>？に変換<br><input type="radio" name="BBS_UNICODE" value="pass" <?php if ($SETTING['BBS_UNICODE']=="pass") echo "checked"; ?>>タイトルのみ？に変換<br><input type="radio" name="BBS_UNICODE" value="checked" <?php if ($SETTING['BBS_UNICODE']=="checked") echo "checked"; ?>>許可</div>
<div><b>短時間連続投稿制限</b></div>
<div><input type="checkbox" value="checked" name="Samba24"<?php if ($SETTING['Samba24']=="checked") echo " checked"; ?>>適用する</div>
<div><input type="text" value="<?=$SETTING['BBS_SAMBA24']?>" name="BBS_SAMBA24">秒</div>
<div><b>各スレッド内でのレス間隔制限秒数</b><small class="notice mt5">(他者の投稿を含む)</small></div>
<div><input type="text" value="<?=$SETTING['timeinterval']?>" name="timeinterval"></div>
<div><b>連続投稿規制</b></div>
<div><input type="checkbox" value="checked" name="timecheck"<?php if ($SETTING['timecheck']=="checked") echo " checked"; ?>>適用する</div>
<div><input type="text" name="timelimit" value="<?=$SETTING['timelimit']?>">秒以内に<input type="text" name="timecount" value="<?=$SETTING['timecount']?>">回中<input type="text" name="timeclose" value="<?=$SETTING['timeclose']?>">回以上で規制</div>
<div><b>各スレッド内での連続投稿規制</b></div>
<div><input type="checkbox" value="checked" name="threadcheck"<?php if ($SETTING['threadcheck']=="checked") echo " checked"; ?>>適用する</div>
<div><input type="text" name="threadlimit" value="<?=$SETTING['threadlimit']?>">秒以内に<input type="text" name="threadcount" value="<?=$SETTING['threadcount']?>">回中<input type="text" name="timecover" value="<?=$SETTING['timecover']?>">回以上で規制</div>
<div><b>投稿上限行数</b><small class="notice mt5">(承認済ユーザーは値の3倍値・未承認ユーザーはこの値の2倍値を適用)</small></div>
<div><input type="text" name="BBS_LINE_NUMBER" value="<?=$SETTING['BBS_LINE_NUMBER']?>"></div>
<div><b>スレッドタイトル上限</b><small class="notice mt5">(承認済ユーザーは値の3倍値を適用)</small></div>
<div><input type="text" name="BBS_SUBJECT_COUNT" value="<?=$SETTING['BBS_SUBJECT_COUNT']?>"></div>
<div><b>名前上限</b><small class="notice mt5">(承認済ユーザーは値の3倍値を適用)</small></div>
<div><input type="text" name="BBS_NAME_COUNT" value="<?=$SETTING['BBS_NAME_COUNT']?>"></div>
<div><b>メール上限</b><small class="notice mt5">(承認済ユーザーは値の3倍値を適用)</small></div>
<div><input type="text" name="BBS_MAIL_COUNT" value="<?=$SETTING['BBS_MAIL_COUNT']?>"></div>
<div><b>本文上限</b><small class="notice mt5">(承認済ユーザーは値の3倍値を適用)</small></div>
<div><input type="text" name="BBS_MESSAGE_COUNT" value="<?=$SETTING['BBS_MESSAGE_COUNT']?>"></div>
<div><b>スレッド作成順番待ち</b></div>
<div><input type="checkbox" value="checked" name="newthread_check"<?php if ($SETTING['newthread_check']=="checked") echo " checked"; ?>>適用する</div>
<div><input type="text" name="JUNBAN_LIMIT" value="<?=$SETTING['JUNBAN_LIMIT']?>">秒以内に<input type="text" name="THREAD_JUNBAN" value="<?=$SETTING['THREAD_JUNBAN']?>">個</div>
<div><b>スレッド作成間隔制限秒数</b><small class="notice mt5">(他者のスレ立てを含む)</small></div>
<div><input type="text" value="<?=$SETTING['THREAD_INTERVAL']?>" name="THREAD_INTERVAL">秒</div>
<div><b>承認済ユーザーを個別規制の対象から除外</b></div>
<div><input type="checkbox" value="checked" name="authorized_denypass"<?php if ($SETTING['authorized_denypass']=="checked") echo " checked"; ?>>する</div>
<div><b>日本国外の回線からの投稿を禁止</b><small class="notice mt5">(未承認ユーザーのみ)</small></div>
<div><input type="radio" name="BBS_FOREIGN_PASS" value="" <?php if ($SETTING['BBS_FOREIGN_PASS']!="on") echo "checked"; ?>>する<input type="radio" name="BBS_FOREIGN_PASS" value="on" <?php if ($SETTING['BBS_FOREIGN_PASS']=="on") echo "checked"; ?>>しない<br></div>
<div><b>Proxy規制</b><small class="notice mt5">(未承認ユーザーのみ)</small></div>
<div><input type="radio" name="BBS_BBX_PASS" value="" <?php if ($SETTING['BBS_BBX_PASS']!="on") echo "checked"; ?>>する<input type="radio" name="BBS_BBX_PASS" value="on" <?php if ($SETTING['BBS_BBX_PASS']=="on") echo "checked"; ?>>しない<br></div>
<div><b><small>名前欄の「管理」「削除」「sakujyo」を「"管理"」等へ置換（なりすまし防止）</small></b></div>
<div><input type="checkbox" name="change_sakujyo" value="checked"<?php if ($SETTING['change_sakujyo']=="checked") echo " checked"; ?>>する</div>
<div><b>投稿時の名前入力を必須</b></div>
<div><input type="checkbox" name="NANASHI_CHECK" value="checked"<?php if ($SETTING['NANASHI_CHECK']=="checked") echo " checked"; ?>>する</div>
<div><b>JPドメイン以外のホストからの投稿を禁止</b><small class="notice mt5">(未承認ユーザーのみ)</small></div>
<div><input type="checkbox" value="checked" name="BBS_PROXY_CHECK"<?php if ($SETTING['BBS_PROXY_CHECK']=="checked") echo " checked"; ?>>する</div>
<div><b>日本語を含まない投稿を禁止</b><small class="notice mt5">(未承認ユーザーのみ)</small></div>
<div><input type="checkbox" value="checked" name="JAPANESE_CHECK"<?php if ($SETTING['JAPANESE_CHECK']=="checked") echo " checked"; ?>>する</div>
<div><b>画像の投稿を禁止</b><small class="notice mt5">(未承認ユーザーのみ)</small></div>
<div><input type="checkbox" value="checked" name="NOPIC"<?php if ($SETTING['NOPIC']=="checked") echo " checked"; ?>>する</div>
<div bgcolor="#999999"><div><b>リンクの投稿を禁止</b><small class="notice mt5">(未承認ユーザーのみ)</small></div>
<div><input type="checkbox" value="checked" name="DISABLE_LINK"<?php if ($SETTING['DISABLE_LINK']=="checked") echo " checked"; ?>>する</div>
<hr><div class="contents"><input type="submit" name="Submit" class="btn btn-primary btn-block" value="適用"></div>
</form>
</div>
</body>
</html>
<?php exit;