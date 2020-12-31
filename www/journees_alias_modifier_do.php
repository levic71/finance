<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

$new_date = substr($zone_calendar, 6, 4) . "-" . substr($zone_calendar, 3, 2) . "-" . substr($zone_calendar, 0, 2);

// Update de la journée
$update = "UPDATE jb_journees SET nom='".$nom.":".$nom_journee."', date='".$new_date."', id_matchs='+|".$selection."' WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getJourneeId().";";
$res = dbc::execSQL($update);

$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$journee = $sjs->getJournee();

$sjs2 = new SQLJourneesServices($sess_context->getChampionnatId(), $journee['id_journee_mere']);
$liste_alias  = $sjs2->getAllAliasJournee();

$exclude_matchs = "";
$selection_matchs = "";
foreach($liste_alias as $alias)
{
	if ($alias['id_matchs'] != "")
	{
		$all_ids = explode('|', $alias['id_matchs']);
		if (isset($all_ids[1]) && $all_ids[1] != "")
		{
			$selection_matchs = $selection_matchs == "" ?  $all_ids[1] : $selection_matchs.",".$all_ids[1];
		}
	}
}
if ($selection_matchs != "") $exclude_matchs = "-|".$selection_matchs;
$update = "UPDATE jb_journees SET id_matchs='".$exclude_matchs."' WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$journee['id_journee_mere'];
$res = dbc::execSQL($update);

mysql_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

// On redirige sur match.php
ToolBox::do_redirect("matchs_tournoi.php");

?>
