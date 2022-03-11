<?

require_once "sess_context.php";

session_start();

include "common.php";

$db = dbc::connect();

$data = calc::getMinMaxQuotations();
var_dump($data);
	
?>