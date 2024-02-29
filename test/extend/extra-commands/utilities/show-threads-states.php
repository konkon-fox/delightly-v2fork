<?php
/**
 * コマンドによって変更されたスレ状態を>>1に反映する処理
 *
 * @param array $SETTING 板の設定
 * @param boolean $newthread スレ立て時判定
 * @param boolean $tlonly TL判定
 * @param boolean $threadsStatesReload スレ状態の変化を>>1に反映するか判定
 * @param string $THREADS_STATES_FILE スレ状態ファイルへのパス
 * @param string &$message >>1の本文
 * @param boolean &$reload >>1更新フラグ
 * @return void
 */
function showThreadsStates(
    $SETTING,
    $newthread,
    $tlonly,
    $threadsStatesReload,
    $THREADS_STATES_FILE,
    &$message,
    &$reload
) {
    if($SETTING['commands'] !== 'checked') {
        return;
    }
    if($tlonly) {
        return;
    }
    if(!$threadsStatesReload) {
        return;
    }
    if(!is_file($THREADS_STATES_FILE)) {
        return;
    }
    // >>1の本文取得
    if($newthread) {
        $comment = $_POST['comment'];
    } else {
        $comment = $message;
    }
    // >>1の本文を3分割
    $commentParts = explode('<hr>', $comment);
    for($i = count($commentParts);$i <= 3;$i++) {
        array_push($commentParts, '');
    }
    $commentParts[2] = '';
    $threadsStates = getThreadsStates($THREADS_STATES_FILE);
    if($threadsStates === false) {
        return;
    }
    // デフォ名無し情報追加
    if(isset($threadsStates[$_POST['thread']]['774'])) {
        $defaultName = $threadsStates[$_POST['thread']]['774'];
        $defaultName = preg_replace('/\!(?=[a-zA-Z0-9])/', '&#33;', $defaultName);
        $commentParts[2] .= "<font color=\"red\">※デフォ名無し=</font>{$defaultName}<br>";
    }
    // 語尾情報追加
    if(isset($threadsStates[$_POST['thread']]['gobi'])) {
        $gobi = $threadsStates[$_POST['thread']]['gobi'];
        $gobi = preg_replace('/\!(?=[a-zA-Z0-9])/', '&#33;', $gobi);
        $commentParts[2] .= "<font color=\"red\">※GOBI=</font>{$gobi}<br>";
    }
    // 分割された本文を統合
    $comment = implode('<hr>', $commentParts);
    $comment = preg_replace('/(<hr>)+$/', '', $comment);
    // 元本文に反映
    if($newthread) {
        $_POST['comment'] = $comment;
    } else {
        $message = $comment;
        $reload = true;
    }
}

showThreadsStates(
    $SETTING,
    $newthread,
    $tlonly,
    $threadsStatesReload,
    $THREADS_STATES_FILE,
    $message,
    $reload
);
