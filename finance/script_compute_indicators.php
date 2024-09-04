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
    
$db = dbc::connect();

$time_series_tables = [ "daily" => "daily_time_series_adjusted", "weekly" => "weekly_time_series_adjusted", "monthly" => "monthly_time_series_adjusted" ];

// ////////////////////////////////////////////////////////
// Parcours des actifs suivis
// ////////////////////////////////////////////////////////
$filter = "%";

$req = "SELECT * FROM stocks WHERE symbol LIKE '".$filter."' ORDER BY symbol";
$res = dbc::execSql($req);

while($row = mysqli_fetch_array($res)) {

	
	foreach($time_series_tables as $serie => $table) {

		$data = [];

		$req2 = "SELECT * FROM ".$table." WHERE symbol='".$row['symbol']."' ORDER BY day ASC";
		$res2 = dbc::execSql($req2);

		while($row2 = mysqli_fetch_array($res2)) $data[] = $row2;

		$ret = computeIndicatorsAndInsertIntoBD($row['symbol'], $data, $serie, 0);

		logger::info("SCRIPT", $row['symbol'], "[computeIndicatorsAndInsertIntoBD] [".sprintf("%7s", $serie)."] [".sprintf("%8d", $ret)." item(s)]");
	}

}

cacheData::deleteTMPFiles();

?>