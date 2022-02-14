var js_debug = true;

error = function(e) { alert('error=' + e); }

isTouch = function() { return ( (navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i)) || (navigator.userAgent.match(/iPad/i))  || (navigator.userAgent.match(/Android/i))); }
isIE8   = function() { return (document.all && document.querySelector && !document.addEventListener); }

myconsole = function(text) { if (js_debug) { if (typeof console.log !== 'undefined') console.log(text); else alert(text); } }

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
fs       = function(id) { try { el(id).focus(); } catch(e) {} }
isHidden = function(id) { try { return (el(id).style.display == 'none' ? true : false); } catch(e) {} }
showelt  = function(id) { try { el(id).style.display = 'block'; } catch(e) {} }
show     = function(id) { try { el(id).style.display = el(id).tagName.toUpperCase() == "TABLE" ? 'inline-table' : 'initial'; } catch(e) {} }
hide     = function(id) { try { el(id).style.display = 'none'; } catch(e) {} }
toogle   = function(id) { try { if (isHidden(id)) show(id); else hide(id); } catch(e) {} }
valof    = function(id) {
	ret = '';
	if (el(id)) {
		if (el(id).type == 'checkbox')
			ret = el(id).checked ? el(id).value : 0;
		else if (el(id).type == 'select')
		{ var elts = document.getElementsByName(id); for (i=0;i<elts.length;i++) if (elts[i].selected) ret = elts[i].value; }
		else if (el(id).type == 'radio')
		{ var elts = document.getElementsByName(id); for (i=0;i<elts.length;i++) if (elts[i].checked) ret = elts[i].value; }
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
isCN  = function(id, cn) { try { var tmp = el(id).className.split(' '); ret=false; for(var n=0;n<tmp.length;n++) { if (tmp[n] == cn) ret=true; }; return ret; } catch(e) { myconsole('isCN:' + id + ':' + e); } }
replaceCN = function(id, cn1, cn2) { try { el(id).className = el(id).className.replace(cn1, cn2); } catch(e) { myconsole('replaceCN:' + id + ':' + e); } }
switchCN  = function(id, cn1, cn2) { try { el(id).className = isCN(id, cn1) ? el(id).className.replace(cn1, cn2) : el(id).className.replace(cn2, cn1); } catch(e) { myconsole('switchCN:' + id + ':' + e); } }
toogleCN  = function(id, cn) { try { if (isCN(id, cn)) rmCN(id, cn); else addCN(id, cn); } catch(e) { myconsole('switchCN:' + id + ':' + e); } }

toogleCheckBox = function(id) { el(id).checked = el(id).checked ? false : true; }

go = function(args) {
	var opt = args||{};
	var id=opt.id||'main';
	var action=opt.action||'';
	var menu=opt.menu||'';
	var confirmdel=opt.confirmdel||0;
	var no_data=opt.no_data||'0';
	var no_chg_cn=opt.no_chg_cn||'0';
	var msg=opt.msg||'';
	var loading_area=opt.loading_area||'';

	myconsole('----> go begin : action=' + action + ' -- url=' + opt.url);

	letsgo = true;
	if (opt.confirmdel == 1) letsgo = confirm('Confirmez vous cette suppression ?');

	if (letsgo) {
		if (opt.confirmdel == 1) opt.url += '&del=1';
		if (loading_area != '') addCN(loading_area, 'loading');
		if (menu != '') change_wide_menu_state('wide_menu', menu);
		jx.load(
			opt.url,
			function(data) {
				myconsole('----> go jx in  : action='+action+' -- url='+opt.url);
				if (no_chg_cn == 0) setCN(id, (id == 'main' ? 'ui container inverted segment main ' : '')+action+'_page');
				if (no_data == 0)   cc(id, data);
				if (msg != '') {
					var p = loadPrompt();
					p.success(msg);
				}
				rmCN('sidebar_menu', 'visible');
				myconsole('----> go jx out : action='+action+' -- url='+opt.url);
			},
			'text', 'post'
		);
	}

	myconsole('----> go end   : action=' + action + ' -- url=' + opt.url);
}

change_wide_menu_state = function(menu, item_menu) {
	items = Dom.children(Dom.id(menu), "a");
	for (var i = 0; i < items.length; i++) {
		if (items[i].id == item_menu)
			addCN(items[i].id, 'active');
		else
			rmCN(items[i].id, 'active');
	}
}

toogle_table = function(id_table_body, ind_row) {
	items = Dom.children(Dom.id(id_table_body), "tr");
	try { items[ind_row].style.display = items[ind_row].style.display == "none" || items[ind_row].style.display == "" ? 'contents' : "none"; }
	catch(e) {}
}
toogle_table2 = function(id_row) {
	el(id_row).style.display = el(id_row).style.display == "none" || el(id_row).style.display == "" ? 'contents' : "none";
}

check_email = function(txt) {
	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(txt);
}

check_JJMMAAAA = function(str, label)
{
	if (str.length == 0) { Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text : 'Le champ "'+label+'" ne doit pas être vide'}); return false; }
	if (!(str.length == 10)) { Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text : 'Le champ "'+label+'" doit être de la forme JJ/MM/AAAA' }); return false; }
	var jour=str.substring(0, 2); var mois=str.substring(3, 5); var year=str.substring(6, 10);
	if (jour > 31 || jour < 1 || mois < 1 || mois > 12) { Swal.fire({title: 'Formulaire non valide !', icon: 'error', text: 'Le champ "'+label+'" doit être de la forme JJ/MM/AAAA'});  return false; }
	return true;
}

check_num = function(num, label, min, max)
{
	if (num.length == 0) { Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Le champ "'+label+'" ne doit pas être vide'}); return false; }
	for(var i=0; i < num.length; i++)
	{
		var car=num.substring(i, i+1);
		if (!((car >= "0" && car <= "9") || car == '.')) { Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Le champ "'+label+'" doit être numérique' }); return false; }
	}
	if (num > max || num < min) { Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Le champ "'+label+'" doit être compris entre '+min+' et '+max }); return false; }
	return true;
}
isacar = function(car) { if ((car >= "0" && car <= "9") || (car >= "A" && car <= "Z") || (car >= "a" && car <= "z")) return true; return false; }
isacarext = function(car) {
	if ((car >= "0" && car <= "9") || (car == "&") || (car == "Ã©") || (car == "\"") || (car == "\n") || (car == "'") || (car == "(") ||
		(car == ")") || (car == "-") || (car == "Ã¨") || (car == "_") || (car == "Ã§") || (car == ",") || (car == "Ã ") || (car == ")") ||
		(car == "=") || (car == "+") || (car == "#") || (car == "{") || (car == "[") || (car == "|") || (car == "\\") || (car == "@") ||
		(car == "Ã¹") || (car == "$") || (car == "Â£") || (car == "Â§") || (car == "Ãª") || (car == "Ã¢") || (car == "Ã´") || (car == "Ã¤") ||
		(car == "Ã«") || (car == " ") || (car == "Ã¯") || (car == "\;") || (car == ".") || (car == "?") || (car == "/") || (car == ":") ||
		(car == "!") || (car == "Â°") || (car == "%") || (car >= "A" && car <= "Z") || (car >= "a" && car <= "z"))
		return true;

	return false;
}
check_alphanum_gen = function(str, label, size, type)
{
	if (str.length == 0) { Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Le champ "'+label+'" ne doit pas être vide'}); return false; }
	if (size != -1 && str.length < size) { Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Minimum '+size+' caractères pour "'+label+'"'}); return false; }
	for(var i=0; i < str.length; i++)
	{
		var car=str.substring(i, i+1);
		if (type == 0)
			if (!isacar(car)) { Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: '"'+label+'" doit être alphanumérique'}); return false; }
		else
			if (!isacarext(car)) { Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: '"'+label+'" doit être alphanumérique'}); return false; }
	}
	return true;
}
check_alphanum    = function(str, label, size) { return check_alphanum_gen(str, label, size, 0); }
check_alphanumext = function(str, label, size) { return check_alphanum_gen(str, label, size, 1); }


switchColorElement = function(elt, c1, c2) {
	elt_on = Dom.hasClass(Dom.id(elt), c1);
	if (elt_on) {
		Dom.removeClass(Dom.id(elt), c1);
		Dom.addClass(Dom.id(elt), c2);
	}
	else {
		Dom.removeClass(Dom.id(elt), c2);
		Dom.addClass(Dom.id(elt), c1);
	}
}

getPerf = function(depart, arrive) {
	return ( ((arrive - depart) * 100) / depart);
}

getRatio = function(ref, val) {
	return ( (val * 100) / ref);
}

setInputValueAndKeepLastCar = function(id, str) {
	Dom.id(id).value = str + " " + Dom.id(id).value.substring(Dom.id(id).value.length - 1);
}

setColNumericTab = function(id, val, innerHTML) {
	rmCN(id, "aaf-positive");
	rmCN(id, "aaf-negative");
	addCN(id, val >= 0 ? "aaf-positive" : "aaf-negative");
	Dom.id(id).innerHTML = innerHTML;
	Dom.attribute(Dom.id(id), { 'data-value': val.toFixed(2) } );
}

setCookie = function(cname, cvalue, exdays) {
	const d = new Date();
	d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
	let expires = "expires="+d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
  
getCookie = function(cname) {
	let name = cname + "=";
	let ca = document.cookie.split(';');
	for(let i = 0; i < ca.length; i++) {
	  let c = ca[i];
	  while (c.charAt(0) == ' ') {
		c = c.substring(1);
	  }
	  if (c.indexOf(name) == 0) {
		return c.substring(name.length, c.length);
	  }
	}
	return "";
}
  
checkCookie = function() {
	let user = getCookie("username");
	if (user != "") {
	  alert("Welcome again " + user);
	} else {
	  user = prompt("Please enter your name:", "");
	  if (user != "" && user != null) {
		setCookie("username", user, 365);
	  }
	}
}