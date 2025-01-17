<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";
include "../www/ManagerFXList.php";

$db = dbc::connect();

$menu = new menu("forum_access");
$menu->debut("");

$modifier = 0;
// R�cup�ration des infos du joueur � modifier
if (isset($pkeys_where) && $pkeys_where != "")
{
	$select = "SELECT * FROM jb_actualites ".urldecode($pkeys_where);
	$res = dbc::execSQL($select);
	$actu = mysql_fetch_array($res);
	$modifier = 1;
}

$a_id     = ($modifier == 1) ? $actu['id']           : "";
$a_date   = ($modifier == 1) ? ToolBox::mysqldate2date($actu['date']) : date("d/m/Y");
$a_resume = ($modifier == 1) ? $actu['resume']       : "";
$a_texte  = ($modifier == 1) ? $actu['texte']        : "";
$a_lien   = ($modifier == 1) ? $actu['lien']         : "";
$a_alaune = ($modifier == 1) ? $actu['alaune']       : "";

?>

<script src="../js/ts_picker.js"></script>

<form action="<?= $modifier == 1 ? "gestion_actualites_modifier_do.php" : "gestion_actualites_ajouter_do.php" ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="id_item" value="<?= $a_id ?>">

<table border="0" cellpadding="0" cellspacing="0" width="700" summary="">

<?

$tab = array();

$tab[] = array("Date:",     "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" style=\"margin: 0px; padding: 0px;\"><tr><td><input type=\"text\" name=\"date\" size=\"32\" value=\"".$a_date."\" /></td><td><a href=\"#\" onclick=\"javascript:show_calendar('document.forms[0].date', document.forms[0].date.value);\" title=\"Afficher le calendrier\"><img src=\"../images/jorkers/images/calendar.png\" border=0 /></a></td><td><b>(jj/mm/aaaa)</b></td></table>");
$tab[] = array("R�sum�:",   "<textarea cols=\"50\" rows=\"8\" name=\"resume\">".$a_resume."</textarea>");
$tab[] = array("Texte:",    "<textarea cols=\"50\" rows=\"8\" name=\"texte\">".$a_texte."</textarea>");
$tab[] = array("Lien:",     "<input type=\"text\" name=\"lien\"   size=\"32\" value=\"".$a_lien."\"   />");
$tab[] = array("A la Une:", "<input type=\"text\" name=\"alaune\" size=\"32\" value=\"".$a_alaune."\" />");

echo "<tr><td colspan=\"2\">";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle(($modifier == 1 ? "Modification" : "Ajout")." d'une actualit�", "center");
$fxlist->FXSetColumnsAlign(array("right", "left"));
$fxlist->FXSetColumnsColor(array("#BCC5EA", ""));
$fxlist->FXSetColumnsWidth(array("20%", ""));
$fxlist->FXDisplay();
echo "</td>";

?>

<tr><td align=right><table border=0>
<tr><td align=left><input type=submit value="Annuler" onclick="return annuler();"></td>
    <td align=left><input type=submit value="<?= $modifier == 1 ? "Modifier" : "Ajouter"?>" onclick="return validate_and_submit();"></td>
</table></td>

</table>

<script>
function validate_and_submit()
{

	return true;
}
function annuler()
{
	document.forms[0].action='gestion_actualites.php';

	return true;
}
document.forms[0].date.focus();
</script>

</form>

<? $menu->end(); ?>
