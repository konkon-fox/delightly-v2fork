<?php

/**
 * 設定された!ngkコマンドに応じて名前を強制固定する処理
 *
 * @param array $SETTING 板の設定
 * @param boolean $tlonly TL判定
 * @param boolean $admin 管理者判定(管理人or常時コマンド権限を持つCAP)
 * @param string $CAPID 投稿者がCAPの場合のID
 * @param array $threadStates スレ状態
 */
function applyngkCommand(
    $SETTING,
    $tlonly,
    $admin,
    $CAPID,
    $threadStates
) {
    if ($SETTING['commands'] !== 'checked') {
        return;
    }
    if ($tlonly) {
        return;
    }
    // 管理者やCAPは対象外
    if ($admin || $CAPID) {
        return;
    }
    if (empty($threadStates)) {
        return;
    }
    if (!isset($threadStates['ngk']) || $threadStates['ngk'] !== true) {
        return;
    }

    // 名前を強制固定
    // !774の設定があればそれを使用、なければ板のデフォルト名を使用
    $forcedName = isset($threadStates['774']) ? $threadStates['774'] : $SETTING['BBS_NONAME_NAME'];

    // !rmjが適用される可能性があるため、一応考慮
    if (function_exists('replaceRmj')) {
        $forcedName = replaceRmj($forcedName);
    }

    $_POST['name'] = $forcedName;
}

applyngkCommand(
    $SETTING,
    $tlonly,
    $admin,
    $CAPID,
    $threadStates
);
