<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['edit'] == "yes") {
 if (!isset($_POST['ip_geolocation'])) $_POST['ip_geolocation'] = '';
 if (!isset($_POST['use_capture'])) $_POST['use_capture'] = '';
 if (!isset($_POST['capture_authorize'])) $_POST['capture_authorize'] = '';
 if (!isset($_POST['use_account_lv'])) $_POST['use_account_lv'] = '';
 if (!isset($_POST['authorized_denypass'])) $_POST['authorized_denypass'] = '';
 if (!isset($_POST['cookie_enable'])) $_POST['cookie_enable'] = '';
 if (!isset($_POST['trip_enable'])) $_POST['trip_enable'] = '';
 if (!isset($_POST['trip_authorize'])) $_POST['trip_authorize'] = '';
 if (!isset($_POST['account_enable'])) $_POST['account_enable'] = '';
 if (!isset($_POST['auto_authorize'])) $_POST['auto_authorize'] = '';
 if (!isset($_POST['g_auto_authorize'])) $_POST['g_auto_authorize'] = '';
 if (!isset($_POST['DISABLE_ICON'])) $_POST['DISABLE_ICON'] = '';
 if (!isset($_POST['DISABLE_NAME'])) $_POST['DISABLE_NAME'] = '';
 if (!isset($_POST['DISABLE_TRIP'])) $_POST['DISABLE_TRIP'] = '';
 if (!isset($_POST['FORCE_DISP_TRIP'])) $_POST['FORCE_DISP_TRIP'] = '';
 if (!isset($_POST['NAME_ARR'])) $_POST['NAME_ARR'] = '';
 if (!isset($_POST['Create_Authentication_required'])) $_POST['Create_Authentication_required'] = '';
 if (!isset($_POST['Create_cap_only'])) $_POST['Create_cap_only'] = '';
 if (!isset($_POST['Authentication_required'])) $_POST['Authentication_required'] = '';
 if (!isset($_POST['cap_only'])) $_POST['cap_only'] = '';
 if (!isset($_POST['disable_kakolog'])) $_POST['disable_kakolog'] = '';
 if (!isset($_POST['thread_supervisor'])) $_POST['thread_supervisor'] = '';
 if (!isset($_POST['aa_check'])) $_POST['aa_check'] = '';
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
<form method="post" action="/test/admin.php?bbs=<?=$_REQUEST['bbs']?>&mode=main">
<input type="hidden" name="password" value="<?=$_REQUEST['password']?>">
<input type="hidden" name="edit" value="yes">
<div class="back"><a href="./admin.php">← 管理ページへ戻る</a></div>
<div><b>指定Lv(鍵発行からの経過日数)以上の場合自動承認</b><small class="notice mt5">無記入で無効化</small></div>
<div><input type="text" value="<?=$SETTING['auto_authorize_lv']?>" name="auto_authorize_lv">Lv以上</div>
<div><b>アイコンを強制非表示</b></div>
<div><input type="checkbox" value="checked" name="DISABLE_ICON"<?php if ($SETTING['DISABLE_ICON']=="checked") echo " checked"; ?>>する</div>
<div><b>名前を強制非表示</b><small class="notice mt5">(※名前欄無効の場合でも投稿ログ閲覧ページからトリップを確認できます)</small></div>
<div><input type="checkbox" value="checked" name="DISABLE_NAME"<?php if ($SETTING['DISABLE_NAME']=="checked") echo " checked"; ?>>する</div>
<div><b>トリップを強制非表示</b><small class="notice mt5">(※トリップ無効の場合でも投稿ログ閲覧ページからトリップを確認できます)</small></div>
<div><input type="checkbox" value="checked" name="DISABLE_TRIP"<?php if ($SETTING['DISABLE_TRIP']=="checked") echo " checked"; ?>>する</div>
<div><b>!hideによるトリップ非表示を無効化</b><small class="notice mt5">(トリップ強制表示)</small></div>
<div><input type="checkbox" value="checked" name="FORCE_DISP_TRIP"<?php if ($SETTING['FORCE_DISP_TRIP']=="checked") echo " checked"; ?>>する</div>
<div><b>名前欄に@転載禁止を自動的に追加</b><small class="notice mt5">転載を完全に防止するものではありません。</small></div>
<div><input type="checkbox" value="checked" name="NAME_ARR"<?php if ($SETTING['NAME_ARR']=="checked") echo " checked"; ?>>する</div>
<div><b>認証済ユーザーのみスレッド作成を許可</b></div>
<div><input type="checkbox" value="checked" name="Create_Authentication_required"<?php if ($SETTING['Create_Authentication_required']=="checked") echo " checked"; ?>>する</div>
<div><b>キャップユーザーのみスレッド作成を許可</b></div>
<div><input type="checkbox" value="checked" name="Create_cap_only"<?php if ($SETTING['create_cap_only']=="checked") echo " checked"; ?>>する</div>
<div><b>認証済ユーザーのみ投稿を許可</b></div>
<div><input type="checkbox" value="checked" name="Authentication_required"<?php if ($SETTING['Authentication_required']=="checked") echo " checked"; ?>>する</div>
<div><b>キャップユーザーのみ投稿を許可</b></div>
<div><input type="checkbox" value="checked" name="cap_only"<?php if ($SETTING['cap_only']=="checked") echo " checked"; ?>>する</div>
<div><b>2ch専用ブラウザ</b></div>
<div><input type="radio" name="2ch_dedicate_browsers" value="disable" <?php if ($SETTING['2ch_dedicate_browsers']=="disable") echo "checked"; ?>>無効<input type="radio" name="2ch_dedicate_browsers" value="" <?php if ($SETTING['2ch_dedicate_browsers']=="") echo "checked"; ?>>閲覧のみ有効<input type="radio" name="2ch_dedicate_browsers" value="enable" <?php if ($SETTING['2ch_dedicate_browsers']=="enable") echo "checked"; ?>>閲覧・投稿共に有効</div>
<div><b>過去ログを保持</b></div>
<div><input type="checkbox" value="checked" name="disable_kakolog"<?php if ($SETTING['disable_kakolog']=="checked") echo " checked"; ?>>しない</div>
<div><b>スレッド最大保持数</b></div>
<div><input type="text" value="<?=$SETTING['BBS_THREADS_LIMIT']?>" name="BBS_THREADS_LIMIT"></div>
<div><b>タイムライン最大保持数</b></div>
<div><input type="text" value="<?=$SETTING['LTL_LIMIT']?>" name="LTL_LIMIT"></div>
<div><b>各スレッドのレス数上限</b></div>
<div><input type="text" value="<?=$SETTING['MAX_RES']?>" name="MAX_RES"></div>
<div><b>強制sageまでの秒数</b><small class="notice mt5">無記入で無効化</small></div>
<div><input type="text" value="" name="BBS_FORCE_SAGE"></div>
<div><b>スレッド作成主機能</b></div>
<div><input type="checkbox" value="checked" name="thread_supervisor"<?php if ($SETTING['thread_supervisor']=="checked") echo " checked"; ?>>有効</div>
<div><b>AAを自動検出し、フォントを最適化</b></div>
<div><input type="checkbox" name="aa_check" value="checked"<?php if ($SETTING['aa_check']=="checked") echo " checked"; ?>>する</div>
<div><b>各スレッドでの設定変更コマンド類</b></div>
<div><input type="radio" name="commands" value="checked"<?php if ($SETTING['commands'] == "checked") echo " checked"; ?>>有効<input type="radio" name="commands" value=""<?php if (!$SETTING['commands']) echo " checked"; ?>>無効</div>
<div><b>投稿最大ログ保存件数</b><small class="notice mt5">無記入の場合は上限なし</small></div>
<div><input type="text" value="<?=$SETTING['LOG_LIMIT']?>" name="LOG_LIMIT"></div>
<hr><div class="contents"><input type="submit" name="Submit" class="btn btn-primary btn-block" value="適用"></div>
</form>
</div>
</body>
</html>
<?php exit;