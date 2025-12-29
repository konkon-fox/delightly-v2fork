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
    if (!isset($threadStates['ngk'])) {
        return;
    }

    // 名前を空にする
    $_POST['name'] = '';
}

applyngkCommand(
    $SETTING,
    $tlonly,
    $admin,
    $CAPID,
    $threadStates
);
