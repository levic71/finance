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
	$menu->debut($sess_context->getChampionnatNom(), "06");
}

?>

<FORM ACTION=journees.php METHOD=post ENCTYPE="multipart/form-data">
<INPUT TYPE=HIDDEN NAME=type_action VALUE="" />
<INPUT TYPE=HIDDEN NAME=pkeys_where VALUE="" />
<INPUT TYPE=HIDDEN NAME=id_journee2del VALUE="" />

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

echo "<TR><TD>";
$fxlist = new FXListJournees($sess_context->getChampionnatId(), $sess_context->getChampionnatType(), $sess_context->isAdmin());
$fxlist->FXSetPagination("journees.php");
$fxlist->FXDisplay();
echo "</TD>";

if ($sess_context->isAdmin() && !(isset($FXOption) && $FXOption == _FXLIST_EXPORT_))
{
	echo "<TR><TD><TABLE BORDER=0 WIDTH=100% SUMMARY=\"\">";
	echo "	<TD ALIGN=right><INPUT TYPE=SUBMIT NAME=bouton VALUE=\"Ajouter une journée\" onClick=\"javascript:ajouter_journee();\" /></TD>";
	echo "</TABLE></TD>";
}
?>

</TABLE>

<DIV CLASS=cmdbox>
<div><a CLASS=cmd href="calendar.php">Mode calendrier</a></div>
<? if ($sess_context->isAdmin() && $sess_context->getChampionnatType() == _TYPE_TOURNOI_) { ?>
<div><a class="cmd" href="journees_alias_choisir.php">Création d'une journée alias</a></div>
<div><a class="cmd" href="journees_virtuelles_ajouter.php">Création d'une journée virtuelle</a></div>
<? } ?>
<? if ($sess_context->isAdmin() && $sess_context->getChampionnatType() == _TYPE_CHAMPIONNAT_) { ?>
<div><a class="cmd" href="journees_ajouter_championnat.php">Création automatique des matchs d'une saison</a></div>
<? } ?>
</DIV>

<SCRIPT type="text/javascript">
function ajouter_journee()
{
    document.forms[0].action = '<?= $sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "journees_ajouter_tournoi.php" : "journees_ajouter.php" ?>';
}
function supprimer_journee(pkeys, action)
{
	if (!confirm('Etes-vous de vouloir supprimer cette journées avec les matchs qu\'elle contient ?'))
		return false;

	document.forms[0].type_action.value=action;
	document.forms[0].pkeys_where.value=pkeys;
    document.forms[0].action = 'journees_supprimer_do.php';

	document.forms[0].submit();
}
function supprimer_journee_alias(id_journee)
{
	if (!confirm('Etes-vous de vouloir supprimer cette journées avec les matchs qu\'elle contient ?'))
		return false;

	document.forms[0].id_journee2del.value=id_journee;
    document.forms[0].action = 'journees_alias_supprimer_do.php';

	document.forms[0].submit();
}
</SCRIPT>

</FORM>

<? if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_) FXList::FXHTLMExportEnd(); else $menu->end(); ?>
