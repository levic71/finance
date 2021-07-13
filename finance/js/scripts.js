function ajaxCall(method, url, msg, refresh=false) {
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			alert(msg)
			if (refresh) window.location.reload();
		}
	};
	xmlhttp.open(method, url, true);
	xmlhttp.send();
}

// DOM functions
el = function(id) { return document.getElementById(id); }
cc = function(id, c) {
	obj = el(id);
    if (obj == null) { myconsole('cc:' + id + " introuvable"); return; }
	try {
		obj.innerHTML = c;
		var allJs = obj.getElementsByTagName("script");
		for(var i=0; i < allJs.length; i++) { if (allJs[i].src && allJs[i].src != "") includeJs(allJs[i].src); else if (allJs[i].innerHTML && allJs[i].innerHTML != "") window.eval(allJs[i].innerHTML); }
	} catch(e) { if (isIE8()) alert('IE8 mal support?, utiliser de pr?f?rence FF, IE9 ou Chrome'); else if (typeof allJs != "undefined" && typeof allJs[i] != "undefined") myconsole('cc: JKX caught error: (' + e.name + '): ' + e.message + '\n' + allJs[i].innerHTML ); else myconsole("cc: js error"+ e); }
}
fs = function(id) { try { el(id).focus(); } catch(e) {} }
isHidden = function(id) { try { return (el(id).style.display == 'none' ? true : false); } catch(e) {} }
show = function(id) { try { el(id).style.display = el(id).tagName.toUpperCase() == "TABLE" ? 'inline-table' : 'initial'; } catch(e) {} }
hide = function(id) { try { el(id).style.display = 'none'; } catch(e) {} }
toogle = function(id) { try { if (isHidden(id)) show(id); else hide(id); } catch(e) {} }
valof = function(id) {
	ret = '';
	if (el(id)) {
		if (el(id).type == 'checkbox')
			ret = el(id).checked ? el(id).value : 0;
		else if (el(id).type == 'radio')
			{ var elts = document.getElementsByName(id); for (i=0;i<elts.length;i++)  if (elts[i].checked) ret = elts[i].value; }
		else if (el(id).type == 'textarea')
			ret = el(id).value.replace(/\n/g,'<br \/>');
		else
			ret = el(id).value;
	}
	return ret;
}
attrs = function(ids) {	ret = ''; for(var n=0;n<ids.length;n++) { ret += '&'+ids[n]+'='+encodeURIComponent(valof(ids[n])); } return ret; }
setCN = function(id, cn) { try { el(id).className = cn; } catch(e) { myconsole('setCN:' + id + ':' + e); } }
addCN = function(id, cn) { try { el(id).className += (el(id).className == "" ? "" : " ")+cn; } catch(e) { myconsole('addCN:' + id + ':' + e); } }
rmCN  = function(id, cn) { try { var tmp = el(id).className.split(' '); ncn = ""; for(var n=0;n<tmp.length;n++) { if (tmp[n] != cn) ncn += " "+tmp[n]; }; el(id).className = ncn; } catch(e) { myconsole('rmCN:' + id + ':' + e); } }


function addStock(symbol, name, type, region, marketopen, marketclose, timezone, currency) {
	url = "stock_add.php?symbol="+symbol+"&name="+name+"&type="+type+"&region="+region+"&marketopen="+marketopen+"&marketclose="+marketclose+"&timezone="+timezone+"&currency="+currency;
	ajaxCall("GET", url, 'Stock '+symbol+' added !');
}

function deleteStock(symbol) {
	if (confirm('Sur de vouloir retirer '+symbol+' ?')) {
		ajaxCall("GET", "stock_delete.php?symbol="+symbol, 'Stock '+symbol+' deleted !', true);
	}
}

function updateStock(symbol) {
	ajaxCall("GET", "stock_update.php?symbol="+symbol, 'Stock '+symbol+' updated !', true);
}

function displayInfo(content) {
	Swal.fire({ title: '', icon: 'info', html: content });
}