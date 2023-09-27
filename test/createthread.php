<?php
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);
$msg = $subject = '';
if (!$_GET['bbs']) exit;
if ($_GET['key']) {
 $thread_file = "../".$_GET['bbs']."/thread/".substr($_GET['key'], 0, 4)."/".$_GET['key'].".dat";
 if (is_file($thread_file)) {
  $LOG = file($thread_file);
  list(,,,$msg,$subject) = explode("<>",$LOG[0]);
  $msg = str_replace("<br>", "\n", $msg);
  $msg = strip_tags($msg, ['details', 'summary']);
  $msg .= "\n\n前スレ\n".$subject."https://".$_SERVER['HTTP_HOST']."/#".$_GET['bbs']."/".$_GET['key']."/"; 
 }
}
// 次スレ作成処理
?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
<meta name="application-name" content="delight">
<meta name="referrer" content="origin">
<title>新規スレッド作成</title>
<link href="/static/st.css" rel="stylesheet" type="text/css"><link href="/static/milligram.css" rel="stylesheet" type="text/css"><link href="/static/s.css" rel="stylesheet" type="text/css"><link href="/static/read.css" rel="stylesheet" type="text/css"></head>
</head>
<body>
<div id="followheader" class="hidden maxwidth100 height2p5 stickymenu container" style="display: block;"><div class="row noflex maxwidth100 white padding0p5 maxheight2p5 borderbottomlightgrey"><div class="topmenu" id="header"><a class="menuitem" href="/<?=$_GET['bbs']?>/">TL</a><a class="menuitem" href="/<?=$_GET['bbs']?>/?m=subback">スレ一覧</a><a class="menuitem" href="/<?=$_GET['bbs']?>/?m=subback&mode=history">履歴</a><a class="menuitem" href="/<?=$_GET['bbs']?>/?m=subback&mode=unread">未読</a><b class="menuitem">スレ作成</b><div id="headtitle" class="menuitem" style="text-decoration: none;"></div></div></div></div>
<div id="subbacktitle"></div>
<div style="min-height:50px;margin-top: 3rem;" id="rule">■ ローカルルール<br></div>
<div style="min-height:50px" id="kokuti">■ 告知欄<br></div>
<div class="formbox" style="margin-top: 4rem;"><form method="POST" id="Form" accept-charset="UTF-8" action="/test/post-v2.php" style="display: block;margin-top: 2rem;"> <span class="fhead" style="border-radius: 3px 3px 0 0;color: #666;padding: 3px;text-align: left;"><span class="spformhead" style="display:inline !important;border-radius:3px 3px 0 0;color:#666;padding:3px;"><a id="iconlink" target="_blank" href="<?=$_COOKIE['homepage']?>"><img id="icon" class="icon" src="<?=$_COOKIE['icon']?>" width="50" height="50" align="left"></a><input name="name" placeholder="名前" class="formelem maxwidth" style="width:50%;margin-top:.5rem;"></span><span class="submitbtn" style="color: #000;float: right;font-size: 28px;font-weight: bold;cursor: pointer;display: inline-block;padding-right: 5px;"><input type="submit" value="新規投稿" name="submit" onclick="Post()"></span></span><input name="mail" size="19" value="" placeholder="E-mail" class="formelem maxwidth"><div class="backmsg" style="display: inline-block;"><input value="on" type="checkbox" id="seticon" name="icon" checked="" <?php if (!$_COOKIE['icon']) echo 'style="display:none"'; ?>><a href="/test/icon.html">アイコン</a> <a href="javascript:BackMSG()">下書きを復元</a></div><textarea rows="5" cols="70" name="comment" id="bbs-textarea" onchange="MSG()" class="formelem maxwidth" wrap="off"><?=$msg?></textarea><input name="title" size="19" value="<?=$subject?>" placeholder="スレッドタイトル" class="formelem maxwidth"><br>画像：<input id="uploadImage" type="file" name="file" size="50" onchange="upload();"><br><input type="hidden" name="board" value="<?=$_GET['bbs']?>"></form></div>
<div class="tlnotice" id="ntxt"></div>
<p style="margin:5rem 0;color:#333">We're sure you'll have a good time with delight and/or 3ch. :)</p>
<style>div.postbutton{display: none !important;}</style>
<script src="/static/textarea.js"></script>
<script src="/static/jquery-1.11.3.min.js"></script>
<script>
if (localStorage.getItem('css') === null) localStorage.setItem('css', '');
document.body.innerHTML += "<style>"+localStorage.getItem('css')+"</style>";
function isSmartPhone() {
let v = false;
  if (navigator.userAgent.match(/iPhone|Android.+Mobile/)) {
    v = true;
  } else {
    v = false;
  }
 if (localStorage.getItem('viewer') == 'sp') v = true;
 else if (localStorage.getItem('viewer') == 'pc') v = false;
 if (location.search.indexOf('v=sp') != -1) v = true;
 if (location.search.indexOf('v=pc') != -1) v = false;
	return v;
}
if (isSmartPhone() == true) document.body.innerHTML += '<link href="/static/sp.css" rel="stylesheet">';
if (localStorage.getItem('darkmode') == "true" || (localStorage.getItem('autodark') == "true" && window.matchMedia('(prefers-color-scheme: dark)').matches === true)) {
 document.body.innerHTML += '<link href="/static/dark.css" rel="stylesheet"><style>#header{color:#333}</style>';
}
  let imgurkey = '';
  if (localStorage.getItem('text2') === null) localStorage.setItem('text2', '');

let bbs = "<?=$_GET['bbs']?>";
setfile = '/'+bbs+'/setting.json';
rulefile = '/'+bbs+'/head.txt';
kokutifile = '/'+bbs+'/kokuti.txt';
 if (document.getElementById('headtitle')) {
	const sroad = new XMLHttpRequest();
	sroad.open('get', setfile);
	sroad.send();
	sroad.onreadystatechange = function() {
		if(sroad.readyState === 4 && sroad.status === 200) {
			const setting = JSON.parse(this.responseText);
			if (setting['BBS_TITLE']) {
				document.getElementById('headtitle').innerHTML = setting['BBS_TITLE'];
				document.getElementById('subbacktitle').innerHTML = setting['BBS_TITLE'];
			}
		}
	}
 }
 if (document.getElementById('rule')) {
	const lroad = new XMLHttpRequest();
	lroad.open('get', rulefile);
	lroad.send();
	lroad.onreadystatechange = function() {
		if(lroad.readyState === 4 && lroad.status === 200) {
			document.getElementById('rule').innerHTML += this.responseText;
		}
	}
 }
 if (document.getElementById('kokuti')) {
	const kroad = new XMLHttpRequest();
	kroad.open('get', kokutifile);
	kroad.send();
	kroad.onreadystatechange = function() {
		if(kroad.readyState === 4 && kroad.status === 200) {
			document.getElementById('kokuti').innerHTML += this.responseText;
		}
	}
 }

function l(e){
 var N=getCookie("NAME"),M=getCookie("MAIL"),i;
 with(document) for(i=0;i<forms.length;i++)if(forms[i].name&&forms[i].mail)with(forms[i]){
  name.value=N;
  mail.value=M;
 }
}
onload=l;
function getCookie(key, tmp1, tmp2, xx1, xx2, xx3) {
 tmp1 = " " + document.cookie + ";";
 while(tmp1.match(/\+/)) {tmp1 = tmp1.replace("+", " ");};
 xx1 = xx2 = 0;
 len = tmp1.length;
 while (xx1 < len) {
  xx2 = tmp1.indexOf(";", xx1);
  tmp2 = tmp1.substring(xx1 + 1, xx2);
  xx3 = tmp2.indexOf("=");
  if (tmp2.substring(0, xx3) == key) {return(unescape(tmp2.substring(xx3 + 1, xx2 - xx1 - 1)));}
  xx1 = xx2 + 1;
 }
 return("");
}

if (!getCookie("WrtAgreementKey")) document.getElementById('Form').innerHTML = '<div><a href="/test/auth.php">投稿時に使用する同意鍵がありません。<br>投稿を行うには投稿前確認画面での同意が必要です。</a></div>'+document.getElementById('Form').innerHTML;

function upload() {
  const preview = document.querySelector('img');
  const file = document.querySelector('input[type=file]').files[0];
  const reader = new FileReader();

  reader.addEventListener("load", () => {
    base64Url = reader.result;
    base64 = base64Url.replace(new RegExp('data.*base64,'), '');
  imgur();
  }, false);

  if (file) {
    reader.readAsDataURL(file);
  }
  /// APIに渡すときは先頭の data:~~~base64 を除外

function imgur() {
 document.getElementById('ntxt').innerHTML = '通信中';
$.ajax({
  url: 'https://api.imgur.com/3/image',
  method: 'POST',
  headers: {
  "Authorization": 'Client-ID '+imgurkey
  },
  data: {
    image: base64,
    type: 'base64'
  },
  success: function(r){
  imgurlink = r.data.link
  document.getElementById('bbs-textarea').value += '\n'+imgurlink; 
  document.getElementById('ntxt').innerHTML = '画像をアップロードしました';
  },

  error: function () {
  document.getElementById('ntxt').innerHTML = 'アップロードできません';
  }
});

}

}

function Post() {
 let cookname = escape(document.getElementsByName("name")[0].value); 
 document.cookie = "NAME="+cookname+"; Max-Age=7776000; path=/";
 let cookmail = escape(document.getElementsByName("mail")[0].value);
 document.cookie = "MAIL="+cookmail+"; Max-Age=7776000; path=/";
}

function MSG() {
 if (document.getElementById('bbs-textarea').value) {
  sessionStorage.setItem('text', document.getElementById('bbs-textarea').value);
  localStorage.setItem('text2', document.getElementById('bbs-textarea').value);
 }
}

function BackMSG() {
 document.getElementById('bbs-textarea').value = localStorage.getItem('text2');
}
</script>
</body>
</html>