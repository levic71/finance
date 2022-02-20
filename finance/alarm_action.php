<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isUserConnected()) tools::do_redirect("index.php");

foreach(['action', 'symbol', 'f_stoploss', 'f_stopprofit'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if ($action == "stops") {

    $req = "INSERT INTO alarms (user_id, symbol, type, date, valeur) VALUES (".$sess_context->getUserId().", '".$symbol."', 'STOP-LOSS', now(), '".sprintf("%2.f", $f_stoploss)."') ON DUPLICATE KEY UPDATE date=now(), valeur='".sprintf("%2.f", $f_stoploss)."'";
    $res = dbc::execSql($req);

    $req = "INSERT INTO alarms (user_id, symbol, type, date, valeur) VALUES (".$sess_context->getUserId().", '".$symbol."', 'STOP-PROFIT', now(), '".sprintf("%2.f", $f_stopprofit)."') ON DUPLICATE KEY UPDATE date=now(), valeur='".sprintf("%2.f", $f_stopprofit)."'";
    $res = dbc::execSql($req);

}

?>