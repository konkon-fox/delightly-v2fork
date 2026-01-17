<?php

/**
 * 設定された!commaコマンドに応じてシステムメッセージを追加する処理
 *
 * @param array $SETTING 板の設定
 * @param boolean $tlonly TL判定
 * @param boolean $admin 管理者判定(管理人or常時コマンド権限を持つCAP)
 * @param string $CAPID 投稿者がCAPの場合のID
 * @param array $threadState スレ状態
 * @param string $commaTime コンマの文字列
 */
function applyCommaCommand(
    $SETTING,
    $tlonly,
    $admin,
    $CAPID,
    $threadStates,
    $commaTime
) {
    if ($SETTING['commands'] !== 'checked') {
        return;
    }
    if (($SETTING['commands-comma'] ??= 'checked') !== 'checked') {
        return;
    }
    if (($SETTING['date_comma_digit'] ??= '0') === '0') {
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
    if (empty($threadStates)) {
        return;
    }
    if (!isset($threadStates['comma'])) {
        return;
    }
    if (!isset($threadStates['comma'][$commaTime])) {
        return;
    }
    addSystemMessage('★<font color="red">.' . $commaTime . '</font>　' . $threadStates['comma'][$commaTime] . '<br>');
}

applyCommaCommand(
    $SETTING,
    $tlonly,
    $admin,
    $CAPID,
    $threadStates,
    $commaTime
);
