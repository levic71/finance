<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

// Récupération des equipes qui participent à la saion courante
$sss = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());
$lst_equipes_saison = $sss->getListeEquipes();

// Récupération de tous les equipes du championnat (ttes saisons confondues)
$ses = new SQLEquipesServices($sess_context->getRealChampionnatId());
$lst_equipes = $ses->getListeEquipes();

$input1 = "<SELECT NAME=j1 MULTIPLE SIZE=8 onChange=\"javascript:SBox_TestSelection(this);\">";
$input2 = "<SELECT NAME=j2 MULTIPLE SIZE=8 onChange=\"javascript:SBox_TestSelection(this);\">";
$input1 .= "<OPTION VALUE=0> __________________________________";
$input2 .= "<OPTION VALUE=0> __________________________________";
foreach($lst_equipes as $row)
{
	if (!isset($lst_equipes_saison[$row['id']]))
		$input1 .= "<OPTION VALUE=".$row['id'].">".$row['nom'];
}
$input1 .= "</SELECT>";
$input2 .= "</SELECT>";

?>

<SCRIPT>
function validate_and_submit()
{
	document.forms[0].selection.value='';
	document.forms[0].nb_selection.value=0;

	nb_sel=document.forms[0].j2.length;

	for(i=1; i < nb_sel; i++)
	{
		document.forms[0].j2.options[i].selected=true;
		document.forms[0].selection.value += document.forms[0].selection.value == '' ? document.forms[0].j2.options[i].value : '|'+document.forms[0].j2.options[i].value;
	}
	
	document.forms[0].nb_selection.value=nb_sel-1;

	document.forms[0].j2.options[0].selected=false;

	return true;
}
function annuler()
{
	document.forms[0].action='equipes.php';

	return true;
}
</SCRIPT>

<FORM ACTION="equipes_get_from_saisons_do.php" METHOD=POST ENCTYPE="multipart/form-data">
<INPUT TYPE=HIDDEN NAME=selection    VALUE="">
<INPUT TYPE=HIDDEN NAME=nb_selection VALUE="0">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

$tab = array();


$inputs  = "<TABLE BORDER=0><TR><TD ALIGN=LEFT><TABLE BORDER=0><TR><TD><IMG SRC=../forum/smileys/icon11.gif BORDER=0></TD><TD ALIGN=LEFT>Liste</TD></TABLE></TD><TD></TD><TD ALIGN=RIGHT><TABLE BORDER=0><TR><TD><IMG SRC=../forum/smileys/icon6.gif BORDER=0></TD><TD ALIGN=LEFT>Sélectionnés</TD></TABLE></TD>";
$inputs .= "<TR><TD>".$input1."</TD>";
$inputs .= "<TD WIDTH=60 ALIGN=CENTER><TABLE BORDER=0><TR><TD><A HREF=\"#\"><IMG SRC=../images/small_right.gif onClick=\"javascript:SBox_SversD(document.forms[0].j1, document.forms[0].j2);\"  BORDER=0></A></TD><TR><TD><A HREF=\"#\"><IMG SRC=../images/small_left.gif onClick=\"javascript:SBox_DversS(document.forms[0].j1, document.forms[0].j2);\" BORDER=0></A></TD></TABLE></TD>";
$inputs .= "<TD>".$input2."</TD>";
$inputs .= "</TABLE>";
$tab[] = array("Equipes:", $inputs);

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Récupération d'une équipe d'une autre saison", "CENTER");
$fxlist->FXSetColumnsAlign(array("RIGHT", "LEFT"));
$fxlist->FXSetColumnsColor(array("#BCC5EA", ""));
$fxlist->FXSetColumnsWidth(array("20%", ""));
$fxlist->FXDisplay();
echo "</TD>";

?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
<TR><TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Annuler" onclick="return annuler();" /></TD>
    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Ajouter" onclick="return validate_and_submit();" /></TD>
</TABLE></TD>

</TABLE>
</FORM>

<? $menu->end(); ?>
