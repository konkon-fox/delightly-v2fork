<?php

/**
 * コマンドによって変更されたスレ状態を>>1に反映する処理
 *
 * @param array $SETTING 板の設定
 * @param boolean $newthread スレ立て時判定
 * @param boolean $tlonly TL判定
 * @param boolean $threadStatesReload スレ状態の変化を>>1に反映するか判定
 * @param array $threadState スレ状態
 * @param string &$message >>1の本文
 * @param boolean &$reload >>1更新フラグ
 * @return void
 */
function showThreadStates(
    $SETTING,
    $newthread,
    $tlonly,
    $threadStatesReload,
    $threadStates,
    &$message,
    &$reload
) {
    if ($SETTING['commands'] !== 'checked') {
        return;
    }
    if ($tlonly) {
        return;
    }
    if (!$threadStatesReload) {
        return;
    }
    // >>1の本文取得
    if ($newthread) {
        $comment = $_POST['comment'];
    } else {
        $comment = $message;
    }
    // >>1の本文を3分割
    $commentParts = explode('<hr>', $comment);
    for ($i = count($commentParts);$i <= 3;$i++) {
        array_push($commentParts, '');
    }
    $commentParts[2] = '';
    // デフォ名無し情報追加
    if (isset($threadStates['774'])) {
        $defaultName = $threadStates['774'];
        if (function_exists('replaceRmj')) {
            $defaultName = replaceRmj($defaultName);
        }
        $defaultName = preg_replace('/\!(?=[a-zA-Z0-9])/', '&#33;', $defaultName);
        $commentParts[2] .= "<font color=\"red\">※デフォ名無し=</font>{$defaultName}<br>";
    }
    // 語尾情報追加
    if (isset($threadStates['gobi'])) {
        $gobi = $threadStates['gobi'];
        if (function_exists('replaceRmj')) {
            $gobi = replaceRmj($gobi);
        }
        $gobi = preg_replace('/\!(?=[a-zA-Z0-9])/', '&#33;', $gobi);
        $commentParts[2] .= "<font color=\"red\">※GOBI=</font>{$gobi}<br>";
    }
    // 分割された本文を統合
    $comment = implode('<hr>', $commentParts);
    $comment = preg_replace('/(<hr>)+$/', '', $comment);
    // 元本文に反映
    if ($newthread) {
        $_POST['comment'] = $comment;
    } else {
        $message = $comment;
        $reload = true;
    }
}

showThreadStates(
    $SETTING,
    $newthread,
    $tlonly,
    $threadStatesReload,
    $threadStates,
    $message,
    $reload,
);
