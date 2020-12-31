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

// On récupère les infos de la journée
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$journee = $sjs->getJournee();
$is_journee_alias = $sjs->isJourneeAlias($journee);

// Suite à une synchronisation, des équipes ont pu être supprimées, donc on vérifie si elles sont bien dans le champ 'equipes'
if ($sess_context->getChampionnatType() == _TYPE_LIBRE_)
{
	$items = explode(',', $journee['equipes']);
	foreach($items as $elt) $liste_equipes[$elt] = $elt;

	if (!isset($liste_equipes[$eq1]) || !isset($liste_equipes[$eq2]))
	{
		$liste_equipes[$eq1] = $eq1;
		$liste_equipes[$eq2] = $eq2;

		$tmp = "";
		foreach($liste_equipes as $elt) $tmp .= ($tmp == "" ? "" : ",").$elt;
		$req = "UPDATE jb_journees SET equipes='".$tmp."' WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getJourneeId();
		$res = dbc::execSQL($req);
	}
}

// Insert du match
if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
{
	$id_j = $is_journee_alias ? $journee['id_journee_mere'] : $sess_context->getJourneeId();
	$insert = "INSERT INTO jb_matchs (penaltys, prolongation, match_joue, id_champ, id_journee, id_equipe1, id_equipe2, resultat, fanny, nbset, niveau, score_points) VALUES ('".$penaltys."', ".($prolongation == "on" ? 1 : 0).", ".($match_joue == "on" ? 1 : 0).", ".$sess_context->getChampionnatId().", ".$id_j.", ".$eq1.", ".$eq2.", '".$resultat."', ".$fanny.", ".$nbset.", '".str_replace("SP|", "P|", $options_type_matchs)."', '".$points_victoire."|".$points_defaite."');";
}
else
	$insert = "INSERT INTO jb_matchs (penaltys, prolongation, match_joue, id_champ, id_journee, id_equipe1, id_equipe2, resultat, fanny, nbset) VALUES ('".$penaltys."', ".($prolongation == "on" ? 1 : 0).", ".($match_joue == "on" ? 1 : 0).", ".$sess_context->getChampionnatId().", ".$sess_context->getJourneeId().", ".$eq1.", ".$eq2.", '".$resultat."', ".$fanny.", ".$nbset.");";
$res = dbc::execSQL($insert);

// Mise des statistiques globales de la journée
$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $sess_context->getChampionnatType());
$stats->SQLUpdateClassementJournee();

// Mise des statistiques de poules pour les journées de tournoi
if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
{
	$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $sess_context->getChampionnatType(), "AND niveau='".str_replace("SP|", "P|", $options_type_matchs)."'");
	$stats->SQLUpdateClassementJourneeTournoi(str_replace("SP|", "P|", $options_type_matchs));
}

mysql_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect($sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "matchs_tournoi.php?options_type_matchs=".$options_type_matchs : "matchs.php");

?>
