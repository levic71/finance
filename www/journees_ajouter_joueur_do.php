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

// On récupère les equipes déjà présentes
$items = explode(',', $journee['joueurs']);
foreach($items as $i) $j_presents[$i] = $i;

// On ajoute l'équipe sélectionnée
$j_presents[$joueur] = $joueur;

// On reconstitue le champ 'equipes'
$res_j = "";
foreach($j_presents as $j) $res_j .= ($res_j == "" ? "" : ",").$j;

// On met à jour la journée
$update = "UPDATE jb_journees SET joueurs='".$res_j."' WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getJourneeId();
$res = dbc::execSQL($update);

// Mise des statistiques de la journée
$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$stats->SQLUpdateClassementJournee();

mysql_close ($db);

ToolBox::do_redirect("matchs.php");

?>