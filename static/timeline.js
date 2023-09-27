  let imgurkey = '';
  let spcheck = '';
  let loaded = false;
  if (isSmartPhone() == true) spcheck = 'checked';
  let LINENUM,reloadbutton,getprev,preved;
  if (localStorage.getItem('ghide') === null) localStorage.setItem('ghide', 'auto');
  if (localStorage.getItem('areload') === null) localStorage.setItem('areload', false);
  if (localStorage.getItem('darkmode') === null) localStorage.setItem('darkmode', false);
  if (localStorage.getItem('autodark') === null) localStorage.setItem('autodark', true);
  if (localStorage.getItem('autoscroll') === null) localStorage.setItem('autoscroll', false);
  if (localStorage.getItem('origdate') === null) localStorage.setItem('origdate', false);
  localStorage.setItem('autoPost', true);
  localStorage.setItem('backlinkOpen', false);
  localStorage.setItem('ankfixed', true);
  if (localStorage.getItem('text2') === null) localStorage.setItem('text2', '');
  sessionStorage.removeItem('text');
  ghide = localStorage.getItem('ghide');
  areload = localStorage.getItem('areload');
  ankfixed = localStorage.getItem('ankfixed');
  darkmode = localStorage.getItem('darkmode');
  autodark = localStorage.getItem('autodark');
  autoscroll = localStorage.getItem('autoscroll');
  origdate = localStorage.getItem('origdate');
  if (autodark == "true" && window.matchMedia('(prefers-color-scheme: dark)').matches === true) darkmode = "true";
  let mutejson;
  let mutelist = [];
  mutejson = localStorage.getItem('mutelist');
  if (mutejson) {
  mutelist = JSON.parse(mutejson);
  mutelist = mutelist.filter(Boolean);
  }
  let ngjson;
  let nglist = [];
  ngjson = localStorage.getItem('nglist');
  if (ngjson) {
  nglist = JSON.parse(ngjson);
  nglist = nglist.filter(Boolean);
  }
  let ntjson;
  let ntlist = [];
  ntjson = localStorage.getItem('ngtitle');
  if (ntjson) {
  ntlist = JSON.parse(ntjson);
  ntlist = ntlist.filter(Boolean);
  }
let target;
if (localStorage.getItem('timeline')) target = localStorage.getItem('timeline');
const ls = new URLSearchParams(window.location.search);
let path = location.pathname.split('/');
let bbs = path[1];
let requestURL = '/'+bbs+'/index.json?'+time();
let n = 0;
document.head.innerHTML += '<link href="/static/st.css" rel="stylesheet" type="text/css"><link href="/static/milligram.css" rel="stylesheet" type="text/css"><link href="/static/s.css" rel="stylesheet" type="text/css"><link href="/static/read.css" rel="stylesheet" type="text/css"><link href="/static/lightbox.css" rel="stylesheet">';
document.body.innerHTML = '<section class="section"><div id="body"><div id="followheader" class="hidden maxwidth100 height2p5 stickymenu container" style="display: block;"><div class="row noflex maxwidth100 white padding0p5 maxheight2p5 borderbottomlightgrey"><div class="topmenu" id="header"><b class="menuitem">TL</b><a class="menuitem" href="/'+bbs+'/?m=subback">スレ一覧</a><a class="menuitem" href="/'+bbs+'/?m=subback&mode=history">履歴</a><a class="menuitem" href="/'+bbs+'/?m=subback&mode=unread">未読</a><a class="menuitem" href="/test/createthread.php?bbs='+bbs+'">スレ作成</a><a class="menuitem" href="javascript:Menu()">メニュー</a><a href="javascript:MenuClick(\'setting\')" class="menuitem">設定</a><div id="headtitle" class="menuitem" style="text-decoration: none;"></div></div></div></div><div id="subbacktitle"></div><div class="formbox" style="margin-top: 3rem;"><form method="POST" id="Form" accept-charset="UTF-8" action="/test/post-v2.php" style="display: block;margin-top: 2rem;"> <span class="fhead" style="border-radius: 3px 3px 0 0;color: #666;padding: 3px;text-align: left;"><span class="spformhead" style="display:inline !important;border-radius:3px 3px 0 0;color:#666;padding:3px;"><a id="iconlink" target="_blank"><img id="icon" class="icon" width="50" height="50" align="left"></a><input name="name" placeholder="名前" class="formelem maxwidth" style="width:50%;margin-top:.5rem;"></span><span class="submitbtn" style="color: #000;float: right;font-size: 28px;font-weight: bold;cursor: pointer;display: inline-block;padding-right: 5px;"><input type="submit" value="新規投稿" name="submit" onclick="Post()"></span></span><input name="mail" size="19" value="" placeholder="E-mail" class="formelem maxwidth"><div class="backmsg" style="display: inline-block;"><input value="on" type="checkbox" id="seticon" name="icon" checked=""><a href="/test/icon.html">アイコン</a> <a href="javascript:BackMSG()">下書きを復元</a></div><textarea rows="5" cols="70" name="comment" id="bbs-textarea" onchange="MSG()" class="formelem maxwidth" wrap="off"></textarea><br>画像：<input id="uploadImage" type="file" name="file" size="50" onchange="upload();"><br><input type="hidden" name="board" value="'+bbs+'"></form></div><div class="tlnotice" id="ntxt"></div><div class="newposts"><center><a href="javascript:load()">リロード<img alt="再読み込み" loading="lazy" decoding="async" data-nimg="1" style="color: transparent;" src="/static/reload.svg" width="16" height="16"></a></center><hr></div><div id="thread" class="thread"></div><div id="hidemetoo" class="side p85 slightpad nobullets"><div style="min-height:50px" id="rule">■ ローカルルール<br></div><div style="min-height:50px" id="kokuti">■ 告知欄<br></div><p style="margin: 5rem 0"></p></div><div class="footer">We\'re sure you\'ll have a good time with delight and/or 3ch. :)</div></div></section>';
if (localStorage.getItem('css') === null) localStorage.setItem('css', '');
document.body.innerHTML += "<style>"+localStorage.getItem('css')+"</style>";
let threadjs = document.createElement("script");
threadjs.src = "/static/thread.js";
document.body.appendChild(threadjs);
let boardjs = document.createElement("script");
boardjs.src = "/static/board.js";
document.body.appendChild(boardjs);
let textareajs = document.createElement("script");
textareajs.src = "/static/textarea.js";
document.body.appendChild(textareajs);
let jqueryjs = document.createElement("script");
jqueryjs.src = "/static/jquery-1.11.3.min.js";
document.body.appendChild(jqueryjs);

if (isSmartPhone() == false) document.getElementById('body').innerHTML += '<style>body{color:rgb(87, 111, 118) !important;}a{color:#485269 !important;}.thread,.title,.pagestats,.newposts,.formbox,.topmenu{padding-left:15px;padding-right:15px}.post{padding:1em 0}.number,span.name,span.date,span.ids,details{font-size:12px;color:rgb(87, 111, 118) !important;margin-right:5px;padding-left:0;margin-left:0}.thread,.newposts,.formbox{width: 75%;}a.id{color:rgb(87, 111, 118) !important;}.message{padding:10px 0;font-size:16px;min-height:4em}.side{display: block;border: .5px solid #DCDCDC; position: fixed; right: 0em; top: 0em; bottom: auto; width: 25%; height: 100%; z-index:  1; margin: 0; padding: 0; color: #333; padding-top: 10em; overflow: auto;scrollbar-width: none;}.side::-webkit-scrollbar{display: none;}#headtitle, b {color: rgb(87, 111, 118) !important;}</style>';
document.getElementById('body').innerHTML += '<style>.subject {background: gray;color: #fff;}div.postbutton{display: none !important;}</style>';

function load() {
 document.getElementById('thread').innerHTML = '';
 n = 0;
 request = new XMLHttpRequest();
 request.open('GET', requestURL);
 request.responseType = 'json';
 request.setRequestHeader('Pragma', 'no-cache');
 request.setRequestHeader('Cache-Control', 'no-cache');
 request.send();
 request.onload = function() {
  let listJSON = JSON.parse(JSON.stringify(request.response));
  listJSON.forEach(function(post) {
  let NG = false;
  n++;
  let name = post['name'];
  let mail = post['mail'];
  let date = TimeDiff(post['date']);
  let id = post['id'];
  let comment = post['comment'];
  let thread = post['thread'];
  let title = post['title'];
  if (!thread) title = '';
  if (title && target) {
   if (title.search(target) == -1) return;
  }
  id = id.replace('ID:', '@');
  if (isSmartPhone() == true) {
   name = name.replace('<b>', '');
   name = name.replace('</b>', '');
  }
  if (!NG) NG = MuteCheck(id);
  if (!NG) NG = WordCheck(name+mail+comment);
  if (!NG) NG = TitleCheck(title);
  if (NG) return;
  let newpost = document.createElement("div");
  newpost.className = 'post '+mail;
  if (id) newpost.className += ' '+id;
  newpost.id = n;
  if (isSmartPhone() == false) newpost.innerHTML = '<a class="number" href="javascript:reply(\''+mail+'\')">'+n+'</a><span class="name"><b>'+name+'</b></span><span class="date">'+date+' <a class="id" href="javascript:IdClick(\''+id+'\')">'+id+'</a> ['+mail+']</span><div class="message">'+comment+'</div><a href="/#'+bbs+'/'+thread+'/"><div class="subject">'+title+'</div></a>';
  else newpost.innerHTML = '<a class="number" href="javascript:reply(\''+mail+'\')">'+n+'</a><span class="name">'+name+'</span><div class="message">'+comment+'</div><span class="date"><a class="id" href="javascript:IdClick(\''+id+'\')">'+id+'</a>'+date+' ['+mail+']</span><a href="/#'+bbs+'/'+thread+'/"><div class="subject">'+title+'</div></a>';
  document.getElementById('thread').appendChild(newpost);
  });
  if (!loaded && ls.has('id')) IdClick(ls.get('id'));
  loaded = true;
 }
}

load();
loadready();

let modal = document.createElement('div');
modal.innerHTML = '<div id="Modal" style="background-color:#fafafa;margin:auto;padding:20px;border:1px solid #888;width:500px;max-width:95%;"><span style="color:#000;float:right;font-size:28px;font-weight:bold;cursor:pointer;" class="rclose" onclick="rclose()">×</span><div id="modal_text" style="width: 100%;color:#808080;font-family: arial,helvetica,sans-serif;font-size:10pt;margin: 2em 4px 0 0;">'+
'</div></div>';
modal.id = 'rModal';
modal.style.cssText = 'position: fixed; z-index: 20; left: 0px; top: 0px; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.4);';
modal.style.display = 'none';
document.getElementById('body').appendChild(modal);

function Post() {
 let cookname = escape(document.getElementsByName("name")[0].value); 
 document.cookie = "NAME="+cookname+"; Max-Age=7776000; path=/";
 let cookmail = escape(document.getElementsByName("mail")[0].value);
 document.cookie = "MAIL="+cookmail+"; Max-Age=7776000; path=/";
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

function loadready() {
	if (typeof jQuery === 'undefined') {
		setTimeout(loadready, 50);
		return;
	}
let lightbox = document.createElement("script");
lightbox.src = "/static/lightbox.js";
document.getElementById('body').appendChild(lightbox);
let t2 = document.createElement("script");
t2.src = "/static/t2.js";
document.getElementById('body').appendChild(t2);
$(document).ready(function(){
	$('#linkToTop').on('click',function(e){
		e.preventDefault();
		$('html,body').scrollTop(0);
	});

	$(document).on("click",".up",function(e){
		$("html,body").animate({scrollTop:$(".topmenu").scrollTop()},{duration:500});
		e.preventDefault();
	});

	$(document).on("click",".down",function(e){
		$("html,body").animate({scrollTop:$(".footer").position().top},{duration:500});
		e.preventDefault();
	});

	$("body").append(
		'<div class="up_down_div" style="z-index:10000;position: fixed; bottom: 220px; right: 10px; top: 333px;">'+
		'<div style="position:relative">'+
		'<div style="font-size:16px;position:absolute;top:-30px;right:0px">'+
		'<a href="#" class="up" style="color: rgba(0, 0, 0, 0.3) !important; "><div class="fas fa-chevron-circle-up">▲</div></a>'+
		'</div>'+
		'<div style="font-size:16px;position:absolute;top:+30px;right:0px">'+
		'<a href="#" class="down" style="color: rgba(0, 0, 0, 0.3) !important; "><div class="fas fa-chevron-circle-down">▼</div></a>'+
		'</div>'+
		'</div>'+
		'</div>'
	);
});
}

if (!getCookie("icon") && document.getElementById('seticon')) document.getElementById('seticon').style.display = 'none';
if (getCookie("icon")) document.getElementById("icon").src = getCookie("icon");
if (getCookie("homepage")) document.getElementById("iconlink").href = getCookie("homepage");

document.getElementById('body').onclick = function (event) {
	let x = event.clientX;
	let y = event.clientY;
	let e = document.elementsFromPoint(x, y);
	let repid,menid,rt,ri;
	e.forEach(function(el) {
		if (el.className == "rep-comment") {
		repid = el.id;
		}
		else if (el.id == "Modal") {
		menid = el.id;
		}
		if (el.className == "number" || el.className == "menuitem") rt = true;
		if (el.className == "id" || el.className == "reply" || el.className == "ank2" || el.className == "id id2") ri = true;
	});
	let reps = document.querySelectorAll(".rep-comment");
	if (!reps) return;
	reps.forEach(function(v) {
	 var timer = setTimeout(function(){
	  if (v.id == repid) return;
	   v.style.display = 'none';
	 },100);
	});
	let mens = document.querySelectorAll("#Modal");
	if (!mens) return;
	mens.forEach(function(v) {
	 var timer = setTimeout(function(){
	  if (v.id == menid || rt || ri) return;
	   document.getElementById('rModal').style.display = 'none';
	 },100);
	});
}

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

function mute(target) {
  if (target == 'list') {
  let mlisthtml = '<div style="font-weight:bold;">ミュートするID</div><input type="text" id="addmute"><button onclick="AddM()">追加</button>&emsp;<button onclick="mute(\'reset\')">リセット</button><br>※クリックで解除<br>';
      for (let i = 0; i < mutelist.length; i++) {
      mlisthtml += '<a class="menulink" href="javascript:unmute(\''+mutelist[i]+'\')">'+mutelist[i]+'</a>&emsp;';
      }
  document.getElementById('modal_text').innerHTML = mlisthtml;
  document.getElementById('rModal').style.display = 'block';
  return;
  }
  if (target == 'reset') {
      mutelist = [];
      mutejson = JSON.stringify(mutelist, undefined, 1);
      localStorage.setItem('mutelist', mutejson);
      document.getElementById('ntxt').innerHTML = 'リセットしました';
  return;
  }
      let a;
      for (let i = 0; i < mutelist.length; i++) {
      if (target == mutelist[i]) {
      a = true;
      break;
      }
      }
      if (a == true) return;
      let mutecount = mutelist.length + 1;
      mutelist[mutecount-1] = target;
      mutejson = JSON.stringify(mutelist, undefined, 1);
      localStorage.setItem('mutelist', mutejson);
      document.getElementById('ntxt').innerHTML = 'ミュートしました';
}

function AddM() {
      let target = document.getElementById('addmute').value;
      let a;
      for (let i = 0; i < mutelist.length; i++) {
      if (target == mutelist[i]) {
      a = true;
      break;
      }
      }
      if (a == true) return;
      let mutecount = mutelist.length + 1;
      mutelist[mutecount-1] = target;
      mutejson = JSON.stringify(mutelist, undefined, 1);
      localStorage.setItem('mutelist', mutejson);
      document.getElementById('ntxt').innerHTML = '追加しました';
}

function unmute(target) {
      let a;
      for (let i = 0; i < mutelist.length; i++) {
      if (target == mutelist[i]) {
      mutelist[i] = '';
      break;
      }
      }
      mutejson = JSON.stringify(mutelist, undefined, 1);
      localStorage.setItem('mutelist', mutejson);
      document.getElementById('ntxt').innerHTML = 'ミュートを解除しました';
}

function MuteCheck(target) {
			target = target.replace('ID:', '@');
			let N;
			for (let i = 0; i < mutelist.length; i++) {
			if (!mutelist[i]) continue;
			let NG = false;
			if (target.search(mutelist[i]) != -1) NG = true;
			if (NG) {
			 N = mutelist[i];
			 break;
			}
			}
			if (N) return N;
}

function WordCheck(target) {
			let N;
			for (let i = 0; i < nglist.length; i++) {
			if (!nglist[i]) continue;
			let NG = false;
			if (target.search(nglist[i]) != -1) NG = true;
			if (NG) {
			 N = nglist[i];
			 break;
			}
			}
			if (N) return 'Word:'+N;
}

function TitleCheck(target) {
      let N;
      for (let i = 0; i < ntlist.length; i++) {
      if (!ntlist[i]) continue;
      let NG = false;
      if (target.search(ntlist[i]) != -1) NG = true;
      if (NG) {
       N = ntlist[i];
       break;
      }
      }
      if (N) return true;
}

function NGWORD() {
      let target = document.getElementById('addngword').value;
      let a;
      for (let i = 0; i < nglist.length; i++) {
      if (target == nglist[i]) {
      a = true;
      break;
      }
      }
      if (a == true) return;
      let ngcount = nglist.length + 1;
      nglist[ngcount-1] = target;
      ngjson = JSON.stringify(nglist, undefined, 1);
      localStorage.setItem('nglist', ngjson);
      document.getElementById('ntxt').innerHTML = '追加しました';
      notice();
}

function NGTITLE() {
      let target = document.getElementById('addngtitle').value;
      let a;
      for (let i = 0; i < ntlist.length; i++) {
      if (target == ntlist[i]) {
      a = true;
      break;
      }
      }
      if (a == true) return;
      let ntcount = ntlist.length + 1;
      ntlist[ntcount-1] = target;
      ntjson = JSON.stringify(ntlist, undefined, 1);
      localStorage.setItem('ngtitle', ntjson);
      document.getElementById('ntxt').innerHTML = '追加しました';
      notice();
}

function unngword(target) {
  if (target == 'reset') {
      nglist = [];
      ngjson = JSON.stringify(nglist, undefined, 1);
      localStorage.setItem('nglist', ngjson);
      document.getElementById('ntxt').innerHTML = 'リセットしました';
      notice();
  return;
  }
      let a;
      for (let i = 0; i < nglist.length; i++) {
      if (target == nglist[i]) {
      nglist[i] = '';
      break;
      }
      }
      ngjson = JSON.stringify(nglist, undefined, 1);
      localStorage.setItem('nglist', ngjson);
      document.getElementById('ntxt').innerHTML = '解除しました';
      notice();
}

function unngtitle(target) {
  if (target == 'reset') {
      ntlist = [];
      ntjson = JSON.stringify(ntlist, undefined, 1);
      localStorage.setItem('ngtitle', ntjson);
      document.getElementById('ntxt').innerHTML = 'リセットしました';
      notice();
  return;
  }
      let a;
      for (let i = 0; i < ntlist.length; i++) {
      if (target == ntlist[i]) {
      ntlist[i] = '';
      break;
      }
      }
      ntjson = JSON.stringify(ntlist, undefined, 1);
      localStorage.setItem('ntlist', ngjson);
      document.getElementById('ntxt').innerHTML = '解除しました';
      notice();
}

function rclose() {
  document.getElementById('rModal').style.display = 'none';
  document.getElementById('modal_text').innerHTML = '';
}

function Menu() {
document.getElementById('modal_text').innerHTML = '<a class="menulink" href="/test/TL.html">タイムライン絞り込み設定</a><div style="font-weight:bold;">ミュート設定</div><a class="menulink" href="javascript:mute(\'list\')">ID</a>&emsp;<a class="menulink" href="javascript:MenuClick(\'ngword\')">Word</a>&emsp;<a class="menulink" href="javascript:MenuClick(\'ngtitle\')">タイトル</a><div style="font-weight:bold;">その他</div><a class="menulink" href="javascript:MenuClick(\'setting\')">閲覧設定</a>&emsp;<a class="menulink" href="/test/backimg.html">背景画像</a>&emsp;<a class="menulink" href="/test/css.html">カスタムcss</a><br><a class="menulink" href="javascript:setclear()">全ログを削除</a>&emsp;<a class="menulink" href="/test/auth.php">認証</a>';
document.getElementById('rModal').style.display = 'block';
}

function MenuClick(val) {
if (val == "ngword") {
  let nlisthtml = '<div style="font-weight:bold;">ミュートするワード</div>※クリックで解除<br>';
  nlisthtml += '<input type="text" id="addngword"><button onclick="NGWORD()">追加</button>&emsp;<button onclick="unngword(\'reset\')">リセット</button><br>'
      for (let i = 0; i < nglist.length; i++) {
      nlisthtml += '<a class="menulink" href="javascript:unngword(\''+nglist[i]+'\')">'+nglist[i]+'</a>&emsp;';
      }
  document.getElementById('modal_text').innerHTML = nlisthtml;
  document.getElementById('rModal').style.display = 'block';
return;
}
if (val == "ngtitle") {
  let nlisthtml = '<div style="font-weight:bold;">ミュートするタイトル</div>※クリックで解除<br>';
  nlisthtml += '<input type="text" id="addngtitle"><button onclick="NGTITLE()">追加</button>&emsp;<button onclick="unngtitle(\'reset\')">リセット</button><br>'
      for (let i = 0; i < ntlist.length; i++) {
      nlisthtml += '<a class="menulink" href="javascript:unngtitle(\''+ntlist[i]+'\')">'+ntlist[i]+'</a>&emsp;';
      }
  document.getElementById('modal_text').innerHTML = nlisthtml;
  document.getElementById('rModal').style.display = 'block';
return;
}
if (val == "setting") {
let arecheck,gurocheck,autocheck,gcheck,darkcheck,adarkcheck,dcheck;
if (areload == "true") arecheck = 'checked';
if (autoscroll == "true") autocheck = 'checked';
if (ghide == "all") gcheck = 'checked';
if (ghide == "auto") gurocheck = 'checked';
if (localStorage.getItem('darkmode') == "true") darkcheck = 'checked';
if (autodark == "true") adarkcheck = 'checked';
if (origdate == "true") dcheck = 'checked';
document.getElementById('modal_text').innerHTML = '<div class="option_style_2">閲覧設定</div><div class="option_style_3">&emsp;</div><div class="option_style_4"><div class="option_style_5"><input class="option_style_6" '+spcheck+' id="spmode" type="checkbox">スマホ用表示</div><div class="option_style_5"><input class="option_style_6" '+darkcheck+' id="darkmode" type="checkbox">ダークモード</div><div class="option_style_5"><input class="option_style_6" '+adarkcheck+' id="autodark" type="checkbox">デバイスのダークモードと同期する</div><div class="option_style_5"><input class="option_style_6" '+arecheck+' id="arecheck" type="checkbox">5秒間隔で自動更新する</div><div class="option_style_5"><input class="option_style_6" '+autocheck+' id="autoscroll" type="checkbox">新着投稿の位置まで自動スクロール</div><div class="option_style_5"><input class="option_style_6" '+gurocheck+' id="g_hide" type="checkbox">注意のある画像を自動的に非表示</div><div class="option_style_5"><input class="option_style_6" '+gcheck+' id="a_hide" type="checkbox">画像のサムネイル表示をオフにする</div><div class="option_style_5"><input class="option_style_6" '+dcheck+' id="origdate" type="checkbox">投稿日時表記の短縮を行わない</div></div><div class="option_style_11"><button id="saveOptions" class="option_style_12" onclick="setoption()">変更を保存</button><button id="cancelOptions" class="option_style_13" onclick="rclose()">キャンセル</button></div>';
document.getElementById('rModal').style.display = 'block';
return;
}
}

function setoption() {
  let ghide,ngword;
  if (document.getElementById('g_hide').checked == true) ghide = 'auto';
  if (document.getElementById('a_hide').checked == true) ghide = 'all';
  localStorage.setItem('ghide', ghide);
  if (document.getElementById('arecheck').checked == true) areload = true;
  else areload = false;
  localStorage.setItem('areload', areload);
  if (document.getElementById('darkmode').checked == true) darkmode = true;
  else darkmode = false;
  localStorage.setItem('darkmode', darkmode);
  if (document.getElementById('autodark').checked == true) autodark = true;
  else autodark = false;
  localStorage.setItem('autodark', autodark);
  if (document.getElementById('spmode').checked == true) viewer = 'sp';
  else viewer = 'pc';
  localStorage.setItem('viewer', viewer);
  if (document.getElementById('autoscroll').checked == true) autoscroll = true;
  else autoscroll = false;
  localStorage.setItem('autoscroll', autoscroll);
  if (document.getElementById('origdate').checked == true) origdate = true;
  else origdate = false;
  localStorage.setItem('origdate', origdate);
  rclose();
}

function setclear() {
 localStorage.clear();
 sessionStorage.clear();
 document.getElementById('ntxt').innerHTML = '完了';
}

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

function IdClick(value) {
      if (!document.getElementsByClassName(value)) return;
      let idcount = document.getElementsByClassName(value).length;
      let ins = '<div><span style="font-weight:bold;">'+value+'</span> '+idcount+'レス</div><div><a class="mbutton" href="javascript:mute(\''+value+'\')" style="color:black;">ミュートする</a></div>'
      for (let i = 0; i < idcount; i++) {
        ins += '<div class="post">'+document.getElementsByClassName(value)[i].innerHTML+'</div>';
      }
      document.getElementById('modal_text').innerHTML = ins;
      document.getElementById('rModal').style.display = 'block';
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

function TimeDiff(postDay) {
    if (!postDay) return 'NG'; 
    if (origdate == "true") return postDay;   // 投稿日時を変換しない設定の場合はそのまま返す
    let posted = new Date(postDay); 
    let diff = new Date().getTime() - posted.getTime();
    let progress = new Date(diff);
    if (progress.getUTCFullYear() - 1970) {
      return progress.getUTCFullYear() - 1970 + '年前';
    }else if (progress.getUTCMonth()) {
      return progress.getUTCMonth() + 'ヶ月前';
    }else if (progress.getUTCDate() - 1) {
      return progress.getUTCDate() - 1 + '日前';
    }else if (progress.getUTCHours()) {
      return progress.getUTCHours() + '時間前';
    }else if (progress.getUTCMinutes()) {
      return progress.getUTCMinutes() + '分前';
    }else if (progress.getUTCSeconds() >= 10) {
      return progress.getUTCSeconds() + '秒前';
    }else if (progress.getUTCSeconds() >= 0) {
      return 'たった今';    // 10秒未満
    }else {
      return postDay;   // 未対応の形式の場合はマイナスの値になるのでそのまま返す
    }
}

function time() {
var date = new Date();
var a = date.getTime();
return Math.floor(a / 1000);
}

function reply(n) {
 document.getElementById('bbs-textarea').value += '>'+n+'\n';
}