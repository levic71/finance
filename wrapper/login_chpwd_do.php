<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$pwd   = Wrapper::getRequest('pwd1',  '123456789');
$token = Wrapper::getRequest('token', '');

$db = dbc::connect();

$select = "SELECT * FROM jb_users WHERE reset_token='".$token."';";
$res = dbc::execSQL($select);

$msg = "Modification failed !";
if ($row = mysqli_fetch_array($res))
{
	$update = "UPDATE jb_users SET password='".$pwd."', reset_time=0, reset_token='', reset_count=0 WHERE reset_token='".$token."'";
	$res = dbc::execSQL($update);
	$msg = "Modification prise en compte";
}

mysqli_close ($db);

?><span class="hack_ie">_HACK_IE_</span><script>mm({action: 'login', mobile: 0}); $aMsg({msg : '<?= $msg ?>' });</script>