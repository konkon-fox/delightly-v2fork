<?php
/**
 * @param array $SETTING 板の設定
 * @param boolean $supervisor スレ主判定
 * @param boolean $admin 管理者判定(管理人or常時コマンド権限を持つCAP)
 * @param boolean $newthread スレ立て時判定
 * @param boolean $tlonly TL判定
 * @param string $datlog スレッド毎連続投稿規制用ファイルへのパス
 * @param string $THREADFILE 通常ブラウザ用datファイルへのパス
 * @param string $DATILE 専ブラ用datファイルへのパス
 * @param array $PAGEFILE subject.json用の連想配列
 */
function applyPoolCommand(
    $SETTING,
    $supervisor,
    $admin,
    $newthread,
    $tlonly,
    $datlog,
    $THREADFILE,
    $DATFILE,
    &$PAGEFILE
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
    if(strpos($_POST['comment'], '!pool') === false) {
        return;
    }
    // datファイル削除
    @unlink($DATFILE);
    // 過去ログを保持しない場合
    if ($SETTING['disable_kakolog'] === 'checked') {
        @unlink($THREADFILE);
    }
    // datlog削除
    if (is_file($datlog)) {
        @unlink($datlog);
    }
    // subject.json用のデータ更新
    $PAGEFILE = array_filter($PAGEFILE, function ($thread) {
        return (int) $thread['thread'] !== (int) $_POST['thread'];
    });
    $PAGEFILE = array_values($PAGEFILE);
}

applyPoolCommand(
    $SETTING,
    $supervisor,
    $admin,
    $newthread,
    $tlonly,
    $PATH."dat/".$_POST['thread']."_kisei.cgi", // $datlog
    $THREADFILE,
    $DATFILE,
    $PAGEFILE
);
