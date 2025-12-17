<?php
/**
 * 設定された!774コマンドに応じて本文に語尾を追加する処理
 *
 * @param array $SETTING 板の設定
 * @param boolean $tlonly TL判定
 * @param boolean $admin 管理者判定(管理人or常時コマンド権限を持つCAP)
 * @param string $CAPID 投稿者がCAPの場合のID
 * @param string $THREADS_STATES_FILE スレ状態ファイルへのパス
 */
function apply774Command(
    $SETTING,
    $tlonly,
    $admin,
    $CAPID,
    $THREADS_STATES_FILE
) {
    if($SETTING['commands'] !== 'checked') {
        return;
    }
    if ($SETTING['DISABLE_NAME'] === 'checked') {
        return;
    }
    if($tlonly) {
        return;
    }
    if($admin || $CAPID) {
        return;
    }
    if ($_POST['name'] !== '') {
        return;
    }
    if (!is_file($THREADS_STATES_FILE)) {
        return;
    }
    $threadsStates = getThreadsStates($THREADS_STATES_FILE);
    if($threadsStates === false) {
        return;
    }
    if(!isset($threadsStates[$_POST['thread']]['774'])) {
        return;
    }
    // 名前変更
    $defalutName = $threadsStates[$_POST['thread']]['774'];
    if(function_exists('replaceRmj')) {
        $defalutName = replaceRmj($defalutName);
    }
    $_POST['name'] = $defalutName;
}

apply774Command(
    $SETTING,
    $tlonly,
    $admin,
    $CAPID,
    $THREADS_STATES_FILE
);
