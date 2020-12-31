<?

include "../include/sess_context.php";
include "../include/constantes.php";
include "../include/cache_manager.php";

session_start();

if (!isset($champ) || $champ == "") ToolBox::do_redirect("home.php");

session_register("sess_context");

$sess_context = new sess_context();

include "../include/toolbox.php";
include "../include/inc_db.php";
include "SQLServices.php";

$db = dbc::connect();

$scs = new SQLChampionnatsServices($champ);
$row = $scs->getChampionnat();

if ($row)
{
	$row['login'] = "";
	$row['pwd']   = "";
	$sess_context->setChampionnat($row);
	ToolBox::do_redirect("forum_message.php?id_msg=".$id_msg);
}
else
	ToolBox::do_redirect("home.php");

mysql_close($db);
		
?>
