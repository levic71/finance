<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

$delete = "DELETE FROM jb_matchs WHERE id_champ=".$sess_context->getChampionnatId()." AND id_journee=".$sess_context->getJourneeId();
$res = dbc::execSQL($delete);

// Mise des statistiques globales de la journée
$update = "UPDATE jb_journees set classement_joueurs='' WHERE id=".$sess_context->getJourneeId();
$res = dbc::execSQL($update);

// Suppression des statistiques de poules pour les journées de tournoi
if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
{
	$delete = "DELETE FROM jb_classement_poules WHERE id_champ=".$sess_context->getChampionnatId()." AND id_journee=".$sess_context->getJourneeId();
	$res = dbc::execSQL($delete);
}

mysql_close($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect($sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "matchs_tournoi.php" : "matchs.php");

?>
