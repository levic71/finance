<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

// Si on vient de journee_alias_choisir
if (isset($choix_journee) && $choix_journee != "")
{
	$sess_context->setJourneeId($choix_journee);
	$pkeys_where_jb_journees = $choix_journee;
}

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

if (!isset($modifier_journee)) $modifier_journee = 0;

// Récupération des équipes
$sss = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());
$liste_equipes = $sss->getListeEquipes();

// On récupère les infos de la journée en session
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$journee = $sjs->getJournee();

$journee_nom = "";
$refdate = date("d/m/Y");
$nb_journees = 0;


// On récupère les infos de la journée mère
if ($modifier_journee == 1)
{
	$journee_mere = $sjs->getJournee($journee['id_journee_mere']);
	$sjs2 = new SQLJourneesServices($sess_context->getChampionnatId(), $journee['id_journee_mere']);
	$liste_matchs = $sjs2->getAllMatchs();
	$liste_alias  = $sjs2->getAllAliasJournee();
	$refdate = ToolBox::mysqldate2date($journee['date']);
	$tmp = explode(':', $journee['nom']);
	$nb_journees = $tmp[0];
	if (isset($tmp[1])) $journee_nom = $tmp[1];
}
else
{
	$journee_mere = $journee;
	$liste_matchs = $sjs->getAllMatchs();
	$liste_alias  = $sjs->getAllAliasJournee();
	$nb_journees = $sss->getNbJournees() + 1;
}

// Recencement des matchs déjà affectés
$matchs_deja_affectes = array();
foreach($liste_alias as $alias)
{
	if ($alias['id_matchs'] != "")
	{
		$all_ids = explode('|', $alias['id_matchs']);
		if (isset($all_ids[1]) && $all_ids[1] != "")
		{
			$tmp = explode(',', $all_ids[1]);
			foreach($tmp as $item)
				$matchs_deja_affectes[$item] = $item;
		}
	}
}

$matchs_de_la_journee = array();
if ($modifier_journee == 1)
{
	$all_ids = explode('|', $journee['id_matchs']);
	if (isset($all_ids[1]) && $all_ids[1] != "")
	{
		$tmp = explode(',', $all_ids[1]);
		foreach($tmp as $item)
			$matchs_de_la_journee[$item] = $item;
	}
}

$lst1 = array();
reset($liste_matchs);
foreach($liste_matchs as $m)
{
	if (!isset($matchs_deja_affectes[$m['id']]))
	{
		$item = explode('|', $m['niveau']);
		$niveau_type = $item[0];
		$niveau_num  = $item[1];
		if ($niveau_type == "P")
			$lst1[] = str_replace('\'', '\\\'', "Poule ".($sess_context->championnat['option_poule_lettre'] == 1 ? chr(ord('A')+$niveau_num-1) : $niveau_num).": ".$liste_equipes[$m['id_equipe1']]['nom']."-".$liste_equipes[$m['id_equipe2']]['nom']."|".$m['id']);
		else if ($niveau_type == "F") // Phase finale
			$lst1[] = str_replace('\'', '\\\'', $libelle_phase_finale[$niveau_num]." de finale : ".$liste_equipes[$m['id_equipe1']]['nom']."-".$liste_equipes[$m['id_equipe2']]['nom']."|".$m['id']);
		else if ($niveau_type == "Y") // Consolante
			$lst1[] = str_replace('\'', '\\\'', "Consolante : ".$liste_equipes[$m['id_equipe1']]['nom']."-".$liste_equipes[$m['id_equipe2']]['nom']."|".$m['id']);
		else if ($niveau_type == "C") // matchs de classement
			$lst1[] = str_replace('\'', '\\\'', "Matchs de classement : ".$liste_equipes[$m['id_equipe1']]['nom']."-".$liste_equipes[$m['id_equipe2']]['nom']."|".$m['id']);
	}
}

?>
<SCRIPT type="text/javascript">
var matchs_non_affectes = new Array(<?
$i=0;
reset($lst1);
sort($lst1);
while(list($cle, $val) = each($lst1))
{
	$tmp = explode('|', $val);
	echo ($i++ == 0 ? "'" : ",'").$tmp[1]."|".$tmp[0]."'";
}
?>);
var matchs_affectes = new Array(<?
if ($modifier_journee == 1)
{
	$i=0;
	reset($liste_matchs);
	foreach($liste_matchs as $m)
	{
		if (isset($matchs_de_la_journee[$m['id']]))
		{
			$item = explode('|', $m['niveau']);
			$niveau_type = $item[0];
			$niveau_num  = $item[1];
			if ($niveau_type == "P")
				echo ($i++ == 0 ? "'" : ",'").$m['id']."|Poule ".($sess_context->championnat['option_poule_lettre'] == 1 ? chr(ord('A')+$niveau_num-1) : $niveau_num).": ".str_replace('\'', '\\\'', $liste_equipes[$m['id_equipe1']]['nom']."-".$liste_equipes[$m['id_equipe2']]['nom'])."'";
			else if ($niveau_type == "F") // Phase finale
				echo ($i++ == 0 ? "'" : ",'").$m['id']."|".$libelle_phase_finale[$niveau_num]." de finale : ".str_replace('\'', '\\\'', $liste_equipes[$m['id_equipe1']]['nom']."-".$liste_equipes[$m['id_equipe2']]['nom'])."'";
			else if ($niveau_type == "Y") // Consolante
				echo ($i++ == 0 ? "'" : ",'").$m['id']."|Consolante : ".str_replace('\'', '\\\'', $liste_equipes[$m['id_equipe1']]['nom']."-".$liste_equipes[$m['id_equipe2']]['nom'])."'";
			else if ($niveau_type == "C") // matchs de classement
				echo ($i++ == 0 ? "'" : ",'").$m['id']."|Matchs de classement : ".str_replace('\'', '\\\'', $liste_equipes[$m['id_equipe1']]['nom']."-".$liste_equipes[$m['id_equipe2']]['nom'])."'";
		}
	}
}
?>);
</SCRIPT>


<SCRIPT SRC="../js/ts_picker.js"></SCRIPT>

<FORM ACTION=<?= $modifier_journee == 1 ? "journees_alias_modifier_do.php" : "journees_alias_ajouter_do.php" ?> METHOD=POST>
<INPUT TYPE=HIDDEN NAME=modifier_journee VALUE=<?= $modifier_journee ?>>
<INPUT TYPE=HIDDEN NAME=selection VALUE="">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

$tab = array();

$lib  = "<TABLE BORDER=0 WIDTH=100%>";
$lib .= "<TR>";
$lib .= "<TD ALIGN=CENTER><TABLE BORDER=0><TR>";
$lib .= "<TD ALIGN=RIGHT>Date  : </TD><TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=zone_calendar id=zone_calendar SIZE=10 VALUE=\"".$refdate."\"></INPUT></TD><TD ALIGN=LEFT><a href=\"#\" onClick=\"javascript:show_calendar('document.forms[0].zone_calendar', document.forms[0].zone_calendar.value);\" title=\"Afficher le calendrier\"><img src=\"../images/images_cal/c_b.gif\" border=0/></a></TD>";
$lib .= "<TD WIDTH=50> </TD>";
$lib .= "<TD ALIGN=RIGHT NOWRAP>Libellé de l'alias de la journée :</TD><TD ALIGN=LEFT><INPUT NAME=nom_journee VALUE=\"".$journee_nom."\" SIZE=32></TD>";
$lib .= "<INPUT TYPE=HIDDEN NAME=nom VALUE=\"".$nb_journees."\">";
$lib .= "</TABLE></TD>";
$lib .= "</TABLE>";
$tab[] = array($lib);

$lib = "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=1 WIDTH=100%>";
$lib .= "<TR><TD ALIGN=LEFT WIDTH=45%><B><SMALL> Liste des matchs : </SMALL></B></TD>";
$lib .= "    <TD WIDTH=60 ALIGN=CENTER ROWSPAN=2><TABLE BORDER=0><TR><TD><A HREF=\"#\"><IMG SRC=../images/small_right.gif onClick=\"javascript:SBox_SversD(document.forms[0].source, document.forms[0].cible);\"  BORDER=0></A></TD><TR><TD><A HREF=\"#\"><IMG SRC=../images/small_left.gif onClick=\"javascript:SBox_DversS(document.forms[0].source, document.forms[0].cible);\" BORDER=0></A></TD></TABLE></TD>";
$lib .= "    <TD ALIGN=LEFT WIDTH=45%><B><SMALL> Matchs sélectionnés : </SMALL></B></TD>";
$lib .= "<TR><TD ALIGN=LEFT><SELECT NAME=\"source\" SIZE=10 MULTIPLE onChange=\"javascript:SBox_TestSelection(this);\">";
$lib .= "    <TD ALIGN=LEFT><SELECT NAME=\"cible\"  SIZE=10 MULTIPLE onChange=\"javascript:SBox_TestSelection(this);\">";
$lib .= "</SELECT></TD>";
$lib .= "</TABLE>";
$tab[] = array($lib);

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
if ($modifier_journee == 1)
	$lib = "Modification de l'alias : '".$sjs->getNomJournee($journee['nom'])."' (".$journee['date'].")";
else
	$lib = "Création d'un alias de la journée '".$sjs->getNomJournee($journee_mere['nom'])."' (".$journee['date'].")";
$fxlist->FXSetTitle($lib, "CENTER");
$fxlist->FXSetMouseOverEffect(false);
$fxlist->FXDisplay();
echo "</TD>";

?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
	<TR><TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Annuler" onclick="return annuler();"></INPUT></TD>
	    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="<?= $modifier_journee == 1 ? "Modifier" : "Ajouter" ?>" onclick="return validate_and_submit();"></INPUT></TD>
	</TABLE></TD>

<SCRIPT type="text/javascript">
function validate_and_submit()
{
	document.forms[0].selection.value='';

    if (!verif_JJMMAAAA(document.forms[0].zone_calendar.value, 'Date'))
		return false;

	chaine = '';
	nb_sel=document.forms[0].cible.length;
	for(i=1; i < nb_sel; i++)
	{
		document.forms[0].cible.options[i].selected=true;
		chaine += chaine == '' ? document.forms[0].cible.options[i].value : ','+document.forms[0].cible.options[i].value;
	}
	document.forms[0].cible.options[0].selected=false;

	document.forms[0].selection.value = chaine;

	return true;
}
function annuler()
{
	document.forms[0].action='../www/matchs_tournoi.php?pkeys_where_jb_journees=<?= $modifier_journee == 1 ? urlencode(" WHERE id=".$journee['id']) : $pkeys_where_jb_journees ?>';

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

chargeListe(document.forms[0].source, matchs_non_affectes);
chargeListe(document.forms[0].cible, matchs_affectes);
</SCRIPT>

</TABLE>
</FORM>

<? $menu->end(); ?>
