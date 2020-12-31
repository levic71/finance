<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

$select = "SELECT * FROM jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getJourneeId();
$res = dbc::execSQL($select);
$journee = mysql_fetch_array($res);

$select = "SELECT * FROM jb_joueurs WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id NOT IN (".$journee['joueurs'].");";
$res = dbc::execSQL($select);

$aff = 0;
$option = "<SELECT NAME=joueur><OPTION VALUE=\"\">";
while($equipe = mysql_fetch_array($res))
{
	$option .= "<OPTION VALUE=\"".$equipe['id']."\"> ".$equipe['pseudo'];
	$aff++;
}
$option .= "</SELECT>";

if ($aff == 0) ToolBox::do_redirect("matchs.php?errno=3");

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

?>

<!-- TABLEAU DU CENTRE --------------------------- -->
<FORM ACTION="journees_ajouter_joueur_do.php">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=500>

<?

$tab = array();

$tab[] = array("Joueur à ajouter", $option);

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Ajouter un joueur", "CENTER");
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
	if (document.forms[0].joueur.value == '')
	{
		alert('Vous devez sélectionnez un joueur ...');
		return false;
	}

	return true;
}
</SCRIPT>

<? $menu->end(); ?>
