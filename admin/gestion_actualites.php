<?

include "../include/sess_context.php";

session_start();

$jorkyball_redirect_exception = 1;

include "../www/common.php";
include "../include/inc_db.php";
include "../www/ManagerFXList.php";

$db = dbc::connect();

if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_)
	FXList::FXHTLMExportBegin();
else
{
	$menu = new menu("forum_access");
	$menu->debut("");
}

?>

<form action="gestion_actualites.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="type_action" value="" />
<input type="hidden" name="pkeys_where" value="" />

<table border="0" cellpadding="0" cellspacing="0" width="700" summary="">

<?

echo "<tr><td>";
$fxlist = new FXListActualites();
$fxlist->FXSetPagination("gestion_actualites.php");
$fxlist->FXDisplay();
echo "</td>";

if (!(isset($FXOption) && $FXOption == _FXLIST_EXPORT_))
{
	echo "<tr><td><table border=\"0\" width=\"100%\" summary=\"\">";
	echo "	<td align=\"right\"><input type=\"submit\" name=\"bouton\" value=\"Ajouter une actualité\" onclick=\"javascript:ajouter_item();\" /></td>";
	echo "</table></td>";
}

?>

</table>

<script type="text/javascript">
function ajouter_item()
{
    document.forms[0].action = 'gestion_actualites_ajouter.php';
}
function modifier_item(pkeys, action)
{
	document.forms[0].type_action.value = action;
	document.forms[0].pkeys_where.value = pkeys;
    document.forms[0].action = 'gestion_actualites_ajouter.php';

	document.forms[0].submit();
}
function supprimer_item(pkeys, action)
{
	if (!confirm('Etes-vous de vouloir supprimer cette actualité ?'))
		return false;

	document.forms[0].type_action.value = action;
	document.forms[0].pkeys_where.value = pkeys;
    document.forms[0].action = 'gestion_actualites_supprimer_do.php';

	document.forms[0].submit();
}
</script>

</form>

<? if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_) FXList::FXHTLMExportEnd(); else $menu->end(); ?>
