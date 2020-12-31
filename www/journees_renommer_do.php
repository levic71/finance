<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

// On met à jour la journée
$update = "UPDATE jb_journees SET nom='".$nom."' WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getJourneeId();
$res = dbc::execSQL($update);

mysql_close ($db);

ToolBox::do_redirect("matchs.php");

?>