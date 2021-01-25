<?

require_once "../include/sess_context.php";
session_start();
include "common.php";
header('Content-Type: text/html; charset='.sess_context::xhr_charset);

if ($sess_context->isUserConnected()) {
	$sess_context->resetUserConnection();
	$sess_context->resetAdmin();
} 

ToolBox::do_redirect("jk.php");

?>