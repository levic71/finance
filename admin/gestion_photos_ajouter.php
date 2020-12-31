<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";
include "../www/ManagerFXList.php";

$db = dbc::connect();

$menu = new menu("forum_access");
$menu->debut("", "", "initEditor()");

$modifier = 0;
// Récupération des infos du joueur à modifier
if (isset($pkeys_where) && $pkeys_where != "")
{
	$select = "SELECT * FROM jb_forum ".urldecode($pkeys_where);
	$res = dbc::execSQL($select);
	$msg = mysql_fetch_array($res);
	$modifier = 1;
}

$a_id      = ($modifier == 1) ? $msg['id']      : "";
$a_date    = ($modifier == 1) ? ToolBox::mysqldate2date($msg['date']) : date("d/m/Y");
$a_nom     = ($modifier == 1) ? $msg['nom']     : "";
$a_title   = ($modifier == 1) ? $msg['title']   : "{photo}";
$a_message = ($modifier == 1) ? $msg['message'] : "";
$a_image   = ($modifier == 1) ? $msg['image'] : "";

?>

<script src="../js/ts_picker.js"></script>

<form action="<?= $modifier == 1 ? "gestion_photos_modifier_do.php" : "gestion_photos_ajouter_do.php" ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="id_item" value="<?= $a_id ?>">

<table border="0" cellpadding="0" cellspacing="0" width="700" summary="">

<?

$tab = array();

$tab[] = array("Date:",    "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" style=\"margin: 0px; padding: 0px;\"><tr><td><input type=\"text\" name=\"date\" size=\"32\" value=\"".$a_date."\" /></td><td><a href=\"#\" onclick=\"javascript:show_calendar('document.forms[0].date', document.forms[0].date.value);\" title=\"Afficher le calendrier\"><img src=\"../images/jorkers/images/calendar.png\" border=0 /></a></td><td><b>(jj/mm/aaaa)</b></td></table>");
$tab[] = array("Auteur:",  "<input type=\"text\" name=\"nom\"   size=\"64\" value=\"".$a_nom."\" />");
$tab[] = array("Titre:",   "<input type=\"text\" name=\"title\" size=\"64\" value=\"".$a_title."\" />");
$tab[] = array("Message:", "<textarea cols=\"64\" rows=\"12\" id=\"ta\" name=\"ta\">".$a_message."</textarea>");
$tab[] = array("Image:",   "<input type=\"file\" name=\"image\" /><a href=\"#\" onmouseover=\"show_info_upright('<img src=".$a_image." />', event);\" onmouseout=\"close_info();\"><img src=\"".$a_image."\" height=\"16\" width=\"16\" /></a>");
$tab[] = array("xImage:",   "<input type=\"file\" name=\"ximage\" /><a href=\"#\" onmouseover=\"show_info_upright('<img src=".str_replace("uploads/", "uploads/x", $a_image)." />', event);\" onmouseout=\"close_info();\"><img src=\"".str_replace("uploads/", "uploads/x", $a_image)."\" height=\"16\" width=\"16\" /></a>");

echo "<tr><td colspan=\"2\">";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle(($modifier == 1 ? "Modification" : "Ajout")." d'une photo", "center");
$fxlist->FXSetColumnsAlign(array("left", "left"));
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
	document.forms[0].action='gestion_photos.php';

	return true;
}
document.forms[0].date.focus();
</script>

</form>

<? $menu->end(); ?>
