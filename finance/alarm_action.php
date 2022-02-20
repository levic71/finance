<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isUserConnected()) tools::do_redirect("index.php");

foreach(['action', 'symbol', 'f_stoploss', 'f_stopprofit'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if ($action == "stops") {

    $req = "INSERT INTO alarms (user_id, symbol, type, date, valeur) VALUES (".$sess_context->getUserId().", '".$symbol."', 'STOP-LOSS', now(), '".$f_stop."') ON DUPLICATE KEY UPDATE date=now(), valeur='".$f_stoploss."'";
    $res = dbc::execSql($req);

    $req = "INSERT INTO alarms (user_id, symbol, type, date, valeur) VALUES (".$sess_context->getUserId().", '".$symbol."', 'STOP-PROFIT', now(), '".$f_stop."') ON DUPLICATE KEY UPDATE date=now(), valeur='".$f_stopprofit."'";
    $res = dbc::execSql($req);

}

?>