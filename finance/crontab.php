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

$GSValues = array();

// ////////////////////////////////////////////////////////
// Mise à jour des valeurs de cotations dans Google Sheet
// ////////////////////////////////////////////////////////
if (tools::useGoogleFinanceService()) {

    // Recuperation des cotations Google Sheet
    $GSValues = updateGoogleSheet();

    // Recuperation des devises Google Sheet et mise en cache
    $GSDevises = calc::getGSDevises();

    // Plus nécessaire
    // Recuperatoin des alertes Google Sheet et mise en cache
    // $GSAlertes = calc::getGSAlertes();
}

?> <div class="ui container inverted segment"><?

logger::info("CRON", "BEGIN", "###########################################################");

$full_data = true;      // false => COMPACT, true => FULL
$limited_computing = 0; // 0 => pas de limite, 1 => on calcule que sur les 300 dernières valeurs

// ////////////////////////////////////////////////////////
// Parcours des actifs suivis
// ////////////////////////////////////////////////////////
$counter = 0; // Permet de compter le nombre d'actifs traités à chaque appel de cron
$stocks2update = array();

$req = "SELECT * FROM stocks ORDER BY last_indicators_update DESC, symbol";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) $stocks2update[] = $row;

// Mise à jour des cotations quotidiennes provenant de GSheet
foreach($stocks2update as $key => $val) {

    $market_status = cacheData::getMarketStatus($val['timezone'], $val['marketopen'], $val['marketclose']);

    // Mise à jour des cotations les jours ouvrés et pendant les heures de cotation de chaque action uniquement
    if (cacheData::isMarketOpen($market_status)) {
    
        // Mise à jour de la cote de l'actif
        if (isset($GSValues[$val['symbol']])) {

            // Mise à jour de la cote avec GSheet
            updateQuotesWithGSData($GSValues[$val['symbol']]);
    
            logger::info("CRON", $val['symbol'], "[updateQuotesWithGSData] OK");
        }

    }
}

if (tools::isLocalHost()) cacheData::deleteTMPFiles();

logger::info("CRON", "END", "###########################################################");

echo "Done.";

?>

</div>
