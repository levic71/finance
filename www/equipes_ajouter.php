<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

// On récupère les infos de la saison
$sss = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());
$saison = $sss->getSaison();

// On récupère tous les joueurs du championnat
$select = "SELECT * FROM jb_joueurs WHERE id_champ=".$sess_context->getRealChampionnatId()." ".($saison['joueurs'] == "" ? "" : "AND id IN (".$saison['joueurs'].")")." ORDER BY nom ASC";
$res_joueurs = dbc::execSQL($select);

// On rejette si aucun joueur existant sauf pour les championnats de type 'Championnats/Tournois'
if (mysql_num_rows($res_joueurs) == 0 && $sess_context->getChampionnatType() == _TYPE_LIBRE_) ToolBox::do_redirect("equipes.php?errno=3");
$no_joueurs = (mysql_num_rows($res_joueurs) == 0) ? 1 : 0;

while($row = mysql_fetch_array($res_joueurs))
	$joueurs[$row['id']] = $row;
	
if (isset($error) && $error == 1) ToolBox::alert("Création équipe impossible. Composition déjà existante ...");
if (isset($error) && $error == 2) ToolBox::alert("Nom équipe déjà existant ...");
if (isset($error) && $error == 3) ToolBox::alert("Vous devez saisir des joueurs avant de créer des équipes ...");

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

$modifier = 0;
// Récupération des infos de l'équipe à modifier
if (isset($pkeys_where) && $pkeys_where != "")
{
	$select = "SELECT * FROM jb_equipes ".urldecode($pkeys_where);
	$res = dbc::execSQL($select);
	$equipe = mysql_fetch_array($res);
	$modifier = 1;
}
else
{
	$equipe = array();
	$equipe['nom'] = "";
	$equipe['photo'] = "";
	$equipe['commentaire'] = "";
	$equipe['nb_joueurs'] = 0;
}

$input1 = "<SELECT NAME=j1 MULTIPLE SIZE=8 onChange=\"javascript:SBox_TestSelection(this);\">";
$input2 = "<SELECT NAME=j2 MULTIPLE SIZE=8 onChange=\"javascript:SBox_TestSelection(this);\">";
$input1 .= "<OPTION VALUE=0> __________________________________";
$input2 .= "<OPTION VALUE=0> __________________________________";

// S'il y a des joueurs dans le championnat
if ($no_joueurs == 0)
{
	if ($equipe['nb_joueurs'] > 0)
	{
		$partners = explode('|', $equipe['joueurs']);
		foreach($partners as $partner)
			$input2 .= "<OPTION VALUE=".$joueurs[$partner]['id'].">".$joueurs[$partner]['nom']." ".$joueurs[$partner]['prenom'];
	}

	foreach($joueurs as $joueur)
		$input1 .= "<OPTION VALUE=".$joueur['id'].">".$joueur['nom']." ".$joueur['prenom'];
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
			document.forms[0].selection.value += document.forms[0].selection.value == '' ? document.forms[0].j2.options[i].value : '|'+document.forms[0].j2.options[i].value;
		}
	
		document.forms[0].nb_selection.value=nb_sel-1;
	}

	document.forms[0].j2.options[0].selected=false;

	return true;
}
function annuler()
{
	document.forms[0].action='equipes.php';

	return true;
}
</SCRIPT>

<FORM ACTION=<?= $modifier == 1 ? "equipes_modifier_do.php" : "equipes_ajouter_do.php" ?> METHOD=POST ENCTYPE="multipart/form-data">
<? if ($modifier == 1) { ?>
<INPUT TYPE=HIDDEN NAME=id_equipe   VALUE="<?= ($modifier == 1) ? $equipe['id'] : "" ?>">
<INPUT TYPE=HIDDEN NAME=pkeys_where VALUE="<?= $pkeys_where ?>">
<? } ?>
<INPUT TYPE=HIDDEN NAME=selection    VALUE="">
<INPUT TYPE=HIDDEN NAME=nb_selection VALUE="0">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

$tab = array();

$tab[] = array("Nom:",  	    $sess_context->isAdmin() ? "<INPUT TYPE=TEXT NAME=nom SIZE=40 MAXLENGTH=64 VALUE=\"".(isset($equipe['nom']) && $equipe['nom'] != "" ? $equipe['nom'] : "\" STYLE='background-color: #FFCCCC'")."\" onKeyUp='javascript:changeColor(this);'>" : $equipe['nom']);
$tab[] = array("Photo:",	    $sess_context->isAdmin() ? "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0><TR><TD><INPUT TYPE=FILE NAME=photo SIZE=40></INPUT></TD><TD onmouseover=\"show_info_upleft('<IMG SRC=".$equipe['photo']." border=1>', event);\" onmouseout=\"close_info();\">".($equipe['photo'] != "" ? "<IMG SRC=".$equipe['photo']." BORDER=1 HEIGHT=20 WIDTH=20>" : "")."</TD><TD>50Ko maximum, jpg ou gif</TD></TR></TABLE>" : "<IMG SRC=".$equipe['photo']." BORDER=1 HEIGHT=20 WIDTH=20>");
$tab[] = array("Commentaire:",  $sess_context->isAdmin() ? "<TEXTAREA COLS=40 ROWS=6 NAME=commentaire SIZE=40 MAXLENGTH=64>".$equipe['commentaire']."</TEXTAREA>" : $equipe['commentaire']);

$inputs  = "<TABLE BORDER=0><TR><TD ALIGN=LEFT><TABLE BORDER=0><TR><TD><IMG SRC=../forum/smileys/icon11.gif BORDER=0></TD><TD ALIGN=LEFT>Liste</TD></TABLE></TD><TD></TD><TD ALIGN=RIGHT><TABLE BORDER=0><TR><TD><IMG SRC=../forum/smileys/icon6.gif BORDER=0></TD><TD ALIGN=LEFT>Sélectionnés</TD></TABLE></TD>";
$inputs .= "<TR><TD>".$input1."</TD>";
$inputs .= "<TD WIDTH=60 ALIGN=CENTER><TABLE BORDER=0><TR><TD><A HREF=\"#\"><IMG SRC=../images/small_right.gif onClick=\"javascript:SBox_SversD(document.forms[0].j1, document.forms[0].j2);\"  BORDER=0></A></TD><TR><TD><A HREF=\"#\"><IMG SRC=../images/small_left.gif onClick=\"javascript:SBox_DversS(document.forms[0].j1, document.forms[0].j2);\" BORDER=0></A></TD></TABLE></TD>";
$inputs .= "<TD>".$input2."</TD>";
$inputs .= "</TABLE>";

$tab[] = array("Joueurs:", $inputs);
if ($no_joueurs == 0) $tab[] = array("", "<TABLE BORDER=0><TR VALIGN=CENTER><TD><IMG SRC=../forum/smileys/icon13.gif BORDER=0></TD><TD>Le premier joueur de la liste sera le défenseur par défaut de l'équipe et le second, l'attaquant.</TD></TABLE>");

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle(($modifier == 1 ? "Modification" : "Ajout")." d'une équipe", "CENTER");
$fxlist->FXSetColumnsAlign(array("RIGHT", "LEFT"));
$fxlist->FXSetColumnsColor(array("#BCC5EA", ""));
$fxlist->FXSetColumnsWidth(array("20%", ""));
$fxlist->FXDisplay();
echo "</TD>";

?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
<TR><TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Annuler" onclick="return annuler();"></INPUT></TD>
    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="<?= $modifier == 1 ? "Modifier" : "Ajouter"?>" onclick="return validate_and_submit();"></INPUT></TD>
</TABLE></TD>

</TABLE>
</FORM>

<? $menu->end(); ?>