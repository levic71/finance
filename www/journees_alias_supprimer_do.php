<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

if (!isset($id_journee2del)) $id_journee2del = $sess_context->getJourneeId();

$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $id_journee2del);
$journee = $sjs->getJournee();

// Update de la journée
$delete = "DELETE FROM jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$id_journee2del.";";
$res = dbc::execSQL($delete);

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
ToolBox::do_redirect($sess_context->championnat['visu_journee'] == _VISU_JOURNEE_CALENDRIER_ ? "calendar.php" : "journees.php");

?>
