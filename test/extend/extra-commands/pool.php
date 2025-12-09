<?php

/**
 * @param array $SETTING 板の設定
 * @param boolean $supervisor スレ主判定
 * @param boolean $admin 管理者判定(管理人or常時コマンド権限を持つCAP)
 * @param boolean $newthread スレ立て時判定
 * @param boolean $tlonly TL判定
 * @param string $datlog スレッド毎連続投稿規制用ファイルへのパス
 * @param string $THREADFILE 通常ブラウザ用datファイルへのパス
 * @param string $DATILE 専ブラ用datファイルへのパス
 * @param array $PAGEFILE subject.json用の連想配列
 * @param string $THREADS_STATES_FILE スレ状態ファイルへのパス
 * @param string $KAKOLOGLIST 過去ログリストへのパス
 * @param string $KAKOLOGLISTINDEX 過去ログリストインデックスへのパス
 * @param string $subject スレタイ
 * @param int $number レス数
 */
function applyPoolCommand(
    $SETTING,
    $supervisor,
    $admin,
    $newthread,
    $tlonly,
    $datlog,
    $THREADFILE,
    $DATFILE,
    &$PAGEFILE,
    $THREADS_STATES_FILE,
    $KAKOLOGLIST,
    $KAKOLOGLISTINDEX,
    $subject,
    $number,
) {
    if ($SETTING['commands'] !== 'checked') {
        return;
    }
    if ($newthread || $tlonly) {
        return;
    }
    if (!($supervisor || $admin)) {
        return;
    }
    if (strpos($_POST['name'], '!nocmd') !== false) {
        return;
    }
    if (strpos($_POST['comment'], '!pool') === false) {
        return;
    }
    // 現行スレッドから削除
    @unlink($THREADS_STATES_FILE);
    // 過去ログを保持しない場合
    if ($SETTING['disable_kakolog'] === 'checked') {
        @unlink($DATFILE);
        @unlink($THREADFILE);
    } else {
        // 過去ログリストへ追記
        $kakologListHandle = fopen($KAKOLOGLIST, 'a');
        if (flock($kakologListHandle, LOCK_SH)) {
            // 末尾位置取得
            fseek($kakologListHandle, 0, SEEK_END);
            $endOffset = ftell($kakologListHandle);
            // 追記
            $kakologLine = $_POST['thread'].".dat<>".$subject." (".$number.")\n";
            fwrite($kakologListHandle, mb_convert_encoding($kakologLine, "SJIS-win", "UTF-8"));
        }
        fclose($kakologListHandle);
        // 過去ログインデックスへ追記
        if (isset($endOffset)) {
            file_put_contents($KAKOLOGLISTINDEX, $endOffset."\n", FILE_APPEND | LOCK_EX);
        }
    }
    // datlog削除
    if (is_file($datlog)) {
        @unlink($datlog);
    }
    // subject.json用のデータ更新
    $PAGEFILE = array_filter($PAGEFILE, function ($thread) {
        return (int) $thread['thread'] !== (int) $_POST['thread'];
    });
    $PAGEFILE = array_values($PAGEFILE);
}

applyPoolCommand(
    $SETTING,
    $supervisor,
    $admin,
    $newthread,
    $tlonly,
    $PATH."dat/".$_POST['thread']."_kisei.cgi", // $datlog
    $THREADFILE,
    $DATFILE,
    $PAGEFILE,
    $THREADS_STATES_FILE,
    $KAKOLOGLIST,
    $KAKOLOGLISTINDEX,
    $subject,
    $number,
);
