<?php

/**
 * 自身の同意鍵を忘れた際にクッキーに保存した同意鍵情報を投稿者自身にのみ表示するコマンド。
 * 返り値のhtmlタグ中のtitleとbody内に「書き込み」という文字が無ければ書き込みエラーとして返される？

 * 専ブラではbbs.php,webブラウザ板ではpost-v2.phpにpostされる。Error関数もそれぞれ違うので処理を分岐させる。
 * 専ブラではhtmlを返し、表示させる。
 * webブラウザでは未定…かなり面倒そう。
 *
 * @param array $SETTING
 */
function applyMykeyCommand($SETTING)
{
    if ($SETTING['commands'] !== 'checked') {
        return;
    }
    if (strpos($_POST['name'], '!nocmd') !== false) {
        return;
    }
    if (strpos($_POST['comment'], '!ninkey') === false) {
        return;
    }
    if (empty($_COOKIE['WrtAgreementKey'])) {
        $message = '<br><br><font color=#FF0000>ERROR:</font> Cookieに同意鍵情報が存在しません。';
    } else {
        $message = "<br><br>あなたの同意鍵は<br><b><font color=#FF0000><code>#{$_COOKIE['WrtAgreementKey']}</font></b><br>です。";
    }
    $backtrace = debug_backtrace();
    $backtrace_file = end($backtrace)['file'];
    // 専ブラの場合
    if (preg_match('/bbs\.php$/', $backtrace_file)) {
        $html = "<html>
        <head>
        <title>同意鍵情報</title>
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=Shift_JIS\">
        </head>
        <body>{$message}</body>
        </html>";
        echo mb_convert_encoding($html, "SJIS-win", "UTF-8");
        exit;
    }
    // 通常webブラウザの場合
    if (preg_match('/post-v2\.php$/', $backtrace_file)) {
        addSystemMessage('★現在通常ブラウザからの!ninkeyは実装されていません。');
    }
}

applyMykeyCommand($SETTING);
