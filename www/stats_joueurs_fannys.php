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

// Si $joueur_ou_equipe = 1 => fannys joueur si = 2 fannys equipe

$joueur_ou_equipe = isset($joueur_ou_equipe) ? $joueur_ou_equipe : 2;

if ($joueur_ou_equipe == 1) echo "<FORM ACTION=".(isset($id_detail) ? "stats_detail_joueur.php" : "stats_joueurs.php")." METHOD=POST>";
if ($joueur_ou_equipe == 2) echo "<FORM ACTION=".(isset($id_detail) ? "stats_detail_equipe.php" : "stats_equipes.php")." METHOD=POST>";

if (isset($id_detail)) echo "<INPUT TYPE=HIDDEN NAME=id_detail VALUE=".$id_detail.">";

?>

<TABLE CELLPADDING=0 CELLSPACING=0 WIDTH=700>

<?

echo "<TR><TD>";
if ($joueur_ou_equipe == 1)
{
	$fxlist = new FXListFannysJoueur($sess_context->getChampionnatId(), isset($id_detail) ? $id_detail : "");
	$fxlist->FXSetPagination("stats_joueurs_fannys.php".(isset($id_detail) ? "?joueur_ou_equipe=".$joueur_ou_equipe."&id_detail=".$id_detail : ""));
}
else
{
	$fxlist = new FXListFannysEquipe($sess_context->getChampionnatId(), isset($id_detail) ? $id_detail : "");
	$fxlist->FXSetPagination("stats_joueurs_fannys.php".(isset($id_detail) ? "?joueur_ou_equipe=".$joueur_ou_equipe."&id_detail=".$id_detail : ""));
}
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
