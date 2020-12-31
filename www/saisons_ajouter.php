<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

// Règles de gestion : 
// Pour les championnats libres   : on récupère tous les joueurs du championnat et on propose de reconduire ceux de la saison en cours par défaut
// Pour les championnats+tournois : on récupère toutes les équipes du championnat/tournoi et on propose de reconduire celle de la saison en cours par défaut

// On récupère tous les joueurs du championnat
$sjs = new SQLJoueursServices($sess_context->getRealChampionnatId());
$joueurs = $sjs->getListeJoueurs();

$ses = new SQLEquipesServices($sess_context->getRealChampionnatId());
$equipes = $ses->getListeEquipes();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

$modifier = 0;
// Récupération des infos de la saison à modifier
if (isset($pkeys_where) && $pkeys_where != "")
{
	$select = "SELECT * FROM jb_saisons ".urldecode($pkeys_where);
	$res = dbc::execSQL($select);
	$saison_courante = mysql_fetch_array($res);
	$modifier = 1;
}

// Récupération des joueurs de la saison en cours
if ($modifier == 1)
{
	$lst_joueurs = $saison_courante['joueurs'];
	$lst_equipes = $saison_courante['equipes'];
}
else
{
	// On récupère les infos de la saison active
	$scs = new SQLChampionnatsServices($sess_context->getRealChampionnatId());
	$saison_active = $scs->getSaisonActive();
	$lst_joueurs = $saison_active['joueurs'];
	$lst_equipes = $saison_active['equipes'];
}

// Formattage des Listes de Sélection
$input1 = "<SELECT NAME=j1 MULTIPLE SIZE=8 onChange=\"javascript:SBox_TestSelection(this);\">";
$input2 = "<SELECT NAME=j2 MULTIPLE SIZE=8 onChange=\"javascript:SBox_TestSelection(this);\">";
$input1 .= "<OPTION VALUE=0> __________________________________";
$input2 .= "<OPTION VALUE=0> __________________________________";

if ($sess_context->getChampionnatType() == _TYPE_LIBRE_)
{
	$res_joueurs = explode(',', $lst_joueurs);
	foreach($res_joueurs as $j) $tab_joueurs[$j] = $j;

	// Répartition des joueurs dans les listes adéquates
	foreach($joueurs as $j)
	{
		if (isset($tab_joueurs[$j['id']]))
			$input2 .= "<OPTION VALUE=".$j['id'].">".$j['nom']." ".$j['prenom'];
		else
			$input1 .= "<OPTION VALUE=".$j['id'].">".$j['nom']." ".$j['prenom'];
	}
}
else
{
	$res_equipes = explode(',', $lst_equipes);
	foreach($res_equipes as $e) $tab_equipes[$e] = $e;

	// Répartition des joueurs dans les listes adéquates
	foreach($equipes as $e)
	{
		if (isset($tab_equipes[$e['id']]))
			$input2 .= "<OPTION VALUE=".$e['id'].">".$e['nom'];
		else
			$input1 .= "<OPTION VALUE=".$e['id'].">".$e['nom'];
	}
}

$input1 .= "</SELECT>";
$input2 .= "</SELECT>";

?>

<SCRIPT SRC="../js/ts_picker.js"></SCRIPT>
<SCRIPT>
function validate_and_submit()
{
	document.forms[0].selection.value='';
	document.forms[0].nb_selection.value=0;

    if (!verif_alphanumext(document.forms[0].nom.value, 'Nom', -1))
		return false;

	nb_sel=document.forms[0].j2.length;

<? if ($sess_context->getChampionnatType() == _TYPE_LIBRE_) { ?>
	if (!(nb_sel > 2))
	{
		alert('Vous devez sélectionner au minimum 2 joueurs ...');
		return false;
	}
<? } ?>

	if (nb_sel >= 2)
	{
		for(i=1; i < nb_sel; i++)
		{
			document.forms[0].j2.options[i].selected=true;
			document.forms[0].selection.value += document.forms[0].selection.value == '' ? document.forms[0].j2.options[i].value : ','+document.forms[0].j2.options[i].value;
		}
	
		document.forms[0].nb_selection.value=nb_sel-1;
	}

	document.forms[0].j2.options[0].selected=false;

	return true;
}
function annuler()
{
	document.forms[0].action='saisons.php';

	return true;
}
</SCRIPT>

<FORM ACTION=<?= $modifier == 1 ? "saisons_modifier_do.php" : "saisons_ajouter_do.php" ?> METHOD=POST ENCTYPE="multipart/form-data">
<? if ($modifier == 1) { ?>
<INPUT TYPE=HIDDEN NAME=saison_id VALUE="<?= $saison_courante['id'] ?>">
<? } ?>
<INPUT TYPE=HIDDEN NAME=selection    VALUE="">
<INPUT TYPE=HIDDEN NAME=nb_selection VALUE="0">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

$tab = array();

$tab[] = array("Nom:", "<INPUT TYPE=TEXT NAME=nom SIZE=40 MAXLENGTH=64 VALUE=\"".(isset($saison_courante['nom']) ? $saison_courante['nom'] : "Saison ".date("Y")."-".(date("Y")+1))."\" onKeyUp='javascript:changeColor(this);'>");

$inputs  = "<TABLE BORDER=0><TR><TD ALIGN=LEFT><TABLE BORDER=0><TR><TD><IMG SRC=../forum/smileys/icon11.gif BORDER=0></TD><TD ALIGN=LEFT>Liste</TD></TABLE></TD><TD></TD><TD ALIGN=RIGHT><TABLE BORDER=0><TR><TD><IMG SRC=../forum/smileys/icon6.gif BORDER=0></TD><TD ALIGN=LEFT>Sélectionnés</TD></TABLE></TD>";
$inputs .= "<TR><TD>".$input1."</TD>";
$inputs .= "<TD WIDTH=60 ALIGN=CENTER><TABLE BORDER=0><TR><TD><A HREF=\"#\"><IMG SRC=../images/small_right.gif onClick=\"javascript:SBox_SversD(document.forms[0].j1, document.forms[0].j2);\"  BORDER=0></A></TD><TR><TD><A HREF=\"#\"><IMG SRC=../images/small_left.gif onClick=\"javascript:SBox_DversS(document.forms[0].j1, document.forms[0].j2);\" BORDER=0></A></TD></TABLE></TD>";
$inputs .= "<TD>".$input2."</TD>";
$inputs .= "</TABLE>";

$tab[] = array($sess_context->getChampionnatType() == _TYPE_LIBRE_ ? "Joueurs:" : "Equipes:", $inputs);

$tab[] = array("Active par défaut:",  "<SELECT NAME=active><OPTION VALUE=1 ".(isset($saison_courante['active']) && $saison_courante['active'] == 1 ? "SELECTED" : "")."> Oui <OPTION VALUE=0 ".(isset($saison_courante['active']) && $saison_courante['active'] == 0 ? "SELECTED" : "")."> Non </SELECT>");

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle(($modifier == 1 ? "Modification" : "Ajout")." d'une Saison", "CENTER");
$fxlist->FXSetColumnsAlign(array("RIGHT", "LEFT"));
$fxlist->FXSetColumnsColor(array("#BCC5EA", ""));
$fxlist->FXSetColumnsWidth(array("15%", ""));
$fxlist->FXDisplay();
echo "</TD>";

?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
<TR><TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Annuler" onclick="return annuler();"></INPUT></TD>
    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="<?= $modifier == 1 ? "Modifier" : "Ajouter"?>" onclick="return validate_and_submit();"></INPUT></TD>
</TABLE></TD>

<TR><TD HEIGHT=20> </TD>

<TR><TD style="border: 1px navy dashed; padding: 5 5 5 5;"> La gestion des saisons permet d'assurer une continuité dans la gestion d'un championnat ou d'un tournoi et surtour elle évite la ressaisie des joueurs et des équipes.
C'est dans cette optique que vous devez sélectionner les joueurs/équipes qui sont reconduit dans la nouvelle saison, ce n'est pas une obligation.
De même, il sera toujours possible de reprendre un joueur ou une équipe ultérieurement.</TD>

</TABLE>
</FORM>

<? $menu->end(); ?>
