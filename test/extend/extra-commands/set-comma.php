<?php

/**
 * !commaコマンドを設定する際の処理
 *
 * @param array $SETTING 板の設定
 * @param boolean $supervisor スレ主判定
 * @param boolean $admin 管理者判定(管理人or常時コマンド権限を持つCAP)
 * @param boolean $newthread スレ立て時判定
 * @param boolean $tlonly TL判定
 * @param array $threadState スレ状態
 * @param boolean $threadStatesReload スレ状態の変化を>>1に反映するか判定
 */
function setCommaCommand(
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
    if (($SETTING['commands-comma'] ??= 'checked') !== 'checked') {
        return;
    }
    if (($SETTING['date_comma_digit'] ??= '0') === '0') {
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
    if (!str_contains($_POST['comment'], '!comma:')) {
        return;
    }
    $commentParts = explode('<hr>', $_POST['comment']);
    if (!preg_match_all('/\!comma:([0-9]{1,3}):(.+?)(?=(?:\<br\>|$))/', $commentParts[0], $commandMatches, PREG_SET_ORDER)) {
        return;
    }
    // コンマセット可能な数
    $COMMA_LIMIT = 20;
    // コンマセット可能な文章の最大文字数
    $COMMA_COMMENT_LIMIT = 100;

    if (!isset($threadStates['comma'])) {
        $threadStates['comma'] = [];
    }

    // スレッド状態を更新
    $systemMessages = [];
    foreach ($commandMatches as $matches) {
        if ($matches[2] === 'kaijo') {
            if (isset($threadStates['comma'][$matches[1]])) {
                unset($threadStates['comma'][$matches[1]]);
                $systemMessages[] = "★.{$matches[1]}を解除しました。";
            }
            continue;
        }
        if (!isset($threadStates['comma'][$matches[1]]) && count($threadStates['comma']) >= $COMMA_LIMIT) {
            $systemMessages[] = "★コンマに設定可能なのは{$COMMA_LIMIT}個までです。";
            break;
        }
        if (mb_strlen($matches[2]) > $COMMA_COMMENT_LIMIT) {
            $systemMessages[] = "★コンマに設定可能な文字数は{$COMMA_COMMENT_LIMIT}までです。";
            continue;
        }
        $comment = trim($matches[2]);
        $threadStates['comma'][$matches[1]] = $comment;
        $systemMessages[] = "★.{$matches[1]}に「{$comment}」を設定しました。";
    }

    // 成功メッセージ出力(本文)
    if (!$newthread && !empty($systemMessages)) {
        addSystemMessage(implode('<br>', $systemMessages));
    }
    // >>1更新判定
    if (!empty($systemMessage)) {
        $threadStatesReload = true;
    }

}
setCommaCommand(
    $SETTING,
    $supervisor,
    $admin,
    $newthread,
    $tlonly,
    $threadStates,
    $threadStatesReload
);
