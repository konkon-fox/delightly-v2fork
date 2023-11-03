<?php
// 汎用ブラウザ識別文字列
$BROWSERS = ['Gecko', 'AppleWebKit', 'Chrome'];

$CLIENT_UA = $_SERVER['HTTP_USER_AGENT'];
$isUtf8 = false;
if (stripos($CLIENT_UA, 'Monazilla') === false) {
	foreach ($BROWSERS as $UA) {
		if (stripos($CLIENT_UA, $UA) !== false) {
			$isUtf8 = true;
			break;
		}
	}
}

// UTF-8
if ($isUtf8 === true) {
	$charset = 'utf-8';
	$bbsTitle = $BBS_TITLE_UTF8;
	header('Content-type: text/html; charset=UTF-8');
}
// Shift_JIS
else {
	$charset = 'shift_jis';
	$bbsTitle = $BBS_TITLE_SJIS;
	header('Content-type: text/html; charset=Shift_JIS');
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="<?= $charset ?>">
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
<meta name="application-name" content="delightly">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<link rel="apple-touch-icon" href="../icon.png">
<link rel="icon" href="../favicon.ico">
<meta name="referrer" content="origin">
<title><?= $bbsTitle ?></title>
</head>
<body>
<table width="95%" cellspacing="7" cellpadding="3" border="1" bgcolor="#CCFFCC" align="center"><tbody><tr><td align="center">
<table width="100%" cellpadding="1" border="0">
<tbody><tr>
<td><font size="+1"><b><?= $bbsTitle ?></b></font></td>
<td width="5%" valign="top" nowrap="" align="right"><a href="#menu">■</a> <a href="#t-1">▼</a></td>
</tr>
<tr><td colspan="3"><noscript><p>JavaScriptを有効にしてください<br>Please turn on your JavaScript</p></noscript></td></tr>
</tbody>
</table>
</body>
<script type="text/javascript" src="/static/index.js"></script>
</html>