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
if (tools::useGoogleFinanceService()) {

    // Recuperation des cotations Google Sheet
    $values = updateGoogleSheet();

    // Recuperation des devises Google Sheet et mise en cache
    $devises = calc::getGSDevises();

    // Recuperatoin des alertes Google Sheet et mise en cache
    $alertes = calc::getGSAlertes();
}

// Updates all quotes whith GS
// updateAllQuotesWithGSData($values);
// exit(0);

?> <div class="ui container inverted segment"><?

logger::info("CRON", "BEGIN", "###########################################################");

$full_data = true;      // false => COMPACT, true => FULL
$limited_computing = 0; // 0 => pas de limite, 1 => on calcule que sur les 300 dernières valeurs

$counter = 0;

// ////////////////////////////////////////////////////////
// Parcours des actifs suivis
// ////////////////////////////////////////////////////////
$req = "SELECT * FROM stocks ORDER BY symbol";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

    if (cacheData::isMarketOpen($row['timezone'], $row['marketopen'], $row['marketclose'])) {

/*

        // Mise à jour journaliere des cotations
        
        if (aafinance::$cache_load) {
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
*/

        // Mise à jour de la cote de l'actif avec la donnée GSheet
        if (isset($values[$row['symbol']])) {
            $ret['gsheet'] = updateQuotesWithGSData($values[$row['symbol']]);

            // Mise a jour des indicateurs du jour (avec quotes)
            computeQuoteIndicatorsSymbol($row['symbol']);

            if ($counter++ <= 20 && $row['date_update'] != date('Y-m-d')) {
                computeIndicatorsForSymbolWithOptions($row['symbol'], array("aggregate" => true, "limited" => 0, "periods" => ['DAILY']));
                $req2 = "UPDATE stocks SET date_update='".date('Y-m-d')."' WHERE symbol='".$row['symbol']."'";
                $res2 = dbc::execSql($req2);
                logger::info("CRON", $row['symbol'], "[computeIndicatorsForSymbolWithOptions] OK");
            } else
                logger::info("CRON", $row['symbol'], "[computeIndicatorsForSymbolWithOptions] PASS");
    
        }
        else
            logger::info("GSHEET", $row['symbol'], "[updateQuotesWithGSData] [No data found] [No update]");

    } else {
/*
        // Si l'option cache load est positionnee
        if (aafinance::$cache_load) {
            foreach(['weekly_time_series_adjusted', 'monthly_time_series_adjusted'] as $key) {
                $req2 = "DELETE FROM ".$key." WHERE symbol='".$row['symbol']."'";
                $res2 = dbc::execSql($req2);    
            }    
            $req2 = "DELETE FROM indicators WHERE symbol='".$row['symbol']."' AND period='WEEKLY'";
            $res2 = dbc::execSql($req2);    
            $req2 = "DELETE FROM indicators WHERE symbol='".$row['symbol']."' AND period='MONTHLY'";
            $res2 = dbc::execSql($req2);    
        }

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
*/
        
        if ($row['date_update'] != date('Y-m-d')) {
            computeIndicatorsForSymbolWithOptions($row['symbol'], array("aggregate" => true, "limited" => 0, "periods" => ['WEEKLY', 'MONTHLY']));
            $req2 = "UPDATE stocks SET date_update='".date('Y-m-d')."' WHERE symbol='".$row['symbol']."'";
            $res2 = dbc::execSql($req2);
            logger::info("CRON", $row['symbol'], "[computeIndicatorsForSymbolWithOptions] OK");
            $counter++;
        }

    }

}

if (tools::isLocalHost()) cacheData::deleteTMPFiles();

logger::info("CRON", "END", "###########################################################");

echo "Done.";

?>

</div>