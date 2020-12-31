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
$score1       = Wrapper::getRequest('score1_zip',   '');
$score2       = Wrapper::getRequest('score2_zip',   '');
$score3       = Wrapper::getRequest('score3_zip',   '');
$score4       = Wrapper::getRequest('score4_zip',   '');
$score5       = Wrapper::getRequest('score5_zip',   '');
$score6       = Wrapper::getRequest('score6_zip',   '');
$score7       = Wrapper::getRequest('score7_zip',   '');
$score8       = Wrapper::getRequest('score8_zip',   '');
$score9       = Wrapper::getRequest('score9_zip',   '');
$score10      = Wrapper::getRequest('score10_zip',  '');
$forfait1     = Wrapper::getRequest('forfait1',     0);
$forfait2     = Wrapper::getRequest('forfait2',     0);
$match_joue   = Wrapper::getRequest('match_joue',   0);
$prolongation = Wrapper::getRequest('prolongation', 0);
$tirs_au_but  = Wrapper::getRequest('tirs_au_but',  0);
$tirs1        = Wrapper::getRequest('tirs1',        '');
$tirs2        = Wrapper::getRequest('tirs2',        '');
$play_date    = Wrapper::getRequest('play_date',    '');
$play_time    = Wrapper::getRequest('play_time',    '');
$nbset        = Wrapper::getRequest('nbset',        '');
$upd          = Wrapper::getRequest('upd',          0);
$idm          = Wrapper::getRequest('idm',          0);
$del          = Wrapper::getRequest('del',          0);
$niveau       = Wrapper::getRequest('niveau',       '');
$points_victoire     = Wrapper::getRequest('points_victoire',     '');
$points_defaite      = Wrapper::getRequest('points_defaite',      '');
$score_points        = ($points_victoire == '' && $points_defaite) ? "" : $points_victoire.'|'.$points_defaite;
$options_type_matchs = Wrapper::getRequest('options_type_matchs', '');

if ($options_type_matchs == '') $options_type_matchs = $niveau;

if (($upd == 1 || $del == 1) && !is_numeric($idm)) ToolBox::do_redirect("grid.php");

if ($del == 1)
{
 	// On récupère les infos de la journée
	$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
	$journee = $sjs->getJournee();
	$is_journee_alias = $sjs->isJourneeAlias($journee);

	$id_j = $sess_context->getJourneeId();
	if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_ && $is_journee_alias)
		$id_j = $journee['id_journee_mere'];

	// Suppression du match
	$delete = "DELETE FROM jb_matchs WHERE id=".$idm." AND id_champ=".$sess_context->getChampionnatId()." AND id_journee=".$id_j;
	$res = dbc::execSQL($delete);

	// Mise des statistiques globales de la journée
	$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $sess_context->getChampionnatType());
	$stats->SQLUpdateClassementJournee();

	// Mise des statistiques de poules pour les journées de tournoi
	if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
	{
		$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $sess_context->getChampionnatType(), "AND niveau='".$options_type_matchs."'");
		$stats->SQLUpdateClassementJourneeTournoi($options_type_matchs);
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
$cMsg({ msg: 'Match supprimé' });
</script>
</div>
<?
	exit(0);
}



$modifier = $upd == 1 ? true : false;

$penaltys = $tirs_au_but == 1 ? $tirs1."|".$tirs2 : "";

// Composition du resultat complet du match
if ($forfait1 == -1 || $forfait2 == -2)
{
	$resultat = $forfait1 == -1 ? $forfait1 : $forfait2;
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

if ($modifier)
{
	$sql = "UPDATE jb_matchs SET score_points='".$score_points."', niveau='".$options_type_matchs."', play_date='".$play_date."', play_time='".$play_time."', penaltys='".$penaltys."', prolongation=".$prolongation.", match_joue=".$match_joue.", id_equipe1=".$eq1.", id_equipe2=".$eq2.", resultat='".$resultat."', fanny=".$fanny.", nbset=".$nbset." WHERE id=".$idm." AND id_champ=".$sess_context->getChampionnatId()." AND id_journee=".$sess_context->getJourneeId().";";
	$res = dbc::execSQL($sql);

	// On essaie de mettre à jour le match suivant pour la phase finale et la consolante
	if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_) Wrapper::setNextMatchTournoi($idm);
}
else
{
	if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
	{
		$id_j = $is_journee_alias ? $journee['id_journee_mere'] : $sess_context->getJourneeId();
		$sql = "INSERT INTO jb_matchs (play_date, play_time, penaltys, prolongation, match_joue, id_champ, id_journee, id_equipe1, id_equipe2, resultat, fanny, nbset, niveau, score_points) VALUES ('".$play_date."', '".$play_time."', '".$penaltys."', ".$prolongation.", ".$match_joue.", ".$sess_context->getChampionnatId().", ".$id_j.", ".$eq1.", ".$eq2.", '".$resultat."', ".$fanny.", ".$nbset.", '".str_replace("SP|", "P|", $options_type_matchs)."', '".$score_points."');";
		$res = dbc::execSQL($sql);

		// On essaie de mettre à jour le match suivant pour la phase finale et la consolante
		$tmp = explode('|', $options_type_matchs);
		if ($tmp[0] == 'F' || $tmp[0] == 'Y')
		{
			$sql2 = "SELECT id FROM jb_matchs WHERE id_champ=".$sess_context->getChampionnatId()." AND id_journee=".$id_j." AND niveau='".str_replace("SP|", "P|", $options_type_matchs)."';";
			$res2 = dbc::execSQL($sql2);
			if ($match = mysqli_fetch_array($res2)) Wrapper::setNextMatchTournoi($match['id']);
		}
	}
	else
	{
		$sql = "INSERT INTO jb_matchs (play_date, play_time, penaltys, prolongation, match_joue, id_champ, id_journee, id_equipe1, id_equipe2, resultat, fanny, nbset) VALUES ('".$play_date."', '".$play_time."', '".$penaltys."', ".$prolongation.", ".$match_joue.", ".$sess_context->getChampionnatId().", ".$sess_context->getJourneeId().", ".$eq1.", ".$eq2.", '".$resultat."', ".$fanny.", ".$nbset.");";
		$res = dbc::execSQL($sql);
	}
}

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
$cMsg({ msg: 'Match <?= $modifier ? "modifié" : "ajouté" ?>' });
</script>
</div>