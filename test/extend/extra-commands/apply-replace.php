<?php

/**
 * 設定された!replaceコマンドに応じてシステムメッセージを追加する処理
 *
 * @param array $SETTING 板の設定
 * @param boolean $tlonly TL判定
 * @param array $threadState スレ状態
 */
function applyReplaceCommand(
    $SETTING,
    $tlonly,
    $threadStates
) {
    if ($SETTING['commands'] !== 'checked') {
        return;
    }
    if (($SETTING['commands-replace'] ?? '') !== 'checked') {
        return;
    }
    if ($tlonly) {
        return;
    }
    if (str_contains($_POST['name'], '!nocmd')) {
        return;
    }
    if (empty($threadStates)) {
        return;
    }
    if (!isset($threadStates['replace'])) {
        return;
    }

    // 本文取得
    $commentParts = explode('<hr>', $_POST['comment']);

    // 置換処理
    $commentParts[0] = strtr($commentParts[0], $threadStates['replace']);

    // 本文変更
    $_POST['comment'] = implode('<hr>', $commentParts);

}

applyReplaceCommand(
    $SETTING,
    $tlonly,
    $threadStates,
    $commaTime
);
