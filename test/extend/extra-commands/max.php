<?php

/**
 * !maxコマンドを設定する際の処理
 *
 * @param array $SETTING 板の設定
 * @param boolean $supervisor スレ主判定
 * @param boolean $admin 管理者判定(管理人or常時コマンド権限を持つCAP)
 * @param boolean $tlonly TL判定
 * @param int $number 現在のレス番号
 * @param array $threadStates スレ状態
 * @param boolean $threadStatesReload スレ状態の変化を>>1に反映するか判定
 */
function setMaxCommand(
  $SETTING,
  $supervisor,
  $admin,
  $tlonly,
  $number,
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
  // !max:数値 を探す
  if (!preg_match('/!max:([0-9]+)/', $_POST['comment'], $matches)) {
    return;
  }

  $newMax = (int) $matches[1];

  // バリデーション
  if ($newMax > 4000) {
    addSystemMessage("★レス上限数は最大4000までです。<br>");
    return;
  }

  if ($newMax < $number) {
    addSystemMessage("★現在のレス数({$number})より低い数値は指定できません。<br>");
    return;
  }

  $threadStates['max'] = $newMax;
  $systemMessage = "★レス上限数を {$newMax} に変更しました。<br>";

  // 成功メッセージ出力(本文)
  addSystemMessage($systemMessage);

  // >>1更新判定
  $threadStatesReload = true;
}

setMaxCommand(
  $SETTING,
  $supervisor,
  $admin,
  $tlonly,
  $number,
  $threadStates,
  $threadStatesReload
);
