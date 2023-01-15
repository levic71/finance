<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isUserConnected()) tools::do_redirect("index.php");

foreach(['action', 'symbol', 'f_stoploss', 'f_stopprofit', 'f_objectif', 'f_quote', 'f_seuils', 'f_active', 'options', 'f_strat_type', 'f_reg_type', 'f_reg_period'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if ($action == "stops") {

    $req = "
        INSERT INTO trend_following (user_id, symbol, stop_loss, stop_profit, objectif, seuils, options, active)
        VALUES (".$sess_context->getUserId().", '".$symbol."', '".sprintf("%2.f", $f_stoploss)."', '".sprintf("%2.f", $f_stopprofit)."', '".sprintf("%2.f", $f_objectif)."', '".sprintf("%s", $f_seuils)."', '".$options."', '".$f_active."')
        ON DUPLICATE KEY UPDATE
        stop_loss='".sprintf("%2.f", $f_stoploss)."', stop_profit='".sprintf("%2.f", $f_stopprofit)."', objectif='".sprintf("%2.f", $f_objectif)."', seuils='".sprintf("%s", $f_seuils)."', options='".$options."', active='".$f_active."'
    ";
    $res = dbc::execSql($req);

    echo $req;

    $req2 = "UPDATE stocks SET strategie_type=".$f_strat_type.", regression_type=".$f_reg_type.", regression_period='".$f_reg_period."'";
    $res2 = dbc::execSql($req2);

    echo $req2;
    exit(0);

}

if ($action == "manual_price") {

    $req = "
        INSERT INTO trend_following (user_id, symbol, manual_price)
        VALUES (".$sess_context->getUserId().", '".$symbol."', '".sprintf("%2.f", $f_quote)."')
        ON DUPLICATE KEY UPDATE
        manual_price='".sprintf("%2.f", $f_quote)."'
    ";
    $res = dbc::execSql($req);

}

calc::resetCacheUserPortfolio($sess_context->getUserId());


?>