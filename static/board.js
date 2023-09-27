let threadmode = false;
path = location.pathname.split('/');
if (!path[1]) {
	path = location.hash.split('/');
	bbs = path[0].replace('#', '');
	threadmode = true;
}else bbs = path[1];
setfile = '/'+bbs+'/setting.json';
rulefile = '/'+bbs+'/head.txt';
kokutifile = '/'+bbs+'/kokuti.txt';
	const sroad = new XMLHttpRequest();
	sroad.open('get', setfile);
	sroad.send();
	sroad.onreadystatechange = function() {
		if(sroad.readyState === 4 && sroad.status === 200) {
			const setting = JSON.parse(this.responseText);
			if (setting['BBS_TITLE'] && !threadmode) {
				if (document.getElementById('headtitle')) document.getElementById('headtitle').innerHTML = setting['BBS_TITLE'];
				if (document.getElementById('subbacktitle')) document.getElementById('subbacktitle').innerHTML = setting['BBS_TITLE'];
				document.title = setting['BBS_TITLE']+' | '+location.hostname;
			}
			if (setting['background']) {
				document.body.style.background = setting['background'];
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