<?

require_once "sess_context.php";

session_start();

include "common.php";

foreach (['alerte'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) uimx::redirectLoginPage('portfolio');

if ($alerte == -1)
	$req = "UPDATE alertes SET lue=1 WHERE user_id=".$sess_context->getUserId();
else {
	$infos = explode("|", $alerte);
	$req = "UPDATE alertes SET lue=1 WHERE user_id=".$sess_context->getUserId()." AND actif='".$infos[2]."' AND date='".$infos[0]."' AND type='".$infos[3]."'";
}

$res = dbc::execSql($req);

?>