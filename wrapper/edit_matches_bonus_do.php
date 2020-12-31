<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/StatsBuilder.php";

$bonus = Wrapper::getRequest('bonus', '');

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$update = "UPDATE jb_journees SET bonus='".$bonus."' WHERE id=".$sess_context->getJourneeId();
$res = dbc::execSQL($update);

// Mise des statistiques globales de la journée
$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $sess_context->getChampionnatType());
$stats->SQLUpdateClassementJournee();

// On récupère les infos de la journée
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$journee = $sjs->getJournee();

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

?>

<div>

<span class="hack_ie">_HACK_IE_</span>
<script>
xx({action: 'matches', id: 'main', tournoi: 1, url: 'tournament_matches.php?action=matches&page=1&idj=<?= $sess_context->getJourneeId() ?>&name=<?= $journee['nom'] ?>&date=<?= ToolBox::mysqldate2date($journee['date']) ?>&options_type_matchs=X|0'});
$cMsg({ msg: 'Bonus mis à jour' });
</script>

</div>