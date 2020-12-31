<!DOCTYPE HTML PUBLIC "-//W3C//DTD 4.01 Transitional//EN">
<HTML>
<HEAD>
<META NAME="author"        CONTENT="Jorkers">
<META NAME="keywords"      CONTENT="Jorkers,gratuit,gestion,championnat,TOURNOI,Tournoi,Forum,jorkers,jorker,Jorkyball, jorkyball, JORKYBALL, JorkyBall,Jorky,online,en ligne,web,footris,Gratuit,Gestion,Championnat,Championship,classement,tournoi,statistique,joueur,équipe,journée,photo,forum,football,sport,divertissement,compétition,ami,pote,fun,futsal,Futsal Tournaments,management">
<META NAME="description"   CONTENT="Gestion de Championnats/tournois de JorkyBall - Tout est gratuit - Saisissez vos joueurs/équipes/matchs et automatiquement les classements et les statistiques sont calculés. Affichage et personnalisation de ces informations sur votre site grâce à la syndication des classements.">
<META NAME="robots"        CONTENT="index, follow">
<META NAME="rating"        CONTENT="General">
<META NAME="distribution"  CONTENT="Global">
<META NAME="author"        CONTENT="contact@jorkers.com">
<META NAME="reply-to"      CONTENT="contact@jorkers.com">
<META NAME="owner"         CONTENT="contact@jorkers.com">
<META NAME="copyright"     CONTENT="jorkers.com">
<META http-equiv="Content-Language" CONTENT="fr-FX">
<META http-equiv="Content-Type"     CONTENT="text/html; charset=<?= sess_context::charset ?>">
<LINK HREF="../images/H.ico" REL="shortcut icon">
<TITLE>Jorkers - Gestion de tournois/championnats de Jorkyball</TITLE>
</HEAD>
<BODY>

<?

include "../include/inc_db.php";
include "../include/toolbox.php";
include "../include/cache_manager.php";
include "../include/constantes.php";

$db = dbc::connect();

$filename = "../cache/flux_options.txt";
if (isset($save) && $save == "1")
{
	if (!isset($photo_icon))   $photo_icon   = "0";
	if (!isset($video_icon))   $video_icon   = "0";
	if (!isset($photos_home))  $photos_home  = "0";
	if (!isset($sondage_home)) $sondage_home = "0";
	if (!isset($sondage_question_home)) $sondage_question_home = "0";
	if (!isset($partenariat)) $partenariat = "0";
	if (!isset($zone_libre))  $zone_libre = "0";

    if (file_exists($filename))
		unlink($filename);
	$fichier = fopen($filename, "w");
	if (flock($fichier, LOCK_EX))
	{
		$flux = array('photo_icon' => $photo_icon, 'video_icon' => $video_icon, 'sondage_home' => $sondage_home, 'sondage_question_home' => $sondage_question_home, 'photos_home' => $photos_home, 'partenariat' => $partenariat, 'zone_libre' => $zone_libre);
		$objet_chaine = serialize($flux);
		fputs($fichier, $objet_chaine);
		flock($fichier, LOCK_UN);
	}
	fclose($fichier);
}

$cache = JKCache::getCache($filename, -1, "_FLUX_OPTIONS_JORKERS_");
$photo_icon   = $cache['photo_icon'];
$video_icon   = $cache['video_icon'];
$sondage_home = $cache['sondage_home'];
$sondage_question_home = $cache['sondage_question_home'];
$photos_home  = $cache['photos_home'];
$partenariat  = $cache['partenariat'];
$zone_libre   = $cache['zone_libre'];

?>
<SCRIPT SRC="../js/flashobject.js" type="text/javascript"></SCRIPT>

<CENTER>
<FORM ACTION=tdb.php METHOD=POST>

<STYLE type="text/css">
.tdb {
	text-align: center;
	display: inline;
}
.tdb .left {
	float: left;
}
.tdb DIV DIV {
	margin: 5px;
}
</STYLE>

<DIV CLASS=tdb>
	<DIV CLASS=left>

		<DIV>
			Championnat :
			<SELECT NAME=ref_champ onChange="javascript:submit();">
				<OPTION VALUE=0> Tous
				<OPTION VALUE=99999 <?= $ref_champ == 99999 ? "SELECTED" : "" ?>> Tous sauf visites
<?
		$select = "SELECT * FROM jb_championnat WHERE actif=1";
		$res = dbc::execSQL($select);
		while($row = mysql_fetch_array($res))
			echo "<OPTION VALUE=".$row['id']." ".(isset($ref_champ) && $ref_champ==$row['id'] ? "SELECTED" : "").">".$row['nom'];
?>
			</SELECT>
		</DIV>

		<DIV id="swfchart">
		<script type="text/javascript">
			// <![CDATA[
			var chart = new FlashObject("../swf/charts.swf", "swfchart", "600", "400", "0", "#666666");
			chart.addParam("quality", "best");
			chart.addParam("salign", "t");
			chart.addParam("scale", "noscale");
			chart.addVariable("library_path", "../swf/charts_library");
			chart.addVariable("xml_source", "../admin/xml_stats_freq.php<?= isset($ref_champ) ? "?ref_champ=".$ref_champ : "" ?>");
			chart.write("swfchart");
			// ]]>
		</script>
		</DIV>

		<SCRIPT SRC="../js/ts_picker.js"></SCRIPT>
		<DIV><CENTER>
		<TABLE BORDER=0><TR><TD>Intraday : </TD><TD><INPUT TYPE=TEXT  NAME=intraday_date VALUE="<?= isset($intraday_date) ? $intraday_date : "" ?>" SIZE=16></INPUT></TD><TD><a href="#"" onClick="javascript:show_calendar('document.forms[0].intraday_date', document.forms[0].intraday_date.value);" title="Afficher le calendrier"><img src="../images/cal.gif" border=0/></a></TD><TD><A HREF=# onClick="javascript:document.forms[0].submit();"><IMG SRC=../images/templates/defaut/bt_ok.gif BORDER=0></A></TD></TABLE>
		</CENTER></DIV>

		<DIV id="swfchart_intra">
		<script type="text/javascript">
			// <![CDATA[
			var chart = new FlashObject("../swf/charts.swf", "swfchart_intra", "600", "400", "0", "#666666");
			chart.addParam("quality", "best");
			chart.addParam("salign", "t");
			chart.addParam("scale", "noscale");
			chart.addVariable("library_path", "../swf/charts_library");
			chart.addVariable("xml_source", "../admin/xml_intraday.php?option=1_<?= isset($intraday_date) ? str_replace("/", "x", $intraday_date) : "" ?>_<?= $ref_champ ?>");
			chart.write("swfchart_intra");
			// ]]>
		</script>
		</DIV>

		<DIV id="swfchart_cumul">
		<script type="text/javascript">
			// <![CDATA[
			var chart = new FlashObject("../swf/charts.swf", "swfchart_cumul", "600", "400", "0", "#666666");
			chart.addParam("quality", "best");
			chart.addParam("salign", "t");
			chart.addParam("scale", "noscale");
			chart.addVariable("library_path", "../swf/charts_library");
			chart.addVariable("xml_source", "../admin/xml_intraday.php?ref_champ=<?= $ref_champ ?>");
			chart.write("swfchart_cumul");
			// ]]>
		</script>
		</DIV>

	</DIV>

	<DIV CLASS=right>
		<DIV>
jorkers.com
<a href="http://www.yagoort.org" target="_blank" title="Google PageRank">
	<img border=0 src="http://www.yagoort.org/pagerank.php?domain=www.jorkers.com&rtcolor=FBBC11&gdcolor=5EAA5E&bgcolor=D9D9D9&textcolor=000000&width=35&height=16&type=RKPR" alt="Futur PageRank"/>
</a>
		</DIV>

		<DIV>
<?
		$select = "SELECT count(*) count FROM jb_stats GROUP BY DATE_FORMAT( date, '%Y%m%d' ) ORDER BY count DESC LIMIT 0 , 1";
		$res = dbc::execSQL($select);
		$row = mysql_fetch_array($res);
		echo "max:".$row['count'];
?>
		</DIV>

<style>
ul {
	text-align: left;
}
li {
	font-family: Verdana, Arial, Helvetica, Geneva, sans-serif;
	font-size: 10px;
	color: black;
	display : list-item;
	list-style-position: outside;
	padding: 0px 0px 0px 15px;
	list-style-type: square;
}
</style>
		<div>
		<ul>
			<li><input type="checkbox" name="photo_icon"            onchange="javascript:document.forms[0].save.checked=true;" <?= isset($photo_icon)   && $photo_icon == "1"   ? "checked=\"checked\"" : "" ?> value="1" /> Photo new</li>
			<li><input type="checkbox" name="video_icon"            onchange="javascript:document.forms[0].save.checked=true;" <?= isset($video_icon)   && $video_icon == "1"   ? "checked=\"checked\"" : "" ?> value="1" /> Video new</li>
			<li><input type="checkbox" name="sondage_home"          onchange="javascript:document.forms[0].save.checked=true;" <?= isset($sondage_home) && $sondage_home == "1" ? "checked=\"checked\"" : "" ?> value="1" /> Sondage on home</li>
			<li><input type="checkbox" name="sondage_question_home" onchange="javascript:document.forms[0].save.checked=true;" <?= isset($sondage_question_home) && $sondage_question_home == "1" ? "checked=\"checked\"" : "" ?> value="1" /> Sondage question on home</li>
			<li><input type="checkbox" name="photos_home"           onchange="javascript:document.forms[0].save.checked=true;" <?= isset($photos_home)  && $photos_home == "1"  ? "checked=\"checked\"" : "" ?> value="1" /> photo on home</li>
			<li><input type="checkbox" name="partenariat"           onchange="javascript:document.forms[0].save.checked=true;" <?= isset($partenariat)  && $partenariat == "1"  ? "checked=\"checked\"" : "" ?> value="1" /> partenariat</li>
			<li><input type="checkbox" name="zone_libre"            onchange="javascript:document.forms[0].save.checked=true;" <?= isset($zone_libre)   && $zone_libre == "1"   ? "checked=\"checked\"" : "" ?> value="1" /> zone libre</li>
			<li><input type="checkbox" name="save" value="1" /> Save datas
		</ul>
		<input type="submit" value="valider" />
		</div>

		<div>
		<ul>
			<li><a href="forum_lookup.php"> Forum lookup </a></li>
			<li><a href="gestion_actualites.php"        target="_blank"> Gestion des actualités </a></li>
			<li><a href="gestion_videos.php"            target="_blank"> Gestion des vidéos </a></li>
			<li><a href="gestion_photos.php"            target="_blank"> Gestion des photos </a></li>
			<li><a href="gestion_astuces.php"           target="_blank"> Gestion des astuces </a></li>
			<li><a href="gestion_championnat_actif.php" target="_blank"> Gestion des championnats actifs </a></li>
			<li><a href="gestion_zonelibre.php"         target="_blank"> Gestion zone libre </a></li>
			<li></li>
<?
$request = "SELECT count(*) total FROM jb_sms WHERE date = '".date("Y-m-d")."';";
$res = dbc::execSQL($request);
$total = ($row = mysql_fetch_array($res)) ? $row['total'] : 0;
echo "<li>Nb sms aujourd'hui: ".$total."</li>";
?>
		</ul>
		</div>

		<DIV>
		<ul><li>Liste des journées passées</li><hr />
<?
		$req = "SELECT j.date date, j.nom journee, c.nom champ from jb_journees j , jb_championnat c, jb_saisons s WHERE c.id=s.id_champ AND s.id=j.id_champ AND date < '".date("Y-m-d")."' ORDER BY date DESC LIMIT 0,9";
		$res = dbc::execSQL($req);
		while($row = mysql_fetch_array($res))
		{
			echo "<li>".$row['date']." <b>".ToolBox::conv_lib_journee($row['journee'])."</b>  ".$row['champ']."</li>";
		}
?>		</ul><ul><li>Liste des futures journées</li><hr />
<?		$req = "SELECT j.date date, j.nom journee, c.nom champ from jb_journees j , jb_championnat c, jb_saisons s WHERE c.id=s.id_champ AND s.id=j.id_champ AND date >= '".date("Y-m-d")."' ORDER BY date ASC LIMIT 0,9";
		$res = dbc::execSQL($req);
		while($row = mysql_fetch_array($res))
		{
			echo "<li>".$row['date']." <b>".ToolBox::conv_lib_journee($row['journee'])."</b> ".$row['champ']."</li>";
		}
?>
		</ul>
		</DIV>

		<DIV>
		<ul><li>Dernières connexions admin</li><hr />
<?
		$req = "SELECT * from jb_stats s, jb_championnat c WHERE admin=1 AND s.id_champ=c.id ORDER BY date DESC LIMIT 0,19";
		$res = dbc::execSQL($req);
		while($row = mysql_fetch_array($res))
		{
			echo "<li>".substr($row['date'], 0, 4)."/".substr($row['date'], 4, 2)."/".substr($row['date'], 6, 2)." <b>".$row['nom']."</b><span style=\"color: brown;\">[".$row['email']."]</span></li>";
		}
?>
		</ul>
		</DIV>


		<DIV>
		<ul><li>Les + connectes en admin</li><hr />
<?
		$req = "SELECT c.id, c.nom, count(*) total from jb_stats s, jb_championnat c WHERE s.date >= ( CURDATE() - INTERVAL 365 DAY ) AND admin=1 AND s.id_champ=c.id GROUP BY c.id ORDER BY total DESC LIMIT 0,19";
		$res = dbc::execSQL($req);
		while($row = mysql_fetch_array($res))
		{
			echo "<li>".$row['total']." - <b>".$row['nom']."</b><span style=\"color: brown;\">[".$row['email']."]</span></li>";
		}
?>
		</ul>
		</DIV>

	</DIV>

</DIV>

</FORM>
</CENTER>

</BODY>
</HTML>
