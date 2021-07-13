<?

include_once "include.php";

$nb_lignes = 40;

foreach(['nb_lignes'] as $key)
    $$key = isset($_GET[$key]) ? $_GET[$key] : (isset($$key) ? $$key : "");

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
              Log <select id="nb_lignes" onChange="window.location='log.php?nb_lignes='+this.value"><option value=40>40 lignes</option><option value=100>100 lignes</option><option value=200>200 lignes</option></select>
              <button onclick="window.location='log.php'">refresh</button>
              <button onclick="window.location='index.php?admin=1'">back</button>
            </div>
          </div>
        </nav>
        
        <div class="row">
            <div class="col-lg-12 table-responsive">

<pre><?php

echo shell_exec( 'tail -n '.$nb_lignes.' ./finance.log');

?></pre>
            </div>
        </div>
    </div>
    
    </body>
</html>