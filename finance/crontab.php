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

    // Recompute all indicators
    // computePeriodIndicatorsSymbol($row['symbol'], 0, "DAILY");
    // continue;

/*     if ($row['symbol'] == "BRE.PAR" || $row['symbol'] == "PUST.PAR" || $row['symbol'] == "ESE.PAR" || $row['symbol'] == "OBLI.PAR") 
    {
        computePeriodIndicatorsSymbol($row['symbol'], 0, "DAILY");
        continue;
    }
    else
        continue;
 */
    if (cacheData::isMarketOpen($row['timezone'], $row['marketopen'], $row['marketclose'])) {

        // //////////////////////////////////////
        // Mise a jour des caches
        // //////////////////////////////////////
        $ret = cacheData::buildDailyCachesSymbol($row['symbol'], false);

        // /////////////////////////////////////////////////////////
        // Mise à jour de la cote de l'actif avec la donnée GSheet
        // /////////////////////////////////////////////////////////
        if (isset($values[$row['symbol']])) {
            $ret['gsheet'] = updateQuotesWithGSData($values[$row['symbol']]);
        }
        else
            logger::info("GSHEET", $row['symbol'], "[updateQuotesWithGSData] [No data found] [No update]");

        if ($ret['daily'])
            computePeriodIndicatorsSymbol($row['symbol'], 0, "DAILY");
        else {

            // Maj du DM du jour
            // if (isset($ret['gsheet']) && strstr($ret['sheet'], "QUOTES"))  calc::processDataDM($item['day'], array("quote" => array(), "data" => $x));

            logger::info("INDIC", $row['symbol'], "[computeDailyIndicators] [Cache] [No computing]");

        }

    } else {

        $ret = cacheData::buildWeekendCachesSymbol($row['symbol'], false);

        if ($ret['weekly'])
            computePeriodIndicatorsSymbol($row['symbol'], 0, "WEEKLY");
        else
            logger::info("INDIC", $row['symbol'], "[computeWeeklyIndicators] [Cache] [No computing]");

        if ($ret['monthly'])
            computePeriodIndicatorsSymbol($row['symbol'], 0, "MONTHLY");
        else
            logger::info("INDIC", $row['symbol'], "[computeMonthlyIndicators] [Cache] [No computing]");
    }

    logger::info("CRON", "---------", "---------------------------------------------------------");

}

logger::info("CRON", "END", "###########################################################");

echo "Done.";

?>

</div>