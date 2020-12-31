<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/StatsBuilder.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

// $zone_calendar = Wrapper::getRequest('zone_calendar', date('d/m/Y'));
$resultat      = Wrapper::getRequest('resultat',   '');
$start_time    = Wrapper::getRequest('start_time', '');
$stop_time     = Wrapper::getRequest('stop_time',  '');
$goals_time    = Wrapper::getRequest('goals_time', '');
$resultat      = Wrapper::getRequest('resultat',   '');
$journal       = Wrapper::getRequest('journal',    '');
$journal_empty = $journal == '' ? 0 : 1;
$nbset         = Wrapper::getRequest('nbset',      '');
$idm           = Wrapper::getRequest('idm',        0);

if (!is_numeric($idm)) ToolBox::do_redirect("grid.php");

// On récupère les infos de la journée
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$journee = $sjs->getJournee();
$is_journee_alias = $sjs->isJourneeAlias($journee);

// Essentiellement pour les tournois
$sjm = new SQLMatchsServices($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $idm);
$match = $sjm->getMatch();
$options_type_matchs = $match['niveau'];

$sql = "UPDATE jb_matchs SET journal_empty=".$journal_empty.", journal='".$journal."', start_time='".$start_time."', stop_time='".$stop_time."', goals_time='".$goals_time."', resultat='".$resultat."', nbset=".$nbset." WHERE id=".$idm." AND id_champ=".$sess_context->getChampionnatId()." AND id_journee=".$sess_context->getJourneeId().";";
$res = dbc::execSQL($sql);

// On essaie de mettre à jour le match suivant pour la phase finale et la consolante
if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_) Wrapper::setNextMatchTournoi($idm);

// Mise des statistiques globales de la journée
$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $sess_context->getChampionnatType());
$stats->SQLUpdateClassementJournee();

// Mise des statistiques de poules pour les journées de tournoi
if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
{
	$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $sess_context->getChampionnatType(), "AND niveau='".str_replace("SP|", "P|", $options_type_matchs)."'");
	$stats->SQLUpdateClassementJourneeTournoi(str_replace("SP|", "P|", $options_type_matchs));
}

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

?>

<span class="hack_ie">_HACK_IE_</span>
<script>
<? if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_) { ?>
xx({action: 'matches', id: 'main', tournoi: 1, url: 'tournament_matches.php?action=matches&page=1&idj=<?= $sess_context->getJourneeId() ?>&name=<?= $journee['nom'] ?>&date=<?= ToolBox::mysqldate2date($journee['date']) ?>&options_type_matchs=<?= $options_type_matchs ?>'});
<? } else { ?>
mm({action:'matches', idj:'<?= $sess_context->getJourneeId() ?>', name:'<?= $journee['nom'] ?>', date:'<?= ToolBox::mysqldate2date($journee['date']) ?>'});
<? } ?>
$cMsg({ msg: 'Match modifié' });
</script>

</div>
