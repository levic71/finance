<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

$joueurs_reguliers      = array();
$joueurs_occasionnels   = array();
$equipes_regulieres     = array();
$equipes_occasionnelles = array();

// ///////////////////////////////////////////////////////////////////
// CHOIX DU TYPE D'AFFICHAGE
// ///////////////////////////////////////////////////////////////////
// 0: choix joueurs/equipes
// 1: joueurs uniquement
// 2: equipes uniquement
// ///////////////////////////////////////////////////////////////////
if (!isset($type_affichage)) $type_affichage = 0;
// ///////////////////////////////////////////////////////////////////

// Récupération des infos de la saison
$sss = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());
$liste_joueurs = $sss->getListeJoueurs();

// Tri des joueurs
while(list($cle, $valeur) = each($liste_joueurs))
{
    if ($valeur['presence'] == _JOUEUR_REGULIER_)
		$joueurs_reguliers[$valeur['id']] = $valeur;
    else
		$joueurs_occasionnels[$valeur['id']] = $valeur;
}

// Récupération des équipes
if ($sess_context->getChampionnatType() == _TYPE_LIBRE_)
{
	$req = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." ORDER BY nom ASC";
	$res = dbc::execSQL($req);
	while($row = mysql_fetch_array($res))
	{
		if ($row['nb_joueurs'] == 0)
		{
			$equipes_regulieres[] = $row;
		}
		else
		{   $attaquant = "";
		    $defenseur = "";
			$item = explode('|', $row['joueurs']);
			$defenseur = $item[0];
			if (isset($item[1])) $attaquant = $item[1];

		    if (isset($joueurs_reguliers[$defenseur]) && isset($joueurs_reguliers[$attaquant]))
				$equipes_regulieres[] = $row;
		    else
				$equipes_occasionnelles[] = $row;
		}
	}
	mysql_free_result($res);
}
else
{
    $liste = $sss->getListeEquipes();
    foreach($liste as $item)
    {
		$equipes_regulieres[] = $item;
	}
}
?>
<SCRIPT>
var joueurs_reg = new Array(<?
$i=0; foreach($joueurs_reguliers as $j) echo ($i++ == 0 ? "'" : ",'").$j['id']."|".str_replace('\'', '\\\'', $j['pseudo'])."'";
?>);
var joueurs_okaz = new Array(<?
$i=0; foreach($joueurs_occasionnels as $j) echo ($i++ == 0 ? "'" : ",'").$j['id']."|".str_replace('\'', '\\\'', $j['pseudo'])."'";
?>);
var equipes_reg = new Array(<?
$i=0; foreach($equipes_regulieres as $e) echo ($i++ == 0 ? "'" : ",'").$e['id']."|".str_replace('\'', '\\\'', $e['nom'])."'";
?>);
var equipes_okaz = new Array(<?
$i=0; foreach($equipes_occasionnelles as $e) echo ($i++ == 0 ? "'" : ",'").$e['id']."|".str_replace('\'', '\\\'', $e['nom'])."'";
?>);
</SCRIPT>
<?

// Récupération du nombre de journée
$scs = new SQLChampionnatsServices($sess_context->getRealChampionnatId());
$nb_journees = $scs->getNbJournees() + 1;

// Génération de la date de référence si elle n'existe pas (si $refdate existe alors on vient de calendar.php)
$refurl  = "calendar.php";
if (!isset($refdate))
{
	$refdate = date("d/m/Y");
	$refurl  = "journees.php";
}

?>

<SCRIPT SRC="../js/ts_picker.js"></SCRIPT>

<FORM ACTION=journees_ajouter_do.php METHOD=POST>
<INPUT TYPE=HIDDEN NAME=refurl VALUE=<?= $refurl ?>>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

$tab = array();

$lib  = "<TABLE BORDER=0 WIDTH=100%><TR>";
$lib .= "<TD ALIGN=CENTER><TABLE BORDER=0><TR>";
$lib .= "<TD ALIGN=RIGHT>Date  : </TD><TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=zone_calendar id=zone_calendar SIZE=10 VALUE=\"".$refdate."\"></INPUT></TD><TD ALIGN=LEFT><a href=\"#\" onClick=\"javascript:show_calendar('document.forms[0].zone_calendar', document.forms[0].zone_calendar.value);\" title=\"Afficher le calendrier\"><img src=\"../images/images_cal/c_b.gif\" border=0/></a></TD>";
$lib .= "<INPUT TYPE=HIDDEN NAME=nom VALUE=\"".$nb_journees."\">";
$lib .= "</TABLE></TD>";
$lib .= "<TD WIDTH=10% ALIGN=RIGHT>Heure : </TD><TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=heure SIZE=5 VALUE=\"21h45\"></INPUT></TD>";
$lib .= "<TD WIDTH=10% ALIGN=RIGHT>Durée : </TD><TD ALIGN=LEFT><SELECT NAME=duree SIZE=1><OPTION VALUE=135> 2h15 <OPTION VALUE=90> 1h30 <OPTION VALUE=45> 45min </SELECT></TD>";
$lib .= "</TABLE>";
$tab[] = array($lib);

if ($sess_context->isAllXDisplay())
{
	echo "<TR>";
	$lib = "Type Participant : <INPUT TYPE=RADIO NAME=type_participant VALUE=0 CHECKED onClick=\"javascript:changeStateType(0)\"> Joueurs <INPUT TYPE=RADIO NAME=type_participant VALUE=1 onClick=\"javascript:changeStateType(1)\"> Equipes";
	$tab[] = array($lib);
}
else
	echo "<INPUT TYPE=HIDDEN NAME=type_participant VALUE=\"".($sess_context->isFreeXDisplay() ? 0 : 1)."\">";

$lib  = "<INPUT TYPE=HIDDEN NAME=selection VALUE=\"\"></INPUT>";
$lib .= "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=1 WIDTH=100%>";
$lib .= "<TR><TD ALIGN=CENTER><B><SMALL> Absents : </SMALL></B></TD>";
$lib .= "    <TD></TD>";
$lib .= "    <TD ALIGN=CENTER><B><SMALL> Participants : </SMALL></B></TD>";
$lib .= "<TR><TD ALIGN=CENTER><SELECT NAME=\"source\" SIZE=10 MULTIPLE onChange=\"javascript:SBox_TestSelection(this);\">";
$lib .= "</SELECT></TD>";
$lib .= "<TD ALIGN=CENTER><TABLE BORDER=0><TR><TD><A HREF=\"#\"><IMG SRC=../images/small_right.gif onClick=\"javascript:SBox_SversD(document.forms[0].source, document.forms[0].cible);\" BORDER=0></IMG></A></TD><TR><TD><A HREF=\"#\"><IMG SRC=../images/small_left.gif onClick=\"javascript:SBox_DversS(document.forms[0].source, document.forms[0].cible);\" BORDER=0></IMG></A></TD></TABLE></TD>";
$lib .= "<TD ALIGN=CENTER><SELECT NAME=\"cible\" SIZE=10 MULTIPLE onChange=\"javascript:SBox_TestSelection(this);\">";
$lib .= "</SELECT></TD>";
$lib .= "<TR><TD COLSPAN=3 ALIGN=CENTER><IMG SRC=../images/fb_arrow3.gif> Si vous souhaitez ajouter des éléments dans ces listes, reportez-vous à la section adéquate <IMG SRC=../images/fb_arrow2.gif></TD>";
$lib .= "</TABLE>";
$tab[] = array($lib);

$lib  = "<TABLE BORDER=0><TR>";
if ($sess_context->isFreeXDisplay())
	$lib .= "<TD ALIGN=RIGHT NOWRAP>Création automatique des matchs : </TD><TD ALIGN=LEFT><SELECT NAME=create_auto SIZE=1 onChange=\"changeState();\"><OPTION VALUE=0 SELECTED> Non <OPTION VALUE=1> Oui </SELECT></TD><TD ID=id1> => Nombre de matchs maxi : </TD><TD ID=id2><INPUT TYPE=TEXT NAME=max_matchs SIZE=3 VALUE=12></INPUT></TD>";
else
	$lib .= "<input type=hidden name=create_auto value=0 />";
$lib .= "<TD ALIGN=RIGHT NOWRAP>Alias libellé de la journee (facultatif) :</TD><TD ALIGN=LEFT><INPUT NAME=nom_journee></TD>";
$lib .= "</TABLE>";
$tab[] = array($lib);

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$lib = "<FONT SIZE=5 COLOR=white>Ajout de la ".ToolBox::conv_lib_journee($nb_journees)."</FONT>";
$fxlist->FXSetTitle($lib, "CENTER");
$fxlist->FXDisplay();
echo "</TD>";

?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
	<TR><TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Annuler" onclick="return annuler();"></INPUT></TD>
	    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Ajouter" onclick="return validate_and_submit();"></INPUT></TD>
	</TABLE></TD>

<SCRIPT>
function validate_and_submit()
{
	document.forms[0].selection.value='';

    if (!verif_JJMMAAAA(document.forms[0].zone_calendar.value, 'Date'))
		return false;

<? if ($sess_context->isAllXDisplay()) { ?>
	min_selection = document.forms[0].type_participant[0].checked ? 5 : 3;
<? } else if ($sess_context->isFreeXDisplay()) { ?>
	min_selection = 4;
<? } else { ?>
	min_selection = 2;
<? } ?>

	nb_sel=document.forms[0].cible.length;
	if (!(nb_sel > min_selection))
	{
<? if ($sess_context->isAllXDisplay()) { ?>
		alert(document.forms[0].type_participant[0].checked ? 'Vous devez sélectionner au minimum 4 joueurs ...' : 'Vous devez sélectionner au minimum 2 équipes ...');
<? } else if ($sess_context->isFreeXDisplay()) { ?>
		alert('Vous devez sélectionner au minimum 4 joueurs ...');
<? } else { ?>
		alert('Vous devez sélectionner au minimum 2 équipes ...');
<? } ?>
		return false;
	}

	for(i=1; i < nb_sel; i++)
	{
		document.forms[0].cible.options[i].selected=true;
		document.forms[0].selection.value += document.forms[0].selection.value == '' ? document.forms[0].cible.options[i].value : ','+document.forms[0].cible.options[i].value;
	}
	document.forms[0].cible.options[0].selected=false;

	return true;
}
function annuler()
{
	document.forms[0].action='<?= $refurl ?>';

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
function changeStateType(type)
{
	if (type == 0)
	{
		chargeListe(document.forms[0].source, joueurs_okaz);
		chargeListe(document.forms[0].cible, joueurs_reg);
	}
	else
	{
		chargeListe(document.forms[0].source, equipes_okaz);
		chargeListe(document.forms[0].cible, equipes_reg);
	}
}
function changeState()
{
    if (document.forms[0].create_auto.value == 0)
    {
        document.getElementById('id1').style.visibility='hidden';
        document.getElementById('id2').style.visibility='hidden';
    }
    else
    {
        document.getElementById('id1').style.visibility='visible';
        document.getElementById('id2').style.visibility='visible';
    }
}
<? if ($sess_context->isFreeXDisplay()) { ?>
chargeListe(document.forms[0].source, joueurs_okaz);
chargeListe(document.forms[0].cible,  joueurs_reg);
<? } else { ?>
chargeListe(document.forms[0].source, equipes_okaz);
chargeListe(document.forms[0].cible,  equipes_reg);
<? } ?>
document.getElementById('id1').style.visibility='hidden';
document.getElementById('id2').style.visibility='hidden';
</SCRIPT>

</TABLE>
</FORM>

<? $menu->end(); ?>
