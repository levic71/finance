<? include_once "include.php" ?>

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
            </div>
          </div>
        </nav>
        
        <div class="row">
            <div class="col-lg-12 table-responsive">

<pre><?php

$db = dbc::connect();

// Parcours des actifs suivis
$req = "SELECT symbol FROM stock ORDER BY symbol";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

    cacheData::buildCacheSymbol($row['symbol']);

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