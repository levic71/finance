<?

session_cache_expire(60*60);

include "../include/lock.php";
include "../include/constantes.php";
include "../include/cache_manager.php";
include "../include/ads_manager.php";
//include "../include/Xclasses.php";
include "../include/templatebox.php";
include "../include/toolbox.php";
include "../include/imagebox.php";
//include "../include/menu.php";
//include "../include/HTMLTable.php";
include "../www/SQLServices.php";

// VERSION DU PROJET
$projet_version = "Jorky 5.00";

// Si on est jamais passé par home.php, on redirige vers cette page
if (!isset($sess_context) || $sess_context->isChampionnatNonDefini())
{
	if (isset($jorkyball_redirect_exception) && $jorkyball_redirect_exception == 1)
	{

		if (isset($_SESSION['sess_context'])) $sess_context = $_SESSION['sess_context'];

		if (!isset($sess_context))
		{
			$sess_context = new sess_context();
			$_SESSION["sess_context"] = $sess_context;
		}

		$sess_context->setLangue($jb_langue);
	}
	else
		ToolBox::do_redirect("../home/index.php");
}

include "../lang/nls_".$sess_context->getLangue().".php";

?>
