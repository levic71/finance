<?

include_once "include.php";
$symbol = isset($_GET["symbol"]) ? $_GET["symbol"] : "";

?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="z-ua-compatible" content="ie=edge">
        <title>Market Data</title>
        <link rel="stylesheet" href="style.css" />
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
                <pre><table id="lst_stock" class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Symbol</th>
                            <th>Name</th>
                            <th>Currency</th>
                            <th>Type</th>
                            <th>Region</th>
                            <th>Market Hours</th>
                            <th>Time Zone</th>
                            <th>Last Day Quote</th>
                            <th>Price</th>
                            <th>DM flottant</th>
                            <th>DM TKL</th>
                            <th>MM200</th>
                            <th>MM20</th>
                            <th>MM7</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
<?

$db = dbc::connect();

$req = "SELECT * FROM stock s, quote q WHERE s.symbol = q.symbol AND s.symbol='".$symbol."'";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

	$c = calc::processData($row['symbol']);

	echo "<tr>
		<td>".$row['symbol']."</td><td></td><td>".$row['currency']."</td>
		<td>".$row['type']."</td><td>".$row['region']."</td>
        <td>".$row['marketopen']."-".$row['marketclose']."</td><td>".$row['timezone']."</td>
		<td>".$row['day']."</td><td>".$row['price']."</td>
		<td>".$c['MMFDM']."%</td>
		<td>".$c['MMZDM']."%</td>
		<td>".$c['MM200']."</td>
		<td>".$c['MM20']."</td>
		<td>".$c['MM7']."</td>
		<td><button onclick=\"location.href='index.php'\">back</button></td>
		</tr>
	";

}

?>
                    </tbody>
                </table></pre>
<ul> 
<?
    echo "<li>Ref date MMZ1M => ".$c['MMZ1MDate']."</li>";
    echo "<li>Ref date MMZ3M => ".$c['MMZ3MDate']."</li>";
    echo "<li>Ref date MMZ6M => ".$c['MMZ6MDate']."</li>";

    foreach(cacheData::$lst_cache as $key)
        echo "<li>Cache ".$key."_".$symbol.".json ".(file_exists("cache/".$key."_".$symbol.".json") ? "Ok" : "NOK")."</li>";

?>
</ul>
            </div>
        </div>
    </div>
    
    </body>
</html>