<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

$insert = "INSERT INTO jb_email (email, date) VALUES ('".$email."', NOW());";
$res = dbc::execSQL($insert);

mysql_close ($db);

if ($sess_context->isChampionnatValide())
	ToolBox::do_redirect("../www/championnat_home.php");
else
	ToolBox::do_redirect("../www/home.php");

?>