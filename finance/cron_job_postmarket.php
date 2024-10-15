<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
//
// Lancer par cron-job
//
// //////////////////////////////////////////

include "include.php";

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

?> <div class="ui container inverted segment"><?

logger::info("CRON", "POSTMARKET", "###########################################################");

$req = "UPDATE quotes SET previous=price WHERE symbol IN (SELECT symbol FROM stocks WHERE type IN ('CALL', 'PUT'))";
$res = dbc::execSql($req);

if (tools::isLocalHost()) cacheData::deleteTMPFiles();

logger::info("CRON", "END", "###########################################################");

echo "Done.";

?>

</div>
