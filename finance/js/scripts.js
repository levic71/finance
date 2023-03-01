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
show     = function(id) { try { el(id).style.display = el(id).tagName.toUpperCase() == "TABLE" ? 'inline-table' : (el(id).getAttribute("data-display") ? el(id).getAttribute("data-display") : 'initial'); } catch(e) {} }
hide     = function(id) { try { el(id).setAttribute('data-display', el(id).style.display); el(id).style.display = 'none'; } catch(e) {} }
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
rmCN  = function(id, cn) { try { var tmp = el(id).className.split(' '); ncn = "";  for(var n=0;n<tmp.length;n++) { if (tmp[n] != cn) ncn += (ncn == "" ? "" : " ")+tmp[n]; }; el(id).className = ncn; } catch(e) { myconsole('rmCN:' + id + ':' + e); } }
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
	var no_scroll=opt.no_scroll||'0';
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
				// Changement classname sur container cible
				if (no_chg_cn == 0) setCN(id, (id == 'main' ? 'ui container inverted segment main ' : '')+ action + '_page');
				// Changement contenu container
				if (no_data == 0) cc(id, data);
				// Affichage des popups informatives
				if (msg != '') { var p = loadPrompt(); p.success(msg); }
				// Fermeture Sidebar
				rmCN('sidebar_menu', 'visible');
				// Top de page
				if (no_scroll == 0) scroll(0,0); 
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

format_num = function(id)
{
	if (el(id) && el(id).type == 'text')
		el(id).value = parseFloat(el(id).value.replace(/\,/g, '.').replace(/\s/g, ''));
}
check_num = function(num, label, min, max, display_err = true)
{
	if (num.length == 0) { if (display_err) Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Le champ "'+label+'" ne doit pas être vide'}); return false; }
	for(var i=0; i < num.length; i++)
	{
		var car=num.substring(i, i+1);
		if (!((car >= "0" && car <= "9") || car == '.')) { if (display_err) Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Le champ "'+label+'" doit être numérique' }); return false; }
	}
	if (num > max || num < min) { if (display_err) Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Le champ "'+label+'" doit être compris entre '+min+' et '+max }); return false; }
	return true;
}
format_and_check_num = function(field, label, min, max, display_err = true) {
	format_num(field);
	return check_num(valof(field), label, min, max, display_err);
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
	return depart == 0 ? 0 : ( ((arrive - depart) * 100) / depart);
}

getRatio = function(ref, val) {
	return ( (val * 100) / ref);
}

setColNumericTab = function(id, val, innerHTML, colored = true) {
	rmCN(id, "aaf-positive");
	rmCN(id, "aaf-negative");
	if (colored) addCN(id, val >= 0 ? "aaf-positive" : "aaf-negative");
	Dom.id(id).innerHTML = innerHTML;
	Dom.attribute(Dom.id(id), { 'data-value': val.toFixed(2) } );
}

setCookie = function(cname, cvalue, exdays) {
	const d = new Date();
	d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
	let expires = "expires="+d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
  
getCookie = function(cname, def = "") {
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
	return def;
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


var overlay = {
	// (A) INITIALIZE - CREATE OVERLAY HTML
	ewrap: null,    // html wrapper
	econtent: null, // html contents
	init: () => {
	  overlay.ewrap = document.createElement("div");
	  overlay.ewrap.id = "owrap";
	  overlay.ewrap.innerHTML = `<button id="oclose" onclick="overlay.hide()" class="circular ui icon very small right floated pink labelled button"><i class="inverted white close icon"></i></button><div id="ocontent"></div>`;
	  overlay.econtent = overlay.ewrap.querySelector("#ocontent");
	  document.body.appendChild(overlay.ewrap);
	},
  
	// (B) SHOW OVERLAY
	show: (content) => {
	  overlay.econtent.innerHTML = content;
	  // Execution du code javascript
	  try {
		  var allJs = overlay.econtent.getElementsByTagName("script");
		  for(var i=0; i < allJs.length; i++) { if (allJs[i].src && allJs[i].src != "") includeJs(allJs[i].src); else if (allJs[i].innerHTML && allJs[i].innerHTML != "") window.eval(allJs[i].innerHTML); }
	  } catch(e) { if (isIE8()) alert('IE8 mal support?, utiliser de pr?f?rence FF, IE9 ou Chrome'); else if (typeof allJs != "undefined" && typeof allJs[i] != "undefined") myconsole('cc: JKX caught error: (' + e.name + '): ' + e.message + '\n' + allJs[i].innerHTML ); else myconsole("cc: js error"+ e); }
	  overlay.ewrap.classList.add("show");
	},
	
	// (C) HIDE OVERLAY
	hide: () => { overlay.ewrap.classList.remove("show"); },
   
	// (D) LOAD & SHOW CONTENT VIA AJAX
	load : (url, data) => {
	  // (D1) FORM DATA
	  let form = new FormData();
	  if (data) { for (let [k,v] of Object.entries(data)) {
		form.append(k, v);
	  }}
   
	  // (D2) SET & SHOW CONTENTS
	  fetch(url, { method:"post", body:form })
	  .then((res) => { return res.text(); })
	  .then((txt) => { overlay.show(txt); });
	}
  };
   
  // (E) ATTACH OVERLAY TO PAGE
  document.addEventListener("DOMContentLoaded", overlay.init);

  var trendfollowing_ui = {

	tab_geo: {},
	tab_secteur: {},
	data_repartition : [[], [], []],
	labels_repartition: [[], [], []],
	bg_repartition: [[], [], []],
	ptf: { valo: 0, achats: 0, stoploss1: 0, stoploss2: 0, stopprofit: 0, objectif: 0 },

	getHtml : (pname, price, active, stoploss, objectif, stopprofit, seuils, options, strat_type, reg_type, reg_period) => {
		var mm200_opt = (options & 16) == 16 ? true : false;
		var mm100_opt = (options & 8)  == 8  ? true : false;
		var mm50_opt  = (options & 4)  == 4  ? true : false;
		var mm20_opt  = (options & 2)  == 2  ? true : false;
		var mm7_opt   = (options & 1)  == 1  ? true : false;
		let perf_stoploss   = stoploss   == 0 ? 0 : getPerf(price, stoploss).toFixed(2);
		let perf_stopprofit = stopprofit == 0 ? 0 : getPerf(price, stopprofit).toFixed(2);
		let perf_objectif   = objectif   == 0 ? 0 : getPerf(price, objectif).toFixed(2);

		html = '' +
			'<div class="ui form trendfollowing_ui_form"><div class="field">' +
			'<label style="text-align: center"><button class="ui primary button">' + pname + ' : ' + price + ' &euro;</button></label>' +
			'<label>Stop Loss   <span class="mini_button ' + (perf_stoploss >= 0   ? 'aaf-positive' : 'aaf-negative') + '">' + perf_stoploss   + '%</span></label><input id="f_stoploss"   class="swal2-input" type="text" placeholder="0.00" value="' + stoploss   + '" />' +
			'<label>Objectif    <span class="mini_button ' + (perf_objectif >= 0   ? 'aaf-positive' : 'aaf-negative') + '">' + perf_objectif   + '%</span></label><input id="f_objectif"   class="swal2-input" type="text" placeholder="0.00" value="' + objectif   + '" />' +
			'<label>Stop Profit <span class="mini_button ' + (perf_stopprofit >= 0 ? 'aaf-positive' : 'aaf-negative') + '">' + perf_stopprofit + '%</span></label><input id="f_stopprofit" class="swal2-input" type="text" placeholder="0.00" value="' + stopprofit + '" />' +
			'<label>Seuils</label><input id="f_seuils" class="swal2-input" type="text" placeholder="0.00;0.00;..." value="' + seuils + '" />' +
			'<label>Type stratégie</label><select id="f_strat_type"><option value="1" ' + (strat_type == 1 ? 'selected="selected"' : '') + '">Spéculatif</option><option value="2" ' + (strat_type == 2 ? 'selected="selected"' : '') + '">Dividende</option><option value="3" ' + (strat_type == 3 ? 'selected="selected"' : '') + '">Croissance</option><option value="4" ' + (strat_type == 4 ? 'selected="selected"' : '') + '">Dividende & croissance</option></select>' +
			'<label>Type régression</label><select id="f_reg_type"><option value="1" ' + (reg_type == 1 ? 'selected="selected"' : '') + '">Linéaire</option><option value="2" ' + (reg_type == 2 ? 'selected="selected"' : '') + '">Exponentiel</option><option value="3" ' + (reg_type == 3 ? 'selected="selected"' : '') + '">logarithmique</option><option value="4" ' + (reg_type == 4 ? 'selected="selected"' : '') + '">Polynomiale</option><option value="5" ' + (reg_type == 5 ? 'selected="selected"' : '') + '">Power</option></select>' +
			'<label>Période régression</label><input id="f_reg_period" class="swal2-input" type="text" placeholder="0" value="' + reg_period   + '" />' +
			'<label class="checkbox"><input id="f_mm200" type="checkbox" ' + (mm200_opt ? 'checked="checked"' : '') + '/> MM200 <input id="f_mm100" type="checkbox" ' + (mm100_opt ? 'checked="checked"' : '') + '/> MM100 <input id="f_mm50" type="checkbox" ' + (mm50_opt ? 'checked="checked"' : '') + '/> MM50 <input id="f_mm20" type="checkbox" ' + (mm20_opt ? 'checked="checked"' : '') + '/> MM20 <input id="f_mm7" type="checkbox" ' + (mm7_opt ? 'checked="checked"' : '') + '/> MM7</label>' +
			'<label class="checkbox"><input id="f_active" type="checkbox" ' + (active == 1 ? 'checked="checked"' : '') + '/> Active</label>' +
			'</div></div>' +
			'';

		return html;
	},
	formatValo : (price) => {
		var ret = price.toFixed(2) + '';
		if (price > 999999) {
			ret = (price / 1000000).toFixed(1) + 'M';
		} else if (price > 999) {
			ret = (price / 1000).toFixed(1) + 'K';
		}
		return ret;
	},
	checkForm : () => {
		var ret = true;
	
		if (!format_and_check_num('f_stoploss',   'Stop loss',   0, 999999, false)) ret = false;
		if (!format_and_check_num('f_stopprofit', 'Stop profit', 0, 999999, false)) ret = false;
		if (!format_and_check_num('f_objectif',   'Objectif',    0, 999999, false)) ret = false;
		if (!format_and_check_num('f_reg_period', 'Période régression', 0, 999999, false)) ret = false;

		return ret;
	},
	getOptionsValue : () => {
		return (valof('f_mm200') == 0 ? 0 : 16) | (valof('f_mm100') == 0 ? 0 : 8) | (valof('f_mm50') == 0 ? 0 : 4) | (valof('f_mm20') == 0 ? 0 : 2) | (valof('f_mm7') == 0 ? 0 : 1);
	},
	getUrlRedirect : (pname) => {
		var options = trendfollowing_ui.getOptionsValue();
		var params = attrs([ 'f_stoploss', 'f_stopprofit', 'f_objectif', 'f_seuils', 'f_strat_type', 'f_reg_type', 'f_reg_period' ]) + '&symbol=' + pname + '&f_active=' + (valof('f_active') == 0 ? 0 : 1) + '&options=' + options;
		return ('trend_following_action.php?action=stops' + params);
	},
	cal1PositionsTable : (table_id) => {

		Dom.find('#' + table_id + ' tbody tr').forEach(function(item) {

			ind      = Dom.attribute(item, 'id').split('_')[2];
			other    = Dom.attribute(item, 'data-other');
			taux     = Dom.attribute(item, 'data-taux');
			taux_moyen = Dom.attribute(item, 'data-taux-moyen');
			in_ptf   = Dom.attribute(item, 'data-in-ptf');
			sum_valo_in_euro = Dom.attribute(item, 'data-sum-valo-in-euro');
			actif    = Dom.attribute(Dom.id('f_actif_' + ind), 'data-pname');
			pru      = parseFloat(Dom.attribute(Dom.id('f_pru_'   + ind), 'data-pru'));
			price    = parseFloat(Dom.attribute(Dom.id('f_price_' + ind), 'data-value'));
			nb       = parseFloat(Dom.attribute(Dom.id('f_pru_'   + ind), 'data-nb'));
	
			// Recuperation des stops
			var stoploss   = 0;
			var stopprofit = 0;
			var objectif   = 0;
	
			var divs = item.getElementsByTagName("td")[5].getElementsByTagName("div")[0].getElementsByTagName("div");
	
			if (divs) {
				stoploss   = divs[0].innerHTML;
				objectif   = divs[1].innerHTML;
				stopprofit = divs[2].innerHTML;
			}
	
			// si devise != EUR, appliquer taux de change
			achat    = parseFloat(nb * pru * taux_moyen);
			valo     = parseFloat(nb * price * taux);
//			gain_pru = parseFloat(nb * (price - pru) * taux);
//			perf_pru = parseFloat(getPerf(pru, price));
			gain_pru = parseFloat(nb * ((price * taux) - (pru * taux_moyen))); // Taux moyen PRU ?
			perf_pru = parseFloat(getPerf(achat, valo));
//			gain_pru = parseFloat((nb * price * taux) - sum_valo_in_euro);
//			perf_pru = parseFloat(getPerf(sum_valo_in_euro, nb * price * taux));
	
			valo_stoploss1  = parseFloat(nb * (stoploss == 0 ? price : stoploss) * taux);
			valo_stoploss2  = parseFloat(nb * stoploss * taux);
			valo_objectif   = parseFloat(nb * (objectif == 0 ? price : objectif) * taux);
			valo_stopprofit = parseFloat(nb * (stopprofit == 0 ? price : stopprofit) * taux);
	
			// Data donuts
			trendfollowing_ui.labels_repartition[0].push(actif);
	
			trendfollowing_ui.ptf.achats += achat;
			trendfollowing_ui.ptf.valo   += valo;
	
			trendfollowing_ui.ptf.stoploss1  += valo_stoploss1;
			trendfollowing_ui.ptf.stoploss2  += valo_stoploss2;
			trendfollowing_ui.ptf.objectif   += valo_objectif;
			trendfollowing_ui.ptf.stopprofit += valo_stopprofit;
	
			setColNumericTab('f_valo_'  + ind, valo, trendfollowing_ui.formatValo(valo)  + '&euro;');
			Dom.attribute(Dom.id('f_valo2_' + ind), { 'data-value': valo.toFixed(2) } );
			setColNumericTab('f_perf_pru_' + ind, perf_pru, '<div><button class="tiny ui ' + (perf_pru >= 0 ? 'aaf-positive' : 'aaf-negative') + ' button">' + perf_pru.toFixed(2) + '%</button><label>' + (gain_pru >= 0 ? '+' : '') + trendfollowing_ui.formatValo(gain_pru) + '&euro;</label></div>');
	
			if (other == 1) {
				Dom.id('f_pct_jour_'  + ind).innerHTML = 'N/A';
			}
		});

	},
	cal2PositionsTable : (table_id) => {

		// On reparcours les lignes du tableau positions pour le % de chaque actif dans le portefeuille
		Dom.find('#' + table_id + ' tbody tr').forEach(function(item) {

			ind = Dom.attribute(item, 'id').split('_')[2];

			in_ptf   = Dom.attribute(item, 'data-in-ptf');

			secteur  = Dom.attribute(item.getElementsByTagName("td")[0], 'data-tootik');
			geo      = Dom.attribute(item.getElementsByTagName("td")[0], 'data-geo');

			price = parseFloat(Dom.attribute(Dom.id('f_price_' + ind), 'data-value'));
			nb    = parseFloat(Dom.attribute(Dom.id('f_pru_' + ind), 'data-nb'));
			valo  = parseFloat(nb * price);
			ratio = in_ptf == 0 ? 0 : getRatio(trendfollowing_ui.ptf.valo, valo).toFixed(2);

			trendfollowing_ui.tab_secteur[secteur] = (trendfollowing_ui.tab_secteur[secteur] ? parseFloat(trendfollowing_ui.tab_secteur[secteur]) : 0) + parseFloat(ratio);
			trendfollowing_ui.tab_geo[geo] = (trendfollowing_ui.tab_geo[geo] ? parseFloat(trendfollowing_ui.tab_geo[geo]) : 0) + parseFloat(ratio);

			setColNumericTab('f_poids_' + ind, Math.round(ratio), in_ptf == 0 ? '-' : Math.round(ratio) + '%', false);

			trendfollowing_ui.data_repartition[0].push(ratio);

		});

	},

	addStockDetailLinkPositionsTable : (table_id, ptf_id) => {

		// Listener sur button detail sur actif
		Dom.find('#' + table_id + ' tbody tr td:nth-child(2) button').forEach(function(element) {
			// pru = Dom.attribute(Dom.id('f_pru_' + element.parentNode.id.split('_')[2]), 'data-pru');
			Dom.addListener(element, Dom.Event.ON_CLICK, function(event) {
				other = Dom.attribute(element.parentNode.parentNode, 'data-other');
				if (other == 1)
					Swal.fire('Actif non suivi');
				else
					go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?ptf_id=' + ptf_id + '&symbol=' + element.innerHTML, loading_area: 'main' });
			});
		});

	},

	addUpdatePricePopupPositionsTable : (table_id) => {

		// Listener sur buttons manuel price ligne tableau si actif other
		Dom.find('#' + table_id + ' tbody tr td:nth-child(4) button').forEach(function(element) {

			let other = Dom.attribute(element.parentNode.parentNode.parentNode, 'data-other');
			let pname = Dom.attribute(element.parentNode.parentNode.parentNode, 'data-pname');

			if (other == 1) {
				Dom.addListener(element, Dom.Event.ON_CLICK, function(event) {

					// On récupère la valeur dans le button
					let quote = Dom.attribute(element, 'data-value');

					Swal.fire({
						title: '',
						html: '<div class="ui form"><div class="field">' +
									'<label>Saisie manuelle de la cotation</label><input type="text"<input id="f_quote" class="swal2-input" type="text" placeholder="0.00" value="' + quote + '" />' +
								'</div></div>',
						showCancelButton: true,
						confirmButtonText: 'Valider',
						cancelButtonText: 'Annuler',
						showLoaderOnConfirm: true,
						allowOutsideClick: () => !Swal.isLoading()
					}).then((result) => {
						if (result.isConfirmed) {
							if (!format_and_check_num('f_quote', 'Cotation', 0, 999999)) return false;
							let params = attrs([ 'f_quote' ]) + '&symbol=' + pname;
							go({ action: 'main', id: 'main', url: 'trend_following_action.php?action=manual_price&' + params, no_data: 1, no_chg_cn: 1 });
							element.innerHTML = valof('f_quote') + '&euro;';
							Dom.attribute(element, { 'data-value': valof('f_quote') });
							updateDataPage('change');
							Swal.fire('Données modifiées');
						}
					});

				});
			}
		});

	},

	addUpdateOptionsPopupPositionsTable : (table_id) => {

		// Listener sur buttons stoploss/stoplimit ligne tableau
		Dom.find('#' + table_id + ' tbody tr td:nth-child(6) > div').forEach(function(element) {
			Dom.addListener(element, Dom.Event.ON_CLICK, function(event) {

				// data sur tr
				var connected  = Dom.attribute(element.parentNode.parentNode, 'data-iuc');
				if (connected == 0) return;

				// On récupère les valeurs dans la cellule du tavleau - Pas tres beau !!!
				var divs   = element.getElementsByTagName("div");

				var pname      = Dom.attribute(element, 'data-pname');
				var price      = Dom.attribute(element.parentNode, 'data-value');
				var active     = Dom.attribute(element.parentNode, 'data-active');
				var stoploss   = divs[0].innerHTML;
				var objectif   = divs[1].innerHTML;
				var stopprofit = divs[2].innerHTML;
				var seuils     = Dom.attribute(element.parentNode, 'data-seuils') ? Dom.attribute(element.parentNode, 'data-seuils') : "";
				var options    = Dom.attribute(element.parentNode, 'data-options');
				var strat_type = parseInt(Dom.attribute(element.parentNode, 'data-strat-type'));
				var reg_type   = parseInt(Dom.attribute(element.parentNode, 'data-reg-type'));
				var reg_period = parseInt(Dom.attribute(element.parentNode, 'data-reg-period'));
		
				tf_ui_html = trendfollowing_ui.getHtml(pname, price, active, stoploss, objectif, stopprofit, seuils, options, strat_type, reg_type, reg_period);

				Swal.fire({
						title: '',
						html: tf_ui_html,
						showCancelButton: true,
						confirmButtonText: 'Valider',
						cancelButtonText: 'Annuler',
						showLoaderOnConfirm: true,
						preConfirm: () => {
							let c = trendfollowing_ui.checkForm();
							if (!c) Swal.showValidationMessage(`Formulaire invalide`);
							return c;
						},
						allowOutsideClick: () => !Swal.isLoading()
					}).then((result) => {
						if (result.isConfirmed) {

							go({ action: 'main', id: 'main', url: trendfollowing_ui.getUrlRedirect(pname), no_data: 1, no_chg_cn: 1 });

							divs[0].innerHTML = valof('f_stoploss');
							divs[1].innerHTML = valof('f_objectif');
							divs[2].innerHTML = valof('f_stopprofit');
							divs[0].className = divs[0].className.replaceAll('grey', '');
							divs[1].className = divs[1].className.replaceAll('grey', '');
							divs[2].className = divs[2].className.replaceAll('grey', '');
							if (valof('f_active') == 0 || parseInt(valof('f_stoploss'))   == 0) divs[0].className = divs[0].className + ' grey';
							if (valof('f_active') == 0 || parseInt(valof('f_objectif'))   == 0) divs[1].className = divs[1].className + ' grey';
							if (valof('f_active') == 0 || parseInt(valof('f_stopprofit')) == 0) divs[2].className = divs[2].className + ' grey';
							Dom.attribute(element.parentNode, { 'data-seuils'  : valof('f_seuils') });
							Dom.attribute(element.parentNode, { 'data-strat-type' : valof('f_strat_type') });
							Dom.attribute(element.parentNode, { 'data-reg-type'   : valof('f_reg_type') });
							Dom.attribute(element.parentNode, { 'data-reg-period' : valof('f_reg_period') });
							Dom.attribute(element.parentNode, { 'data-options' : trendfollowing_ui.getOptionsValue() });
							Dom.attribute(element.parentNode, { 'data-active'  : valof('f_active') == 0 ? 0 : 1 });

							Swal.fire('Données modifiées');
						}
					});
				
			});
		});

	},

	computePositionsTable : (table_id, ptf_id) => {
		// Reset
		trendfollowing_ui.tab_geo = {};
		trendfollowing_ui.tab_secteur = {};
		trendfollowing_ui.data_repartition = [[], [], []];
		trendfollowing_ui.labels_repartition = [[], [], []];
		trendfollowing_ui.bg_repartition = [[], [], []];
		trendfollowing_ui.ptf = { valo: 0, achats: 0, stoploss1: 0, stoploss2: 0, stopprofit: 0, objectif: 0 };

		// Computing	
		trendfollowing_ui.cal1PositionsTable(table_id);
		trendfollowing_ui.cal2PositionsTable(table_id);

		// On garnit les data du donut secteur
		for (var key in trendfollowing_ui.tab_secteur) {
			trendfollowing_ui.labels_repartition[1].push(key);
			trendfollowing_ui.data_repartition[1].push(trendfollowing_ui.tab_secteur[key]);
		}

		// On garnit les data du donut geo
		for (var key in trendfollowing_ui.tab_geo) {
			trendfollowing_ui.labels_repartition[2].push(key);
			trendfollowing_ui.data_repartition[2].push(trendfollowing_ui.tab_geo[key]);
		}

		// Garanissage des tablaux de couleurs
		[ 0, 1, 2].forEach(function(ind) {

			let nb_actifs = trendfollowing_ui.data_repartition[ind].length;
			if (nb_actifs == 0) {

				trendfollowing_ui.data_repartition[ind].push(100);
				trendfollowing_ui.labels_repartition[ind].push('None');
				trendfollowing_ui.bg_repartition[ind].push('rgb(200, 200, 200)');

			} else {

				// var colrs = ['#e41a1c','#377eb8','#4daf4a','#984ea3','#ff7f00','#ffff33','#a65628','#f781bf','#999999'];
				// var colrs = [ '#9b59b6', '#2980b9', '#1abc9c', '#27ae60', '#f1c40f', '#e67e22', '#7d3c98', '#e74c3c' ];
				var colrs = [];
				var h = 225;
				for (var n = 0; n < nb_actifs; n++) {
					var c = new KolorWheel([h, 63, 62]);
					colrs.push(c.getHex());
					h += Math.round(360 / nb_actifs);
				}

				colrs.forEach((item) => { trendfollowing_ui.bg_repartition[ind].push(item); });
			}
		});

		trendfollowing_ui.addStockDetailLinkPositionsTable(table_id, ptf_id);
		trendfollowing_ui.addUpdatePricePopupPositionsTable(table_id);
		trendfollowing_ui.addUpdateOptionsPopupPositionsTable(table_id);

	},
	getData: () => { return { a: trendfollowing_ui.tab_geo, b: trendfollowing_ui.tab_secteur, c: trendfollowing_ui.data_repartition, d: trendfollowing_ui.labels_repartition } }

  }

