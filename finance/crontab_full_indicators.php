<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
// //////////////////////////////////////////

include "include.php";
include "indicators.php";

ini_set('max_execution_time', '300'); //300 seconds = 5 minutes

// Overwrite include value
$dbg = false;

if (!is_dir("cache/")) mkdir("cache/");

$db = dbc::connect();

// On récupère toutes les actifs pour calculer daily/monthly/indicators
$req = "SELECT * FROM stocks";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

	echo "Computing : ".$row['symbol']."<br />";
	computeIndicatorsForSymbolWithOptions($row['symbol'], array("aggregate" => true, "limited" => 0, "periods" => ['WEEKLY', 'MONTHLY']));

}

?>