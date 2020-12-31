<?

require_once "../include/sess_context.php";

session_start();

include "../include/constantes.php";
include "../include/cache_manager.php";
include "../include/inc_db.php";

// http://localhost:8088/jorkyball/autologon/reach.php?entity=FOOTSPOTS&idc=487&login=login&pwd=xxxxx
// http://www.jorkers.com/autologon/reach.php?entity=FOOTSPOTS&idc=525&login=footspots&pwd=meneur54dejeu
// http://www.jorkers.com/autologon/reach.php?entity=FOOTSPOTS&idc=536&login=footspots&pwd=meneur54dejeu

if (!isset($sess_context))
{
	echo "Session expirée"; exit(0);
}
else
	$idc = $sess_context->getRealChampionnatId();

$db = dbc::connect();

$req = mysqli_query("SELECT count(*) total FROM jb_championnat WHERE id=".$idc) or die('Erreur SQL !<br>'.mysqli_error());
$row = mysqli_fetch_assoc($req);
if ($row['total'] == 0) $idc = 85;

$req = mysqli_query("SELECT c.entity entity, c.gestion_fanny, c.gestion_sets, c.tri_classement_general, c.type_sport, c.demo, c.gestion_nul, c.friends friends, c.type_lieu type_lieu, c.email email, c.login login, c.pwd pwd, c.description description, c.lieu lieu, c.gestionnaire gestionnaire, c.dt_creation dt_creation, c.valeur_victoire valeur_victoire, c.valeur_defaite valeur_defaite, c.valeur_nul valeur_nul, c.visu_journee visu_journee, c.news news, c.options options, c.id championnat_id, s.id saison_id, c.type type, c.nom championnat_nom, s.nom saison_nom FROM jb_championnat c, jb_saisons s WHERE c.id=".$idc." AND c.id=s.id_champ AND s.active=1") or die('Erreur SQL !<br>'.mysqli_error());
$chp = mysqli_fetch_assoc($req);

$chp['login'] = "";
$chp['pwd']   = "";
$sess_context->setChampionnat($chp);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><? $wrapper = true; require_once "head.php"; ?></head>
<body class="wrapper">

<div id="head">
	<div id="inner">
		<ul id="shortcuts">
			<li class="standby" id="myloader"></li>
			<li class="logo"><?= $chp['championnat_nom'] ?></li>
			<li class="teams"><div></div><a href="#" onclick="mm({action: 'teams'});">Equipes</a></li>
			<li class="days"><div></div><a href="#" onclick="mm({action: 'days'});">Journées</a></li>
		</ul>
	</div>
</div>

<div id="content">
	<div id="main"></div>
</div>

<div id="msgboxes"></div>

<? if (!$sess_context->isSuperUser()) { ?>

<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("UA-1509984-1");
pageTracker._initData();
pageTracker._trackPageview('/footspots.php?idc=<?= $idc ?>');
</script>

<? } ?>

</body>
</html>