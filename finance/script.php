<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
// //////////////////////////////////////////



include "include.php";

aafinance::$cache_load = true;

include "indicators.php";
include "googlesheet/sheet.php";

ini_set('max_execution_time', '300'); //300 seconds = 5 minutes

// Overwrite include value
$dbg = false;

if (!is_dir("cache/")) mkdir("cache/");

$filter = "%";

foreach (['filter'] as $key)
    $$key = isset($_GET[$key]) ? $_GET[$key] : (isset($$key) ? $$key : "");
    
$db = dbc::connect();

$values = array();

echo "###########################################################\n";

// ////////////////////////////////////////////////////////
// Parcours des actifs suivis
// ////////////////////////////////////////////////////////
// $req = "SELECT * FROM stocks ORDER BY symbol";
$req = "SELECT * FROM stocks WHERE symbol LIKE '".$filter."' ORDER BY symbol";
echo $req."\n";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

    echo $row['symbol']."\n";

    $limited_computing = 0;

    foreach(['weekly_time_series_adjusted', 'monthly_time_series_adjusted'] as $key) {
        $req2 = "DELETE FROM ".$key." WHERE symbol='".$row['symbol']."'";
        $res2 = dbc::execSql($req2);    
    }    
    $req2 = "DELETE FROM indicators WHERE symbol='".$row['symbol']."' AND period='WEEKLY'";
    $res2 = dbc::execSql($req2);    
    $req2 = "DELETE FROM indicators WHERE symbol='".$row['symbol']."' AND period='MONTHLY'";
    $res2 = dbc::execSql($req2);    

    // Mise a jour des caches : full = false => compact (aucun impact sur le calcul des indicateurs) 
    $ret = cacheData::buildWeekendCachesSymbol($row['symbol'], false);

    // Mise a jour des caches : full = false => compact (aucun impact sur le calcul des indicateurs) 
    $ret = cacheData::buildWeekendCachesSymbol($row['symbol'], true);


    if ($ret['weekly'])
        computePeriodIndicatorsSymbol($row['symbol'], $limited_computing, "WEEKLY");
    else
        echo "[computeWeeklyIndicators] [Cache] [No computing]\n";

    if ($ret['monthly'])
        computePeriodIndicatorsSymbol($row['symbol'], $limited_computing, "MONTHLY");
    else
        echo "[computeMonthlyIndicators] [Cache] [No computing]\n";

}

echo "###########################################################\n";

cacheData::deleteTMPFiles();

echo "Done.\n";