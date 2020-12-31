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
        })(x, y, data[i], labels[i], dot);
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

drawPolar = function (args) {
	var data = args.data||new Array(),
		size = args.size||400,
		R = args.R||180,
		centerGutter = args.centerGutter||0,
		stroke = args.stroke||30,
		items = new Array(),
		param = {stroke: "#fff", "stroke-width": stroke},
		hash = document.location.hash,
		marksAttr = {fill: hash || "#999", stroke: "none"},
		r = Raphael(args.name, size, size);

    // Custom Attribute
	r.customAttributes.arc = function (value, i) {
		var alpha = 360 / data[i].max * value,
			a = (90 - alpha) * Math.PI / 180,
			x = data[i].max == value ? (size / 2) - 0.01 : (size / 2) + data[i].rayon * Math.cos(a),
			y = data[i].max == value ? (size / 2) - data[i].rayon : (size / 2) - data[i].rayon * Math.sin(a);

		var path = [["M", (size / 2), (size / 2) - data[i].rayon], ["A", data[i].rayon, data[i].rayon, 0, data[i].max == value ? 1 : +(alpha > 180), 1, x, y]];

		return {path: path, stroke: data[i].c};
	};

	function drawMarks(R, max) {
		var out = r.set();
		for (var value = 0; value < max; value++) {
			var alpha = 360 / max * value, a = (90 - alpha) * Math.PI / 180;
			out.push(r.circle((size / 2) + R * Math.cos(a), (size / 2) - R * Math.sin(a), 2).attr(marksAttr));
		}
		return out;
	}

	for(i=0; i < data.length; i++) {
		data[i].rayon = R - (Math.floor( (R - centerGutter) / data.length) * i);
		drawMarks(data[i].rayon, data[i].max);
		items[i] = r.path().attr(param).attr({arc: [0, i], title: 'toto'});
		items[i].animate({arc: [data[i].v, i]}, 900, ">");
	}

//	var circle1 = r.circle(size/2, size/2, 95).attr("fill", "#aaa").attr("stroke", "none");
//	var circle2 = r.circle(size/2, size/2, 80).attr("id", "circle2").attr("fill", "#fff").attr("stroke", "none");
//	circle2.node.id = "circle2";
//	var img = r.image("../img/webclip114.png", 200-80, 200-80, 160, 160);
//	img.setAttribute('clipPath', 'url(#cp)');

//	r.rect(200-80, 200-80, 160, 160, 80).attr({
//	    fill: "url(../img/webclip114.png)",
//	    "background-repeat": "no-repeat",
//	    "stroke-width": "none"
//	});

var xmlns = 'http://www.w3.org/2000/svg';
var xlinkNS='http://www.w3.org/1999/xlink:'
var cp = document.createElementNS(xmlns, 'clipPath');
cp.setAttribute('id', 'cp');
var c = document.createElementNS(xmlns, 'circle');
c.setAttribute('cx', '0');
c.setAttribute('cy', '0');
c.setAttribute('r', '10');
cp.appendChild(c);
r.defs.appendChild(cp);

var img = r.image("../img/webclip114.png", 200-80, 200-80, 160, 160);
img.setAttribute('clipPath', 'url(#cp)');

};

window.onload = function () {

    var labels = [], data = [];

	// Grab the data
    elts = document.getElementById('data').getElementsByTagName('TH');
	for(var i=0; i < elts.length; i++) labels.push(elts[i].innerHTML);
    elts = document.getElementById('data').getElementsByTagName('TD');
	for(var i=0; i < elts.length; i++)  data.push(elts[i].innerHTML);

//	drawAnalytics({ name: "holder", labels: labels, data: data, overmax: 100 });
	drawAnalytics({ name: "holder", width: 670, height: 200, avg: 0.1, labels: [], data: [], yaxe_labels: ["", "1", "2", "3", "4", "5"], overmax: 100 });

	drawPolar({ name: "holder2", centerGutter: 80, data : [ { v: 45, max: 60, c: "#00AEEF" }, { v: 60, max: 60, c: "#EC008C" }, { v: 04, max: 12, c: "#cccccc" } ]});

}