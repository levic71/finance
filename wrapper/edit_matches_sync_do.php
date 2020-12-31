<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/StatsBuilder.php";
include "../www/journees_synchronisation.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$tab = synchronize_journees($sess_context->getRealChampionnatId(), $sess_context->getChampionnatType(), $sess_context->getChampionnatId(), "yes", $sess_context->getJourneeId());

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

echo "1||Synchronisation Ok";

?>