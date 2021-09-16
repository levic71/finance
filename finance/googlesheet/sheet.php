<?php

require __DIR__ . '/vendor/autoload.php';

$client = new \Google_Client();
$client->setApplicationName('Google Sheets and PHP');
$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
$client->setAccessType('offline');
$client->setAuthConfig(__DIR__ . '/credentials.json');

$service = new Google_Service_Sheets($client);

$spreadsheetId = "1DuYV6Wbpg2evUdvL2X4VNo3T2bnNPBQzXEh92oj-3Xo";

$get_range = "actifs!B2:H20";

//Request to get data from spreadsheet.
$response = $service->spreadsheets_values->get($spreadsheetId, $get_range);
$values = $response->getValues();

var_dump($values);

// Clear values
$clear_range = 'actifs!A3:H1000'; 

$requestBody = new Google_Service_Sheets_ClearValuesRequest();
$response = $service->spreadsheets_values->clear($spreadsheetId, $clear_range, $requestBody);

var_dump($response);


// Update datas
$update_range = "actifs!B18:C20";

$values = [
	[ 'EPA:BNP', '=IF(ISBLANK($B18),"",GOOGLEFINANCE($B18,C$2))' ]
];

$body = new Google_Service_Sheets_ValueRange([
	'values' => $values
]);

$params = [
	'valueInputOption' => 'USER_ENTERED'
];

$update_sheet = $service->spreadsheets_values->update($spreadsheetId, $update_range, $body, $params);

var_dump($update_sheet);

?>
