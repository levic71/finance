<?

include "../include/sess_context.php";

ini_set("url_rewriter.tags","input=src");
ini_set('arg_separator.output', '&amp;');

session_start();

$jorkyball_redirect_exception = 1;

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";


$db = dbc::connect();


if (isset($sess_context) && $sess_context->isChampionnatValide())
{
	$menu = new menu("full_access");
	$menu->debut($sess_context->getChampionnatNom());
}
else
{
	$menu = new menu("forum_access");
	$menu->debut("");
}

?>

<iframe src="../chat/index.php" height="640" width="695" frameborder="0" style="padding: 0px; margin: 0px;"></iframe>


<? $menu->end(); ?>

