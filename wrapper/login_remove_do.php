<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

if (!$sess_context->isUserConnected()) exit(0);

$user = $sess_context->getUser();

$select = "SELECT * FROM jb_users WHERE removed=0 AND id=".$user['id'];
$res = dbc::execSQL($select);
if ($row = mysqli_fetch_array($res))
{
	$update = "UPDATE jb_users SET removed=1 WHERE id=".$row['id'];
	$res = dbc::execSQL($update);

	$sess_context->resetUserConnection();
	$sess_context->resetAdmin();
}

mysqli_close ($db);

ToolBox::do_redirect("jk.php");

?>