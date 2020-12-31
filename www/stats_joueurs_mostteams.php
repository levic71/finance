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

//$sgb = new StatsGlobalBuilder($sess_context->getChampionnatId(), $sess_context->getChampionnatType());
$sgb         = JKCache::getCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", 24*60*60, "_FLUX_STATS_CHAMP_");
$most_matchs = $sgb->getMostTeams(isset($id_joueur) ? $id_joueur : "");

?>

<FORM ACTION=<?= isset($id_joueur) ? "stats_detail_joueur.php" : "stats_joueurs.php" ?> METHOD=POST>
<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=700>

<?

if (isset($id_joueur)) echo "<INPUT TYPE=HIDDEN NAME=id_detail VALUE=".$id_joueur.">";

echo "<TR><TD>";
$fxlist = new FXListMostOnGround($most_matchs);
$fxlist->FXSetPagination("stats_joueurs_mostteams.php".(isset($id_joueur) ? "?id_joueur=".$id_joueur : ""));
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
