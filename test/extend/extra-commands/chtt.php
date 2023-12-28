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
    if (strpos($_POST['name'], '!nocmd') !== false) {
        return;
    }
    if(strpos($_POST['comment'], '!chtt:') === false) {
        return;
    }
    $commentParts = explode('<hr>', $_POST['comment']);
    if(!preg_match('/\!chtt:(.*?)((?=\<br\>)|$)/', $commentParts[0], $commandMatches)) {
        return;
    }
    // コマンド文字列から新スレタイを抽出
    $newThreadTitle = trim($commandMatches[1]);
    // 空欄エラー
    if($newThreadTitle === '') {
        addSystemMessage('★新スレタイが空欄です。<br>');
        return;
    }
    // スレタイ長すぎエラー
    if(mb_strlen($newThreadTitle, 'UTF-8') > $SETTING['BBS_SUBJECT_COUNT']) {
        addSystemMessage('★新スレタイが長すぎます。<br>');
        return;
    }
    // スレタイにIDが存在するかを判定
    preg_match('/ID:(.+)$/', $d, $IDMatches);
    $titleHasId = $SETTING['createid'] === 'checked' && $IDMatches;
    // 成功メッセージ出力(本文)
    $oldThreadTitle = $titleHasId ? preg_replace('/\s\[[^\[]+?★\]$/', '', $subject) : $subject;
    $changeMessage = "★スレタイ変更【{$oldThreadTitle}】→【{$newThreadTitle}】<br>";
    addSystemMessage($changeMessage);
    // 成功メッセージ出力(>>1) datへの反映はbbs-main.phpで行われる
    $messageParts = explode('<hr>', $message);
    if(count($messageParts) < 2) {
        array_push($messageParts, '');
    }
    $messageParts[1] .= preg_replace('/\!(?=[a-zA-Z0-9])/', '&#33;', $changeMessage);
    $message = implode('<hr>', $messageParts);
    // 新スレタイに>>1のIDを追加
    if($titleHasId) {
        $newThreadTitle .= " [{$IDMatches[1]}★]";
    }
    // 過去ログ用subject.jsonを更新
    if(is_file($threadSubjectFile)) {
        $threadSubjectFileHandle = fopen($threadSubjectFile, 'r+');
        if(flock($threadSubjectFileHandle, LOCK_EX)) {
            $tlist = json_decode(fread($threadSubjectFileHandle, filesize($threadSubjectFile)), true);
            $tlist = array_map(function ($thread) use ($newThreadTitle) {
                if((int) $thread['thread'] === (int) $_POST['thread']) {
                    $thread['title'] = $newThreadTitle;
                }
                return $thread;
            }, $tlist);
            ftruncate($threadSubjectFileHandle, 0);
            rewind($threadSubjectFileHandle);
            fwrite($threadSubjectFileHandle, json_encode($tlist, JSON_UNESCAPED_UNICODE));
        }
        fclose($threadSubjectFileHandle);
    }
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
