	let imgurkey = '';
	let spcheck = '';
	let loaded = false;
	if (isSmartPhone() == true) spcheck = 'checked';
	let LINENUM,reloadbutton,getprev,preved;
	if (localStorage.getItem('treeview') === null) localStorage.setItem('treeview', true);
	if (localStorage.getItem('ghide') === null) localStorage.setItem('ghide', 'auto');
	if (localStorage.getItem('areload') === null) localStorage.setItem('areload', false);
	if (localStorage.getItem('darkmode') === null) localStorage.setItem('darkmode', false);
	if (localStorage.getItem('autodark') === null) localStorage.setItem('autodark', true);
	if (localStorage.getItem('autoscroll') === null) localStorage.setItem('autoscroll', false);
	if (localStorage.getItem('origdate') === null) localStorage.setItem('origdate', true);
	if (localStorage.getItem('css') === null) localStorage.setItem('css', '');
	document.head.innerHTML += "<style>"+localStorage.getItem('css')+"</style>";
	localStorage.setItem('autoPost', true);
	localStorage.setItem('backlinkOpen', false);
	localStorage.setItem('ankfixed', true);
	localStorage.setItem('treeView', false);
	if (localStorage.getItem('text2') === null) localStorage.setItem('text2', '');
	sessionStorage.removeItem('text');
	treeview = localStorage.getItem('treeview');
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
	let thread = '';
	let path = location.hash.split('/');
	if (!location.hash) location.href = '/index2.html';
	let bbs = path[0].replace('#', '');
	let key = path[1];
	if (!key) location.href = '/'+bbs+'/';
	let threadfile = '/'+bbs+'/thread/'+key.substring(0, 4)+'/'+key+'.dat';
	const ls = new URLSearchParams(window.location.search);
	let res = [];
	let idlist = [];
	let imglist = [];
	let plist = [];
	let number = 0;
	let threaddata = document.getElementsByClassName('thread')[0];
	if (window.location.search && ls.has('ls') == false && ls.has('nofirst') == false) areload = false;
document.getElementById('header').innerHTML = '<a class="menuitem" href="/'+bbs+'/">TL</a><a class="menuitem" href="/'+bbs+'/?m=subback">スレ一覧</a><a class="menuitem" href="/'+bbs+'/?m=subback&mode=history">履歴</a><a class="menuitem" href="/'+bbs+'/?m=subback&mode=unread">未読</a><a class="menuitem" href="javascript:MenuClick(\'popular\')">人気</a><a class="menuitem" href="javascript:MenuClick(\'picture\')">画像</a><a class="menuitem" href="javascript:Menu(1)">メニュー</a><a href="javascript:MenuClick(\'setting\')" class="menuitem">設定</a><div id="headtitle" class="menuitem" style="text-decoration: none;"></div>';
let style = document.createElement('style');
let stylesheet = '';
stylesheet += '.treeView{display:none !important;}';
if (ghide == "all") stylesheet += '.image{display:none;}';
if (treeview == "true") stylesheet += '.backs-front{display:none;}';
style.innerHTML = stylesheet;
document.getElementById('body').appendChild(style);
document.getElementById('body').innerHTML += '<link href="/static/lightbox.css" rel="stylesheet"><style id="mute"></style><style id="prev-hide"></style>';
if (isSmartPhone() == false) document.getElementById('body').innerHTML += '<style>body{color:rgb(87, 111, 118) !important;}a{color:#485269 !important;}.thread,.title,.pagestats,.newposts,.formbox,.topmenu,.search{padding-left:15px;padding-right:15px}.post{padding:1em 0}.number,.name,.date,.ids,details{font-size:12px;color:rgb(87, 111, 118) !important;margin-right:5px;padding-left:0;margin-left:0}.thread,.newposts,.formbox,.search{width: 75%;}.id{color:rgb(87, 111, 118) !important;}.message{padding:10px 0;font-size:16px;min-height:4em}.side{display: block;border: .5px solid #DCDCDC; position: fixed; right: 0em; top: 0em; bottom: auto; width: 25%; height: 100%; z-index:  1; margin: 0; padding: 0; color: #333; padding-top: 10em; overflow: auto;scrollbar-width: none;}.side::-webkit-scrollbar{display: none;}input{margin-bottom: 1.5rem;}#headtitle, b {color: rgb(87, 111, 118) !important;}</style>';
if (document.getElementsByTagName('form')[0]) document.getElementsByTagName('form')[0].id = 'postForm';
document.getElementById('postForm').innerHTML += '<input type="hidden" name="board" value="'+bbs+'"><input type="hidden" name="thread" value="'+key+'">';
if (document.getElementsByTagName('textarea')[0]) {
	document.getElementsByTagName('textarea')[0].id = 'bbs-textarea';
	document.getElementsByTagName('textarea')[0].setAttribute('onchange', 'MSG()');
}
 if (localStorage.getItem('formfixed') == "true") {
     document.getElementById('postForm').style.backgroundColor = '#fff';
     document.getElementById('postForm').style.border = '1px outset #000';
     document.getElementById('postForm').style.position = 'fixed';
     document.getElementById('postForm').style.zIndex = '1';
     document.getElementById('postForm').style.bottom = '5px';
     document.getElementById('postForm').style.left = '10px';
     document.getElementById('postForm').style.padding = '5px';
     document.getElementById('postForm').innerHTML += '<label><input type="checkbox" id="isFixedForm" onchange="FixedForm();" checked>入力フォーム位置固定</label>';
 }else document.getElementById('postForm').innerHTML += '<label><input type="checkbox" id="isFixedForm" onchange="FixedForm();">入力フォーム位置固定</label>';
let mutes = '';
function load(mode) {
	//スレッド描画
	if (!mode && loaded && time() - localStorage.getItem('rtime') < 3) return;
	res = [];
	idlist = [];
	imglist = [];
	plist = [];
	number = 0;
	let t = '';
        let target = '';
        if (mode == "POST") t = '?'+time();
        else target = mode;
	let request = new XMLHttpRequest();
	request.open('GET', '/' + bbs + '/thread/' + key.substring(0, 4) + '/' +key + '.dat'+t);
	request.setRequestHeader('Pragma', 'no-cache');
	request.setRequestHeader('Cache-Control', 'no-cache');
	request.send();
	request.onreadystatechange = () => {
		if(request.readyState === 4 && request.status === 200) {
			document.getElementsByClassName('formbox')[0].style.display = 'block';
			let data = request.responseText.split('\n');
			if (ls.has('ls')) start = data.length - ls.get('ls');
			else if (ls.has('st')) start = ls.get('st');
			else start = 1;
			if (ls.has('to')) end = ls.get('to');
			else end = data.length;
			document.getElementById('thread').innerHTML = '';
			data.forEach(function(value) {
			if (value.indexOf('<>') == -1) return;
			let dat = value.split('<>');
			let name = dat[0];
			let mail = dat[1];
			let dateid = dat[2];
			let message = dat[3];
			let NG;
			if (target) {
			 if (message.search(target) == -1 && name.search(target) == -1 && mail.search(target) == -1 && dateid.search(target) == -1) NG = true;
			}
			if (dat[4]) {
				document.title = dat[4];
				document.getElementById('title').innerHTML = dat[4];
				document.getElementById('headtitle').innerHTML = dat[4];
			}
			number++;
			if (ls.get('nofirst') == "true" && number == 1) return;
			if (number != 1) {
			 if (number < start || number > end) return;
			}
			let numtext = '<a href="javascript:Menu('+number+')" class="number" id="number-'+number+'">'+number+'</a>';
			if (isSmartPhone() == false) name = '<b>'+name+'</b>';
			else {
			 name = name.replace('<b>', '');
			 name = name.replace('</b>', '');
			}
			if (mail) mail = ' ['+mail+']';
			let nametext = '<span class="name" id="name-'+number+'">'+name+'</span>';
			let dateids;
			if (dateid) dateids = dateid.split(' ');
			if (!dateids) return;
			let date = TimeDiff(dateids[0]+' '+dateids[1]);
			let id = [];
			if (dateids[2]) {
			if (!NG) NG = MuteCheck(dateids[2]);
			id[0] = ID(dateids[2],0,number);
			}
			if (dateids[3]) {
			if (!NG) NG = MuteCheck(dateids[3]);
			id[1] = ID(dateids[3],1,number);
			}
			if (dateids[4]) {
			if (!NG) NG = MuteCheck(dateids[4]);
			id[2] = ID(dateids[4],2,number);
			}
			if (dateids[5]) {
			if (!NG) NG = MuteCheck(dateids[5]);
			id[3] = ID(dateids[5],3,number);
			}
			let ids = '<span id="ids-'+number+'">';
			if (id[0]) ids += ' '+id[0];
			if (id[1]) ids += ' '+id[1];
			if (id[2]) ids += ' '+id[2];
			if (id[3]) ids += ' '+id[3];
			ids += '</span>';
			let datetext;
			if (isSmartPhone() == true) datetext = '<span class="date" id="date-'+number+'">'+ids+date+mail+'</span>';
			else datetext = '<span class="date" id="date-'+number+'">'+date+ids+mail+'</span>';
			let rnum = message.match(/&gt;&gt;([0-9]+)(?![-\d])/);
			if (rnum) rnum = rnum[1];
			if (ghide == "auto") {
			 if (message.indexOf('グロ') != -1 || message.indexOf('死ね') != -1) {
			  if (rnum) imghide(rnum);
			  imghide(number);
			 }
			}
			if (document.getElementById('back-'+rnum)) {
			if (treeview != "true") document.getElementById('back-'+rnum).innerHTML += '<a class="back-link" href="javascript:void(0);">&gt;&gt;'+number+'</a>';
			else document.getElementById('back-'+rnum).innerHTML += '<a></a>';
			let replycount = $('#back-'+rnum).children().length;
			if (replycount < 3) document.getElementById('number-'+rnum).style.color = '#518dc9';
			else if (replycount < 5) document.getElementById('number-'+rnum).style.color = '#a551c9';
			else if (replycount < 10) document.getElementById('number-'+rnum).style.color = 'darkred';
			else document.getElementById('number-'+rnum).style.color = 'darkgoldenrod';
			document.getElementById('rcount-'+rnum).innerHTML = replycount;
			document.getElementById('replys-'+rnum).style.display = "block";
			if (replycount == 3) plist.push(rnum);
			}
			let newpost = document.createElement("div");
			newpost.className = 'post';
			newpost.id = number;
			newpost.dataset.date = 'NG';
			newpost.dataset.userid = dateids[2];
			newpost.dataset.id = number;
			if (treeview == "true" && rnum && document.getElementById(rnum)) {
			let tleft = 10;
			if (document.getElementById(rnum).dataset.tree) tleft = document.getElementById(rnum).dataset.tree * 1.5;
			newpost.dataset.tree = tleft;
			 if (darkmode == "true") newpost.style.cssText = 'border-left:.5px solid #333;margin-left:'+tleft+'px';
			 else newpost.style.cssText = 'border-left:.5px solid #DCDCDC;margin-left:'+tleft+'px';

			}
			let msgtext = '<div class="message" id="msg-'+number+'">'+message+'</div>';
			if (isSmartPhone() == true) newpost.innerHTML = numtext+nametext+msgtext+datetext+'<div id="replys-'+number+'" style="display:none;"><img class="aresicon" src="/static/ares.svg" width="12" height="12"><small id="rcount-'+number+'" class="rcount"></small><span class="backs-front"> :</span><span id="back-'+number+'"></span></div>';
			else newpost.innerHTML = numtext+nametext+datetext+msgtext+'<div id="replys-'+number+'" style="display:none;"><img class="aresicon" src="/static/ares.svg" width="12" height="12"><small id="rcount-'+number+'" class="rcount"></small><span class="backs-front"> :</span><span id="back-'+number+'"></span></div>';
			if (treeview != "true" || !rnum || !document.getElementById(rnum)) document.getElementById('thread').appendChild(newpost);
			else document.getElementById(rnum).after(newpost);
			if (message.indexOf('<span class="AA">') != -1) {
			message = message.replace('<span class="AA">','');
			message = message.replace('</span>','');
			document.getElementById('msg-'+number).className += " AA";
			}
			if (message.indexOf('data-lightbox="image"') != -1) imglist.push(number);
			document.getElementById('msg-'+number).innerHTML = message;
			if (!NG) NG = WordCheck(name+mail+message);
			if (NG) {
			Muten(number);
			document.getElementById(number).style.display = "none";
			}
			if (!loaded && number == localStorage.getItem(bbs+key)) {
			 document.getElementById(number).scrollIntoView();
			 let readline = document.createElement('div');
			 readline.innerHTML = 'ここまで読んだ';
			 if (darkmode == "true") readline.style.cssText = 'background-color: #333333;color: #fff;text-align: center;font-size: 80%;';
			 else readline.style.cssText = 'background-color: #f0f0f0;color: #666666;text-align: center;font-size: 80%;';
			 document.getElementById(number).after(readline);
			}
			LINENUM = number;
			document.getElementById('count').innerHTML = LINENUM+'レス';
			if (areload != false && number > localStorage.getItem(bbs+key)) localStorage.setItem(bbs+key, LINENUM);
			});
			if (!loaded && ls.has('id')) IdClick(ls.get('id'));
        	loaded = true;
        	if (areload != "true") localStorage.setItem('rtime', time());
		}
	};
}

load();

function Post() {
 if (isSmartPhone() == true) fclose();
 setTimeout(Getresponse, 1500);
 let cookname = escape(document.getElementsByName("name")[0].value); 
 document.cookie = "NAME="+cookname+"; Max-Age=7776000; path=/";
 let cookmail = escape(document.getElementsByName("mail")[0].value);
 document.cookie = "MAIL="+cookmail+"; Max-Age=7776000; path=/";
}

function Getresponse() {
 if (getCookie("response")) {
	if (decodeURI(getCookie("response")) != "success") {
		document.getElementById('ntxt').innerHTML = decodeURI(getCookie("response"));
		notice();
	 if (sessionStorage.getItem('text')) document.getElementById('bbs-textarea').value = sessionStorage.getItem('text');
	}else {
	 sessionStorage.removeItem('text');
 	 if (areload != "true") setTimeout(load, 1500, "POST");
	}
 }else {
	 sessionStorage.removeItem('text');
	 if (areload != "true") setTimeout(load, 1500, "POST");
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

if (!getCookie("WrtAgreementKey")) document.getElementById('postForm').innerHTML = '<div><a href="/test/auth.php">投稿時に使用する同意鍵がありません。<br>投稿を行うには投稿前確認画面での同意が必要です。</a></div>'+document.getElementById('postForm').innerHTML;
if (!getCookie("icon") && document.getElementById('seticon')) document.getElementById('seticon').style.display = 'none';
if (getCookie("icon")) document.getElementById("icon").src = getCookie("icon");
if (getCookie("homepage")) document.getElementById("iconlink").href = getCookie("homepage");

if (areload == "true" && document.getElementById('postForm').style.display != "none") {
 setInterval(load, 5000);
}

if (getprev) {
			let prevbutton = document.createElement('center');
			prevbutton.id = "prevbutton";
			prevbutton.innerHTML = '<a name="1"></a><a href="#1" onclick="prev('+getprev+')">前のレスを取得</a>';
			document.getElementById(getprev).before(prevbutton);
}

let modal = document.createElement('div');
modal.innerHTML = '<div id="Modal" style="background-color:#fafafa;margin:auto;padding:20px;border:1px solid #888;width:500px;max-width:95%;"><span style="color:#000;float:right;font-size:28px;font-weight:bold;cursor:pointer;" class="rclose" onclick="rclose()">×</span><div id="modal_text" style="width: 100%;color:#808080;font-family: arial,helvetica,sans-serif;font-size:10pt;margin: 2em 4px 0 0;">'+
'</div></div>';
modal.id = 'rModal';
modal.style.cssText = 'position: fixed; z-index: 20; left: 0px; top: 0px; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.4);';
modal.style.display = 'none';
document.getElementById('body').appendChild(modal);

let notific = document.createElement('div');
notific.innerHTML = '<div id="ntxt" style="font-size:13px;background-color:#404040;width:18%;margin:0 auto;text-align:center;border-radius:3px;padding:4px">'+'</div>';
notific.id = 'notific';
notific.style.cssText = 'display:none;opacity:0;bottom:5%;color:#fff;position:fixed;width:100%;z-index:20';
document.getElementById('body').appendChild(notific);

let boardjs = document.createElement("script");
boardjs.src = "/static/board.js";
document.body.appendChild(boardjs);
let textareajs = document.createElement("script");
textareajs.src = "/static/textarea.js";
document.body.appendChild(textareajs);
let jqueryjs = document.createElement("script");
jqueryjs.src = "/static/jquery-1.11.3.min.js";
document.body.appendChild(jqueryjs);

loadready();

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
let inactive = document.createElement("script");
inactive.src = "/static/inactive.js";
document.getElementById('body').appendChild(inactive);
$(document).ready(function(){
	$('#linkToTop').on('click',function(e){
		e.preventDefault();
		$('html,body').scrollTop(0);
	});

	$(document).on("click",".up",function(e){
		$("html,body").animate({scrollTop:$("h1").scrollTop()},{duration:500});
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

$(document).on("mouseover", ".ank",function(e){
	var timer = setTimeout(function(_this){
		let anknum = $(_this).text().replace('>>', '');
		let repnum = $(_this).attr('class').replace('ank rep-', '');
		if (anknum != "1" && getprev > anknum) prev(getprev,true);
		//ResAnchor(anknum,repnum);
	},0,$(this));
});
$(document).on("mouseover", ".ank2",function(e){
let repnum,anks;
	var t = setTimeout(function(_this){
		let anknum = $(_this).text().replace('>>', '');
		repnum = $(_this).attr('class').replace('ank2 rep-', '');
		anks = anknum.split('-');
		if (getprev > anks[0] || getprev > anks[1]) prev(getprev,true);
		ResAnchor2(anks[0],anks[1],repnum);
	},500,$(this));
});

$(document).on("mouseover", ".id",function(e){
	var timer = setTimeout(function(_this){
		let i = $(_this).text().split('(');
		let c,f;
		if (i[1]) c = i[1].replace(')', '');
		else c = 1;
		let n;
		if ($(_this).parent().attr('id')) n = $(_this).parent().attr('id').replace('ids-', '');
		if (!$(_this).attr('id')) {
		$(_this).attr('id', 'id-'+i[0]+'-'+n);
		f = 'id-'+i[0]+'-'+n;
		}
		else f = false;
		HighLightId(i[0],c,f);
	},500,$(this));
});

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

function Num(n) {
document.getElementById('rModal').style.display = 'none';
if (!document.getElementById('postForm')) return;
if (n) document.getElementById('bbs-textarea').value += '>>'+n+'\n';
if (isSmartPhone() == true) {
 fopen();
 return;
}
 if (ankfixed == "true") {
     document.getElementById("isFixedForm").checked = true; 
     document.getElementById('postForm').style.backgroundColor = '#fff';
     document.getElementById('postForm').style.border = '1px outset #000';
     document.getElementById('postForm').style.position = 'fixed';
     document.getElementById('postForm').style.zIndex = '1';
     document.getElementById('postForm').style.bottom = '5px';
     document.getElementById('postForm').style.left = '10px';
 }
}

function Menu(n) {
document.getElementById('modal_text').innerHTML = '<a class="menulink" href="javascript:Num(\'\')">投稿欄を開く</a>&emsp;<a class="menulink" href="javascript:Num(\''+n+'\')">&gt;&gt;'+n+' へ返信</a><div style="font-weight:bold;">ミュート設定</div><a class="menulink" href="javascript:mute(\'list\')">ID</a>&emsp;<a class="menulink" href="javascript:MenuClick(\'ngword\')">Word</a>&emsp;<a class="menulink" href="javascript:MenuClick(\'ngtitle\')">タイトル</a><div style="font-weight:bold;">レス情報コピー</div><a class="menulink" href="javascript:MenuClick(\'Copyr-'+n+'\')">レス</a>&emsp;<a class="menulink" href="javascript:MenuClick(\'Copyur-'+n+'\')">URL+レス</a>&emsp;<a class="menulink" href="javascript:MenuClick(\'Copytur-'+n+'\')">タイトル+URL+レス</a>&emsp;<a class="menulink" href="javascript:MenuClick(\'Copyu-'+n+'\')">URL</a><div style="font-weight:bold;">スレッド</div><a class="menulink" href="javascript:logclear()">このスレのログを削除</a>&emsp;<a <a class="menulink" href="/test/createthread.php?bbs='+bbs+'&key='+key+'">次スレッド作成</a><br><a class="menulink" href="javascript:MenuClick(\'CopyThreadLink\')">タイトルとスレッドURLをコピー</a><br><a class="menulink" href="javascript:MenuClick(\'CopyLink\')">スレッドURLをコピー</a><br><div style="font-weight:bold;">その他</div><a class="menulink" href="javascript:MenuClick(\'setting\')">閲覧設定</a>&emsp;<a class="menulink" href="/test/backimg.html">背景画像</a>&emsp;<a class="menulink" href="/test/css.html">カスタムcss</a><br><a class="menulink" href="javascript:setclear()">全ログを削除</a>&emsp;<a class="menulink" href="/test/auth.php">認証</a>';
document.getElementById('rModal').style.display = 'block';
}

function ResAnchor(n,r) {
if (!document.getElementById('msg-'+r)) return;
if (!document.getElementById(n)) prev(getprev);
if (!document.getElementById('reply-'+r+'-'+n)) document.getElementById('msg-'+r).innerHTML = '<dl class="rep-comment" id="reply-'+r+'-'+n+'">'+document.getElementById(n).innerHTML+'</dl>'+document.getElementById('msg-'+r).innerHTML;
else document.getElementById('reply-'+r+'-'+n).style.display = 'block';
}

function ResAnchor2(a,b) {
if (!document.getElementById(a) || !document.getElementById(b)) return;
let d = b;
++d;
document.getElementById('modal_text').innerHTML = '';
 for (let c = a; c < d; c++) {
  if (document.getElementById(c) === null) break;
  if (document.getElementById(c)) document.getElementById('modal_text').innerHTML += document.getElementById(c).innerHTML;
 }
document.getElementById('rModal').style.display = 'block';
}

function HighLightId(ID,count,e) {
if (!document.getElementById(e) || !e) return;
			let idr = idlist[ID];
			let idcount;
			if (idr) idcount = idr.length;
			if (idcount > 1) document.getElementById(e).innerHTML = ID+'('+count+'/'+idcount+')';
}

function IdClick(ID,n) {
			let idr = idlist[ID];
			let idcount = idr.length;
			let ins = '<div><span style="font-weight:bold;">'+ID+'</span> '+idcount+'レス</div><div><a class="mbutton" href="javascript:mute(\''+ID+'\')" style="color:black;">ミュートする</a></div>'
			idr.forEach(function(value) {
			ins += '<div class="post">'+document.getElementById(value).innerHTML+'</div>';
			});
			document.getElementById('modal_text').innerHTML = ins;
			document.getElementById('rModal').style.display = 'block';
}

function Iclose(a) {document.getElementById(a).style.display = 'none';}

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
function ID(d, n, num) {
			did = d.replace('ID:', '@');
			let idr = idlist[did];
			let idcount;
			if (idr) idcount = idr.length + 1;
			else {
			idcount = 1;
			idr = [];
			}
			if (idcount == 1) id = '<a class="id" href="javascript:IdClick(\''+did+'\','+num+')">'+did+'</a>';
			else if (idcount < 3) id = '<a class="id" href="javascript:IdClick(\''+did+'\','+num+')" style="color: #518dc9 !important;">'+did+'('+idcount+')</a>';
			else if (idcount < 5) id = '<a class="id" href="javascript:IdClick(\''+did+'\','+num+')" style="color: #a551c9 !important;">'+did+'('+idcount+')</a>';
			else if (idcount < 10) id = '<a class="id" href="javascript:IdClick(\''+did+'\','+num+')" style="color: darkred !important;">'+did+'('+idcount+')</a>';
			else id = '<a class="id" href="javascript:IdClick(\''+did+'\','+num+')" style="color: darkgoldenrod !important;">'+did+'('+idcount+')</a>';
			idr[idcount-1] = num;
			idlist[did] = idr;
			return id;
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
			notice();
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
			notice();
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
			notice();
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
			notice();
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

function rclose() {
	document.getElementById('rModal').style.display = 'none';
	document.getElementById('modal_text').innerHTML = '';
}

function MenuClick(val) {
if (val.indexOf('Copyr-') != -1) {
	   document.getElementById('rModal').style.display = 'none';
	let n = val.replace('Copyr-', '');
  if (navigator.clipboard) {
	let dg = document.getElementById('date-'+n).innerText;
	let id = document.getElementById('ids-'+n).innerText;
	let d1 = dg.replace(id, '');
	let d2;
	if (id.length > 5) d2 = d1.replace(/\([0-9]+.*\)/, '');
	else d2 = d1;
	let date = d2.replace(/\n/, '');
	let d = date.trim()+' '+id.trim();
	let text = n+' '+document.getElementById('name-'+n).innerText+' '+d+'\n'+document.getElementById('msg-'+n).innerText;
   navigator.clipboard.writeText(text).then(function () {
  			document.getElementById('ntxt').innerHTML = 'コピーしました';
			notice();
   })
  }else {
			document.getElementById('ntxt').innerHTML = 'コピーできません';
			notice();
  }
return;
}

if (val.indexOf('Copyur-') != -1) {
	   document.getElementById('rModal').style.display = 'none';
	let n = val.replace('Copyur-', '');
  if (navigator.clipboard) {
	let dg = document.getElementById('date-'+n).innerText;
	let id = document.getElementById('ids-'+n).innerText;
	let d1 = dg.replace(id, '');
	let d2;
	if (id.length > 5) d2 = d1.replace(/\([0-9]+.*\)/, '');
	else d2 = d1;
	let date = d2.replace(/\n/, '');
	let d = date.trim()+' '+id.trim();
   let url = 'https://'+location.host+'/?st='+n+'#'+bbs+'/'+key+'/';
	let text = url+'\n'+n+' '+document.getElementById('name-'+n).innerText+' '+d+'\n'+document.getElementById('msg-'+n).innerText;
   navigator.clipboard.writeText(text).then(function () {
  			document.getElementById('ntxt').innerHTML = 'コピーしました';
			notice();
   })
  }else {
			document.getElementById('ntxt').innerHTML = 'コピーできません';
			notice();
  }
return;
}

if (val.indexOf('Copy4-') != -1) {
	   document.getElementById('rModal').style.display = 'none';
	let n = val.replace('Copy4-', '');
  if (navigator.clipboard) {
	let dg = document.getElementById('date-'+n).innerText;
	let id = document.getElementById('ids-'+n).innerText;
	let d1 = dg.replace(id, '');
	let d2;
	if (id.length > 5) d2 = d1.replace(/\([0-9]+.*\)/, '');
	else d2 = d1;
	let date = d2.replace(/\n/, '');
	let d = date.trim()+' '+id.trim();
   let url = 'https://'+location.host+'/?st='+n+'#'+bbs+'/'+key+'/';
	let text = url+' '+d;
   navigator.clipboard.writeText(text).then(function () {
  			document.getElementById('ntxt').innerHTML = 'コピーしました';
			notice();
   })
  }else {
			document.getElementById('ntxt').innerHTML = 'コピーできません';
			notice();
  }
return;
}

if (val.indexOf('Copytur-') != -1) {
	   document.getElementById('rModal').style.display = 'none';
	let n = val.replace('Copytur-', '');
  if (navigator.clipboard) {
	let dg = document.getElementById('date-'+n).innerText;
	let id = document.getElementById('ids-'+n).innerText;
	let d1 = dg.replace(id, '');
	let d2;
	if (id.length > 5) d2 = d1.replace(/\([0-9]+.*\)/, '');
	else d2 = d1;
	let date = d2.replace(/\n/, '');
	let d = date.trim()+' '+id.trim();
   let url = 'https://'+location.host+'/?st='+n+'#'+bbs+'/'+key+'/';
	let text = document.title+'\n'+url+'\n'+n+' '+document.getElementById('name-'+n).innerText+' '+d+'\n'+document.getElementById('msg-'+n).innerText;
   navigator.clipboard.writeText(text).then(function () {
  			document.getElementById('ntxt').innerHTML = 'コピーしました';
			notice();
   })
  }else {
			document.getElementById('ntxt').innerHTML = 'コピーできません';
			notice();
  }
return;
}

if (val.indexOf('Copyu-') != -1) {
	   document.getElementById('rModal').style.display = 'none';
  let n = val.replace('Copyu-', '');
  if (navigator.clipboard) {
   let url = 'https://'+location.host+'/?st='+n+'#'+bbs+'/'+key+'/';
   navigator.clipboard.writeText(url).then(function () {
  			document.getElementById('ntxt').innerHTML = 'コピーしました';
			notice();
   })
  }else {
			document.getElementById('ntxt').innerHTML = 'コピーできません';
			notice();
  }
return;
}
if (val == "CopyThreadLink") {
	   document.getElementById('rModal').style.display = 'none';
  if (navigator.clipboard) {
   let url = 'https://'+location.host+'/#'+bbs+'/'+key+'/';
	let text = document.title+'\n'+url;
   navigator.clipboard.writeText(text).then(function () {
  			document.getElementById('ntxt').innerHTML = 'コピーしました';
			notice();
   })
  }else {
			document.getElementById('ntxt').innerHTML = 'コピーできません';
			notice();
  }
return;
}
if (val == "CopyLink") {
	   document.getElementById('rModal').style.display = 'none';
  if (navigator.clipboard) {
   let url = 'https://'+location.host+'/#'+bbs+'/'+key+'/';
   navigator.clipboard.writeText(url).then(function () {
  			document.getElementById('ntxt').innerHTML = 'コピーしました';
			notice();
   })
  }else {
			document.getElementById('ntxt').innerHTML = 'コピーできません';
			notice();
  }
return;
}
if (val == "picture") {
			let imgcount = imglist.length;
			let html = '';
			html += '<div><span style="font-weight:bold;">画像レス</span> '+imgcount+'件</div>'
			imglist.forEach(function(value) {
			html += '<div class="post">'+document.getElementById(value).innerHTML+'</div>';
			});
document.getElementById('modal_text').innerHTML = html;
document.getElementById('rModal').style.display = 'block';
return;
}
if (val == "popular") {
			let pcount = plist.length;
			let html = '';
			html += '<div><span style="font-weight:bold;">人気レス</span> '+pcount+'件</div>'
			plist.forEach(function(value) {
			html += '<div class="post">'+document.getElementById(value).innerHTML+'</div>';
			});
document.getElementById('modal_text').innerHTML = html;
document.getElementById('rModal').style.display = 'block';
return;
}
if (val == "setting") {
let arecheck,gurocheck,autocheck,gcheck,darkcheck,adarkcheck,treecheck,dcheck;
if (areload == "true") arecheck = 'checked';
if (autoscroll == "true") autocheck = 'checked';
if (ghide == "all") gcheck = 'checked';
if (ghide == "auto") gurocheck = 'checked';
if (localStorage.getItem('darkmode') == "true") darkcheck = 'checked';
if (autodark == "true") adarkcheck = 'checked';
if (treeview == "true") treecheck = 'checked';
if (origdate == "true") dcheck = 'checked';
document.getElementById('modal_text').innerHTML = '<div class="option_style_2">閲覧設定</div><div class="option_style_3">&emsp;</div><div class="option_style_4"><div class="option_style_5"><input class="option_style_6" '+spcheck+' id="spmode" type="checkbox">スマホ用表示</div><div class="option_style_5"><input class="option_style_6" '+darkcheck+' id="darkmode" type="checkbox">ダークモード</div><div class="option_style_5"><input class="option_style_6" '+adarkcheck+' id="autodark" type="checkbox">デバイスのダークモードと同期する</div><div class="option_style_5"><input class="option_style_6" '+arecheck+' id="arecheck" type="checkbox">5秒間隔で自動更新する</div><div class="option_style_5"><input class="option_style_6" '+autocheck+' id="autoscroll" type="checkbox">新着投稿の位置まで自動スクロール</div><div class="option_style_5"><input class="option_style_6" '+gurocheck+' id="g_hide" type="checkbox">注意のある画像を自動的に非表示</div><div class="option_style_5"><input class="option_style_6" '+gcheck+' id="a_hide" type="checkbox">画像のサムネイル表示をオフにする</div><div class="option_style_5"><input class="option_style_6" '+treecheck+' id="tview" type="checkbox">ツリー表示</div><div class="option_style_5"><input class="option_style_6" '+dcheck+' id="origdate" type="checkbox">投稿日時表記の短縮を行わない</div></div><div class="option_style_11"><button id="saveOptions" class="option_style_12" onclick="setoption()">変更を保存</button><button id="cancelOptions" class="option_style_13" onclick="rclose()">キャンセル</button></div>';
document.getElementById('rModal').style.display = 'block';
return;
}
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
  if (document.getElementById('tview').checked == true) treeview = true;
  else treeview = false;
  localStorage.setItem('treeview', treeview);
  if (document.getElementById('origdate').checked == true) origdate = true;
  else origdate = false;
  localStorage.setItem('origdate', origdate);
  rclose();
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

function Muten(number) {
document.getElementById('mute').innerHTML += '.tv-'+number+'{display:none !important;}';
}

function imghide(n) {
document.getElementById('mute').innerHTML += '.img-'+n+'{display:none !important;}';
}

function notice() {
	if (document.getElementById('notific').style.display != "none") return;
			document.getElementById('notific').style.display = "block";
			document.getElementById('notific').style.opacity = 1;
			$("#notific").fadeTo(3000, 0, closenotice);
}

function closenotice() {
	document.getElementById('notific').style.display = "none";
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
 notice();
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
	notice();
  },

  error: function () {
	document.getElementById('ntxt').innerHTML = 'アップロードできません';
	notice();
  }
});
}
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

function FixedForm() {
    if (document.getElementById("isFixedForm").checked) {
     localStorage.setItem('formfixed', true);
     document.getElementById('postForm').style.backgroundColor = '#fff';
     document.getElementById('postForm').style.border = '1px outset #000';
     document.getElementById('postForm').style.position = 'fixed';
     document.getElementById('postForm').style.zIndex = '1';
     document.getElementById('postForm').style.padding = '5px';
	if (isSmartPhone() == true) {
     document.getElementById('postForm').style.bottom = '0';
     document.getElementById('postForm').style.left = '0';
	}else {
     document.getElementById('postForm').style.bottom = '5px';
     document.getElementById('postForm').style.left = '10px';
	}
    } else {
     localStorage.setItem('formfixed', false);
     document.getElementById('postForm').style.backgroundColor = '';
     document.getElementById('postForm').style.border = '';
     document.getElementById('postForm').style.bottom = '';
     document.getElementById('postForm').style.position = '';
     document.getElementById('postForm').style.zIndex = '';
     document.getElementById('postForm').style.right = '';
     document.getElementById('postForm').style.padding = '';
     document.getElementById('bbs-textarea').style.height = '';
  }
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
function fopen() {
 document.getElementById('postForm').style.display = 'block';
}
function fclose() {
 document.getElementById('postForm').style.display = 'none';
}
function setclear() {
 localStorage.clear();
 sessionStorage.clear();
 document.getElementById('ntxt').innerHTML = '完了';
 notice();
}
function logclear() {
 localStorage.removeItem(bbs+key);
 document.getElementById('ntxt').innerHTML = '完了';
 notice();
}
function search() {
 load(document.getElementById('searchInput').value);
}

//prev was removed.
function prev() {return;}		