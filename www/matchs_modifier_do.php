<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "StatsBuilder.php";

$menu = new menu("full_access");

$db = dbc::connect();

if (!isset($match_joue)) $match_joue = "off";
if (!isset($prolongation)) $prolongation = "off";
if (!isset($tirs_au_but)) $tirs_au_but = "off";
$penaltys = $tirs_au_but == "on" ? $tirs1."|".$tirs2 : "";

// Composition du resultat complet du match
if ((isset($forfait1) && $forfait1 == -1) || (isset($forfait2) && $forfait2 == -2))
{
	$resultat = (isset($forfait1) && $forfait1 == -1) ? $forfait1 : $forfait2;
	$fanny = 0;
}
else
{
	$resultat = $score1."/".$score2;
	if ($nbset >= 2) $resultat .= ",".$score3."/".$score4;
	if ($nbset >= 3) $resultat .= ",".$score5."/".$score6;
	if ($nbset >= 4) $resultat .= ",".$score7."/".$score8;
	if ($nbset == 5) $resultat .= ",".$score9."/".$score10;

	$fanny = ($nbset == 1 && (($score1 == 0 && $score2 > 0) || ($score1 > 0 && $score2 == 0))) ? 1 : 0;
}

if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
	$update = "UPDATE jb_matchs SET penaltys='".$penaltys."', prolongation=".($prolongation == "on" ? 1 : 0).", match_joue=".($match_joue == "on" ? 1 : 0).", score_points='".$points_victoire."|".$points_defaite."', niveau='".$options_type_matchs."', id_equipe1=".$eq1.", id_equipe2=".$eq2.", resultat='".$resultat."', fanny=".$fanny.", nbset=".$nbset." WHERE id=".$id_match;
else
	$update = "UPDATE jb_matchs SET penaltys='".$penaltys."', prolongation=".($prolongation == "on" ? 1 : 0).", match_joue=".($match_joue == "on" ? 1 : 0).", id_equipe1=".$eq1.", id_equipe2=".$eq2.", resultat='".$resultat."', fanny=".$fanny.", nbset=".$nbset." WHERE id=".$id_match;
$res = dbc::execSQL($update);

// Mise des statistiques globales de la journée
$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $sess_context->getChampionnatType());
$stats->SQLUpdateClassementJournee();

// Mise des statistiques de poules pour les journées de tournoi
if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
{
	$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $sess_context->getChampionnatType(), "AND niveau='".$options_type_matchs."'");
	$stats->SQLUpdateClassementJourneeTournoi($options_type_matchs);
}

mysql_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect($sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "matchs_tournoi.php?options_type_matchs=".$options_type_matchs : "matchs.php");

?>
