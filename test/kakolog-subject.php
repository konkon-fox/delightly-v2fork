<?php

header('Content-Type: text/plain; charset=Shift_JIS');
header('Connection: keep-alive');

if (!isset($_GET['bbs'])) {
    $line = 'bbsが指定されていません。';
    $errorData = mb_convert_encoding($line, 'SJIS-win', 'UTF-8');
    http_response_code(403);
    echo $errorData;
    exit;
}

if (!isset($_GET['time'])) {
    $line = 'timeが指定されていません。';
    $errorData = mb_convert_encoding($line, 'SJIS-win', 'UTF-8');
    http_response_code(403);
    echo $errorData;
    exit;
}

if (!isset($_GET['sig'])) {
    $line = 'sigが指定されていません。';
    $errorData = mb_convert_encoding($line, 'SJIS-win', 'UTF-8');
    http_response_code(403);
    echo $errorData;
    exit;
}

require_once './utils/safe-file-get-contents.php';
require_once './extend/KakologDB.php';

$bbs = basename($_GET['bbs']);
$time = (int) $_GET['time'];

// 時間制限
if ($time + 60 < time()) {
    http_response_code(403);
    exit;
}

// シークレットキーを入手
$secretKeyPath = './secret-key.txt';
if (!is_file($secretKeyPath)) {
    http_response_code(500);
    exit;
}
$rawSecretKey = safe_file_get_contents($secretKeyPath);
if ($rawSecretKey === false) {
    http_response_code(503);
    exit;
}
$secretKey = trim($rawSecretKey);

// 署名を検証
$expectedSig = hash_hmac('sha256', $time, $secretKey);
if ($expectedSig !== $_GET['sig']) {
    http_response_code(403);
    exit;
}

// ------------------------------------------
// ここからSQLite使用判定ブロック
// ------------------------------------------
$bbsPath = dirname(__FILE__, 2) . '/' . $bbs;
$db = new KakologDB($bbsPath);

if ($db->isSQLiteMode()) {
    // --------------------
    // SQLiteモード(v4以降)
    // --------------------

    // Last-Modified チェック
    $fileTime = $db->getFileTime();
    $lastModified = gmdate('D, d M Y H:i:s', $fileTime) . ' GMT';
    $ifModifiedSince = trim($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '');
    // ※キャッシュを返した場合 $fileTime < strtotime($ifModifiedSince) になる可能性があるので <= は使わない
    if (!empty($ifModifiedSince) && $fileTime === strtotime($ifModifiedSince)) {
        header('Last-Modified: ' . $lastModified);
        http_response_code(304);
        exit;
    }

    // 最大件数
    $ITEMS_PER_PAGE = 200000;
    // 検索オプション作成
    $options = [
        'page' => 1,
        'per_page' => $ITEMS_PER_PAGE,
    ];

    // 結果
    $result = $db->search($options);
    if ($result === false) {
        $line = '過去ログの取得に失敗しました。';
        $errorData = mb_convert_encoding($line, 'SJIS-win', 'UTF-8');
        http_response_code(503);
        echo $errorData;
        exit;
    }
    $logs = $result['logs'];
    $lines = array_map(function ($log) {
        return sprintf(
            "%d.dat<>%s (%d)\n",
            $log['thread'],
            $log['title'],
            $log['res']
        );
    }, $logs);
    $data = mb_convert_encoding(implode('', $lines), 'SJIS-win', 'UTF-8');

    // 最終返却
    header('Last-Modified: ' . $lastModified);
    echo $data;
    exit;
} else {
    // --------------------
    // ファイルモード(v3)
    // --------------------

    // 過去ログファイルをチェック
    $kakologSubject = "../{$bbs}/kakolog-subject.txt";
    if (!is_file($kakologSubject)) {
        http_response_code(404);
        exit;
    }

    // Last-Modified チェック
    $fileTime = filemtime($kakologSubject);
    $lastModified = gmdate('D, d M Y H:i:s', $fileTime) . ' GMT';
    $ifModifiedSince = trim($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '');
    // ※キャッシュを返した場合 $fileTime < strtotime($ifModifiedSince) になる可能性があるので <= は使わない
    if (!empty($ifModifiedSince) && $fileTime === strtotime($ifModifiedSince)) {
        header('Last-Modified: ' . $lastModified);
        http_response_code(304);
        exit;
    }

    // 最新版を取得
    $rangeBytes = 20 * 1000 * 1000; // 上限を20MBに設定
    $fp = fopen($kakologSubject, 'rb');
    if (!$fp) {
        http_response_code(503);
        exit;
    }
    if (!flock($fp, LOCK_SH)) {
        fclose($fp);
        http_response_code(503);
        exit;
    }
    $fileSize = filesize($kakologSubject);
    if ($fileSize > $rangeBytes) {
        // 部分返却
        header('Delightly-Subject-Truncated: true');
        $offset = -$rangeBytes;
    } else {
        // 全体返却
        header('Delightly-Subject-Truncated: false');
        $offset = -$fileSize;
    }
    fseek($fp, $offset, SEEK_END);
    $data = fread($fp, $rangeBytes);
    flock($fp, LOCK_UN);
    fclose($fp);

    // 最終返却
    header('Last-Modified: ' . $lastModified);
    echo $data;
    exit;
}
