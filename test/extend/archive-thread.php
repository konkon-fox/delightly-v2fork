<?php

/**
 * 該当スレッドを過去ログ送りにする関数
 *
 * @param array $SETTING 板の設定
 * @param string $KAKOLOGLIST 過去ログリストファイルへのパス
 * @param string $KAKOLOGLISTINDEX 過去ログ用インデックスファイルへのパス
 * @param string $threadStatesFile スレ状態ファイルへのパス
 * @param string $threadFile 通常ブラウザ用DATへのパス
 * @param string $datFile 専ブラ用DATへのパス
 * @param string $thread スレ番号
 * @param string $title スレッドタイトル
 * @param int $resNumber レス数
 * @param string $datlog datlogへのパス
 */
function archiveThread(
    $SETTING,
    $KAKOLOGLIST,
    $KAKOLOGLISTINDEX,
    $threadStatesFile,
    $threadFile,
    $datFile,
    $thread,
    $title,
    $resNumber,
    $datlog,
) {
    // 現行スレッドから削除
    @unlink($threadStatesFile);
    // 過去ログを保持しない場合
    if ($SETTING['disable_kakolog'] == "checked") {
        @unlink($threadFile);
        @unlink($datFile);
    } else {
        // 過去ログリストへ追記
        $kakologListHandle = fopen($KAKOLOGLIST, 'a+');
        if (flock($kakologListHandle, LOCK_EX)) {
            // 末尾位置取得
            fseek($kakologListHandle, 0, SEEK_END);
            $endOffset = ftell($kakologListHandle);
            // 追記
            $kakologLine = $thread.".dat<>".$title." (".$resNumber.")\n";
            fwrite($kakologListHandle, mb_convert_encoding($kakologLine, "SJIS-win", "UTF-8"));
        }
        fclose($kakologListHandle);
        // 過去ログインデックスへ追記
        if (isset($endOffset)) {
            file_put_contents($KAKOLOGLISTINDEX, $endOffset."\n", FILE_APPEND | LOCK_EX);
        }
    }
    // datlog削除
    if (is_file($datlog)) {
        @unlink($datlog);
    }
}
