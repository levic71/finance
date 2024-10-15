<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
//
// Lancer par cron-job
//
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

$req = "SELECT * FROM stocks s LEFT JOIN quotes q ON s.symbol = q.symbol ORDER BY last_indicators_update DESC, s.symbol";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) $stocks2update[$row['symbol']] = $row;

// Mise à jour des cotations quotidiennes provenant de GSheet
foreach($stocks2update as $key => $val) {

    $market_status = cacheData::getMarketStatus($val['timezone'], $val['marketopen'], $val['marketclose']);

    // Mise à jour des cotations les jours ouvrés et pendant les heures de cotation de chaque action uniquement
    if (cacheData::isMarketOpen($market_status)) {
    
        // Mise à jour de la cote de l'actif si valeur suivi par dans Google Sheet
        if (isset($GSValues[$val['symbol']])) {

            // Mise à jour de la cote avec GSheet
            updateQuotesWithGSData($GSValues[$val['symbol']]);
    
            logger::info("CRON", $val['symbol'], "[updateQuotesWithGSData] OK");
        }

        // Si Turbo, calcul nouvelle valeur en fonction variation du jour du sousjacent
        if ($val['type'] == 'CALL' || $val['type'] == 'PUT') {

            $market_status_sousjacent = cacheData::getMarketStatus($stocks2update[$val['pc_sousjacent']]['timezone'], $stocks2update[$val['pc_sousjacent']]['marketopen'], $stocks2update[$val['pc_sousjacent']]['marketclose']);

            if (cacheData::isMarketOpen($market_status_sousjacent)) {

                $cotation_turbo_veille = $val['price'];
                $cotation_turbo_levier = $val['pc_levier'];
                $cotation_sousjacent_percent = $GSValues[$val['pc_sousjacent']][10];

                // Calcul nouvelle cotation turbo
                $cotation_turbo_new = $cotation_turbo_veille + ($cotation_turbo_veille * ($cotation_sousjacent_percent * $cotation_turbo_levier)) / 100;

                // Insertion nouvelle cotation turbo
                $req = "UPDATE quotes SET price='".$cotation_turbo_new."', open='".$cotation_turbo_new."', high='".$cotation_turbo_new."', low='".$cotation_turbo_new."', percent='".($cotation_sousjacent_percent * $cotation_turbo_levier)."' WHERE symbol='".$key."'";
                $res = dbc::execSql($req);

                $req = "UPDATE stocks SET date_update='".date('Y-m-d')."' WHERE symbol='".$key."'";
                $res = dbc::execSql($req);

                // echo $cotation_turbo_veille.":X".$cotation_turbo_levier.":".$cotation_sousjacent_percent."%:".$cotation_turbo_new."\n";

            }

        }

    }
}

if (tools::isLocalHost()) cacheData::deleteTMPFiles();

logger::info("CRON", "END", "###########################################################");

echo "Done.";

?>

</div>
