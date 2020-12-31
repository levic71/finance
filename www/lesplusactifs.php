<?

include "../include/sess_context.php";

session_start();

$jorkyball_redirect_exception = 1;

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_)
	FXList::FXHTLMExportBegin();
else
{
	$menu = new menu("forum_access");
	$menu->debut($sess_context->getChampionnatNom());
}

$most_active = JKCache::getCache("../cache/most_active_home.txt", 900, "_FLUX_MOST_ACTIVE_");

$tab = array();
foreach($most_active as $c)
{
	if ($c['actif'] == 1)
		$tab [] = array($c['dt_creation'], "<A CLASS=\"blue icon_".$c['type']."\" HREF=\"championnat_acces.php?ref_champ=".$c['id']."\">".$c['nom']."</A>", $c['points']." points");
}

echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY=\"tab central\">";

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Les plus actifs", "center");
$fxlist->FXSetColumnsAlign(array("center", "left", "right"));
$fxlist->FXSetColumnsColor(array(""));
$fxlist->FXSetColumnsWidth(array("", "", 100));
$fxlist->FXSetNumerotation(true);
$fxlist->FXDisplay();
echo "</TD>";

echo "</TABLE>";

?>

<div class="allaccess"><a href="../www/lesnouveaux.php">Tous les nouveaux</a></div>

<?

if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_) FXList::FXHTLMExportEnd(); else $menu->end();

?>
