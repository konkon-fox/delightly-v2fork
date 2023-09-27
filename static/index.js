document.body.innerHTML = '';
const mode = new URLSearchParams(window.location.search);
let request;
if (mode.get('m') == "subback") request = 'subback.js';
else request = 'timeline.js';
let requestjs = document.createElement("script");
requestjs.src = "/static/"+request;
document.body.appendChild(requestjs);