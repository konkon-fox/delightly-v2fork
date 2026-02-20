<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);
ob_start();
if (function_exists('sys_getloadavg')) {
    $loadavg = sys_getloadavg(); //LoadAverageを取得
    if ($loadavg !== false) {
        // LA200以上は拒否
        if ($loadavg[0] > 200) {
            Error2('現在高負荷のため、bbs.cgiを一時的に停止しています。お手数ですが、Web版からの投稿をお願いします。 -> LoadAverage:' . $loadavg[0]);
        }
        if ($loadavg[0] > 50 && (empty($_POST['mail']) || strlen($_POST['mail']) !== 9) && (empty($_COOKIE['WrtAgreementKey']) || strlen($_COOKIE['WrtAgreementKey']) !== 8)) {
            finish();
        }
    }
}

// 専ブラ用なのでShift_JISで出力
header('Content-Type: text/html; charset=Shift_JIS');
if (!isset($_POST['bbs'])) {
    $_POST['bbs'] = '';
}
if (!isset($_POST['key'])) {
    $_POST['key'] = '';
}
if (!isset($_POST['MESSAGE'])) {
    $_POST['MESSAGE'] = '';
}
if (!isset($_POST['FROM'])) {
    $_POST['name'] = '';
}
if (!isset($_POST['mail'])) {
    $_POST['mail'] = '';
}
if (!isset($_POST['subject'])) {
    $_POST['subject'] = '';
}

require_once './utils/get-json-file.php';
require_once './extend/BbsDB.php';

$PATH = '../' . $_POST['bbs'] . '/';
$NOWTIME = time();

// 一部特殊なアプリが有るためv2ではMonazilla以外のUAも許容する。

// 投稿先の掲示板設定を取得
$setfile = $PATH . 'setting.json';
if (!is_file($setfile)) {
    Error2('This board does not exist.');
}
$SETTING = getJsonFile($setfile);
if ($SETTING === false) {
    Error2('板設定ファイルの取得に失敗しました。');
}

// 専ブラ投稿が許可されていない場合はここで拒否
if ($SETTING['2ch_dedicate_browsers'] !== 'enable') {
    Error2('invalid:2ch dedicate browsers is forbidden.');
}

// 専ブラなのにtimeなし
if (!$_POST['time']) {
    Error2('invalid');
}

// 一部の絵文字の後ろに?が付く不具合への対処
// ?の元であるバイトシーケンス「fc」をゼロ幅接合子(ZWJ)あるいは異体字セレクタ(VS16)のHTMLエンティティに置換
// 89 fc や 8a fc を含む文字列を除外するため、fcが4連続と2連続の場合を決め打ちしている
// 0-9 00-09
// * 2a
// # 23
// ♂ 81 89
// ♀ 81 8a
function emojiReplace($text)
{
    $html_entity = '&#[0-9a-zA-Z]+?;';
    $emoji_base_chars = '(?:[0-9*#]|♂|♀)';
    $byte_fc = '(?:\xFC)';
    $zwj_regexp = "/({$html_entity}|{$emoji_base_chars}){$byte_fc}{4}/";
    $text = preg_replace($zwj_regexp, '$1&#65039;&#8205;', $text);
    $vs16_regexp = "/({$html_entity}|{$emoji_base_chars}){$byte_fc}{2}/";
    $text = preg_replace($vs16_regexp, '$1&#65039;', $text);
    return $text;
}
$_POST['MESSAGE'] = emojiReplace($_POST['MESSAGE']);
$_POST['subject'] = emojiReplace($_POST['subject']);
$_POST['FROM'] = emojiReplace($_POST['FROM']);

// Shift_JISからUTF-8へ
mb_convert_variables('UTF-8', 'SJIS-win', $_POST);

$_POST['comment'] = $_POST['MESSAGE'];
$_POST['title'] = $_POST['subject'];
$_POST['name'] = $_POST['FROM'];
$_POST['board'] = $_POST['bbs'];
$_POST['thread'] = $_POST['key'];

require 'bbs-main.php';

// 投稿完了画面
function finish()
{
    ?><html><head><title>書きこみました。</title><meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS"></head><body>書きこみが終わりました。<br><br>画面を切り替えるまでしばらくお待ち下さい。</body></html>
<?php exit;
}

// 各種規制など(専ブラ向けに整形)
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
    ?><head>
<title>ＥＲＲＯＲ！</title>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
</head>
<body bgcolor="#FFFFFF">
<font size=+1 color=#FF0000><b>ERROR: <?php echo mb_convert_encoding($error, 'SJIS-win', 'UTF-8'); ?></b></font>
</body>
</html>
<?php exit;
}

// その他のエラー
function Error2($error)
{
    ?><head>
<title>ＥＲＲＯＲ！</title>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
</head>
<body bgcolor="#FFFFFF">
<font size=+1 color=#FF0000><b>ERROR: <?=$error;?></b></font>
</body>
</html>
<?php exit;
}

function makeDir($path)
{
    return is_dir($path) || mkdir($path, 0777, true);
}
