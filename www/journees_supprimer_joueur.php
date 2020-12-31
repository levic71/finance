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

$select = "SELECT * FROM jb_joueurs WHERE id_champ=".$sess_context->getChampionnatId()." AND id IN (".$journee['joueurs'].");";
$res = dbc::execSQL($select);
while($row = mysql_fetch_array($res)) $joueurs[$row['id']] = $row;

// On reconstitue le champ 'equipes'
$option = "<SELECT NAME=joueur><OPTION VALUE=\"\">";
foreach($joueurs as $j) $option .= "<OPTION VALUE=\"".$j['id']."\"> ".$j['pseudo'];
$option .= "</SELECT>";

?>

<!-- TABLEAU DU CENTRE --------------------------- -->
<FORM ACTION="journees_supprimer_joueur_do.php">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=500>

<?

$tab = array();

$tab[] = array("Joueur à supprimer", $option);

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Supprimer un joueur", "CENTER");
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
	
	return confirm('Etes-vous de vouloir supprimer ce joueur');
}
</SCRIPT>

<? $menu->end(); ?>
