<?php

/**
 * 設定された!774コマンドに応じて本文に語尾を追加する処理
 *
 * @param array $SETTING 板の設定
 * @param boolean $tlonly TL判定
 * @param boolean $admin 管理者判定(管理人or常時コマンド権限を持つCAP)
 * @param string $CAPID 投稿者がCAPの場合のID
 * @param ThreadsStatesUpdater $threadsStatesUpdater スレ状態ファイルを取得・更新するオブジェクト
 */
function apply774Command(
    $SETTING,
    $tlonly,
    $admin,
    $CAPID,
    $threadsStatesUpdater,
) {
    if ($SETTING['commands'] !== 'checked') {
        return;
    }
    if ($SETTING['DISABLE_NAME'] === 'checked') {
        return;
    }
    if ($tlonly) {
        return;
    }
    if ($admin || $CAPID) {
        return;
    }
    if ($_POST['name'] !== '') {
        return;
    }
    $threadsStates = $threadsStatesUpdater->get();
    if ($threadsStates === false || empty($threadsStates)) {
        return;
    }
    if (!isset($threadsStates['774'])) {
        return;
    }
    // 名前変更
    $defalutName = $threadsStates['774'];
    if (function_exists('replaceRmj')) {
        $defalutName = replaceRmj($defalutName);
    }
    $_POST['name'] = $defalutName;
}

apply774Command(
    $SETTING,
    $tlonly,
    $admin,
    $CAPID,
    $threadsStatesUpdater,
);
