<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";

$db = dbc::connect();

// Insertion
$insert = "INSERT INTO jb_forum (date, nom, title, message) VALUES ('".ToolBox::date2mysqldate($date)."', '".$nom."', '".$title."', '".$ta."');";
$res = dbc::execSQL($insert);

mysql_close($db);

ToolBox::do_redirect("gestion_astuces.php");

?>
