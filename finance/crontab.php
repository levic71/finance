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
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

    if (cacheData::isMarketOpen($row['timezone'], $row['marketopen'], $row['marketclose'])) {

        // Mise a jour des caches : Si full = false => compact (aucun impact sur le calcul des indicateurs) 
        $ret = cacheData::buildDailyCachesSymbol($row['symbol'], false);

        // Mise à jour des data daily
        if ($ret['daily'])
            computePeriodIndicatorsSymbol($row['symbol'], 1, "DAILY");
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

        // Mise a jour des caches : full = false => compact (aucun impact sur le calcul des indicateurs) 
        $ret = cacheData::buildWeekendCachesSymbol($row['symbol'], false);

        if ($ret['weekly'])
            computePeriodIndicatorsSymbol($row['symbol'], 1, "WEEKLY");
        else
            logger::info("INDIC", $row['symbol'], "[computeWeeklyIndicators] [Cache] [No computing]");

        if ($ret['monthly'])
            computePeriodIndicatorsSymbol($row['symbol'], 1, "MONTHLY");
        else
            logger::info("INDIC", $row['symbol'], "[computeMonthlyIndicators] [Cache] [No computing]");
    }

    logger::info("CRON", "---------", "---------------------------------------------------------");
}

logger::info("CRON", "END", "###########################################################");

echo "Done.";

?>

</div>