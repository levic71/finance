<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!is_dir("cache/")) mkdir("cache/");

$db = dbc::connect();

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
    if ($dateTimestamp0 > $dateTimestamp1 && $dateTimestamp0 < $dateTimestamp2)
        cacheData::buildCacheSymbol($row['symbol'], true);
    else
        logger::info("CRON", $row['symbol'], "Market close, no update !");
}

// Recuperation de l'historique du mois

// Recuperation de la derniere cotation

?>
    </pre>
</div>