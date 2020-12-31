<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

$req = "SELECT * FROM jb_joueurs WHERE id_champ=".$sess_context->getRealChampionnatId()." ORDER BY pseudo";
$res = dbc::execSQL($req);
if (mysql_num_rows($res) == 0) ToolBox::do_redirect("equipes.php?errno=1");

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

?>

<FORM ACTION=equipes_create_all.php METHOD=POST>

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

$tab = array();

$lib = "<SELECT NAME=\"joueur_selected\">";
while($row = mysql_fetch_array($res))
	$lib .= "<OPTION VALUE=\"".$row['id']."\"> ".(strlen($row['pseudo']) > 0 ? $row['pseudo'] : $row['nom']." ".$row['prenom']);
$lib .= "</SELECT>";
$tab[] = array("Joueur:", $lib);

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Sélection du joueur", "CENTER");
$fxlist->FXSetColumnsAlign(array("RIGHT", "LEFT"));
$fxlist->FXSetColumnsColor(array("#BCC5EA", ""));
$fxlist->FXSetColumnsWidth(array("35%", ""));
$fxlist->FXDisplay();
echo "</TD>";

?>

<SCRIPT>
function annuler()
{
	document.forms[0].action='equipes.php';

	return true;
}
</SCRIPT>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
<TR><TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Annuler" onclick="return annuler();"></INPUT></TD>
    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Valider"></INPUT></TD>
</TABLE></TD>

</TABLE>
</FORM>

<? $menu->end(); ?>