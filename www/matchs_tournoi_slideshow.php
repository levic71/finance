<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

// On récupère les infos de la journée en session
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$journee = $sjs->getJournee();
$consolante = $journee['tournoi_consolante'];

$options = array();
$options[] = "Détail Poules";
$options[] = $libelle_phase_finale[_PHASE_PLAYOFF_];
$options[] = $libelle_phase_finale[_PHASE_CONSOLANTE1_];
if ($consolante > 0) $options[] = $libelle_phase_finale[_PHASE_CONSOLANTE2_];
$options[] = "Classement Tournoi";

?>
<SCRIPT type="text/javascript">
var options = new Array(<?
$i=0;
reset($options);
foreach($options as $item)
{
	echo ($i++ == 0 ? "'" : ",'").$i."|".$item."'";
}
?>);
var options_affectes = new Array(<?
?>);
</SCRIPT>

<FORM ACTION="matchs_tournoi_slideshow.php" METHOD=POST>
<INPUT TYPE="HIDDEN" NAME=selection VALUE="">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

$tab = array();

$lib = "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=1 WIDTH=100%>";
$lib .= "<TR><TD ALIGN=LEFT WIDTH=45%><B><SMALL> Liste des options : </SMALL></B></TD>";
$lib .= "    <TD WIDTH=60 ALIGN=CENTER ROWSPAN=2><TABLE BORDER=0><TR><TD><A HREF=\"#\"><IMG SRC=../images/small_right.gif onClick=\"javascript:SBox_SversD(document.forms[0].source, document.forms[0].cible);\"  BORDER=0></A></TD><TR><TD><A HREF=\"#\"><IMG SRC=../images/small_left.gif onClick=\"javascript:SBox_DversS(document.forms[0].source, document.forms[0].cible);\" BORDER=0></A></TD></TABLE></TD>";
$lib .= "    <TD ALIGN=LEFT WIDTH=45%><B><SMALL> Options sélectionnées : </SMALL></B></TD>";
$lib .= "    <TD WIDTH=60 ALIGN=CENTER ROWSPAN=2><TABLE BORDER=0><TR><TD><A HREF=\"#\"><IMG SRC=../images/small_up.gif onClick=\"javascript:SBox_Up(document.forms[0].cible);\"  BORDER=0></A></TD><TR><TD><A HREF=\"#\"><IMG SRC=../images/small_down.gif onClick=\"javascript:SBox_Down(document.forms[0].cible);\" BORDER=0></A></TD></TABLE></TD>";
$lib .= "<TR><TD ALIGN=LEFT><SELECT NAME=\"source\" SIZE=10 MULTIPLE onChange=\"javascript:SBox_TestSelection(this);\">";
$lib .= "    <TD ALIGN=LEFT><SELECT NAME=\"cible\"  SIZE=10 MULTIPLE onChange=\"javascript:SBox_TestSelection(this);\">";
$lib .= "</SELECT></TD>";
$lib .= "</TABLE>";
$tab[] = array($lib);

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Choix des options du slide show", "CENTER");
$fxlist->FXSetMouseOverEffect(false);
$fxlist->FXDisplay();
echo "</TD>";

?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
	<TR><TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Annuler" onclick="return annuler();"></INPUT></TD>
	    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Lancer" onclick="return validate_and_submit();"></INPUT></TD>
	</TABLE></TD>

<SCRIPT type="text/javascript">
function validate_and_submit()
{
	document.forms[0].selection.value='';
	
	chaine = '';
	nb_sel=document.forms[0].cible.length;
	for(i=1; i < nb_sel; i++)
	{
		document.forms[0].cible.options[i].selected=true;
		chaine += chaine == '' ? document.forms[0].cible.options[i].value : ','+document.forms[0].cible.options[i].value;
	}
	document.forms[0].cible.options[0].selected=false;

	document.forms[0].selection.value = chaine;

	if (chaine == '')
	{
		alert('Vous devez sélectionner au moins une option ...');
		return false;
	}
	
	window.open('matchs_tournoi.php?options_type_matchs=SLIDE', 'slide_show', 'width=900, height=600, resizable=yes, alwaysRaised=yes, toolbar=no, location=no, personnalBar=no, status=no, menuBar=no');

	return false;
}
function annuler()
{
	document.forms[0].action='../www/matchs_tournoi.php?pkeys_where_jb_journees=<?= urlencode(" WHERE id=".$journee['id']) ?>';

	return true;
}
function chargeListe(liste, tab)
{
	SBox_Vider(liste);
	SBox_Ajout_Item(liste,  '______________________________________', 0, false);
	for(i=0; i < tab.length; i++)
	{
		var col = tab[i].split("|");
		SBox_Ajout_Item(liste, col[1], col[0], false);
	}
}

chargeListe(document.forms[0].source, options);
chargeListe(document.forms[0].cible, options_affectes);
</SCRIPT>

</TABLE>
</FORM>

<? $menu->end(); ?>
