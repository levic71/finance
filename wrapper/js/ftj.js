var ftj = function() {
	var h, w, a, s, act, cp, morph, imc, imcc, img, imgc, dcq, pfl, gimc, pfm, pfm_max, pfc, pfc_max, tabcal, tabcal_max;

	// Constantes
	var imc_comments = [ { s: 100, c: 'Obésité massive', i: 'black' }, { s: 40, c: 'Obésité sévère', i: 'red' }, { s: 35, c: 'Obésité modérée', i: 'orange' }, { s: 30, c: 'Surpoids', i: 'orange' }, { s: 25, c: 'Corpulance normale', i: 'green' }, { s: 18.5, c: 'Dénutrition modérée', i: 'orange' }, { s: 17, c: 'Dénutrition', i: 'orange' }, { s: 16, c: 'Dénutrition assez sévère', i: 'red' }, { s: 13, c: 'Dénutrition sévère', i: 'red' }, { s: 10, c: 'Dénutrition très sévère', i: 'black' } ];

	var img_comments = new Array();
	img_comments[1] = [ { s: 100, c: 'En excès de graisse', i: 'orange' }, { s: 20, c: 'Normal', i: 'green' }, { s: 15, c: 'Trop maigre', i: 'orange' } ];
	img_comments[2] = [ { s: 100, c: 'En excès de graisse', i: 'orange' }, { s: 30, c: 'Normal', i: 'green' }, { s: 25, c: 'Trop maigre', i: 'orange' } ];

	var ftj_sexe = [ { v:0, l:'Masculin' }, { v:1, l:'Féminin' } ];
	var ftj_morph = [ { v:0, l:'Mince' }, { v:1, l:'Normale' }, { v:2, l:'Large' } ];
	var ftj_activites = [ { v:1.25, l:'Sédentaire' }, { v:1.3, l:'Légère' }, { v:1.5, l:'Moyen' }, { v:1.7, l:'Intense' }, { v:2.0, l:'Très intense' } ];

	var ftj_coef_activities = [ { l:'sprint', c:0.134 }, { l:'stairs', c:0.116 }, { l:'running', c: 0.1 }, { l:'aerobics', c:0.088 }, { l:'racquet', c:0.081 }, { l:'chopping', c:0.080 }, { l:'dancing', c:0.076 }, { l:'swimming', c:0.071 }, { l:'skiing', c:0.068 }, { l:'basketball', c:0.063 }, { l:'jogging', c:0.061 }, { l:'walking', c:0.054 }, { l:'tennis', c:0.050 }, { l:'cycling', c:0.045 }, { l:'grocery', c:0.028 }, { l:'walkingslow', c:0.022 }, { l:'bowling', c:0.021 }, { l:'sitting', c:0.009 } ];

	return {

	setEnv:function(args) {
		h = args.h||181;
		w = args.w||75;
		a = args.a||41;
		s = args.s||1;
		act = args.act||1.5;
		cp = args.cp||16;
		morph = args.morph||1;
		imci = 'green'; imgi = 'green'; imc = 0; imcc = ''; img = 0; imgc = ''; dcq = 0; pfl = 0; gimc = 0; pfm = 0; pfm_max = 0; pfc = 0; pfc_max = 0; tabcal = 0; tab_cal_max = 0;
	},

	round:function(num) { return (Math.round(num*100)/100); },

	getCaloriesBurn:function(coef, duration) {
		return (w > 0 ? Math.round(coef*w*(duration/60)) : 0);
	},

	getProfile:function(args) {
		ftj.setEnv(args);

		if (false && (a < 3 || a > 100)) { alert('Ce test est valide pour les personnes de plus de 3 ans et de moins de 100 ans'); return false; }
		if (false && (w < 30 || w > 200)) { alert('Ce test est valide pour les personnes de plus de 30 kg et de moins de 200 kg'); return false; }

		if ((a < 3 || a > 100) || (w < 30 || w > 200))
			return { imc: '0', img: '0', gimc: '0', pfl: '-', imc_comment: '-', imc_indice: '-', img_comment: '-', img_indice: '-', dcq: '-', pfm: '-', pfm_max: '-', pfc: '-', pfc_max: '-', tabcal: '-', tabcal_max: '-' };


		imc = ftj.round(w/eval((h/100)*(h/100)));
		for(i = 0; i < imc_comments.length; i++) if (imc < imc_comments[i].s) { imcc = imc_comments[i].c; imci = imc_comments[i].i; }
		gimc = ftj.round(22*eval((h/100)*(h/100)));

		var ww = (s == 1 ? 66 + (13.7 * w) : 655 + (9.6 * w));
		var hh = (s == 1 ? 5 * h : 1.7 * h);
		var aa = (s == 1 ? 6.8 * a : 4.7 * a);
		dcq = Math.round(ww + hh - aa) * act;

		// poids de forme lorentz
		pfl = s == 1 ? (h - 100 - (h - 150) / 4) : (h - 100 - (h - 150) / 2.5);

		// poids de forme monnerot
		pfm = (h-100+4*cp)/2;
		pfm_max = ((h-100+4*cp)/2)+((h-100+4*cp)/2)*0.1;

		// poids de forme creff
		if (morph==0) {
			pfc = ftj.round((h-100+(a/10))*0.9*0.9);
			pfc_max = ftj.round(((h-100+(a/10))*0.9*0.9)+((h-100+(a/10))*0.9*0.9)*0.1);
		} else if (morph==1) {
			pfc = ftj.round((h-100+(a/10))*0.9);
			pfc_max = ftj.round(((h-100+(a/10))*0.9)+((h-100+(a/10))*0.9)*0.1);
		} else {
			pfc = ftj.round((h-100+(a/10))*0.9*1.1);
			pfc_max = ftj.round(((h-100+(a/10))*0.9*1.1)+((h-100+(a/10))*0.9*1.1)*0.1);
		}

		// Calories depensees selon tablescalories.com
		if (a < 10)			tabcal = s == 1 ? Math.round(w*22.7+495) : Math.round(w*22.5+499);
		else if (a < 18)	tabcal = s == 1 ? Math.round(w*17.5+651) : Math.round(w*12.2+746);
		else if (a < 30)	tabcal = s == 1 ? Math.round(w*15.3+679) : Math.round(w*14.7+496);
		else if (a < 60)	tabcal = s == 1 ? Math.round(w*11.6+879) : Math.round(w*8.70+829);
		else 				tabcal = s == 1 ? Math.round(w*13.5+487) : Math.round(w*10.5+596);

		if (act == 1.25 || act == 1.3) tabcal_max = s == 1 ? Math.round(tabcal*1.55) : Math.round(tabcal*1.56);
		else if (act == 1.5)           tabcal_max = s == 1 ? Math.round(tabcal*1.78) : Math.round(tabcal*1.64);
		else                           tabcal_max = s == 1 ? Math.round(tabcal*2.10) : Math.round(tabcal*1.82);

		img = ftj.round( ((1.2 * imc) + (0.23 * a) - (10.8 * (s == 1 ? 1 : 0)) - 5.4) );
		for(i = 0; i < img_comments[s].length; i++) if (img < img_comments[s][i].s) { imgc = img_comments[s][i].c; imgi = img_comments[s][i].i; }

		return { imc: imc, img: img, gimc: gimc, pfl: pfl, imc_comment: imcc, imc_indice: imci, img_comment: imgc, img_indice: imgi, dcq: dcq, pfm: pfm, pfm_max: pfm_max, pfc: pfc, pfc_max: pfc_max, tabcal: tabcal, tabcal_max: tabcal_max };
	},

	getActiviteRatio:function(act) {
		ret = 1.5;
		switch(act)
		{
			case 1: ret = 1.25; break;
			case 2: ret = 1.3; break;
			case 3: ret = 1.5; break;
			case 4: ret = 1.7; break;
			case 5: ret = 2; break;
			default: ret = 1.5;
		}
		return(ret);
	},

	setJorkersProfile:function(args) {
		args.act = ftj.getActiviteRatio(args.act);
		var me = ftj.getProfile(args);
		cc('imcval', Math.round(me.imc*10)/10);
		cc('imgval', Math.round(me.img*10)/10);
		el('b8').className = el('b8').className.replace('blue', me.imc_indice);
		el('b8').onclick = function() { alert(me.imc_comment+'\n\n'+'> 40: Obésité massive,\n entre 40 et 35: Obésité sévère,\n entre 35 et 30: Obésité modérée,\n entre 30 et 25: Surpoids,\n entre 25 et 18.5: Corpulence normale,\n 18.5 et 17: Dénutrition modérée,\n entre 18 et 17: Dénutrition,\n entre 17 et 13: Dénutrition sévère,\n < 13: Dénutrition très sévère');}
		el('b11').className = el('b11').className.replace('blue', me.img_indice);
		el('b11').onclick = function() { alert(me.img_comment+'\n\n'+(s == 1 ? '< 15%: trop maigre,\n > 20%: trop de graisse,\n sinon normal' : '< 25%: trop maigre,\n > 30%: trop de graisse,\n sinon normal'));}
		cc('dcqmval', me.dcq+' cal/j');
		cc('dcqvval', me.tabcal+' cal/j');
		cc('dcqxval', me.tabcal_max+' cal/j');
	}

	};
}();
