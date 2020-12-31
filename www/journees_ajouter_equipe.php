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

// On récupère les equipes déjà présentes
$items = explode(',', $journee['equipes']);
foreach($items as $i) $eq_presentes[$i] = $i;

$select = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId();
$res = dbc::execSQL($select);

$aff = 0;
$option = "<SELECT NAME=equipe><OPTION VALUE=\"\">";
while($equipe = mysql_fetch_array($res))
{
	if (!isset($eq_presentes[$equipe['id']]))
	{
		$option .= "<OPTION VALUE=\"".$equipe['id']."\"> ".$equipe['nom'];
		$aff++;
	}
}
$option .= "</SELECT>";

if ($aff == 0) ToolBox::do_redirect("matchs.php?errno=1");

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

?>

<!-- TABLEAU DU CENTRE --------------------------- -->
<FORM ACTION="journees_ajouter_equipe_do.php">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=500>

<?

$tab = array();

$tab[] = array("Equipe à ajouter", $option);

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Ajouter une équipe", "CENTER");
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
	if (document.forms[0].equipe.value == '')
	{
		alert('Vous devez sélectionnez une équipe ...');
		return false;
	}

	return true;
}
</SCRIPT>

<? $menu->end(); ?>
