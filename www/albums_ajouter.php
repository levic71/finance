<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

$modifier = 0;
// Récupération des infos du joueur à modifier
if (isset($pkeys_where) && $pkeys_where != "")
{
	$select = "SELECT * FROM jb_albums ".urldecode($pkeys_where);
	$res = dbc::execSQL($select);
	$row = mysql_fetch_array($res);
	$modifier = 1;
}

$t_id          = ($modifier == 1) ? $row['id']           : "";
$t_commentaire = ($modifier == 1) ? $row['commentaire']  : "";
$t_photo       = ($modifier == 1) ? $row['photo']        : "";
$t_date        = ($modifier == 1) ? ToolBox::mysqldate2date($row['date']) : date("d/m/Y");

?>

<SCRIPT SRC="../js/ts_picker.js"></SCRIPT>

<FORM ACTION=<?= $modifier == 1 ? "albums_modifier_do.php" : "albums_ajouter_do.php" ?> METHOD=POST ENCTYPE="multipart/form-data">
<INPUT TYPE=HIDDEN NAME=MAX_FILE_SIZE VALUE=50000>
<INPUT TYPE=HIDDEN NAME=id_photo  VALUE="<?= $t_id ?>">
<INPUT TYPE=HIDDEN NAME=id_theme VALUE="<?= $id_theme ?>">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

$tab = array();

$tab[] = array("Commentaire:", "<INPUT TYPE=TEXT NAME=commentaire SIZE=32 VALUE=\"".$t_commentaire."\" onKeyUp='javascript:changeColor(this);'>");
$tab[] = array("Photo:",       "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0><TR><TD><INPUT TYPE=FILE NAME=photo SIZE=32></INPUT></TD><TD WIDTH=3> </TD><TD>".($t_photo != "" ? "<a HREF=\"#\" onmouseover=\"show_info_upleft('<IMG SRC=".$t_photo." border=1>', event);\" onmouseout=\"close_info();\"><IMG SRC=".$t_photo." BORDER=1 HEIGHT=20 WIDTH=20></A>" : "")."</TD><TR><TD ALIGN=LEFT COLSPAN=3>50Ko maximum, jpg ou gif</TD></TABLE>");
$tab[] = array("Date:",        "<TABLE BORDER=0 CELLPADDING=2 CELLSPACING=0><TR><TD><INPUT TYPE=TEXT  NAME=date_creation SIZE=32 VALUE=\"".$t_date."\"></INPUT></TD><TD><a href=\"#\" onClick=\"javascript:show_calendar('document.forms[0].date_creation', document.forms[0].date_creation.value);\" title=\"Afficher le calendrier\"><img src=\"../images/images_cal/c_b.gif\" border=0/></a></TD><TD><B>(JJ/MM/AAAA)</B></TD></TABLE>");
echo "<TR><TD COLSPAN=2>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle(($modifier == 1 ? "Modification" : "Ajout")." d'une photo", "CENTER");
$fxlist->FXSetColumnsAlign(array("RIGHT", "LEFT"));
$fxlist->FXSetColumnsColor(array("#BCC5EA", ""));
$fxlist->FXSetColumnsWidth(array("30%", ""));
$fxlist->FXDisplay();
echo "</TD>";

?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
<TR><TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Annuler" onClick="return annuler();"></INPUT></TD>
    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="<?= $modifier == 1 ? "Modifier" : "Ajouter"?>" onClick="return validate_and_submit();"></INPUT></TD>
</TABLE></TD>

</TABLE>

<SCRIPT>
function validate_and_submit()
{
    if (!verif_JJMMAAAA(document.forms[0].date_nais.value, 'Date de naissance'))
		return false;

	return true;
}
function annuler()
{
	document.forms[0].action='albums.php';

	return true;
}
document.forms[0].commentaire.focus();
</SCRIPT>

</FORM>

<? $menu->end(); ?>
