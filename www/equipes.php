<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_)
	FXList::FXHTLMExportBegin();
else
{
	$menu = new menu("full_access");
	$menu->debut($sess_context->getChampionnatNom(), "04");
}

?>

<FORM ACTION=equipes.php METHOD=post>
<INPUT TYPE=HIDDEN NAME=type_action VALUE="" />
<INPUT TYPE=HIDDEN NAME=pkeys_where VALUE="" />

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

if (isset($errno) && $errno == 1) ToolBox::alert("Pour créer toutes les équipes d'un joueur, il faut au préalable saisir au moins un joueur !");
if (isset($errno) && $errno == 2) ToolBox::alert('Pour ajouter une équipe, il faut au préalable ajouter les joueurs qui la composent ...\nAller dans le menu JOUEURS');

echo "<TR><TD>";
$fxlist = new FXListTeams($sess_context->getRealChampionnatId(), $sess_context->getChampionnatType(), $sess_context->getChampionnatId(), $sess_context->isAdmin());
$fxlist->FXSetPagination("equipes.php");
$fxlist->FXDisplay();
echo "</TD>";

if ($sess_context->isAdmin() && !(isset($FXOption) && $FXOption == _FXLIST_EXPORT_))
{
	echo "<TR><TD><TABLE BORDER=0 WIDTH=100% SUMMARY=\"\">";
	echo "<TD ALIGN=right><INPUT TYPE=SUBMIT NAME=bouton VALUE=\"Ajouter une équipe\" onClick=\"javascript:ajouter_equipe();\" /></TD>";
	echo "</TABLE></TD>";
}

?>

</TABLE>

<? if ($sess_context->isAdmin()) { ?>
<DIV CLASS=cmdbox>
<? if ($sess_context->getChampionnatType() != _TYPE_LIBRE_) { ?>
<div><a CLASS=cmd href="equipes_get_from_saisons.php">Récupérer des équipes de saisons différentes</a></div>
<? } ?>
<? if ($sess_context->getChampionnatType() == _TYPE_LIBRE_) { ?>
<div><a CLASS=cmd href="equipes_create_forone.php">Création de toutes les équipes pour un joueur</a></div>
<div><a CLASS=cmd href="equipes_create_all.php">Création de toutes les équipes possibles</a></div>
<? } ?>
</DIV>
<? } ?>

<SCRIPT type="text/javascript">
function ajouter_equipe()
{
    document.forms[0].action = 'equipes_ajouter.php';
}
function modifier_equipe(pkeys, action)
{
	document.forms[0].type_action.value=action;
	document.forms[0].pkeys_where.value=pkeys;
    document.forms[0].action = 'equipes_ajouter.php';

	document.forms[0].submit();
}
function supprimer_equipe(pkeys, action)
{
	if (!confirm('Cette suppression s\'applique sur toutes saisons, êtes-vous de vouloir supprimer cette équipe ? De plus tous les matchs joués par cette équipe seront supprimés, il faudra penser à synchroniser manuellement les journées où cette équipe à jouer.'))
		return false;

	document.forms[0].type_action.value=action;
	document.forms[0].pkeys_where.value=pkeys;
    document.forms[0].action = 'equipes_supprimer_do.php';

	document.forms[0].submit();
}
</SCRIPT>

</FORM>

<? if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_) FXList::FXHTLMExportEnd(); else $menu->end(); ?>
