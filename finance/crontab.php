<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
// //////////////////////////////////////////

include "include.php";
include "indicators.php";
include "googlesheet/sheet.php";

// Overwrite include value
$dbg = true;

// Si on est samedi ou dimanche bye bye
if (!tools::isLocalHost() && date("N") > 5) exit(0);

if (!is_dir("cache/")) mkdir("cache/");

$db = dbc::connect();

// Purge log file
logger::purgeLogFile("./finance.log", 5*1048576);

// Mise à jour des valeurs de cotations dans Google Sheet
$values = updateGoogleSheet();

?>

<div class="ui container inverted segment">

    <pre style="width: 100%; height: 500px; overflow: scroll;">
<?

// Parcours des actifs suivis
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

    // Place de marche ouverte ?
    if (tools::isLocalHost() || ($dateTimestamp0 > $dateTimestamp1 && $dateTimestamp0 < $dateTimestamp2)) {
        cacheData::buildAllsCachesSymbol($row['symbol']);
        if (isset($values[$row['symbol']])) echo updateQuotesWithGSData($values[$row['symbol']]);
    }
    else
        logger::info("CRON", $row['symbol'], "[buildAllsCachesSymbol] [Market close] [No update]");

/*     $req2 = "SELECT count(*) total FROM indicators WHERE symbol='".$row['symbol']."'";
    $res2 = dbc::execSql($req2);
    $row2 = mysqli_fetch_array($res2);
    $limited = ($row2 && $row2['total'] == 0) ? 0 : 1;
 */

    $limited = 0;
    // Calcul des MMX/RSI/D/W/M (1 fois par jour => controle dans la fonction)
    computeIndicators($row['symbol'], $limited);
}

?>
    </pre>
</div>