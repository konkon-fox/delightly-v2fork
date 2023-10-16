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
	getLocalHtml('rule', rulefile, true);
}
if (document.getElementById('kokuti')) {
	getLocalHtml('kokuti', kokutifile, false);
}

async function getLocalHtml (targetId, targetFile, isShiftJIS) {
	try{
		const response = await fetch(targetFile, {cache: 'no-store'});
		if(!response.ok) throw new Error();
		if(isShiftJIS){
			const arrayBuffer = await response.arrayBuffer();
			const utf8Text = arrayBufferToUtf8(arrayBuffer);
			document.getElementById(targetId).innerHTML += utf8Text;
		}else{
			const utf8Text = await response.text();
			document.getElementById(targetId).innerHTML += utf8Text;
		}
	}catch(e){
		console.error(e);
	}
}

function arrayBufferToUtf8 (arrayBuffer) {
  const textDecoder = new TextDecoder('sjis');
  const rawUtf8Text = textDecoder.decode(arrayBuffer);
	const utf8Text = rawUtf8Text.replace(/(\r\n|\r|\n)/g, '');
  return utf8Text;
}