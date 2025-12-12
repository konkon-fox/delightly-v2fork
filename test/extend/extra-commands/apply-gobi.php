<?php

/**
 * 設定された!gobiコマンドに応じて本文に語尾を追加する処理
 *
 * @param array $SETTING 板の設定
 * @param boolean $tlonly TL判定
 * @param ThreadsStatesUpdater $threadsStatesUpdater スレ状態ファイルを取得・更新するオブジェクト
 */
function applyGobiCommand(
    $SETTING,
    $tlonly,
    $threadsStatesUpdater,
) {
    if ($SETTING['commands'] !== 'checked') {
        return;
    }
    if ($tlonly) {
        return;
    }
    if (strpos($_POST['name'], '!nocmd') !== false) {
        return;
    }
    $threadsStates = $threadsStatesUpdater->get();
    if ($threadsStates === false || empty($threadsStates)) {
        return;
    }
    if (!isset($threadsStates['gobi'])) {
        return;
    }
    // 元本文のみ取得 ※<hr>以降はシステムメッセージなので対象外
    $commentParts = explode('<hr>', $_POST['comment']);
    // 語尾追加
    $gobi = $threadsStates['gobi'];
    if (function_exists('replaceRmj')) {
        $gobi = replaceRmj($gobi);
    }
    $commentParts[0] .= $gobi;
    // 本文変更
    $_POST['comment'] = implode('<hr>', $commentParts);
}

applyGobiCommand(
    $SETTING,
    $tlonly,
    $threadsStatesUpdater,
);
