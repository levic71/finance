<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

$new_date = substr($zone_calendar, 6, 4) . "-" . substr($zone_calendar, 3, 2) . "-" . substr($zone_calendar, 0, 2);

// On v�rifie qu'il n'y a pas d�j� une journ�e pr�vue � cette date
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), -1);
if ($sjs->getJourneeByDate($new_date))
{
	ToolBox::do_redirect("matchs_tournoi.php?errno=1");
}

// Insertion de la journ�e
$insert = "INSERT INTO jb_journees (id_champ, nom, date, id_journee_mere, id_matchs) VALUES (".$sess_context->getChampionnatId().", '".$nom.":".$nom_journee."', '".$new_date."', ".$sess_context->getJourneeId().", '+|".$selection."');";
$res = dbc::execSQL($insert);

// On r�cup�re les infos de la journ�e
$journee = $sjs->getJourneeByDate($new_date);

// Mise � jour des donn�es de la journ�e m�re pour ne pas afficher les matchs de la journ�e alias
if ($selection != "")
{
	$journee_mere = $sjs->getJournee($journee['id_journee_mere']);
	$exclude_matchs = $journee_mere['id_matchs'] == "" ? "-|".$selection : $journee_mere['id_matchs'].",".$selection;
	$update = "UPDATE jb_journees SET id_matchs='".$exclude_matchs."' WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$journee_mere['id'];
	$res = dbc::execSQL($update);
}

// La journ�e ins�r�e devient la journee de session
$sess_context->setJourneeId($journee['id']);

mysql_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

// On redirige sur match.php
ToolBox::do_redirect("matchs_tournoi.php");

?>
