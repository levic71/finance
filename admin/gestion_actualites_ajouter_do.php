<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";

$db = dbc::connect();

// Insertion
$insert = "INSERT INTO jb_actualites (date, resume, texte, lien, alaune) VALUES ('".ToolBox::date2mysqldate($date)."', '".$resume."', '".$texte."', '".$lien."', '".$alaune."');";
$res = dbc::execSQL($insert);

mysql_close($db);

ToolBox::do_redirect("gestion_actualites.php");

?>
