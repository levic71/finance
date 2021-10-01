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

	// Clear values
	$clear_range = $onglet.'!A3:H1000'; 

	$requestBody = new Google_Service_Sheets_ClearValuesRequest();
	$response = $service->spreadsheets_values->clear($spreadsheetId, $clear_range, $requestBody);

	// Update datas
	$update_range = $onglet."!A3:K200";
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
			'=IF(ISBLANK($B'.$i.'),"",GOOGLEFINANCE($B'.$i.',K$2))'
		];
		$i++;
	}

	$body = new Google_Service_Sheets_ValueRange([
		'values' => $values
	]);

	$params = [
		'valueInputOption' => 'USER_ENTERED'
	];

	$update_sheet = $service->spreadsheets_values->update($spreadsheetId, $update_range, $body, $params);

	// Update datas
	$update_range = $onglet."!A1:A1";
	$values = array();
	$datetime = new DateTime();
	$datetime->setTimezone(new DateTimeZone('Europe/Paris'));
	$values[] = [ $datetime->format('Y-m-d H:i:s') ];

	$body = new Google_Service_Sheets_ValueRange([
		'values' => $values
	]);
	$update_sheet = $service->spreadsheets_values->update($spreadsheetId, $update_range, $body, $params);
	

	// Request to get data from spreadsheet
	$get_range = $onglet."!A3:K200";
	$response = $service->spreadsheets_values->get($spreadsheetId, $get_range);
	$values = $response->getValues();

	if (!empty($values)) {
		foreach($values as $key => $val) {
			$ret[$val[0]] = $val;
		}
	}

	return $ret;
}




function updateQuotesWithGSData($val) {

	$ret = "[No Symbol found] [updateQuotesWithGSData]";

	$req = "SELECT count(*) total FROM quotes WHERE symbol='".$val[0]."'";
	$res = dbc::execSql($req);
	$row = mysqli_fetch_assoc($res);

	if ($row['total'] == 1 && is_numeric($val[2])) {

		$req = "UPDATE quotes SET price='".$val[2]."', open='".$val[3]."', high='".$val[4]."', low='".$val[5]."', volume='".$val[6]."', previous='".$val[8]."', day_change='".$val[9]."', percent='".$val[10]."', day='".date("Y-m-d")."' WHERE symbol='".$val[0]."'";
		$ret = "[price='".$val[2]."', open='".$val[3]."', high='".$val[4]."', low='".$val[5]."', volume='".$val[6]."', previous='".$val[8]."', day_change='".$val[9]."', percent='".$val[10]."']";
		$res = dbc::execSql($req);
		computeIndicators($val[0], 0, 0);
	}

	return $ret;
}

function updateAllQuotesWithGSData($values) {

	$ret = array();
	foreach($values as $key => $val) $ret[] = updateQuotesWithGSData($val);
	return $ret;
}

// FORCE Computing
$force = 0;

foreach(['force'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");


if ($force == 1) {
	require_once "../include.php";
	require_once "../indicators.php";

	$db = dbc::connect();
	$values = updateGoogleSheet();
	$ret = updateAllQuotesWithGSData($values);

	foreach($ret as $key => $val) logger::info("SHEET", 'QUOTE', $val);

}

?>
