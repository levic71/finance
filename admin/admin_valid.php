<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";

$db = dbc::connect();
$select = "SELECT * FROM jb_championnat WHERE id = '".$sess_context->getRealChampionnatId()."' AND login='".$login."' AND pwd='".$pwd."';";
$res = dbc::execSQL($select);
$row = mysql_fetch_array($res);
if ($row || ($sess_context->isSuperUser() && true))
{
	$sess_context->setAdmin();

	Toolbox::trackUser($sess_context->getRealChampionnatId(), _TRACK_ADMIN_);
	ToolBox::do_redirect("superuser_fcts.php?ref_champ=".$sess_context->getRealChampionnatId());
}
else
	ToolBox::do_redirect("superuser.php?admin_valid=no");

mysql_close ($db);

?>
