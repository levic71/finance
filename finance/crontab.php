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

    // Ajustement heure par rapport UTC (On ajoute 15 min pour etre sur d'avoir la premiere cotation)
    $my_date_time=time();
    $my_new_date_time=$my_date_time+((3600*(intval(substr($row['timezone'], 3))) + 15*60));
    $my_new_date=date("Y-m-d H:i:s", $my_new_date_time);

    $dateTimestamp0 = strtotime(date($my_new_date));
    $dateTimestamp1 = strtotime(date("Y-m-d ".$row['marketopen']));
    $dateTimestamp2 = strtotime(date("Y-m-d ".$row['marketclose']));

    // Market Open ? (marketclose + 30')
    if (tools::isLocalHost() || ($dateTimestamp0 > $dateTimestamp1 && $dateTimestamp0 < ($dateTimestamp2 + (30*60)))) {

        // //////////////////////////////////////
        // Mise a jour des caches
        // //////////////////////////////////////
        $ret = cacheData::buildAllsCachesSymbol($row['symbol'], false);

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
        logger::info("CRON", $row['symbol'], "[buildAllsCachesSymbol] [Market close] [No update]");

    logger::info("CRON", "---------", "---------------------------------------------------------");

}

logger::info("CRON", "END", "###########################################################");

echo "Done.";

?>

</div>