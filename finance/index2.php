<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('class.stockMarketAPI.php');
?>
<html>
<body>
	
<h1>Current Stock Information for AAPL</h1>

<?php
$StockMarketAPI = new StockMarketAPI;
$StockMarketAPI->symbol = 'AAPL';
?>
<pre><?php print_r($StockMarketAPI->getData());?></pre>

<?php
$start = '01-01-2013';
$end = '01-07-2013';
?>
<h1>Historical Stock Information for AAPL (<?php echo $start ?> - <?php echo $end ?>)</h1>
<?php
$StockMarketAPI = new StockMarketAPI;
$StockMarketAPI->symbol = 'AAPL';
$StockMarketAPI->history = array(
  'start' 	 => $start,
  'end' 	 => $end,
  'interval' => 'd' // Daily
);
?>
<pre><?php print_r($StockMarketAPI->getData());?></pre>

<hr>

<h1>Stock Information for AAPL, MSFT, GOOGL</h1>
<?php
$StockMarketAPI = new StockMarketAPI;
$StockMarketAPI->symbol = array('AAPL', 'MSFT', 'GOOGL');
?>
<pre><?php print_r($StockMarketAPI->getData());?></pre>

</body></html>
