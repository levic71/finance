<?

$ticker_array = array();
foreach (range('a', 'z') as $letter) {
    $url = "https://query1.finance.yahoo.com/v1/finance/lookup?formatted=true&lang=en-US&region=US&query=$letter*&type=equity&count=3000&start=0";
    $data = json_decode(file_get_contents($url), true);
    foreach ($data['finance']['result'][0]['documents'] as $ticker) {
        $ticker_array[] = $ticker['symbol'];
    }
}
$fp = fopen("tickers.json", "w");
fwrite($fp, json_encode($ticker_array));
fclose($fp);

?>