<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";
include "../www/ManagerFXList.php";

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom(), "13");

if (isset($admin_valid) && $admin_valid="no")
	ToolBox::alert("Identification gestionnaire incorrect, veuillez recommencer ...");

?>

<!-- TABLEAU DU CENTRE -->
<FORM ACTION="admin_valid.php">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=500>

<?

$tab = array();

$tab[] = array("Login:", "<INPUT TYPE=TEXT SIZE=26 MAXLENGTH=16 NAME=login></INPUT>");
$tab[] = array("Mot de passe :", "<INPUT TYPE=PASSWORD SIZE=26 MAXLENGTH=16 NAME=pwd></INPUT>");

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Administration Championnat", "CENTER");
$fxlist->FXSetColumnsAlign(array("RIGHT", "LEFT"));
$fxlist->FXSetColumnsColor(array("#BCC5EA", ""));
$fxlist->FXSetColumnsWidth(array("35%", ""));
$fxlist->FXDisplay();
echo "</TD>";

?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
		<TR><TD><INPUT TYPE=SUBMIT VALUE="Administrer" onClick="return checkForm();"></INPUT></TD>
		    <TD><INPUT TYPE=SUBMIT VALUE="Mot de passe oublié" onClick="return mail2adm();"></INPUT></TD>
	</TABLE></TD>

</TABLE>
</FORM>

<SCRIPT>
function mail2adm()
{
	if (confirm('Cette option va envoyer le mot de passe d\'administration par mail au gestionnaire du championnat.\nVoulez-vous continuer ?'))
	{
		document.forms[0].action='mail2adm.php';
		return true;
	}
	else
		return false;
}

function checkForm()
{
	if (verif_alphanum2(document.forms[0].login.value, 'Login', 6) == false)
		return false;
	if (verif_alphanum2(document.forms[0].pwd.value, 'Mot de passe', 6) == false)
		return false;

	return true;
}
document.forms[0].login.focus();
</SCRIPT>

<? $menu->end(); ?>
