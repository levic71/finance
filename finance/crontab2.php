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

?>

<div class="ui container inverted segment">

<?

logger::info("DIRECT", "---------", "---------------------------------------------------------");

// Parcours des actifs suivis
$req = "SELECT * FROM stocks ORDER BY symbol";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

        // Mise a jour des caches
        $ret = cacheData::buildAllsCachesSymbol($row['symbol'], true);

        if ($ret['weekly'] || $ret['monthly']) {
            // Calcul des MMX/RSI/D/W/M (1 fois par jour)
            if (!cacheData::isComputeIndicatorsDoneToday($row['symbol']))
                computeIndicators($row['symbol'], 0);
            else
                logger::info("INDIC", $row['symbol'], "[computeIndicators] [Cache] [No computing]");
        }

}
logger::info("DIRECT", "---------", "---------------------------------------------------------");

echo "Done.";

?>

</div>