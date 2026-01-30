<?php

error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);
ob_start();
header('Accept-CH: Sec-CH-UA-Arch, Sec-CH-UA-Bitness, Sec-CH-UA-Full-Version-List, Sec-CH-UA-Mobile, Sec-CH-UA-Model, Sec-CH-UA-Platform, Sec-CH-UA-Platform-Version');
header('Content-Type: text/html; charset=UTF-8');
if (!isset($_POST['board'])) {
    $_POST['board'] = '';
}
if (!isset($_POST['thread'])) {
    $_POST['thread'] = '';
}
if (!isset($_POST['comment'])) {
    $_POST['comment'] = '';
}
if (!isset($_POST['name'])) {
    $_POST['name'] = '';
}
if (!isset($_POST['mail'])) {
    $_POST['mail'] = '';
}
if (!isset($_POST['title'])) {
    $_POST['title'] = '';
}

require_once './utils/get-json-file.php';
require_once './extend/BbsDB.php';

$PATH = '../' . $_POST['board'] . '/';
$NOWTIME = time();

// User-AgentにMozilla/5.0を含まない場合は拒否
if (strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla/5.0') === false) {
    Error2('invalid');
}

// 投稿先の掲示板設定を取得
$setfile = $PATH . 'setting.json';
if (!is_file($setfile)) {
    Error2('This board does not exist.');
}
$SETTING = getJsonFile($setfile);
if ($SETTING === false) {
    Error2('板設定ファイルの取得に失敗しました。');
}

require 'bbs-main.php';

// 投稿完了画面
function finish()
{
    global $NOWTIME,$tlonly;
    if ($tlonly) {
        header('Location: /' . $_POST['board'] . '/');
    } else {
        header('Location: /#' . $_POST['board'] . '/' . $_POST['thread'] . '/');
    }
    setcookie('response', 'success', $NOWTIME + 5, '/');
    exit;
}

// エラーメッセージ表示用関数
function Error($error)
{
    global $NOWTIME,$PATH,$HOST,$DATE,$ID,$WrtAgreementKey,$number,$CH_UA,$ACCEPT,$accountId,$LV,$info,$HAP,$subject,$clientId;
    $HAP ??= [];
    $bbs = basename($_POST['board']);
    $db = new BbsDB(dirname(__FILE__, 2) . '/' . $bbs);
    if ($db->isSQLiteMode()) {
        // DBへ保存
        $data = [
            'error' => $error,
            'name' => $_POST['name'] ?? 'unknown',
            'mail' => $_POST['mail'] ?? 'unknown',
            'date_id' => $DATE . ' ' . ($ID ?? 'unknown') ,
            'comment' => $_POST['comment'] ?? 'unknown',
            'title' => $subject ?? 'unknown',
            'thread' => $_POST['thread'] ?? 'unknown',
            'number' => $number ?? 0,
            'host' => $HOST ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'ch_ua' => $CH_UA ?? 'unknown',
            'accept' => $ACCEPT ?? 'unknown',
            'client_id' => $clientId ?? 'unknown',
            'lv' => $LV ?? 0,
            'port' => $_SERVER['REMOTE_PORT'] ?? 'unknown',
            'cf_ipcountry' => $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'unknown',
            'hap_ip' => $HAP['REMOTE_ADDR'] ?? 'unknown',
            'hap_area' => ($HAP['HOST'] ?? 'unknown') . ($HAP['country'] ?? 'unknown') . ($HAP['region'] ?? 'unknown') . ' ' . ($HAP['provider'] ?? 'unknown'),
            'hap_slip' => ($HAP['SLIP_NAME'] ?? 'unknown') . ' ' . ($HAP['USER_AGENT'] ?? 'unknown') . ($HAP['CH_UA'] ?? 'unknown') . ($HAP['ACCEPT'] ?? 'unknown'),
            'account_id' => $accountId ?? 'unknown',
            'posted_at' => $NOWTIME ?? time(),
        ];
        $db->addToErrorLog($data);
    } else {
        // ファイル形式
        // エラーログに保存
        if (is_file($PATH . 'errors.cgi')) {
            $EROG = file($PATH . 'errors.cgi');
        } else {
            $EROG = [];
        }
        array_unshift($EROG, $error . '<>' . $_POST['name'] . '<>' . $_POST['mail'] . '<>' . $DATE . ' ' . $ID . '<>' . $_POST['comment'] . '<>' . $_POST['title'] . '<>' . $_POST['thread'] . '<>' . $number . '<>' . $HOST . '<>' . $_SERVER['REMOTE_ADDR'] . '<>' . $_SERVER['HTTP_USER_AGENT'] . '<>' . $CH_UA . '<>' . $ACCEPT . '<>' . $WrtAgreementKey . '<>' . $LV . '<>' . $info . "\n");
        // 500 個以内に調整して保存
        while (count($EROG) > 500) {
            array_pop($EROG);
        }
        $EROG = array_unique($EROG);
        $fp = @fopen($PATH . 'errors.cgi', 'w');
        foreach ($EROG as $tmp) {
            fputs($fp, $tmp);
        }
        fclose($fp);
    }

    http_response_code(403);
    setcookie('response', mb_convert_encoding($error, 'HTML-ENTITIES', 'UTF-8'), $NOWTIME + 5, '/');
    exit($error);
}
function Error2($error)
{
    global $NOWTIME;
    Header('HTTP/1.0 403 Forbidden');
    setcookie('response', mb_convert_encoding($error, 'HTML-ENTITIES', 'UTF-8'), $NOWTIME + 5, '/');
    exit($error);
}

function makeDir($path)
{
    return is_dir($path) || mkdir($path, 0777, true);
}
