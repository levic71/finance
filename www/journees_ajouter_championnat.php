<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

function estBissextile ($annee) {
	return (($annee % 4 == 0) && (($annee % 100 != 0) || ($annee % 400 == 0)));
}

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

// Récupération des équipes
$ses = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());
$liste_equipes = $ses->getListeEquipes();

// Ajustement si nb equipes impair
if ((count($liste_equipes) % 2) == 1) $liste_equipes[-1] = array("id" => "-1", "nom" => "Equipe virtuelle");

$nb_journees = count($liste_equipes) - 1;

$refdate = date("d/m/Y");

$etape = isset($etape) ? $etape : 0;

?>

<script src="../js/ts_picker.js"></script>

<style type="text/css">
.mycalendar {
	background: #FFFFFF;
}
.mycalendar .vide {
	background: #EEEEEE;
}
.mycalendar .vide2 {
	background: #CCFFFF;
}
.mycalendar .pleine {
	background: #E0E0E0;
	padding: 0px 2px;
}
.mycalendar .pleine2 {
	background: #CCFFFF;
	padding: 0px 2px;
}
.mycalendar td {
	height: 30px;
	font-weight: normal;
	font-size: 12px;
}
.mycalendar .mois {
	font-weight: bold;
	background: #CCCCCC;
}
.jours td {
	font-weight: bold;
	background: #CCCCCC;
	width: 30px;
}
.jours .fonce {
	background: #99FFFF;
}
</style>

<FORM ACTION="<?= $etape == 0 ? "journees_ajouter_championnat.php" : "journees_ajouter_championnat_do.php" ?>" METHOD=POST>
<INPUT TYPE=HIDDEN NAME=etape VALUE=<?= ($etape+1) ?> />

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 ID=tableau>

<?

$select_allerretour  = "<SELECT NAME=allerretour>";
$select_allerretour .= "<OPTION VALUE=0> Oui";
$select_allerretour .= "<OPTION VALUE=1> Non";
$select_allerretour .= "</SELECT>";

$tab = array();

if ($etape == 0)
{

$lib  = "<TABLE BORDER=0>";
$lib .= "<TR>";
$lib .= "<TD ALIGN=CENTER COLSPAN=2 STYLE=\"color: red;\">ATTENTION,<br /> il est conseillé de créer une saison vierge pour la création automatique de journées/matchs d'une saison, sinon il faut bien choisir la date de début de la première journée pour ne pas avoir de chevauchement avec des journées déjà saisies.</TD>";
$lib .= "</TR>";
$lib .= "</TABLE>";
$tab[] = array($lib);

$lib  = "<TABLE BORDER=0>";
$lib .= "<TR>";
$lib .= "<TD ALIGN=RIGHT>Date de la première journée : </TD>";
$lib .= "<TD ALIGN=LEFT><TABLE BORDER=0><TR><TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=zone_calendar id=zone_calendar SIZE=10 VALUE=\"".$refdate."\"></INPUT></TD><TD ALIGN=LEFT><a href=\"#\" onClick=\"javascript:show_calendar('document.forms[0].zone_calendar', document.forms[0].zone_calendar.value);\" title=\"Afficher le calendrier\"><img src=\"../images/jorkers/images/calendar.png\" border=0/></a></TD></TABLE></TD>";
$lib .= "</TR>";
$lib .= "<TR>";
$lib .= "<TD ALIGN=RIGHT NOWRAP>Matchs aller/retour : </TD><TD ALIGN=LEFT>".$select_allerretour."</TD>";
$lib .= "</TR>";
$lib .= "</TABLE>";
$tab[] = array($lib);

$lib  = "<TABLE BORDER=0>";
$lib .= "<TR>";
$lib .= "<TD ALIGN=RIGHT>Liste des équipes : </TD>";
$lib .= "<TD ALIGN=LEFT><ul>";
foreach($liste_equipes as $item)
	if ($item['id'] != -1)
		$lib .= "<li>".$item['nom']."</li>";
$lib .= "</ul></TD>";
$lib .= "</TR>";
$lib .= "</TABLE>";
$tab[] = array($lib);

}

if ($etape == 1)
{

echo "<INPUT TYPE=HIDDEN NAME=zone_calendar VALUE=".$zone_calendar." />";
echo "<INPUT TYPE=HIDDEN NAME=allerretour VALUE=".$allerretour." />";

$tab_nbjoursmois = array("1" => 31, "2" => 28, "3" => 31, "4" => 30, "5" => 31, "6" => 30, "7" => 31, "8" => 31, "9" => 30, "10" => 31, "11" => 30, "12" => 31);

$mon_jour  = substr($zone_calendar, 0, 2);
$mon_mois  = substr($zone_calendar, 3, 2);
$mon_annee = substr($zone_calendar, 6, 4);

if ($allerretour == 0) $nb_journees = $nb_journees * 2;

$lib  = "<TABLE BORDER=0>";
$lib .= "<TR>";
$lib .= "<TD ALIGN=RIGHT style=\"font-size: 14px; color: red;\">Cocher les journées où les matchs devront être joués : </TD>";
$lib .= "<TD ALIGN=LEFT style=\"font-size: 14px; color: red;\">".$nb_journees." journées à cocher</td>";
$lib .= "</TR>";
$lib .= "</TABLE>";
$tab[] = array($lib);

$lib  = "<TABLE BORDER=0 cellpadding=0 cellspacing=0 class=mycalendar>";
$lib .= "<tr valign=top>";
$lib .= "<td><table cellpadding=0 cellspacing=1 border=0 class=jours>";
$lib .= "<tr><td>&nbsp;</td></tr>";
$lib .= "<tr><td class=fonce>D</td></tr><tr><td>L</td></tr><tr><td>M</td></tr><tr><td>M</td></tr><tr><td>J</td></tr><tr><td>V</td></tr><tr><td class=fonce>S</td></tr>";
$lib .= "<tr><td class=fonce>D</td></tr><tr><td>L</td></tr><tr><td>M</td></tr><tr><td>M</td></tr><tr><td>J</td></tr><tr><td>V</td></tr><tr><td class=fonce>S</td></tr>";
$lib .= "<tr><td class=fonce>D</td></tr><tr><td>L</td></tr><tr><td>M</td></tr><tr><td>M</td></tr><tr><td>J</td></tr><tr><td>V</td></tr><tr><td class=fonce>S</td></tr>";
$lib .= "<tr><td class=fonce>D</td></tr><tr><td>L</td></tr><tr><td>M</td></tr><tr><td>M</td></tr><tr><td>J</td></tr><tr><td>V</td></tr><tr><td class=fonce>S</td></tr>";
$lib .= "<tr><td class=fonce>D</td></tr><tr><td>L</td></tr><tr><td>M</td></tr><tr><td>M</td></tr><tr><td>J</td></tr><tr><td>V</td></tr><tr><td class=fonce>S</td></tr>";
$lib .= "<tr><td class=fonce>D</td></tr><tr><td>L</td></tr><tr><td>M</td></tr><tr><td>M</td></tr><tr><td>J</td></tr><tr><td>V</td></tr><tr><td class=fonce>S</td></tr>";
$lib .= "</table></td>";
$local_annee = $mon_annee;
$local_mois = intval($mon_mois);
for($m = 0; $m < 15; $m++)
{
	if ($local_mois > 12)
	{
		$local_mois = 1;
		$local_annee++;
	}
	
	$lib .= "<td><table cellpadding=0 cellspacing=1 border=0>";
	$lib .= "<tr><td class=\"mois\">".($local_mois > 9 ? "" : "0").$local_mois."/".substr($local_annee, 2, 2);

	$deb_mois = mktime(0, 0, 0, $local_mois, 1, $local_annee);
	$jour_sem = date("w", $deb_mois);
	
	for($z = 0; $z < $jour_sem; $z++)
		$lib .= "<tr><td class=\"".($z == 0 || $z == 6 ? "vide2" : "vide")."\">&nbsp;</td></tr>";

	for($j = 1; $j <= ((7*6)-$jour_sem); $j++)
	{
		$j_mois = mktime(0, 0, 0, $local_mois, $j, $local_annee);
		$jour_sem2 = date("w", $j_mois);

		$checkname = "j_".$j."_".$local_mois."_".$local_annee;
		$limite = estBissextile($local_annee) && $local_mois == 2 ? 29 : $tab_nbjoursmois[$local_mois];
		if ($j > $limite)
			$lib .= "<tr><td class=\"".($jour_sem2 == 0 || $jour_sem2 == 6 ? "vide2" : "vide")."\">&nbsp;</td></tr>";
		else
			$lib .= "<tr><td class=\"".($jour_sem2 == 0 || $jour_sem2 == 6 ? "pleine2" : "pleine")."\"><span><input onclick=\"checkj('".$checkname."');\" type=\"checkbox\" name=\"".$checkname."\" id=\"".$checkname."\" value=\"1\">".($j > 9 ? "" : "0").$j."</span></td></tr>";
	}
	$lib .= "</table></td>";

	$local_mois++;
}
$lib .= "</TR>";
$lib .= "</TABLE>";
$tab[] = array($lib);

}

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$lib = "<FONT SIZE=5 COLOR=white>Création automatique de journées/matchs d'une saison</FONT>";
$fxlist->FXSetTitle($lib, "CENTER");
$fxlist->FXSetMouseOverEffect(false);
$fxlist->FXDisplay();
echo "</TD>";

?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
	<TR><TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Annuler" onclick="return annuler();"></INPUT></TD>
	    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="<?= $etape == 0 ? "Suivant" : "Ajouter" ?>" onclick="return <?= $etape == 0 ? "validate_and_submit();" : "checknbjourneescochees();" ?>"></INPUT></TD>
	</TABLE></TD>

<script>
function validate_and_submit()
{
	document.forms[0].selection.value='';
	
    if (!verif_JJMMAAAA(document.forms[0].zone_calendar.value, 'Date'))
		return false;
		
	return true;
}
function annuler()
{
	document.forms[0].action='calendar.php';

	return true;
}
nb_checkj = 0;
function checkj(name)
{
	if (document.getElementById(name).checked == false)
	{
		nb_checkj--;
		return true;
	}
	
	if (nb_checkj >= <?= $nb_journees ?>)
	{
		document.getElementById(name).checked = false;
		alert('Vous ne pouvez pas saisir plus de journées !!!!');
		return false;
	}
	
	nb_checkj++;
	
	return true;
}
function checknbjourneescochees()
{
	if (nb_checkj != <?= $nb_journees ?>)
	{
		alert('Vous devez saisir <?= $nb_journees ?> journées en tout !!!');
		return false;
	}
	
	return true;
}
</script>

</TABLE>
</FORM>

<? $menu->end(); ?>
