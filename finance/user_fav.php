<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isUserConnected()) tools::do_redirect("index.php");

foreach(['symbol', 'action'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

$req = "SELECT * FROM users WHERE id=".$sess_context->getUserId();
$res = dbc::execSql($req);

if ($row = mysqli_fetch_array($res)) {

    if ($action == "add") {
        $favoris = $row['favoris']."|".$symbol;
    } else {
        $t = array_flip(explode('|', $row['favoris']));
        if (isset($t[$symbol])) unset($t[$symbol]);
        $favoris = implode("|", array_keys($t));
    }

    $req = "UPDATE users SET favoris='".$favoris."' WHERE id=".$sess_context->getUserId();
    $res = dbc::execSql($req);

    $user = $sess_context->getUser();
    $user['favoris'] = $favoris;
    $sess_context->setUserConnection($user);

}

?>