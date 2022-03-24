<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isUserConnected()) tools::do_redirect("index.php");

foreach(['action', 'symbol', 'f_stoploss', 'f_stopprofit', 'f_objectif', 'f_quote'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

// SQL SCHEMA UPDATE
$ret = dbc::addColTable("trend_following", "objectif", "ALTER TABLE `trend_following` ADD `objectif` VARCHAR(16) NOT NULL AFTER `stop_profit`");
$ret = dbc::delColTable("trend_following", "quotes", "ALTER TABLE `portfolios` DROP `quotes`;");


if ($action == "stops") {

    $req = "
        INSERT INTO trend_following (user_id, symbol, stop_loss, stop_profit, objectif)
        VALUES (".$sess_context->getUserId().", '".$symbol."', '".sprintf("%2.f", $f_stoploss)."', '".sprintf("%2.f", $f_stopprofit)."', '".sprintf("%2.f", $f_objectif)."')
        ON DUPLICATE KEY UPDATE
        stop_loss='".sprintf("%2.f", $f_stoploss)."', stop_profit='".sprintf("%2.f", $f_stopprofit)."', objectif='".sprintf("%2.f", $f_objectif)."'
    ";

}

if ($action == "manual_price") {

    $req = "
        INSERT INTO trend_following (user_id, symbol, manual_price)
        VALUES (".$sess_context->getUserId().", '".$symbol."', '".sprintf("%2.f", $f_quote)."')
        ON DUPLICATE KEY UPDATE
        manual_price='".sprintf("%2.f", $f_quote)."'
    ";

}

$res = dbc::execSql($req);

?>