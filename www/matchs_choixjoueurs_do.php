<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "StatsBuilder.php";

$db = dbc::connect();

// Récupération données équipe
$req = "UPDATE jb_matchs SET surleterrain".$choix_equipe."='".$selected_defenseur."|".$selected_attaquant."' WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$id_match;
$res = dbc::execSQL($req);

// Mise des statistiques de la journée
$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$stats->SQLUpdateClassementJournee();

mysql_close($db);

ToolBox::do_redirect("matchs.php?pkeys_where_jb_journees=+WHERE+id%3D".$sess_context->id_journee_encours);

?>
