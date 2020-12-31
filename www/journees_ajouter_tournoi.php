<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

$equipes_regulieres = array();

// ///////////////////////////////////////////////////////////////////
// CHOIX DU TYPE D'AFFICHAGE
// ///////////////////////////////////////////////////////////////////
// 0: choix joueurs/equipes
// 1: joueurs uniquement
// 2: equipes uniquement
// ///////////////////////////////////////////////////////////////////
if (!isset($type_affichage)) $type_affichage = 0;
// ///////////////////////////////////////////////////////////////////

if (!isset($modifier_journee )) $modifier_journee = 0;

// Récupération des équipes
$ses = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());
$liste_equipes = $ses->getListeEquipes();

// Récupération des infos de la journée si on est en modification
$tournoi_phase_finale = _PHASE_FINALE_8_;
$tournoi_nb_poules    = 4;
$tournoi_consolante   = 0;
$equipes_affectees    = array();
$poules               = array();
$tournoi_journee_nom  = "";
if ($modifier_journee == 1)
{
	$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
	$journee = $sjs->getJournee();
	$tournoi_phase_finale = $journee['tournoi_phase_finale'];
	$tournoi_nb_poules    = $journee['tournoi_nb_poules'];
	$tournoi_consolante   = $journee['tournoi_consolante'];
	$items = explode(':', $journee['nom']);
	$nb_journees = $items[0];
	$tournoi_journee_nom  = isset($items[1]) ? $items[1] : "";
	
	// Reformatage des poules
	$i = 1;
	$items = explode('|', $journee['equipes']);
	foreach($items as $elt)
	{
		$equipes = explode(',', $elt);
		foreach($equipes as $eq)
		{
			if ($eq != "")
			{
				$equipes_affectees[$eq] = $eq;
				$poules[$i][$eq]['id']  = $eq;
				$poules[$i][$eq]['nom'] = $liste_equipes[$eq]['nom'];
			}
		}
		$i++;
	}
}

// Si on vient d'une introversion, alors on recharge les valeurs modifiées
if (isset($nb_poules))		$tournoi_nb_poules    = $nb_poules;
if (isset($phase_finale))	$tournoi_phase_finale = $phase_finale;
	
?>
<SCRIPT>
var equipes_reg = new Array(<?
$i=0;
foreach($liste_equipes as $e)
	if (!isset($equipes_affectees[$e['id']]))
		echo ($i++ == 0 ? "'" : ",'").$e['id']."|".str_replace("'", "\\'", $e['nom'])."'";
?>);
<?
for($p=1; $p <= $tournoi_nb_poules; $p++) { ?>
var equipes_poule<?= $p ?> = new Array(<?
if (isset($poules[$p]))
{
	$i=0;
	foreach($poules[$p] as $e)
		echo ($i++ == 0 ? "'" : ",'").$e['id']."|".str_replace("'", "\\'", $e['nom'])."'";
}
?>);
<? } ?>
</SCRIPT>
<?

// Récupération du nombre de journée
if ($modifier_journee == 0)
{
	$scs = new SQLChampionnatsServices($sess_context->getRealChampionnatId());
	$nb_journees = $scs->getNbJournees() + 1;
}

// Génération de la date de référence si elle n'existe pas (si $refdate existe alors on vient de calendar.php)
$refurl  = $sess_context->championnat['visu_journee'] == _VISU_JOURNEE_CALENDRIER_ ? "calendar.php" : "journees.php";
if (!isset($refdate))
{
	$refdate = date("d/m/Y");
	$refurl  = "journees.php";
}

// Génération de la date de référence pour la gestion des modifs d'une journée de tournoi
if ($modifier_journee == 1)
{
	$refdate = ToolBox::mysqldate2date($journee['date']);
	$refurl  = "matchs_tournoi.php?pkeys_where_jb_journees=+WHERE+id%3D".$sess_context->getJourneeId();
}

?>

<SCRIPT SRC="../js/ts_picker.js"></SCRIPT>

<FORM ACTION=<?= $modifier_journee == 1 ? "journees_modifier_tournoi_do.php" : "journees_ajouter_tournoi_do.php" ?> METHOD=POST>
<INPUT TYPE=HIDDEN NAME=refurl VALUE=<?= $refurl ?>>
<INPUT TYPE=HIDDEN NAME=heure  VALUE=0>
<INPUT TYPE=HIDDEN NAME=duree  VALUE=0>
<INPUT TYPE=HIDDEN NAME=modifier_journee VALUE=<?= $modifier_journee ?>>
<INPUT TYPE=HIDDEN NAME=nom VALUE=<?= $nb_journees ?>>

<STYLE type="text/css">
#tableau input {
    font-size: 9px;
}
#tableau select {
    font-size: 9px;
}
#tableau p {
    text-align: left;
    background:#525252;
    color: white;
    padding: 2px 0px 2px 5px;
    font-size: 9px;
}
</STYLE>

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 ID=tableau>

<?
$select_nb_poules  = "<SELECT NAME=nb_poules onChange=\"javascript:reload_journee();\">";
for($i=1; $i < 13; $i++) $select_nb_poules .= "<OPTION VALUE=".$i." ".($i == $tournoi_nb_poules ? "SELECTED" : "")."> ".$i;
$select_nb_poules .= "</SELECT>";

$select_phase_finale  = "<SELECT NAME=phase_finale>";
$select_phase_finale .= "<OPTION VALUE="._PHASE_FINALE_32_." ".($tournoi_phase_finale == _PHASE_FINALE_32_ ? "SELECTED" : "")."> ".$libelle_phase_finale[_PHASE_FINALE_32_];
$select_phase_finale .= "<OPTION VALUE="._PHASE_FINALE_16_." ".($tournoi_phase_finale == _PHASE_FINALE_16_ ? "SELECTED" : "")."> ".$libelle_phase_finale[_PHASE_FINALE_16_];
$select_phase_finale .= "<OPTION VALUE="._PHASE_FINALE_8_."  ".($tournoi_phase_finale == _PHASE_FINALE_8_  ? "SELECTED" : "")."> ".$libelle_phase_finale[_PHASE_FINALE_8_];
$select_phase_finale .= "<OPTION VALUE="._PHASE_FINALE_4_."  ".($tournoi_phase_finale == _PHASE_FINALE_4_  ? "SELECTED" : "")."> ".$libelle_phase_finale[_PHASE_FINALE_4_];
$select_phase_finale .= "<OPTION VALUE="._PHASE_FINALE_2_."  ".($tournoi_phase_finale == _PHASE_FINALE_2_  ? "SELECTED" : "")."> ".$libelle_phase_finale[_PHASE_FINALE_2_];
$select_phase_finale .= "</SELECT>";

$select_matchs_auto  = "<SELECT NAME=matchs_auto>";
$select_matchs_auto .= "<OPTION VALUE=0> Oui";
$select_matchs_auto .= "<OPTION VALUE=1 ".($modifier_journee == 1 ? "SELECTED" : "")."> Non";
$select_matchs_auto .= "</SELECT>";

$select_matchs_ar  = "<SELECT NAME=matchs_ar>";
$select_matchs_ar .= "<OPTION VALUE=0> Oui";
$select_matchs_ar .= "<OPTION VALUE=1 SELECTED> Non";
$select_matchs_ar .= "</SELECT>";

$select_consolante  = "<SELECT NAME=tournoi_consolante>";
$select_consolante .= "<OPTION VALUE=0 ".($tournoi_consolante == 0 ? "SELECTED" : "")."> Non";
$select_consolante .= "<OPTION VALUE="._PHASE_FINALE_32_." ".($tournoi_consolante == _PHASE_FINALE_32_ ? "SELECTED" : "")."> ".$libelle_phase_finale[_PHASE_FINALE_32_];
$select_consolante .= "<OPTION VALUE="._PHASE_FINALE_16_." ".($tournoi_consolante == _PHASE_FINALE_16_ ? "SELECTED" : "")."> ".$libelle_phase_finale[_PHASE_FINALE_16_];
$select_consolante .= "<OPTION VALUE="._PHASE_FINALE_8_."  ".($tournoi_consolante == _PHASE_FINALE_8_  ? "SELECTED" : "")."> ".$libelle_phase_finale[_PHASE_FINALE_8_];
$select_consolante .= "<OPTION VALUE="._PHASE_FINALE_4_."  ".($tournoi_consolante == _PHASE_FINALE_4_  ? "SELECTED" : "")."> ".$libelle_phase_finale[_PHASE_FINALE_4_];
$select_consolante .= "<OPTION VALUE="._PHASE_FINALE_2_."  ".($tournoi_consolante == _PHASE_FINALE_2_  ? "SELECTED" : "")."> ".$libelle_phase_finale[_PHASE_FINALE_2_];
$select_consolante .= "</SELECT>";

$tab = array();

$lib  = "<TABLE BORDER=0 WIDTH=100%>";
$lib .= "<TR>";
$lib .= "<TD ALIGN=RIGHT>Date du tournoi : </TD>";
$lib .= "<TD><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0><TR><TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=zone_calendar id=zone_calendar SIZE=10 VALUE=\"".$refdate."\"></INPUT></TD><TD ALIGN=LEFT><a href=\"#\" onClick=\"javascript:show_calendar('document.forms[0].zone_calendar', document.forms[0].zone_calendar.value);\" title=\"Afficher le calendrier\"><img src=\"../images/images_cal/c_b.gif\" border=0/></a></TD></TABLE></TD>";
$lib .= "<TD ALIGN=RIGHT NOWRAP>Alias libellé (facultatif) :</TD><TD ALIGN=LEFT><INPUT NAME=nom_journee VALUE=\"".$tournoi_journee_nom."\" SIZE=40></TD>";
$lib .= "<TR>";
$lib .= "<TD ALIGN=RIGHT>Nombre de poules : </TD><TD ALIGN=LEFT>".$select_nb_poules."</TD>";
$lib .= "<TD ALIGN=RIGHT>Phase finale : </TD>";
$lib .= "<TD><TABLE BORDER=0><TR><TD ALIGN=LEFT>".$select_phase_finale."</TD>";
$lib .= "<TD ALIGN=RIGHT>Consolante : </TD><TD ALIGN=LEFT>".$select_consolante."</TD></TABLE></TD>";
$lib .= "<TR>";
$lib .= "<TD ALIGN=RIGHT NOWRAP>Création automatique des matchs de poules : </TD><TD ALIGN=LEFT>".$select_matchs_auto."</TD>";
$lib .= "<TD ALIGN=RIGHT NOWRAP>Matchs aller/retour : </TD><TD ALIGN=LEFT>".$select_matchs_ar."</TD>";
if ($modifier_journee == 1) $lib .= "<TR><TD ALIGN=CENTER COLSPAN=8 STYLE=\"color:red;\">[Attention, la création automatique supprimera les matchs déjà saisis !!!]</TD>";
$lib .= "</TABLE>";
$tab[] = array($lib);

$lib  = "<INPUT TYPE=HIDDEN NAME=selection VALUE=\"\"></INPUT>";

$lib .= "<TABLE BORDER=0 WIDTH=100%>";
$lib .= "   <TR><TD ALIGN=CENTER WIDTH=50%><TABLE BORDER=0 WIDTH=100%>";
$lib .= "       <TR><TD WIDTH=50% ALIGN=CENTER><TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0><TR><TD><P> Liste des équipes : </P></TD>";
$lib .= "           <TR><TD ALIGN=CENTER><SELECT NAME=\"source\" SIZE=10 MULTIPLE onChange=\"javascript:SBox_TestSelection(this);\">";
$lib .= "               </SELECT></TD>";
$lib .= "       </TABLE></TD>";
$lib .= "       <TD ALIGN=CENTER><TABLE BORDER=0>";
$lib .= "           <TR><TD STYLE=\"border: 1px dashed white; height: 100px; padding: 0px 10px 0px 0px; background: #828282; color: black;\"> <ol style=\"width:200px;\"><li>Sélectionner les équipes dans la liste de droite</li><li>Sélectionner la poule de destination ci-dessous</li><li>Cliquer sur le bouton 'Ajouter'</li></ol> </TD>";
$lib .= "           <TR><TD ALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\"Ajouter équipes à la poule =>\" onClick=\"return setPoule();\"><SELECT NAME=poule_target>";
for($i=1; $i <= $tournoi_nb_poules; $i++) $lib .= "<OPTION VALUE=".$i."> Poule ".($sess_context->championnat['option_poule_lettre'] == 1 ? chr(ord('A')+$i-1) : $i);
$lib .= "               </SELECT></TD>";
$lib .= "       </TABLE></TD>";
$lib .= "   </TABLE></TD>";
$lib .= "</TABLE>";
$tab[] = array($lib);

$lib  = "<TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1 WIDTH=100%>\n";
for($i=1; $i <= $tournoi_nb_poules; $i++)
{
	if (($i+1)%2 == 0) $lib .= "<TR>";
	$lib .= "<TD ALIGN=CENTER WIDTH=50%><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0><TR><TD><P> Poule ".($sess_context->championnat['option_poule_lettre'] == 1 ? chr(ord('A')+$i-1) : $i)." </P></TD><TR><TD><SELECT NAME=poule".$i." SIZE=6 MULTIPLE onChange=\"javascript:SBox_TestSelection(this);\"></SELECT></TD><TR><TD ALIGN=RIGHT><INPUT TYPE=SUBMIT VALUE=Retirer onClick=\"return removeFromPoule(".$i.");\"></TD></TABLE></TD>\n";
}
$lib .= "\n</TABLE>";
$lib .= "<TR>";
$tab[] = array($lib);

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$lib_journee = ($modifier_journee == 1 && $tournoi_journee_nom != "") ? $tournoi_journee_nom : $nb_journees.($nb_journees == '1' ? "ère" : "ème")." journée";
$lib = "<FONT SIZE=5 COLOR=white>".($modifier_journee == 1 ? "Modification" : "Ajout")." de la ".$lib_journee."</FONT>";
$fxlist->FXSetTitle($lib, "CENTER");
$fxlist->FXSetMouseOverEffect(false);
$fxlist->FXDisplay();
echo "</TD>";

?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
	<TR><TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Annuler" onclick="return annuler();"></INPUT></TD>
	    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="<?= $modifier_journee == 1 ? "Modifier" : "Ajouter" ?>" onclick="return validate_and_submit();"></INPUT></TD>
	</TABLE></TD>

<SCRIPT>
function reload_journee()
{
	document.forms[0].action='journees_ajouter_tournoi.php';
	document.forms[0].submit();
	return true;
}
function setPoule()
{
	SBox_SversD(document.forms[0].source, document.forms[0].elements['poule'+document.forms[0].poule_target.value]);
	return false;
}
function removeFromPoule(poule)
{
	SBox_SversD(document.forms[0].elements['poule'+poule], document.forms[0].source);
	return false;
}
function collectePoule(liste)
{
	chaine = '';
	nb_sel=liste.length;
	for(i=1; i < nb_sel; i++)
	{
		liste.options[i].selected=true;
		chaine += chaine == '' ? liste.options[i].value : ','+liste.options[i].value;
	}
	liste.options[0].selected=false;
	
	return chaine;
}
function validate_and_submit()
{
	document.forms[0].selection.value='';
	
    if (!verif_JJMMAAAA(document.forms[0].zone_calendar.value, 'Date'))
		return false;
		
<?
	for($i=1; $i <= $tournoi_nb_poules; $i++)
	{
		echo "ret = collectePoule(document.forms[0].poule".$i.");\n";
		echo "document.forms[0].selection.value += document.forms[0].selection.value == '' ? ret : '|'+ret;\n";
	}
?>

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

chargeListe(document.forms[0].source, equipes_reg);
<?
	for($i=1; $i <= $tournoi_nb_poules; $i++)
		echo "chargeListe(document.forms[0].poule".$i.", equipes_poule".$i.");\n";
?>

</SCRIPT>

</TABLE>
</FORM>

<? $menu->end(); ?>
