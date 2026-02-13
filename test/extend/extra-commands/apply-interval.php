<?php

/**
 * 設定された!intervalコマンドを適用する処理
 *
 * @param array &$SETTING 板の設定(参照渡し)
 * @param boolean $admin 管理者判定(管理人or常時コマンド権限を持つCAP)
 * @param boolean $tlonly TL判定
 * @param array $threadState スレ状態
 */
function applyIntervalCommand(
    &$SETTING,
    $admin,
    $tlonly,
    $threadStates
) {
    if ($SETTING['commands'] !== 'checked') {
        return;
    }
    if (($SETTING['commands-interval'] ?? '') !== 'checked') {
        return;
    }
    if ($tlonly) {
        return;
    }
    if ($admin) {
        return;
    }
    if (empty($threadStates)) {
        return;
    }
    if (!isset($threadStates['interval'])) {
        return;
    }
    // 投稿間隔規制の値を変更
    $SETTING['timeinterval'] = $threadStates['interval'];
}

applyIntervalCommand(
    $SETTING,
    $admin,
    $tlonly,
    $threadStates
);
