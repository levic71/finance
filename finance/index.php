<?

include_once "include.php";
$pea = isset($_GET["pea"]) ? $_GET["pea"] : -1;
$admin = isset($_GET["admin"]) && $_GET["admin"] == 1 ? true : false;

?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="z-ua-compatible" content="ie=edge">
        <title>Market Data</title>
		<link rel="stylesheet" href="css/style.css?ver=122" />
		<script type="text/javascript" src="js/scripts.js"></script>
    </head>
    <body>

    <div class="container">
        
        <nav class="navbar navbar-default">
          <div class="container">
            <div class="navbar-header">
            	World Markets
<? if ($admin) { ?>
				<button onclick="window.location='search.php'">search</button>
<? } ?>
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
                            <th>DM Float</th>
                            <th>DM TKL</th>
                            <th>MM200</th>
                            <th>MM20</th>
                            <th>MM7</th>
<? if ($admin) { ?>
							<th></th>
                            <th></th>
                            <th></th>
<? } ?>
                        </tr>
                    </thead>
                    <tbody>
<?

$db = dbc::connect();

$stocks = array();

$req = "SELECT * FROM stock s, quote q WHERE s.symbol = q.symbol AND ".($pea == -1 ? "1=1" : "pea=".$pea)." ORDER BY s.symbol";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

	$symbol = $row['symbol'];
	$stocks[$symbol] = array_merge($row, calc::processData($symbol));
	$perf[$symbol] = $stocks[$symbol]['MMZDM'];

	echo "<tr>
		<td>".$stocks[$symbol]['symbol']."</td>
		<td>".$stocks[$symbol]['name']."</td><td>".$row['currency']."</td>
		<td>".$stocks[$symbol]['type']."</td><td>".$row['region']."</td>
		<td>".$stocks[$symbol]['marketopen']."-".$row['marketclose']."</td>
		<td>".$stocks[$symbol]['timezone']."</td>
		<td>".$stocks[$symbol]['day']."</td>
		<td>".sprintf("%.2f", $stocks[$symbol]['price'])."</td>
		<td>".sprintf("%.2f", $stocks[$symbol]['MMFDM'])."%</td>
		<td>".sprintf("%.2f", $stocks[$symbol]['MMZDM'])."%</td>
		<td>".sprintf("%.2f", $stocks[$symbol]['MM200'])."</td>
		<td>".sprintf("%.2f", $stocks[$symbol]['MM20'])."</td>
		<td>".sprintf("%.2f", $stocks[$symbol]['MM7'])."</td>
	";

	if ($admin) {
		echo "
		<td><button onclick=\"location.href='detail.php?symbol=".$symbol."'\">more</button></td>
		<td><button onclick=\"updateStock('".$symbol."');\">update</button></td>
		<td><button onclick=\"deleteStock('".$symbol."');\">delete</button></td>
	";

	}
	
	echo "</tr>";
}

?>
                    </tbody>
                </table></pre> 

				- DM RP PEA<br />
<?

arsort($perf);
echo "<ul>";
foreach($perf as $key => $val) {
	if ($key == "BRE.PAR" || $key == "ESE.PAR" || $key == "PUST.PAR" || $key == "OBLI.PAR") echo "<li>".$key." : ".$val."</li>";
}
echo "</ul>";

?>
				- DM+ PEA<br />
<?

arsort($perf);
echo "<ul>";
foreach($perf as $key => $val) {
	if ($key == "GWT.PAR" || $key == "PMEH.PAR" || $key == "BRE.PAR" || $key == "ESE.PAR" || $key == "PUST.PAR" || $key == "OBLI.PAR") echo "<li>".$key." : ".$val."</li>";
}
echo "</ul>";

?>
				- Calcul MM sur les mois precedents
            </div>
        </div>
    </div>
    
    </body>
</html>