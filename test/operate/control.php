<?php
if (!$_REQUEST['key']) {
	?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html lang="ja">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<title>スレッド・レス管理</title>
		<link href="/static/a.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div id="container"><section>
			<h1 class="section-title">スレッド・レス管理</h1>
			<h3>スレッド番号を直接入力</h3>
			<form class="form-basic" method="POST" accept-charset="UTF-8" action="?bbs=<?=$_REQUEST['bbs']?>&mode=control">
			<input type="hidden" name="password" value="<?=$_REQUEST['password']?>">
				<div class="contents">
					<input type="text" class="form-control" name="key" value="" placeholder="スレッド番号">
					<p class="notice mt5">スレッド番号を入力してください。</p>
				</div>
				<div class="contents">
					<button type="submit" class="btn btn-primary btn-block">管理</button>
				</div>
			</form>
			<h3>スレッド一覧から探す</h3>
			<div id="threadlist" class="contents"><?php 
			$Threads = json_decode(file_get_contents("../".$_REQUEST['bbs']."/subject.json"), true);
			$PAGEFILE = array();
			if ($Threads) {
				foreach ($Threads as $thread) {
				?><div><form class="form-basic" method="POST" accept-charset="UTF-8" action="?bbs=<?=$_REQUEST['bbs']?>&mode=control">
				<div>スレッド番号:<a href="/#<?=$_REQUEST['bbs']?>/<?=$thread['thread']?>/"><?=$thread['thread']?></a></div>
				<div>タイトル:<?=$thread['title']?></div>
				<div>レス数:<?=$thread['number']?></div>
				<div>作成時刻:<?php echo date("Y-m-d H:i:s", $thread['thread']); ?></div>
				<div>最終更新時刻:<?php echo date("Y-m-d H:i:s", $thread['date']); ?></div>
				<button type="submit" class="btn btn-primary btn-block">管理</button>
				<input type="hidden" name="key" value="<?=$thread['thread']?>">
				<input type="hidden" name="password" value="<?=$_REQUEST['password']?>">
				</form></div><hr>
				<?php
				}
			}
			?></div>
		</section></div>
		</body>
		</html><?php
	exit;
}
// dat
$THREADFILE = "../".$_REQUEST['bbs']."/thread/".substr($_REQUEST['key'], 0, 4)."/".$_REQUEST['key'].".dat";
$DATFILE = "../".$_REQUEST['bbs']."/dat/".$_REQUEST['key'].".dat";
$KFILE = "../".$_REQUEST['bbs']."/dat/".$_REQUEST['key']."_kisei.cgi";
$subjectfile = "../".$_REQUEST['bbs']."/subject.json";
// スレッドが存在しない場合
if (!is_file($THREADFILE)) Finish('<b>該当するスレッドがありません</b>');
// スレッド取得
$LOG = file($THREADFILE);
if ($_POST['del']) {
	if (!$_POST['kakunin']) {
		// スレッド削除
		if ($_POST['saku'] == "checked") {
			unlink($THREADFILE);
			if (is_file($DATFILE)) unlink($DATFILE);
			if (is_file($KFILE)) unlink($KFILE);
			//スレッド一覧から取り除く
			$Threads = json_decode(file_get_contents($subjectfile), true);
			$PAGEFILE = array();
			if ($Threads) {
				foreach ($Threads as $thread) {
					if ($thread['thread'] != $_REQUEST['key']) array_push($PAGEFILE,$thread);
				}
			}
			// 更新
			file_put_contents($subjectfile, json_encode($PAGEFILE, JSON_UNESCAPED_UNICODE), LOCK_EX);
			Finish('<b>スレッドを削除しました。</b>');
		}else {
			// レス削除・過去ログ化
			if ($_POST['kako'] == "checked") {
				if (is_file($DATFILE)) unlink($DATFILE);
				//スレッド一覧から取り除く
				$Threads = json_decode(file_get_contents($subjectfile), true);
				$PAGEFILE = array();
				if ($Threads) {
					foreach ($Threads as $thread) {
						if ($thread['thread'] != $_REQUEST['key']) array_push($PAGEFILE,$thread);
					}
				}
				// 更新
				file_put_contents($subjectfile, json_encode($PAGEFILE, JSON_UNESCAPED_UNICODE), LOCK_EX);
			}
			for ($i = 0; $i < count($LOG); $i++) {
    			 if ($_POST[$i] == "checked" || ($i + 1 >= $_POST['from'] && $i + 1 <= $_POST['to']) || ($_POST['itti'] && strpos($LOG[$i],$_POST['itti']) !== false)) $LOG[$i] = "<><><>".$SETTING['DELETED_TEXT']."<>\n";
			}
			$fp = '';
			foreach($LOG as $tmp) $fp .= $tmp;
			file_put_contents($THREADFILE, $fp, LOCK_EX);
			if (is_file($DATFILE)) file_put_contents($DATFILE, mb_convert_encoding($fp, "SJIS-win", "UTF-8"), LOCK_EX);
			$result = "実行しました";
		}
	}else $result = "確認画面(削除されるレスにチェックが入っています。宜しければ「実行」をクリック)";
}
?><!DOCTYPE HTML>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<title>スレッド・レス管理</title>
	<link href="/static/a.css" rel="stylesheet" type="text/css">
	<style>
	dd {
		color: black;
		margin-left: 40px;
		clear: both;
		font-size: 16px;
	}
	</style>
</head>
<body>
<b><?=$result?></b>
<div class="back"><a href='https://<?=$_SERVER['HTTP_HOST']?>/#<?=$_REQUEST['bbs']?>/<?=$_REQUEST['key']?>/'>スレッドを開く</a></div>
<form class="form-basic" method="POST" accept-charset="UTF-8" action=""><input type="hidden" name="password" value="<?=$_REQUEST['password']?>"><input type="hidden" name="bbs" value="<?=$_REQUEST['bbs']?>"><input type="hidden" name="key" value="<?=$_REQUEST['key']?>"><input type="hidden" name="del" value="true"><div class="contents"><button type="submit" class="btn btn-primary btn-block">実行</button></div>
<?php
$n = $i = 0;
echo "このスレッドを削除<input type=\"checkbox\" name=\"saku\" value=\"checked\"";
if ($_POST['kakunin'] && $_POST['saku'] == "checked") echo ' checked><br>';
else echo "><br>";
echo "このスレッドを強制過去ログ化<input type=\"checkbox\" name=\"kako\" value=\"checked\"";
if ($_POST['kakunin'] && $_POST['kako'] == "checked") echo ' checked>';
else echo ">";
echo '<div>レス一括削除(範囲指定)：<input type="text" name="from">-<input type="text" name="to"></div><div>レス一括削除(条件一致)：<input type="text" name="itti"></div>';
foreach($LOG as $tmp) {
	$n++;
	if ($_POST['kakunin'] && $_POST[$i] == "checked") $d = ' checked';
	else $d = '';
	list($name,$mail,$dateid,$comment,$subject) = explode("<>",$LOG[$i]);
	$name = str_replace(array('<b>', '</b>'), "", $name);
	if ($subject) echo "タイトル:".$subject;
	echo "<dt>レス削除<input type=\"checkbox\" name=\"".$i."\" value=\"checked\"".$d."> ".$n."：".$name."[".$mail."]：".$dateid."</dt><dd>".$comment."</dd>";
	$i++;
}
echo "<div>確認(削除が実行されるレスを確認できます。一括削除用)<input type=\"checkbox\" name=\"kakunin\" value=\"checked\"></div>";
exit('<div class="contents"><button type="submit" class="btn btn-primary btn-block">実行</button></div><div>一度操作を行うと復元できません</div></body></html>');