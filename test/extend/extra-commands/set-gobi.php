<?php

/**
 * !gobiコマンドを設定する際の処理
 *
 * @param array $SETTING 板の設定
 * @param boolean $supervisor スレ主判定
 * @param boolean $admin 管理者判定(管理人or常時コマンド権限を持つCAP)
 * @param boolean $newthread スレ立て時判定
 * @param boolean $tlonly TL判定
 * @param array $threadState スレ状態
 * @param boolean $threadStatesReload スレ状態の変化を>>1に反映するか判定
 */
function setGobiCommand(
    $SETTING,
    $supervisor,
    $admin,
    $newthread,
    $tlonly,
    &$threadStates,
    &$threadStatesReload
) {
    if ($SETTING['commands'] !== 'checked') {
        return;
    }
    if (isset($SETTING['commands-gobi']) && $SETTING['commands-gobi'] !== 'checked') {
        return;
    }
    if ($tlonly) {
        return;
    }
    if (!($supervisor || $admin)) {
        return;
    }
    if (str_contains($_POST['name'], '!nocmd')) {
        return;
    }
    if (!str_contains($_POST['comment'], '!gobi:')) {
        return;
    }
    $commentParts = explode('<hr>', $_POST['comment']);
    if (!preg_match('/\!gobi:(.*):/', $commentParts[0], $commandMatches)) {
        return;
    }
    // 語尾の最大文字数
    $MAX_GOBI_LENGTH = 100;

    $MAX_GOBI_LENGTH = min($MAX_GOBI_LENGTH, floor($SETTING['BBS_MESSAGE_COUNT'] / 2));
    $gobi = trim($commandMatches[1]);
    // 例外処理
    if (mb_strlen($gobi, 'UTF-8') > $MAX_GOBI_LENGTH) {
        addSystemMessage("★語尾の最大文字数は{$MAX_GOBI_LENGTH}です。");
        return;
    }
    // スレッド状態を更新
    $threadStates['gobi'] = $gobi;
    $systemMessage = "★語尾を「{$gobi}」に設定しました。";
    if ($gobi === '') {
        unset($threadStates['gobi']);
        $systemMessage = '★語尾を取り消しました。';
    }
    // 成功メッセージ出力(本文)
    if (!$newthread) {
        addSystemMessage($systemMessage);
    }
    // >>1更新判定
    $threadStatesReload = true;
}
setGobiCommand(
    $SETTING,
    $supervisor,
    $admin,
    $newthread,
    $tlonly,
    $threadStates,
    $threadStatesReload
);
