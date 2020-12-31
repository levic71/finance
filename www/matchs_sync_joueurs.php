<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "StatsBuilder.php";
include "../www/journees_synchronisation.php";

$db = dbc::connect();

$tab = synchronize_journees($sess_context->getRealChampionnatId(), $sess_context->getChampionnatType(), $sess_context->getChampionnatId(), "yes", $sess_context->getJourneeId());

mysql_close($db);

ToolBox::do_redirect($sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "matchs_tournoi.php" : "matchs.php");

?>
<!-- a href="<?= $sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "matchs_tournoi.php" : "matchs.php" ?>">clic</a -->
