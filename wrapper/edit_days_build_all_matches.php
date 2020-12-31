<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$etape         = Wrapper::getRequest('etape',         '0');
$zone_calendar = Wrapper::getRequest('zone_calendar', date('d/m/Y'));
$allerretour   = Wrapper::getRequest('allerretour',   0);
$jsem          = Wrapper::getRequest('jsem',          6);

// Récupération des équipes
$ses = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());
$liste_equipes = $ses->getListeEquipes();

// Ajustement si nb equipes impair
if ((count($liste_equipes) % 2) == 1) $liste_equipes[-1] = array("id" => "-1", "nom" => "Equipe virtuelle");

$nb_journees = count($liste_equipes) - 1;

?>

<div id="build_all_matches_form" class="edit">

<?

if ($etape == 0) { ?>

<h2 class="grid days">Création de tous les matchs d'une saison - [Etape <?= $etape+1 ?>/2]</h2>

<table cellspacing="0" cellpadding="0" class="jkgrid">
<tbody>
<tr><td class="c1"><div>ATTENTION,<br />pour la création automatique de matchs, il est conseillé de créer une saison vierge, sinon il faut bien choisir la date<br /> de début de la première journée pour ne pas avoir de chevauchement avec des journées déjà saisies.</div></td></tr>
</tbody>
</table>

<table cellspacing="0" cellpadding="0" class="jkgrid">
<tr>
	<td class="c2"><div>Première journée le :</div></td>
	<td class="c3"><div><input type="text" name="zone_calendar" id="zone_calendar" size="10" value="<?= $zone_calendar ?>" /></div></td>
</tr>
</table>

<table cellspacing="0" cellpadding="0" class="jkgrid">
<tr>
	<td class="c2"><div>Matchs aller/retour :</div></td>
	<td class="c3"><div id="allerretour" class="grouped"></div></td>
</tr>
</table>

<table cellspacing="0" cellpadding="0" class="jkgrid">
<tr>
	<td class="c2"><div>Jours de match :</div></td>
	<td class="c3"><div id="singledaychoice"></div></td>
</tr>
</table>

<table cellspacing="0" cellpadding="0" class="jkgrid">
<tr>
	<td class="c2"><div>Liste des équipes :</div></td>
	<td class="c3"><div id="allteams"></div></td>
</tr>
</table>

<? }

if ($etape == 1) {

if ($allerretour == 0) $nb_journees = $nb_journees * 2;

?>

<h2 class="grid days">
<span style="float: left; line-height: 30px; margin-top: 5px;">[Etape <?= $etape+1 ?>/2] - <span id="jcochees"><?= $nb_journees ?></span>/<?= $nb_journees ?> journées cochées</span>
<span class="actions" style="float: right;line-height: 18px;  width: 200px; margin-right: 10px;">
<button onclick="return <?= $etape == 0 ? "validate_and_submit();" : "checknbjourneescochees();" ?>" class="button green" style="float: right; margin-left: 10px;"><?= $etape == 0 ? "Suivant" : "Ajouter" ?></button>
<button onclick="return annuler();" class="button green" style="float: right;">Annuler</button>
</span>
</h2>

<?

$tab_nbjoursmois = array("1" => 31, "2" => 28, "3" => 31, "4" => 30, "5" => 31, "6" => 30, "7" => 31, "8" => 31, "9" => 30, "10" => 31, "11" => 30, "12" => 31);

$mon_jour  = substr($zone_calendar, 0, 2);
$mon_mois  = substr($zone_calendar, 3, 2);
$mon_annee = substr($zone_calendar, 6, 4);

?>

<table border="0" cellpadding="0" cellspacing="0" class="mycalendar">
<tr valign="top">
<td><table cellpadding="0" cellspacing="0" border="0" class="jours">
<tr><td>&nbsp;</td></tr>
<tr><td class="fonce">D</td></tr><tr><td>L</td></tr><tr><td>M</td></tr><tr><td>M</td></tr><tr><td>J</td></tr><tr><td>V</td></tr><tr><td class="fonce">S</td></tr>
<tr><td class="fonce">D</td></tr><tr><td>L</td></tr><tr><td>M</td></tr><tr><td>M</td></tr><tr><td>J</td></tr><tr><td>V</td></tr><tr><td class="fonce">S</td></tr>
<tr><td class="fonce">D</td></tr><tr><td>L</td></tr><tr><td>M</td></tr><tr><td>M</td></tr><tr><td>J</td></tr><tr><td>V</td></tr><tr><td class="fonce">S</td></tr>
<tr><td class="fonce">D</td></tr><tr><td>L</td></tr><tr><td>M</td></tr><tr><td>M</td></tr><tr><td>J</td></tr><tr><td>V</td></tr><tr><td class="fonce">S</td></tr>
<tr><td class="fonce">D</td></tr><tr><td>L</td></tr><tr><td>M</td></tr><tr><td>M</td></tr><tr><td>J</td></tr><tr><td>V</td></tr><tr><td class="fonce">S</td></tr>
<tr><td class="fonce">D</td></tr><tr><td>L</td></tr><tr><td>M</td></tr><tr><td>M</td></tr><tr><td>J</td></tr><tr><td>V</td></tr><tr><td class="fonce">S</td></tr>
</table></td>

<?

$lib = "";
$local_annee = $mon_annee;
$local_mois = intval($mon_mois);
$ldate1 = str_replace("-", "", Toolbox::date2mysqldate($zone_calendar));
$case_precochee = 0;
for($m = 0; $m < 15; $m++)
{
	if ($local_mois > 12)
	{
		$local_mois = 1;
		$local_annee++;
	}

	$lib .= "<td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
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
			$lib .= "<tr><td class=\"".($jour_sem2 == 0 || $jour_sem2 == 6 ? ($jour_sem2 == 0 ? "vide3" : "vide2") : "vide")."\">&nbsp;</td></tr>";
		else
		{
			$ldate2 = $local_annee.sprintf("%02d%02d", $local_mois, $j);
			$lib .= "<tr><td ".($ldate2 > $ldate1 && $jour_sem2 == $jsem && $case_precochee < $nb_journees ? "style=\"background: #666;\"" : "")." class=\"".($jour_sem2 == 0 || $jour_sem2 == 6 ? ($jour_sem2 == 0 ? "pleine3" : "pleine2") : "pleine")."\"><span><input ".($ldate2 > $ldate1 && $jour_sem2 == $jsem && $case_precochee < $nb_journees ? "checked=\"checked\"" : "")." onclick=\"checkj('".$checkname."');\" type=\"checkbox\" name=\"chkjour\" id=\"".$checkname."\" value=\"1\">".($j > 9 ? "" : "0").$j."</span></td></tr>";
			if ($ldate2 > $ldate1 && $jour_sem2 == $jsem && $case_precochee < $nb_journees) $case_precochee += 1;
		}
	}
	$lib .= "</table></td>";

	$local_mois++;
}

echo $lib;

}

?>

</tr>
</table>

<div class="actions grouped_inv">
<button onclick="return <?= $etape == 0 ? "validate_and_submit();" : "checknbjourneescochees();" ?>" class="button green"><?= $etape == 0 ? "Suivant" : "Ajouter" ?></button>
<button onclick="return annuler();" class="button gray">Annuler</button>
</div>

<script>

choices.build({ name: 'allerretour', c1: 'blue', c2: 'white', labels: ['Oui', 'Non'], values: [{ v: 0, l: 'Oui', s: true }, { v: 1, l: 'Non' }] });
choices.build({ name: 'singledaychoice', c1: 'blue', singlepicking: true, removable: true, values: [ {v: 1, l: 'Lundi'}, {v: 2, l: 'Mardi'}, {v: 3, l: 'Mercredi'}, {v: 4, l: 'Jeudi'}, {v: 5, l: 'Vendredi'}, {v: 6, l: 'Samedi', s: true}, {v: 7, l: 'Dimanche'} ] });

<?
$values = "";
foreach($liste_equipes as $item)
	if ($item['id'] != -1)
		$values .= ($values == "" ? "" : ",")."{v: ".$item['id'].", l: '".Wrapper::stringEncode4JS($item['nom'])."', s: true}";
?>

choices.build({ name: 'allteams', multiple: true, readonly: true, c1: 'orange', values: [<?= $values ?>] });

validate_and_submit = function()
{
    if (!check_JJMMAAAA(el('zone_calendar').value, 'Date'))
		return false;

	var nb = choices.getSelection('singledaychoice');
	if (nb == 7) nb = 0;

	params = '?jsem='+nb+'&allerretour='+choices.getSelection('allerretour')+attrs(['zone_calendar']);
	go({id:'main', url:'edit_days_build_all_matches.php'+params+'&etape=1'});

	return true;
}
annuler = function()
{
	mm({action: 'days'});
	return true;
}
var nb_checkj = <?= $nb_journees ?>;
checkj = function(name)
{
	if (el(name).checked == false)
	{
		nb_checkj--;
		cc('jcochees', nb_checkj);
		el(name).parentNode.parentNode.style.background = '';
		return true;
	}

	if (nb_checkj >= <?= $nb_journees ?>)
	{
		el(name).checked = false;
		alert('Vous ne pouvez pas saisir plus de journées !!!!');
		return false;
	}

	nb_checkj++;
	cc('jcochees', nb_checkj);
	el(name).parentNode.parentNode.style.background = '#666';

	return true;
}
checknbjourneescochees = function()
{
	if (nb_checkj != <?= $nb_journees ?>)
	{
		alert('Vous avez coché ' + nb_checkj + ' journées, vous devez en cocher <?= $nb_journees ?> !!!');
		return false;
	}

	var jours = '';
    var checks = document.getElementsByName('chkjour');
    for(i = 0; i < checks.length; i++) {
        if (checks[i].checked) {
            jours += (jours == '' ? '' : ',') + checks[i].id;
        }
    }

	params = '?jours='+jours;
	go({id:'main', url:'edit_days_build_all_matches_do.php'+params});

	return true;
}
</script>

</div>

<?

function estBissextile ($annee) {
	return (($annee % 4 == 0) && (($annee % 100 != 0) || ($annee % 400 == 0)));
}

?>