<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";

$db = dbc::connect();

// Insertion
$insert = "INSERT INTO jb_forum (date, nom, title, message, smiley) VALUES ('".ToolBox::date2mysqldate($date)."', '".$nom."', '".$title."', '".$ta."', '../forum/smileys/smile.gif');";
$res = dbc::execSQL($insert);

mysql_close($db);

ToolBox::do_redirect("gestion_chroniques.php");

?>
