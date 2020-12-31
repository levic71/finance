<?

include "../include/sess_context.php";

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

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=500 SUMMARY="">

<?

$tab = array();

$tab[] = array("<IMG SRC=../images/world_big.gif ALT=\"Carte monde\" />");

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Foot 2x2 dans le monde", "center");
$fxlist->FXSetColumnsAlign(array("right"));
$fxlist->FXSetColumnsColor(array(""));
$fxlist->FXSetColumnsWidth(array(""));
$fxlist->FXDisplay();
echo "</TD>";

?>

</TABLE>

<? $menu->end(); ?>
