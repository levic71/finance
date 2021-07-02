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
              World Markets
            </div>
          </div>
        </nav>
        
        <div class="row">
            <div class="col-lg-12 table-responsive">
                <!--tables for tabular data :) ... only-->
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Symbol</th>
                            <th>Last Price</th>
                            <th>Change (%)</th>
                            <th>Standard Dev.</th>
                            <th>Date/Time</th>
                        </tr>
                    </thead>
                    <tbody>
<?php

	$key = "ZFO6Y0QL00YIG7RH";
	$marketsArray = array(
		"SHA:000001" => array("name" => "Shanghai"),
		"INDEXNIKKEI:NI225" => array("name" => "Nikkei 225"),
		"INDEXHANGSENG:HSI" => array("name" => "Hang Seng Index"),
		"TPE:TAIEX" => array("name" => "TSEC"),
		"INDEXFTSE:UKX" => array("name" => "FTSE 100"),
		"INDEXSTOXX:SX5E" => array("name" => "EURO STOXX 50"),
		"INDEXEURO:PX1" => array("name" => "CAC 40"),
		"INDEXTSI:OSPTX" => array("name" => "S&P TSX"),
		"INDEXASX:XJO" => array("name" => "S&P/ASX 200"),
		"INDEXBOM:SENSEX" => array("name" => "BSE Sensex"),
		"TLV:T25" => array("name" => "TA25"),
		"INDEXSWX:SMI" => array("name" => "SMI"),
		"INDEXVIE:ATX" => array("name" => "ATX"),
		"INDEXBVMF:IBOV" => array("name" => "IBOVESPA"),
		"INDEXBKK:SET" => array("name" => "SET"),
		"INDEXIST:XU100" => array("name" => "BIST100"),
		"INDEXBME:IB" => array("name" => "IBEX"),
		"WSE:WIG" => array("name" => "WIG"),
		"TADAWUL:TASI" => array("name" => "TASI"),
		"BCBA:IAR" => array("name" => "MERVAL"),
		"INDEXBMV:ME" => array("name" => "IPC"),
		"IDX:COMPOSITE" => array("name" => "IDX Composite")
	);

	foreach ($marketsArray as $key => $val) {
		//echo '<tr><td>'.$val['name'].'</td><td>'.$code.'</td><td>'.$lastTradePriceCur.'</td><td>'.$cAmount.' ('.$cPercentage.')</td><td>'.$final.'</td><td>'.$lastTradeDate.'</td></tr>';
	}

	$json = file_get_contents('https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol=IBM&interval=5min&apikey='.$key);

	$data = json_decode($json,true);
	
	print_r($data);
?>
                    </tbody>
                </table> 
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-1.12.0.min.js"></script>
    <script src="libraries/bootstrap/js/bootstrap.min.js"></script>
    </body>
</html>