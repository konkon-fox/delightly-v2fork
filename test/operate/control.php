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
			<form class="form-basic" method="POST" accept-charset="UTF-8" action="?bbs=<?=$_REQUEST['bbs'];?>&mode=control">
			<input type="hidden" name="password" value="<?=$_REQUEST['password'];?>">
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
            $subjectHandle = fopen('../'.$_REQUEST['bbs'].'/subject.json', 'r');
    if ($subjectHandle !== false) {
        if (flock($subjectHandle, LOCK_SH)) {
            $Threads = json_decode(stream_get_contents($subjectHandle), true);
            flock($subjectHandle, LOCK_UN);
            $PAGEFILE = [];
            if ($Threads) {
                foreach ($Threads as $thread) {
                    ?><div><form class="form-basic" method="POST" accept-charset="UTF-8" action="?bbs=<?=$_REQUEST['bbs'];?>&mode=control">
								<div>スレッド番号:<a href="/#<?=$_REQUEST['bbs'];?>/<?=$thread['thread'];?>/"><?=$thread['thread'];?></a></div>
								<div>タイトル:<?=$thread['title'];?></div>
								<div>レス数:<?=$thread['number'];?></div>
								<div>作成時刻:<?php echo date('Y-m-d H:i:s', $thread['thread']); ?></div>
								<div>最終更新時刻:<?php echo date('Y-m-d H:i:s', $thread['date']); ?></div>
								<button type="submit" class="btn btn-primary btn-block">管理</button>
								<input type="hidden" name="key" value="<?=$thread['thread'];?>">
								<input type="hidden" name="password" value="<?=$_REQUEST['password'];?>">
								</form></div><hr>
								<?php
                }
            }
        }
        fclose($subjectHandle);
    }
    ?></div>
		</section></div>
		</body>
		</html><?php
    exit;
}

/**
 * 現行スレッド(subject.json)から該当スレッドを取り除く関数
 *
 * @param string $subjectfile subject.jsonへのパス
 *
 * @return array|false 成功時は取り除いたスレの情報配列、失敗時はfalse
 */
function removeFromCurrentSubjects($subjectfile)
{
    // 該当スレッドを取り除く
    $fp = fopen($subjectfile, 'r+');
    if ($fp === false) {
        return false;
    }
    if (!flock($fp, LOCK_EX)) {
        flock($fp, LOCK_UN);
        fclose($fp);
        return false;
    }
    $content = stream_get_contents($fp);
    if ($content === false) {
        flock($fp, LOCK_UN);
        fclose($fp);
        return false;
    }
    $Threads = json_decode($content, true);
    $PAGEFILE = [];
    $targetThread = false;
    if ($Threads) {
        foreach ($Threads as $thread) {
            if ((int)$thread['thread'] === (int)$_REQUEST['key']) {
                $targetThread = $thread;
            } else {
                array_push($PAGEFILE, $thread);
            }
        }
    }
    // 更新
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($PAGEFILE, JSON_UNESCAPED_UNICODE));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    return $targetThread;
}
//
include './utils/get-json-file.php';
$PATH = '../'.$_REQUEST['bbs'].'/';
$KAKOLOGLIST = $PATH.'kakolog-subject.txt';
$KAKOLOGLISTINDEX = $PATH.'kakolog-subject.idx';
$THREAD_STATES_PATH = $PATH.'threads-states';
$threadStatesFile = $THREAD_STATES_PATH.'/'.$_REQUEST['key'].'.json';
// dat
$THREADFILE = '../'.$_REQUEST['bbs'].'/thread/'.substr($_REQUEST['key'], 0, 4).'/'.$_REQUEST['key'].'.dat';
$DATFILE = '../'.$_REQUEST['bbs'].'/dat/'.$_REQUEST['key'].'.dat';
$KFILE = '../'.$_REQUEST['bbs'].'/dat/'.$_REQUEST['key'].'_kisei.cgi';
$subjectfile = '../'.$_REQUEST['bbs'].'/subject.json';
// スレッドが存在しない場合
if (!is_file($THREADFILE)) {
    Finish('<b>該当するスレッドがありません</b>');
}
// スレッド取得
$threadFileHandle = fopen($THREADFILE, 'r');
if ($threadFileHandle === false) {
    Finish('<b>スレッドファイルを開けませんでした。</b>');
}
if (!flock($threadFileHandle, LOCK_SH)) {
    flock($threadFileHandle, LOCK_UN);
    fclose($threadFileHandle);
    Finish('<b>スレッドファイルのロックに失敗しました。</b>');
}
$logContent = stream_get_contents($threadFileHandle);
if ($logContent === false) {
    flock($threadFileHandle, LOCK_UN);
    fclose($threadFileHandle);
    Finish('<b>レスの取得に失敗しました。</b>');
}
$LOG = explode("\n", $logContent);
fclose($threadFileHandle);
if ($_POST['del']) {
    if (!$_POST['kakunin']) {
        // スレッド削除
        if ($_POST['saku'] === 'checked') {
            unlink($THREADFILE);
            if (is_file($DATFILE)) {
                unlink($DATFILE);
            }
            if (is_file($KFILE)) {
                unlink($KFILE);
            }
            //スレッド一覧から取り除く
            removeFromCurrentSubjects($subjectfile);

            $result = 'スレの削除を実行しました';
        } else {
            // 過去ログ化
            if ($_POST['kako'] === 'checked') {
                //スレッド一覧から取り除く
                $targetThread = removeFromCurrentSubjects($subjectfile);
                if ($targetThread === false) {
                    Finish('<b>過去ログ化に失敗しました。</b>');
                }
                // 過去ログ送り用関数
                include './extend/archive-thread.php';
                archiveThread(
                    $SETTING,
                    $KAKOLOGLIST,
                    $KAKOLOGLISTINDEX,
                    $threadStatesFile,
                    $THREADFILE,
                    $DATFILE,
                    $targetThread['thread'],
                    $targetThread['title'],
                    $targetThread['number'],
                    $KFILE,
                );
            }

            // 以降レス削除

            // >>1保存
            $AUTHOR = $LOG[0];
            // 削除後の文字列
            $REPLACE_TEXT = '</b>'.$SETTING['DELETED_TEXT'].'<b><>'.str_repeat($SETTING['DELETED_TEXT'].'<>', 3);

            for ($i = 0, $LOG_COUNT = count($LOG) - 1; $i < $LOG_COUNT; ++$i) {
                if ($_POST[$i] === 'checked' // レス個別
                || ($i + 1 >= $_POST['from'] && $i + 1 <= $_POST['to']) // レス範囲
                || ($_POST['itti'] && strpos($LOG[$i], $_POST['itti']) !== false)) { // レス条件一致
                    $LOG[$i] = $REPLACE_TEXT;
                }
            }
            // >>1が削除対象だった場合、末尾にスレタイ付与
            if ($LOG[0] === $REPLACE_TEXT) {
                $LOG[0] .= explode('<>', $AUTHOR)[4];
            }

            $newLogContent = implode("\n", $LOG);
            file_put_contents($THREADFILE, $newLogContent, LOCK_EX);

            $prevChar = mb_substitute_character();
            mb_substitute_character('entity');
            if (is_file($DATFILE)) {
                file_put_contents($DATFILE, mb_convert_encoding($newLogContent, 'SJIS-win', 'UTF-8'), LOCK_EX);
            }
            mb_substitute_character($prevChar);

            $result = '実行しました';
        }
    } else {
        $result = '確認画面(削除されるレスにチェックが入っています。宜しければ「実行」をクリック)';
    }
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
<b><?=$result;?></b>
<?php
if (!is_file($THREADFILE)) {
    exit('<p>スレッドが存在しません。</p>');
}
?><div class="back"><a href='https://<?=$_SERVER['HTTP_HOST'];?>/#<?=$_REQUEST['bbs'];?>/<?=$_REQUEST['key'];?>/'>スレッドを開く</a></div>
<form class="form-basic" method="POST" accept-charset="UTF-8" action=""><input type="hidden" name="password" value="<?=$_REQUEST['password'];?>"><input type="hidden" name="bbs" value="<?=$_REQUEST['bbs'];?>"><input type="hidden" name="key" value="<?=$_REQUEST['key'];?>"><input type="hidden" name="del" value="true"><div class="contents"><button type="submit" class="btn btn-primary btn-block">実行</button></div>
このスレッドを削除
<input
  type="checkbox"
	name="saku"
	value="checked"
	id="delete-thread-checkbox"
<?php
if ($_POST['kakunin'] && $_POST['saku'] === 'checked') {
    echo ' checked';
}
?>
>
<script>
	document.getElementById('delete-thread-checkbox').addEventListener('click',confirmCheckbox);
	function confirmCheckbox(e){
		const isChecked = !e.target.checked;
		if(isChecked){
			return;
		}
		const confirmed = window.confirm('本当に「このスレッドを削除」にチェックを入れますか？');
		if(confirmed){
			return;
		}
		e.preventDefault();
	}
</script>
<br>
<?php
echo 'このスレッドを強制過去ログ化<input type="checkbox" name="kako" value="checked"';
if ($_POST['kakunin'] && $_POST['kako'] === 'checked') {
    echo ' checked>';
} else {
    echo '>';
}
echo '<div>レス一括削除(範囲指定)：<input type="text" name="from">-<input type="text" name="to"></div><div>レス一括削除(条件一致)：<input type="text" name="itti"></div>';
$n = $i = 0;
foreach ($LOG as $tmp) {
    if ($tmp === '') {
        continue;
    }
    $n++;
    if ($_POST['kakunin'] && $_POST[$i] === 'checked') {
        $d = ' checked';
    } else {
        $d = '';
    }
    list($name, $mail, $dateid, $comment, $subject) = explode('<>', $LOG[$i]);
    $name = str_replace(['<b>', '</b>'], '', $name);
    if ($subject) {
        echo 'タイトル:'.$subject;
    }
    echo '<dt>レス削除<input type="checkbox" name="'.$i.'" value="checked"'.$d.'> '.$n.'：'.$name.'['.$mail.']：'.$dateid.'</dt><dd>'.$comment.'</dd>';
    $i++;
}
echo '<div>確認(削除が実行されるレスを確認できます。一括削除用)<input type="checkbox" name="kakunin" value="checked"></div>';
exit('<div class="contents"><button type="submit" class="btn btn-primary btn-block">実行</button></div><div>一度操作を行うと復元できません</div></body></html>');
