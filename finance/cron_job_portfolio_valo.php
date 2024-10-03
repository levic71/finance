<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
//
// Lancer par cron-job
//
// //////////////////////////////////////////

include "include.php";
include "indicators.php";
include "googlesheet/sheet.php";

ini_set('max_execution_time', '300'); //300 seconds = 5 minutes

// Overwrite include value
$dbg = false;

if (!is_dir("cache/")) mkdir("cache/");

$db = dbc::connect();

// On récupère les portefeuilles de l'utilisateur
$req = "SELECT * FROM portfolios";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {

	$portfolio_data = calc::aggregatePortfolio($row);

	unset($portfolio_data['orders']);
	unset($portfolio_data['positions']);
	unset($portfolio_data['trend_following']);

	if (count($portfolio_data) == 0) continue;

	$update = "INSERT INTO portfolio_valo (date, portfolio_id, data) VALUES ('".date("Ymd")."', '".$row['id']."', '".json_encode($portfolio_data)."') ON DUPLICATE KEY UPDATE data='".json_encode($portfolio_data)."'";
	$res2 = dbc::execSql($update);

}
	
?>