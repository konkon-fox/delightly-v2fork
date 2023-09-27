<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);
ob_start();
if (function_exists('sys_getloadavg')) {
 $loadavg = sys_getloadavg(); //LoadAverage���擾
 if ($loadavg !== false) {
  // LA200�ȏ�͋���
  if ($loadavg[0] > 200) Error2("���ݍ����ׂ̂��߁Abbs.cgi���ꎞ�I�ɒ�~���Ă��܂��B���萔�ł����AWeb�ł���̓��e�����肢���܂��B -> LoadAverage:".$loadavg[0]);
  if ($loadavg[0] > 50 && (empty($_POST['mail']) || strlen($_POST['mail']) !== 9) && (empty($_COOKIE['WrtAgreementKey']) || strlen($_COOKIE['WrtAgreementKey']) !== 8)) finish();
 }
}

// ��u���p�Ȃ̂�Shift_JIS�ŏo��
header('Content-Type: text/html; charset=Shift_JIS');
if (!isset($_POST['bbs'])) $_POST['bbs'] = '';
if (!isset($_POST['key'])) $_POST['key'] = '';
if (!isset($_POST['MESSAGE'])) $_POST['MESSAGE'] = '';
if (!isset($_POST['FROM'])) $_POST['name'] = '';
if (!isset($_POST['mail'])) $_POST['mail'] = '';
if (!isset($_POST['subject'])) $_POST['subject'] = '';
$PATH = "../".$_POST['bbs']."/";
$NOWTIME = time();

// �ꕔ����ȃA�v�����L�邽��v2�ł�Monazilla�ȊO��UA�����e����B

// ���e��̌f���ݒ���擾
$setfile = $PATH."setting.json";
if (!is_file($setfile)) Error2("This board does not exist.");
$SETTING = json_decode(file_get_contents($setfile), true);

// ��u�����e��������Ă��Ȃ��ꍇ�͂����ŋ���
if ($SETTING['2ch_dedicate_browsers'] != "enable") Error2("invalid:2ch dedicate browsers is forbidden.");

// ��u���Ȃ̂�time�Ȃ�
if (!$_POST['time']) Error2("invalid");

// Shift_JIS����UTF-8��
mb_convert_variables('UTF-8','SJIS-win',$_POST);

$_POST['comment'] = $_POST['MESSAGE'];
$_POST['title'] = $_POST['subject'];
$_POST['name'] = $_POST['FROM'];
$_POST['board'] = $_POST['bbs'];
$_POST['thread'] = $_POST['key'];

require "bbs-main.php";

// ���e�������
function finish() {
?><html><head><title>�������݂܂����B</title><meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS"></head><body>�������݂��I���܂����B<br><br>��ʂ�؂�ւ���܂ł��΂炭���҂��������B</body></html>
<?php exit;
}

// �e��K���Ȃ�(��u�������ɐ��`)
function Error($error) {
 global $PATH,$HOST,$DATE,$ID,$WrtAgreementKey,$number,$CH_UA,$ACCEPT,$accountid,$LV,$info;
 // �G���[���O�ɕۑ�
 if (is_file($PATH."errors.cgi")) $EROG = file($PATH."errors.cgi");
 else $EROG = [];
 array_unshift($EROG, $error."<>".$_POST['name']."<>".$_POST['mail']."<>".$DATE." ".$ID."<>".$_POST['comment']."<>".$_POST['title']."<>".$_POST['thread']."<>".$number."<>".$HOST."<>".$_SERVER['REMOTE_ADDR']."<>".$_SERVER['HTTP_USER_AGENT']."<>".$CH_UA."<>".$ACCEPT."<>".$WrtAgreementKey."<>".$LV."<>".$info."\n");
 // 500 �ȓ��ɒ������ĕۑ�
 while (count($EROG) > 500) array_pop($EROG);
 $EROG = array_unique($EROG);
 $fp = @fopen($PATH."errors.cgi", "w");
 foreach($EROG as $tmp) fputs($fp, $tmp);
 fclose($fp); 
?><head>
<title>�d�q�q�n�q�I</title>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
</head>
<body bgcolor="#FFFFFF">
<font size=+1 color=#FF0000><b>ERROR: <?php echo mb_convert_encoding($error, "SJIS-win", "UTF-8"); ?></b></font>
</body>
</html>
<?php exit;
}

// ���̑��̃G���[
function Error2($error) {
?><head>
<title>�d�q�q�n�q�I</title>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
</head>
<body bgcolor="#FFFFFF">
<font size=+1 color=#FF0000><b>ERROR: <?=$error?></b></font>
</body>
</html>
<?php exit;
}

function makeDir($path) {
 return is_dir($path) || mkdir($path, 0777, true);
}	