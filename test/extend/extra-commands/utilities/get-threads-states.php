<?php

/**
 * スレ状態ファイル`/{$bbs}/threads-states/{スレ番号}.json`の内容を取得するための関数です。
 * 内容の更新を行いたいときにはThreadsStatesUpdaterクラスを使用してください。
 *
 * @param string $path `/{$bbs}/threads-states/{スレ番号}.json`へのパス
 * @return array|false スレ状態の連想配列あるいはfalse
 */
function getThreadsStates($path)
{
    $threadsStatesHandle = fopen($path, 'c+');
    if (!flock($threadsStatesHandle, LOCK_SH)) {
        return false;
    }
    clearstatcache();
    $resource = stream_get_contents($threadsStatesHandle);
    fclose($threadsStatesHandle);
    $data = json_decode($resource, true);
    if ($data === null) {
        return [];
    }
    return $data;
}
