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
		<script type="text/javascript" src="js/scripts.js?ver=122"></script>
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
							<th>Cache</th>
                            <th>Last Day Quote</th>
                            <th>Max Archive</th>
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

$data = calc::getDualMomentum("ALL", date("Y-m-d"));
foreach($data["stocks"] as $key => $val) {

	$symbol = $key;

	$max_histo = calc::getMaxHistoryDate($symbol);

	$cache_filename = "cache/QUOTE_".$symbol.".json";
	$cache_timestamp = file_exists($cache_filename) ? date("Y-m-d", filemtime($cache_filename)) : "xxxx-xx-xx";


	echo "<tr>
		<td>".$val['symbol']."</td>
		<td>".$val['name']."</td>
		<td>".$val['currency']."</td>
		<td>".$val['type']."</td>
		<td>".$val['region']."</td>
		<td>".$val['marketopen']."-".$val['marketclose']."</td>
		<td>".$val['timezone']."</td>
		<td>".$cache_timestamp."</td>
		<td>".$val['day']."</td>
		<td>".$max_histo."</td>
		<td>".sprintf("%.2f", $val['price'])."</td>
		<td>".sprintf("%.2f", $val['MMFDM'])."%</td>
		<td>".sprintf("%.2f", $val['MMZDM'])."%</td>
		<td>".sprintf("%.2f", $val['MM200'])."</td>
		<td>".sprintf("%.2f", $val['MM20'])."</td>
		<td>".sprintf("%.2f", $val['MM7'])."</td>
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

<?
echo "DM RP PEA [".$data["day"]."]";

arsort($data["perfs"]);
echo "<ul>";
foreach($data["perfs"] as $key => $val) {
	if ($key == "BRE.PAR" || $key == "ESE.PAR" || $key == "PUST.PAR" || $key == "OBLI.PAR") echo "<li>".$key." : ".$val."</li>";
}
echo "</ul>";

echo "DM+ PEA [".$data["day"]."]";

echo "<ul>";
foreach($data["perfs"] as $key => $val) {
	if ($key == "GWT.PAR" || $key == "PMEH.PAR" || $key == "BRE.PAR" || $key == "ESE.PAR" || $key == "PUST.PAR" || $key == "OBLI.PAR") echo "<li>".$key." : ".$val."</li>";
}
echo "</ul>";

?>

			</div>
        </div>
    </div>
    
    </body>
</html>