<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isUserConnected()) tools::do_redirect("index.php");

foreach(['action', 'symbol', 'f_stoploss', 'f_stopprofit'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if ($action == "stops") {

    $req = "
        INSERT INTO trend_following (user_id, symbol, stop_loss, stop_profit)
        VALUES (".$sess_context->getUserId().", '".$symbol."', '".sprintf("%2.f", $f_stoploss)."', '".sprintf("%2.f", $f_stopprofit)."')
        ON DUPLICATE KEY UPDATE
        stop_loss='".sprintf("%2.f", $f_stoploss)."', stop_profit='".sprintf("%2.f", $f_stopprofit)."'
    ";

    $res = dbc::execSql($req);

}

?>