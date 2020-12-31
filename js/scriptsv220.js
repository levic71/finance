// Lance un sondage
function launchSondage(id_sondage, objet, display)
{
	document.forms[0].action='../www/sondage.php';
	document.forms[0].id_sondage.value=id_sondage;
	if (display == 1)
	{
		nb_sel=objet.length;
		for(i=0; i < nb_sel; i++)
		{
			if (objet.options[i].selected == true)
			{
				document.forms[0].reponse1.value=objet.options[i].value;
			}
		}
	}
	
	if (display == 2)
	{
		if (document.forms[0].reponse1.value == 0) document.forms[0].reponse1.value = 1;
	}
	
	document.forms[0].submit();
}

// Gestion des affichages sur les pages actualités
function changeActu(actu)
{
	if (id_actu != -1)
		document.getElementById('actu'+id_actu).style.display='none';
	document.getElementById('actu'+actu).style.display='block';
	id_actu = actu;
}
// Swap 2 div sur la HOME
function divSwapDisplay(div1, div2, titre, img1, img2)
{
	if (document.getElementById(div2).style.display == '' || document.getElementById(div2).style.display == 'none')
	{
		document.getElementById(titre).style.background='url('+img1+') no-repeat';
		document.getElementById(div1).style.display='none';
		document.getElementById(div2).style.display='block';
	}
	else
	{
		document.getElementById(titre).style.background='url('+img2+') no-repeat';
		document.getElementById(div1).style.display='block';
		document.getElementById(div2).style.display='none';
	}
}
// Fonction pour le 'ENTER' sur la HOME
function enter_home(event)
{
	if (event.keyCode == 13)
		document.forms[0].submit();
}
// Accès direct aux championnats de la home
function launch_access(value)
{
	if (value == '' || value == '0')
	{
		alert('Sélectionner un championnat !!!');
		return false;
	}
	
	document.forms[0].ref_champ.value = value;
	document.forms[0].submit();
}
// Changement de style sur mouseover dans le pave forum sur la home
function changeStyle1(obj)
{
	obj.style.cursor='pointer';
	obj.style.border='1px dashed #BBBBBB';
	obj.style.paddingLeft='5px';
	obj.style.background='#DDDDDD';
}
function changeStyle2(obj)
{
	obj.style.border='0px';
	obj.style.paddingLeft='0px';
	obj.style.background='none';
}
// Changement de style sur mouseover dans le pave forum des pages intérieuree
function changeStyle3(obj)
{
	obj.style.cursor='pointer';
	obj.style.background='#DDDDDD';
}
function changeStyle4(obj)
{
	obj.style.background='none';
}
// Changement de championnat
function cchamp(id_championnat)
{
	window.location.href='../www/championnat_changer_do.php?choix_amis='+id_championnat;
}
// Changement de la langue
function clang(langue)
{
	window.location.href='../www/langue_changer_do.php?choix_langue='+langue;
}
// Changement de saison
function csaison(id_saison, appelant)
{
	window.location.href='../www/saisons_changer_do.php?choix_saisons='+id_saison+'&appelant='+appelant;
}
function gohome(url)
{
	window.location=url;
}
function launch(url)
{
	window.location=url;
}

var tempX = 0
var tempY = 0

function getMouseXY(e)
{
	var IE = document.all?true:false

	if (IE)
	{
		tempX = event.clientX + document.body.scrollLeft;
		tempY = event.clientY + document.body.scrollTop;
	}
	else
	{
		tempX = e.pageX;
		tempY = e.pageY;
	}

	// catch possible negative values in NS4
	if (tempX < 0) {tempX = 0;}
	if (tempY < 0) {tempY = 0;}

	return true
}
function getInfoElement()
{
   	if (document.getElementById)
   		obj = document.getElementById('div_info');
   	else
   		obj = document.all["div_info"];

	return obj;
}
function setInfo(obj, str)
{
	obj.innerHTML=str;
}
function show_info(str, event)
{
	obj = getInfoElement();
	setInfo(obj, str);
	getMouseXY(event);
	show_infoxy(str, tempX+10, tempY+10);
}
function show_info_oncenter(str, event)
{
	obj = getInfoElement();
	setInfo(obj, str);
	getMouseXY(event);
	show_infoxy(str, tempX-(obj.offsetWidth/2), tempY-(obj.offsetHeight/2));
}
function show_info_upcenter(str, event)
{
	obj = getInfoElement();
	setInfo(obj, str);
	getMouseXY(event);
	show_infoxy(str, tempX-(obj.offsetWidth/2), tempY-obj.offsetHeight-20);
}
function show_info_upleft(str, event)
{
	obj = getInfoElement();
	setInfo(obj, str);
	getMouseXY(event);
	show_infoxy(str, tempX-obj.offsetWidth-20, tempY-obj.offsetHeight-20);
}
function show_info_upright(str, event)
{
	obj = getInfoElement();
	setInfo(obj, str);
	getMouseXY(event);
	show_infoxy(str, tempX+20, tempY-obj.offsetHeight-20);
}
function show_infoxy(str, x, y)
{
	obj = getInfoElement();
	if ((x+obj.offsetWidth+20) > document.body.clientWidth) x=document.body.clientWidth-obj.offsetWidth-20;
	if ((y+obj.offsetHeight+20) > document.body.clientHeight) y=document.body.clientHeight-obj.offsetHeight-20;
	if (x < 0) x = 10;
	if (y < 0) y = 10;
	obj.style.left=x+'px';
	obj.style.top=y+'px';
	obj.style.visibility='visible';
}
function close_info()
{
	obj = getInfoElement();
	obj.style.visibility='hidden';
}
function showhide(body, img)
{
	if (document.getElementById(body).style.display == 'none')
	{
		document.getElementById(body).style.display='block';
		document.getElementById(img).src='../images/top.gif';
	}
	else
	{
		document.getElementById(body).style.display='none';
		document.getElementById(img).src='../images/bottom.gif';
	}
}

function verif_EMAIL(str)
{
	var filter=/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i
	if (filter.test(str))
		testresults=true
	else
	{
		alert("Le champ Email est incorrect !")
		testresults=false
	}
	return (testresults)
}

function verif_JJMMAAAA(str, label)
{
        if (str.length == 0)
        {
                alert('Le champ <'+label+'> ne doit pas être vide');
                return false;
        }

        if (!(str.length == 10))
        {
                alert('Le champ <'+label+'> doit être de la forme JJ/MM/AAAA');
                return false;
        }

        var jour=str.substring(0, 2);
        var mois=str.substring(3, 5);
        var year=str.substring(6, 10);

        if (jour > 31 || jour < 1 || mois < 1 || mois > 12)
        {
                alert('Le champ <'+label+'> doit être de la forme JJ/MM/AAAA');
                return false;
        }

        return true;
}

function verif_JJMMAAAA_brut(str, label)
{
        if (str.length == 0)
        {
                alert('Le champ <'+label+'> ne doit pas être vide');
                return false;
        }

        if (str.length != 8)
        {
                alert('Le champ <'+label+'> doit être de la forme JJMMAAAA');
                return false;
        }

        var jour=str.substring(0, 2);
        var mois=str.substring(2, 4);
        var year=str.substring(4, 8);

        if (jour > 31 || jour < 1 || mois < 1 || mois > 12)
        {
                alert('Le champ <'+label+'> doit être de la forme JJMMAAAA');
                return false;
        }

        return true;
}

function verif_MMAAAA(str, label)
{
        if (str.length == 0)
        {
                alert('Le champ <'+label+'> ne doit pas être vide');
                return false;
        }

        if (str.length != 7)
        {
                alert('Le champ <'+label+'> doit être de la forme MM/AAAA');
                return false;
        }

        var mois=str.substring(0, 2);
        var year=str.substring(3, 7);

        if (mois < 1 || mois > 12)
        {
                alert('Le champ <'+label+'> doit être de la forme MM/AAAA');
                return false;
        }

        return true;
}

function isacar(car)
{
	if (	(car >= "0" && car <= "9") ||
		(car >= "A" && car <= "Z") ||
		(car >= "a" && car <= "z")   
	   )
		return true;
	else
		return false;
}
function isaextcar(car)
{
	if (	(car >= "0" && car <= "9") ||
		(car == "&"                ) ||
		(car == "é"                ) ||
		(car == "\""             ) ||
		(car == "\n"             ) ||
		(car == "'"                ) ||
		(car == "("                ) ||
		(car == ")"                ) ||
		(car == "-"                ) ||
		(car == "è"                ) ||
		(car == "_"                ) ||
		(car == "ç"                ) ||
		(car == ","                ) ||
		(car == "à"                ) ||
		(car == ")"                ) ||
		(car == "="                ) ||
		(car == "+"                ) ||
		(car == "#"                ) ||
		(car == "{"                ) ||
		(car == "["                ) ||
		(car == "|"                ) ||
		(car == "\\"             ) ||
		(car == "@"                ) ||
		(car == "ù"                ) ||
		(car == "$"                ) ||
		(car == "£"                ) ||
		(car == "§"                ) ||
		(car == "ê"                ) ||
		(car == "â"                ) ||
		(car == "ô"                ) ||
		(car == "ä"                ) ||
		(car == "ë"                ) ||
		(car == " "                ) ||
		(car == "ï"                ) ||
		(car == "\;"               ) ||
		(car == "."                ) ||
		(car == "?"                ) ||
		(car == "/"                ) ||
		(car == ":"                ) ||
		(car == "!"                ) ||
		(car == "°"                ) ||
		(car == "%"               ) ||
		(car >= "A" && car <= "Z") ||
		(car >= "a" && car <= "z")   
	   )
		return true;
	else
		return false;
}

function verif_alphanum(str, label, size)
{
        if (str.length == 0)
        {
                alert('Le champ <'+label+'> ne doit pas être vide');
                return false;
        }

        if (size != -1 && str.length != size)
        {
                alert('Le champ <'+label+'> doit être composé de '+size+' caractères alphanumériques');
                return false;
        }

        for(var i=0; i < str.length; i++)
        {
                var car=str.substring(i, i+1);
                if (!isacar(car))
                {
                        alert('Le champ <'+label+'> doit être alphanumérique');
                        return false;
                }
        }

        return true;
}

function verif_alphanum2(str, label, size)
{
        if (str.length == 0)
        {
                alert('Le champ <'+label+'> ne doit pas être vide');
                return false;
        }

        if (size != -1 && str.length < size)
        {
                alert('Le champ <'+label+'> doit être composé d\'au moins '+size+' caractères alphanumériques');
                return false;
        }

        for(var i=0; i < str.length; i++)
        {
                var car=str.substring(i, i+1);
                if (!isacar(car))
                {
                        alert('Le champ <'+label+'> doit être alphanumérique');
                        return false;
                }
        }

        return true;
}

function verif_alphanumext(str, label, size)
{
        if (str.length == 0)
        {
                alert('Le champ <'+label+'> ne doit pas être vide');
                return false;
        }

        if (size != -1 && str.length < size)
        {
                alert('Le champ <'+label+'> doit être composé d\'au moins '+size+' caractères alphanumériques');
                return false;
        }

        for(var i=0; i < str.length; i++)
        {
                var car=str.substring(i, i+1);
                if (!isaextcar(car))
                {
                        alert('Le champ <'+label+'> doit être alphanumérique');
                        return false;
                }
        }

        return true;
}

function verif_num(num, label, min, max)
{
        if (num.length == 0)
        {
                alert('Le champ <'+label+'> ne doit pas être vide');
                return false;
        }

        for(var i=0; i < num.length; i++)
        {
                var car=num.substring(i, i+1);
                if (!(car >= "0" && car <= "9"))
                {
                        alert('Le champ <'+label+'> doit être numérique');
                        return false;
                }
        }

        if (num > max || num < min)
        {
                alert('Le champ <'+label+'> doit être compris entre '+min+' et '+max);
                return false;
        }

        return true;
}

function changeColor(item)
{
	if (item.value.length == 0)
		item.style.backgroundColor='#FFCCCC';
	else
		item.style.backgroundColor='';
}

function upperFirstLetter(str)
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
function SBox_TestSelection(sbox)
{
        if (sbox.options[0].selected == true)
                sbox.options[0].selected=false;
}
function SBox_DversS(sboxS, sboxD)
{
        nb_sel=sboxD.length;
        for(i=0; i < nb_sel; i++)
        {
                if (sboxD.options[i].selected == true)
                {
                        txt=sboxD.options[i].text;
                        val=sboxD.options[i].value;
                        sboxD.options[i]=null;
                        a=new Option(txt, val, false, true);
                        indexD=sboxS.options.length;
                        sboxS.options[indexD]=a;
                        i--; nb_sel--;
                }
        }
}
function SBox_SversD(sboxS, sboxD)
{
        nb_sel=sboxS.length;
        for(i=0; i < nb_sel; i++)
        {
                if (sboxS.options[i].selected == true)
                {
                        txt=sboxS.options[i].text;
                        val=sboxS.options[i].value;
                        sboxS.options[i]=null;
                        a=new Option(txt, val, false, true);
                        indexD=sboxD.options.length;
                        sboxD.options[indexD]=a;
                        i--; nb_sel--;
                }
        }
}
function SBox_Vider(sbox)
{
        nb_sel=sbox.length;
        for(i=0; i < nb_sel; i++)
        {
			sbox.options[i]=null;
            i--; nb_sel--;
        }
}
function SBox_Ajout_Item(sbox, item, val, selected)
{
	a=new Option(item, val, false, selected);
	indexD=sbox.options.length;
	sbox.options[indexD]=a;
}
function SBox_Del_SelectedItems(sboxS)
{
        nb_sel=sboxS.length;
        for(i=0; i < nb_sel; i++)
        {
                if (sboxS.options[i].selected == true)
                {
                        txt=sboxS.options[i].text;
                        val=sboxS.options[i].value;
                        sboxS.options[i]=null;
                        i--; nb_sel--;
                }
        }
}
function SBox_Up(sboxS)
{
        nb_sel=sboxS.length;
        nb_selected = 0;
        for(i=0; i < nb_sel; i++)
        {
                if (sboxS.options[i].selected == true)
                	nb_selected++;
        }

        if (nb_selected > 1)
        {
        	alert('Une seule entrée peut être sélectionner !!!');
        	return false;
		}
		
        for(i=0; i < nb_sel; i++)
        {
                if (sboxS.options[i].selected == true && i > 1)
                {
                        txt=sboxS.options[i-1].text;
                        val=sboxS.options[i-1].value;
                        sboxS.options[i-1].text  = sboxS.options[i].text;
                        sboxS.options[i-1].value = sboxS.options[i].value;
                        sboxS.options[i-1].selected = true;
                        sboxS.options[i].text = txt;
                        sboxS.options[i].val  = val;
                        sboxS.options[i].selected = false;
                        return true;
                }
        }
}
function SBox_Down(sboxS)
{
        nb_sel=sboxS.length;
        nb_selected = 0;
        for(i=0; i < nb_sel; i++)
        {
                if (sboxS.options[i].selected == true)
                	nb_selected++;
        }

        if (nb_selected > 1)
        {
        	alert('Une seule entrée peut être sélectionner !!!');
        	return false;
		}
		
        for(i=0; i < nb_sel; i++)
        {
                if (sboxS.options[i].selected == true && i < (nb_sel-1))
                {
                        txt=sboxS.options[i+1].text;
                        val=sboxS.options[i+1].value;
                        sboxS.options[i+1].text  = sboxS.options[i].text;
                        sboxS.options[i+1].value = sboxS.options[i].value;
                        sboxS.options[i+1].selected = true;
                        sboxS.options[i].text = txt;
                        sboxS.options[i].val  = val;
                        sboxS.options[i].selected = false;
                        return true;
                }
        }
}

var cache_equipe = "";

function changeBG_equipe(id, bg)
{
    if (document.getElementById)
    {
        for(var i=0; i < 30; i++)
        {
	        if (document.getElementById("M"+id+""+i))
	        {
		        var elt = document.getElementById("M"+id+""+i);
		        elt.style.background=bg;
	        }
        }
    }
}

function highlight_equipe(id)
{
	if (cache_equipe != "") changeBG_equipe(cache_equipe, "");
	changeBG_equipe(id, "orange");
	cache_equipe = id;
}


