<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";

$db = dbc::connect();

// Insertion
$delete = "DELETE FROM jb_forum ".urldecode($pkeys_where);
$res = dbc::execSQL($delete);

mysql_close($db);

ToolBox::do_redirect("gestion_chroniques.php");

?>
