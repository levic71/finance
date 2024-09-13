<?php

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
// Execution CRON en semaine uniquement, 1 x le matin, 1 x le midi et 1 x le soir
// //////////////////////////////////////////

use Google\Service\AndroidEnterprise\WebApp;

include "include.php";

aafinance::$cache_load = true;

include "indicators.php";
include "googlesheet/sheet.php";

if (!is_dir("cache/")) mkdir("cache/");
    
$db = dbc::connect();

logger::info("SCRIPT", "#####", "###########################################################");
logger::info("SCRIPT", "BEGIN", "[".sprintf("%40s", "script_compute_indicators")."]");

// ////////////////////////////////////////////////////////
// Parcours des actifs suivis
// ////////////////////////////////////////////////////////
$filter = "%%";
$nb_quotes = 0;

$req = "SELECT * FROM stocks WHERE symbol LIKE '".$filter."' ORDER BY symbol";
$res = dbc::execSql($req);

while($row = mysqli_fetch_array($res)) {

	computeDWMIndicators($row['symbol'], $row['engine']);
	$nb_quotes++;

}

logger::info("SCRIPT", "END", "[".sprintf("%40s", "script_compute_indicators")."] [".sprintf("%7d", $nb_quotes)."] [".sprintf("%8s", "item(s)")."]");

cacheData::deleteTMPFiles();

?>