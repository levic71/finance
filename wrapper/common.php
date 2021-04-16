<?

// Debug r7/prod si nécessaire
if (false) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	ini_set('error_log', "./php_errors.log");
}

if (session_status() != PHP_SESSION_ACTIVE) session_cache_expire(60*60);

require_once "../include/lock.php";
require_once "../include/constantes.php";
require_once "../include/cache_manager.php";
require_once "../include/toolbox.php";
require_once "../www/SQLServices.php";
require_once "../wrapper/nimagebox.php";
require_once "../wrapper/wrapper_fcts.php";

// VERSION DU PROJET
$projet_version = "Jorky 3.0";

$sess_context = $_SESSION["sess_context"];

// Si on est jamais passé par jk.php, on redirige vers cette page
// if (!isset($sess_context) || ($sess_context->isChampionnatNonDefini() && basename($SCRIPT_NAME) != "login.php" && basename($SCRIPT_NAME) != "upload.php" && basename($SCRIPT_NAME) != "logout.php" && basename($SCRIPT_NAME) != "inscription.php" && basename($SCRIPT_NAME) != "inscription_do.php" && basename($SCRIPT_NAME) != "login_panel.php" && basename($SCRIPT_NAME) != "myprofile.php"))
if (false && !isset($sess_context))
{
	$dns = explode('.', $_SERVER['SERVER_NAME']);

	if ($_SERVER['SERVER_NAME'] == "localhost" || (isset($dns[0]) && $dns[0] == "www"))
		ToolBox::do_redirect("jk.php");
	else
		ToolBox::do_redirect("http://".$_SERVER['SERVER_NAME']);
}

require_once "../lang/nls_".$sess_context->getLangue().".php";

/**
 * function to emulate the register_globals setting in PHP
 * for all of those diehard fans of possibly harmful PHP settings :-)
 * @author Ruquay K Calloway
 * @param string $order order in which to register the globals, e.g. 'egpcs' for default
 * @link hxxp://www.php.net/manual/en/security.globals.php#82213
 */
function register_globals($order = 'egpcs')
{
	// define a subroutine
	if(!function_exists('register_global_array'))
	{
		function register_global_array(array $superglobal)
		{
			foreach($superglobal as $varname => $value)
			{
				global $$varname;
				$$varname = $value;
			}
		}
	}
	
	$order = explode("\r\n", trim(chunk_split($order, 1)));
	foreach($order as $k)
	{
		switch(strtolower($k))
		{
			case 'e': register_global_array($_ENV);    break;
			case 'g': register_global_array($_GET);    break;
			case 'p': register_global_array($_POST);   break;
			case 'c': register_global_array($_COOKIE); break;
			case 's': register_global_array($_SERVER); break;
		}
	}
}

/**
 * Undo register_globals
 * @author Ruquay K Calloway
 * @link hxxp://www.php.net/manual/en/security.globals.php#82213
 */
function unregister_globals() {
	if (ini_get(register_globals)) {
		$array = array('_REQUEST', '_SESSION', '_SERVER', '_ENV', '_FILES');
		foreach ($array as $value) {
			foreach ($GLOBALS[$value] as $key => $var) {
				if ($var === $GLOBALS[$key]) {
					unset($GLOBALS[$key]);
				}
			}
		}
	}
}

register_globals();

?>