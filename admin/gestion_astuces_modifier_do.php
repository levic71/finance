<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";

$db = dbc::connect();

// Insertion
$update = "UPDATE jb_forum SET date='".ToolBox::date2mysqldate($date)."', nom='".$nom."', title='".$title."', message='".$ta."' WHERE id=".$id_item.";";
$res = dbc::execSQL($update);

mysql_close($db);

ToolBox::do_redirect("gestion_astuces.php");

?>
