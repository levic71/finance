<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$params = Wrapper::getRequest('params', '');

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

if ($params == "") { echo "-1||Erreur paramètres"; exit(0); }

$tmp = explode("|", $params);
$idc   = isset($tmp[0]) ? $tmp[0] : sess_context::INVALID_CHAMP_ID_HOME;
$login = isset($tmp[1]) ? $tmp[1] : '';
$pwd   = isset($tmp[2]) ? $tmp[2] : '';

$db = dbc::connect();

$sess_context->resetUserConnection();
$sess_context->resetAdmin();

$select = "SELECT * FROM jb_users WHERE removed =0 AND login='".$login."' AND pwd='".$pwd."';";
$res = dbc::execSQL($select);
if ($row = mysqli_fetch_array($res))
{
	$sess_context->setUserConnection($row);
	setcookie("login_user", str_replace('\\\'', '\'', $login), time()+(3600*24*30*6));

	$status = 1;

	if ($sess_context->isSuperAdmin()) { $status = 2; $sess_context->setAdmin(); }

	$select = "SELECT * FROM jb_roles WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id_user=".$sess_context->user['id'].";";
	$res = dbc::execSQL($select);
	if ($row2 = mysqli_fetch_array($res))
	{
		if ($row2['role'] == _ROLE_ADMIN_) { $status = 2; $sess_context->setAdmin(); }
	//	Toolbox::trackUser($sess_context->getRealChampionnatId(), _TRACK_ADMIN_);
	}

	echo 	$status."||Bienvenue ".$row['pseudo'];
}
else
	echo "-1||Paramètres non valide";

mysqli_close ($db);

?>
