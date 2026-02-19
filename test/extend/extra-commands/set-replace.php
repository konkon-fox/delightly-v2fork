<?php

/**
 * !replaceコマンドを設定する際の処理
 *
 * @param array $SETTING 板の設定
 * @param boolean $supervisor スレ主判定
 * @param boolean $admin 管理者判定(管理人or常時コマンド権限を持つCAP)
 * @param boolean $newthread スレ立て時判定
 * @param boolean $tlonly TL判定
 * @param array $threadStates スレ状態
 * @param boolean $threadStatesReload スレ状態の変化を>>1に反映するか判定
 * @param int $LV 投稿者のレベル
 */
function setReplaceCommand(
    $SETTING,
    $supervisor,
    $admin,
    $newthread,
    $tlonly,
    &$threadStates,
    &$threadStatesReload,
    $LV
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
    if (!($supervisor || $admin)) {
        return;
    }
    if (str_contains($_POST['name'], '!nocmd')) {
        return;
    }
    if (!str_contains($_POST['comment'], '!replace:')) {
        return;
    }

    // コマンド定数設定取得
    $settingFile = dirname(__FILE__, 3) . '/operate/data/commands-constant-settings.json';
    if (is_file($settingFile)) {
        $settings = getJsonFile($settingFile);
        if ($settings === false) {
            Error('コマンド定数設定ファイルの取得に失敗しました。');
        }
    } else {
        $settings = [];
    }

    // 必要最低レベル
    $REPLACE_MIN_LV = (int) ($settings['REPLACE_MIN_LV'] ?? 0);

    if ($LV < $REPLACE_MIN_LV) {
        addSystemMessage("★レベル{$REPLACE_MIN_LV}未満のユーザーは!replaceコマンドを使えません。");
        return;
    }

    // 置換設定の上限数
    $REPLACE_COUNT_LIMIT = (int) (($settings['REPLACE_COUNT_LIMIT'] ?? 0) ?: 5);

    // 置換前後に使用可能なテキストの最大文字数
    $REPLACE_COMMENT_LIMIT = (int) (($settings['REPLACE_COMMENT_LIMIT'] ?? 0) ?: 100);

    // 置換後の文字列を青字太字にする
    $REPLACE_ENABLE_CHANGE_FONT = ($settings['REPLACE_ENABLE_CHANGE_FONT'] ?? 'checked') === 'checked';

    // 本文取得
    $commentParts = explode('<hr>', $_POST['comment']);
    // 変数初期化
    $systemMessages = [];
    $alertMessages = [];
    $countIsOver = false;
    if (!isset($threadStates['replace'])) {
        $threadStates['replace'] = [];
    }

    // 解除
    if (str_contains($commentParts[0], '!replace:kaijo')) {
        $threadStates['replace'] = [];
        $systemMessages[] = '★置換を全解除しました。';
    }

    // replace構文検索
    $commentParts[0] = preg_replace_callback(
        '/!replace:([^→]+)→([^→]+?)(?=<br>|$)/u',
        function ($matches) use (
            $REPLACE_COUNT_LIMIT,
            $REPLACE_COMMENT_LIMIT,
            $REPLACE_ENABLE_CHANGE_FONT,
            &$alertMessages,
            &$systemMessages,
            &$threadStates,
            &$countIsOver,
        ) {
            // 既に個数上限オーバー
            if ($countIsOver) {
                return $matches[0];
            }
            // 個数上限チェック
            if (count($threadStates['replace']) >= $REPLACE_COUNT_LIMIT) {
                $countIsOver = true;
                $alertMessages[] = "★置換の設定数は最大{$REPLACE_COUNT_LIMIT}個までです。";
                return $matches[0];
            }
            // 文字列長さチェック
            if (mb_strlen($matches[1]) > $REPLACE_COMMENT_LIMIT || mb_strlen($matches[2]) > $REPLACE_COMMENT_LIMIT) {
                $alertMessages[] = "★置換に使える文字数は最大{$REPLACE_COMMENT_LIMIT}文字までです。";
                return $matches[0];
            }
            // フォント変えるか判定
            if ($REPLACE_ENABLE_CHANGE_FONT) {
                // 悪用対策で青字太字化
                $threadStates['replace'][$matches[1]] = "<b><font color=\"blue\">{$matches[2]}</font></b>";
            } else {
                // そのまま表示
                $threadStates['replace'][$matches[1]] = $matches[2];
            }

            $systemMessages[] = '★置換を設定しました。';
            return '!replace:████→████';
        },
        $commentParts[0]
    );

    // 本文変更
    $_POST['comment'] = implode('<hr>', $commentParts);

    // 失敗メッセージ出力(本文)
    if (!empty($alertMessages)) {
        addSystemMessage(implode('<br>', $alertMessages));
    }

    //成功メッセージ出力(スレ立て時は省略)
    if (!$newthread && !empty($systemMessages)) {
        addSystemMessage(implode('<br>', $systemMessages));
    }

    // >>1更新判定
    if (!empty($systemMessages)) {
        $threadStatesReload = true;
    }
}

setReplaceCommand(
    $SETTING,
    $supervisor,
    $admin,
    $newthread,
    $tlonly,
    $threadStates,
    $threadStatesReload,
    $LV
);
