<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['edit'] == "yes") {
 file_put_contents($PATH."cap.cgi", $_POST['cap']);
}
$cap = implode('', file($PATH."cap.cgi"));
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content>
<meta name="author" content>
<title>キャップ</title>
<link rel="stylesheet" href="/static/a.css">
</head>
<body>
<div class="main">
<form method="post" action="/test/admin.php?bbs=<?=$_REQUEST['bbs']?>&mode=cap">
<input type="hidden" name="password" value="<?=$_REQUEST['password']?>">
<input type="hidden" name="edit" value="yes">
<div class="back"><a href="./admin.php">← 管理ページへ戻る</a></div>
<div><b>キャップ</b></div><div><small class="notice mt5">掲示板TOP画面に表示されます</small></div>
<div class="notice mt5">
・&lt;&gt;が区切り文字、<b>名前</b>&lt;&gt;<b>パスワード</b>&lt;&gt;<b>権限</b>&lt;&gt;<b>ID</b><br>
・権限の欄が未記入の場合の権限(一般キャップ)：<b>名前表示、ID秘匿、常時コマンド使用、一部規制回避、スレッド作成権限</b><br>
・権限の欄に<b>saku</b>を入れた場合：<b>一般キャップの権限に削除権限を加える</b><br>
・権限の欄に<b>sakud</b>を入れた場合：<b>削除権限、名前表示のみ</b><br>
・権限の欄に<b>plus</b>を入れた場合：<b>記者・立て子(名前表示+スレッド作成権限のみ)</b><br>
・権限の欄に<b>authorized</b>を入れた場合：キャップ使用時に<b>承認済ユーザー扱い</b>となる(ID欄に記入されている場合はそのIDを表示する。※名前は表示されませんが<b>アカウント・トリップ・Cookie</b>の欄に記入することで個別の規制を適用可能)<br>
・ID欄を設定するとキャップの種類に関わらず、<b>@&#60;設定したID&#62;</b>の形式で表示されます。ID秘匿権限が有るキャップでID欄が無記入の場合、<b>@CAP_USER</b>が表示されます。
・使用時は <b>Email欄</b>に<b>#パスワード</b>の形で記入して投稿してください。<br>
・authorized権限を除き名前欄に記入したものが投稿時の名前として表示されます。<br>
・一行につき一つのキャップです。<br>
・行頭に半角の『#』を含んでいる行はコメント行として扱われます。<br>
</div>
<div><textarea style="font-size:9pt" rows="10" cols="70" name="cap" wrap="OFF"><?=$cap?></textarea></div>
<hr><div class="contents"><input type="submit" name="Submit" class="btn btn-primary btn-block" value="適用"></div>
</form>
</div>
</body>
</html>
<?php exit;