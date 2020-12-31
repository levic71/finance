<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "StatsBuilder.php";

$db = dbc::connect();

$select = "SELECT * FROM jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getJourneeId();
$res = dbc::execSQL($select);
$journee = mysql_fetch_array($res);

// On récupère les joueurs déjà présentes
$items = explode(',', $journee['joueurs']);
foreach($items as $i) $j_presents[$i] = $i;

// On retire le joueur sélectionné
$j_presents[$joueur] = "";

// On reconstitue le champ 'joueurs'
$res_j = "";
foreach($j_presents as $j)
	if ($j != "") $res_j .= ($res_j == "" ? "" : ",").$j;

// On met à jour la journée
$update = "UPDATE jb_journees SET joueurs='".$res_j."' WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getJourneeId();
$res = dbc::execSQL($update);

// Suppression des matchs joués par cette équipe
///////////////////////////////////////////////////////////////////////////
// POINT EN SUSPENS
///////////////////////////////////////////////////////////////////////////
if (false)
{
$select = "SELECT m.id id FROM jb_matchs m, jb_equipes e1 WHERE m.id_champ=".$sess_context->getChampionnatId()." AND m.id_journee=".$sess_context->getJourneeId()." AND m.id_equipe1=e1.id AND (e1.id_joueur1=".$joueur." OR e1.id_joueur2=".$joueur.");";
$res = dbc::execSQL($select);
while ($row = mysql_fetch_array($res))
{
	$delete = "DELETE FROM jb_matchs WHERE id_champ=".$sess_context->getChampionnatId()." AND id_journee=".$sess_context->getJourneeId()." AND id=".$row['id'];
	$res2 = dbc::execSQL($delete);
}
$select = "SELECT m.id id FROM jb_matchs m, jb_equipes e1 WHERE m.id_champ=".$sess_context->getChampionnatId()." AND m.id_journee=".$sess_context->getJourneeId()." AND m.id_equipe2=e1.id AND (e1.id_joueur1=".$joueur." OR e1.id_joueur2=".$joueur.");";
$res = dbc::execSQL($select);
while ($row = mysql_fetch_array($res))
{
	$delete = "DELETE FROM jb_matchs WHERE id_champ=".$sess_context->getChampionnatId()." AND id_journee=".$sess_context->getJourneeId()." AND id=".$row['id'];
	$res2 = dbc::execSQL($delete);
}
}

// Mise des statistiques de la journée
$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$stats->SQLUpdateClassementJournee();

mysql_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect("matchs.php");

?>
