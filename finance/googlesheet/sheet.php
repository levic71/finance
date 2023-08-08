<?php

require __DIR__ . '/vendor/autoload.php';

// if (!is_dir("cache/")) mkdir("cache/");

function updateGoogleSheet() {

	$ret = array();

	$onglet = stristr(__DIR__, "MAMP") ? "actifs-dev" : "actifs";

	$client = new \Google_Client();
	$client->setApplicationName('Google Sheets and PHP');
	$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
	$client->setAccessType('offline');
	$client->setAuthConfig(__DIR__ . '/credentials.json');

	$service = new Google_Service_Sheets($client);

	$spreadsheetId = "1DuYV6Wbpg2evUdvL2X4VNo3T2bnNPBQzXEh92oj-3Xo";

	$_range = $onglet."!A3:U1000";

	// Clear values
	$requestBody = new Google_Service_Sheets_ClearValuesRequest();
	$response = $service->spreadsheets_values->clear($spreadsheetId, $_range, $requestBody);

	// Update data : Mise a jour des valeurs recherchees
	$values = array();

	$req = "SELECT *, s.symbol symbol FROM stocks s LEFT JOIN quotes q ON s.symbol = q.symbol WHERE NOT s.gf_symbol = '' ORDER BY s.symbol";
	$res = dbc::execSql($req);
	$i = 3;
	while($row = mysqli_fetch_assoc($res)) {
		$symbol = $row['symbol'];
		$values[] = [
			$row['symbol'],
			$row['gf_symbol'],
			'=IF(ISBLANK($B'.$i.'),"",GOOGLEFINANCE($B'.$i.',C$2))',
			'=IF(ISBLANK($B'.$i.'),"",GOOGLEFINANCE($B'.$i.',D$2))',
			'=IF(ISBLANK($B'.$i.'),"",GOOGLEFINANCE($B'.$i.',E$2))',
			'=IF(ISBLANK($B'.$i.'),"",GOOGLEFINANCE($B'.$i.',F$2))',
			'=IF(ISBLANK($B'.$i.'),"",GOOGLEFINANCE($B'.$i.',G$2))',
			'=IF(ISBLANK($B'.$i.'),"",GOOGLEFINANCE($B'.$i.',H$2))',
			'=IF(ISBLANK($B'.$i.'),"",GOOGLEFINANCE($B'.$i.',I$2))',
			'=IF(ISBLANK($B'.$i.'),"",GOOGLEFINANCE($B'.$i.',J$2))',
			'=IF(ISBLANK($B'.$i.'),"",GOOGLEFINANCE($B'.$i.',K$2))',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			''
		];
		$i++;
	}

	$body = new Google_Service_Sheets_ValueRange([
		'values' => $values
	]);

	$params = [
		'valueInputOption' => 'USER_ENTERED'
	];

	$update_sheet = $service->spreadsheets_values->update($spreadsheetId, $_range, $body, $params);

	// Update data : Mise a jour de l'entete
	$values = array();
	$datetime = new DateTime();
	$datetime->setTimezone(new DateTimeZone('Europe/Paris'));
	$values[] = [ $datetime->format('Y-m-d H:i:s') ];

	$body = new Google_Service_Sheets_ValueRange([
		'values' => $values
	]);
	$update_sheet = $service->spreadsheets_values->update($spreadsheetId, $onglet."!A1:A1", $body, $params);

	// Reccuperation des data de finance une fois que google a fait ca maj automatiquement
	$response = $service->spreadsheets_values->get($spreadsheetId, $_range);
	$values = $response->getValues();

	if (!empty($values)) {
		foreach($values as $key => $val) {
			$ret[$val[0]] = $val;
		}
	}

	return $ret;
}

function updateGoogleSheetDevises() {

	$ret = array();

	$onglet = "devises";

	try {
		$client = new \Google_Client();
		$client->setApplicationName('Google Sheets and PHP');
		$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
		$client->setAccessType('offline');
		$client->setAuthConfig(__DIR__ . '/credentials.json');

		$service = new Google_Service_Sheets($client);

		$spreadsheetId = "1DuYV6Wbpg2evUdvL2X4VNo3T2bnNPBQzXEh92oj-3Xo";

		// Reccuperation des data de finance une fois que google a fait ca maj automatiquement
		$get_range = $onglet."!B2:C50";
		$response = $service->spreadsheets_values->get($spreadsheetId, $get_range);
		$values = $response->getValues();
	} catch(RuntimeException $e) { }

	if (!empty($values)) {
		foreach($values as $key => $val) {
			$ret[$val[0]] = $val;
		}
	}

	return $ret;
}

function updateGoogleSheetAlertesFX($range) {

	$ret = array();

	$onglet = "alertes";

	try {
		$client = new \Google_Client();
		$client->setApplicationName('Google Sheets and PHP');
		$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
		$client->setAccessType('offline');
		$client->setAuthConfig(__DIR__ . '/credentials.json');

		$service = new Google_Service_Sheets($client);

		$spreadsheetId = "1DuYV6Wbpg2evUdvL2X4VNo3T2bnNPBQzXEh92oj-3Xo";

		// Reccuperation des data de finance une fois que google a fait ca maj automatiquement
		$get_range = $onglet."!".$range;
		$response = $service->spreadsheets_values->get($spreadsheetId, $get_range);
		$values = $response->getValues();
	} catch(RuntimeException $e) { }

	if (!empty($values)) {
		foreach($values as $key => $val) {
			$ret[$val[1]] = $val;
		}
	}

	return $ret;
}

function updateGoogleSheetAlertes() {
	return updateGoogleSheetAlertesFX("A3:W100");
}

function updateGoogleSheetAlertesHeader() {
	return updateGoogleSheetAlertesFX("A2:W2");
}

function updateQuotesWithGSData($val) {

	$symbol = $val[0];

	$ret = "[No Symbol found] [updateQuotesWithGSData]";

	$req = "SELECT count(*) total FROM quotes WHERE symbol='".$symbol."'";
	$res = dbc::execSql($req);
	$row = mysqli_fetch_assoc($res);

	if ($row['total'] == 1 && is_numeric($val[2])) {

		// Si maj forcée le weekend
		if (date("N") > 5)
			$day = date("Y-m-d", strtotime(date("Y-m-d"). ' - '.(date('N') - 5).' days'));
		else
			$day = date("Y-m-d");

		// Mise à jour de la cotation dans quote et dans daily
		$req = "UPDATE quotes SET price='".$val[2]."', open='".$val[3]."', high='".$val[4]."', low='".$val[5]."', volume='".$val[6]."', previous='".$val[8]."', day_change='".$val[9]."', percent='".$val[10]."', day='".$day."' WHERE symbol='".$symbol."'";
		$res = dbc::execSql($req);
		$req = "INSERT INTO daily_time_series_adjusted (symbol, day, open, high, low, close, adjusted_close, volume, dividend, split_coef) VALUES ('".$symbol."','".$day."', '".$val[3]."', '".$val[4]."', '".$val[5]."', '".$val[2]."', '".$val[2]."', '".$val[6]."', '0', '0') ON DUPLICATE KEY UPDATE open='".$val[3]."', high='".$val[4]."', low='".$val[5]."', close='".$val[2]."', adjusted_close='".$val[2]."', volume='".$val[6]."', dividend='0', split_coef='0'";
		$res = dbc::execSql($req);
		$ret = "[QUOTES+DAILY_TIME_SERIES_ADJUSTED] [price='".$val[2]."', open='".$val[3]."', volume='".$val[6]."', percent='".$val[10]."', ... ]";

		logger::info("GSHEET", $symbol, $ret);
	}

	return $ret;
}

function updateAllQuotesWithGSData($values) {

	$ret = array();
	foreach($values as $key => $val) {

		$ret[] = updateQuotesWithGSData($val);

		computeQuoteIndicatorsSymbol($val[0]);
	}
	return $ret;
}


function setGoogleSheetStockSymbol($symbol, $onglet = "data") {

	$ret = array();

	$client = new \Google_Client();
	$client->setApplicationName('Google Sheets and PHP');
	$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
	$client->setAccessType('offline');
	$client->setAuthConfig(__DIR__ . '/credentials.json');

	$service = new Google_Service_Sheets($client);

	$spreadsheetId = "1V0Mj1-qdHoKDOZ7BWP7AHQUdTDEU1OmqDwwK7354wvc";

	// Update data : Mise a jour de l'entete
	$update_range = $onglet."!A1:A1";
	$values = array();
	$datetime = new DateTime();
	$datetime->setTimezone(new DateTimeZone('Europe/Paris'));
	$values[] = [ $symbol ];

	$body = new Google_Service_Sheets_ValueRange([
		'values' => $values
	]);

	$params = [
		'valueInputOption' => 'USER_ENTERED'
	];

	$update_sheet = $service->spreadsheets_values->update($spreadsheetId, $update_range, $body, $params);
	
	return $ret;
}

function getGoogleSheetStockData($range, $onglet = "data") {

	$ret = array();

	try {
		$client = new \Google_Client();
		$client->setApplicationName('Google Sheets and PHP');
		$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
		$client->setAccessType('offline');
		$client->setAuthConfig(__DIR__ . '/credentials.json');

		$service = new Google_Service_Sheets($client);

		$spreadsheetId = "1V0Mj1-qdHoKDOZ7BWP7AHQUdTDEU1OmqDwwK7354wvc";

		// Reccuperation des data de finance une fois que google a fait ca maj automatiquement
		$get_range = $onglet."!".$range;
		$response = $service->spreadsheets_values->get($spreadsheetId, $get_range);
		$ret = $response->getValues();
	} catch(RuntimeException $e) { }

	return $ret;
}


// FORCE Computing
$force = 0;

foreach(['force'] as $key)
    $$key = isset($_GET[$key]) ? $_GET[$key] : (isset($$key) ? $$key : "");

foreach(['force'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

if ($force == 1) {

	require_once "../include.php";
	require_once "../indicators.php";

	// Le fichier de log est dans le repertoire au dessus
	ini_set('error_log', '../finance.log');

	$db = dbc::connect();

	if (tools::useGoogleFinanceService()) $values = updateGoogleSheet();

	if ($force == 1) var_dump($values);

	$ret = updateAllQuotesWithGSData($values);

	foreach($ret as $key => $val) logger::info("SHEET", 'QUOTE', $val);

	// On supprime les fichiers cache tmp
	cacheData::deleteTMPFiles();

}

?>
