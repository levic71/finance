<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
// //////////////////////////////////////////

include "include.php";
include "indicators.php";
include "googlesheet/sheet.php";

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

?> <div class="ui container inverted segment"><?

logger::info("CRON", "BEGIN", "###########################################################");

// ////////////////////////////////////////////////////////
// Parcours des actifs suivis
// ////////////////////////////////////////////////////////
$req = "SELECT * FROM stocks ORDER BY symbol";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

    if (cacheData::isMarketOpen($row['timezone'], $row['marketopen'], $row['marketclose'])) {

        // //////////////////////////////////////
        // Mise a jour des caches
        // //////////////////////////////////////
        $ret = cacheData::buildAllCachesSymbol($row['symbol'], false);

        // /////////////////////////////////////////////////////////
        // Mise à jour de la cote de l'actif avec la donnée GSheet
        // /////////////////////////////////////////////////////////
        if (isset($values[$row['symbol']]))
            $ret['gsheet'] = updateQuotesWithGSData($values[$row['symbol']]);
        else
            logger::info("GSHEET", $row['symbol'], "[updateQuotesWithGSData] [No data] [No update]");

        if ($ret['weekly'] || $ret['monthly']) {
            // ///////////////////////////////////////////////
            // Calcul des MMX/RSI/D/W/M (1 fois par jour)
            // ///////////////////////////////////////////////
            if (!cacheData::isComputeIndicatorsDoneToday($row['symbol']))
                computeIndicators($row['symbol'], 0);
            else
                logger::info("INDIC", $row['symbol'], "[computeIndicators] [Cache] [No computing]");
        }
    }
    else
        logger::info("CRON", $row['symbol'], "[buildAllCachesSymbol] [Market close] [No update]");

    logger::info("CRON", "---------", "---------------------------------------------------------");

}

logger::info("CRON", "END", "###########################################################");

echo "Done.";

?>

</div>