<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";

$db = dbc::connect();

// Insertion
$insert = "INSERT INTO jb_videos (date, titre, description, url) VALUES ('".ToolBox::date2mysqldate($date)."', '".$titre."', '".$desc."', '".$url."');";
$res = dbc::execSQL($insert);

mysql_close($db);

ToolBox::do_redirect("gestion_videos.php");

?>
