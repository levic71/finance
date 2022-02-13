<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
// //////////////////////////////////////////

include "include.php";
include "indicators.php";
include "googlesheet/sheet.php";

ini_set('max_execution_time', '300'); //300 seconds = 5 minutes

// Overwrite include value
$dbg = false;

if (!is_dir("cache/")) mkdir("cache/");

$db = dbc::connect();

// Purge log file
logger::purgeLogFile("./finance.log", 5*1048576);

$values = array();

// ////////////////////////////////////////////////////////
// Mise à jour des valeurs de cotations dans Google Sheet
// ////////////////////////////////////////////////////////
if (tools::useGoogleFinanceService()) $values = updateGoogleSheet();

// Updates all quotes whith GS
// updateAllQuotesWithGSData($values);
// exit(0);

?> <div class="ui container inverted segment"><?

logger::info("CRON", "BEGIN", "###########################################################");

// ////////////////////////////////////////////////////////
// Parcours des actifs suivis
// ////////////////////////////////////////////////////////
$req = "SELECT * FROM stocks ORDER BY symbol";
// $req = "SELECT * FROM stocks WHERE symbol LIKE 'C%' ORDER BY symbol";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

    $full_data = true;     // false => COMPACT, true => FULL
    $limited_computing = 0; // 0 => pas de limite, 1 => on calcule que sur les 300 dernières valeurs
    // bug si 1 !!!!

    if (cacheData::isMarketOpen($row['timezone'], $row['marketopen'], $row['marketclose'])) {

        if (aafinance::$cache_load) {
            $full_data = true;
            $limited_computing = 0;
            foreach(['daily_time_series_adjusted'] as $key) {
                $req2 = "DELETE FROM ".$key." WHERE symbol='".$row['symbol']."'";
                $res2 = dbc::execSql($req2);
            }    
            $req2 = "DELETE FROM indicators WHERE symbol='".$row['symbol']."' AND period='DAILY'";
            $res2 = dbc::execSql($req2);    
    }

        // Mise a jour des caches : Si full = false => compact (aucun impact sur le calcul des indicateurs) 
        $ret = cacheData::buildDailyCachesSymbol($row['symbol'], $full_data);

        // Mise à jour des data daily
        if ($ret['daily'])
            computePeriodIndicatorsSymbol($row['symbol'], $limited_computing, "DAILY");
        else
            logger::info("INDIC", $row['symbol'], "[computeDailyIndicators] [Cache] [No computing]");

        // Mise à jour de la cote de l'actif avec la donnée GSheet
        if (isset($values[$row['symbol']])) {
            $ret['gsheet'] = updateQuotesWithGSData($values[$row['symbol']]);

            // Mise a jour des indicateurs du jour (avec quotes)
            computeQuoteIndicatorsSymbol($row['symbol']);
        }
        else
            logger::info("GSHEET", $row['symbol'], "[updateQuotesWithGSData] [No data found] [No update]");

    } else {

        // Si l'option cache load est positionnee
        if (aafinance::$cache_load) {
            $full_data = true;
            $limited_computing = 0;
            foreach(['weekly_time_series_adjusted', 'monthly_time_series_adjusted'] as $key) {
                $req2 = "DELETE FROM ".$key." WHERE symbol='".$row['symbol']."'";
                $res2 = dbc::execSql($req2);    
            }    
            $req2 = "DELETE FROM indicators WHERE symbol='".$row['symbol']."' AND period='WEEKLY'";
            $res2 = dbc::execSql($req2);    
            $req2 = "DELETE FROM indicators WHERE symbol='".$row['symbol']."' AND period='MONTHLY'";
            $res2 = dbc::execSql($req2);    
        }

        // On forece à true !!!!
        $full_data = true;

        // Mise a jour des caches : full = false => compact (aucun impact sur le calcul des indicateurs) 
         $ret = cacheData::buildWeekendCachesSymbol($row['symbol'], $full_data);

        if ($ret['weekly'])
            computePeriodIndicatorsSymbol($row['symbol'], $limited_computing, "WEEKLY");
        else
            logger::info("INDIC", $row['symbol'], "[computeWeeklyIndicators] [Cache] [No computing]");

        if ($ret['monthly'])
            computePeriodIndicatorsSymbol($row['symbol'], $limited_computing, "MONTHLY");
        else
            logger::info("INDIC", $row['symbol'], "[computeMonthlyIndicators] [Cache] [No computing]");
    }

    logger::info("CRON", "---------", "---------------------------------------------------------");
}

if (tools::isLocalHost()) cacheData::deleteTMPFiles();

logger::info("CRON", "END", "###########################################################");

echo "Done.";

?>

</div>