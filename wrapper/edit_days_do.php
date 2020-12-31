<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/journeebuilder.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$zone_calendar    = Wrapper::getRequest('zone_calendar',    date('d/m/Y'));
$selection        = Wrapper::getRequest('selection',        '0');
$type_participant = Wrapper::getRequest('type_participant', 1);
$num_journee      = Wrapper::getRequest('num_journee',      ''); // numero de la journee
$create_auto      = Wrapper::getRequest('create_auto',      '0');
$alias_journee    = Wrapper::getRequest('alias_journee',    '');
$heure            = Wrapper::getRequest('heure',            '21h00');
$duree            = Wrapper::getRequest('duree',            90);
$consolante       = Wrapper::getRequest('consolante',       _PHASE_FINALE_8_);
$nb_poules        = Wrapper::getRequest('nb_poules',        4);
$phase_finale     = Wrapper::getRequest('phase_finale',     _PHASE_FINALE_8_);
$matchs_auto      = Wrapper::getRequest('matchs_auto',      0);
$matchs_ar        = Wrapper::getRequest('matchs_ar',        1);
$del              = Wrapper::getRequest('del',              0);
$idd              = Wrapper::getRequest('idd',              0);
$upd              = Wrapper::getRequest('upd',              0);

if (($upd == 1 || $del == 1) && !is_numeric($idd)) ToolBox::do_redirect("grid.php");


if ($del == 1)
{
	$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $idd);
	$sjs->delJournee();

	// Suppression du cache des stats championnat pour forcer le recalcul
	JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");
?>
<span class="hack_ie">_HACK_IE_</span><script>journees=""; xx({action: 'days', id:'main', url:'grid.php?action=days&page=0', grid:1}); $cMsg({ msg: 'Journée supprimée' });</script>
<?
	exit(0);
}


$modifier = $upd == 1 ? true : false;


// Si on vient d un sous menu (et non de journees_ajouter.php)
if (!isset($selection))
{
    $select = "SELECT * from jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getJourneeId();
    $res = dbc::execSQL($select);
    $row = mysqli_fetch_array($res);
	$selection = $row['joueurs'];
}

$new_date = substr($zone_calendar, 6, 4) . "-" . substr($zone_calendar, 3, 2) . "-" . substr($zone_calendar, 0, 2);

$js = "";
$eq = "";

// $type_participation == 0 : Sélection des joueurs qui sont présents
// $type_participation == 1 : Sélection des équipes qui sont présentes

if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_) $type_participation = 1;
if ($type_participant == 0) $js = $selection;

// Dans le cadre de la gestion par équipe, on va quand même initialiser les joueurs de la journée
if ($type_participant == 1 && $sess_context->getChampionnatType() != _TYPE_TOURNOI_)
{
	$j_selected = array();
	$eq = $selection;
	$select = "SELECT * FROM jb_equipes WHERE id IN (".SQLServices::cleanIN($selection).")";
	$res = dbc::execSQL($select);
	while($row = mysqli_fetch_array($res))
	{
		if ($row['nb_joueurs'] >= 2)
		{
			$item = explode('|', $row['joueurs']);
			foreach($item as $j) $j_selected[$j] = $j;
		}
	}
	foreach($j_selected as $j) $js .= ($js == "" ? "" : ",").$j;
}

if ($modifier)
{
	if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_) {

		$eq = $selection;

		// Formatage des équipes = Somme des équipes des poules sans les '|'
		$all_equipes = "";
		$tmp = str_replace('|', ',', $eq);
		$items = explode(',', $tmp);
		foreach($items as $item)
			if ($item != "") $all_equipes .= $all_equipes == "" ? $item : ",".$item;

		// Récupération de la liste des joueurs à partir des équipes
		$sjs = new SQLJoueursServices($sess_context->getChampionnatId());
		$js   = $sjs->getListeJoueursByEquipes($all_equipes);

		// Insertion de la journée
		$update = "UPDATE jb_journees SET nom='".$num_journee.":".$alias_journee."', tournoi_consolante=".$consolante.", tournoi_nb_poules=".$nb_poules.", tournoi_phase_finale=".$phase_finale.", date='".$new_date."', joueurs='".$js."', equipes='".$eq."' WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$idd.";";
		$res = dbc::execSQL($update);

		// Création automatique des matchs de poule
		if ($matchs_auto == 0)
		{
			$sms = new SQLMatchsServices($sess_context->getChampionnatId(), $sess_context->getJourneeId(), -1);
			$sms->createMatchsPoulesTournoi($eq, $matchs_ar);
		}
	} else {
		$sql = "UPDATE jb_journees SET nom='".$num_journee.":".$alias_journee."', date='".$new_date."', heure='".$heure."', duree=".$duree.", joueurs='".$js."', equipes='".$eq."', pref_saisie=".$type_participant." WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$idd.";";
		$res = dbc::execSQL($sql);
	}
	$sql = "SELECT * from jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$idd." ORDER BY id DESC;";
	$res = dbc::execSQL($sql);
	$row = mysqli_fetch_array($res);
	$sess_context->setJourneeId($row['id']);
}
else
{
	if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_) {

		$eq = $selection;

		// Formatage des équipes = Somme des équipes des poules sans les '|'
		$all_equipes = "";
		$tmp = str_replace('|', ',', $eq);
		$items = explode(',', $tmp);
		foreach($items as $item)
			if ($item != "") $all_equipes .= $all_equipes == "" ? $item : ",".$item;

		// Récupération de la liste des joueurs à partir des équipes
		$sjs1 = new SQLJoueursServices($sess_context->getChampionnatId());
		$js   = $sjs1->getListeJoueursByEquipes($all_equipes);

		// Insertion de la journée
		$insert = "INSERT INTO jb_journees (id_champ, tournoi_consolante, tournoi_nb_poules, tournoi_phase_finale, nom, date, heure, duree, joueurs, equipes, pref_saisie) VALUES (".$sess_context->getChampionnatId().", ".$consolante.", ".$nb_poules.", ".$phase_finale.", '".$num_journee.":".$alias_journee."', '".$new_date."', '".$heure."', ".$duree.", '".$js."', '".$eq."', ".$type_participant.");";
		$res = dbc::execSQL($insert);

		// On récupère les infos de la journée
		$sjs2 = new SQLJourneesServices($sess_context->getChampionnatId(), -1);
		$row = $sjs2->getJourneeByDate($new_date);

		// On affecte l'id de la journée en cours
		$sess_context->setJourneeId($row['id']);

		// Création automatique des matchs de poule
		if ($matchs_auto == 0)
		{
			$sms = new SQLMatchsServices($sess_context->getChampionnatId(), $sess_context->getJourneeId(), -1);
			$sms->createMatchsPoulesTournoi($eq, $matchs_ar);
		}

	} else {
		$sql = "INSERT INTO jb_journees (id_champ, nom, date, heure, duree, joueurs, equipes, pref_saisie) VALUES (".$sess_context->getChampionnatId().", '".$num_journee.":".$alias_journee."', '".$new_date."', '".$heure."', ".$duree.", '".$js."', '".$eq."', ".$type_participant.");";
		$res = dbc::execSQL($sql);
		$sql = "SELECT * from jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." AND date='".$new_date."' ORDER BY id DESC;";
		$res = dbc::execSQL($sql);
		$row = mysqli_fetch_array($res);
		$sess_context->setJourneeId($row['id']);
	}
}

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

?>

<span class="hack_ie">_HACK_IE_</span>
<script>
mm({action:'matches', tournoi: <?= $sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? 1 : 0 ?>, idj:'<?= $row['id'] ?>', name:'<?= $row['nom'] ?>', date:'<?= ToolBox::mysqldate2date($row['date']) ?>'});
$cMsg({ msg: 'Journée <?= $modifier ? "modifiée" : "ajoutée" ?>' });
</script>
