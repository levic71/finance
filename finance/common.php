<?

if (session_status() != PHP_SESSION_ACTIVE) session_cache_expire(60*60);

include_once "include.php";

// Si on est jamais passé par index.php, on redirige vers cette page
if (!isset($_SESSION["sess_context"]))
{
	$dns = explode('.', $_SERVER['SERVER_NAME']);

	if ($_SERVER['SERVER_NAME'] == "localhost" || (isset($dns[0]) && $dns[0] == "www"))
		ToolBox::do_redirect($location = 'https://'.$_SERVER['HTTP_HOST']."/jorkyball/finance/index.php");
	else
		ToolBox::do_redirect("https://".$_SERVER['SERVER_NAME']);
	
	exit(0);
}

$sess_context = $_SESSION["sess_context"];

?>