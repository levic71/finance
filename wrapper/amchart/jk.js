/* globals var */
var journees = "";

/* dom */
el = function(id) { return document.getElementById(id); }
cc = function(id, c) {
	obj = el(id);
	try {
		obj.innerHTML = c;
		var allJs = obj.getElementsByTagName("script");
		for(var i=0; i < allJs.length; i++) { if (allJs[i].src && allJs[i].src != "") includeJs(allJs[i].src); else window.eval(allJs[i].innerHTML); }
	} catch(e) { console.error('JKX caught error: (' + e.name + '): ' + e.message + '\n' + allJs[i].innerHTML); }
}
fs = function(id) { try { el(id).focus(); } catch(e) {} }
show = function(id) { try { el(id).style.display = 'block'; } catch(e) {} }
hide = function(id) { try { el(id).style.display = 'none'; } catch(e) {} }
toogle = function(id) { try { if (el(id).style.display == 'block') hide(id); else show(id); } catch(e) {} }
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
addCN = function(id, cn) { try { el(id).className += (el(id).className == "" ? "" : " ")+cn; } catch(e) {} }
rmCN  = function(id, cn) { try { var tmp = el(id).className.split(' '); ncn = ""; for(var n=0;n<tmp.length;n++) { if (tmp[n] != cn) ncn += " "+tmp[n]; }; el(id).className = ncn; } catch(e) {} }
showtip = function(id) { addCN(id, 'ToolTextHover'); el(id).onmouseout=function() { hidetip(id) }; }
hidetip = function(id) { if (el(id)) rmCN(id, 'ToolTextHover'); }
mandatory = function(ids) { for(var n=0;n<ids.length;n++) { addCN(ids[n], 'mandatory'); } }

addIcon = function(id, img) {
	ico = document.createElement('img');
	ico.setAttribute('src', img);
	ico.className = 'icon';
	el(id).appendChild(ico);
}

addDelIcon = function(id) { addIcon(id, 'img/delete_16.png'); }
addPlusIcon = function(id) { addIcon(id, 'img/plus_16.png'); }
addOkIcon = function(id) { addIcon(id, 'img/tick_16.png'); }
addBlockIcon = function(id) { addIcon(id, 'img/block_16.png'); }

rmIcon = function(id) {
	children = el(id).getElementsByTagName('img');
	for(var i=0; i < children.length; i++) el(id).removeChild(children[i]);
}

/* tracking */
google_tracking = function(page) {
	if (window.location.href.indexOf('localhost') > -1) return;
	var tmp = page.split(page.indexOf('edit_') > -1 ? '?' : '&');
	var pageTracker = _gat._getTracker("UA-1509984-1");
	pageTracker._initData();
	pageTracker._trackPageview('/wrapper/'+(tmp.length > 1 ? tmp[0] : page));
}

/* action */
go = function(args) {
	var opt = args||{};
	var action=opt.action||'';
	var confirmdel=opt.confirmdel||0;
	var confirminvit=opt.confirminvit||0;
	letsgo = true;
	if (opt.confirmdel == 1) letsgo = confirm('Confirmez vous cette suppression ?');
	if (opt.confirminvit == 1) letsgo = confirm('Confirmez vous l\'envoi de cette invitation aux participants de cette journée ?');

	if (letsgo) {
		if (opt.confirmdel == 1) opt.url += '&del=1';
		el('loader').className = "waiting";
		jx.load(
			opt.url,
			function(data) {
				cc(opt.id, data);
				el('loader').className = "standby";
				setmenu(action);
				google_tracking(opt.url);
			},
			'text', 'post'
		);
	}
}

go2 = function(args) {
	var opt = args||{};
	letsgo = true;

	if (letsgo) {
		jx.load(
			opt.url,
			function(data) {
				cc(opt.id, data);
			},
			'text', 'post'
		);
	}
}

/* menu */
xx = function(args) {
	var opt = args||{};
	el('loader').className = "waiting";
	jx.load(
		opt.url,
		function(data) {

			var tmp = data.split('||');
			if (tmp.length > 1)
			{
				if (tmp[0] > 0)
				{
					if (opt.action == 'valid') { el('settings').style.display='block'; el('mailme').style.display='none'; el('log').className = "poweron"; mm({action: 'tables'}); }
					if (opt.action == 'login') { el('settings').style.display='none'; el('mailme').style.display='block'; el('log').className = ""; cc('main', '')}
					if (opt.action == 'login') $aMsg({ msg: tmp[1] }); else $cMsg({ msg: tmp[1] });
					if (opt.action == 'login' && tmp[0] == 2) mm({action: 'tables'});
				}
				else
					$dMsg({ msg: tmp[1] });
			}
			else
				cc(opt.id, data);

			d = new Date();
			if (opt.action == 'days') {
				var myad = (el('ad')&&el('ad').value==1)?1:0;
				cal(d.getMonth()+1, d.getFullYear(), myad, opt.tournoi);
				if (opt.grid == -1)
					{ hide('box'); hide('swap2'); }
				else
					{ hide('box2'); hide('swap1'); }
			}
			el('loader').className = "standby";
			setmenu(opt.action);
			if (opt.url == 'login.php' && tmp[0] > 0) opt.url = 'logout.php';
			if (opt.action == 'valid') { var t = opt.url.split('|'); opt.url = t[0].replace('params', 'idc'); }
			google_tracking(opt.url);
		},
		'text', 'post'
	);
}

mm = function(args) {
	var opt = args||{};
	var action=opt.action||'tables';
	var grid=opt.grid||1;
	var params=opt.params||'';
	var page=opt.page||0;
	var next=opt.next||0;
	var prev=opt.prev||0;
	var idj=opt.idj||0;
	var idp=opt.idp||0;
	var idt=opt.idt||0;
	var name=encodeURIComponent(opt.name)||'';
	var date=opt.date||'';
	var idc=opt.idc||85;
	var idg=opt.idg||0;
	var search=opt.search||0;
	var sort=opt.sort||'';
	var tournoi=opt.tournoi||0;
	var favoris=opt.favoris||0;
	var sport_sort=opt.sport_sort||99;
	var filtre_type_champ=opt.filtre_type_champ||9;
	var mem = store.get(action);
	var mktime = Math.floor((new Date()).getTime() / 1000);
	var search_value = (search == 1 && valof('search') != '') ? valof('search') : '';

	if (mem != null && next == 0 && prev == 0 && page == 0 && search == 0 && sort == '') {
		if ((mktime - mem.mktime) < 3600)
		{
			page = mem.page;
			if (mem.search_value != '' && mem.search_value != 'undefined') { search = 1; search_value = mem.search_value; }
			sort = mem.sort;
		}
		else
			store.remove(action);
	}
	else
		store.set(action, { mktime: mktime, page: page, search_value: search_value, sort: sort });

	if (action == 'home')
		window.location = 'jk.php?idc='+idc;
	else if (action == 'login')
		xx({action: action, id:'main', url:'login.php'});
	else if (action == 'photos')
		xx({action: action, id:'main', url:'albums.php?id_theme='+idg});
	else if (action == 'valid')
		xx({action: action, id:'main', url:'login_valid.php?params='+params});
	else if (action == 'days')
		xx({action: action, id:'main', tournoi: tournoi, url:'grid.php?action='+action+'&page='+page+'&sort='+sort+(search == 1 ? '&search='+search_value : ''), grid: grid});
	else if (action == 'tables')
		xx({action: action, id:'main', url:'table_teams.php'});
	else if (action == 'stats' && idt!=0)
		xx({action: action, id:'main', url:'stats_team.php?idt='+idt});
	else if (action == 'stats' && idp!=0)
		xx({action: action, id:'main', url:'stats_player.php?idp='+idp});
	else if (action == 'matches' && tournoi == 1)
		xx({action: action, id:'main', tournoi: tournoi, url:'tournament_matches.php?action='+action+'&page='+page+'&idj='+idj+'&name='+name+'&date='+date});
	else if (action == 'matches')
		xx({action: action, id:'main', tournoi: tournoi, url:'grid.php?action='+action+'&page='+page+'&idj='+idj+'&name='+name+'&date='+date});
	else if (action == 'fannys' && idp!=0)
		xx({action: action, id:'main', url:'grid.php?action='+action+'&page='+page+'&idp='+idp});
	else if (action == 'fannys' && idt!=0)
		xx({action: action, id:'main', url:'grid.php?action='+action+'&page='+page+'&idt='+idt});
	else
		xx({action: action, id:'main', tournoi: tournoi, url:'grid.php?action='+action+'&page='+page+'&sort='+sort+(search == 1 ? '&search='+search_value : '')+(filtre_type_champ != 9 ? '&filtre_type_champ='+filtre_type_champ : '')+(favoris != 0 ? '&favoris='+favoris : '')+'&sport_sort='+sport_sort});
}

setmenu = function(action) {
	if (action == 'stats' || action == 'matches' || action == 'fannys') return;
	buttons = document.getElementById('shortcuts').getElementsByTagName('li');
	for(var i=0; i < buttons.length; i++) {
		if (buttons[i].id != 'loader') {
			buttons[i].className = buttons[i].className.replace(' selected', '');
			if (buttons[i].className == action) buttons[i].className += ' selected';
		}
	}
}

/* calendar */
cal = function(month, year, admin, tournoi) { cc('box2', buildCal(month, year, "iPhoneCal", admin, tournoi)); if (journees == "") cal_getjournees(month, year, admin, tournoi); else cal_setjournees(month, year, admin, tournoi); }
cal_go   = function(month, year, admin, tournoi) { cc('box2', buildCal(month, year, "iPhoneCal", admin, tournoi)); cal_setjournees(month, year, admin, tournoi); }
cal_prev = function(month, year, admin, tournoi) { if (month == 1)  { month = 12; year -= 1; } else month -= 1; cal_go(month, year, admin, tournoi); }
cal_next = function(month, year, admin, tournoi) { if (month == 12) { month = 1;  year += 1; } else month += 1; cal_go(month, year, admin, tournoi); }
cal_getjournees = function(month, year, admin, tournoi) { jx.load('json_getjournees.php?month='+month+'&year='+year, function(data){ var json = eval('(' + data + ')'); journees = json.journees; cal_setjournees(month, year, admin, tournoi); },'text','post'); }
cal_setjournees = function(month, year, admin, tournoi) {
	for(var n=0;n<journees.length;n++) {
		var tmp = journees[n].date.split('/');
		if (parseInt(tmp[1],10) == parseInt(month,10) && parseInt(tmp[2],10) == parseInt(year,10)) {
			el('cal_day_1_'+journees[n].day).className += " dayplayed";
			(function(myid, myname, mydate){
				el('cal_day_1_'+journees[n].day).onclick = function() { mm({action: 'matches', tournoi: tournoi, idj: myid, name: myname, date: mydate}); }
			})(journees[n].id, journees[n].nom, journees[n].date);
		}
	}
	if (admin == 1) {
		for(var i=0; i<= 31; i++) {
			if (el('cal_day_1_'+i)) {
				tmp = el('cal_day_1_'+i).className.split(' ');
				var dayplayed = false;
				for(var k=0; k<tmp.length; k++)
					if (tmp[k] == "dayplayed") dayplayed = true;

				if (!dayplayed) {
					el('cal_day_1_'+i).onclick = function() {
						var tmp = String(el('prevdate').onclick).split('(');
						var val = tmp[2].split(')');
						var ddd = val[0].split(',');
						var month = parseInt(ddd[0]); if (month < 10) month = '0'+month;
						var year  = parseInt(ddd[1]);
						var args = (this.id).split('_');
						var day = args[3]; if (day < 10) day = '0'+day;
						go({action: 'days', id:'main', url:'edit_days.php?refdate='+day+'/'+month+'/'+year});
					}
				}
			}
		}
	}
}
swap_cal = function(sens) { if (sens == 0) { show('swap2'); hide('swap1'); hide('box2'); show('box'); } else { show('swap1'); hide('swap2'); hide('box'); show('box2'); } }

/* other */
check_email = function(str)
{
	var filter=/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i
	if (!filter.test(str))
	{
		alert("Le format du champ Email est incorrect !");
		return false;
	}
	return true;
}

check_JJMMAAAA = function(str, label)
{
	if (str.length == 0) { alert('Le champ <'+label+'> ne doit pas être vide'); return false; }
	if (!(str.length == 10)) { alert('Le champ <'+label+'> doit être de la forme JJ/MM/AAAA'); return false; }
	var jour=str.substring(0, 2); var mois=str.substring(3, 5); var year=str.substring(6, 10);
	if (jour > 31 || jour < 1 || mois < 1 || mois > 12) { alert('Le champ <'+label+'> doit être de la forme JJ/MM/AAAA'); return false; }
	return true;
}

check_num = function(num, label, min, max)
{
	if (num.length == 0) { alert('Le champ <'+label+'> ne doit pas être vide'); return false; }
	for(var i=0; i < num.length; i++)
	{
		var car=num.substring(i, i+1);
		if (!(car >= "0" && car <= "9")) { alert('Le champ <'+label+'> doit être numérique'); return false; }
	}
	if (num > max || num < min) { alert('Le champ <'+label+'> doit être compris entre '+min+' et '+max); return false; }
	return true;
}
isacar = function(car) { if ((car >= "0" && car <= "9") || (car >= "A" && car <= "Z") || (car >= "a" && car <= "z")) return true; return false; }
isaextcar = function(car)
{
	if ((car >= "0" && car <= "9") || (car == "&") || (car == "é") || (car == "\"") || (car == "\n") || (car == "'") || (car == "(") ||
		(car == ")") || (car == "-") || (car == "è") || (car == "_") || (car == "ç") || (car == ",") || (car == "à") || (car == ")") ||
		(car == "=") || (car == "+") || (car == "#") || (car == "{") || (car == "[") || (car == "|") || (car == "\\") || (car == "@") ||
		(car == "ù") || (car == "$") || (car == "£") || (car == "§") || (car == "ê") || (car == "â") || (car == "ô") || (car == "ä") ||
		(car == "ë") || (car == " ") || (car == "ï") || (car == "\;") || (car == ".") || (car == "?") || (car == "/") || (car == ":") ||
		(car == "!") || (car == "°") || (car == "%") || (car >= "A" && car <= "Z") || (car >= "a" && car <= "z"))
		return true;

	return false;
}
check_alphanum_gen = function(str, label, size, type)
{
	if (str.length == 0) { alert('Le champ <'+label+'> ne doit pas être vide'); return false; }
	if (size != -1 && str.length < size) { alert('Le champ <'+label+'> doit être composé d\'au moins '+size+' caractères alphanumériques'); return false; }
	for(var i=0; i < str.length; i++)
	{
		var car=str.substring(i, i+1);
		if (type == 0)
			if (!isacar(car)) { alert('Le champ <'+label+'> doit être alphanumérique'); return false; }
		else
			if (!isacarext(car)) { alert('Le champ <'+label+'> doit être alphanumérique'); return false; }
	}
	return true;
}
check_alphanum    = function(str, label, size) { return check_alphanum_gen(str, label, size, 0); }
check_alphanumext = function(str, label, size) { return check_alphanum_gen(str, label, size, 1); }

upperFirstLetter = function(str)
{
	if (str.length == 0) return str;

	if (str.length >= 2)
	{
		first = str.substring(0,1);
		rest  = str.substring(1);
		return (first.toUpperCase()+rest.toLowerCase());
	}
	else
		return str.toUpperCase();
}

/* days */
setcheckedallitems = function(val) { for(i=1; i <= valof('nb_items'); i++) el('item'+i).checked = val; }
selectall = function() { setcheckedallitems(true); }
unselectall = function() { setcheckedallitems(false); }

/* upload */
startUpload = function() {
	hide('f1_upload_err'); hide('f1_upload_ok');
	show('f1_upload_process'); hide('f1_upload_form');
	document.uploadform.submit();
	return true;
}

removeImg = function(elt, filename, target) {
	if (confirm('Etes-vous sur de vouloir supprimer cette image ?'))
	{
		elt.parentNode.removeChild(elt);
		var ret = '';
		var tmp = valof(target).split(',');
		for(var i=0; i < tmp.length; i++)
			if (tmp[i] != filename) ret += (ret == '' ? '' : ',')+tmp[i];
		el(target).value = ret;
	}
}

stopUpload = function(data) {
	var tmp = data.split('|');
	var success=tmp[0]||'0';
	var filename=tmp[1]||'';
	var target=tmp[2]||'';
	var image=tmp[3]||'';
	var multi=tmp[4]||0;
	if (success == '1')	{
		if (multi == 1) {
			if (target != '') el(target).value += (el(target).value == '' ? '' : ',')+filename;
			var img = document.createElement("img");
			img.src = filename;
			img.className = 'button gray';
			img.onclick = function() {
				removeImg(this, filename, target);
			};
			el(image).appendChild(img);
		} else {
			if (target != '') el(target).value = filename;
			if (image != '') el(image).src = filename;
		}
		show('f1_upload_ok');
	}
	else
		show('f1_upload_err');
	document.getElementById('f1_upload_form').innerHTML = '<label for="myfile">&nbsp;</label><input name="myfile" id="myfile" type="file" size="30" /><button onclick="startUpload();" class="button blue">Upload</button><small>< 100ko</small>';
	hide('f1_upload_process'); show('f1_upload_form');
	return true;
}

liveScoring = function(id_match, nom1, nom2, nbsets, resultat) { counter.create({ id_match: id_match, name: 'score', l1: nom1, l2: nom2, nbsets: nbsets, resultat: resultat, view: 2 }); }
viewScoring = function(id_match, nom1, nom2, nbsets, resultat) { counter.create({ id_match: id_match, name: 'score', l1: nom1, l2: nom2, nbsets: nbsets, resultat: resultat, view: 1 }); }

choosegooglemap = function() {
	var container = document.createElement("div");
	container.id = 'geolocalisation';
	blackscreen.create();
	blackscreen.pushInBody(container);

	var tmp = encodeURIComponent(valof('lieu_pratique')).split(',');
	var street = tmp[0]||'';
	var zip = tmp[1]||'';
	var city = tmp[2]||'';
	var state = tmp[3]||'';
	var country = tmp[4]||'';
	if (street == '' && zip == '' && city == '' && state == '' && country == '') { city = 'Paris'; country = 'France'; }
	if (valof('lat') != '') { street = ''; zip = ''; city = ''; state = ''; country = ''; }

	go({action: 'leagues', id: 'geolocalisation', url: 'admin_address_chooser.php?zoom='+valof('zoom')+'&lat='+valof('lat')+'&lng='+valof('lng')+'&street='+street+'&zip='+zip+'&city='+city+'&state='+state+'&country='+country});
}

closegooglemap = function() {
	// appel depuis l'iframe
	window.parent.document.getElementById('blackscreen').style.display='none';
	window.parent.document.getElementById('geolocalisation').parentNode.removeChild(window.parent.document.getElementById('geolocalisation'));
}

/* effets */
var jfx=function(){
	var speed = 10;
	var delay = 20;
	var endalpha = 100;
	var alpha = 0;
	var ie = document.all ? true : false;
	var top = 25;
	var left = -50;
	var tt,h, bs;
	return{
		display:function(id){
			e = document.getElementById(id);
			if (!e) return;
			clearInterval(e.timer);
			e.style.opacity = 100;
			e.style.filter = 'alpha(opacity=100)';
			e.style.display = 'block';
		},
		show:function(id){
			e = document.getElementById(id);
			if (!e) return;
//			if (e.style.display == 'block') return;
			e.style.opacity = 0;
			e.style.filter = 'alpha(opacity=0)';
			e.style.display = 'block';
			clearInterval(e.timer);
			e.timer = setInterval(function(){jfx.fade(id, 1)},delay);
		},
		buildblackscreen:function() {
			if(bs != null) return;
			bs = document.createElement('div');
			bs.setAttribute('id','blackscreen');
			document.body.insertBefore(bs, document.body.firstChild);
			fr = document.createElement('div');
			fr.setAttribute('id','overblackscreen');
			fr.style.opacity = 0;
			fr.style.filter = 'alpha(opacity=0)';
			fr.style.display = 'none';
			document.body.insertBefore(fr, document.body.firstChild);
			bs.style.display = 'none';
		},
		popincontent:function(c) {
			jfx.buildblackscreen();
			cc('overblackscreen', c);
		},
		popin: function() {
			if(el('blackscreen')) el('blackscreen').style.display = 'block';
			jfx.show('overblackscreen');
		},
		popout: function() {
			if(el('blackscreen')) el('blackscreen').style.display = 'none';
			jfx.hide('overblackscreen');
		},
		fade:function(id, d){
			e = document.getElementById(id);
			if (!e) return;
			var a = alpha;
			if((a != endalpha && d == 1) || (a != 0 && d == -1)){
				var i = speed;
				if(endalpha - a < speed && d == 1){
					i = endalpha - a;
				}else if(alpha < speed && d == -1){
					i = a;
				}
				alpha = a + (i * d);
				e.style.opacity = alpha * .01;
				e.style.filter = 'alpha(opacity=' + alpha + ')';
			}else{
				clearInterval(e.timer);
				if(d == -1){e.style.display = 'none'}
			}
		},
		hide:function(id){
			e = document.getElementById(id);
			if (!e) return;
			if (e.style.display == 'hide') return;
			clearInterval(e.timer);
			e.timer = setInterval(function(){jfx.fade(id, -1)},delay);
		},
		httip:function(){
			jfx.hide('ttip');
		},
		sttip:function(e,v){
			jfx.tooltip(e,v,top,left);
		},
		scttip:function(e,v){
			jfx.tooltip(e,v,10,-100);
		},
		tooltip:function(e,v,t,l){
			var u = ie ? event.clientY + document.documentElement.scrollTop : e.pageY;
			var l = ie ? event.clientX + document.documentElement.scrollLeft : e.pageX;
			if(tt == null){
				tt = document.createElement('div');
				tt.setAttribute('id','ttip');
				document.body.appendChild(tt);
				tt.style.opacity = 0;
				tt.style.filter = 'alpha(opacity=0)';
			}
			tt.innerHTML = v;
			tt.style.top = (u + top) + 'px';
			tt.style.left = (l + left) + 'px';
			jfx.show('ttip');
		}
	};
}();


setfav = function(id) {
	var ret = '';
	var favs = store.get('fav_champs') == null ? '' : store.get('fav_champs');
	var tmpfavs = favs.split(',');
	var isInList = 0;
	if (favs != '') {
		for(var i = 0; i < tmpfavs.length; i++) {
			if (tmpfavs[i] == id) { isInList = 1; el('fav_'+id).src = 'img/star1_gray_32.png'; }
			if (tmpfavs[i] != id) ret += (ret == '' ? '' : ',')+tmpfavs[i];
		}
	}
	if (isInList == 0) { ret = (favs == '' ? '' : favs+',')+id; el('fav_'+id).src = 'img/star1_32.png'; }

	store.set('fav_champs', ret);
}

getfav = function() { return store.get('fav_champs'); }

showfavs = function() {
	var favs = store.get('fav_champs') == null ? '' : store.get('fav_champs');
	var tmpfavs = favs.split(',');
	var isInList = 0;
	if (favs != '') {
		for(var i = 0; i < tmpfavs.length; i++) {
			try {
				el('fav_'+tmpfavs[i]).src = 'img/star1_32.png';
			} catch(e) {}
		}
	}
}

compare_sm = function(id1, id2, type) {
	if (type == 0) {
		var tmp1 = id1.split('|');
		var tmp2 = id2.split('|');
		if (tmp1[0] == tmp2[0]) return true;
		if (tmp1.length > 1 && tmp2.length > 1) {
			for(var i = 1; i < tmp1.length; i++)
			{
				for(var j = 1; j < tmp1.length; j++)
					if (tmp1[i] == tmp2[j]) return true;
			}
		}
	} else {
		return (id1 == id2);
	}
	return false;
}

update_sm = function(id, type) {

	if (id == 1) { src = "equipe1"; dst = "equipe2"; } else { src = "equipe2"; dst = "equipe1"; }

	var selected = "";

	var s1 = document.getElementById(src);
	for(i=0; i < s1.options.length; i ++)
		if (s1.options[i].selected) selected = s1.options[i].value;

	var s2 = document.getElementById(dst);
	for(i=0; i < s2.options.length; i ++) {
		is_compatible = compare_sm(s2.options[i].value, selected, type);
		s2.options[i].style.display = is_compatible ? "none" : "block";
		if (is_compatible && s2.options[i].selected) s2.options[i].selected = false;
	}
}