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
	$menu->debut($sess_context->getChampionnatNom());
}

if (isset($delete) && $delete == "no")
	Toolbox::alert('Suppression impossible, il faut au moins une saison dans un championnat.');

if (isset($errno) && $errno == 1) ToolBox::alert('On ne peut pas supprimer la saison active !!!');
if (isset($errno) && $errno == 2) ToolBox::alert('Pour modifier le status de cette saison, il faut d\'abord en activer une autre !!!');

?>

<FORM ACTION=saisons.php METHOD=POST ENCTYPE="multipart/form-data">
<INPUT TYPE=HIDDEN NAME=type_action VALUE="">
<INPUT TYPE=HIDDEN NAME=pkeys_where VALUE="">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

echo "<TR><TD>";
$fxlist = new FXListSaisons($sess_context->getRealChampionnatId(), $sess_context->isAdmin());
$fxlist->FXSetPagination("saisons.php");
$fxlist->FXDisplay();
echo "</TD>";

if ($sess_context->isAdmin() && !(isset($FXOption) && $FXOption == _FXLIST_EXPORT_))
{
	echo "<TR><TD><TABLE BORDER=0 WIDTH=100%>";
	echo "	<TD ALIGN=RIGHT><INPUT TYPE=SUBMIT NAME=bouton VALUE=\"Ajouter une saison\" onClick=\"javascript:ajouter_saison();\"></TD>";
	echo "</TABLE></TD>";
}
?>

</TABLE>

<? if ($sess_context->isAdmin() && $sess_context->getChampionnatType() != _TYPE_TOURNOI_) { ?>
<DIV CLASS=cmdbox>
<div><a CLASS=cmd href="joueurs_get_from_saisons.php">Récupérer des joueurs de saisons différentes</a></div>
<div><a CLASS=cmd href="equipes_create_all.php">Créer toutes les équipes possibles</a></div>
</DIV>
<? } ?>

<SCRIPT>
function ajouter_saison()
{
    document.forms[0].action = 'saisons_ajouter.php';
}
function modifier_saison(pkeys, action)
{
	document.forms[0].type_action.value=action;
	document.forms[0].pkeys_where.value=pkeys;
    document.forms[0].action = 'saisons_ajouter.php';

	document.forms[0].submit();
}
function supprimer_saison(pkeys, action)
{
	if (!confirm('Etes-vous de vouloir supprimer cette saison ?'))
		return false;

	document.forms[0].type_action.value=action;
	document.forms[0].pkeys_where.value=pkeys;
    document.forms[0].action = 'saisons_supprimer_do.php';

	document.forms[0].submit();
}
</SCRIPT>

</FORM>

<? if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_) FXList::FXHTLMExportEnd(); else $menu->end(); ?>
