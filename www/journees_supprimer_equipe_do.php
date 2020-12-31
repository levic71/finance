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

$select = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$equipe;
$res = dbc::execSQL($select);
$mon_equipe = mysql_fetch_array($res);

// On récupère les joueurs déjà présentes
$items = explode(',', $journee['joueurs']);
foreach($items as $i) $j_presents[$i] = $i;

// On retire les joueurs de l'équipe sélectionnée
$item = explode('|', $mon_equipe['joueurs']);
$defenseur = $item[0];
$attaquant = $item[1];
$j_presents[$defenseur] = "";
$j_presents[$attaquant] = "";

// On reconstitue le champ 'joueurs'
$res_j = "";
foreach($j_presents as $j)
	if ($j != "") $res_j .= ($res_j == "" ? "" : ",").$j;

// On récupère les equipes déjà présentes
$items = explode(',', $journee['equipes']);
foreach($items as $i) $eq_presentes[$i] = $i;

// On reconstitue le champ 'equipes' en retirant l'équipe à supprimer
$res_eq = "";
foreach($eq_presentes as $eq)
	if ($eq != $equipe)$res_eq .= ($res_eq == "" ? "" : ",").$eq;

// On met à jour la journée
$update = "UPDATE jb_journees SET equipes='".$res_eq."', joueurs='".$res_j."' WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getJourneeId();
$res = dbc::execSQL($update);

// Suppression des matchs joués par cette équipe
$delete = "DELETE FROM jb_matchs WHERE id_champ=".$sess_context->getChampionnatId()." AND id_journee=".$sess_context->getJourneeId()." AND (id_equipe1=".$equipe." OR id_equipe2=".$equipe.");";
$res = dbc::execSQL($delete);

// Mise des statistiques de la journée
$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$stats->SQLUpdateClassementJournee();

mysql_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect("matchs.php");

?>
