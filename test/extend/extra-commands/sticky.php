<?php

/**
 * スレッドをスレ一覧の最上部に固定(ピン留め)するコマンド
 *
 * @param array $SETTING 板の設定
 * @param boolean $admin 管理者判定(管理人or常時コマンド権限を持つCAP)
 * @param boolean $newthread スレ立て時判定
 * @param boolean $tlonly TL判定
 * @param array $threadState スレ状態
 * @param boolean $threadStatesReload スレ状態の変化を>>1に反映するか判定
 */
function applyStickyCommand(
    $SETTING,
    $admin,
    $newthread,
    $tlonly,
    &$threadStates,
    &$threadStatesReload
) {
    if ($SETTING['commands'] !== 'checked') {
        return;
    }
    if (isset($SETTING['commands-sticky']) && $SETTING['commands-sticky'] !== 'checked') {
        return;
    }
    if ($tlonly) {
        return;
    }
    if (!$admin) {
        return;
    }
    if (str_contains($_POST['name'], '!nocmd')) {
        return;
    }
    if (!str_contains($_POST['comment'], '!sticky')) {
        return;
    }
    if (!str_contains($_POST['comment'], '!sticky:kaijo')) {
        // ピン留め
        $threadStates['sticky'] = true;
        $systemMessage = '★スレッドをピン留めしました。';
    } else {
        // ピン留めを解除
        unset($threadStates['sticky']);
        $systemMessage = '★スレッドのピン留めを解除しました。';
    }
    // 成功メッセージ出力(本文)
    if (!$newthread) {
        addSystemMessage($systemMessage);
    }
    // >>1更新判定
    $threadStatesReload = true;
}
applyStickyCommand(
    $SETTING,
    $admin,
    $newthread,
    $tlonly,
    $threadStates,
    $threadStatesReload
);
