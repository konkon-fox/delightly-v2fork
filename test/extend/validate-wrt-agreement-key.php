<?php

require_once __DIR__ . '/SystemDB.php';

/**
 * 投稿ログを記録する関数
 *
 * @param int $NOWTIME 投稿時刻
 *
 * @return array{account_id:string, hap_file:string}
 */
function validateWrtAgreementKey(
    $NOWTIME
) {
    $db = new SystemDB();
    // 同意鍵
    if (empty($_COOKIE['WrtAgreementKey'])) {
        $_COOKIE['WrtAgreementKey'] = str_replace('#', '', $_POST['mail']);
    }
    $data = [
        'date' => date('Y-m-d H:i:s', $NOWTIME),
        'key' => $_COOKIE['WrtAgreementKey'] ?? '',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'host' => $HOST ?? 'unknown',
        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'posted_at' => $NOWTIME,
        'mail' => $_POST['mail'],
        'wrt_agreement_key' => $_COOKIE['WrtAgreementKey'] ?? null,
        'wrt_agreement_key_sig' => $_COOKIE['wrt_agreement_key_sig'] ?? null,
    ];
    try {
        /** @var array{account_id:string, hap_file:string, signature: string}|false */
        $result = $db->checkWrtAgreementKey($data);
    } catch (Exception $e) {
        Error('DBの参照に失敗しました。');
    }
    if ($result === false) {
        http_response_code(403);
        Error('投稿するには同意が必要です。<br><a href="https://' . $_SERVER['HTTP_HOST'] . '/test/auth.php">https://' . $_SERVER['HTTP_HOST'] . '/test/auth.php</a>');
    }

    // クッキー更新
    setcookie('WrtAgreementKey', $_COOKIE['WrtAgreementKey'], $NOWTIME + 31536000, '/');
    setcookie('wrt_agreement_key_sig', $result['signature'], $NOWTIME + 31536000, '/');

    return [
        'account_id' => $result['account_id'],
        'hap_file' => $result['hap_file'],
    ];
}
