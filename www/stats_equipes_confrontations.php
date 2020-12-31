<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";
include "StatsBuilder.php";

$db = dbc::connect();

if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_)
	FXList::FXHTLMExportBegin();
else
{
	$menu = new menu("full_access");
	$menu->debut($sess_context->getChampionnatNom());
}

echo "<FORM ACTION=stats_detail_equipe.php METHOD=POST>";
echo "<INPUT TYPE=HIDDEN NAME=id_detail VALUE=".$id_detail.">";

?>

<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=700>

<?

echo "<TR><TD>";
$fxlist = new FXListStatsConfrontations($sess_context->getChampionnatId(), $id_detail);
$fxlist->FXSetPagination("stats_equipes_confrontations.php?id_detail=".$id_detail);
$fxlist->FXSetArrayProperties("WIDTH=700");
$fxlist->FXDisplay();
echo "</TD>";

if (!(isset($FXOption) && $FXOption == _FXLIST_EXPORT_)) {
?>
<TR><TD HEIGHT=5></TD>
<TR><TD ALIGN=RIGHT><INPUT TYPE=SUBMIT VALUE="Retour"></TD>
<? } ?>

</TABLE>
</FORM>

<? if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_) FXList::FXHTLMExportEnd(); else $menu->end(); ?>
