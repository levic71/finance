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

?>

<FORM ACTION=albums_themes.php METHOD=POST>
<INPUT TYPE=HIDDEN NAME=type_action VALUE="">
<INPUT TYPE=HIDDEN NAME=pkeys_where VALUE="">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

echo "<TR><TD>";
$fxlist = new FXListAlbumsThemes($sess_context->getRealChampionnatId(), $sess_context->isAdmin());
$fxlist->FXSetPagination("albums_themes.php");
$fxlist->FXDisplay();
echo "</TD>";

if ($sess_context->isAdmin() && !(isset($FXOption) && $FXOption == _FXLIST_EXPORT_))
{
	echo "<TR><TD><TABLE BORDER=0 WIDTH=100%>";
	echo "<TD ALIGN=RIGHT><INPUT TYPE=SUBMIT NAME=bouton VALUE=\"Ajouter un thème\" onClick=\"javascript:ajouter_theme();\"></TD>";
	echo "</TABLE></TD>";
}

?>

</TABLE>

<SCRIPT>
function ajouter_theme()
{
    document.forms[0].action = 'albums_themes_ajouter.php';
}
function modifier_theme(pkeys, action)
{
	document.forms[0].type_action.value=action;
	document.forms[0].pkeys_where.value=pkeys;
    document.forms[0].action = 'albums_themes_ajouter.php';

	document.forms[0].submit();
}
function supprimer_theme(pkeys, action)
{
	if (!confirm('Etes-vous de vouloir supprimer ce theme ?'))
		return false;

	document.forms[0].type_action.value=action;
	document.forms[0].pkeys_where.value=pkeys;
    document.forms[0].action = 'albums_themes_supprimer_do.php';

	document.forms[0].submit();
}
</SCRIPT>

</FORM>

<? if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_) FXList::FXHTLMExportEnd(); else $menu->end(); ?>
