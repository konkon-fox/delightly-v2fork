<?php

/**
 * JSONファイルを開く関数
 *
 * @param string $path ファイルへのパス
 * @return array|false 成功時は配列、失敗時はfalse
 */
function getJsonFile(
    $path
) {
    if(!is_file($path)){
        return false;
    }
    $fp = fopen($path, 'r');
    if($fp===false){
        return false;
    }
    if(!flock($fp, LOCK_SH)){
        fclose($fp);
        return false;
    }
    $content = stream_get_contents($fp);
    if($content ===false){
        flock($fp, LOCK_UN);
        fclose($fp);
        return false;
    }
    $json = json_decode($content, true);
    flock($fp, LOCK_UN);
    fclose($fp);
    if($json === false || $json===null){
        return false;
    }
    return $json;
}
