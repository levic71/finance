<?

include_once "include.php";

if (!is_dir("cache/")) mkdir("cache/");

?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="z-ua-compatible" content="ie=edge">
        <title>Google Market Data - Sandbox</title>
        <link rel="stylesheet" href="libraries/bootstrap/css/bootstrap.min.css">
    </head>
    <body>

    <div class="container">
        
        <nav class="navbar navbar-default">
          <div class="container">
            <div class="navbar-header">
              Crontab
              <button onclick="window.location='index.php?admin=1'">back</button>
            </div>
          </div>
        </nav>
        
        <div class="row">
            <div class="col-lg-12 table-responsive">

<pre><?php

$db = dbc::connect();

// Parcours des actifs suivis
$req = "SELECT * FROM stock ORDER BY symbol";
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
    if (true)
        cacheData::buildCacheSymbol($row['symbol'], true);
    else
        logger::info("CRON", $row['symbol'], "Market close, no update !");
}

// Recuperation de l'historique du mois

// Recuperation de la derniere cotation

?></pre>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-1.12.0.min.js"></script>
    <script src="libraries/bootstrap/js/bootstrap.min.js"></script>
    </body>
</html>