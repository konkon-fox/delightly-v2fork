<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);
ob_start();
header("Accept-CH: Sec-CH-UA-Arch, Sec-CH-UA-Bitness, Sec-CH-UA-Full-Version-List, Sec-CH-UA-Mobile, Sec-CH-UA-Model, Sec-CH-UA-Platform, Sec-CH-UA-Platform-Version");
header('Content-Type: text/html; charset=UTF-8');
if (!isset($_POST['board'])) $_POST['board'] = '';
if (!isset($_POST['thread'])) $_POST['thread'] = '';
if (!isset($_POST['comment'])) $_POST['comment'] = '';
if (!isset($_POST['name'])) $_POST['name'] = '';
if (!isset($_POST['mail'])) $_POST['mail'] = '';
if (!isset($_POST['title'])) $_POST['title'] = '';

$PATH = "../".$_POST['board']."/";
$NOWTIME = time();

// User-AgentにMozilla/5.0を含まない場合は拒否
if (strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla/5.0') === false) Error2("invalid");

// 投稿先の掲示板設定を取得
$setfile = $PATH."setting.json";
if (!is_file($setfile)) Error2("This board does not exist.");
$SETTING = json_decode(file_get_contents($setfile), true);

require "bbs-main.php";

// 投稿完了画面
function finish() {
 global $NOWTIME,$tlonly;
 if ($tlonly) header('Location: /'.$_POST['board'].'/');
 else header('Location: /#'.$_POST['board'].'/'.$_POST['thread'].'/');
 setcookie("response", "success", $NOWTIME+5, "/");
 exit;
}

// エラーメッセージ表示用関数
function Error($error) {
 global $NOWTIME,$PATH,$HOST,$DATE,$ID,$WrtAgreementKey,$number,$CH_UA,$ACCEPT,$accountid,$LV,$info;
 // エラーログに保存
 if (is_file($PATH."errors.cgi")) $EROG = file($PATH."errors.cgi");
 else $EROG = [];
 array_unshift($EROG, $error."<>".$_POST['name']."<>".$_POST['mail']."<>".$DATE." ".$ID."<>".$_POST['comment']."<>".$_POST['title']."<>".$_POST['thread']."<>".$number."<>".$HOST."<>".$_SERVER['REMOTE_ADDR']."<>".$_SERVER['HTTP_USER_AGENT']."<>".$CH_UA."<>".$ACCEPT."<>".$WrtAgreementKey."<>".$LV."<>".$info."\n");
 // 500 個以内に調整して保存
 while (count($EROG) > 500) array_pop($EROG);
 $EROG = array_unique($EROG);
 $fp = @fopen($PATH."errors.cgi", "w");
 foreach($EROG as $tmp) fputs($fp, $tmp);
 fclose($fp); 
 Header("HTTP/1.0 418 I'm a teapot");
 setcookie("response", mb_convert_encoding($error, 'HTML-ENTITIES', 'UTF-8'), $NOWTIME+5, "/");
 exit($error);
}
function Error2($error) {
 global $NOWTIME;
 Header("HTTP/1.0 403 Forbidden");
 setcookie("response", mb_convert_encoding($error, 'HTML-ENTITIES', 'UTF-8'), $NOWTIME+5, "/");
 exit($error);
}

function makeDir($path) {
 return is_dir($path) || mkdir($path, 0777, true);
}