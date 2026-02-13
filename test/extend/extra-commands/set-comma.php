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
    if (($SETTING['commands-comma'] ?? 'checked') !== 'checked') {
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

    // コンマセット可能な数
    $COMMA_LIMIT = (int) (($settings['COMMA_LIMIT'] ?? 0) ?: 20);
    // コンマセット可能な文章の最大文字数
    $COMMA_COMMENT_LIMIT = (int) (($settings['COMMA_COMMENT_LIMIT'] ?? 0) ?: 100);

    // 配列初期化
    if (!isset($threadStates['comma'])) {
        $threadStates['comma'] = [];
    }

    $countIsOver = false;
    $systemMessages = [];

    $commentParts[0] = preg_replace_callback(
        '/\!comma:([0-9]{1,3}):(.+?)(:h)?(?=(?:\<br\>|$))/',
        function ($matches) use (
            $COMMA_LIMIT,
            $COMMA_COMMENT_LIMIT,
            &$systemMessages,
            &$threadStates,
            &$countIsOver,
        ) {
            // コンマ解除
            if ($matches[2] === 'kaijo') {
                if (isset($threadStates['comma'][$matches[1]])) {
                    unset($threadStates['comma'][$matches[1]]);
                    unset($threadStates['comma_hide'][$matches[1]]);
                    $systemMessages[] = "★.{$matches[1]}を解除しました。";
                }
                return $matches[0];
            }
            // 個数上限スキップ
            if ($countIsOver) {
                return $matches[0];
            }
            // 個数上限チェック
            if (!isset($threadStates['comma'][$matches[1]]) && count($threadStates['comma']) >= $COMMA_LIMIT) {
                $systemMessages[] = "★コンマに設定可能なのは{$COMMA_LIMIT}個までです。";
                $countIsOver = true;
                return $matches[0];
            }
            // 文字数上限チェック
            if (mb_strlen($matches[2]) > $COMMA_COMMENT_LIMIT) {
                $systemMessages[] = "★コンマに設定可能な文字数は{$COMMA_COMMENT_LIMIT}までです。";
                return $matches[0];
            }

            // スレ状態に適用
            $comment = trim($matches[2]);
            $threadStates['comma'][$matches[1]] = $comment;

            // 隠蔽オプション
            $option = $matches[3] ?? '';
            if ($option === ':h') {
                $threadStates['comma_hide'][$matches[1]] = true;
                $systemMessages[] = "★.{$matches[1]}に「████」を設定しました。";
                return "!comma:{$matches[1]}:████";
            } else {
                unset($threadStates['comma_hide'][$matches[1]]);
                $systemMessages[] = "★.{$matches[1]}に「{$comment}」を設定しました。";
                return $matches[0];
            }
        },
        $commentParts[0]
    );

    // 本文変更
    $_POST['comment'] = implode('<hr>', $commentParts);

    // 成功メッセージ出力(本文)
    if (!$newthread && !empty($systemMessages)) {
        addSystemMessage(implode('<br>', $systemMessages));
    }
    // >>1更新判定
    if (!empty($systemMessages)) {
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
