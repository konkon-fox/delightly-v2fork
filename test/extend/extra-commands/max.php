<?php

/**
 * !maxコマンドを設定する際の処理
 *
 * @param array $SETTING 板の設定
 * @param boolean $supervisor スレ主判定
 * @param boolean $admin 管理者判定(管理人or常時コマンド権限を持つCAP)
 * @param boolean $newthread スレ立て時判定
 * @param boolean $tlonly TL判定
 * @param int $number 現在のレス番号
 * @param array $threadStates スレ状態
 * @param boolean $threadStatesReload スレ状態の変化を>>1に反映するか判定
 */
function setMaxCommand(
    $SETTING,
    $supervisor,
    $admin,
    $newthread,
    $tlonly,
    $number,
    &$threadStates,
    &$threadStatesReload
) {
    if ($SETTING['commands'] !== 'checked') {
        return;
    }
    if (isset($SETTING['commands-max']) && $SETTING['commands-max'] !== 'checked') {
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
    if (!str_contains($_POST['comment'], '!max:')) {
        return;
    }
    // !max:数値 を探す
    if (!preg_match('/!max:([0-9]+)/', $_POST['comment'], $matches)) {
        return;
    }

    // レス数上限
    $newMax = (int) $matches[1];

    // 4000を超えている場合は設定不可
    if ($newMax > 4000) {
        addSystemMessage('★レス上限数は最大4000までです。');
        return;
    }

    // 現在のレス数より低い数値は設定不可
    if ($newMax < $number) {
        addSystemMessage("★現在のレス数({$number})より低い数値は指定できません。");
        return;
    }

    // レス数上限を設定
    $threadStates['max'] = $newMax;

    // 成功メッセージ出力(スレ立て時は省略)
    $systemMessage = "★レス数上限を {$newMax} に設定";
    if (!$newthread) {
        addSystemMessage($systemMessage);
    }

    // >>1更新判定
    $threadStatesReload = true;
}

setMaxCommand(
    $SETTING,
    $supervisor,
    $admin,
    $newthread,
    $tlonly,
    $number,
    $threadStates,
    $threadStatesReload
);
