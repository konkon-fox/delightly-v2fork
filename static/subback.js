  let ls = new URLSearchParams(window.location.search);
  let spcheck = '';
  let kakolog,archive;
  if (isSmartPhone() == true) spcheck = 'checked';
  let LINENUM,reloadbutton,getprev,preved;
  if (localStorage.getItem('ghide') === null) localStorage.setItem('ghide', 'auto');
  if (localStorage.getItem('areload') === null) localStorage.setItem('areload', false);
  if (localStorage.getItem('darkmode') === null) localStorage.setItem('darkmode', false);
  if (localStorage.getItem('autodark') === null) localStorage.setItem('autodark', true);
  if (localStorage.getItem('autoscroll') === null) localStorage.setItem('autoscroll', false);
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
function time() {
var date = new Date();
var a = date.getTime();
return Math.floor(a / 1000);
}
if (!ls.has('archive')) kakolog = String(time()).substring(0, 4);
else kakolog = ls.get('archive');
let path = location.pathname.split('/');
let bbs = path[1];
let requestURL = '';
if (ls.get('mode') != 'archive') requestURL = '/'+bbs+'/subject.json?'+time();
else requestURL = '/'+bbs+'/thread/'+kakolog+'/subject.json';
let n = 0;
document.head.innerHTML += '<link href="/static/st.css" rel="stylesheet" type="text/css"><link href="/static/milligram.css" rel="stylesheet" type="text/css"><link href="/static/s.css" rel="stylesheet" type="text/css"><link href="/static/index.css" rel="stylesheet" type="text/css"><link href="/static/read.css" rel="stylesheet" type="text/css">';
document.body.innerHTML = '<section class="section"><div id="body"><div id="followheader" class="hidden maxwidth100 height2p5 stickymenu container" style="display: block;"><div class="row noflex maxwidth100 white padding0p5 maxheight2p5 borderbottomlightgrey"><div class="topmenu" id="header"><a class="menuitem" href="/'+bbs+'/">TL</a><a class="menuitem" href="/'+bbs+'/?m=subback">スレ一覧</a><a class="menuitem" href="/'+bbs+'/?m=subback&mode=history">履歴</a><a class="menuitem" href="/'+bbs+'/?m=subback&mode=unread">未読</a><a class="menuitem" href="/test/createthread.php?bbs='+bbs+'">スレ作成</a><a class="menuitem" href="javascript:Menu()">メニュー</a><a href="javascript:MenuClick(\'setting\')" class="menuitem">設定</a><div id="headtitle" class="menuitem" style="text-decoration: none;"></div></div></div></div><div id="subbacktitle"></div><div class="tlnotice" id="ntxt" style="margin-top:3rem"></div><div class="search"><input type="text" id="searchInput" placeholder="スレッドタイトル検索" onchange="search()"></div><div class="newposts"><center><a href="javascript:load()">リロード<img alt="再読み込み" loading="lazy" decoding="async" data-nimg="1" style="color: transparent;" src="/static/reload.svg" width="16" height="16"></a></center><hr></div><div id="main"></div><div id="hidemetoo" class="side p85 slightpad nobullets"><div style="min-height:50px" id="rule">■ ローカルルール<br></div><div style="min-height:50px" id="kokuti">■ 告知欄<br></div><p style="margin:5rem 0;color:#333">We\'re sure you\'ll have a good time with delight and/or 3ch. :)</p></div></div></section>';
if (localStorage.getItem('css') === null) localStorage.setItem('css', '');
document.body.innerHTML += "<style>"+localStorage.getItem('css')+"</style>";
let threadjs = document.createElement("script");
threadjs.src = "/static/thread.js";
document.body.appendChild(threadjs);
let boardjs = document.createElement("script");
boardjs.src = "/static/board.js";
document.body.appendChild(boardjs);
if (isSmartPhone() == false) document.getElementById('body').innerHTML += '<style>a{color:#485269 !important;}#headtitle,b{color:rgb(87, 111, 118) !important}</style>';
else document.getElementById('body').innerHTML += '<style>.title{margin-top:.5em;}</style>';
document.getElementById('body').innerHTML += '<style>div.postbutton{display: none !important;}#main{border-top:.5px solid #DCDCDC}</style>';
function load(target) {
 document.getElementById('main').innerHTML = '';
 request = new XMLHttpRequest();
 request.open('GET', requestURL);
 request.responseType = 'json';
 request.setRequestHeader('Pragma', 'no-cache');
 request.setRequestHeader('Cache-Control', 'no-cache');
 request.send();
 request.onload = function() {
 if (request.response) {
  let listJSON = JSON.parse(JSON.stringify(request.response));
  listJSON.forEach(function(post) {
  let NG = false;
  n++;
  let t,create,created,nres,css,ncount;
  let key = post['thread'];
  let number = post['number'];
  let title = post['title'];
  let date = post['date'];
  if (title && !ls.has('mode')) {
   if (title.indexOf("[stop] ") != -1) return;
  }
  if (title && target) {
   if (title.search(target) == -1) return;
  }
  if (date && date != "archive") {
  t = time() - date;
  if (t < 60) t += "秒";
  else if (t < 3600) {
  t = Math.floor(t / 60);
  t += "分";
  }else if (t < 86400) {
  t = Math.floor(t / 3600);
  t += "時間";
  }else {
  t = Math.floor(t / 86400);
  t += "日";
  }
  }
  if (key) {
    create = new Date(key * 1000);
    created = create.toLocaleDateString()+' '+create.toLocaleTimeString();
  }
  if (ls.get('mode') != 'archive' && localStorage.getItem(bbs+key) && number > localStorage.getItem(bbs+key)) {
    ncount = number - localStorage.getItem(bbs+key);
    if (isSmartPhone() == false) nres = '/未読:'+ncount;
    else nres = '<span class="new">'+ncount+'</span>';
  }else {
  if (ls.get('mode') == "unread") return;
  else if (ls.get('mode') == "history" && !localStorage.getItem(bbs+key)) return; 
  nres = '';
  }
  if (localStorage.getItem(bbs+key)) css = 'font-weight: bold;';
  if (!NG) NG = TitleCheck(title);
  if (NG) return;
  let newpost = document.createElement("div");
  newpost.className = 'threads';
  if (isSmartPhone() == false) newpost.innerHTML = '<a class="t" href="/#'+bbs+'/'+key+'/"><span class="title" style="'+css+'">'+title+'</span><small>(レス:'+number+nres+')</small><span class="date"><span class="created">'+created+'</span> <span class="speed">'+t+'</span></span></a>';
  else newpost.innerHTML = '<a class="t" href="/#'+bbs+'/'+key+'/"><span class="right">'+number+'</span>'+nres+'<div class="title" style="'+css+'">'+title+'</div><span class="date"><span class="right"><span class="speed">'+t+'</span></span><span class="created">'+created+'</span></span></a>';
  document.getElementById('main').appendChild(newpost);
  });
 }
  if (ls.get('mode') != 'archive') document.getElementById('main').innerHTML += '<div class="m threads"><b><a href="/'+bbs+'/?m=subback&mode=archive">過去ログ倉庫はこちら</a></b></div>';
  else {
   archive = kakolog - 1;
   document.getElementById('main').innerHTML += '<div class="m threads"><b><a href="/'+bbs+'/?m=subback&mode=archive&archive='+archive+'">次へ('+archive+')</a></b></div>';
  }

 }
}

load();

let modal = document.createElement('div');
modal.innerHTML = '<div id="Modal" style="background-color:#fafafa;margin:auto;padding:20px;border:1px solid #888;width:500px;max-width:95%;"><span style="color:#000;float:right;font-size:28px;font-weight:bold;cursor:pointer;" class="rclose" onclick="rclose()">×</span><div id="modal_text" style="width: 100%;color:#808080;font-family: arial,helvetica,sans-serif;font-size:10pt;margin: 2em 4px 0 0;">'+
'</div></div>';
modal.id = 'rModal';
modal.style.cssText = 'position: fixed; z-index: 20; left: 0px; top: 0px; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.4);';
modal.style.display = 'none';
document.getElementById('body').appendChild(modal);

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
		if (el.className == "id" || el.className == "ank2" || el.className == "id id2") ri = true;
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
      let N;
      for (let i = 0; i < mutelist.length; i++) {
      if (!mutelist[i]) continue;
      let NG = target.match(mutelist[i]);
      if (NG) {
       N = mutelist[i];
       break;
      }
      }
      if (N) return true;
}

function WordCheck(target) {
      let N;
      for (let i = 0; i < nglist.length; i++) {
      if (!nglist[i]) continue;
      let NG = target.match(nglist[i]);
      if (NG) {
       N = nglist[i];
       break;
      }
      }
      if (N) return true;
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
document.getElementById('modal_text').innerHTML = '<div style="font-weight:bold;">ミュート設定</div><a class="menulink" href="javascript:mute(\'list\')">ID</a>&emsp;<a class="menulink" href="javascript:MenuClick(\'ngword\')">Word</a>&emsp;<a class="menulink" href="javascript:MenuClick(\'ngtitle\')">タイトル</a><div style="font-weight:bold;">その他</div><a class="menulink" href="javascript:MenuClick(\'setting\')">閲覧設定</a>&emsp;<a class="menulink" href="/test/backimg.html">背景画像</a>&emsp;<a class="menulink" href="/test/css.html">カスタムcss</a><br><a class="menulink" href="javascript:setclear()">全ログを削除</a>&emsp;<a class="menulink" href="/test/auth.php">認証</a>';
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
let arecheck,gurocheck,autocheck,gcheck,darkcheck,adarkcheck;
if (areload == "true") arecheck = 'checked';
if (autoscroll == "true") autocheck = 'checked';
if (ghide == "all") gcheck = 'checked';
if (ghide == "auto") gurocheck = 'checked';
if (localStorage.getItem('darkmode') == "true") darkcheck = 'checked';
if (autodark == "true") adarkcheck = 'checked';
document.getElementById('modal_text').innerHTML = '<div class="option_style_2">閲覧設定</div><div class="option_style_3">&emsp;</div><div class="option_style_4"><div class="option_style_5"><input class="option_style_6" '+spcheck+' id="spmode" type="checkbox">スマホ用表示</div><div class="option_style_5"><input class="option_style_6" '+darkcheck+' id="darkmode" type="checkbox">ダークモード</div><div class="option_style_5"><input class="option_style_6" '+adarkcheck+' id="autodark" type="checkbox">デバイスのダークモードと同期する</div><div class="option_style_5"><input class="option_style_6" '+arecheck+' id="arecheck" type="checkbox">5秒間隔で自動更新する</div><div class="option_style_5"><input class="option_style_6" '+autocheck+' id="autoscroll" type="checkbox">新着投稿の位置まで自動スクロール</div><div class="option_style_5"><input class="option_style_6" '+gurocheck+' id="g_hide" type="checkbox">注意のある画像を自動的に非表示</div><div class="option_style_5"><input class="option_style_6" '+gcheck+' id="a_hide" type="checkbox">画像のサムネイル表示をオフにする</div></div><div class="option_style_11"><button id="saveOptions" class="option_style_12" onclick="setoption()">変更を保存</button><button id="cancelOptions" class="option_style_13" onclick="rclose()">キャンセル</button></div>';
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
  rclose();
}

function setclear() {
 localStorage.clear();
 sessionStorage.clear();
 document.getElementById('ntxt').innerHTML = '完了';
}

function search() {
 load(document.getElementById('searchInput').value);
}