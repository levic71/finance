<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";

$sess_context->resetAdmin();

ToolBox::do_redirect("../www/championnat_acces.php");

?>