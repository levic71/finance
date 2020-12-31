<?

include "../include/sess_context.php";

session_start();

$jorkyball_redirect_exception = 1;

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

if (isset($sess_context) && $sess_context->isChampionnatValide())
{
	$menu = new menu("full_access");
	$menu->debut($sess_context->getChampionnatNom());
}
else
{
	$menu = new menu("forum_access");
	$menu->debut("");
}

if (!isset($option)) $option = 1;

?>

<FORM ACTION="envoyer.php">
<INPUT TYPE=HIDDEN NAME=option VALUE=<?= $option ?>>

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

$tab = array();

$tab[] = array("Nom :",     "<INPUT TYPE=TEXT SIZE=28 NAME=reco_nom></INPUT>");
$tab[] = array("Email :",   "<INPUT TYPE=TEXT SIZE=28 NAME=reco_email></INPUT>");
$tab[] = array("Sujet :",   "<INPUT TYPE=TEXT SIZE=28 NAME=reco_sujet></INPUT>");
$tab[] = array("Message :", "<TEXTAREA NAME=reco_corps COLS=50 ROWS=8></TEXTAREA>");

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Envoi d'un message au ".($option == 0 ? "gestionnaire" : "webmaster"), "CENTER");
$fxlist->FXSetColumnsAlign(array("CENTER", "LEFT", "CENTER"));
$fxlist->FXSetColumnsColor(array("#BCC5EA", ""));
$fxlist->FXSetColumnsWidth(array("30%", ""));
$fxlist->FXDisplay();
echo "</TD>";

?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
		<TR><TD ALIGN=RIGHT><INPUT TYPE=SUBMIT VALUE="Envoyer" onClick="javascript:return verif();"></INPUT></TD>
	</TABLE></TD>

</TABLE>
</FORM>

<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
function verif()
{
	if (document.forms[0].reco_email.value == '')
		return confirm('Vous n\'avez pas renseigner l\'email, je ne pourrais donc pas vous répondre, êtes-vous sûr de vouloir continuer ?');
}
</SCRIPT>

<? $menu->end(); ?>
