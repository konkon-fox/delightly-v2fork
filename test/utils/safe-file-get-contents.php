<?php

/**
 * 安全にファイルを開いて中身を文字列で返す関数
 *
 * @param string $path ファイルへのパス
 * @return string|false 成功時は文字列、失敗時はfalse
 */
function safe_file_get_contents(
    $path
) {
    if (!is_file($path)) {
        return false;
    }
    $fp = fopen($path, 'r');
    if ($fp === false) {
        return false;
    }
    if (!flock($fp, LOCK_SH)) {
        fclose($fp);
        return false;
    }
    $content = stream_get_contents($fp);
    if ($content === false) {
        flock($fp, LOCK_UN);
        fclose($fp);
        return false;
    }
    flock($fp, LOCK_UN);
    fclose($fp);
    return $content;
}
