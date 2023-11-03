<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
header("HTTP/1.1 403 Forbidden");
exit("403 Forbidden");
}
header('Content-Type: text/html; charset=UTF-8');
if (!isset($_POST['directory'])) $_POST['directory'] = '';
if (!isset($_POST['password'])) $_POST['password'] = '';
if (!isset($_POST['code'])) $_POST['code'] = '';
$file = "createcode.cgi";
$code = @file_get_contents($file);
if (strlen($_POST['directory']) == 0) Finish("ディレクトリ名が記入されていません");
if (strlen($_POST['directory']) > 16) Finish("ディレクトリ名は16文字以下で記入して下さい");
if (file_exists("../".$_POST['directory'])) Finish("ディレクトリ名は既に使用されています");
if (preg_match("/[^a-z0-9]/", $_POST['directory'])) Finish("ディレクトリ名には半角英数小文字のみ使用できます");
if (strlen($_POST['password']) == 0) Finish("パスワードが記入されていません");
if (strlen($code) > 0) {
 if (strlen($_POST['code']) == 0) Finish("作成コードが記入されていません");
 if ($_POST['code'] != $code) Finish("作成コードが無効です");
}
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

copy_dir("./board", "../".$_POST['directory']);
if (file_exists("../".$_POST['directory'])) {
 @file_put_contents("../".$_POST['directory']."/passfile.cgi", $password);
 $htaccess = '<Files ~ "\.cgi$">'."\n".'deny from all'."\n".'</Files>';
 @file_put_contents("../".$_POST['directory']."/.htaccess", $htaccess);
 // index.phpの置換
 $utf8 = $sjis = $_POST['directory'];
 file_put_contents("../".$_POST['directory']."/index.php", "<?php \$BBS_TITLE_UTF8 = \"{$utf8}\";\$BBS_TITLE_SJIS = \"{$sjis}\";include \"../test/board/index.php\";?>");

 header('Location: ../'.$_POST['directory'].'/');
 exit;
}else Finish("新規掲示板作成に失敗しました");

function Finish($value) {
header("HTTP/1.1 403 Forbidden");
?><!DOCTYPE HTML>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <link href="/static/a.css" rel="stylesheet" type="text/css">
</head>
<body>
  <?=$value?>
</body>
</html><?php
 exit; 
}

/**
 * ディレクトリをコピーする
 *
 * @param  string $dir     コピー元ディレクトリ
 * @param  string $new_dir コピー先ディレクトリ
 * @return void
 */
function copy_dir($dir, $new_dir) {
    $dir     = rtrim($dir, '/').'/';
    $new_dir = rtrim($new_dir, '/').'/';

    // コピー元ディレクトリが存在すればコピーを行う
    if (is_dir($dir)) {
        // コピー先ディレクトリが存在しなければ作成する
        if (!is_dir($new_dir)) {
            mkdir($new_dir);
        }

        // ディレクトリを開く
        if ($handle = opendir($dir)) {
            // ディレクトリ内のファイルを取得する
            while (false !== ($file = readdir($handle))) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                // 下の階層にディレクトリが存在する場合は再帰処理を行う
                if (is_dir($dir.$file)) {
                    copy_dir($dir.$file, $new_dir.$file);
                } else {
                    copy($dir.$file, $new_dir.$file);
                }
            }
            closedir($handle);
        }
    }
}