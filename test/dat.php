<?php

// gzip圧縮を無効化
ini_set('zlib.output_compression', 'Off');

if (!isset($_GET['bbs'])) {
    http_response_code(403);
    exit('bbsが指定されていません。');
}
if (!isset($_GET['thread'])) {
    http_response_code(403);
    exit('threadが指定されていません。');
}

$bbs = basename($_GET['bbs']);
$thread = basename($_GET['thread']);

// 板のパス
$bbsPath = dirname(__DIR__, 1) . '/' . $bbs;

// 現行スレ判定
$threadStates = "{$bbsPath}/threads-states/{$thread}.json";

// 現行dat
$currentFile = "{$bbsPath}/dat/{$thread}.dat";
// 過去ログdat
$dir1 = substr($thread, 0, 4);
$dir2 = substr($thread, 0, 5);
$kakoDir = "{$bbsPath}/kako/{$dir1}/{$dir2}";
$kakoFile = "{$kakoDir}/{$thread}.dat";

// 過去ログの場合
if (!is_file($threadStates) && $thread !== '1000000000') {

    // 過去ログフォルダがなければ作成
    if (!is_dir($kakoDir)) {
        if (!@mkdir($kakoDir, 0775, true) && !file_exists($kakoDir)) {
            http_response_code(500);
            echo '過去ログフォルダの作成に失敗しました';
            exit;
        }
    }

    // 現行フォルダにあれば過去ログフォルダへ移動
    if (is_file($currentFile)) {
        rename($currentFile, $kakoFile);
    }

    // 過去ログdatへリダイレクト
    $targetUrl = "/{$bbs}/kako/{$dir1}/{$dir2}/{$thread}.dat";
    header('Location: ' . $targetUrl, true, 302);
    exit;
}

// 404
if (!is_file($currentFile)) {
    http_response_code(404);
    exit;
}

// 現行スレの場合

// レスポンスヘッダー設定
header('Content-Type: text/plain; charset=Shift_JIS');
header('Accept-Ranges: bytes');

// etag
$fileTime = filemtime($currentFile);
$fileSize = filesize($currentFile);
$etag = md5($fileTime . $fileSize);
header('ETag: "' . $etag . '"');

// 304 etag
if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
    if (trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') === $etag) {
        http_response_code(304);
        exit;
    }
}

// 304 lastmodified
$lastModified = gmdate('D, d M Y H:i:s', $fileTime) . ' GMT';
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    $ifModifiedSince = trim($_SERVER['HTTP_IF_MODIFIED_SINCE']);
    if ($fileTime <= strtotime($ifModifiedSince)) {
        header('Last-Modified: ' . $lastModified);
        http_response_code(304);
        exit;
    }
}
header('Last-Modified: ' . $lastModified);

// Range要求に対応
$rangeEnd = $fileSize - 1;
$rangeStart = 0;
$rangeSize = null;
if (isset($_SERVER['HTTP_RANGE'])) {
    preg_match('/bytes=([0-9]+)-/', $_SERVER['HTTP_RANGE'], $matches);
    $rangeStart = (int) ($matches[1] ?? 0);
    if ($rangeStart <= $rangeEnd) {
        $rangeSize = $fileSize - $rangeStart;
        http_response_code(206);
        header("Content-Range: bytes {$rangeStart}-{$rangeEnd}/{$fileSize}");
    } else {
        $rangeStart = 0;
    }
}
$contentLength = $rangeSize ?? $fileSize;
header('Content-Length: ' . $contentLength);

// 現行datを返却
$fp = fopen($currentFile, 'rb');
if (!$fp) {
    http_response_code(503);
    exit;
}
if (!flock($fp, LOCK_SH)) {
    fclose($fp);
    http_response_code(503);
    exit;
}
if ($rangeStart > 0) {
    fseek($fp, $rangeStart);
}
fpassthru($fp);
flock($fp, LOCK_UN);
fclose($fp);
exit;
