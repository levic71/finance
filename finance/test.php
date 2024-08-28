<?php

require_once "sess_context.php";
include "indicators.php";
include "googlesheet/sheet.php";

ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
session_start();

include "common.php";

date_default_timezone_set("Europe/Paris");

// ini_set('display_errors', false);
ini_set('error_log', './finance.log');

//header( 'content-type: text/html; charset=iso-8859-1' );
header( 'content-type: text/html; charset=iso-8859-1' );
header('Access-Control-Allow-Origin: *');

$symbol = "INDEXEURO.PX1";
$gf_symbol = "INDEXEURO:PX1";

// Init de l'object Stock recherché
$stock = [];
$stock['gf_symbol']   = $gf_symbol;
$stock['symbol']      = $symbol;
$stock['type']        = "INDICE";
$stock['region']      = "Europe";
$stock['engine']      = "google";
$stock['marketopen']  = "09:00";
$stock['marketclose'] = "17:30";
$stock['timezone']    = "UTC+01";
$stock_histo = [];

// Affectation du symbol recherche dans la feuille de calcul
setGoogleSheetStockSymbol($stock['gf_symbol'], "daily");

// Pause pour laisser le temps a GS de bosser
sleep(5);

// Recuperation du nombre de ligne de cotation daily
$ret = getGoogleSheetStockData("A3", "daily");
$nb = $ret[0][0];

if ($nb > 1) {

    // Recuperation infos actif 
    $ret = getGoogleSheetStockData("C1:W2", "daily");

    // Si tradetime non existant bye bye
    if ($ret[1][0] == "#N/A") { echo "bye bye"; exit(0); }

    // RAZ data
    calc::removeSymbol($stock['symbol']);

    // Creation de l'objet stock avec les valeurs recuperees
    foreach(range(0, 20) as $i) $stock[$ret[0][$i]] = $ret[1][$i];
    
    $req = "INSERT INTO stocks (symbol, gf_symbol, name, type, region, marketopen, marketclose, timezone, currency, engine) VALUES ('".$stock['symbol']."', '".$stock['gf_symbol']."', '".addslashes($stock['name'])."', '".$stock['type']."', '".$stock['region']."', '".$stock['marketopen']."', '".$stock['marketclose']."', '".$stock['timezone']."', '".$stock['currency']."', '".$stock['engine']."')";
    $res = dbc::execSql($req);

    $req = "INSERT INTO quotes (symbol, open, high, low, price, volume, day, previous, day_change, percent) VALUES ('".$stock['symbol']."','".str_replace(',', '.', $stock['priceopen'])."', '".str_replace(',', '.', $stock['high'])."', '".str_replace(',', '.', $stock['low'])."', '".str_replace(',', '.', $stock['price'])."', '".str_replace(',', '.', $stock['volume'])."', '".substr($stock['tradetime'], 6, 4)."-".substr($stock['tradetime'], 3, 2)."-".substr($stock['tradetime'], 0, 2)."', '".str_replace(',', '.', $stock['closeyest'])."', '".str_replace(',', '.', $stock['change'])."', '".str_replace(',', '.', $stock['changepct'])."')";
    $res = dbc::execSql($req);

    // Recuperation historique cotation actif en daily
    $ret = getGoogleSheetStockData("C3:H".($nb+2), "daily");

    $col_names = [];
    foreach($ret as $key => $val) {

        if ($key == 0) {

            // Recuperation des noms de colonnes
            foreach(range(0, 5) as $i) $col_names[$i] = $ret[$key][$i];

        } else {

            $stock_histo['symbol'] = $symbol;

            foreach(range(0, 5) as $i) $stock_histo[$col_names[$i]] = $ret[$key][$i];

            $date  = substr($stock_histo['Date'], 6, 4)."-".substr($stock_histo['Date'], 3, 2)."-".substr($stock_histo['Date'], 0, 2);
            $close = str_replace(',', '.', $stock_histo['Close']);
            $open  = str_replace(',', '.', $stock_histo['Open']);
            $high  = str_replace(',', '.', $stock_histo['High']);
            $low   = str_replace(',', '.', $stock_histo['Low']);
            $vol   = str_replace(',', '.', $stock_histo['Volume']);
            
            $req = "INSERT INTO daily_time_series_adjusted (symbol, day, open, high, low, close, adjusted_close, volume, dividend, split_coef) VALUES ('".$stock_histo['symbol']."','".$date."', '".$open."', '".$high."', '".$low."', '".$close."', '".$close."', '".$vol."', '0', '0') ON DUPLICATE KEY UPDATE open='".$open."', high='".$high."', low='".$low."', close='".$close."', adjusted_close='".$close."', volume='".$vol."', dividend='0', split_coef='0'";
            $res = dbc::execSql($req);
            
        }

    }

}

// Recalcul des indicateurs en fct maj cache
computeIndicatorsForSymbolWithOptions($symbol, array("aggregate" => true, "limited" => 0, "periods" => ['DAILY', 'WEEKLY', 'MONTHLY']));

// Mise à jour de la cote de l'actif avec la donnée GSheet
if ($engine != "google" && isset($values[$symbol])) {
    $ret['gsheet'] = updateQuotesWithGSData($values[$symbol]);

    // Mise a jour des indicateurs du jour (avec quotes)
    computeDailyIndicatorsSymbol($symbol);
}

// On supprime les fichiers cache tmp
cacheData::deleteTMPFiles();



