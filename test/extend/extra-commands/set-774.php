<?php

/**
 * !774コマンドを設定する際の処理
 *
 * @param array $SETTING 板の設定
 * @param boolean $supervisor スレ主判定
 * @param boolean $admin 管理者判定(管理人or常時コマンド権限を持つCAP)
 * @param boolean $newthread スレ立て時判定
 * @param boolean $tlonly TL判定
 * @param ThreadsStatesUpdater $threadsStatesUpdater スレ状態ファイルを更新するオブジェクト
 * @param boolean $threadsStatesReload スレ状態の変化を>>1に反映するか判定
 */
function set774Command(
    $SETTING,
    $supervisor,
    $admin,
    $newthread,
    $tlonly,
    $threadsStatesUpdater,
    &$threadsStatesReload
) {
    if ($SETTING['commands'] !== 'checked') {
        return;
    }
    if ($SETTING['DISABLE_NAME'] === 'checked') {
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
    if (strpos($_POST['comment'], '!774:') === false) {
        return;
    }
    $commentParts = explode('<hr>', $_POST['comment']);
    if (!preg_match('/\!774:(.*?)((?=\<br\>)|$)/', $commentParts[0], $commandMatches)) {
        return;
    }
    // デフォ名無しの最大文字数
    $MAX_774_LENGTH = $SETTING['BBS_NAME_COUNT'];

    $name = trim($commandMatches[1]);
    // 例外処理
    if (mb_strlen($name, 'UTF-8') > $MAX_774_LENGTH) {
        addSystemMessage("★デフォ名無しの最大文字数は{$MAX_774_LENGTH}です。<br>");
        return;
    }
    /* --置換処理ここから-- */
    // 変換
    if ($SETTING['change_sakujyo'] == "checked") {
        $name = str_replace("管理", '"管理"', $name);
        $name = str_replace("削除", '"削除"', $name);
        $name = str_replace("sakujyo", '"sakujyo"', $name);
    }
    // 偽キャップ、偽トリップ変換
    $name = str_replace("★", "☆", $name);
    $name = preg_replace("/&#0*9733([^0-9]|$)/", "☆", $name);
    $name = preg_replace("/&#[xX]0*2605([^a-zA-Z0-9]|$)/", "☆", $name);
    $name = str_replace("◆", "◇", $name);
    $name = preg_replace("/&#0*9670([^0-9]|$)/", "◇", $name);
    $name = preg_replace("/&#[xX]0*25[cC]6([^a-zA-Z0-9]|$)/", "◇", $name);
    /* --置換処理ここまで-- */
    // スレッド情報ファイルに書き込み
    $threadsStates = $threadsStatesUpdater->get();
    if ($threadsStates === false) {
        addSystemMessage("★!774コマンドの発動に失敗しました。<br>");
        return;
    }
    $threadsStates['774'] = $name;
    $systemMessage = "★デフォ名無しを「{$name}」に設定しました。<br>";
    if ($name === '') {
        unset($threadsStates['774']);
        $systemMessage = "★デフォ名無しを取り消しました。<br>";
    }
    $threadsStatesUpdater->put($threadsStates);
    // 成功メッセージ出力(本文)
    if (!$newthread) {
        addSystemMessage($systemMessage);
    }
    // >>1更新判定
    $threadsStatesReload = true;
}
set774Command(
    $SETTING,
    $supervisor,
    $admin,
    $newthread,
    $tlonly,
    $threadsStatesUpdater,
    $threadsStatesReload
);
