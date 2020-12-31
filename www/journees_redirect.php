<?

include "../include/sess_context.php";
include "../include/constantes.php";
include "../include/cache_manager.php";
include "../include/toolbox.php";

session_start();

if (!isset($champ) || $champ == "") ToolBox::do_redirect("error_redirect.php?error=1");
if (!isset($journee)  || $journee == "")  ToolBox::do_redirect("error_redirect.php?error=2");

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

	ToolBox::do_redirect("../www/matchs.php?pkeys_where_jb_journees=+WHERE+id=".$journee."&redirect=1");
}

ToolBox::do_redirect("error_redirect.php?error=3");

mysql_close($db);

?>
