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

<MAP NAME="france_map">
<AREA SHAPE=circle ALT="" COORDS="328,161,13" HREF="#" onMouseOver="javascript:show_info('<A HREF=http://www.jorkyball94.com>Alfortville</A><br>ZAC du Val de Seine<br>Zone Techniparc<br>94140 ALFORTVILLE', event);" onMouseOut="javascript:close_info();" />
</MAP>

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=500 SUMMARY="">

<?

$tab = array();

$tab[] = array("<IMG USEMAP=\"#france_map\" SRC=../images/france_big.gif BORDER=0 ALT=\"carte france\" />");

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Foot 2x2 en France", "center");
$fxlist->FXSetColumnsAlign(array("right"));
$fxlist->FXSetColumnsColor(array(""));
$fxlist->FXSetColumnsWidth(array(""));
$fxlist->FXDisplay();
echo "</TD>";

?>

</TABLE>

<? $menu->end(); ?>
