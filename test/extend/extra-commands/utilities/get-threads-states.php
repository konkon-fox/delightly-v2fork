<?php
/**
 * スレ情報ファイルthreads-states.cgiの内容を取得するための関数です。
 * 内容の更新を行いたいときにはThreadsStatesUpdaterクラスを使用してください。
 *
 * @param string $path threads-states.cgiへのパス
 * @return array|false スレ状態の連想配列あるいはfalse
 */
function getThreadsStates($path)
{
    $threadsStatesHandle = fopen($path, 'r');
    if(!flock($threadsStatesHandle, LOCK_SH)) {
        return false;
    }
    clearstatcache();
    $resource = fread($threadsStatesHandle, filesize($path));
    fclose($threadsStatesHandle);
    return json_decode($resource, true);
}
