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
// Récupération des infos du joueur à modifier
if (isset($pkeys_where) && $pkeys_where != "")
{
	$select = "SELECT * FROM jb_videos ".urldecode($pkeys_where);
	$res = dbc::execSQL($select);
	$actu = mysql_fetch_array($res);
	$modifier = 1;
}

$a_id     = ($modifier == 1) ? $actu['id']          : "";
$a_date   = ($modifier == 1) ? ToolBox::mysqldate2date($actu['date']) : date("d/m/Y");
$a_titre  = ($modifier == 1) ? $actu['titre']       : "";
$a_desc   = ($modifier == 1) ? $actu['description'] : "";
$a_url    = ($modifier == 1) ? $actu['url']         : "";

?>

<script src="../js/ts_picker.js"></script>

<form action="<?= $modifier == 1 ? "gestion_videos_modifier_do.php" : "gestion_videos_ajouter_do.php" ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="id_item" value="<?= $a_id ?>">

<table border="0" cellpadding="0" cellspacing="0" width="700" summary="">

<?

$tab = array();

$tab[] = array("Date:",        "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" style=\"margin: 0px; padding: 0px;\"><tr><td><input type=\"text\" name=\"date\" size=\"32\" value=\"".$a_date."\" /></td><td><a href=\"#\" onclick=\"javascript:show_calendar('document.forms[0].date', document.forms[0].date.value);\" title=\"Afficher le calendrier\"><img src=\"../images/jorkers/images/calendar.png\" border=0 /></a></td><td><b>(jj/mm/aaaa)</b></td></table>");
$tab[] = array("Titre:",       "<textarea cols=\"50\" rows=\"8\" name=\"titre\">".$a_titre."</textarea>");
$tab[] = array("Description:", "<textarea cols=\"50\" rows=\"8\" name=\"desc\">".$a_desc."</textarea>");
$tab[] = array("Url:",         "<input type=\"text\" name=\"url\"   size=\"32\" value=\"".$a_url."\"   />");

echo "<tr><td colspan=\"2\">";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle(($modifier == 1 ? "Modification" : "Ajout")." d'une vidéo", "center");
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
	document.forms[0].action='gestion_videos.php';

	return true;
}
document.forms[0].date.focus();
</script>

</form>

<? $menu->end(); ?>
