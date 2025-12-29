<?php

/**
 * !ngkコマンドを設定する際の処理
 *
 * @param array $SETTING 板の設定
 * @param boolean $supervisor スレ主判定
 * @param boolean $admin 管理者判定(管理人or常時コマンド権限を持つCAP)
 * @param boolean $tlonly TL判定
 * @param array $threadStates スレ状態
 * @param boolean $threadStatesReload スレ状態の変化を>>1に反映するか判定
 */
function setngkCommand(
    $SETTING,
    $supervisor,
    $admin,
    $tlonly,
    &$threadStates,
    &$threadStatesReload
) {
    if ($SETTING['commands'] !== 'checked') {
        return;
    }
    if ($tlonly) {
        return;
    }
    if (!($supervisor || $admin)) {
        return;
    }
    if (strpos($_POST['name'], '!nocmd') !== false) {
        return;
    }
    if (strpos($_POST['comment'], '!ngk') === false) {
        return;
    }
    // スレッド状態を更新
    $threadStates['ngk'] = true;
    $systemMessage = '★ngk（名前入力禁止）モードを発動しました。<br>';
    if (strpos($_POST['comment'], '!ngk:kaijo') !== false) {
        unset($threadStates['ngk']);
        $systemMessage = '★ngk（名前入力禁止）モードを解除しました。<br>';
    }
    // 成功メッセージ出力(本文)
    addSystemMessage($systemMessage);
    // >>1更新判定
    $threadStatesReload = true;
}
setngkCommand(
    $SETTING,
    $supervisor,
    $admin,
    $tlonly,
    $threadStates,
    $threadStatesReload
);
