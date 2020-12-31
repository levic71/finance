<?

include "../include/sess_context.php";

session_start();

include "common.php";

$sess_context->setLangue($choix_langue);

ToolBox::do_redirect("../www/championnat_home.php");

?>