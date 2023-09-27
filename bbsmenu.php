<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<TITLE>BBS MENU for <?=$_SERVER['HTTP_HOST']?></TITLE>
<BASE TARGET="_blank">
</HEAD>
<BODY BGCOLOR="SEASHELL" TEXT="#E60565" LINK="#444494" ALINK="#a99999" VLINK="#444494">
<font size="2">
<br><br><B><?=$_SERVER['HTTP_HOST']?></B><br>
<?php
$dir = './';
$list1 = glob($dir . '*', GLOB_ONLYDIR);
$count = 0;
foreach ($list1 as $dir1) {
	++$count;
	$dir1 = substr($dir1, 1)."/";
	if ($dir1 == "/test/" || $dir1 == "/static/" || $dir1 == "/images/") continue;
	echo '<A HREF="http://'.$_SERVER['HTTP_HOST'].$dir1.'">'.$dir1.'</A>';
	if ($count != count($list1)) echo "<br>";
	echo "\n";
}
?></FONT>
</BODY>
</HTML>