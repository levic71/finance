<?

include "../include/sess_context.php";
include "../include/constantes.php";
include "../include/cache_manager.php";
include "../include/toolbox.php";

session_start();

if (!isset($champ) || $champ == "") ToolBox::do_redirect("error_redirect.php");

session_register("sess_context");

$sess_context = new sess_context();

include "../include/inc_db.php";
include "../www/SQLServices.php";

$db = dbc::connect();

$scs = new SQLChampionnatsServices($champ);
$row = $scs->getChampionnat();

if ($row)
{
	$row['login'] = "";
	$row['pwd']   = "";
	$sess_context->setChampionnat($row);

	Toolbox::trackUser($sess_context->getRealChampionnatId(), _TRACK_EXPORT_);

	ToolBox::do_redirect("../www/championnat_home.php?redirect=1");
}

ToolBox::do_redirect("error_redirect.php");

mysql_close($db);
		
?>
