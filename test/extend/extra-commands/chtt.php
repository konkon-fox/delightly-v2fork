<?php
/**
 * @param array $SETTING 板の設定
 * @param boolean $supervisor スレ主判定
 * @param boolean $admin 管理者判定(管理人or常時コマンド権限を持つCAP)
 * @param boolean $newthread スレ立て時判定
 * @param boolean $tlonly TL判定
 * @param string $threadSubjectFile 過去ログ用subject.jsonへのパス
 * @param string $d datファイル1行目の日付ID
 * @param string $message datファイル1行目の本文
 * @param string $subject datファイル1行目のスレタイ
 * @param boolean $reload bbs-main.phpでの>>1更新フラグ
 */
function applyChttCommand(
    $SETTING,
    $supervisor,
    $admin,
    $newthread,
    $tlonly,
    $threadSubjectFile,
    $d,
    &$message,
    &$subject,
    &$reload
) {
    if($SETTING['commands'] !== 'checked') {
        return;
    }
    if($newthread || $tlonly) {
        return;
    }
    if(!($supervisor || $admin)) {
        return;
    }
    if(strpos($_POST['comment'], '!chtt:') === false) {
        return;
    }
    if(!preg_match('/\!chtt:(.+?)((?=\<br\>)|$)/', $_POST['comment'], $commandMatches)) {
        return;
    }
    // コマンド文字列から新スレタイを抽出
    $newThreadTitle = trim($commandMatches[1]);
    // 本文にシステムメッセージ用ラインを追加
    if(strpos($_POST['comment'], '<hr>') === false) {
        $_POST['comment'] .= '<hr>';
    }
    // 空欄エラー
    if($newThreadTitle === '') {
        $_POST['comment'] .= '★新スレタイが空欄です。<br>';
        return;
    }
    // スレタイ長すぎエラー
    if(mb_strlen($newThreadTitle, 'UTF-8') > $SETTING['BBS_SUBJECT_COUNT']) {
        $_POST['comment'] .= '★新スレタイが長すぎます。<br>';
        return;
    }
    // スレタイにIDが存在するかを判定
    preg_match('/ID:(.+)$/', $d, $IDMatches);
    $titleHasId = $SETTING['createid'] === 'checked' && $IDMatches;
    // 成功メッセージ出力(本文)
    $oldThreadTitle = $titleHasId ? preg_replace('/\s\[[^\[]+?★\]$/', '', $subject) : $subject;
    $changeMessage = "★スレタイ変更【{$oldThreadTitle}】→【{$newThreadTitle}】<br>";
    $_POST['comment'] .= $changeMessage;
    // 成功メッセージ出力(>>1) datへの反映はbbs-main.phpで行われる
    if(strpos($message, '<hr>') === false) {
        $message .= '<hr>';
    }
    $message .= preg_replace('/\!(?=[a-zA-Z0-9])/', '！', $changeMessage);
    // 新スレタイに>>1のIDを追加
    if($titleHasId) {
        $newThreadTitle .= " [{$IDMatches[1]}★]";
    }
    // 過去ログ用subject.jsonを更新
    $tlist = json_decode(file_get_contents($threadSubjectFile), true);
    $tlist = array_map(function ($thread) use ($newThreadTitle) {
        if((int) $thread['thread'] === (int) $_POST['thread']) {
            $thread['title'] = $newThreadTitle;
        }
        return $thread;
    }, $tlist);
    file_put_contents($threadSubjectFile, json_encode($tlist, JSON_UNESCAPED_UNICODE), LOCK_EX);
    // subject.jsonとsubject.txtへの反映はbbs-main.phpで行われる
    $subject = $newThreadTitle;
    // >>1更新フラグ
    $reload = true;
}

applyChttCommand(
    $SETTING,
    $supervisor,
    $admin,
    $newthread,
    $tlonly,
    $PATH."thread/".substr($_POST['thread'], 0, 4)."/subject.json",
    $d,
    $message,
    $subject,
    $reload
);
