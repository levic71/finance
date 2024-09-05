<?php

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
// //////////////////////////////////////////

use Google\Service\AndroidEnterprise\WebApp;

include "include.php";

aafinance::$cache_load = true;

include "indicators.php";
include "googlesheet/sheet.php";

if (!is_dir("cache/")) mkdir("cache/");

$filter = "";

foreach (['filter'] as $key)
    $$key = isset($_GET[$key]) ? $_GET[$key] : (isset($$key) ? $$key : "");
    
$db = dbc::connect();

if (!$argv || count($argv) <= 2) { echo "paramètre manquant : symbol EFT/INDICE/Equity"; exit(0); }

$symbol = $argv[1];
$type   = $argv[2];

if (!$symbol || !$type) { echo "paramètre manquant ..."; exit(0); }

if ($type != "Equity" && $type != "ETF" && $type != "INDICE") { echo "type incorrect ..."; exit(0); }

cacheData::getAndInsertAllDataQuoteFromGS($symbol, $type);

?>