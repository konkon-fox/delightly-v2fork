<?php
/**
 * 設定された!gobiコマンドに応じて本文に語尾を追加する処理
 *
 * @param array $SETTING 板の設定
 * @param boolean $tlonly TL判定
 * @param string $THREADS_STATES_FILE スレ状態ファイルへのパス
 */
function applyGobiCommand(
    $SETTING,
    $tlonly,
    $THREADS_STATES_FILE
) {
    if($SETTING['commands'] !== 'checked') {
        return;
    }
    if($tlonly) {
        return;
    }
    if (strpos($_POST['name'], '!nocmd') !== false) {
        return;
    }
    if (!is_file($THREADS_STATES_FILE)) {
        return;
    }
    $threadsStates = getThreadsStates($THREADS_STATES_FILE);
    if($threadsStates === false) {
        return;
    }
    if(!isset($threadsStates[$_POST['thread']]['gobi'])) {
        return;
    }
    // 元本文のみ取得 ※<hr>以降はシステムメッセージなので対象外
    $commentParts = explode('<hr>', $_POST['comment']);
    // 語尾追加
    $commentParts[0] .= $threadsStates[$_POST['thread']]['gobi'];
    // 本文変更
    $_POST['comment'] = implode('<hr>', $commentParts);
}

applyGobiCommand(
    $SETTING,
    $tlonly,
    $THREADS_STATES_FILE
);
