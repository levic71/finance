<?

include "../include/sess_context.php";

session_start();

if ((isset($dual) && $dual == 5)) $jorkyball_redirect_exception = 1;

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

if (!isset($dual)) $dual = 0;
if ($dual == 0)	$refresh_page_forum = 1;

$db = dbc::connect();

if (isset($force_admin) && $force_admin == 1)
	$sess_context->setAdmin();

if (($dual == 5 || $dual == 2 || $dual == 3) && isset($sess_context) && $sess_context->getRealChampionnatId() == 0)
{
	$champ['championnat_id']   = 0;
	$champ['championnat_nom']  = "Forum général";
	$champ['type'] = 0;
	$sess_context->setChampionnat($champ);
	$menu_option = "forum_access";
}
else
	$menu_option = "full_access";

if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_)
	FXList::FXHTLMExportBegin();
else
{
	$menu = new menu($menu_option);
	$menu->debut($sess_context->getChampionnatNom());
}

if (isset($errno) && $errno == 1) ToolBox::alert('Pb sur message, nom vide ...');

?>

<form action="forum_message.php" method="post">
<input type="hidden" name="id_msg2del" value="" />
<input type="hidden" name="dual" value="<?= $dual ?>" />

<table border="0" cellpadding="0" cellspacing="0" width="700" summary="tab central">

<?

echo "<tr><td>";
$fxlist = new FXListForum($dual == 2 || $dual == 3 ? 0 : $sess_context->getRealChampionnatId(), $sess_context->isAdmin(), $dual);
if ($menu_option == "forum_access")
{
	if ($dual == 2)
		$titre_liste = "Sélection des photos de la semaine";
	else if ($dual == 3)
		$titre_liste = "Le saviez-vous ?";
	else
		$titre_liste = "<a href=\"whois.php\" class=\"white\">Forum général</A>";
}
else
{
	if ($dual == 2)
		$titre_liste = "Sélection des photos de la semaine";
	else if ($dual == 3)
		$titre_liste = "Le saviez-vous ?";
	else if ($dual == 5)
		$titre_liste = "<a href=\"whois.php\" class=\"white\">Forum général</a>";
	else
		$titre_liste = "<a href=\"whois.php\" class=\"white\">Forum</a>";
}

$fxlist->FXSetTitle($titre_liste, "center");
$fxlist->FXDisplay();
echo "</td>";

if (!(isset($FXOption) && $FXOption == _FXLIST_EXPORT_) && ($dual == 0 || $dual == 5)) {
?>
<tr><td align="right"><table border="0" summary="">
<tr><td align="left"><input type="submit" value="Nouveau Message" onclick="javascript:return submit_validation();" /></td>
</table></td>
<? } ?>

<?	if ($dual == 2) { ?>
	<tr><td><div class="allaccess" style="float: right; padding: 10px 5px 0px 0px;"><a href="mailto:contact@jorkers.com">Participer</a></div></td>
<? } ?>

</table>

<script type="text/javascript">
function submit_validation()
{
<? if ($lock == 0) { ?>
	document.forms[0].submit();
<? } else { ?>
	alert('Le site est en cours de maintenance, cette fonctionnalité est indisponible pour l\'instant ...');
	return false;
<? } ?>
}
function modifier_message(msg)
{
	document.forms[0].id_msg2del.value=msg;
    document.forms[0].action = 'forum_message.php';

	document.forms[0].submit();
}
function supprimer_msg(msg)
{
	if (!confirm('Etes-vous de vouloir supprimer ce message ?'))
	return false;

	document.forms[0].id_msg2del.value=msg;
	document.forms[0].action = 'forum_delmsg_do.php';

	document.forms[0].submit();
}
</script>

</form>

<? if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_) FXList::FXHTLMExportEnd(); else $menu->end(); ?>
