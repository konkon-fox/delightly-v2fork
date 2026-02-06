<?php

require_once __DIR__ . '/BbsDB.php';

/**
 * 投稿ログを記録する関数
 *
 * @param array $SETTING 板の設定
 * @param string $LOGFILE LOG.cgiへのパス
 * @param string $DATE 日付
 * @param string $ID 投稿ID
 * @param string $subject スレタイ
 * @param int $number レス数
 * @param string $CH_UA client hints ua
 * @param string $HOST ホスト
 * @param string $ACCEPT accept
 * @param string $clientId client ID
 * @param int $LV レベル
 * @param string $info hap情報
 * @param array $HAP ユーザーデータ
 * @param string $accountId hapファイル名
 * @param int $NOWTIME 投稿時刻
 */
function recordPostLog(
    $SETTING,
    $LOGFILE,
    $DATE,
    $ID,
    $subject,
    $number,
    $CH_UA,
    $HOST,
    $ACCEPT,
    $clientId,
    $LV,
    $info,
    $HAP,
    $accountId,
    $NOWTIME
) {
    $db = new BbsDB(dirname(__FILE__, 3) . '/' . $_POST['board']);
    if ($db->isSQLiteMode()) {
        $data = [
            'name' => $_POST['name'] ,
            'mail' => $_POST['mail'] ,
            'date_id' => $DATE . ' ' . $ID ,
            'comment' => $_POST['comment'],
            'title' => $subject,
            'thread' => $_POST['thread'],
            'number' => $number,
            'host' => $HOST,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'ch_ua' => $CH_UA,
            'accept' => $ACCEPT,
            'client_id' => $clientId,
            'lv' => $LV,
            'port' => $_SERVER['REMOTE_PORT'] ?? 'unknown',
            'cf_ipcountry' => $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'unknown',
            'hap_ip' => $HAP['REMOTE_ADDR'],
            'hap_area' => $HAP['HOST'] . $HAP['country'] . $HAP['region'] . ' ' . $HAP['provider'],
            'hap_slip' => $HAP['SLIP_NAME'] . ' ' . $HAP['USER_AGENT'] . $HAP['CH_UA'] . $HAP['ACCEPT'],
            'account_id' => $accountId,
            'posted_at' => $NOWTIME,
        ];
        $db->addToPostLog($data);
    } else {// ファイル形式
        $LOG_LIMIT = 10000;
        if ($SETTING['LOG_LIMIT'] !== '') {
            $LOG_LIMIT = min((int) $SETTING['LOG_LIMIT'], $LOG_LIMIT);
            if ($LOG_LIMIT < 0) {
                $LOG_LIMIT = 0;
            }
        }
        $logFileHandle = fopen($LOGFILE, 'a+');
        if (flock($logFileHandle, LOCK_EX)) {
            // 新規ログを追記
            $newLog = $_POST['name'] . '<>' . $_POST['mail'] . '<>' . $DATE . ' ' . $ID . '<>' . $_POST['comment'] . '<>' . $subject . '<>' . $_POST['thread'] . '<>' . $number . '<>' . $HOST . '<>' . $_SERVER['REMOTE_ADDR'] . '<>' . $_SERVER['HTTP_USER_AGENT'] . '<>' . htmlspecialchars($CH_UA, ENT_NOQUOTES, 'UTF-8') . '<>' . htmlspecialchars($ACCEPT, ENT_NOQUOTES, 'UTF-8') . '<>' . $clientId . '<>' . $LV . '<>' . $info . "\n";
            fwrite($logFileHandle, $newLog);
            // ログの行数確認
            rewind($logFileHandle);
            for ($lineCount = 0; fgets($logFileHandle); $lineCount++);
            if ($lineCount > $LOG_LIMIT + 100) {
                // ログ縮小処理用にファイルを開き直す
                flock($logFileHandle, LOCK_UN);
                fclose($logFileHandle);
                $logFileHandle = fopen($LOGFILE, 'c+');
                // 古いログを削除
                if (flock($logFileHandle, LOCK_EX)) {
                    $logLines = [];
                    while (($logLine = fgets($logFileHandle)) !== false) {
                        $logLines[] = $logLine;
                    }
                    $logLines = array_slice($logLines, $lineCount - $LOG_LIMIT);
                    ftruncate($logFileHandle, 0);
                    rewind($logFileHandle);
                    fwrite($logFileHandle, implode('', $logLines));
                }
            }
        }
        flock($logFileHandle, LOCK_UN);
        fclose($logFileHandle);
    }
}

recordPostLog(
    $SETTING,
    $LOGFILE,
    $DATE,
    $ID,
    $subject,
    $number,
    $CH_UA,
    $HOST,
    $ACCEPT,
    $clientId,
    $LV,
    $info,
    $HAP,
    $accountId,
    $NOWTIME
);
