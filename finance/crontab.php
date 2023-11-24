<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
// //////////////////////////////////////////

include "include.php";
include "indicators.php";
include "googlesheet/sheet.php";

ini_set('max_execution_time', '300'); //300 seconds = 5 minutes

error_reporting(E_ALL); // Error/Exception engine, always use E_ALL
ini_set('ignore_repeated_errors', TRUE); // always use TRUE
ini_set('display_errors', FALSE); // Error/Exception display, use FALSE only in production environment or real server. Use TRUE in development environment
ini_set('log_errors', TRUE); // Error/Exception file logging engine.
ini_set('error_log', './finance.log'); // Logging file path

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

    $market_status = cacheData::getMarketStatus($row['timezone'], $row['marketopen'], $row['marketclose']);

    // Mise à jour journaliere des cotations
    if (cacheData::isMarketOpen($market_status)) {


/*
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


        // Mise à jour de la cote de l'actif
        if (isset($values[$row['symbol']])) {

            // Mise à jour de la cote avec GSheet
            $ret['gsheet'] = updateQuotesWithGSData($values[$row['symbol']]);

            // Mise a jour des indicateurs du jour (avec quotes)
            computeDailyIndicatorsSymbol($row['symbol']);
    
            logger::info("CRON", $row['symbol'], "[updateQuotesWithGSData+computeDailyIndicatorsSymbol] OK");
        }

    }
    
    // Mise à jour journaliere des indicateurs daily apres closing
    if (cacheData::isMarketAfterClosing($market_status)) {

        if ($counter <= 10 && $row['date_update'] != date('Y-m-d')) {

            computePeriodIndicatorsSymbol($row['symbol'], 0, "DAILY");
            
            $req2 = "UPDATE stocks SET date_update='".date('Y-m-d')."' WHERE symbol='".$row['symbol']."'";
            $res2 = dbc::execSql($req2);

            $counter++;

            logger::info("CRON", $row['symbol'], "[computePeriodIndicatorsSymbol DAILY] OK");
        }

    }

    // Mise à jour des indicateurs hebdo et mensuel le samedi
    if (cacheData::isMarketOnWeekend($market_status) && date("N") == 6) {
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


        // Mise à jour 1 fois par jour en dehors des heures d'ouverture de bourse des indicateurs
        if ($counter <= 10 && $row['date_update'] != date('Y-m-d')) {

            computeIndicatorsForSymbolWithOptions($row['symbol'], array("aggregate" => true, "limited" => 0, "periods" => ['WEEKLY', 'MONTHLY']));

            $req2 = "UPDATE stocks SET date_update='".date('Y-m-d')."' WHERE symbol='".$row['symbol']."'";
            $res2 = dbc::execSql($req2);

            $counter++;

            logger::info("CRON", $row['symbol'], "[computeIndicatorsForSymbolWithOptions WEEKLY+MONTHLY] OK");
        }

    }

}

if (tools::isLocalHost()) cacheData::deleteTMPFiles();

logger::info("CRON", "END", "###########################################################");

echo "Done.";

?>

</div>
