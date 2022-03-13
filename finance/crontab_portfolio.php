<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
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
$req = "SELECT * FROM users";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

	$portfolio_data = calc::getAggregatePortfoliosByUser($row['id']);

	if (count($portfolio_data) == 0) continue;

}
	
?>