<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

// Récupéraction de l'id de la journee
$tmp = explode("=", urldecode($pkeys_where));
$id_journee = $tmp[1];

$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $id_journee);
$sjs->delJournee();

// Suppression du cache des stats championnat pour forcer le recalcul
JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

mysql_close ($db);

ToolBox::do_redirect("journees.php");

?>
