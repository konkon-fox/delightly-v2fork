if (isSmartPhone() == true) {
let postbutton = '';
postbutton += '<div class="postbutton" style="display:none">'+
	      '<a href="javascript:void(0);" onclick="fopen()"> '+
	      '<svg viewBox="0 0 24 24" aria-hidden="true" class="postopen"><g><path d="M23 3c-6.62-.1-10.38 2.421-13.05 6.03C7.29 12.61 6 17.331 6 22h2c0-1.007.07-2.012.19-3H12c4.1 0 7.48-3.082 7.94-7.054C22.79 10.147 23.17 6.359 23 3zm-7 8h-1.5v2H16c.63-.016 1.2-.08 1.72-.188C16.95 15.24 14.68 17 12 17H8.55c.57-2.512 1.57-4.851 3-6.78 2.16-2.912 5.29-4.911 9.45-5.187C20.95 8.079 19.9 11 16 11zM4 9V6H1V4h3V1h2v3h3v2H6v3H4z"></path></g></svg>'+
	      ' </a>'+
	      '</div>';
document.getElementById('body').innerHTML += '<link href="/static/sp.css" rel="stylesheet">'+postbutton;
}
if (localStorage.getItem('darkmode') == "true" || (localStorage.getItem('autodark') == "true" && window.matchMedia('(prefers-color-scheme: dark)').matches === true)) {
document.getElementById('body').innerHTML += '<link href="/static/dark.css" rel="stylesheet">';
if (isSmartPhone() == false) document.getElementById('body').innerHTML += '<style>#postForm{background: #262626 !important;}</style>';
}
if (localStorage.getItem('backimg')) {
	if (localStorage.getItem('darkmode') == "true") document.getElementById('body').innerHTML += '<style>#body{position: relative;background-color: rgba(38,38,38,0.75) !important}.section {position: relative;} .section:before { content: ""; display: block;  position: fixed;  top: 0;  left: 0;  width: 100%;  height: 100vh;  background: url('+localStorage.getItem('backimg')+') center top no-repeat;  background-size: 100% auto;}.asetting_4,.topmenu,.title,.thread,.cLength,.newposts,.bottommenu{background-color: transparent !important;}</style>';
	else document.getElementById('body').innerHTML += '<style>#body{position: relative;}.section {position: relative;} .section:before { content: ""; display: block;  position: fixed;  top: 0;  left: 0;  width: 100%;  height: 100vh;  background: url('+localStorage.getItem('backimg')+') center top no-repeat;  background-size: 100% auto;}.asetting_4,.topmenu,.title,.thread,.cLength,.newposts,.bottommenu{background-color: transparent !important;}</style>';
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