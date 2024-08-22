<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
// //////////////////////////////////////////



include "include.php";

aafinance::$cache_load = true;

include "indicators.php";
include "googlesheet/sheet.php";

ini_set('max_execution_time', '3600'); // 3600 seconds = 60 minutes
error_reporting(E_ALL); // Error/Exception engine, always use E_ALL
ini_set('ignore_repeated_errors', TRUE); // always use TRUE
ini_set('display_errors', FALSE); // Error/Exception display, use FALSE only in production environment or real server. Use TRUE in development environment
ini_set('log_errors', TRUE); // Error/Exception file logging engine.
ini_set('error_log', './finance.log'); // Logging file path

// Overwrite include value
$dbg = false;

if (!is_dir("cache/")) mkdir("cache/");

$filter = "%";

foreach (['filter'] as $key)
    $$key = isset($_GET[$key]) ? $_GET[$key] : (isset($$key) ? $$key : "");
    
$db = dbc::connect();

$values = array();

logger::info("SCRIPT.PHP", "BEGIN", "###########################################################");
exit(0);

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