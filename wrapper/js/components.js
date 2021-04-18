convertHtmlToText = function(inputText) {

	var returnText = "" + inputText;

	//-- remove BR tags and replace them with line break
	returnText=returnText.replace(/<br>/gi, "\n");
	returnText=returnText.replace(/<br\s\/>/gi, "\n");
	returnText=returnText.replace(/<br\/>/gi, "\n");

	//-- remove P and A tags but preserve what's inside of them
	returnText=returnText.replace(/<p.*>/gi, "\n");
	returnText=returnText.replace(/<a.*href="(.*?)".*>(.*?)<\/a>/gi, " $2 ($1)");

	//-- remove all inside SCRIPT and STYLE tags
	returnText=returnText.replace(/<script.*>[\w\W]{1,}(.*?)[\w\W]{1,}<\/script>/gi, "");
	returnText=returnText.replace(/<style.*>[\w\W]{1,}(.*?)[\w\W]{1,}<\/style>/gi, "");
	//-- remove all else
	returnText=returnText.replace(/<(?:.|\s)*?>/g, "");

	//-- get rid of more than 2 multiple line breaks:
	returnText=returnText.replace(/(?:(?:\r\n|\r|\n)\s*){2,}/gim, "\n\n");

	//-- get rid of more than 2 spaces:
	returnText = returnText.replace(/ +(?= )/g,'');

	//-- get rid of html-encoded characters:
	returnText=returnText.replace(/&nbsp;/gi," ");
	returnText=returnText.replace(/&amp;/gi,"&");
	returnText=returnText.replace(/&quot;/gi,'"');
	returnText=returnText.replace(/&lt;/gi,'<');
	returnText=returnText.replace(/&gt;/gi,'>');

	return returnText;
}

trim = function(str) { return str.replace(/^\s\s*/, '').replace(/\s\s*$/, ''); }

isInList = function(val, list) {
	if (!list || list == '') return false;
	var tmp = list.split(',');
	for(i=0; i < tmp.length; i++) if (trim(tmp[i]) == trim(val)) return true;
	return false;
}

var ooo = function() {
	var elt;

	return {

	init:function(args) {
		id = args.id||'';
		className = args.className||'';
		addClassName = args.addClassName||'';
		innerHTML = args.innerHTML||'';
		att = args.att||{};
		css = args.css||'';
		if (id == '') return;

		elt = document.getElementById(id)||id;
		try {
			if (className  != '') elt.className = className;
			if (addClassName  != '') elt.className += ' '+addClassName;
			if (innerHTML  != '') elt.innerHTML = innerHTML;
			elt.style.cssText = css;
			for(property in att) elt.setAttribute(property, ""+att[property]);
		}
		catch(e) { console.log("ooo erreur ..."); }
  	}

	};
}();

var mmm = function() {
	var id_msg = 0;

	return {

	infoMsg:function(args) { mmm.addMsg(args); },
	alertMsg:function(args) { args.c = 'alertmsg'; mmm.addMsg(args); },
	denyMsg:function(args) { args.c = 'denymsg'; mmm.addMsg(args); },
	checkMsg:function(args) { args.c = 'checkmsg'; mmm.addMsg(args); },

	addMsg:function(args) {
		msg = args.msg||'';
		c = args.c||'errmsg';
		var div = document.createElement("div");
		$o({ id: div, className: 'msgbox '+c, innerHTML: msg, att: { id: 'msg_'+id_msg }, css: 'display: block' });
		(function(id) { div.onclick = function() {
			mmm.delMsg(id);
		} })(id_msg);
		div.idm = id_msg;
		div.timerautodel = setTimeout(function() { mmm.delMsg(div.idm); }, 5000);
		div.timerdel = 0;

		msgb = document.getElementById('msgboxes');
		msgb.appendChild(div);
		div.className +=' fadeInLeft';

		id_msg += 1;
  	},

	delMsg:function(id) {
		var elt = document.getElementById('msg_'+id);
		elt.className = elt.className.replace('fadeInLeft', '') +' fadeOutLeft';
		msgb = document.getElementById('msgboxes');
		elt.timer = setTimeout(function() { mmm.rmMsg(elt); }, 1000);
	},

	rmMsg:function(elt) {
		msgb = document.getElementById('msgboxes');
		if (elt.timer != 0) clearInterval(elt.timer);
		msgb.removeChild(elt);
	}

	};
}();

$o = window.ooo.init;
$iMsg = window.mmm.infoMsg;
$aMsg = window.mmm.alertMsg;
$dMsg = window.mmm.denyMsg;
$cMsg = window.mmm.checkMsg;


var blackscreen = function() {
	var bs, scrolly;

	return {

	show:function() { bs.style.display='block'; scrolly = (document.documentElement && document.documentElement.scrollTop) || window.pageYOffset || self.pageYOffset || document.body.scrollTop; scroll(0, 0); document.body.style.overflow='hidden'; },
	hide:function() { bs.style.display='none'; document.body.style.overflow=''; scroll(0, scrolly) },

	create:function() {
		if (bs == null)
		{
			bs = document.createElement('div');
			bs.setAttribute('id','blackscreen');
			blackscreen.pushInBody(bs);
		}
		bs.className = 'blackscreen';
		blackscreen.show();
	},

	veryblackscreen:function() { bs.className = 'blackscreen veryblackscreen'; },
	fullblackscreen:function() { bs.className = 'blackscreen fullblackscreen'; },
	scoreblackscreen:function() { bs.className = 'blackscreen scoreblackscreen'; },

	pushInBody:function(elt) {
		try {
			document.body.insertBefore(elt, document.body.firstChild);
		} catch(e) {
			document.documentElement.insertBefore(elt, document.documentElement.firstChild);
		}
	}

	};
}();

var minicalc = function() {
	var mcdiv, name, c1, c2;

	return {

	setEnv:function(args) {
		name    = args.name||'';
		c1      = args.c1||'orange';
		c2      = args.c2||'orange';
	},

	change_colors:function() {
		document.getElementById('digits_'+name+'_opt_3').className = 'button black';
		document.getElementById('digits_'+name+'_opt_7').className = 'button black';
		document.getElementById('digits_'+name+'_opt_11').className = 'button white';
		document.getElementById('digits_'+name+'_opt_14').className = 'button white';
		document.getElementById('digits_'+name+'_opt_15').className = 'button blue';
	},

	create:function() {
		var elt = document.getElementById('minicalc_'+name);
		if (elt) return;

		var val = document.getElementById(name).innerHTML == '' ? '0' : document.getElementById(name).innerHTML;

		mcdiv = document.createElement("div");
		mcdiv.id = 'minicalc_'+name;
		mcdiv.className = 'minicalc';
		mcdiv.innerHTML = '<div id="screen_'+name+'" class="choices screen">'+val+'</div><div id="digits_'+name+'" class="digits noradius"></div>';
		mcdiv.style.display='none';
		blackscreen.pushInBody(mcdiv);
		choices.build({ name: 'digits_'+name, multiple: true, c1: c1, c2: c2, callback: 'minicalc.callback', values: [ {v: 7, l: '7'}, {v: 8, l: '8'}, {v: 9, l: '9'}, {v: 12, l: '-'}, {v: 4, l: '4'}, {v: 5, l: '5'}, {v: 6, l: '6'}, {v: 13, l: '+'}, {v: 1, l: '1'}, {v: 2, l: '2'}, {v: 3, l: '3'}, {v: 14, l: 'A'}, {v: 88, l: '0'}, {v: 10, l: '.'}, {v: 11, l: 'C'}, {v: 99, l: '='} ] });
		minicalc.change_colors();
	},

	callback:function(name, elt) {
		var v = choices.getSelection(name);
		var x = elt.value;
		var s = document.getElementById('screen_'+name.replace('digits_', '')).innerHTML;

		if (x > 0 && x < 10) document.getElementById('screen_'+name.replace('digits_', '')).innerHTML = s+''+x;
		if (x == 88 && s != '') document.getElementById('screen_'+name.replace('digits_', '')).innerHTML = s+'0';
		if (x == 10 && s == '') document.getElementById('screen_'+name.replace('digits_', '')).innerHTML = '0.';
		if (x == 10 && s != '' && s.indexOf('.') == -1) document.getElementById('screen_'+name.replace('digits_', '')).innerHTML = s+'.';
		if (x == 11 && s != '') document.getElementById('screen_'+name.replace('digits_', '')).innerHTML = s.substring(0, s.length-1);
		if (x == 12 || x == 13) s = s.replace('+', '').replace('-', '');
		if (x == 12) document.getElementById('screen_'+name.replace('digits_', '')).innerHTML = '-'+s;
		if (x == 13) document.getElementById('screen_'+name.replace('digits_', '')).innerHTML = '+'+s;

		minicalc.change_colors();

		if (x == 99) document.getElementById(name.replace('digits_', '')).innerHTML = s;
		if (x == 99 || x == 14) minicalc.close(name.replace('digits_', ''));
	},

	close:function(name) {
		elt = document.getElementById('minicalc_'+name);
		if (!elt) return;

		document.body.removeChild(elt);
		blackscreen.hide();
	},

	picker:function(args) {

		minicalc.setEnv(args);
		if (name == '') return;

		var elt = document.getElementById(name);
		if (!elt) return;

		elt.args = args;

		blackscreen.create();
		minicalc.create();
		mcdiv.style.display='block';
	}

	};
}();

var numbers = function() {
	var numbersdiv, name, start, end, delta, c1, c2, nb_cols;

	return {

	setEnv:function(args) {
		name    = args.name||'';
		c1      = args.c1||'orange';
		c2      = args.c2||'gray';
		start   = args.start||0;
		end     = args.end||999;
		delta   = args.delta||20;
		nb_cols = 5; // par rapport affichage
	},

	getValue:function(name) {
		return convertHtmlToText(document.getElementById(name).innerHTML);
	},

	change_digits:function(elt) {
		var tmp = elt.id.split('_');
		var local_name = tmp[1];
		var k = tmp[2];
		choices.removeChildren('digits_'+local_name);
		choices.build({ name: 'digits_'+local_name, c1: c1, c2: c2, callback: 'numbers.callback', values: numbers.gendigits(k*delta+start, end, delta, document.getElementById(local_name).innerHTML) });
	},

	gendigits:function(s, e, d, x) {
		var v = new Array();
		for(i=0; i < d; i++) if ((s+i) <= e) v[i] = { v: s+i, l: s+i+'', s: (x == (s+i) ? true : false) };

		return v;
	},

	genrange: function(n, k, s, e, d) {
		var str = '';
		var nb_items = (Math.floor(delta/nb_cols)-1)*2;
		for(i=(k*nb_items); i < ((k+1)*nb_items); i++) {
			s_ = i*d+s;
			e_ = ((i+1)*d)+s-1;
			if (e_ > e) e_ = e;
			if (s_ <= e) str += '<button id="range_'+n+'_'+i+'" class="button blue" onclick="numbers.change_digits(this);">'+s_+'-'+e_+'</button>';

		}
		k_prv = (k-1) > 0 ? k-1 : 0;
		k_nxt = ((k+1)*nb_items*d) > e ? k : k+1;
		str += '<div class="cmd">';
		str += '<button id="prv" class="button green" onclick="numbers.genrange(\''+n+'\','+k_prv+','+s+','+e+','+d+');"><span>&#171;</span></button>';
		str += '<button id="close" class="button green" onclick="numbers.close(\''+n+'\');"><span>Fermer</span></button>';
		str += '<button id="nxt" class="button green" onclick="numbers.genrange(\''+n+'\','+k_nxt+','+s+','+e+','+d+');"><span>&#187;</span></button>';
		str += '</div>';
		document.getElementById('range_'+n).innerHTML = str;
	},

	create:function() {
		var elt = document.getElementById('numbers_'+name);
		if (elt) return;

		var str = convertHtmlToText(document.getElementById(name).innerHTML);
		var val = str == '' ? '0' : str;

		numbersdiv = document.createElement("div");
		numbersdiv.id = 'numbers_'+name;
		numbersdiv.className = 'numbers noradius';
		numbersdiv.innerHTML = '<div id="range_'+name+'" class="choices range"></div><div id="digits_'+name+'" class="digits"></div>';
		numbersdiv.style.display='none';
		blackscreen.pushInBody(numbersdiv);
		choices.build({ name: 'digits_'+name, c1: c1, c2: c2, callback: 'numbers.callback', values: numbers.gendigits(Math.floor(val/delta)*delta, end, delta, val) });
		numbers.genrange(name, 0, start, end, delta);
	},

	callback:function(name) {
		var v = choices.getSelection(name);
		if (v != '') document.getElementById(name.replace('digits_', '')).innerHTML = '<span>'+v+'</span>';

		numbers.close(name.replace('digits_', ''));
	},

	close:function(name) {
		elt = document.getElementById('numbers_'+name);
		if (!elt) return;

		document.body.removeChild(elt);
		blackscreen.hide();
	},

	picker:function(args) {

		numbers.setEnv(args);
		if (name == '') return;
		if (name.indexOf('_') != -1) { alert("'_' non toléré !"); return; }

		var elt = document.getElementById(name);
		if (!elt) return;

		elt.args = args;

		blackscreen.create();
		numbers.create();
		numbersdiv.style.display='block';
	}

	};
}();

var calendar = function() {
	var caldiv, name, c1, c2, mn, dim, initd;

	return {

	setEnv:function(args) {
		name  = args.name||'';
		initd = args.initd||'';
		c1    = args.c1||'orange';
		c2    = args.c2||'blue';
		mn    = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Aoutt','Septembre','Octobre','Novembre','Décembre'];
		dim   = [31,0,31,30,31,30,31,31,30,31,30,31];
	},

	getValue:function(name) {
		return convertHtmlToText(document.getElementById(name).innerHTML);
	},

	getDate:function() {
		return choices.getSelection('caldays');
	},

	change_month:function(d, m, y, s) {
		var mmm = m + (s == 0 ? -1 : (s == 1 ? 1 : 0));
		var yyy = y + (s == -1 ? -1 : (s == 2 ? 1 : 0));
		if (mmm == 13) { mmm = 1;  yyy += 1; }
		if (mmm == 0)  { mmm = 12; yyy -= 1; }
		calendar.closePicker();
		calendar.picker({ name: name, initd: d+'/'+mmm+'/'+yyy });
	},

	gendays:function(d, m, y, dn) {

		var refd = 0; var refm = 0; var refy = 0;
		if (name != '') {
			var elt = document.getElementById(name);
			if (elt) {
				var tmp = convertHtmlToText(elt.innerHTML).split('/');
				refd = tmp[0]; refm = tmp[1]; refy = tmp[2];
			}
		}

		dim[1]=(((y%100!=0)&&(y%4==0))||(y%400==0))?29:28;

		var blank = (dn-1) == -1 ? 6 : (dn-1);
		var v = new Array();
		for(i=1; i <= blank; i++) v[i-1] = { v: i, l: '', s: false };
		for(i=(1+blank); i <= (dim[m-1]+blank); i++) v[i-1] = { v: ((i-blank) < 10 ? '0' : '')+(i-blank)+'/'+((m < 10 ? '0' : '')+parseInt(m))+'/'+y, l: (i-blank)+'', s: (parseInt(refd) == (i-blank) && parseInt(refm) == parseInt(m) && parseInt(refy) == parseInt(y) ? true : false) };

		return v;
	},

	formatdays:function() {
		buttons = document.getElementById('caldays').getElementsByTagName('button');
		for(var i=0; i < buttons.length; i++) {
			if (buttons[i].innerHTML == '<span></span>') {
				buttons[i].className = 'button white';
				buttons[i].onclick = function() { };
			}
		}
	},

	create:function(d, m, y, dn) {
		if (!(caldiv == null)) return;
		caldiv = document.createElement("div");
		caldiv.id = 'calendar_picker';
		caldiv.className = 'calendar_picker';
		var str = '<div id="cmd" class="choices"><button id="prv" class="button green" onclick="calendar.change_month('+d+','+m+','+y+', -1);"><span>&#171;&#171;</span></button><button id="prv" class="button green" onclick="calendar.change_month('+d+','+m+','+y+', 0);"><span>&#171;</span></button><button id="lib" class="button green"><span>'+mn[m-1]+' '+y+'</span></button><button id="nxt" class="button green" onclick="calendar.change_month('+d+','+m+','+y+', 1);"><span>&#187;</span></button><button id="nxt" class="button green" onclick="calendar.change_month('+d+','+m+','+y+', 2);"><span>&#187;&#187;</span></button><button id="close" class="button green" onclick="calendar.closePicker();" style="float: right;"><span>Fermer</span></button></div>';
		str += '<div id="calndays"></div>';
		str += '<div id="caldays"></div>';
		caldiv.innerHTML = str;
		caldiv.style.display='none';
		blackscreen.pushInBody(caldiv);
		choices.build({ name: 'calndays', c1: 'white', c2:  'white', values: [{ v: 0, l: 'Lun' }, { v: 0, l: 'Mar' }, { v: 0, l: 'Mer' }, { v: 0, l: 'Jeu' }, { v: 0, l: 'Ven' }, { v: 0, l: 'Sam' }, { v: 0, l: 'Dim' }] });
		choices.build({ name: 'caldays', c1: c1, c2: c2, callback: 'calendar.callback', values: calendar.gendays(d, m, y, dn) });
		calendar.formatdays();
	},

	callback:function(picker) {
		var elt = document.getElementById(name);
		elt.innerHTML = '<span>'+calendar.getDate()+'</span>';
		calendar.closePicker();
	},

	closePicker:function() {
		if (!caldiv) return;
		document.body.removeChild(caldiv);
		caldiv = null;
		blackscreen.hide();
	},

	picker:function(args) {

		var now = new Date();

		calendar.setEnv(args);
		if (name == '') return;
		var elt = document.getElementById(name);
		if (!elt) return;

		var str = convertHtmlToText(elt.innerHTML);

		var ddd = initd != '' ? initd : (str == '' ? now.getDate()+'/'+(now.getMonth()+1)+'/'+now.getFullYear() : str);
		var tmp = ddd.split('/');
		var d = tmp[0]; var m = tmp[1]; var y = tmp[2];
		now.setDate(1); now.setMonth(m-1); now.setFullYear(y);
		var dn = now.getDay();

		blackscreen.create();
		calendar.create(d, m, y, dn);

		caldiv.style.display='block';
	}

	};
}();

var clock = function() {
	var clockdiv, name, c1, c2;

	return {

	setEnv:function(args) {
		name   = args.name||'';
		c1     = args.c1||'blue';
		c2     = args.c2||'orange';
	},

	getValue:function(name) {
		return convertHtmlToText(document.getElementById(name).innerHTML);
	},

	getTime:function() {
		var h1 = choices.getSelection('hours1');
		var h2 = choices.getSelection('hours2');
		var m = choices.getSelection('minutes')*15;
		return ((h1 != '' ? (h1 < 10 ? '0' : '')+h1 : (h2 < 10 ? '0' : '')+h2)+':'+(m == 0 ? '00' : (m < 10 ? '0' : '')+m));
	},

	ampm:function(c) {
		document.getElementById('hours1').style.display = 'none';
		document.getElementById('hours2').style.display = 'none';
		document.getElementById(c == 'am' ? 'hours1' : 'hours2').style.display = 'block';
	},

	unselect:function(n) {
		var c = choices.getSelection('ampm');
		if (c == 'pm') choices.unSelectAll('hours1');
		if (c == 'am') choices.unSelectAll('hours2');
	},

	changeampm:function(n) {
		clock.ampm(choices.getSelection(n));
	},

	create:function(h, m) {
		if (!(clockdiv == null)) return;
		clockdiv = document.createElement("div");
		clockdiv.id = 'clock_picker';
		clockdiv.className = 'clock_picker noradius';
		var str = '<div id="ampm" class="choices"></div>';
		str += '<div id="hours1"></div>';
		str += '<div id="hours2"></div>';
		str += '<div id="minutes"></div>';
		str += '<div id="cmd"><button class="button green" onclick="clock.closePicker(\'clock_picker\');">Fermer</button><button class="button green" onclick="clock.callback(\'clock_picker\');">Valider</button></div>';
		clockdiv.innerHTML = '<span>'+str+'</span>';
		clockdiv.style.display='none';
		blackscreen.pushInBody(clockdiv);

		choices.build({ name: 'ampm', c1: 'orange', c2: 'blue', callback: 'clock.changeampm', values: [ { v: 'am', l: 'am', s: h < 12 ? true : false }, { v: 'pm', l: 'pm', s: h > 11 ? true : false } ] });

		var v1 = new Array(); var v2 = new Array();

		for(i=1; i <= 11; i++) v1[i-1] = { v: i, l: (i < 10 ? '0' : '')+i+'', s: h == i ? true : false };
		choices.build({ name: 'hours1', c1: 'orange', c2: 'black', callback: 'clock.unselect', values: [ { v: 0, l: '00'}, { v: 1, l: '01'}, { v: 2, l: '02'}, { v: 3, l: '03'}, { v: 4, l: '04'}, { v: 5, l: '05'}, { v: 6, l: '06'}, { v: 7, l: '07'}, { v: 8, l: '08'}, { v: 9, l: '09'}, { v: 10, l: '10'}, { v: 11, l: '11'} ] });

		for(i=12; i <= 23; i++) v2[i-12] = { v: i, l: i+'', s: h == i ? true : false };
		choices.build({ name: 'hours2', c1: 'orange', c2: 'black', callback: 'clock.unselect', values: v2 });

		choices.build({ name: 'minutes', c1: 'orange', c2: 'blue', values: [ { v: 0, l: '00', s: parseInt(m) == 0 ? true : false }, { v: 1, l: '15', s: parseInt(m) == 15 ? true : false}, { v: 2, l: '30', s: parseInt(m) == 30 ? true : false}, { v: 3, l: '45', s: parseInt(m) == 45 ? true : false} ] });
	},

	callback:function(picker) {
		var elt = document.getElementById(name);
		elt.innerHTML = '<span>'+clock.getTime()+'</span>';
		document.getElementById(picker).style.display='none';
		blackscreen.hide();
		clock.closePicker(picker);
	},

	closePicker:function(picker) {
		document.getElementById(picker).style.display='none';
		blackscreen.hide();
	},

	picker:function(args) {

		clock.setEnv(args);
		if (name == '') return;
		var elt = document.getElementById(name);
		if (!elt) return;

		var tmp = convertHtmlToText(elt.innerHTML).split(':');
		var h = tmp[0];
		var m = tmp[1];

		blackscreen.create();
		clock.create(h, m);

		clock.ampm(h < 12 ? 'am' : 'pm');

		clockdiv.style.display='block';
	}

	};
}();

var choices = function() {
	var name, multiple, removable, readonly, values, c1, c2, c3, min, max, singlepicking, multipicking, callback, clibelle;

	return {

	setEnv:function(args) {
		name   = args.name||'';
		values = args.values||[];
		c1     = args.c1||'blue';
		c2     = args.c2||'gray';
		c3     = args.c3||'green';
		multiple  = args.multiple||false;
		removable = args.removable||false;
		readonly = args.readonly||false;
		min = args.min||0;
		max = args.max||9999;
		singlepicking = args.singlepicking||false;
		multipicking = args.multipicking||false;
		callback = args.callback||'';
		closelibelle = args.clibelle||'Fermer';
		if (singlepicking) { min = 1; max = 1; }
		if (multipicking) { removable = true; }
	},

	build:function(args) {
		choices.setEnv(args);
		if (name == '' || values.length < 1) return;
		var container = document.getElementById(name);
		if (!container) return;
		container.className += ' choices';

		for(var i=0; i < values.length; i++)
		{
			var id  = name+'_opt_'+i;
			var elt = document.createElement("button");
			var val = values[i].v||i;
			elt.setAttribute('id', id);
			elt.setAttribute('name', name+'_name');
			elt.setAttribute('value', val);
			elt.innerText = '';
			var span = document.createElement("span");
			span.innerHTML = values[i].l||'';
			elt.appendChild(span);
			issel = values[i].s||false;
			elt.className = issel ? "button "+c1 : "button "+c2;
            if (values.length > 1) {
                if (removable && !issel) elt.style.display = 'none';
                if (!multipicking) (function(id) { elt.onclick =  function() { choices.click(id); } })(elt.id);
                if (!multipicking && removable && container.className.search('removable') <= 0) container.className += ' removable';
                if (singlepicking && container.className.search('singlepicking') <= 0) container.className += ' singlepicking';
            }
			container.appendChild(elt);
			container.args = args;
		}
	},

	click:function(id) {
		elt = document.getElementById(id);
		choices.setEnv(elt.parentNode.args);
		if (readonly) return;
		if (singlepicking)
		{
			if (elt.parentNode.id.search('_picker') > 0) {
				choices.closePicker(elt.parentNode.id);
			}
			else {
				choices.picker(elt.parentNode.id);
			}
		}
		else
		{
			if (multipicking)
			{
				elt.className = elt.className.search(c1) > 0 ? "button "+c2 : "button "+c1;
			}
			else if (multiple)
			{
				elt.className = elt.className.search(c1) > 0 ? "button "+c2 : "button "+c1;

				if (callback != '') {	// A faire dans les autres cas en fonction comportement souhaité (peut etre ... pas sur !)
					eval(callback)(name, elt);
				}
			}
			else
			{
				for(var i=0; i < elt.parentNode.getElementsByTagName('button').length; i++) elt.parentNode.getElementsByTagName('button')[i].className = "button "+c2;
				elt.className = "button "+c1;

				if (callback != '') {	// A faire dans les autres cas en fonction comportement souhaité (peut etre ... pas sur !)
					eval(callback)(name, elt);
				}
			}
		}
		if (removable && !singlepicking) elt.style.display = 'none';
	},

	removeChildren:function(name) {
		elt = document.getElementById(name);
		if (elt.hasChildNodes()) while (elt.childNodes.length >= 1)	elt.removeChild(elt.firstChild);
	},

	closePicker:function(picker) {
		document.getElementById(picker).style.display='none';
		blackscreen.hide();
	},

	closeIfEmptyPicker:function(picker) {
		var elt = document.getElementById(picker);
		choices.setEnv(elt.args);
		var nb = 0;
		var buttons = elt.getElementsByTagName('button');
		for(var i=0; i < buttons.length; i++) if (buttons[i].className.search(c1) > 0) nb++;
		if (nb == 0) choices.closePicker(picker);
	},

	picker:function(source) { choices.pickergen(source, null, null); },
	multipicker:function(source, target, selected) { choices.pickergen(source, target, selected); },

	pickergen:function(source, target, selected) {

		var dst;
		var src = document.getElementById(source);

		choices.setEnv(src.args);
		blackscreen.create();
		var basket = multipicking ? document.getElementById(target) : null;

		if (multipicking && !basket) return;
		if (multipicking) basket.className += " choices removable";

		if (document.getElementById(source+'_picker'))
		{
			dst = document.getElementById(source+'_picker');
			choices.removeChildren(source+'_picker');
			dst.style.display='block';
		} else {
			dst = document.createElement('div');
			dst.setAttribute('id', source+'_picker');
			dst.className = 'choices choices_picker removable';
			if (singlepicking) dst.className += ' singlepicking_picker';
			dst.style.zIndex=99999;
			blackscreen.pushInBody(dst);
		}

		choices.setEnv(src.args);
		buttons = src.getElementsByTagName('button');
		for(var i=0; i < buttons.length; i++) dst.appendChild(buttons[i].cloneNode(true));
		if (multipicking && basket.getElementsByTagName('button').length == 0) {
			basket.args = src.args;
			for(var i=0; i < buttons.length; i++) {
				clone = buttons[i].cloneNode(true);
				var issel = isInList(clone.value, selected);
				clone.id = clone.id.replace(name, target);
				clone.className = "button "+(issel ? c1 : c2);
				clone.style.display = issel ? "block" : "none";
				if (issel) {
					buttons[i].className = "button "+c2;
					buttons[i].style.display = "none";
				}
				(function(id1, id2) { clone.onclick = function() {
					var elt2 = document.getElementById(id1);
					elt2.className = 'button '+c2;
					elt2.style.display = 'none';
					var elt3 = document.getElementById(id2);
					elt3.className = 'button '+c1;
					elt3.style.display = 'inline';
				} })(clone.id, clone.id.replace(target, name));
				basket.appendChild(clone);
			}
		}

		dst.args = src.args;
		dst.name = source+'_picker';
		buttons = dst.getElementsByTagName('button');
		for(var i=0; i < buttons.length; i++)
		{
			if (!multipicking) {
				buttons[i].className = 'button '+(buttons[i].style.display == 'none' ? c1 : c2);
				buttons[i].style.display = buttons[i].style.display == 'none' ? 'inline' : 'none';
			}
			buttons[i].id = buttons[i].id.replace(source, dst.name);
			(function(id, id2, c1, c2) { buttons[i].onclick = function() {
					if (singlepicking) { var b=document.getElementById(id2).parentNode.getElementsByTagName('button'); for(var k=0; k<b.length; k++) { b[k].style.display='none'; b[k].className='button '+c2; } }
					choices.click(id);
					var elt = document.getElementById(multipicking ? id2.replace(name, target) : id2);
					elt.style.display='inline';
					elt.className='button '+c1;
					if (multipicking) document.getElementById(id2).style.display = "none";
					choices.closeIfEmptyPicker(document.getElementById(id).parentNode.id);

					if (callback != '') {	// A faire dans les autres cas en fonction comportement souhaité (peut etre ... pas sur !)
						eval(callback)(document.getElementById(id).parentNode.id.replace('_picker', ''), elt);
					}

			} })(buttons[i].id, buttons[i].id.replace('_picker', ''), c1, c2);
		}
		var card_menu = document.createElement("div");
		card_menu.className = "mdl-card__actions mdl-card--border";
		var card_close = document.createElement("a");
		card_close.className="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect";
		card_close.innerHTML = closelibelle;
		card_close.onclick = function() { choices.closePicker(source+'_picker'); };
		card_menu.appendChild(card_close);
		dst.appendChild(card_menu);
	},

	init:function(source, target, selected) {
		choices.multipicker(source, target, selected);
		choices.closePicker(source+'_picker');
	},

	getSelection:function(name) {
		ret='';
		container = document.getElementById(name);
		if (!container || !container.args) return ret;
		choices.setEnv(container.args);
		buttons = container.getElementsByTagName('button');
		for(var i=0; i < buttons.length; i++) if (buttons[i].className.search(c1) > 0) ret += (ret==''?'':',')+buttons[i].value;
		return ret;
	},

	selectAllGen:function(name, sens) {
		container = document.getElementById(name);
		choices.setEnv(container.args);
		buttons = container.getElementsByTagName('button');
		for(var i=0; i < buttons.length; i++) buttons[i].className = sens == 0 ? "button "+c2 : "button "+c1;;
	},

	selectAll:function(name) {
		choices.selectAllGen(name, 1);
	},

	unSelectAll:function(name) {
		choices.selectAllGen(name, 0);
	}

	};
}();


var bars = function() {
	var name, values, c1, c2, c3, c4;

	return {

	setEnv:function(args) {
		name   = args.name||'';
		values = args.values||[];
		c1     = args.c1||'red';
		c2     = args.c2||'orange';
		c3     = args.c3||'blue';
		c4     = args.c3||'green';
		psize  = args.psize||60;
		tsize  = args.tsize||200;
		rsize  = args.rsize||250;
		lsize  = psize+20+tsize+20+1;
		bsize  = rsize+lsize+20;
		msize  = args.msize||150;
	},

	build:function(args) {
		bars.setEnv(args);
		if (name == '' || values.length < 1) return;
		var container = document.getElementById(name);
		if (!container) return;
		container.className += ' bars';
		container.args = args;
		for(var i=0; i < values.length; i++)
		{
			var p = values[i].p;
			var c = p > 74 ? c4 : (p > 49 ? c3 : (p > 24 ? c2 : c1));

			var li = document.createElement("li");
			li.id = 'bar_'+i;
			li.style.width = bsize+'px';
			if (values[i].hidden&&values[i].hidden=='yes') li.style.display = 'none';

			var div = document.createElement("div");
			div.className = 'item '+(values[i].c||c);
			div.style.width = Math.ceil(lsize+(rsize * p / 100))+'px';

			var pourc = document.createElement("div");
			pourc.className = values[i].icon ? 'icon' : 'pourc';
			pourc.style.width = psize+'px';
			pourc.innerHTML = values[i].icon ? '<img src="'+values[i].icon+'" />' : (values[i].o ? values[i].o : p+'<span>%</span>');

			var lib = document.createElement("div");
			lib.className = 'lib';
			lib.style.width = tsize+'px';
			lib.innerHTML = values[i].l;

			if (values[i].info) {
				var info = document.createElement("div");
				info.className = 'lib2';
				info.style.width = (bsize-lsize-20-40-1)+'px';
				info.innerHTML = values[i].info;
			}

			if (values[i].plus) {
				var plus = document.createElement("div");
				plus.className = 'plus';
				plus.style.width = msize+'px';
				plus.innerHTML = values[i].plus;
			}

			div.appendChild(pourc);
			div.appendChild(lib);
			if (values[i].info) div.appendChild(info);
			if (values[i].plus) div.appendChild(plus);
			li.appendChild(div);
			container.appendChild(li);
		}
	},

	showAll:function(name) {
		container = document.getElementById(name);
		bars.setEnv(container.args);
		items = container.getElementsByTagName('li');
		for(var i=0; i < items.length; i++) items[i].style.display = 'block';
	},

	showOnlyFirst:function(name, nb) {
		container = document.getElementById(name);
		bars.setEnv(container.args);
		items = container.getElementsByTagName('li');
		for(var i=0; i < items.length; i++) items[i].style.display = i < nb ? 'block' : 'none';
	}

	};

}();

var rounds = function() {
	var name, values, c1, c2, c3, c4;

	return {

	setEnv:function(args) {
		name   = args.name||'';
		values = args.values||[];
		c1     = args.c1||'red';
		c2     = args.c2||'orange';
		c3     = args.c3||'blue';
		c4     = args.c3||'green';
	},

	build:function(args) {
		rounds.setEnv(args);
		if (name == '' || values.length < 1) return;
		var container = document.getElementById(name);
		if (!container) return;
		container.className += ' rounds';
		for(var i=0; i < values.length; i++)
		{
			var p = values[i].p;
			var c = p > 74 ? c4 : (p > 49 ? c3 : (p > 24 ? c2 : c1));

			var li = document.createElement("li");
			li.id = 'round_'+i;
			li.innerHTML = '<div class="item"><div class="out '+(values[i].c||c)+'"><div class="in '+(values[i].c||c)+'">'+values[i].p+'<span>%</span></div></div><div class="label">'+values[i].l+'</div></div>';
			container.appendChild(li);
		}
	}

	};

}();

var counter = function() {
	var name, id_match, c1, h, w, nbsets, l1, l2, resultat, timer, start_time, goals_time, stop_time, view;

	return {

	setEnv:function(args) {
		resultat = args.resultat||'0/0';
		id_match = args.id_match||0;
		name   = args.name||'';
		c1     = args.c1||'gray';
		h      = args.height||'auto';
		w      = args.width||'auto';
		nbsets = args.nbsets||1;
		view   = args.view||1;
		l1     = args.l1||'';
		l2     = args.l2||'';
		if (w == 'auto') w = 215;
		if (h == 'auto') h = 175;
	},

	setSizeBox:function(elt) {
		elt.parentNode.style.height = h+'px';
		elt.parentNode.style.width = w+'px';
		elt.style.lineHeight = h+'px';
		elt.style.fontSize = h+'px';

		var children = elt.parentNode.childNodes;
		for(var j=0; j < children.length; j++) {
			if (children[j].className == 'bottom' || children[j].className == 'top')
				children[j].style.height = Math.round(h/2)+'px';
			if (children[j].className == 'top')
				children[j].style.marginTop = '-'+h+'px';
		}
	},

	sync_set:function(id) {
		var str = document.getElementById(name+'_num_left_'+id).innerHTML+'/'+document.getElementById(name+'_num_right_'+id).innerHTML;
		document.getElementById(name+'_mini_'+id).innerHTML = str;
	},

	delComment:function(e) {
		var elt;
		if (!confirm("Etes-vous sur de vouloir supprimer ce commentaire ?")) return;
		if (!e) var e = window.event;
		if (e.target) elt = e.target;
		else if (e.srcElement) elt = e.srcElement;
		if (elt.nodeType == 3) // defeat Safari bug
		elt = elt.parentNode;

		var tbody = el(name+'_tabcomm').getElementsByTagName('TBODY').item(0);
		tbody.removeChild(elt.parentNode.parentNode.parentNode);
	},

	insertCommentElt:function(time, type, comment) {
		var tr = document.createElement("tr");
		var udt = '<td class="edit"><a onclick="alert(\'Pas encore implémenté\');"><img src="img/pencil_16.png" /></a></td><td class="del"><a onclick="counter.delComment(event);"><img src="img/delete_16.png" /></a></td>';
		tr.innerHTML = '<td class="time">'+time+'</td><td class="icon '+type+'"></td><td class="text">'+comment+'</td>'+(view == 2 ? udt : '');
		var tbody = el(name+'_tabcomm').getElementsByTagName('TBODY').item(0);
		tbody.insertBefore(tr, tbody.firstChild);
	},

	insertComment:function(type, comment) {
		var t = counter.getCounter().split(':');
		var h = parseInt(t[0], 10);
		var m = parseInt(t[1], 10);
		var s = parseInt(t[2], 10);
		var tr = document.createElement("tr");
		counter.insertCommentElt((h==0 && m==0) ? s+'\'\'' : (h*60+m)+'\'', 'icon_'+type, comment.charAt(0).toUpperCase() + comment.slice(1));
	},

	log:function() {
		var str = document.getElementById('comm_input').value;
		if (str.length > 0) {
			counter.insertComment(choices.getSelection('myicons'), document.getElementById('comm_input').value);
			document.getElementById('comm_input').value = '';
			document.getElementById('comm_input').focus();
		}
	},

	buildNumber:function(id, number, c) {
		var div_n = document.createElement("div");
		div_n.className = 'number';
		div_n.id = name+'_num_'+c+'_'+id;
		div_n.innerHTML = number;

		var div_t = document.createElement("div");
		div_t.className = 'top';
		if (view == 2) {
			(function(elt) { div_t.onclick = function() {
				counter.play();
				elt.innerHTML=parseInt(elt.innerHTML)+1;
				counter.setGoalsTime(id, '+1', c);
				counter.sync_set(id);
				counter.insertComment(3, 'But de '+(c == 'left' ? l1 : l2));
			} })(div_n);
		}

		var div_b = document.createElement("div");
		div_b.className = 'bottom';
		if (view == 2) {
			(function(elt) { div_b.onclick = function() { if (parseInt(elt.innerHTML) > 0) {
				counter.play();
				elt.innerHTML=parseInt(elt.innerHTML)-1;
				counter.setGoalsTime(id, '-1', c);
			}} })(div_n);
		}

		var div = document.createElement("div");
		div.className = 'button '+c1;

		div.appendChild(div_n);
		div.appendChild(div_t);
		div.appendChild(div_b);

		return div;
	},

	showSet:function(id) {
		var div = document.getElementById(name+'_mini_'+id);
		div.className = 'button orange';
		for(var i=0; i < nbsets; i++) {
			el(name+'_num_left_'+i).parentNode.style.display = i == id ? 'inline-block' : 'none';
			el(name+'_num_right_'+i).parentNode.style.display = i == id ? 'inline-block' : 'none';
			el(name+'_mini_'+i).className = (i == id) ? 'button orange' : 'button '+c1;
		}
		el(name+'_labelset').innerHTML = id == 0 ? "1er set" : (id+1)+"iéme set";
	},

	buildMiniBox:function(id, n1, n2) {
		var div = document.createElement("div");
		div.className = 'button '+c1;
		div.id = name+'_mini_'+id;
		div.innerHTML = n1+'/'+n2;
		div.onclick = function() { counter.showSet(id); }
		return div;
	},

	create:function(args) {
		timer = 0;
		counter.setStartTime();
		goals_time = '';
		counter.setEnv(args);
		if (name == '') return;

		var container = document.createElement("div");
		container.args = args;
		container.id = name+'_counter';
		container.className = 'counter nbsets'+nbsets;

		var lb1 = document.createElement("div");
		lb1.className = 'label';
		lb1.innerHTML = l1;

		var lb2 = document.createElement("div");
		lb2.className = 'label right';
		lb2.innerHTML = l2;

		var lb3 = document.createElement("div");
		lb3.id = name+'_labelset';
		lb3.className = 'labelset';

		var teams = document.createElement("div");
		teams.className = 'teams';
		teams.appendChild(lb1);
		teams.appendChild(lb2);

		var comm = document.createElement("div");
		comm.className = 'comment_form';
		comm.innerHTML = '<input type="text" id="comm_input" placeholder="Ajouter un commentaire ..." required /><button onclick="counter.log();">Log</button>';

		var comm1 = document.createElement("div");
		comm1.id = 'myicons';

		var comm2 = document.createElement("div");
		comm2.className = 'comments tableContainer';
		comm2.innerHTML = '<table id="'+name+'_tabcomm" cellpadding="0" cellspacing="0"><tbody></tbody></table>';

		var b1 = document.createElement("div");
		b1.className = 'box';
		b1.setAttribute('id', name+'_counter_box1');

		var setsdiv = document.createElement("div");
		setsdiv.className = 'setsdiv';

		var addset = document.createElement("button");
		addset.className='button blue addset';
		addset.innerHTML='<span>Set</span>';
		addset.onclick = function() { counter.addSet(); };

		var rmset = document.createElement("button");
		rmset.className='button blue rmset';
		rmset.innerHTML='<span>Set</span>';
		rmset.onclick = function() { counter.removeSet(); };

		if (view == 2) setsdiv.appendChild(rmset);

		var miniboxes = document.createElement("span");
		miniboxes.id = name+'_miniboxes';
		miniboxes.className = 'miniboxes grouped';

		var sets = resultat.split(',');
		for(var i=0; i < nbsets; i++) {
			var s = sets[i]||'0/0';
			var score = s.split('/');
			b1.appendChild(counter.buildNumber(i, score[0]||0, 'left'));
			b1.appendChild(counter.buildNumber(i, score[1]||0, 'right'));
			miniboxes.appendChild(counter.buildMiniBox(i, score[0]||0, score[1]||0));
		}

		setsdiv.appendChild(miniboxes);

		var boxes = document.createElement("div");
		boxes.className = 'boxes';
		boxes.appendChild(b1);

		if (view == 2) setsdiv.appendChild(addset);

		var cmds = document.createElement("div");
		cmds.className = 'cmds grouped';

		var close = document.createElement("button");
		close.className='button '+(view == 1 ? 'green' : 'gray');
		close.innerHTML= (view == 1 ? 'Fermer' : 'Annuler');
		close.onclick = function() { counter.closeScore(); };
		cmds.appendChild(close);

		var valid = document.createElement("button");
		valid.className='button green';
		valid.innerHTML='Valider';
		valid.onclick = function() { counter.validScore(); };
		if (view == 2) cmds.appendChild(valid);

		var starter = document.createElement("div");
		starter.className='starter';
		starter.innerHTML='<img src="img/stop2_48.png" id="starter_stop" onclick="counter.stop();" /><div class="num"><span id="starter_h">00</span>HEURES</div><div class="num"><span id="starter_m">00</span>MINUTES</div><div class="num"><span id="starter_s">00</span>SECONDES</div><img id="starter_play" src="img/play_48.png" onclick="counter.play();" /><img id="starter_pause" src="img/pause_48.png" onclick="counter.pause();" />';

		if (view == 2) container.appendChild(starter);
		container.appendChild(teams);
		container.appendChild(lb3);
		container.appendChild(boxes);
		container.appendChild(setsdiv);
		if (view == 2) container.appendChild(comm);
		if (view == 2) container.appendChild(comm1);
		container.appendChild(comm2);
		container.appendChild(cmds);

		blackscreen.create();
		blackscreen.scoreblackscreen();
		blackscreen.pushInBody(container);
		if (view == 2) hide('starter_pause');

		choices.build({ name: 'myicons', c1: 'black', c2: 'gray', values: [ { v: 1, l: '<img src="img/talk.png" />', s: true }, { v: 2, l: '<img src="img/whistle.png" />' }, { v: 3, l: '<img src="img/ballon.png" />' }, { v: 4, l: '<img src="img/corner.png" />' }, { v: 5, l: '<img src="img/flag.png" />' }, { v: 6, l: '<img src="img/exchange.png" />' }, { v: 7, l: '<img src="img/yellow_card.png" />' }, { v: 8, l: '<img src="img/red_card.png" />' }, { v: 9, l: '<img src="img/cards.png" />' } ] });
		counter.setJournal();
		counter.resizeBoxes(0);
	},

	play:function() {
		if (timer != 0) return;
		hide('starter_play');
		show('starter_pause');
		timer = setTimeout("counter.count()", 1000);
		counter.setStartTime();
	},

	initChrono:function(chrono) {
		var s = 0;
		var m = 0;
		var h = 0;
		if (chrono.indexOf('\'\'') > 0)
			s = chrono.replace('\'\'', '');
		else {
			h = Math.floor(parseInt(chrono.replace('\'', ''), 10) / 60);
			m = parseInt(chrono.replace('\'', ''), 10) - (h * 60);
		}
		el('starter_s').innerHTML = (s < 10 ? '0' : '') + s;
		el('starter_m').innerHTML = (m < 10 ? '0' : '') + m;
		el('starter_h').innerHTML = (h < 10 ? '0' : '') + h;
	},

	setStartTime:function() {
		var ladate=new Date()
		start_time = ladate.getHours()+":"+ladate.getMinutes()+":"+ladate.getSeconds();
	},

	setStopTime:function() {
		var ladate=new Date()
		stop_time = ladate.getHours()+":"+ladate.getMinutes()+":"+ladate.getSeconds();
	},

	pause:function() {
		show('starter_play');
		hide('starter_pause');
		if (timer != 0) clearTimeout(timer);
		timer = 0;
	},

	stop:function() {
		show('starter_play');
		hide('starter_pause');
		if (timer != 0) clearTimeout(timer);
		timer = 0;
		counter.initChrono("0''");
		counter.setStopTime();
	},

	count:function() {
		var s = parseInt(document.getElementById('starter_s').innerHTML, 10)+1;
		var m = parseInt(document.getElementById('starter_m').innerHTML, 10)+(s == 60 ? 1 : 0);
		var h = parseInt(document.getElementById('starter_h').innerHTML, 10)+(m == 60 ? 1 : 0);
		s = s % 60;
		m = m % 60;
		h = h % 24;
		document.getElementById('starter_s').innerHTML = (s < 10 ? '0' : '')+s.toString();
		document.getElementById('starter_m').innerHTML = (m < 10 ? '0' : '')+m.toString();
		document.getElementById('starter_h').innerHTML = (h < 10 ? '0' : '')+h.toString();
		timer = setTimeout("counter.count()", 1000);
	},

	getCounter:function() {
		return (document.getElementById('starter_h').innerHTML+':'+document.getElementById('starter_m').innerHTML+':'+document.getElementById('starter_s').innerHTML);
	},

	setGoalsTime:function(set, val, team) {
		goals_time += (goals_time == '' ? '' : ',')+counter.getCounter()+'|'+set+'|'+val+'|'+team;
	},

	resizeBoxes:function(id) {
		for(var i=0; i < nbsets; i++) {
			counter.setSizeBox(el(name+'_num_left_'+i));
			counter.setSizeBox(el(name+'_num_right_'+i));
		}

//		el(name+'_labelset').style.display = nbsets == 1 ? 'none' : 'block';
		el(name+'_miniboxes').style.display = nbsets == 1 ? 'none' : 'inline-block';

		var anyDisplay = false;
		for(var i=0; i < nbsets; i++) {
			if (el(name+'_num_left_'+i).parentNode.style.display == 'inline-block') anyDisplay = true;
		}
		if (!anyDisplay) {
			el(name+'_num_left_0').parentNode.style.display = 'inline-block';
			el(name+'_num_right_0').parentNode.style.display = 'inline-block';
		}

		counter.showSet(id);
	},

	removeSet:function() {
		container = document.getElementById(name+'_counter');
		counter.setEnv(container.args);
		if (nbsets == 1) return;
		container.args.nbsets = parseInt(nbsets)-1;
		counter.setEnv(container.args);
		el(name+'_counter').className = 'counter nbsets'+nbsets
		el(name+'_counter_box1').removeChild(el(name+'_counter_box1').lastChild);
		el(name+'_counter_box1').removeChild(el(name+'_counter_box1').lastChild);
		el(name+'_miniboxes').removeChild(el(name+'_miniboxes').lastChild);
		counter.resizeBoxes(nbsets-1);
	},

	addSet:function() {
		container = document.getElementById(name+'_counter');
		counter.setEnv(container.args);
		if (nbsets == 5) return;
		container.args.nbsets = parseInt(nbsets)+1;
		counter.setEnv(container.args);
		el(name+'_counter').className = 'counter nbsets'+nbsets
		var child1 = counter.buildNumber(nbsets-1, 0, 'left');
		child1.style.display = 'none';
		el(name+'_counter_box1').appendChild(child1);
		var child2 = counter.buildNumber(nbsets-1, 0, 'right');
		child2.style.display = 'none';
		el(name+'_counter_box1').appendChild(child2);
		el(name+'_miniboxes').appendChild(counter.buildMiniBox(nbsets-1, 0, 0));
		counter.resizeBoxes(nbsets-1);
	},

	closeScore:function() {
		document.getElementById(name+'_counter').parentNode.removeChild(document.getElementById(name+'_counter'));
		blackscreen.hide();
	},

	validScore:function() {
		counter.setStopTime();
		container = document.getElementById(name+'_counter');
		counter.setEnv(container.args);
		var attrs = '';
		attrs += 'idm='+id_match;
		attrs += '&resultat='+counter.getScore(name);
		attrs += '&nbset='+nbsets;
		attrs += '&start_time='+start_time;
		attrs += '&stop_time='+stop_time;
		attrs += '&goals_time='+goals_time;
		attrs += '&journal='+counter.getJournal(name);
		go({action: 'matches', id:'main', url:'edit_matches_live_do.php?'+attrs});
		counter.closeScore();
	},

	getJournal:function(name) {
		var ret  = '';
		var tr = el(name+'_tabcomm').getElementsByTagName('TR');
		for(var j=0; j < tr.length; j++) {
			var l = '';
			var td = tr[j].getElementsByTagName('TD');
			for(var k=0; k < td.length; k++) {
				if (k < 3) l += (l == '' ? '' : ';')+(k == 1 ? td[k].className.replace('icon ', '') : td[k].innerHTML);
			}
			ret += (ret == '' ? '' : '|')+l;
		}

		return ret;
	},

	setJournal:function() {
		jx.load('json_getjournal.php?id_match='+id_match, function(data){ var json = eval('(' + data + ')'); journal = json.journal; for(var n=(journal.length-1); n >= 0; n--) { var tmp = journal[n].item.split(';'); counter.insertCommentElt(tmp[0], tmp[1], tmp[2]); if (view == 2 && n == 0 && tmp.length > 0) counter.initChrono(tmp[0]); } },'text','post');
	},

	getScore:function(name) {
		ret='';
		container = document.getElementById(name+'_counter');
		counter.setEnv(container.args);
		for(var i=0; i < nbsets; i++)
			ret += (ret == '' ? '' : ',')+document.getElementById(name+'_num_left_'+i).innerHTML+"/"+document.getElementById(name+'_num_right_'+i).innerHTML;
		return ret;
	}

	};

}();


var o = {
	init: function(args){
		this.diagram(args);
	},
	random: function(l, u){
		return Math.floor((Math.random()*(u-l+1))+l);
	},
	drawMarks: function (r, size, rad, max) {
		var out = r.set(),
			hash = document.location.hash,
			marksAttr = {fill: hash || "#999", stroke: "none"};

		for (var value = 0; value < max; value++) {
			var alpha = 360 / max * value, a = (90 - alpha) * Math.PI / 180;
			out.push(r.circle((size / 2) + rad * Math.cos(a), (size / 2) - rad * Math.sin(a), 1.5).attr(marksAttr));
		}
		return out;
	},
	drawItem: function(r, sl, elt, rad, rs, opt) {
		var bg = elt.c.bg||elt.c;
		var fg = elt.c.fg||"#fff";
		var op = elt.c.op||1.0;
		var lbl = elt.t.lbl||elt.t;
		var z = r.path().attr({ arc: [elt.v, bg, rad, rs], 'stroke-width': opt.arc_weight, 'opacity': op });
		if (elt.t != "") {

			(function(elt) { z.mouseover(function(){
				this.animate({ 'stroke-width': opt.arc_weight_zoom, opacity: opt.arc_opacity_zoom }, 1000, 'elastic');
				if(Raphael.type != 'VML') //solves IE problem
				this.toFront();
				opt.title.stop().animate({ opacity: 0 }, opt.speed, '>', function(){
// On affiche que le %
//					var skill = elt.t.skl||(elt.t + '\n' + elt.v + '%');
					var skill = elt.t.skl||(elt.v + '%');
					this.attr({ text: skill }).animate({ opacity: 1 }, opt.speed, '<');
				});
			})})(elt);

			(function(elt) { z.mouseout(function(){
				var op2 = elt.c.op||1.0;
				this.stop().animate({ 'stroke-width': opt.arc_weight, opacity: op2 }, opt.speed*4, 'elastic');
				opt.title.stop().animate({ opacity: 0 }, opt.speed, '>', function(){
					opt.title.attr({ text: opt.defaultText }).animate({ opacity: op2 }, opt.speed, '<');
				});
			})})(elt);

			var li = document.createElement('li');
			li.style.background = bg;
			li.style.color = fg;
			var txt = document.createTextNode(lbl);
			li.appendChild(txt);

			(function(z, elt) { li.onmouseover = function(){
				z.animate({ 'stroke-width': opt.arc_weight_zoom, opacity: opt.arc_opacity_zoom }, 1000, 'elastic');
				if(Raphael.type != 'VML') //solves IE problem
				z.toFront();
				opt.title.stop().animate({ opacity: 0 }, opt.speed, '>', function(){
// On affiche que le %
//					var skill = elt.t.skl||(elt.t + '\n' + elt.v + '%');
					var skill = elt.t.skl||(elt.v + '%');
					this.attr({ text: skill }).animate({ opacity: 1 }, opt.speed, '<');
				});
			}})(z, elt);

			(function(z, elt) { li.onmouseout = function(){
				var op2 = elt.c.op||1.0;
				z.stop().animate({ 'stroke-width': opt.arc_weight, opacity: op2 }, opt.speed*4, 'elastic');
				opt.title.stop().animate({ opacity: 0 }, opt.speed, '>', function(){
					opt.title.attr({ text: opt.defaultText }).animate({ opacity: op2 }, opt.speed, '<');
				});
			}})(z, elt);

			sl.appendChild(li);

		}
	},
	diagram: function(args){
		var size = args.size||500,
			r = Raphael(args.name, size, size),
			rad = args.rad||73,
			defaultText = args.defaultText||'Skills',
			speed = args.speed||250,
			random_start = args.random_start||true,
			cc_color = args.cc_color||'#193340',
			cc_size = args.cc_size||'87',
			arc_weight = args.arc_weight||28,
			arc_weight_zoom = args.arc_weight_zoom||50,
			arc_opacity_zoom = args.arc_opacity_zoom||.75;

		r.circle(size/2, size/2, cc_size).attr({ stroke: 'none', fill: cc_color });

		var title = r.text(size/2, size/2, defaultText).attr({
			font: '20px Arial',
			fill: '#fff'
		}).toFront();

		r.customAttributes.arc = function(value, color, rad, rs){
			var v = 3.6*value,
				alpha = v == 360 ? 359.99 : v,
				a = (rs-alpha) * Math.PI/180,
				b = rs * Math.PI/180,
				sx = (size/2) + rad * Math.cos(b),
				sy = (size/2) - rad * Math.sin(b),
				x = (size/2) + rad * Math.cos(a),
				y = (size/2) - rad * Math.sin(a),
				path = [['M', sx, sy], ['A', rad, rad, 0, +(alpha > 180), 1, x, y]];
			return { path: path, stroke: color }
		}

		var sl = document.getElementById(args.skills_list);
		for(var i=0; i < args.data.length; i++) {
			var rs = args.data[i].rs||(random_start ? o.random(91, 240) : 90);
			var opt = { defaultText: defaultText, title: title, arc_weight: arc_weight, arc_weight_zoom: arc_weight_zoom, arc_opacity_zoom: arc_opacity_zoom, speed: speed };
			// On dessine les petits points
			o.drawMarks(r, size, rad + 30 * (i + 1), 100);
			// Dessin de l'arc
			if (args.data[i].v instanceof Array) {
				for(var j=0; j < args.data[i].v.length; j++) {
					var tmp = { v: args.data[i].v[j], t: args.data[i].t[j], c: args.data[i].c[j] };
					o.drawItem(r, sl, tmp, rad + 30 * (i + 1), rs, opt);
					rs -= (3.6*args.data[i].v[j]);
				}
			}
			else
				o.drawItem(r, sl, args.data[i], rad + 30 * (i + 1), rs, opt);
		}
	}
}


Raphael.fn.drawGrid = function (x, y, w, h, wv, hv, c) {
    var path = ["M", Math.round(x) + .5, Math.round(y) + .5, "L", Math.round(x + w) + .5, Math.round(y) + .5, Math.round(x + w) + .5, Math.round(y + h) + .5, Math.round(x) + .5, Math.round(y + h) + .5, Math.round(x) + .5, Math.round(y) + .5],
        rowHeight = h / hv,
        columnWidth = w / wv;
    for (var i = 1; i < hv; i++) {
        path = path.concat(["M", Math.round(x) + .5, Math.round(y + i * rowHeight) + .5, "H", Math.round(x + w) + .5]);
    }
    for (i = 0; i <= wv; i++) {
        path = path.concat(["M", Math.round(x + i * columnWidth) + .5, Math.round(y) + .5, "V", Math.round(y + h) + .5]);
    }
    return this.path(path.join(",")).attr({stroke: c});
}

Raphael.fn.drawAxes = function (x, y, w, h, wv, hv, c) {
    var path = ["M", Math.round(x) + .5, Math.round(y) + .5, "L", Math.round(x) + .5, Math.round(y + h) + .5, Math.round(x + w) + .5, Math.round(y + h) + .5];
    return this.path(path.join(",")).attr({stroke: c});
}

Raphael.fn.drawYAxeLabels = function (x, y, w, h, wv, hv, attr, labels) {
	for(var i=0; i < labels.length; i++) this.text(x, y - ((h / hv) * i), labels[i]).attr(attr).attr( {'text-anchor':'end'} ).toBack();
}

Raphael.fn.drawAVG = function (x, y, w, h, wv, hv, c) {
    var path = ["M", Math.round(x) + .5, Math.round(y) + .5, "L", Math.round(x + w) + .5, Math.round(y) + .5];
    return this.path(path.join(",")).attr({stroke: c});
}

drawAnalytics = function (args) {

    function getAnchors(p1x, p1y, p2x, p2y, p3x, p3y) {
        var l1 = (p2x - p1x) / 2,
            l2 = (p3x - p2x) / 2,
            a = Math.atan((p2x - p1x) / Math.abs(p2y - p1y)),
            b = Math.atan((p3x - p2x) / Math.abs(p2y - p3y));
        a = p1y < p2y ? Math.PI - a : a;
        b = p3y < p2y ? Math.PI - b : b;
        var alpha = Math.PI / 2 - ((a + b) % (Math.PI * 2)) / 2,
            dx1 = l1 * Math.sin(alpha + a),
            dy1 = l1 * Math.cos(alpha + a),
            dx2 = l2 * Math.sin(alpha + b),
            dy2 = l2 * Math.cos(alpha + b);
        return {
            x1: p2x - dx1,
            y1: p2y + dy1,
            x2: p2x + dx2,
            y2: p2y + dy2
        };
    }

    // Draw
    var labels = args.labels||new Array(),
    	yaxe_labels = args.yaxe_labels||["", "20%", "40%", "60%", "80%", "100%"],
    	data = args.data||new Array(),
    	overmax = args.overmax||-1,
    	width = args.width||800,
        height = args.height||250,
        leftgutter = args.leftgutter||50,
        rightgutter = args.rightgutter||10,
        bottomgutter = args.bottomgutter||50,
        topgutter = args.topgutter||20,
        xrange = args.xrange||12,
        yrange = args.yrange||5,
        colorhue = .6 || Math.random(),
        color = "#00AEEF",
        txt = {font: '12px Helvetica, Arial', fill: "#fff"},
        txt1 = {font: '10px Helvetica, Arial', fill: color},
        txt2 = {font: '12px Helvetica, Arial', fill: "#333"},
        lblext = args.lblext||"% matchs gagnés",
        tips_labels = args.tips_labels||new Array(),
        X = (width - leftgutter - rightgutter) / (labels.length-1),
        max = Math.max(Math.max.apply(Math, data), overmax),
        Y = (height - bottomgutter - topgutter) / max,
        avg = args.avg||50;

    var r = Raphael(args.name, width, height);

    var modulo = Math.floor(labels.length / xrange);

    var graph_w = width - leftgutter - rightgutter - 2,
    	graph_h = height - topgutter - bottomgutter;

    r.drawGrid(leftgutter + .5, topgutter + .5, graph_w, graph_h, xrange, yrange, "#ccc");
    r.drawAxes(leftgutter + .5, topgutter + .5, graph_w, graph_h, xrange, yrange, "#666");
    if (labels.length > 0) r.drawAVG(leftgutter, Math.round(height - bottomgutter - Y * avg) + .5, graph_w, graph_h, xrange, yrange, "#EC008C");

    var path = r.path().attr({stroke: color, "stroke-width": 4, "stroke-linejoin": "round"}),
        bgp = r.path().attr({stroke: "none", opacity: .3, fill: color}),
        label = r.set(),
        lx = 0, ly = 0,
        is_label_visible = false,
        leave_timer,
        blanket = r.set();

    label.push(r.text(60, 12, "100 %").attr(txt));
    label.push(r.text(60, 27, "22 September 2008").attr(txt1));
    label.hide();

	r.drawYAxeLabels(leftgutter - 5, height - bottomgutter, graph_w, graph_h, xrange, yrange, txt2, yaxe_labels);

    var frame = r.popup(100, 100, label, "right").attr({fill: "#000", stroke: "#666", "stroke-width": 2, "fill-opacity": .7}).hide();

    var p, bgpp;
    for (var i = 0, ii = labels.length; i < ii; i++) {
        var y = Math.round(height - bottomgutter - Y * data[i]),
            x = Math.round(leftgutter + X * i + 1);

		// Cas particulier qd une seule donnee a afficher
		if (labels.length == 1) x = Math.round(leftgutter + 1);

		// Si trop de donnees on affiche pas tous les libelles
        if ((i % modulo) == 0 || labels.length <= 10) t = r.text(x, height - 40, labels[i]).attr(txt2).rotate(-45).attr( {'text-anchor':'end'} ).toBack();

        if (!i) {
            p = ["M", x, y, "C", x, y];
            bgpp = ["M", leftgutter, height - bottomgutter, "L", x, y, "C", x, y];
        }

        if (i && (i < ii - 1)) {
            var Y0 = Math.round(height - bottomgutter - Y * data[i - 1]),
                X0 = Math.round(leftgutter + X * (i - 1) + 1),
                Y2 = Math.round(height - bottomgutter - Y * data[i + 1]),
                X2 = Math.round(leftgutter + X * (i + 1) + 1);
            var a = getAnchors(X0, Y0, x, y, X2, Y2);
            p = p.concat([a.x1, a.y1, x, y, a.x2, a.y2]);
            bgpp = bgpp.concat([a.x1, a.y1, x, y, a.x2, a.y2]);
        }

        var dot = r.circle(x, y, 4).attr({fill: "#333", stroke: color, "stroke-width": 2});
        blanket.push(r.rect(leftgutter + X * i, 0, X, height - bottomgutter).attr({stroke: "none", fill: "#fff", opacity: 0}));
        var rect = blanket[blanket.length - 1];
        (function (x, y, data, lbl, dot) {
            var timer, i = 0;
            rect.hover(function () {
                clearTimeout(leave_timer);
                var side = "right";
                if (x + frame.getBBox().width > width) {
                    side = "left";
                }
                var ppp = r.popup(x, y, label, side, 1),
                    anim = Raphael.animation({
                        path: ppp.path,
                        transform: ["t", ppp.dx, ppp.dy]
                    }, 200 * is_label_visible);
                lx = label[0].transform()[0][1] + ppp.dx;
                ly = label[0].transform()[0][2] + ppp.dy;
                frame.show().stop().animate(anim);
                label[0].attr({text: data + lblext}).show().stop().animateWith(frame, anim, {transform: ["t", lx, ly]}, 200 * is_label_visible);
                label[1].attr({text: lbl}).show().stop().animateWith(frame, anim, {transform: ["t", lx, ly]}, 200 * is_label_visible);
                dot.attr("r", 6);
                is_label_visible = true;
            }, function () {
                dot.attr("r", 4);
                leave_timer = setTimeout(function () {
                    frame.hide();
                    label[0].hide();
                    label[1].hide();
                    is_label_visible = false;
                }, 1);
            });
        })(x, y, tips_labels.length > 0 && tips_labels[i] ? tips_labels[i] : data[i], labels[i], dot);
    }
    if (labels.length > 0) {
		p = p.concat([x, y, x, y]);
		bgpp = bgpp.concat([x, y, x, y, "L", x, height - bottomgutter, "z"]);
		path.attr({path: p});
		bgp.attr({path: bgpp});
		frame.toFront();
		label[0].toFront();
		label[1].toFront();
		blanket.toFront();
	}
}