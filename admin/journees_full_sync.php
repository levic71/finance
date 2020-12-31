<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";
include "../www/StatsBuilder.php";
include "../www/ManagerFXList.php";
include "../www/journees_synchronisation.php";

// Pour que les commandes SQL de mise à jour soient exécutées il faut que confirm="yes"
if (!isset($confirm) || $confirm != "yes") $confirm = "no";

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

?>

<CENTER>
<FORM ACTION=../admin/journees_full_sync.php>

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=650>

<?

$tab = synchronize_journees($sess_context->getRealChampionnatId(), $sess_context->getChampionnatType(), $sess_context->getChampionnatId(), $confirm);

echo "<INPUT TYPE=HIDDEN NAME=confirm VALUE=\"yes\">";
echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Résultat synchronisation journées", "CENTER");
$fxlist->FXSetColumnsAlign(array("CENTER", "LEFT", "CENTER"));
$fxlist->FXSetColumnsColor(array("#BCC5EA", "", ""));
$fxlist->FXSetColumnsWidth(array("10%", "", "10%"));
$fxlist->FXDisplay();
echo "</TD>";

if ($confirm != "yes") echo "<tr><td align=\"center\"><INPUT TYPE=SUBMIT VALUE=\"Valider\"></TD>";

?>

</TABLE>


</FORM>
</CENTER>

<? $menu->end(); ?>
