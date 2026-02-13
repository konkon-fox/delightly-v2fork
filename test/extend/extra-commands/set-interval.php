<?php

/**
 * !intervalコマンドを設定する際の処理
 *
 * @param array $SETTING 板の設定
 * @param boolean $supervisor スレ主判定
 * @param boolean $admin 管理者判定(管理人or常時コマンド権限を持つCAP)
 * @param boolean $newthread スレ立て時判定
 * @param boolean $tlonly TL判定
 * @param array $threadStates スレ状態
 * @param boolean $threadStatesReload スレ状態の変化を>>1に反映するか判定
 */
function setIntervalCommand(
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
    if (($SETTING['commands-interval'] ?? '') !== 'checked') {
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
    if (!str_contains($_POST['comment'], '!interval:')) {
        return;
    }
    // !interval:数値 を探す
    if (!preg_match('/!interval:([0-9]+)/', $_POST['comment'], $matches)) {
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

    // 設定可能な投稿間隔(秒)の最小値
    $MIN_SECOND = (int) ($settings['INTERVAL_MIN_SECOND'] ?? 0);

    // 設定可能な投稿間隔(秒)の最大値
    $MAX_SECOND = (int) (($settings['INTERVAL_MAX_SECOND'] ?? 0) ?: 60);

    // 設定する投稿間隔(秒)
    $newInterval = (int) $matches[1];

    // 下限より小さい場合は却下
    if ($newInterval < $MIN_SECOND) {
        addSystemMessage("★投稿間隔は最小{$MIN_SECOND}秒までです。");
        return;
    }

    // 上限より大きい場合は却下
    if ($newInterval > $MAX_SECOND) {
        addSystemMessage("★投稿間隔は最大{$MAX_SECOND}秒までです。");
        return;
    }

    // レス数上限を設定
    $threadStates['interval'] = $newInterval;

    // 成功メッセージ出力(スレ立て時は省略)
    $systemMessage = "★レスの投稿間隔を {$newInterval}秒 に設定";
    if (!$newthread) {
        addSystemMessage($systemMessage);
    }

    // >>1更新判定
    $threadStatesReload = true;
}

setIntervalCommand(
    $SETTING,
    $supervisor,
    $admin,
    $newthread,
    $tlonly,
    $threadStates,
    $threadStatesReload
);
