<?

require_once "sess_context.php";

session_start();

include "common.php";

$db = dbc::connect();

// On récupère les portefeuilles de l'utilisateur
$req = "SELECT * FROM users";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

	$portfolio_data = calc::getAggregatePortfoliosByUser($row['id']);

	if (count($portfolio_data) == 0) continue;

}
	
?>