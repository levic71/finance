<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

$select = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getChampionnatId();
$res = dbc::execSQL($select);
while($row = mysql_fetch_array($res)) $equipes[$row['id']] = $row;

$select = "SELECT * FROM jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getjourneeId();
$res = dbc::execSQL($select);
$journee = mysql_fetch_array($res);

// On récupère les equipes déjà présentes
$items = explode(',', $journee['equipes']);
foreach($items as $i) $eq_presentes[$i] = $i;

// On reconstitue le champ 'equipes'
$option = "<SELECT NAME=equipe><OPTION VALUE=\"\">";
foreach($eq_presentes as $eq) $option .= "<OPTION VALUE=\"".$eq."\"> ".$equipes[$eq]['nom'];
$option .= "</SELECT>";

?>

<!-- TABLEAU DU CENTRE --------------------------- -->
<FORM ACTION="journees_supprimer_equipe_do.php">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=500>

<?

$tab = array();

$tab[] = array("Equipe à supprimer", $option);

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Supprimer une équipe", "CENTER");
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
	
	return confirm('Etes-vous de vouloir supprimer cette équipe');
}
</SCRIPT>

<? $menu->end(); ?>
