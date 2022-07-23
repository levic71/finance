<?

require_once "sess_context.php";

session_start();

include "common.php";

$portfolio_id = 0;

foreach (['alerte'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) {
	uimx::staticInfoMsg("VOUS DEVEZ ETRE CONNECTE POUR UTILISER CETTE FONCTIONNALITE", "comment outline", "blue");
	exit(0);
}

$infos = explode("|", $alerte);

// $req = "UPDATE alertes SET lue=1 WHERE user_id=".$sess_context->getUserId()." AND actif='".$infos[2]."' AND date='".$infos[0]."' AND type='".$infos[3]."'";
$req = "UPDATE alertes SET lue=1 WHERE actif='".$infos[2]."' AND date='".$infos[0]."' AND type='".$infos[3]."'";
$res = dbc::execSql($req);
 
echo $req;

?>