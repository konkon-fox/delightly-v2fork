<?php

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
if (!isset($_SERVER['HTTP_SEC_CH_UA'])) {
    $_SERVER['HTTP_SEC_CH_UA'] = '';
}
if (!isset($_SERVER['HTTP_SEC_CH_UA_PLATFORM'])) {
    $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] = '';
}
if (!isset($_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'])) {
    $_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'] = '';
}
if (!isset($_SERVER['HTTP_SEC_CH_UA_BITNESS'])) {
    $_SERVER['HTTP_SEC_CH_UA_BITNESS'] = '';
}
if (!isset($_SERVER['HTTP_SEC_CH_UA_ARCH'])) {
    $_SERVER['HTTP_SEC_CH_UA_ARCH'] = '';
}
if (!isset($_SERVER['HTTP_SEC_CH_UA_MODEL'])) {
    $_SERVER['HTTP_SEC_CH_UA_MODEL'] = '';
}
if (!isset($_SERVER['HTTP_SEC_CH_UA_MOBILE'])) {
    $_SERVER['HTTP_SEC_CH_UA_MOBILE'] = '';
}
if (!isset($_SERVER['HTTP_SEC_CH_UA_FULL_VERSION_LIST'])) {
    $_SERVER['HTTP_SEC_CH_UA_FULL_VERSION_LIST'] = '';
}
if (isset($SETTING['date_comma_digit']) && $SETTING['date_comma_digit'] !== '0') {
    $NOWMICROTIME = microtime(true);
    $microTime = $NOWMICROTIME - floor($NOWMICROTIME);
    $commaDigit = (int) $SETTING['date_comma_digit'];
    $commaTime = floor($microTime * pow(10, $commaDigit));
    $commaTime = sprintf('%0'.$commaDigit.'d', $commaTime);
    $DATE = date('Y/m/d', $NOWMICROTIME);
    $TIME = date('H:i:s', $NOWMICROTIME).'.'.$commaTime;
} else {
    $DATE = date('Y/m/d', $NOWTIME);
    $TIME = date('H:i:s', $NOWMICROTIME);
}
if (file_exists(__DIR__ . '/.use_cloudflare') && isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}
$HOST = gethostbyaddr($_SERVER['REMOTE_ADDR']);
$subjectfile = $PATH.'subject.json';	//スレッド一覧
$LTLFILE = $PATH.'index.json';	//ローカルタイムライン
$LOGFILE = $PATH.'LOG.cgi';	//投稿ログ・検索用
$THREADFILE = $PATH.'thread/'.substr($_POST['thread'], 0, 4).'/'.$_POST['thread'].'.dat';	//UTF-8 read.html用
$DATFILE = $PATH.'dat/'.$_POST['thread'].'.dat';	//Shift_JIS 専ブラ用 ※過去ログ用に保持
$KAKOLOGLIST = $PATH.'kakolog-subject.txt';  // 過去ログ一覧
$KAKOLOGLISTINDEX = $PATH.'kakolog-subject.idx';  // 過去ログ一覧のインデックスファイル
$THREAD_STATES_PATH = $PATH.'threads-states';  // スレ状態ファイル用のフォルダ 現行スレ判定にも使用
# 記録ファイルが設置された場所。
$HAP_PATH = './HAP/';
mb_substitute_character('entity');
$M = $ken = $ncolor = $Cookmail = $LV = $CAPID = $accountid = $supervisorID = '';
$stop = $admin = $sage = $supervisor = $authorized = $PROXY = $threadStatesReload = false;

include './utils/safe-file-get-contents.php';
include './utils/safe-file.php';
include './utils/get-json-file.php';

// GETメソッド
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Error2('invalid:GET');
}

// 有り得ないPOST情報or本文空
if (($_POST['title'] && $_POST['thread']) || !$_POST['board'] || strlen($_POST['comment']) === 0) {
    Error2('invalid:1');
}

// キーが数字でない
if (preg_match("/\D/", $_POST['thread'])) {
    Error2('invalid:2');
}

// UserAgentに全角文字が混じっているor極端に短い・長いor本来入らない文字が混入している
if (strlen($_SERVER['HTTP_USER_AGENT']) !== mb_strlen($_SERVER['HTTP_USER_AGENT'], 'UTF-8') || strlen($_SERVER['HTTP_USER_AGENT']) < 7 || strlen($_SERVER['HTTP_USER_AGENT']) > 384 || preg_match("/[^a-zA-Z0-9\-\/\.\(\):;,_\s]/", $_SERVER['HTTP_USER_AGENT'])) {
    Error2('invalid:3');
}

if (strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla/5.0') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'Monazilla/1.00') === false) {
    Error2('invalid:4');
}

// refererが無い
// 開発環境ではスキップ
if (!getenv('SKIP_VERIFICATION')) {
    if (empty($_SERVER['HTTP_REFERER'])) {
        Error2('invalid:5');
    } else {
        if ($_SERVER['HTTP_HOST'] !== $_SERVER['SERVER_NAME']) {
            Error2('invalid:host');
        }
        if (!stristr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) {
            Error2('invalid:ref1');
        }
    }
}

// IPv6に対応したサーバ用
$count_semi = substr_count($_SERVER['REMOTE_ADDR'], ':');
$count_dot = substr_count($_SERVER['REMOTE_ADDR'], '.');
if ($count_semi > 0 && $count_dot === 0) {
    $ipv6 = true;
} else {
    $ipv6 = false;
}
// IPアドレス範囲
if ($ipv6 === true) {
    $d = explode(':', $_SERVER['REMOTE_ADDR']);
    // IPv6では後半を切り捨て
    $IP_ADDR = $d[0].':'.$d[1].':'.$d[2].':'.$d[3];
    $range = $d[0].':'.$d[1].':'.substr($d[2], 0, 2);
    $sliphost = $d[0].':'.$d[1];
} else {
    $d = explode('.', $_SERVER['REMOTE_ADDR']);
    $IP_ADDR = $_SERVER['REMOTE_ADDR'];
    $range = $d[0];
    $sliphost = preg_replace('/[0-9]/', '', $HOST);
}

// 特殊な文字等変換
function escapePostData(&$postData, $keepNewLine)
{
    // 専ブラからの絵文字は数値実体参照で送られてくるのでhtmlspecialcharsは不可
    $postData = preg_replace('/&(?!#[a-zA-Z0-9]+;)/', '&amp;', $postData);
    $postData = str_replace('<', '&lt;', $postData);
    $postData = str_replace('>', '&gt;', $postData);
    $postData = str_replace('"', '&quot;', $postData);
    $postData = str_replace("'", '&apos;', $postData);
    // &#10;(LF) &#13;(CR) をエスケープ
    $postData = preg_replace('/&#0*1[03];/', ' ', $postData);
    // &#x0a;(LF) &#x0d;(CR) をエスケープ
    $postData = preg_replace('/&#[xX]0*[aAdD];/', ' ', $postData);
    // 改行コードをエスケープ ※本文のみ<br>に変換
    $newLineChar = $keepNewLine ? '<br>' : ' ';
    $postData = str_replace(["\r\n","\r","\n"], $newLineChar, $postData);
    // trim
    $postData = trim($postData);
}
escapePostData($_POST['title'], false);
escapePostData($_POST['name'], false);
escapePostData($_POST['mail'], false);
escapePostData($_POST['comment'], true);
$_POST['board'] = str_replace(['.','/','|'], '', $_POST['board']);
$_POST['thread'] = str_replace(['.','/','|'], '', $_POST['thread']);
$msgbr = explode('<br>', $_POST['comment']);

// スレ立て時の判定
if ($newthread) {
    // 先頭と末尾の空白文字を削除
    $_POST['title'] = trim($_POST['title']);
    // trim後のスレタイが空文字ならerror
    if ($_POST['title'] === '') {
        Error2('invalid:1');
    }
}

// 変換
if ($SETTING['change_sakujyo'] === 'checked') {
    $_POST['name'] = str_replace('管理', '"管理"', $_POST['name']);
    $_POST['name'] = str_replace('削除', '"削除"', $_POST['name']);
    $_POST['name'] = str_replace('sakujyo', '"sakujyo"', $_POST['name']);
}

// 全角＃のパス漏れ防止
$_POST['name'] = str_replace('＃', '#', $_POST['name']);
$_POST['mail'] = str_replace('＃', '#', $_POST['mail']);

// AAを自動検出
if ($SETTING['aa_check'] === 'checked') {
    if (strpos($_POST['comment'], '＿') !== false) {
        $aa = true;
    }
    if (strpos($_POST['comment'], '￣') !== false) {
        $aa = true;
    }
    if (strpos($_POST['comment'], '彡') !== false) {
        $aa = true;
    }
    if (strpos($_POST['comment'], 'Ｕ') !== false) {
        $aa = true;
    }
    if (strpos($_POST['comment'], '＜') !== false) {
        $aa = true;
    }
    if (strpos($_POST['comment'], '／') !== false) {
        $aa = true;
    }
    if (strpos($_POST['comment'], '＼') !== false) {
        $aa = true;
    }
    if (strpos($_POST['comment'], '┼') !== false) {
        $aa = true;
    }
    if (strpos($_POST['comment'], '┬') !== false) {
        $aa = true;
    }
    if (strpos($_POST['comment'], '::') !== false) {
        $aa = true;
    }
    if (strpos($_POST['comment'], '≡') !== false) {
        $aa = true;
    }
    if (strpos($_POST['comment'], ':.') !== false) {
        $aa = true;
    }
}

// 専ブラ用簡易スレッド作成
if ($_POST['thread'] === '1000000000' && strpos($msgbr[0], '!newthread') !== false) {
    $newthread = true;
    $_POST['title'] = str_replace('!newthread', '', $msgbr[0]);
    unset($msgbr[0]);
    $_POST['comment'] = implode('<br>', $msgbr);
    $msgbr = explode('<br>', $_POST['comment']);
}

// 新規スレッド作成
if ($_POST['title']) {
    $newthread = true;
} else {
    $newthread = false;
}

// タイムラインのみ
if (!$_POST['title'] && (!$_POST['thread'] || $_POST['thread'] === '1000000000')) {
    $tlonly = true;
} else {
    $tlonly = false;
}

// 偽キャップ、偽トリップ変換
$_POST['name'] = str_replace('★', '☆', $_POST['name']);
$_POST['name'] = preg_replace('/&#0*9733([^0-9]|$)/', '☆', $_POST['name']);
$_POST['name'] = preg_replace('/&#[xX]0*2605([^a-zA-Z0-9]|$)/', '☆', $_POST['name']);
$_POST['name'] = str_replace('◆', '◇', $_POST['name']);
$_POST['name'] = preg_replace('/&#0*9670([^0-9]|$)/', '◇', $_POST['name']);
$_POST['name'] = preg_replace('/&#[xX]0*25[cC]6([^a-zA-Z0-9]|$)/', '◇', $_POST['name']);

//名前欄Cookie
$Cookname = $_POST['name'];

// 数値文字参照をトリップと認識しないよう強引に置き換える
$_POST['name'] = str_replace('&#', '&!E', $_POST['name']);

// トリップ変換
if (preg_match("/([^\#]*)\#(.+)/", $_POST['name'], $tr)) {
    $_POST['name'] = $tr[1].nametrip('#'.$tr[2]);
}

// トリップ変換後元に戻す
$_POST['name'] = str_replace('&!E', '&#', $_POST['name']);

// 数値文字参照を検出
if (strpos($_POST['title'], '&#') !== false || strpos($_POST['comment'], '&#') !== false || strpos($_POST['name'], '&#') !== false) {
    $emoji = true;
}

// トリップ値を取り出す
list($_POST['name'], $trip) = explode('◆', $_POST['name']);

// トリップを表示する場合
if ($trip) {
    if ((strpos($_POST['name'], '!hide') === false && strpos($_POST['mail'], '!hide') === false && $SETTING['DISABLE_TRIP'] !== 'checked') || $SETTING['FORCE_DISP_TRIP'] === 'checked') {
        $_POST['name'] .= ' </b>◆'.$trip.' <b>';
    }
}
$_POST['name'] = str_replace('!hide', '', $_POST['name']);
$_POST['mail'] = str_replace('!hide', '', $_POST['mail']);

// 名前を非表示
if ($SETTING['DISABLE_NAME'] === 'checked') {
    $_POST['name'] = '';
}

// 同意鍵
if (empty($_COOKIE['WrtAgreementKey'])) {
    $_COOKIE['WrtAgreementKey'] = str_replace('#', '', $_POST['mail']);
}
if (!$_COOKIE['WrtAgreementKey']) {
    Error('投稿するには同意が必要です <a href="http://'.$_SERVER['HTTP_HOST'].'/test/auth.php">http://'.$_SERVER['HTTP_HOST'].'/test/auth.php</a>');
}
$clientid = hash('sha256', hash('sha256', md5($_COOKIE['WrtAgreementKey']).preg_replace('/[^0-9]/', '', md5($_COOKIE['WrtAgreementKey']))));
$hapfile = $HAP_PATH.$clientid.'.cgi'; // 新方式
if (!is_file($hapfile)) {
    $hapfile = $HAP_PATH.$_COOKIE['WrtAgreementKey'].'.cgi';
} // 旧方式の記録ファイル
if (!is_file($hapfile)) {
    Error('鍵が失効しています:'.$_COOKIE['WrtAgreementKey']);
}
setcookie('WrtAgreementKey', $_COOKIE['WrtAgreementKey'], $NOWTIME + 31536000, '/');
// 記録されたデータを取得
$HAP = getJsonFile($hapfile);
if ($HAP === false) {
    Error('ユーザーデータの取得に失敗しました。');
}
$WrtAgreementKey = substr(md5($HAP['range'].$HAP['provider'].$HAP['CH_UA'].$HAP['ACCEPT']), 0, 7);

// 指定Lv以上で自動承認
$ltime = $NOWTIME - $HAP['first'];
$LV = floor($ltime / 86400);
if ($HAP['slip'] !== '0') {
    $LV -= 1;
}
if ($HAP['country'] !== 'JP') {
    $LV -= 7;
}
if ($HAP['proxy']) {
    $LV -= 7;
}
if ($HAP['hosting'] || $HAP['slip'] === 'F' || $HAP['slip'] === 'H') {
    $LV -= 7;
}
if ($SETTING['auto_authorize_lv'] !== '' && $LV >= $SETTING['auto_authorize_lv']) {
    $authorized = true;
}

// 手動承認リスト
if (is_file($PATH.'authorize.cgi')) {
    $auth_str = safe_file($PATH.'authorize.cgi');
    foreach ($auth_str as $tmp) {
        $tmp = trim($tmp);
        if (!$tmp || strpos(substr($tmp, 0, 1), '#') !== false) {
            continue;
        }
        if ($WrtAgreementKey === $tmp || stristr($HOST, $tmp) !== false || stristr($_SERVER['REMOTE_ADDR'], $tmp) !== false) {
            $authorized = true;
            break;
        }
    }
}

// 掲示板のパスワード
if (preg_match("/([^\#]*)\#(.+)/", $_POST['mail'], $ca)) {
    $pass1 = safe_file_get_contents($PATH.'passfile.cgi');
    if ($pass1 === false) {
        Error('パスワードファイルの取得に失敗しました。');
    }
    if (password_verify($ca[2], $pass1)) {
        if ($_POST['name']) {
            $_POST['name'] .= '＠管理人 ★';
        } else {
            $_POST['name'] = '管理人 ★';
        }
        $admin = true;
        $authorized = true;
        $CAPID = 'administrator';
    }
}

// キャップパスワード
if (preg_match("/([^\#]*)\#(.+)/", $_POST['mail'], $ca)) {
    if (is_file($PATH.'cap.cgi')) {
        $cap_str = safe_file($PATH.'cap.cgi');
        if ($cap_str === false) {
            Error('CAPファイルの取得に失敗しました。');
        }
        foreach ($cap_str as $tmp) {
            $tmp = trim($tmp);
            if (!$tmp || strpos(substr($tmp, 0, 1), '#') !== false || strpos($tmp, '<>') === false) {
                continue;
            }
            list($name1, $pass1, $a1, $caid) = explode('<>', $tmp);
            if ($ca[2] === $pass1) {
                if ($a1 !== 'authorized') {
                    if ($_POST['name']) {
                        $_POST['name'] .= "＠$name1 ★";
                    } else {
                        $_POST['name'] = "$name1 ★";
                    }
                }
                if ($a1 !== 'plus' && $a1 !== 'authorized' && $a1 !== 'sakud') {
                    $admin = true;
                }
                if ($a1 !== 'plus' && $a1 !== 'sakud') {
                    $authorized = true;
                }
                if ($a1 === 'authorized') {
                    $WrtAgreementKey = $name1;
                }
                if ($caid) {
                    $CAPID = $caid;
                } elseif ($admin) {
                    $CAPID = 'CAP_USER';
                }
                break;
            }
        }
    }
}
if (preg_match("/([^\#]*)\#(.+)/", $_POST['mail'], $ca)) {
    $Cookmail = $_POST['mail'];
    $_POST['mail'] = $ca[1];
}
if (!$Cookmail) {
    $Cookmail = $_POST['mail'];
}

// User-Agent Client Hints
if ($_SERVER['HTTP_SEC_CH_UA_FULL_VERSION_LIST']) {
    $_SERVER['HTTP_SEC_CH_UA'] = $_SERVER['HTTP_SEC_CH_UA_FULL_VERSION_LIST'];
}
$CH_UA = $_SERVER['HTTP_SEC_CH_UA'].$_SERVER['HTTP_SEC_CH_UA_PLATFORM'].$_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'].$_SERVER['HTTP_SEC_CH_UA_BITNESS'].$_SERVER['HTTP_SEC_CH_UA_ARCH'].$_SERVER['HTTP_SEC_CH_UA_MODEL'].$_SERVER['HTTP_SEC_CH_UA_MOBILE'];
if (!$CH_UA) {
    $CH_UA = $_SERVER['HTTP_USER_AGENT'];
}
$ACCEPT = $_SERVER['HTTP_ACCEPT'].$_SERVER['HTTP_ACCEPT_LANGUAGE'].$_SERVER['CONTENT_TYPE'];

// smart phone marks + ID末尾
$SLIP_NAME = 'JP';
$slip = '0';
$SLIP_SP = $MM = $WF = false;
@include './extend/smartphonemarks.php';

// 新規スレッドの場合はスレッド番号を現在時刻に設定＆同時刻スレ立て規制
if ($newthread) {
    $_POST['thread'] = $NOWTIME;
    $THREADFILE = $PATH.'thread/'.substr($_POST['thread'], 0, 4).'/'.$_POST['thread'].'.dat';
    $DATFILE = $PATH.'dat/'.$_POST['thread'].'.dat';
    $subject = $_POST['title'];
    // 同じファイルが既にあった場合
    if (is_file($thread_file)) {
        $_POST['thread'] += 1;
    }
    // キャップユーザーのみスレッド作成可能とする
    if ($SETTING['Create_cap_only'] === 'checked' && !$admin) {
        Error('この掲示板はキャップユーザーのみスレッドを作成することができます');
    }
    // 承認済ユーザーのみスレッド作成可能とする
    if ($SETTING['Create_Authentication_required'] === 'checked' && !$authorized) {
        Error('この掲示板では承認済ユーザーのみスレッドを作成することができます');
    }
    // 主表示
    if ($SETTING['thread_supervisor'] === 'checked') {
        $supervisor = true;
        $supervisorID = substr(md5($_POST['thread'].$HAP['range'].$HAP['provider'].$HAP['CH_UA'].$HAP['ACCEPT']), 0, 8);
    }
} elseif (!$tlonly) {
    // スレッドタイトルを取得
    $THREADFILEHANDLE = fopen($THREADFILE, 'r');
    if (flock($THREADFILEHANDLE, LOCK_SH)) {
        rewind($THREADFILEHANDLE);
        for ($number = 0; $line = fgets($THREADFILEHANDLE); $number++) {
            if ($number === 0) {
                $firstRes = $line;
            }
        }
        fclose($THREADFILEHANDLE);
        list($firstResName, $firstResMail, $firstResDateId, $message, $subject) = explode('<>', $firstRes);
        if (strpos($firstResMail, substr(md5($_POST['thread'].$HAP['range'].$HAP['provider'].$HAP['CH_UA'].$HAP['ACCEPT']), 0, 8)) !== false) {
            $supervisor = true;
        }
        $subject = str_replace(["\r\n","\r","\n"], '', $subject);
    } else {
        fclose($THREADFILEHANDLE);
        Error('投稿に失敗しました。');
    }
}

// コマンド
$reload = false;
if (!$tlonly) {
    @include './extend/commands.php';
}

// 直前の投稿からtimeinterval秒投稿禁止
if ($SETTING['timeinterval'] && !$tlonly && !$newthread) {
    if ($NOWTIME < filemtime($THREADFILE) + $SETTING['timeinterval']) {
        Error('このスレッドでは直前の投稿から'.$SETTING['timeinterval'].'秒経たなければ投稿することができません');
    }
}

/**
 * スレ状態を管理するファイルです。
 * 現行スレ判定にも使用されます。
 * ファイルは`/{$bbs}/threads-steates/`以下に生成されます。
 *
 * @var string $THREAD_STATES_FILE
 * @var array{'774':string, 'gobi': string} $threadStates
 */
// $THREAD_STATES_FILE = $PATH.'threads-states.cgi'; 廃止
$THREAD_STATES_FILE = "{$THREAD_STATES_PATH}/{$_POST['thread']}.json";
include './extend/extra-commands/utilities/ThreadStatesUpdater.php';
$threadStatesUpdater = new ThreadStatesUpdater($THREAD_STATES_FILE);
$threadStates = $threadStatesUpdater->get();
if ($threadStates === false) {
    $threadStates = [];
}

// 現行スレフォルダ追加
if ($newthread) {
    if (!is_dir($THREAD_STATES_PATH)) {
        makeDir($THREAD_STATES_PATH, 0700, true);
    }
}

if (!$newthread && !$tlonly) {
    // スレッドファイルが無い
    if (!is_file($THREADFILE)) {
        Error('該当するスレッドがありません');
    }
    // 過去ログ
    if (!is_file($THREAD_STATES_FILE)) {
        Error('このスレッドは過去ログのため投稿できません');
    }
    // 強制sage
    if ($SETTING['BBS_FORCE_SAGE'] && $_POST['thread'] + $SETTING['BBS_FORCE_SAGE'] < $NOWTIME) {
        $sage = true;
    }
}

// システムメッセージ用関数
include './extend/extra-commands/utilities/add-system-message.php';
// スレ状態ファイル読み込み用関数 => $threadStatesUpdater に統合
// include './extend/extra-commands/utilities/get-threads-states.php';
// !ninkeyコマンド
@include './extend/extra-commands/ninkey.php';
// !chttコマンド
@include './extend/extra-commands/chtt.php';
// !774設定
@include './extend/extra-commands/set-774.php';
// !gobi設定
@include './extend/extra-commands/set-gobi.php';
// !rmjコマンド
@include './extend/extra-commands/rmj.php';
// !774適用
@include './extend/extra-commands/apply-774.php';
// !gobi適用
@include './extend/extra-commands/apply-gobi.php';
// スレ状態更新処理
@include './extend/extra-commands/utilities/show-threads-states.php';
// !xDy(dice)コマンド
@include './extend/extra-commands/dice.php';

// 曜日表示
$daysOfWeek = ['日', '月', '火', '水', '木', '金', '土'];
$dayOfWeek = $daysOfWeek[date('w')];
$DATE .= " ({$dayOfWeek}) {$TIME}";

// レス番号を取得
if (!$newthread && !$tlonly) {
    $number++;
} else {
    $number = 1;
}

// 上限超え
if (!$newthread && !$tlonly && $number > $SETTING['MAX_RES']) {
    Error('このスレッドに投稿できる上限を超えました');
}
if (!$newthread && !$tlonly && $number > $SETTING['MAX_RES'] - 2) {
    $stop = true;
}

// 画像禁止
if ($SETTING['NOPIC'] === 'checked' && (preg_match('/.(gif|jpg|jpeg|png)/', $_POST['comment']) || strpos($_POST['comment'], 'imgur.com') !== false) && !$authorized) {
    Error('この掲示板・スレッドでは未承認ユーザーは画像リンクを投稿することができません');
}

// リンク禁止
if ($SETTING['DISABLE_LINK'] && preg_match('/(https?|ttps?):\S+/', $_POST['comment']) && !$authorized) {
    Error('この掲示板・スレッドでは未承認ユーザーはリンクを投稿することができません');
}

// sage・強制sage
if (!$tlonly) {
    if (strpos($_POST['mail'], 'age') !== false && strpos($_POST['mail'], 'sage') === false) {
        $sage = false;
    } elseif (strpos($_POST['mail'], 'sage') !== false) {
        $sage = true;
    }
}

// キャップ必須
if ($SETTING['cap_only'] === 'checked' && !$admin) {
    Error('この掲示板はキャップユーザーのみ投稿することができます');
}

// 認証必須
if ($SETTING['Authentication_required'] === '1' && !$authorized) {
    Error('この掲示板・スレッドは承認済ユーザーのみ投稿することができます');
}

// ユニコード変換
if ($SETTING['BBS_UNICODE'] === 'deny') {
    if ($emoji) {
        Error('この掲示板・スレッドはUNICODE・絵文字の使用が禁止されています');
    }
} elseif ($SETTING['BBS_UNICODE'] === 'change') {
    $_POST['title'] = preg_replace("/\&\#\d+\;/", '？', $_POST['title']);
    $_POST['comment'] = preg_replace("/\&\#\d+\;/", '？', $_POST['comment']);
    $_POST['title'] = preg_replace("/\&\#x1F\d+\;/", '？', $_POST['title']);
    $_POST['comment'] = preg_replace("/\&\#x1F\d+\;/", '？', $_POST['comment']);
}

// 各種チェック
if (!$authorized) {
    if (!$newthread && count($msgbr) > $SETTING['BBS_LINE_NUMBER'] * 2) {
        Error('改行が多すぎます');
    }
    if (mb_strlen($_POST['comment'], 'UTF-8') > $SETTING['BBS_MESSAGE_COUNT']) {
        Error('本文が長すぎます。 (Check:'. mb_strlen($_POST['comment'], 'UTF-8').'/'.$SETTING['BBS_MESSAGE_COUNT'].')');
    }
    if (mb_strlen($_POST['name'], 'UTF-8') > $SETTING['BBS_NAME_COUNT']) {
        Error('名前が長すぎます');
    }
    if (mb_strlen($_POST['mail'], 'UTF-8') > $SETTING['BBS_MAIL_COUNT']) {
        Error('メールアドレスが長すぎます');
    }
    if (mb_strlen($_POST['title'], 'UTF-8') > $SETTING['BBS_SUBJECT_COUNT']) {
        Error('スレッドタイトルが長すぎます');
    }
    if (preg_match_all('/&gt;&gt;[0-9]/', $_POST['comment'], $matches) > $SETTING['BBS_LINE_NUMBER'] * 2) {
        Error('レスアンカーリンクの個数が多すぎます');
    }
} else {
    $maxkaigy = $SETTING['BBS_LINE_NUMBER'] * 3;
    $maxmsg = $SETTING['BBS_MESSAGE_COUNT'] * 3;
    $maxname = $SETTING['BBS_NAME_COUNT'] * 3;
    $maxmail = $SETTING['BBS_MAIL_COUNT'] * 3;
    $maxtitle = $SETTING['BBS_SUBJECT_COUNT'] * 3;
    if (!$newthread && count($msgbr) > $maxkaigy) {
        Error('改行が多すぎます。 (Check:'.count($msgbr).'/'.$maxkaigy.')');
    }
    if (mb_strlen($_POST['comment'], 'UTF-8') > $maxmsg) {
        Error('本文が長すぎます。 (Check:'.mb_strlen($_POST['comment'], 'UTF-8').'/'.$maxmsg.')');
    }
    if (mb_strlen($_POST['name'], 'UTF-8') > $maxname) {
        Error('名前が長すぎます。 (Check:'.mb_strlen($_POST['name'], 'UTF-8').'/'.$maxname.')');
    }
    if (mb_strlen($_POST['mail'], 'UTF-8') > $maxmail) {
        Error('メールアドレスが長すぎます。 (Check:'.mb_strlen($_POST['mail'], 'UTF-8').'/'.$maxmail.')');
    }
    if (mb_strlen($_POST['title'], 'UTF-8') > $maxtitle) {
        Error('スレッドタイトルが長すぎます。 (Check:'.mb_strlen($_POST['title'], 'UTF-8').'/'.$maxtitle.')');
    }
}

// 簡易PROXYチェック
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && $_SERVER['REMOTE_ADDR'] !== $_SERVER['HTTP_X_FORWARDED_FOR']) {
    $PROXY = true;
}
if (!empty($_SERVER['HTTP_VIA']) && $_SERVER['HTTP_VIA']) {
    $PROXY = true;
}
if (!empty($_SERVER['HTTP_FORWARDED']) && $_SERVER['HTTP_FORWARDED']) {
    $PROXY = true;
}
if (!empty($_SERVER['HTTP_CACHE_INFO']) && $_SERVER['HTTP_CACHE_INFO']) {
    $PROXY = true;
}
if (!empty($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']) {
    $PROXY = true;
}
if (!empty($_SERVER['HTTP_PROXY_CONNECTION']) && $_SERVER['HTTP_PROXY_CONNECTION']) {
    $PROXY = true;
}
if (!empty($_SERVER['HTTP_SP_HOST']) && $_SERVER['HTTP_SP_HOST']) {
    $PROXY = true;
}
if (!empty($_SERVER['HTTP_X_LOCKING']) && $_SERVER['HTTP_X_LOCKING']) {
    $PROXY = true;
}
if ($PROXY) {
    #Error("PROXYを検出したため投稿することができません"); // 設置環境によっては誤判定があるので使わないことにする
    $SLIP_NAME .= '-p';
}

// 未承認ユーザは海外回線からの投稿禁止
if (!empty($_SERVER['HTTP_CF_IPCOUNTRY']) && $_SERVER['HTTP_CF_IPCOUNTRY'] !== 'JP') {
    if ($SETTING['BBS_FOREIGN_PASS'] !== 'on' && !$authorized) {
        Error('未承認ユーザーは日本国外の回線から投稿することはできません');
    }
    $SLIP_NAME = $_SERVER['HTTP_CF_IPCOUNTRY'];
    $slip = 'H';
}
// 未承認ユーザーはJPドメイン以外のホストからの投稿禁止
elseif (!preg_match("/\.jp$/i", $HOST) && !preg_match("/\.bbtec\.net$/", $HOST) && $ipv6 === false && !$SLIP_SP && !$MM && !$WF) {
    if ($SETTING['BBS_PROXY_CHECK'] === 'checked' && !$authorized) {
        Error('未承認ユーザーはJPドメイン以外のホストから投稿することはできません');
    }
    $SLIP_NAME = 'unknown';
    $slip = 'H';
}
// 未承認ユーザは海外回線の鍵から投稿禁止
elseif (!empty($HAP['country']) && $HAP['country'] !== 'JP') {
    if ($SETTING['BBS_FOREIGN_PASS'] !== 'on' && !$authorized) {
        Error('未承認ユーザーは日本国外の回線で発行された鍵で投稿することはできません');
    }
    $SLIP_NAME .= '-'.$HAP['country'];
}

// ID生成用
$SLIP_SERV = $_POST['board'];
if ($SETTING['ID_RESET'] === 'year') {
    $SLIP_SERV .= date('Y');
} elseif ($SETTING['ID_RESET'] === 'month') {
    $SLIP_SERV .= date('Ym');
} elseif ($SETTING['ID_RESET'] === '10days') {
    $SLIP_SERV .= date('Ym').substr(date('d'), 0, 1);
} elseif ($SETTING['ID_RESET'] === '10hours') {
    $SLIP_SERV .= date('Ymd').substr(date('H'), 0, 1);
} elseif ($SETTING['ID_RESET'] === 'hour') {
    $SLIP_SERV .= date('YmdH');
} elseif ($SETTING['ID_RESET'] === '10minutes') {
    $SLIP_SERV .= date('YmdH').substr(date('i'), 0, 1);
} elseif ($SETTING['ID_RESET'] === 'minute') {
    $SLIP_SERV .= date('Ymdi');
} else {
    $SLIP_SERV .= date('Ymd');
}

// スレッド毎に生成するIDを変える
if ($SETTING['BBS_ID_CHANGE']) {
    $SLIP_SERV .= $_POST['thread'];
}

// プロバイダのドメインを取得
if (!$provider) {
    $SLIP_HOST = str_replace('ne.jp', 'nejp', $HOST);
    $SLIP_HOST = str_replace('ad.jp', 'adjp', $SLIP_HOST);
    $SLIP_HOST = str_replace('or.jp', 'orjp', $SLIP_HOST);
    $d = explode('.', $SLIP_HOST); // リモートホストからドメイン部分を取り出す
    if (isset($d)) {
        $c = count($d);
        $provider = $d[$c - 2].$d[$c - 1];
    }
}

// KOROKORO
$SLIP_IP = substr(crypt(md5($HAP['range'].$SLIP_SERV), md5($HAP['range'].$SLIP_SERV)), 2, 2); #IPの一部
$SLIP_ID = substr(crypt(md5($HAP['provider'].$SLIP_SERV), md5($HAP['provider'].$SLIP_SERV)), 2, 2); #プロバイダ
$SLIP_AC = substr(crypt(md5($HAP['CH_UA'].$SLIP_SERV), md5($HAP['CH_UA'].$SLIP_SERV)), 2, 2);	#ブラウザ
$SLIP_TE = substr(crypt(md5($HAP['ACCEPT'].$SLIP_SERV), md5($HAP['ACCEPT'].$SLIP_SERV)), 2, 2); #ACCEPTヘッダー

// モバイル等
if ($HAP['slip'] !== '0') {
    $temp = substr($SLIP_ID, 0, 1);
    $SLIP_ID = $SLIP_IP;
    $SLIP_IP = $HAP['slip'].$temp;
}

// IDの種類
$rawID = $SLIP_IP.$SLIP_ID.$SLIP_AC.$SLIP_TE;
$rawID = str_replace(['.', '/', '+'], '0', $rawID);
// chidにプレフィックス
if ($SETTING['BBS_ID_CHANGE'] === 'checked') {
    $rawID = 'ch-'.$rawID;
}

if ($CAPID) {  // キャップID
    $rawID = $CAPID;
    $ID = 'ID:'.$CAPID;
} elseif ($SETTING['id'] === 'siberia') {
    $ID = '発信元:'.$_SERVER['REMOTE_ADDR'];
} elseif ($SETTING['id']) {
    if (isset($SETTING['id_9th_char']) && $SETTING['id_9th_char'] === 'checked') {
        $rawID .= substr(hash('sha256', $IP_ADDR.md5($IP_ADDR)), 2, 1);
    }
    $ID = 'ID:'.$rawID;
}

// 未ログイン時で本文が半角文字のみ
if ($SETTING['unauthorized_half_check'] === 'checked' && strlen($_POST['comment']) === mb_strlen($_POST['comment'], 'UTF-8') && !$authorized) {
    Error('この掲示板・スレッドでは未承認ユーザでの日本語を含まない投稿が禁止されています');
}

// 安価と競合しないように一時変換
$_POST['comment'] = str_replace('&gt;&gt;', ' &gt;&gt;', $_POST['comment']);

//レス表示用の装飾
if (isset($SETTING['res_decoration']) && $SETTING['res_decoration'] === 'checked') {
    // 折りたたみ・要約
    $_POST['comment'] = str_replace('&lt;details&gt;', '<details>', $_POST['comment']);
    $_POST['comment'] = str_replace('&lt;/details&gt;', '</details>', $_POST['comment']);
    $_POST['comment'] = str_replace('&lt;summary&gt;', '<summary>', $_POST['comment']);
    $_POST['comment'] = str_replace('&lt;/summary&gt;', '</summary>', $_POST['comment']);
    // 返信
    $_POST['comment'] = preg_replace('/&gt;(No\.[0-9]+?)(<br>|\s|$)/', '<a class="reply" href="javascript:IdClick(\'$1\')">&gt;$1</a>', $_POST['comment']);
    // 引用
    $_POST['comment'] = preg_replace('/(<br>|^)&gt;(.*?)(<br>|$)/', '$1<div class="quote">&gt;$2</div>$3', $_POST['comment']);
    $_POST['comment'] = preg_replace('/(<br>|^)＞(.*?)(<br>|$)/', '$1<font color="gray">＞$2</font>$3', $_POST['comment']);
    // 太字＆斜体
    $_POST['comment'] = preg_replace('/\*\*\*(.*?)\*\*\*/', '<em><strong>$1</strong></em>', $_POST['comment']);
    // 太字
    $_POST['comment'] = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $_POST['comment']);
    // 斜体
    $_POST['comment'] = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $_POST['comment']);
    // 取り消し線
    $_POST['comment'] = preg_replace('/~~(.*?)~~/', '<del>$1</del>', $_POST['comment']);
    // 目立たなくする
    $_POST['comment'] = preg_replace('/\^(.*?)\^/', '<small style="opacity: 0.7;">$1</small>', $_POST['comment']);
    // ぼかし
    $_POST['comment'] = preg_replace('/{(.*?)}/', '<span class="_mfm_blur_">$1</span>', $_POST['comment']);
    // マーカー
    $_POST['comment'] = preg_replace('/==(.*?)==/', '<mark>$1</mark>', $_POST['comment']);
    // 下線
    $_POST['comment'] = preg_replace('/\+\+(.*?)\+\+/', '<ins>$1</ins>', $_POST['comment']);
}

// アイコン
if ($_POST['icon'] === 'on' && $_COOKIE['icon'] && $SETTING['DISABLE_ICON'] !== 'checked') {
    $_COOKIE['icon'] = preg_replace('/(https?|[^a-zA-Z0-9\.\-\/]+)/', '', $_COOKIE['icon']);
    $_COOKIE['homepage'] = preg_replace('/(https?|[^a-zA-Z0-9\.\-\/@\_\?=]+)/', '', $_COOKIE['homepage']);
    if (strlen($_COOKIE['icon']) > 150 || strlen($_COOKIE['icon']) < 10 || strpos($_COOKIE['icon'], '//') === false) {
        Error('アイコンURLが異常です。削除するか再度設定してください');
    }
    if (strlen($_COOKIE['homepage']) > 150 || strlen($_COOKIE['homepage']) < 10 || strpos($_COOKIE['homepage'], '//') === false) {
        Error('ホームページURLが異常です。削除するか再度設定してください');
    }
    if ($_COOKIE['homepage']) {
        $_POST['comment'] = '<a href="'.$_COOKIE['homepage'].'" target="_blank"><img src="'.$_COOKIE['icon'].'" class="icon" width="50" height="50" align="left"></a>'.$_POST['comment'];
    } else {
        $_POST['comment'] = '<img src="'.$_COOKIE['icon'].'" class="icon" width="50" height="50" align="left">'.$_POST['comment'];
    }
}

// AAモード
if ($aa || $SETTING['BBS_AA'] === 'checked') {
    $_POST['comment'] = '<span class="AA">'.$_POST['comment'].'</span>';
}

// レス情報欄
$info = $_SERVER['REMOTE_PORT'].'<>'.htmlspecialchars($_SERVER['HTTP_CF_IPCOUNTRY'], ENT_NOQUOTES, 'UTF-8').'<>'.$HAP['REMOTE_ADDR'].'<>'.htmlspecialchars($HAP['HOST'].$HAP['country'].$HAP['region'].' '.$HAP['provider'], ENT_NOQUOTES, 'UTF-8').'<>'.htmlspecialchars($HAP['SLIP_NAME'].' '.$HAP['USER_AGENT'].$HAP['CH_UA'].$HAP['ACCEPT'], ENT_NOQUOTES, 'UTF-8').'<>';

// 板別規制
if (!$admin) {
    if ($SETTING['authorized_denypass'] !== 'checked' || !$authorized) {
        if (is_file($PATH.'deny_host.cgi')) {
            $denys = safe_file($PATH.'deny_host.cgi');
            if ($denys === false) {
                Error('規制ファイルの取得に失敗しました。');
            }
            foreach ($denys as $deny) {
                $deny = trim($deny);
                if (!$deny || strpos(substr($deny, 0, 1), '#') !== false) {
                    continue;
                }
                list($kisei, $kt, $kw) = explode('<>', $deny);
                if (strpos(substr($kisei, 0, 1), '/') === false) {
                    $kisei = '/'.$kisei.'/';
                } // 規制対象
                if (strpos(substr($kt, 0, 1), '/') === false) {
                    $kt = '/'.$kt.'/';
                } // 規制を発動するスレッドタイトル
                if (strpos(substr($kw, 0, 1), '/') === false) {
                    $kw = '/'.$kw.'/';
                } // 規制を発動するワード
                if (preg_match($kisei, $HOST) || preg_match($kisei, $HAP['HOST'])) {
                    if ((!$kt || preg_match($kt, $subject)) && (!$kw || preg_match($kw, $_POST['name'].$_POST['mail'].$_POST['comment'].$_POST['title']))) {
                        Error('このホストからの投稿は禁止されています');
                    }
                }
            }
        }

        if (is_file($PATH.'deny_ip.cgi')) {
            $denys = safe_file($PATH.'deny_ip.cgi');
            if ($denys === false) {
                Error('規制ファイルの取得に失敗しました。');
            }
            foreach ($denys as $deny) {
                $deny = trim($deny);
                if (!$deny || strpos(substr($deny, 0, 1), '#') !== false) {
                    continue;
                }
                list($kisei, $kt, $kw) = explode('<>', $deny);
                if (strpos(substr($kisei, 0, 1), '/') === false) {
                    $kisei = '/'.$kisei.'/';
                } // 規制対象
                if (strpos(substr($kt, 0, 1), '/') === false) {
                    $kt = '/'.$kt.'/';
                } // 規制を発動するスレッドタイトル
                if (strpos(substr($kw, 0, 1), '/') === false) {
                    $kw = '/'.$kw.'/';
                } // 規制を発動するワード
                if (preg_match($kisei, $_SERVER['REMOTE_ADDR']) || preg_match($kisei, $HAP['REMOTE_ADDR'])) {
                    if ((!$kt || preg_match($kt, $subject)) && (!$kw || preg_match($kw, $_POST['name'].$_POST['mail'].$_POST['comment'].$_POST['title']))) {
                        Error('このIPアドレスからの投稿は禁止されています');
                    }
                }
            }
        }

        if (is_file($PATH.'deny_ua.cgi')) {
            $denys = safe_file($PATH.'deny_ua.cgi');
            if ($denys === false) {
                Error('規制ファイルの取得に失敗しました。');
            }
            foreach ($denys as $deny) {
                $deny = trim($deny);
                if (!$deny || strpos(substr($deny, 0, 1), '#') !== false) {
                    continue;
                }
                list($kisei, $kt, $kw) = explode('<>', $deny);
                if (strpos(substr($kisei, 0, 1), '/') === false) {
                    $kisei = '/'.$kisei.'/';
                } // 規制対象
                if (strpos(substr($kt, 0, 1), '/') === false) {
                    $kt = '/'.$kt.'/';
                } // 規制を発動するスレッドタイトル
                if (strpos(substr($kw, 0, 1), '/') === false) {
                    $kw = '/'.$kw.'/';
                } // 規制を発動するワード
                if (preg_match($kisei, $_SERVER['HTTP_USER_AGENT']) || preg_match($kisei, $CH_UA) || preg_match($kisei, $HAP['USER_AGENT']) || preg_match($kisei, $HAP['CH_UA'])) {
                    if ((!$kt || preg_match($kt, $subject)) && (!$kw || preg_match($kw, $_POST['name'].$_POST['mail'].$_POST['comment'].$_POST['title']))) {
                        Error('このUser-Agentからの投稿は禁止されています');
                    }
                }
            }
        }

        if (is_file($PATH.'deny_area.cgi')) {
            $denys = safe_file($PATH.'deny_area.cgi');
            if ($denys === false) {
                Error('規制ファイルの取得に失敗しました。');
            }
            foreach ($denys as $deny) {
                $deny = trim($deny);
                if (!$deny || strpos(substr($deny, 0, 1), '#') !== false) {
                    continue;
                }
                list($kisei, $kt, $kw) = explode('<>', $deny);
                if (strpos(substr($kisei, 0, 1), '/') === false) {
                    $kisei = '/'.$kisei.'/';
                } // 規制対象
                if (strpos(substr($kt, 0, 1), '/') === false) {
                    $kt = '/'.$kt.'/';
                } // 規制を発動するスレッドタイトル
                if (strpos(substr($kw, 0, 1), '/') === false) {
                    $kw = '/'.$kw.'/';
                } // 規制を発動するワード
                if (preg_match($kisei, $_SERVER['HTTP_CF_IPCOUNTRY'].$HAP['country'].$HAP['region'])) {
                    if ((!$kt || preg_match($kt, $subject)) && (!$kw || preg_match($kw, $_POST['name'].$_POST['mail'].$_POST['comment'].$_POST['title']))) {
                        Error('この国・地域からの投稿は禁止されています');
                    }
                }
            }
        }

    }

    if (is_file($PATH.'deny_account.cgi')) {
        $denys = safe_file($PATH.'deny_account.cgi');
        if ($denys === false) {
            Error('規制ファイルの取得に失敗しました。');
        }
        foreach ($denys as $deny) {
            $deny = trim($deny);
            if (!$deny || strpos(substr($deny, 0, 1), '#') !== false) {
                continue;
            }
            list($kisei, $kt, $kw) = explode('<>', $deny);
            if (strpos(substr($kisei, 0, 1), '/') === false) {
                $kisei = '/'.$kisei.'/';
            } // 規制対象
            if (strpos(substr($kt, 0, 1), '/') === false) {
                $kt = '/'.$kt.'/';
            } // 規制を発動するスレッドタイトル
            if (strpos(substr($kw, 0, 1), '/') === false) {
                $kw = '/'.$kw.'/';
            } // 規制を発動するワード
            if (preg_match($kisei, $WrtAgreementKey)) {
                if ((!$kt || preg_match($kt, $subject)) && (!$kw || preg_match($kw, $_POST['name'].$_POST['mail'].$_POST['comment'].$_POST['title']))) {
                    Error('BANされています');
                }
            }
        }
    }

}

// 短時間連続投稿制限
#$SETTING['BBS_SAMBA24'] => (20) 規制秒数
if ($SETTING['Samba24'] === 'checked' && $HAP['last']) {
    $samba = $HAP['last'] + $SETTING['BBS_SAMBA24'];
    if ($NOWTIME < $samba) {
        Error('593 間隔が'.$SETTING['BBS_SAMBA24'].'秒以内の連続投稿はできません');
    }
}

// スレッド毎連続投稿制限
if (!$tlonly && $SETTING['threadcheck'] === 'checked') {
    if ($authorized && $SETTING['threadlimit'] > 0) {
        $SETTING['threadlimit'] = $SETTING['threadlimit'] / 2;
    }
    $IP = [];
    $count = 0;
    if (is_file($PATH.'dat/'.$_POST['thread'].'_kisei.cgi')) {
        $IP = safe_file($PATH.'dat/'.$_POST['thread'].'_kisei.cgi');
        if ($IP === false) {
            Error('規制ファイルの取得に失敗しました。');
        }
        foreach ($IP as $tmp) {
            $tmp = trim($tmp);
            list($time1, $addr1, $c1) = explode('<>', $tmp);
            if ($NOWTIME < $time1 + $SETTING['threadlimit'] && ($IP_ADDR === $addr1 || $WrtAgreementKey === $c1)) {
                $count++;
            }
        }
    }
    if ($count >= $SETTING['timecover']) {
        Error('このスレッド内で一定時間内に投稿可能な上限に達しました');
    }
    array_unshift($IP, $NOWTIME.'<>'.$IP_ADDR.'<>'.$WrtAgreementKey);
    while (count($IP) > $SETTING['threadcount']) {
        array_pop($IP);
    }
    // datディレクトリがあるかチェック
    $directoryPath = $PATH . 'dat/';
    if (!file_exists($directoryPath)) {
        mkdir($directoryPath, 0777, true);
    }
    $fp = fopen($PATH.'dat/'.$_POST['thread'].'_kisei.cgi', 'w');
    if ($fp !== false) {
        if (flock($fp, LOCK_EX)) {
            foreach ($IP as $tmp) {
                fwrite($fp, $tmp."\n");
            }
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }
}

// 板毎連続投稿制限
if ($SETTING['timecheck'] === 'checked') {
    if ($authorized && $SETTING['timelimit'] > 0) {
        $SETTING['timelimit'] = $SETTING['timelimit'] / 2;
    }
    $IP = [];
    $count = $time1 = $addr1 = $tmp = 0;
    if (is_file($PATH.'timecheck.cgi')) {
        $IP = safe_file($PATH.'timecheck.cgi');
        if ($IP === false) {
            Error('規制ファイルの取得に失敗しました。');
        }
        foreach ($IP as $tmp) {
            $tmp = trim($tmp);
            list($time1, $addr1, $c1) = explode('<>', $tmp);
            if ($NOWTIME < $time1 + $SETTING['timelimit'] && ($IP_ADDR === $addr1 || $WrtAgreementKey === $c1)) {
                $count++;
            }
        }
    }
    if ($count >= $SETTING['timeclose']) {
        Error('一定時間内に投稿可能な上限に達しました');
    }
    array_unshift($IP, $NOWTIME.'<>'.$IP_ADDR.'<>'.$WrtAgreementKey);
    while (count($IP) > $SETTING['timecount']) {
        array_pop($IP);
    }
    $fp = fopen($PATH.'timecheck.cgi', 'w');
    if ($fp !== false) {
        if (flock($fp, LOCK_EX)) {
            foreach ($IP as $tmp) {
                fwrite($fp, $tmp."\n");
            }
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }
}

// スレッド作成規制
if ($newthread && !$admin) {
    if ($SETTING['authorized_makedenypass'] !== 'checked' || !$authorized) {
        if (is_file($PATH.'makedeny_host.cgi')) {
            $denys = safe_file($PATH.'makedeny_host.cgi');
            if ($denys === false) {
                Error('規制ファイルの取得に失敗しました。');
            }
            foreach ($denys as $deny) {
                $deny = trim($deny);
                if (!$deny || strpos(substr($deny, 0, 1), '#') !== false) {
                    continue;
                }
                list($kisei, $kt, $kw) = explode('<>', $deny);
                if (strpos(substr($kisei, 0, 1), '/') === false) {
                    $kisei = '/'.$kisei.'/';
                } // 規制対象
                if (strpos(substr($kt, 0, 1), '/') === false) {
                    $kt = '/'.$kt.'/';
                } // 禁止タイトル
                if (strpos(substr($kw, 0, 1), '/') === false) {
                    $kw = '/'.$kw.'/';
                } // 規制を発動するワード
                if (preg_match($kisei, $HOST) || preg_match($kisei, $HAP['HOST'])) {
                    if ((!$kt || preg_match($kt, $_POST['title'])) && (!$kw || preg_match($kw, $_POST['name'].$_POST['mail'].$_POST['comment'].$_POST['title']))) {
                        Error('このホストからのスレッド作成は禁止されています');
                    }
                }
            }
        }

        if (is_file($PATH.'makedeny_ip.cgi')) {
            $denys = safe_file($PATH.'makedeny_ip.cgi');
            if ($denys === false) {
                Error('規制ファイルの取得に失敗しました。');
            }
            foreach ($denys as $deny) {
                $deny = trim($deny);
                if (!$deny || strpos(substr($deny, 0, 1), '#') !== false) {
                    continue;
                }
                list($kisei, $kt, $kw) = explode('<>', $deny);
                if (strpos(substr($kisei, 0, 1), '/') === false) {
                    $kisei = '/'.$kisei.'/';
                } // 規制対象
                if (strpos(substr($kt, 0, 1), '/') === false) {
                    $kt = '/'.$kt.'/';
                } // 禁止タイトル
                if (strpos(substr($kw, 0, 1), '/') === false) {
                    $kw = '/'.$kw.'/';
                } // 規制を発動するワード
                if (preg_match($kisei, $_SERVER['REMOTE_ADDR']) || preg_match($kisei, $HAP['REMOTE_ADDR'])) {
                    if ((!$kt || preg_match($kt, $_POST['title'])) && (!$kw || preg_match($kw, $_POST['name'].$_POST['mail'].$_POST['comment'].$_POST['title']))) {
                        Error('このIPアドレスからのスレッド作成は禁止されています');
                    }
                }
            }
        }

        if (is_file($PATH.'makedeny_ua.cgi')) {
            $denys = safe_file($PATH.'makedeny_ua.cgi');
            if ($denys === false) {
                Error('規制ファイルの取得に失敗しました。');
            }
            foreach ($denys as $deny) {
                $deny = trim($deny);
                if (!$deny || strpos(substr($deny, 0, 1), '#') !== false) {
                    continue;
                }
                list($kisei, $kt, $kw) = explode('<>', $deny);
                if (strpos(substr($kisei, 0, 1), '/') === false) {
                    $kisei = '/'.$kisei.'/';
                } // 規制対象
                if (strpos(substr($kt, 0, 1), '/') === false) {
                    $kt = '/'.$kt.'/';
                } // 禁止タイトル
                if (strpos(substr($kw, 0, 1), '/') === false) {
                    $kw = '/'.$kw.'/';
                } // 規制を発動するワード
                if (preg_match($kisei, $_SERVER['HTTP_USER_AGENT']) || preg_match($kisei, $CH_UA) || preg_match($kisei, $HAP['USER_AGENT']) || preg_match($kisei, $HAP['CH_UA'])) {
                    if ((!$kt || preg_match($kt, $_POST['title'])) && (!$kw || preg_match($kw, $_POST['name'].$_POST['mail'].$_POST['comment'].$_POST['title']))) {
                        Error('このUAからのスレッド作成は禁止されています');
                    }
                }
            }
        }

        if (is_file($PATH.'makedeny_area.cgi')) {
            $denys = safe_file($PATH.'makedeny_area.cgi');
            if ($denys === false) {
                Error('規制ファイルの取得に失敗しました。');
            }
            foreach ($denys as $deny) {
                $deny = trim($deny);
                if (!$deny || strpos(substr($deny, 0, 1), '#') !== false) {
                    continue;
                }
                list($kisei, $kt, $kw) = explode('<>', $deny);
                if (strpos(substr($kisei, 0, 1), '/') === false) {
                    $kisei = '/'.$kisei.'/';
                } // 規制対象
                if (strpos(substr($kt, 0, 1), '/') === false) {
                    $kt = '/'.$kt.'/';
                } // 禁止タイトル
                if (strpos(substr($kw, 0, 1), '/') === false) {
                    $kw = '/'.$kw.'/';
                } // 規制を発動するワード
                if (preg_match($kisei, $_SERVER['HTTP_CF_IPCOUNTRY'].$HAP['country'].$HAP['region'])) {
                    if ((!$kt || preg_match($kt, $_POST['title'])) && (!$kw || preg_match($kw, $_POST['name'].$_POST['mail'].$_POST['comment'].$_POST['title']))) {
                        Error('この国・地域からのスレッド作成は禁止されています');
                    }
                }
            }
        }

    }

    if (is_file($PATH.'makedeny_account.cgi')) {
        $denys = safe_file($PATH.'makedeny_account.cgi');
        if ($denys === false) {
            Error('規制ファイルの取得に失敗しました。');
        }
        foreach ($denys as $deny) {
            $deny = trim($deny);
            if (!$deny || strpos(substr($deny, 0, 1), '#') !== false) {
                continue;
            }
            list($kisei, $kt, $kw) = explode('<>', $deny);
            if (strpos(substr($kisei, 0, 1), '/') === false) {
                $kisei = '/'.$kisei.'/';
            } // 規制対象
            if (strpos(substr($kt, 0, 1), '/') === false) {
                $kt = '/'.$kt.'/';
            } // 禁止タイトル
            if (strpos(substr($kw, 0, 1), '/') === false) {
                $kw = '/'.$kw.'/';
            } // 規制を発動するワード
            if (preg_match($kisei, $WrtAgreementKey)) {
                if ((!$kt || preg_match($kt, $_POST['title'])) && (!$kw || preg_match($kw, $_POST['name'].$_POST['mail'].$_POST['comment'].$_POST['title']))) {
                    Error('あなたはスレッドを作成することができません');
                }
            }
        }
    }
}

// スレッド作成制限
if ($newthread) {
    if ($SETTING['newthread_check'] === 'checked') {
        if ($authorized && $SETTING['JUNBAN_LIMIT'] > 0) {
            $SETTING['JUNBAN_LIMIT'] = $SETTING['JUNBAN_LIMIT'] / 2;
        }
        $IP = [];
        $count = $tmp = 0;
        if (is_file($PATH.'newthread.cgi')) {
            if ($NOWTIME < filemtime($PATH.'newthread.cgi') + $SETTING['THREAD_INTERVAL']) {
                Error('直前のスレッド作成から'.$SETTING['THREAD_INTERVAL'].'秒経たなければスレッドを作成することができません');
            }
            $IP = safe_file($PATH.'newthread.cgi');
            if ($IP === false) {
                Error('規制ファイルの取得に失敗しました。');
            }
            foreach ($IP as $tmp) {
                $tmp = trim($tmp);
                list($t1, $p1, $c1) = explode('<>', $tmp);
                if ($NOWTIME < $t1 + $SETTING['JUNBAN_LIMIT'] && ($IP_ADDR === $p1 || $WrtAgreementKey === $c1)) {
                    Error('スレッド作成の順番待ち中です');
                }
            }
        }
        array_unshift($IP, $NOWTIME.'<>'.$IP_ADDR.'<>'.$WrtAgreementKey);
        while (count($IP) > $SETTING['THREAD_JUNBAN']) {
            array_pop($IP);
        }
        $fp = fopen($PATH.'newthread.cgi', 'w');
        if ($fp !== false) {
            if (flock($fp, LOCK_EX)) {
                foreach ($IP as $tmp) {
                    fwrite($fp, $tmp."\n");
                }
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }
    } elseif ($SETTING['THREAD_INTERVAL']) {
        $file = $PATH.'newthread.cgi';
        if (is_file($file) and $NOWTIME < filemtime($file) + $SETTING['THREAD_INTERVAL']) {
            Error('直前のスレッド作成から'.$SETTING['THREAD_INTERVAL'].'秒経たなければスレッドを作成することができません');
        }
        touch($file);
    }
}

// マルチポスト制限
if (!$authorized) {
    similar_text($HAP['comment'], $_POST['comment'], $perc);
    if ($perc > 98) {
        Error('同一・類似投稿を連続して投稿することはできません');
    }
}

$no = false;
if ($SETTING['disable_supervisor'] === 'checked') {
    $no = true;
}
if (strpos($_POST['mail'], '!no') !== false && $SETTING['BBS_DISABLE_NO'] !== 'checked') {
    $no = true;
}
$_POST['mail'] = str_replace('!no', '', $_POST['mail']);

// キャップ色
if ($ncolor) {
    $_POST['name'] = "<font color=\"$ncolor\">".$_POST['name'].'</font>';
}

// 名前入力チェックと補完
if (!$_POST['name']) {
    if ($SETTING['NANASHI_CHECK'] === 'checked') {
        Error('名前を入れてください');
    } else {
        $_POST['name'] = $SETTING['BBS_NONAME_NAME'];
    }
}

// 規制終了

// 名前欄転載禁止表示
if ($SETTING['NAME_ARR'] === 'checked') {
    $_POST['name'] .= '@転載禁止';
}

// 県名表示
if ($SETTING['BBS_JP_CHECK'] && $SETTING['BBS_JP_CHECK'] !== 'none' && !$admin) {
    $M .= ' </b>('.$HAP['region'].')<b>';
}

// 回線別末尾+新規表示
if ($SETTING['slip'] === 'checked' && !$admin) {
    $endChar = '';
    if ($LV < 1) {
        $endChar .= '新規';
    }
    $endChar .= $slip;
    $M .= " </b>($endChar)<b>";
}

// BBS_SLIP=vvvvv相当
$sliprange = substr(crypt(md5($range.$_POST['board'].date('Ym').substr(date('d'), 0, 1)), md5($range.$_POST['board'].date('Ym').substr(date('d'), 0, 1))), 2, 2); #IPの一部
$slipid = substr(crypt(md5($sliphost.$_POST['board'].date('Ym').substr(date('d'), 0, 1)), md5($sliphost.$_POST['board'].date('Ym').substr(date('d'), 0, 1))), 2, 2); #プロバイダ
$slipua = substr(crypt(md5($CH_UA.$_POST['board'].date('Ym').substr(date('d'), 0, 1)), md5($CH_UA.$_POST['board'].date('Ym').substr(date('d'), 0, 1))), 2, 2);	#ブラウザ
$slipac = substr(crypt(md5($ACCEPT.$_POST['board'].date('Ym').substr(date('d'), 0, 1)), md5($ACCEPT.$_POST['board'].date('Ym').substr(date('d'), 0, 1))), 2, 2);
// 置き換える文字
$vvvvv = preg_replace('/\./', '+', $sliprange.$slipid.'-'.$slipua.$slipac);
$vvvvv = str_replace('/', '+', $vvvvv);
$vvvvv = str_replace('+', '0', $vvvvv);	// read.js対策
if ($SETTING['disp_slipname'] === 'checked' && !$authorized && !$admin) {
    $M .= " </b>({$SLIP_NAME} {$vvvvv})<b>";
}

// 強制リモートホスト表示
if ($SETTING['fusianasan'] === 'name' && !$authorized && !$admin) {
    $M .= " </b>($HOST)<b>";
}
// 強制ClientID表示
elseif ($SETTING['fusianasan'] === 'id' && !$authorized && !$admin) {
    $M .= " </b>($WrtAgreementKey)<b>";
}

// スレッド主表示
if (!$newthread && $supervisor && !$no && $SETTING['id'] !== '') {
    $M .= ' </b>(主)<b>';
}

// KOROKOROをタイトルに表示
if ($newthread && $SETTING['createid'] === 'checked' && $SETTING['id'] && !$admin) {
    $_POST['title'] .= ' ['.$rawID.'★]';
    $subject .= ' ['.$rawID.'★]';
}

// fusianasanでホスト表示
$_POST['name'] = str_replace('fusianasan', ' </b>('.$HOST.')<b>', $_POST['name']);
// ClientID表示
$_POST['name'] = str_replace('!clientid', ' </b>('.$WrtAgreementKey.')<b>', $_POST['name']);
// 県名表示
$_POST['name'] = str_replace('!ken', ' </b>('.$HAP['region'].')<b>', $_POST['name']);
// ID表示
$_POST['name'] = str_replace('!id', ' </b>('.$SLIP_IP.$SLIP_ID.$SLIP_AC.$SLIP_TE.')<b>', $_POST['name']);

// ワッチョイ等を表示
if ($M) {
    $_POST['name'] .= $M;
}

// mail欄にはTLでの返信に使うNoを入れる
// $_POST['mail'] = 'No.'.$NOWTIME;
// 鍵漏れ等の対策としてメール欄の内容は削除
$_POST['mail'] = '';

// 現行スレフォルダへファイル追加
if ($newthread) {
    if (!touch($THREAD_STATES_FILE)) {
        Error('投稿に失敗しました。');
    }
}

// スレ状態ファイルを更新
if ($threadStatesReload) {
    $threadStatesUpdater->put($threadStates);
}

// >>1への変更を反映させる
function updateFirstRes($datFile, $newFirstRes, $isShiftJis)
{
    $datFileHandle = fopen($datFile, 'r+');
    if (flock($datFileHandle, LOCK_EX)) {
        $datLines = explode("\n", fread($datFileHandle, filesize($datFile)));
        if ($isShiftJis) {
            $datLines[0] = mb_convert_encoding($newFirstRes, 'SJIS-win', 'UTF-8');
        } else {
            $datLines[0] = $newFirstRes;
        }
        ftruncate($datFileHandle, 0);
        rewind($datFileHandle);
        fwrite($datFileHandle, implode("\n", $datLines));
        flock($datFileHandle, LOCK_UN);
    }
    fclose($datFileHandle);
}
if (!$newthread && !$tlonly && $reload) {
    $newFirstRes = $firstResName.'<>'.$firstResMail.'<>'.$firstResDateId.'<>'.$message.'<>'.$subject;
    // $THREADFILE更新
    updateFirstRes($THREADFILE, $newFirstRes, false);
    // $DATFILE更新
    if (is_file($DATFILE)) {
        updateFirstRes($DATFILE, $newFirstRes, true);
    }
}

// dat追記用関数
function addNewResToDat($datFile, $newRes)
{
    $datFileHandle = fopen($datFile, 'a+');
    if (flock($datFileHandle, LOCK_EX)) {
        fwrite($datFileHandle, $newRes);
        flock($datFileHandle, LOCK_UN);
        fclose($datFileHandle);
    } else {
        fclose($datFileHandle);
        Error('投稿に失敗しました。');
    }
}

// dat用にShift_JISに再変換
if (!$tlonly) {
    $DATMAIL = $newthread ? $supervisorID : $_POST['mail'];
    $outdat = mb_convert_encoding($_POST['name'].'<>'.$DATMAIL.'<>'.$DATE.' '.$ID.'<>'.$_POST['comment'].'<>'.$_POST['title']."\n", 'SJIS-win', 'UTF-8');
    // datに書き込み
    // datディレクトリがあるかチェック
    $directoryPath = $PATH . 'dat/';
    if (!file_exists($directoryPath)) {
        mkdir($directoryPath, 0777, true);
    }
    // datに追記
    addNewResToDat($DATFILE, $outdat);
}

// レス情報を本文末尾に追加
// if ($M) $_POST['comment'] .= '<br><font color="gray"><small>['.$M.']</small></font>';

// URLをリンクに変換
$_POST['comment'] = preg_replace("/\[(.+?)\]\(https?:\/\/([\w;\/\?:\@&=\+\$,\-\.!~\*'\(\)%#]+)\)/", '<a href="//$2" rel="nofollow noopener" target="_blank" title="//$2">$1</a>', $_POST['comment']);
$_POST['comment'] = preg_replace_callback('/https?:([a-zA-z0-9\/\._\-&\?#=%:@]+)/', function ($m) {
    global $number;
    $httphost = $_SERVER['HTTP_HOST'];
    $url = $m[0];
    if (preg_match('/https?:\S+(gif|jpg|jpeg|tiff|png|webp)/', $url)) {
        return "<a href=\"$url\" data-lightbox=\"image\"><img class=\"image img-$number\" src=\"$url\" loading=\"lazy\"></a><br>$url";
    } elseif (strpos($url, 'youtube.com/watch') !== false) {
        $iframeurl = substr($url, (strpos($url, '=') + 1));
        $iframeurl = substr($iframeurl, 0, 11);
        return "<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/$iframeurl\" title=\"YouTube video player\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen=\"\" data-ruffle-polyfilled=\"\" loading=\"lazy\"></iframe><br>$url";
    } elseif (strpos($url, 'm.youtube.com/watch') !== false) {
        $iframeurl = substr($url, (strpos($url, '=') + 1));
        $iframeurl = substr($iframeurl, 0, 11);
        return "<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/$iframeurl\" title=\"YouTube video player\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen=\"\" data-ruffle-polyfilled=\"\" loading=\"lazy\"></iframe><br>$url";
    } elseif (strpos($url, 'youtu.be/') !== false) {
        $iframeurl = substr($url, (strpos($url, 'youtu.be/') + 9));
        $iframeurl = substr($iframeurl, 0, 11);
        return "<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/$iframeurl\" title=\"YouTube video player\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen=\"\" data-ruffle-polyfilled=\"\" loading=\"lazy\"></iframe><br>$url";
    } elseif (strpos($url, 'youtube.com/shorts') !== false) {
        $iframeurl = substr($url, (strpos($url, 'shorts/') + 7));
        $iframeurl = substr($iframeurl, 0, 11);
        return "<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/$iframeurl\" title=\"YouTube video player\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen=\"\" data-ruffle-polyfilled=\"\" loading=\"lazy\"></iframe><br>$url";
    } elseif (strpos($url, 'youtube.com/live') !== false) {
        $iframeurl = substr($url, (strpos($url, 'live/') + 5));
        $iframeurl = substr($iframeurl, 0, 11);
        return "<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/$iframeurl\" title=\"YouTube video player\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen=\"\" data-ruffle-polyfilled=\"\" loading=\"lazy\"></iframe><br>$url";
    } elseif (strpos($url, 'nicovideo.jp/watch') !== false) {
        $iframeurl = substr($url, (strpos($url, 'watch/') + 6));
        $iframeurl = substr($iframeurl, 0, 10);
        return "<iframe allowfullscreen=\"allowfullscreen\" allow=\"autoplay\" frameborder=\"0\" width=\"560\" height=\"315\" src=\"https://embed.nicovideo.jp/watch/$iframeurl?persistence=1&amp;oldScript=1&amp;referer=https%3A%2F%2F$httphost%2F&amp;from=0&amp;allowProgrammaticFullScreen=1\" style=\"max-width: 100%;\" data-ruffle-polyfilled=\"\" loading=\"lazy\"></iframe><br>$url";
    } elseif (strpos($url, 'nico.ms/') !== false) {
        $iframeurl = substr($url, (strpos($url, 'nico.ms/') + 8));
        $iframeurl = substr($iframeurl, 0, 10);
        return "<iframe allowfullscreen=\"allowfullscreen\" allow=\"autoplay\" frameborder=\"0\" width=\"560\" height=\"315\" src=\"https://embed.nicovideo.jp/watch/$iframeurl?persistence=1&amp;oldScript=1&amp;referer=https%3A%2F%2F$httphost%2F&amp;from=0&amp;allowProgrammaticFullScreen=1\" style=\"max-width: 100%;\" data-ruffle-polyfilled=\"\" loading=\"lazy\"></iframe><br>$url";
    } elseif (strpos($url, 'twitcasting.tv/') !== false) {
        $iframeurl = substr($url, (strpos($url, 'twitcasting.tv/') + 15));
        return "<iframe allowfullscreen=\"allowfullscreen\" allow=\"autoplay\" frameborder=\"0\" width=\"560\" height=\"315\" src=\"https://twitcasting.tv/$iframeurl/embeddedplayer/live?auto_play=false&default_mute=false\" loading=\"lazy\"></iframe><br>$url";
    } elseif (strpos($url, 'www.tiktok.com/') !== false) {
        $iframeurl = substr($url, (strpos($url, 'video/') + 6));
        $iframeurl = substr($iframeurl, 0, 19);
        return "<iframe class=\"viewon\" width=\"320\" height=\"550\" src=\"https://www.tiktok.com/embed/$iframeurl\" _src=\"https://www.tiktok.com/embed/$iframeurl\" frameborder=\"0\" allow=\"accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen=\"\" data-ruffle-polyfilled=\"\" loading=\"lazy\"></iframe><br>$url";
    } elseif (strpos($url, 'www.instagram.com/p/') !== false) {
        return "<iframe class=\"instagram-media instagram-media-rendered\" id=\"instagram-embed-12\" src=\"{$url}embed/?cr=1&amp;\" allowtransparency=\"true\" allowfullscreen=\"true\" frameborder=\"0\" height=\"500\" data-instgrm-payload-id=\"instagram-media-payload-12\" scrolling=\"no\" style=\"background-color: white; border-radius: 3px; border: 1px solid rgb(219, 219, 219); box-shadow: none; display: block; margin: 0px 0px 12px; min-width: 326px; padding: 0px;\" data-ruffle-polyfilled=\"\" loading=\"lazy\"></iframe><br>$url";
    } elseif (strpos($url, 'www.instagram.com/reel/') !== false) {
        $iframeurl = substr($url, (strpos($url, 'reel/') + 5));
        $iframeurl = substr($iframeurl, 0, 11);
        return '<iframe class="instagram-media instagram-media-rendered" id="instagram-embed-12" src="https://www.instagram.com/p/'.$iframeurl."/embed/?cr=1&amp;\" allowtransparency=\"true\" allowfullscreen=\"true\" frameborder=\"0\" height=\"500\" data-instgrm-payload-id=\"instagram-media-payload-12\" scrolling=\"no\" style=\"background-color: white; border-radius: 3px; border: 1px solid rgb(219, 219, 219); box-shadow: none; display: block; margin: 0px 0px 12px; min-width: 326px; padding: 0px;\" data-ruffle-polyfilled=\"\" loading=\"lazy\"></iframe><br>$url";
    } elseif ((strpos($url, 'x.com/') !== false || strpos($url, 'twitter.com/') !== false) && strpos($url, '/status/') !== false) {
        if (strpos($url, 'twitter.com') !== false) {
            $xdomain = 'platform.twitter.com';
        } else {
            $xdomain = 'platform.x.com';
        }
        $twitterurl = substr($url, (strpos($url, 'status/') + 7));
        $twitterurl = substr($twitterurl, 0, 19);
        $rurl = str_replace('/', '%2F', $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        return '<div class="twitter-tweet twitter-tweet-rendered" style="display: flex; max-width: 550px; width: 100%; margin-bottom: 10px;"><iframe id="twitter-widget-0" scrolling="no" allowtransparency="true" allowfullscreen="true" class="" style="position: static; visibility: visible; width: 550px; min-height: 550px; display: block; flex-grow: 1;" title="Twitter Tweet" src="https://'.$xdomain.'/embed/Tweet.html?dnt=false&amp;embedId=twitter-widget-0&amp;features=eyJ0ZndfdGltZWxpbmVfbGlzdCI6eyJidWNrZXQiOltdLCJ2ZXJzaW9uIjpudWxsfSwidGZ3X2ZvbGxvd2VyX2NvdW50X3N1bnNldCI6eyJidWNrZXQiOnRydWUsInZlcnNpb24iOm51bGx9LCJ0ZndfdHdlZXRfZWRpdF9iYWNrZW5kIjp7ImJ1Y2tldCI6Im9uIiwidmVyc2lvbiI6bnVsbH0sInRmd19yZWZzcmNfc2Vzc2lvbiI6eyJidWNrZXQiOiJvbiIsInZlcnNpb24iOm51bGx9LCJ0ZndfbWl4ZWRfbWVkaWFfMTU4OTciOnsiYnVja2V0IjoidHJlYXRtZW50IiwidmVyc2lvbiI6bnVsbH0sInRmd19leHBlcmltZW50c19jb29raWVfZXhwaXJhdGlvbiI6eyJidWNrZXQiOjEyMDk2MDAsInZlcnNpb24iOm51bGx9LCJ0ZndfZHVwbGljYXRlX3NjcmliZXNfdG9fc2V0dGluZ3MiOnsiYnVja2V0Ijoib24iLCJ2ZXJzaW9uIjpudWxsfSwidGZ3X3ZpZGVvX2hsc19keW5hbWljX21hbmlmZXN0c18xNTA4MiI6eyJidWNrZXQiOiJ0cnVlX2JpdHJhdGUiLCJ2ZXJzaW9uIjpudWxsfSwidGZ3X2xlZ2FjeV90aW1lbGluZV9zdW5zZXQiOnsiYnVja2V0Ijp0cnVlLCJ2ZXJzaW9uIjpudWxsfSwidGZ3X3R3ZWV0X2VkaXRfZnJvbnRlbmQiOnsiYnVja2V0Ijoib24iLCJ2ZXJzaW9uIjpudWxsfX0%3D&amp;frame=false&amp;hideCard=false&amp;hideThread=false&amp;id='.$twitterurl.'&amp;lang=en&amp;origin=https%3A%2F%2F'.$rurl.'&amp;sessionId=a9d4d113f56d7d35c5da4aa01b7ee15e6bdeb19a&amp;theme=light&amp;widgetsVersion=aaf4084522e3a%3A1674595607486&amp;width=550px" data-tweet-id="'.$twitterurl.'" frameborder="0" loading=\"lazy\"></iframe></div>'.$url;
    } elseif (preg_match('/https?:\S+\.mp4/', $url)) {
        return "<video src=\"$url\" width=\"560\" height=\"315\" playsinline=\"\" controls=\"\"></video><br>$url";
    } elseif (strpos($url, $_SERVER['HTTP_HOST']) !== false && strpos($url, '#') !== false) {
        return "<a href=\"$url\">$url</a>";
    } else {
        $url = preg_replace("/(https?):\/\/([\w;\/\?:\@&=\+\$,\-\.!~\*'\(\)%#]+)/", '<iframe scrolling="no" style="display:block;width:100%;height:155px;max-width:500px;margin:10px 0px;" loading="lazy" src="https://hatenablog-parts.com/embed?url=$1://$2" frameborder="0"></iframe>$1://$2', $url);
        return $url;
    }
}, $_POST['comment']);
$_POST['comment'] = str_replace(' rel="nofollow noopener" target="_blank" title="//', ' rel="nofollow noopener" target="_blank" title="https://', $_POST['comment']);
// レスアンカーをリンクに変換
$_POST['comment'] = preg_replace_callback('/&gt;&gt;([0-9]+),?([0-9]+)?,?([0-9]+)?,?([0-9]+)?,?([0-9]+)?(?![-\d])/', function ($m) {
    global $number;
    $anka = "<a class=\"ank rep-$number\" href=\"/?st=$m[1]#".$_POST['board'].'/'.$_POST['thread']."/\">&gt;&gt;$m[1]</a>";
    if ($m[2]) {
        $anka .= ",<a class=\"ank rep-$number\" href=\"/?st=$m[2]#".$_POST['board'].'/'.$_POST['thread']."/\">&gt;&gt;$m[2]</a>";
    }
    if ($m[3]) {
        $anka .= ",<a class=\"ank rep-$number\" href=\"/?st=$m[3]#".$_POST['board'].'/'.$_POST['thread']."/\">&gt;&gt;$m[3]</a>";
    }
    if ($m[4]) {
        $anka .= ",<a class=\"ank rep-$number\" href=\"/?st=$m[4]#".$_POST['board'].'/'.$_POST['thread']."/\">&gt;&gt;$m[4]</a>";
    }
    if ($m[5]) {
        $anka .= ",<a class=\"ank rep-$number\" href=\"/?st=$m[5]#".$_POST['board'].'/'.$_POST['thread']."/\">&gt;&gt;$m[5]</a>";
    }
    return $anka;
}, $_POST['comment']);
$_POST['comment'] = preg_replace_callback('/&gt;&gt;([0-9]+)\-([0-9]+)/', function ($m) {
    global $number;
    return "<a href=\"javascript:ResAnchor2('$m[1],$m[2],$number');\" class=\"ank2 rep-$number\">&gt;&gt;$m[1]-$m[2]</a>";
}, $_POST['comment']);

if (!$tlonly) {
    // ディレクトリチェック
    makeDir($PATH.'thread/'.substr($_POST['thread'], 0, 4).'/');
    // スレッドファイルに書き込み
    $newRes = $_POST['name'].'<>'.$DATMAIL.'<>'.$DATE.' '.$ID.'<>'.$_POST['comment'].'<>'.$_POST['title']."\n";
    addNewResToDat($THREADFILE, $newRes);
}

// 新規スレッドの場合、過去ログ用スレッド一覧(subject.json)に追加
if ($newthread) {
    $kakoSubjectFile = $PATH.'thread/'.substr($_POST['thread'], 0, 4).'/subject.json';
    $fileExists = is_file($kakoSubjectFile);
    $kakoSubjectFileHandle = fopen($kakoSubjectFile, 'c+');
    if (flock($kakoSubjectFileHandle, LOCK_EX)) {
        if ($fileExists) {
            $tlist = json_decode(fread($kakoSubjectFileHandle, filesize($kakoSubjectFile)), true);
        } else {
            $tlist = [];
        }
        $created = [
            'thread' => $_POST['thread'],
            'title' => $subject,
            'number' => 'archive',
            'date' => 'archive',
        ];
        $tlist[] = $created;
        ftruncate($kakoSubjectFileHandle, 0);
        rewind($kakoSubjectFileHandle);
        fwrite($kakoSubjectFileHandle, json_encode($tlist, JSON_UNESCAPED_UNICODE));
        flock($kakoSubjectFileHandle, LOCK_UN);
    }
    fclose($kakoSubjectFileHandle);
}

// ローカルタイムライン (index.json)
if (!$sage) {
    $post = [
        'name' => $_POST['name'],
        'mail' => 'No.'.$NOWTIME,
        'date' => $DATE,
        'id' => $ID,
        'comment' => $_POST['comment'],
    ];
    if (!$tlonly) {
        $post['title'] = $subject;
        $post['thread'] = $_POST['thread'];
    }
    $fileExists = is_file($LTLFILE);
    $LTLFILEHandle = fopen($LTLFILE, 'c+');
    if (flock($LTLFILEHandle, LOCK_EX)) {
        if ($fileExists) {
            $LTL = json_decode(fread($LTLFILEHandle, filesize($LTLFILE)), true);
        } else {
            $LTL = [];
        }
        array_unshift($LTL, $post);
        // ファイル内の投稿数を $SETTING['LTL_LIMIT'] 個以内に調整して保存
        if ($SETTING['LTL_LIMIT'] < 50) {
            $SETTING['LTL_LIMIT'] = 50;
        }
        if (count($LTL) > $SETTING['LTL_LIMIT'] + 100) {
            $LTL = array_slice($LTL, 0, (int) $SETTING['LTL_LIMIT']);
        }
        ftruncate($LTLFILEHandle, 0);
        rewind($LTLFILEHandle);
        fwrite($LTLFILEHandle, json_encode($LTL, JSON_UNESCAPED_UNICODE));
        flock($LTLFILEHandle, LOCK_UN);
    }
    fclose($LTLFILEHandle);
    // datディレクトリがあるかチェック
    $directoryPath = $PATH . 'dat/';
    if (!file_exists($directoryPath)) {
        mkdir($directoryPath, 0777, true);
    }
    // 専ブラ用タイムライン (1000000000.dat)
    $TTL = array_reverse($LTL);
    $headText = safe_file_get_contents($PATH.'head.txt');
    if ($headText === false) {
        Error('heat.txtの取得に失敗しました。');
    }
    $headText = mb_convert_encoding($headText, 'UTF-8', 'SJIS-win');
    $headText = str_replace(["\r\n","\r","\n"], '', $headText);
    $kokutiText = safe_file_get_contents($PATH.'kokuti.txt');
    if ($kokutiText === false) {
        Error('kokuti.txtの取得に失敗しました。');
    }
    $kokutiText = str_replace(["\r\n","\r","\n"], '', $kokutiText);
    $tlDatData = 'ローカルルール<><>99/01/01 00:00:00 <>'.$headText."<>TL\n";
    $tlDatData .= '告知欄<><>99/01/01 00:00:00 <>'.$kokutiText."<>\n";
    foreach ($TTL as $tmp) {
        if (isset($tmp['thread'])) {
            $tt = '<br><hr>'.$tmp['title'].'<br>http://'.$_SERVER['HTTP_HOST'].'/test/read.cgi/'.$_POST['board'].'/'.$tmp['thread'].'/';
        } else {
            $tt = '';
        }
        $tlDatData .= $tmp['name'].'<>'.$tmp['mail'].'<>'.$tmp['date'].' '.$tmp['id'].'<>'.$tmp['comment'].$tt."<>\n";
    }
    $tlDatHandle = fopen($PATH.'dat/1000000000.dat', 'w');
    if (flock($tlDatHandle, LOCK_EX)) {
        fwrite($tlDatHandle, mb_convert_encoding($tlDatData, 'SJIS-win', 'UTF-8'));
        flock($tlDatHandle, LOCK_UN);
    }
    fclose($tlDatHandle);
}

include './extend/archive-thread.php';

// スレッド一覧 (subject.json)
if (!$tlonly) {
    // 停止済のスレッド
    if ($stop) {
        $subject = '[stop] '.$subject;
    }
    // 投稿先スレッド
    $posted = [
        'thread' => $_POST['thread'],
        'title' => $subject,
        'number' => $number,
        'date' => $NOWTIME,
    ];
    // subject.json更新
    $fileExists = is_file($subjectfile);
    $subjectfileHandle = fopen($subjectfile, 'c+');
    if (flock($subjectfileHandle, LOCK_EX)) {
        if ($fileExists) {
            $Threads = json_decode(fread($subjectfileHandle, filesize($subjectfile)), true);
        } else {
            $Threads = [];
        }
        $PAGEFILE = [];
        if (!$sage || $newthread) {
            $PAGEFILE[] = $posted;
        }
        // その他のスレッド
        foreach ($Threads as $thread) {
            if ((int) $thread['thread'] === (int) $_POST['thread']) {
                // sageの場合
                if ($sage && !$newthread) {
                    $PAGEFILE[] = $posted;
                }
            } else {
                $PAGEFILE[] = $thread;
            }
        }
        // 過去ログ化チェック
        if ($newthread) {
            // スレッド数を取得
            $ThreadCount = count($Threads) + 1;
            // 上限以下のスレッドを過去ログ化
            if ($ThreadCount > $SETTING['BBS_THREADS_LIMIT']) {
                for ($start = $SETTING['BBS_THREADS_LIMIT']; $start < $ThreadCount; $start++) {
                    // 過去ログへ送る
                    archiveThread(
                        $SETTING,
                        $KAKOLOGLIST,
                        $KAKOLOGLISTINDEX,
                        $THREAD_STATES_PATH."/{$PAGEFILE[$start]['thread']}.json",
                        $PATH.'thread/'.substr($PAGEFILE[$start]['thread'], 0, 4).'/'.$PAGEFILE[$start]['thread'].'.dat',
                        $PATH.'dat/'.$PAGEFILE[$start]['thread'].'.dat',
                        $PAGEFILE[$start]['thread'],
                        $PAGEFILE[$start]['title'],
                        $PAGEFILE[$start]['number'],
                        $PATH.'dat/'.$PAGEFILE[$start]['thread'].'_kisei.cgi',
                    );
                }
                $PAGEFILE = array_slice($PAGEFILE, 0, $SETTING['BBS_THREADS_LIMIT']);
            }
        }
        // !poolコマンド
        @include './extend/extra-commands/pool.php';
        // subject.jsonに書き込み
        ftruncate($subjectfileHandle, 0);
        rewind($subjectfileHandle);
        fwrite($subjectfileHandle, json_encode($PAGEFILE, JSON_UNESCAPED_UNICODE));
        flock($subjectfileHandle, LOCK_UN);
    }
    fclose($subjectfileHandle);
    // subject.txt (専ブラ無効でない場合のみ)
    if ($SETTING['2ch_dedicate_browsers'] !== 'disable') {
        $subjectTxtLines = array_merge(
            ["1000000000.dat<>TL (1)\n"],
            array_map(function ($thread) {
                return $thread['thread'].'.dat<>'.$thread['title'].' ('.$thread['number'].")\n";
            }, $PAGEFILE)
        );
        $subjectTxtData = mb_convert_encoding(implode('', $subjectTxtLines), 'SJIS-win', 'UTF-8');
        $subjectTxtHandle = fopen($PATH.'subject.txt', 'w');
        if (flock($subjectTxtHandle, LOCK_EX)) {
            fwrite($subjectTxtHandle, $subjectTxtData);
            flock($subjectTxtHandle, LOCK_UN);
        }
        fclose($subjectTxtHandle);
    }

}

// 投稿ログ
$LOG_LIMIT = 100000;
if ($SETTING['LOG_LIMIT'] !== '') {
    $LOG_LIMIT = min((int) $SETTING['LOG_LIMIT'], $LOG_LIMIT);
    if ($LOG_LIMIT < 0) {
        $LOG_LIMIT = 0;
    }
}
$logFileHandle = fopen($LOGFILE, 'a+');
if (flock($logFileHandle, LOCK_EX)) {
    // 新規ログを追記
    $newLog = $_POST['name'].'<>'.$_POST['mail'].'<>'.$DATE.' '.$ID.'<>'.$_POST['comment'].'<>'.$_POST['title'].'<>'.$_POST['thread'].'<>'.$number.'<>'.$HOST.'<>'.$_SERVER['REMOTE_ADDR'].'<>'.$_SERVER['HTTP_USER_AGENT'].'<>'.htmlspecialchars($CH_UA, ENT_NOQUOTES, 'UTF-8').'<>'.htmlspecialchars($ACCEPT, ENT_NOQUOTES, 'UTF-8').'<>'.$WrtAgreementKey.'<>'.$LV.'<>'.$info."\n";
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

// 記録
$HAP['last'] = $NOWTIME;
$HAP['comment'] = $_POST['comment'];
file_put_contents($hapfile, json_encode($HAP, JSON_UNESCAPED_UNICODE), LOCK_EX);

// ヘッダー送信
header('X-Resnum: '.$number);

// 投稿完了画面
finish();

// トリップを変換する関数
function nametrip($tripkey)
{
    // check
    preg_match('|^#(.*)$|', $tripkey, $keys);
    if (empty($keys[1])) {
        return false;
    }
    $tripkey = $keys[1];

    // start
    if (strlen($tripkey) >= 12) {
        // digit 12
        $mark = substr($tripkey, 0, 1);
        if ($mark === '#' || $mark === '$') {
            if (preg_match('|^#([[:xdigit:]]{16})([./0-9A-Za-z]{0,2})$|', $tripkey, $str)) {
                $trip = substr(crypt(pack('H*', $str[1]), "$str[2].."), -10);
            } else {
                // ext
                $trip = '???';
            }
        } else {
            $trip = substr(base64_encode(sha1($tripkey, true)), 0, 12);
            $trip = str_replace('+', '.', $trip);
        }
    } else { // 10 digits
        $salt = substr($tripkey.'H.', 1, 2);
        $salt = preg_replace("/[^\.-z]/", '.', $salt);
        $salt = strtr($salt, ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');
        $trip = substr(crypt($tripkey, $salt), -10);
    }
    $trip = '◆'.$trip;
    return $trip;
}
