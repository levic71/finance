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

$last_created = JKCache::getCache("../cache/last_created_home.txt", 300, "_FLUX_LAST_CREATED_");

$tab = array();
foreach($last_created as $c)
	$tab [] = array($c['dt_creation'], "<A HREF=\"championnat_acces.php?ref_champ=".$c['id']."\" CLASS=\"icon_".$c['type']." blue\">".$c['nom']."</A>", $c['points']." points");

echo "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY=\"tab central\">";

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Les nouveaux", "center");
$fxlist->FXSetColumnsAlign(array("center", "left", "right"));
$fxlist->FXSetColumnsColor(array(""));
$fxlist->FXSetColumnsWidth(array("100", ""));
$fxlist->FXSetNumerotation(true);
$fxlist->FXDisplay();
echo "</TD>";

echo "</TABLE>";

?>

<div class="allaccess"><a href="../www/lesplusactifs.php">Tous les + actifs</a></div>

<?

if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_) FXList::FXHTLMExportEnd(); else $menu->end();

?>
