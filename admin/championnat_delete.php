<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";
include "../www/ManagerFXList.php";

$db = dbc::connect();

$select = "SELECT * FROM jb_championnat ORDER BY nom;";
$res = dbc::execSQL($select);
$option = "<SELECT NAME=championnat><OPTION VALUE=\"\">";
while($c = mysql_fetch_array($res)) $option .= "<OPTION VALUE=\"".$c['id']."\"> ".$c['id']." - ".$c['nom'];
$option .= "</SELECT>";

?>

<HTML>
<BODY>
<FORM ACTION="championnat_delete_do.php">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=500>

<?

$tab = array();

$tab [] = array("Championnat à supprimer", $option);

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Suppression d'un championnat", "CENTER");
$fxlist->FXSetColumnsAlign(array("RIGHT", "LEFT"));
$fxlist->FXSetColumnsColor(array("#BCC5EA", ""));
$fxlist->FXSetColumnsWidth(array("40%", ""));
$fxlist->FXDisplay();
echo "</TD>";
?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
		<TR><TD ALIGN=RIGHT><INPUT TYPE=RESET  VALUE="Annuler" onClick="return cancelForm();"></INPUT></TD>
            <TD ALIGN=RIGHT><INPUT TYPE=SUBMIT VALUE="Supprimer" onClick="return checkForm();"></INPUT></TD>
    </TABLE></TD>

</TABLE>
</FORM>

<SCRIPT>
function cancelForm()
{
	document.forms[0].action='superuser_fcts.php';
	document.forms[0].submit();
}
function checkForm()
{
	if (document.forms[0].championnat.value == '')
	{
		alert('Vous devez sélectionnez un championnat ...');
		return false;
	}

	return confirm('Etes-vous de vouloir supprimer ce championnat');
}
</SCRIPT>

</BODY>
</HTML>

