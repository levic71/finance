<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

$select = "SELECT * FROM jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getJourneeId();
$res = dbc::execSQL($select);
$journee = mysql_fetch_array($res);

?>

<FORM ACTION="journees_renommer_do.php">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=500>

<?

$tab = array();

$tab[] = array("", "La syntaxe pour le nom de la journée est : <ul><li>\"numéro de la journée:nom de la journée\"</li><li>ex1: \"9:Ouverture du championnat\" => Affichera \"Ouverture du championnat\" comme nom de journée</li><li>ex2: \"9:\" => Affichera \"9ième journée\" comme nom de journée</li></ul>");
$tab[] = array("Nom :", "<input type=text size=32 name=nom value=\"".$journee['nom']."\" />");

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Renommer le nom de la journée", "CENTER");
$fxlist->FXSetColumnsAlign(array("RIGHT", "LEFT"));
$fxlist->FXSetColumnsColor(array("#BCC5EA", ""));
$fxlist->FXSetColumnsWidth(array("35%", ""));
$fxlist->FXDisplay();
echo "</TD>";

?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
		<TR><TD ALIGN=RIGHT><INPUT TYPE=SUBMIT VALUE="Annuler" onClick="return cancelForm();"></INPUT></TD>
            <TD ALIGN=RIGHT><INPUT TYPE=SUBMIT VALUE="Valider" onClick="return checkForm();"></INPUT></TD>
    </TABLE></TD>

</TABLE>
</FORM>

<SCRIPT>
function cancelForm()
{
	document.forms[0].action='matchs.php';
	document.forms[0].submit();
}
function checkForm()
{
	return true;
}
</SCRIPT>

<? $menu->end(); ?>
