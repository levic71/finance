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
			marksAttr = {fill: hash || "#ccc", stroke: "none"};

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
					var skill = elt.t.skl||(elt.t + '\n' + elt.v + '%');
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
					var skill = elt.t.skl||(elt.t + '\n' + elt.v + '%');
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

window.onload = function () {
	o.init({ name: 'diagram', skills_list: 'skills_list', data: [ { rs: 90+((10+25)*3.6), v: [10, 25, 45] , t: [{lbl:'The best', skl:'Javascript\n100%'}, 'OO', 'Raphael'], c: ["#ED0086", "#666", "#aaa"] }, { v: 90, t: 'CSS3', c: "#08A7DC" }, { v: 80, t: 'Html5', c: {bg:'#FFE700', fg:'#000', op:0.5} }, { v: 60, t: 'Php', c: "#AFF53D" }, { v:100, t: '', c: "#eee" } ] });
}

