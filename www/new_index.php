<?

include "../include/sess_context.php";

session_start();

session_register("sess_context");

include "../include/inc_db.php";
include "../include/constantes.php";
include "../include/toolbox.php";

$db = dbc::connect();
include "SQLServices.php";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html><head><title>Jorkyball Championship</title>

<style type="text/css">

	body {
		margin:0px 0px 0px 0px;
		background-color:#eeeeee;
		font: 12px/1.2 Verdana, Arial, Helvetica, sans-serif;
	}
	#liens {
		text-align: right;
		position: absolute;
		background:#fff;
		height: 70px;
		width: 19%;
		left: 80%;
		}
	#listeliens {
		text-align: center;
		position: absolute;
		background: blue;
		height: 100px;
		width: 19%;
		left: 79%;
		top: 10%;
		-moz-opacity:0.5;
		filter:Alpha(Opacity=50);
		}
	#imagecenter {
		position: absolute;
		background:#fff;
		height: 300px;
		width:100%;
		}

	#leftcontent {
		position: absolute;
		left:1%;
		width:20%;
		background:#fff;
		}

	#centerleftcontent {
		position: absolute;
		left:22%;
		width:28%;
		background:#fff;
		}

	#centerrightcontent {
		position: absolute;
		left:51%;
		width:28%;
		background:#fff;
		}

	#rightcontent {
		position: absolute;
		left:80%;
		width:19%;
		background:#fff;
		}

	#liens {
		border:1px dashed #000;
		}

	#listeliens2 {
		width: 99%;
		height: 99%;
		border:1px solid black;
		}

	#imagecenter {
		border-top:1px dashed #000;
		border-bottom:1px dashed #000;
		top:75px;
		}

	#rightcontent, #centerrightcontent, #centerleftcontent, #leftcontent {
		border:1px dashed #000;
		top:380px;
		}

	p, h1, pre {
		margin:1px 1px 5px 1px;
		}

	h1 {
		font-size:14px;
		padding-top:10px;
	}

	#rightcontent, #centerrightcontent, #centerleftcontent, #leftcontent p {
		font-size:10px;
	}

	.list_liens {
		list-style: none;
		margin-top: 3px;
	}

	.links_liens:hover {
		color:#555;
		font-weight:bold;
	}

	.links_liens {
		margin:3px 5px 0px 0px;
		text-align: right;
		text-decoration:none;
		color:#999;
	}

	.championnat_liens {
		margin:3px 5px 0px 0px;
		text-decoration:none;
		color:black;
	}

	.championnat_liens:hover {
		color:white;
		background:#555;
	}

	select, option {
		font: 10px/1.2 Verdana, Arial, Helvetica, sans-serif;
	}

</style>
</head>

<body>

<div id="liens">
<ul class="list_liens">
<li><a href=# class=links_liens> Découvrir <img src=arrow.gif border=0></a></li>
<li><a href=# class=links_liens> S'inscrire <img src=arrow.gif border=0></a></li>
<li><a href=# class=links_liens> Forum général <img src=arrow.gif border=0></a></li>
<li><a href=# class=links_liens onMouseover="showmenuwithtitle(event,linkset, 200, 'Liens')" onMouseout="delayhidemenu()"> Liens <img src=arrow.gif border=0></a></li>
</ul>
</div>

<div id="imagecenter">
<div id="part2" style="float: left; background: url('theme.jpg'); background-position: center center; width: 100%; height: 100%;"></div>
</div>

<div id="leftcontent">
<p style="background: #eee; color: #555; border-bottom: 1px solid #999;"><img src=bullet_menu_gray_on.gif border=0 style="margin-left: 3px;"><B> EDITO </B></p>
<p style="margin-left: 3px;">
Jouez vos matchs<Br>
Créer votre championnat<BR>
Enregistrer vos matchs<BR>
Analyser vos performance<BR>
<BR>
<B>TOUT EST GRATUIT !!!</B>
</p>
</div>

<div id="centerleftcontent">
<p style="background: #eee; color: #555; border-bottom: 1px solid #999;"><img src=bullet_menu_gray_on.gif border=0 style="margin-left: 3px;"><B> DERNIERS CHAMPIONNATS CREES </B></p>
<p style="margin-left: 3px;">
<table border=0 width=95% CELLSPACING=0 CELLPADDING=0 style="margin-left: 3px;">
<?
	$req = "SELECT * FROM jb_championnat ORDER BY dt_creation DESC LIMIT 0,10";
	$res = dbc::execSql($req);
	while($row = mysql_fetch_array($res))
	{
		echo "<TR><TD ALIGN=LEFT><IMG SRC=arrow.gif BORDER=0> <B><A HREF=\"championnat_acces.php?championnat=".$row['nom']."\" CLASS=championnat_liens>".$row['nom']."</A></B></TD><TD ALIGN=RIGHT>".$row['dt_creation']."</TD>";
	}
?>
</table>
</p>
</div>

<div id="centerrightcontent">
<p style="background: #eee; color: #555; border-bottom: 1px solid #999;"><img src=bullet_menu_gray_on.gif border=0 style="margin-left: 3px;"><B> ACCES CHAMPIONNATS </B></p>
<p style="margin-left: 3px;">

<img src=arrow.gif border=0 style="margin-left: 3px;"><B> Championnats libres : </B><BR>
<FORM NAME=libre ACTION=championnat_acces.php METHOD=POST>
<table border=0 cellpadding=0 cellspacing=0 style="margin-left: 3px;"><tr><td>
<?
	echo "<SELECT NAME=championnat onChange=\"javascript:document.forms[0].submit();\">";
	echo "<OPTION VALUE=\"\">";
	$req = "SELECT * FROM jb_championnat WHERE type=0";
	$res = dbc::execSql($req);
	while($row = mysql_fetch_array($res))
		echo "<OPTION VALUE=\"".$row['nom']."\" ".(isset($mon_championnat) && $mon_championnat == $row['nom'] ? "SELECTED" : "")."> ".$row['nom'];
	echo "</SELECT>";
?>
<td><A HREF=# onClick="javascript:document.forms['libre'].submit();"><img src=p_ok_gray.gif border=0 style="margin-left: 3px;"></A></td></table>
</FORM>
<img src=arrow.gif border=0 style="margin-left: 3px;"><B> Championnats : </B><BR>
<FORM NAME=championnat ACTION=championnat_acces.php METHOD=POST>
<table border=0 cellpadding=0 cellspacing=0 style="margin-left: 3px;"><tr><td>
<?
	echo "<SELECT NAME=championnat onChange=\"javascript:document.forms[0].submit();\">";
	echo "<OPTION VALUE=\"\">";
	$req = "SELECT * FROM jb_championnat WHERE type=1";
	$res = dbc::execSql($req);
	while($row = mysql_fetch_array($res))
		echo "<OPTION VALUE=\"".$row['nom']."\" ".(isset($mon_championnat) && $mon_championnat == $row['nom'] ? "SELECTED" : "")."> ".$row['nom'];
	echo "</SELECT>";
?>
<td><A HREF=# onClick="javascript:document.forms['championnat'].submit();"><img src=p_ok_gray.gif border=0 style="margin-left: 3px;"></A></td></table>
</FORM>
<img src=arrow.gif border=0 style="margin-left: 3px;"><B> Tournois : </B><BR>
<FORM NAME=tournoi ACTION=championnat_acces.php METHOD=POST>
<table border=0 cellpadding=0 cellspacing=0 style="margin-left: 3px;"><tr><td>
<?
	echo "<SELECT NAME=championnat onChange=\"javascript:document.forms[0].submit();\">";
	echo "<OPTION VALUE=\"\">";
	$req = "SELECT * FROM jb_championnat WHERE type=2";
	$res = dbc::execSql($req);
	while($row = mysql_fetch_array($res))
		echo "<OPTION VALUE=\"".$row['nom']."\" ".(isset($mon_championnat) && $mon_championnat == $row['nom'] ? "SELECTED" : "")."> ".$row['nom'];
	echo "</SELECT>";
?></td>
<td><A HREF=# onClick="javascript:document.forms['tournoi'].submit();"><img src=p_ok_gray.gif border=0 style="margin-left: 3px;"></A></td></table>
</FORM>

</p>
</div>

<div id="rightcontent">
<p style="background: #eee; color: #555; border-bottom: 1px solid #999;"><img src=bullet_menu_gray_on.gif border=0 style="margin-left: 3px;"><B> PUBLICITE/FORUM </B></p>
<p style="margin-left: 3px;">
					<TABLE BORDER=0 WIDTH=100%>
							<TR><TD ALIGN=CENTER><A HREF="#" onClick="javascript:alert('Vous pouvez utiliser ce logo pour me faire référence.\n\n\t\tMerci ...');"></B><IMG SRC="../images/accueil/jb_link.jpg" BORDER=0></A></TD>

							<TR><TD HEIGHT=2></TD>
							<TR><TD ALIGN=CENTER><A HREF="#" onClick="javascript:window.open('flash.html', 'vidéo', '');"><IMG SRC="../images/accueil/jb_video.jpg" BORDER=0></A></TD>
							<TR><TD HEIGHT=2></TD>
							<TR><TD ALIGN=CENTER><A HREF="#" onClick="javascript:window.open('http://www.footris.net', 'footris', '');"><IMG SRC="../images/accueil/jb_footris.gif" BORDER=0></A></TD>
						</TABLE></p>
</div>

<div style="position:absolute; top:450px">
</div>

<div id="listeliens">
<div id="listeliens2">
ddsf
</div>
</div>

</body>
</html>
