<?

require_once "sess_context.php";

session_start();

include "common.php";

$date = "";

$db = dbc::connect();

// Recuperation des DM en BD
$data2 = calc::getIndicatorsLastQuote();

// Tri décroissant des perf DM des stocks
arsort($data2["perfs"]);

// Recuperation de tous les actifs
$quotes = calc::getIndicatorsLastQuote();

// On récupère les portefeuilles de l'utilisateur
$req = "SELECT * FROM portfolios WHERE user_id=".$sess_context->getUserId()." AND id=3";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

	$portfolio_data = calc::aggregatePortfolio($row['id'], $quotes);

	var_dump($portfolio_data);
}
	
?>