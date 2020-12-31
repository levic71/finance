<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";

$db = dbc::connect();

// Insertion
$update = "UPDATE jb_actualites SET date='".ToolBox::date2mysqldate($date)."', resume='".$resume."', texte='".$texte."', lien='".$lien."', alaune='".$alaune."' WHERE id=".$id_item.";";
$res = dbc::execSQL($update);

mysql_close($db);

ToolBox::do_redirect("gestion_actualites.php");

?>
