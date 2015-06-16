
var IE = {
	version: function() {
		var version = 999; // we assume a sane browser
		if (navigator.appVersion.indexOf("MSIE") != -1) version = parseFloat(navigator.appVersion.split("MSIE")[1]);
		return version;
	}
}

if (IE.version() < 9) {
	// html5 hack, http://html5doctor.com/how-to-get-html5-working-in-ie-and-firefox-2/
	(function(){if(!/*@cc_on!@*/0)return;var e = "abbr,article,aside,audio,bb,canvas,datagrid,datalist,details,dialog,eventsource,figure,figcaption,footer,header,hgroup,mark,menu,meter,nav,output,progress,section,time,video".split(",");for(var i=0;i<e.length;i++){document.createElement(e[i])}})()
}
/* changer l'image de la colonne */
function colonne(a) {
	window.document.body.className=window.document.body.className.replace(/ *noImage/gi, ' ');
	Cookie.del('noImage');
	var win=window.parent;
	if (!win.frames['side']) return true;
	a.target='side';
	if (a.href.search(/image.php/) != -1) return true;
	a.href='image.php?id='+a.href.substr(a.href.search(/\/[A-Z]\//)+1);
	return true;
}
// encoding="UTF-8"
/* Bascule avec ou sans articles en latin */
function fro_lat() {
	// attraper le conteneur
	var o=document.getElementById('article');
	// sortir sans laisser d'erreur
	if (!o) return;
	if (o.className=="fro") o.className="";
	else o.className="fro";
	return false;
}
/** Behavior of the main input for suggestion */
function qKey(input) {
	// no modification of content, do nothing
	if (input.last == input.value) return false;
	o=document.getElementById('fulltext');
	if (o && o.checked) return false;
	// keep track of last value
	input.last=input.value;
	var iframe=document.getElementById('side');
	if (!iframe) return true;
	iframe.src='suggest.php?q='+input.value;
	return true;
}
