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
              Log
              <button onclick="window.location='index.php?admin=1'">back</button>
            </div>
          </div>
        </nav>
        
        <div class="row">
            <div class="col-lg-12 table-responsive">

<pre><?php

echo exec( 'tail -n 100 ./finance.log');

?></pre>
            </div>
        </div>
    </div>
    
    </body>
</html>