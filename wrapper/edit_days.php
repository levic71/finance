<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$idd = isset($_REQUEST['idd']) && is_numeric($_REQUEST['idd']) && $_REQUEST['idd'] > 0 ? $_REQUEST['idd'] : 0;
$modifier = $idd > 0 ? true : false;

// Génération de la date de référence si elle n'existe pas (si $refdate existe alors on vient de calendar.php)
$refdate  = Wrapper::getRequest('refdate', date('d/m/Y'));
$refurl   = isset($_REQUEST['refdate']) ? 'calendar.php' : 'journees.php';

$heure         = "21:00";
$duree         = "90";
$alias_journee = "";
$nb_poules     = 4;
$phase_finale  = _PHASE_FINALE_8_;
$consolante    = 0;
$matchs_auto   = 0;
$matchs_ar     = 1;
$row_equipes   = "";

$joueurs_reguliers      = array();
$joueurs_occasionnels   = array();
$equipes_regulieres     = array();
$equipes_occasionnelles = array();
$items_selectionnes     = array();

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
	while($row = mysqli_fetch_array($res))
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
	mysqli_free_result($res);
}
else
{
    $liste = $sss->getListeEquipes();
    foreach($liste as $item)
    {
		$equipes_regulieres[] = $item;
	}
}

// Récupération du nombre de journée
$scs = new SQLChampionnatsServices($sess_context->getRealChampionnatId());
$saison_active = $scs->getSaisonActive();
$req = "SELECT COUNT(*) total FROM jb_journees WHERE id_champ=".$saison_active['id'];
$res = dbc::execSQL($req);
if ($row = mysqli_fetch_array($res)) $nb_journees = $row['total'] + 1;

if ($modifier) {
	$select = "SELECT * FROM jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$idd;
	$res = dbc::execSQL($select);
	if ($row = mysqli_fetch_array($res))
	{
		$refdate = ToolBox::mysqldate2date($row['date']);
		$heure   = $row['heure'] == '0' || $row['heure'] == '' ? '21:00' : $row['heure'];
		$duree   = $row['duree'];
		$nb_poules    = $row['tournoi_nb_poules'] > 0 ? $row['tournoi_nb_poules'] : 4;
		$phase_finale = $row['tournoi_phase_finale'] > 0 ? $row['tournoi_phase_finale'] :  _PHASE_FINALE_4_;

		$consolante   = $row['tournoi_consolante'];
		$matchs_auto  = 1;
		$tmp     = explode(":", $row['nom']);
		$nb_journees = $tmp[0];
		if (isset($tmp[1])) $alias_journee = $tmp[1];
		if ($sess_context->isFreeXDisplay())
			$items = explode(',', $row['joueurs']);
		else
			$items = explode(',', $row['equipes']);

		$row_equipes = $row['equipes'];
		foreach($items as $i) $items_selectionnes[$i] = $i;
	}
}

?>

<div id="edit_days" class="edit">

<input type="hidden" id="refurl" name="refurl" value="<?= $refurl ?>" />
<input type="hidden" id="type_participant" name="type_participant" value="<?= $sess_context->isFreeXDisplay() ? 0 : 1 ?>" />

<h2 class="grid days"><?= $modifier ? "Modification" : "Ajout" ?> de la <?= ToolBox::conv_lib_journee($nb_journees) ?></h2>

<table cellspacing="0" cellpadding="0" class="jkgrid">
<tr>
	<td class="c2"><div><label for="num_journee">N° - Alias de la journée</label></div></td>
	<td class="c3"><div class="singlepicking"><button class="button blue" id="numj" onclick="numbers.picker({ name: 'numj' });"><span><?= $nb_journees ?></span></button> - <input type="text" style="width: 240px;" id="alias_journee" name="alias_journee" value="<?= $alias_journee ?>" /><small>(facultatif)</small></div></td>
</tr>
<tr>
	<td class="c2"><div><label for="zone_calendar">Date/Heure</label></div></td>
	<td class="c3"><div class="singlepicking"><button class="button blue" id="zone_calendar" onclick="calendar.picker({ name: 'zone_calendar' });"><span><?= $refdate ?></span></button><small>JJ/MM/AAAA</small><button style="margin-left: 60px;" id="heure" class="button blue" onclick="clock.picker({ name: 'heure' });"><span><?= $heure ?></span></button><small>HH:MM</small></div></td>
</tr>
<? if ($sess_context->getChampionnatType() != _TYPE_TOURNOI_) { ?>
<tr>
	<td class="c2"><div><label for="duree">Durée prévue</label></div></td>
	<td class="c3"><div id="duree"></div></td>
</tr>
<? } else { ?>
<tr>
	<td class="c2"><div><label for="nb_poules">Nombre de poules</label></div></td>
	<td class="c3">
		<div id="nb_poules" style=" float: left;width: 100px;"></div>
		<div style="float: left; width: 360px; <?= $modifier ? "display: none;" : "" ?>">
			<label for="matchs_ar" style="width: 155px;">Matchs aller/retour</label>
			<div id="matchs_ar" class="grouped" style="width: 150px;"></div>
		</div>
	</td>
</tr>
<tr>
	<td class="c2"><div><label for="phase_finale">Phase finale</label></div></td>
	<td class="c3">
		<div id="phase_finale" style=" float: left;width: 155px;"></div>
		<div style="float: left; width: 280px;">
			<label for="consolante" style="width: 100px;">Consolante</label>
			<div id="consolante" style="width: 100px;"></div>
		</div>
	</td>
</tr>
<tr <?= $modifier ? "style=\"display: none;\"" : "" ?>>
	<td class="c2"><div><label for="matchs_auto" style="line-height: 14px;">Création automatique des <br />matchs de poules</label></div></td>
	<td class="c3"><div id="matchs_auto" class="grouped" style="width: 200px;"></div></td>
</tr>
<? } ?>
</table>


<table cellspacing="0" cellpadding="0" class="jkgrid" id="days" style="border-top: 1px solid #eee;">

<thead><tr><th>
<div style="float: left; padding-left: 15px; width: <?= $sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "675px" : "205px" ?>; line-height: 30px;">&nbsp;<?= $sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "Constituer les poules en cliquant sur chaque bouton" : "Sélection des ".($sess_context->isFreeXDisplay() ? "joueurs" : "équipes") ?></div>
<? if ($sess_context->getChampionnatType() != _TYPE_TOURNOI_) { ?>
<div style="float:right; width: 470px; line-height: 32px;  text-align: left;">
	<img class="bt" style="margin: 0px 0px -10px 10px;" onclick="choices.selectAll('item_choice');" src="img/icons/dark/appbar.checkmark.thick.png" />
	<img class="bt" style="margin: 0px 0px -10px 10px;" onclick="choices.unSelectAll('item_choice');" src="img/icons/dark/appbar.checkmark.thick.unchecked.png" />
</div>
<? } ?>
</th></tr></thead>

<tbody>
<tr <?= $sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "style=\"display: none;\"" : "" ?>>
<td class="c2"><div></div></td><td class="c3"><div id="item_choice">
<?
$values = "";
$k=1;
if ($sess_context->isFreeXDisplay()) {
	foreach($joueurs_reguliers as $j)    { $values .= ($values == "" ? "" : ",")."{ l: '".Wrapper::stringEncode4JS($j['pseudo'])."', v: '".$j['id']."', s: ".(isset($items_selectionnes[$j['id']]) ? "true" : "false")." }"; }
	foreach($joueurs_occasionnels as $j) { $values .= ($values == "" ? "" : ",")."{ l: '".Wrapper::stringEncode4JS($j['pseudo'])."', v: '".$j['id']."', s: ".(isset($items_selectionnes[$j['id']]) ? "true" : "false")." }"; }
	$nb_items = count($joueurs_reguliers)+count($joueurs_occasionnels);
} else if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_) {
	foreach($equipes_regulieres as $e)     { $values .= ($values == "" ? "" : ",")."{ l: '".Wrapper::stringEncode4JS($e['nom'])."', v: '".$e['id']."', s: ".(isset($items_selectionnes[$e['id']]) ? "true" : "true")." }"; }
	foreach($equipes_occasionnelles as $e) { $values .= ($values == "" ? "" : ",")."{ l: '".Wrapper::stringEncode4JS($e['nom'])."', v: '".$e['id']."', s: ".(isset($items_selectionnes[$e['id']]) ? "true" : "true")." }"; }
	$nb_items = count($equipes_regulieres)+count($equipes_occasionnelles);
} else {
	foreach($equipes_regulieres as $e)     { $values .= ($values == "" ? "" : ",")."{ l: '".Wrapper::stringEncode4JS($e['nom'])."', v: '".$e['id']."', s: ".(isset($items_selectionnes[$e['id']]) ? "true" : "false")." }"; }
	foreach($equipes_occasionnelles as $e) { $values .= ($values == "" ? "" : ",")."{ l: '".Wrapper::stringEncode4JS($e['nom'])."', v: '".$e['id']."', s: ".(isset($items_selectionnes[$e['id']]) ? "true" : "false")." }"; }
	$nb_items = count($equipes_regulieres)+count($equipes_occasionnelles);
}
?>
</div></td></tr>

<? if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_) { ?>
<? for($k=1; $k<=16; $k++) { ?>
<tr id="tr_poule<?= $k ?>"><td><div style="padding-left: 15px; width: 675px;">
<div class="singlepicking"><button class="button blue" onclick="choices.multipicker('item_choice', 'poule<?= $k ?>');"><span>Poule <?= $k ?></span></button></div></span>
<div id="poule<?= $k ?>" style="padding-top: 5px;"></div>
</div></td></tr>
<? } ?>
<? } ?>

</tbody></table>

<input type="hidden" id="nb_items" name="nb_items" value="<?= $nb_items ?>" />
<input type="hidden" id="create_auto" name="create_auto" value="0" />

</table>

<div class="actions grouped_inv">
<button onclick="return validate_and_submit();" class="button green" style="float: right;"><?= $modifier ? "Valider" : "Ajouter" ?></button>
<button onclick="return annuler();" class="button gray" style="float: right;">Annuler</button>
</div>

<script>

showhide_poules = function(nb_poules) {
	for (i=1; i<=16; i++) {
		if (i <= nb_poules) show('tr_poule'+i);
		else hide('tr_poule'+i);
	}
}

change_nb_poules = function(name) {
	showhide_poules(choices.getSelection(name));
}

<? if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_) { ?>
choices.build({ name: 'nb_poules',    c1: 'blue', c2: 'white', singlepicking: true, removable: true, callback: 'change_nb_poules', values: [{ v: 1, l: '1', s: <?= $nb_poules == 1 ? "true" : "false" ?> } <? for($i=2; $i <= 16; $i++) { echo ", { v: ".$i.", l: '".$i."', s: ".($nb_poules == $i ? "true" : "false")." }"; } ?> ] });
choices.build({ name: 'phase_finale', c1: 'blue', c2: 'white', singlepicking: true, removable: true, values: [{ v: <?= _PHASE_FINALE_32_ ?>, l: '<?= $libelle_phase_finale[_PHASE_FINALE_32_] ?>', s: <?= $phase_finale == _PHASE_FINALE_32_ ? "true" : "false" ?> }, { v: <?= _PHASE_FINALE_16_ ?>, l: '<?= $libelle_phase_finale[_PHASE_FINALE_16_] ?>', s: <?= $phase_finale == _PHASE_FINALE_16_ ? "true" : "false" ?> }, { v: <?= _PHASE_FINALE_8_ ?>, l: '<?= $libelle_phase_finale[_PHASE_FINALE_8_] ?>', s: <?= $phase_finale == _PHASE_FINALE_8_ ? "true" : "false" ?> }, { v: <?= _PHASE_FINALE_4_ ?>, l: '<?= $libelle_phase_finale[_PHASE_FINALE_4_] ?>', s: <?= $phase_finale == _PHASE_FINALE_4_ ? "true" : "false" ?> }, { v: <?= _PHASE_FINALE_2_ ?>, l: '<?= $libelle_phase_finale[_PHASE_FINALE_2_] ?>', s: <?= $phase_finale == _PHASE_FINALE_2_ ? "true" : "false" ?> }] });
choices.build({ name: 'consolante',   c1: 'blue', c2: 'white', singlepicking: true, removable: true, values: [{ v: 0, l: 'Non', s: <?= $consolante == 0 ? "true" : "false" ?> }, { v: <?= _PHASE_FINALE_32_ ?>, l: '<?= $libelle_phase_finale[_PHASE_FINALE_32_] ?>', s: <?= $consolante == _PHASE_FINALE_32_ ? "true" : "false" ?> }, { v: <?= _PHASE_FINALE_16_ ?>, l: '<?= $libelle_phase_finale[_PHASE_FINALE_16_] ?>', s: <?= $consolante == _PHASE_FINALE_16_ ? "true" : "false" ?> }, { v: <?= _PHASE_FINALE_8_ ?>, l: '<?= $libelle_phase_finale[_PHASE_FINALE_8_] ?>', s: <?= $consolante == _PHASE_FINALE_8_ ? "true" : "false" ?> }, { v: <?= _PHASE_FINALE_4_ ?>, l: '<?= $libelle_phase_finale[_PHASE_FINALE_4_] ?>', s: <?= $consolante == _PHASE_FINALE_4_ ? "true" : "false" ?> }, { v: <?= _PHASE_FINALE_2_ ?>, l: '<?= $libelle_phase_finale[_PHASE_FINALE_2_] ?>', s: <?= $consolante == _PHASE_FINALE_2_ ? "true" : "false" ?> }] });
choices.build({ name: 'matchs_auto',  c1: 'blue', c2: 'white', values: [{ v: 0, l: 'Oui', s: <?= $matchs_auto == 0 ? "true" : "false" ?> }, { v: 1, l: 'Non', s: <?= $matchs_auto == 1 ? "true" : "false" ?> }] });
choices.build({ name: 'matchs_ar',    c1: 'blue', c2: 'white', values: [{ v: 0, l: 'Oui', s: <?= $matchs_ar == 0 ? "true" : "false" ?> }, { v: 1, l: 'Non', s: <?= $matchs_ar == 1 ? "true" : "false" ?> }] });
choices.build({ name: 'item_choice',  c1: 'orange', multipicking: true, closelibelle: 'Fermer', values: [<?= $values ?>] });
<?
	$defaults = explode('|', $row_equipes);
	for($i = 0; $i < count($defaults); $i++) {
?>
choices.init('item_choice', 'poule<?= ($i+1) ?>', '<?= $defaults[$i] ?>');
<? } ?>
showhide_poules(<?= $nb_poules ?>);
<? } else { ?>
choices.build({ name: 'duree', c1: 'blue', c2: 'white', singlepicking: true, removable: true, values: [{ v: 135, l: '2h15', s: <?= $duree == 135 ? "true" : "false" ?> }, { v: 120, l: '2h', s: <?= $duree == 120 ? "true" : "false" ?> }, { v: 90, l: '1h30', s: <?= $duree == 90 ? "true" : "false" ?> }, { v: 60, l: '1h', s: <?= $duree == 60 ? "true" : "false" ?> }, { v: 45, l: '45min', s: <?= $duree == 45 ? "true" : "false" ?> }, { v: 30, l: '30min', s: <?= $duree == 30 ? "true" : "false" ?> }] });
choices.build({ name: 'item_choice', c1: 'orange', multiple: true, values: [<?= $values ?>] });
<? } ?>

validate_and_submit = function()
{
	var num_journee = numbers.getValue('numj');
	var heure = clock.getValue('heure');
	var zone_calendar = calendar.getValue('zone_calendar');

    if (!check_num(num_journee, 'N° journée', -1))
		return false;

    if (!check_JJMMAAAA(zone_calendar, 'Date'))
		return false;

    if (!check_alphanumext(heure, 'Heure'))
		return false;


<? if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_) { ?>

	var selection = '';
	var nb_poules = choices.getSelection('nb_poules');

	for(i=1; i <= nb_poules; i++) {
		selection += (i == 1 ? '' : '|')+choices.getSelection('poule'+i);
	}

	var tmp = selection.replace('|', ',').split(',');
	var nb_sel = tmp.length;

<? } else { ?>

	var min_selection = <?= $sess_context->isFreeXDisplay() ? 4 : 2 ?>;
	var selection = choices.getSelection('item_choice');

	tmp = selection.split(',');
	nb_sel = tmp.length;

	if (selection == '') nb_sel = 0;

	if (nb_sel < min_selection)
	{
		alert('Vous devez sélectionner au minimum '+min_selection+' <?= $sess_context->isFreeXDisplay() ? "joueurs" : "équipes" ?> !');
		return false;
	}

<? } ?>

	journees = ""; // variables gloables
	var duree = <?= $sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? 0 : "choices.getSelection('duree')" ?>;
	var tournoi_opt = '';

<? if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_) { ?>
	var phase_finale = choices.getSelection('phase_finale');
	var consolante = choices.getSelection('consolante');

	var min_selection = (consolante * 2) + (phase_finale * 2);
	if (nb_sel < Math.round((min_selection*3)/4)) {
		if (!confirm('Le nombre d\'équipes sélectionnées ne semble pas en adéquation avec le reste des paramètres,\nvoulez-vous continuer ?')) return false;
	}

	var matchs_auto = choices.getSelection('matchs_auto');
	var matchs_ar = choices.getSelection('matchs_ar');
	var tournoi_opt = 'nb_poules='+nb_poules+'&matchs_auto='+matchs_auto+'&matchs_ar='+matchs_ar+'&phase_finale='+phase_finale+'&consolante='+consolante+'&';
<? } ?>

	params = '?'+tournoi_opt+'num_journee='+num_journee+'&heure='+heure+'&zone_calendar='+zone_calendar+'&duree='+duree+'&selection='+selection+attrs(['create_auto', 'type_participant', 'nom', 'alias_journee']);
	go({id:'main', url:'edit_days_do.php'+params+'&idd='+<?= $idd ?>+'&upd=<?= $modifier ? 1 : 0 ?>'});

	return true;
}
annuler = function()
{
	mm({ <?= $modifier ? "action:'matches', tournoi: ".($sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? 1 : 0).", idj:'".$idd."', name:'".$nb_journees.":".$alias_journee."', date:'".$refdate."'" : "action: 'days'" ?> });
	return true;
}
mandatory(['num_journee', 'zone_calendar', 'heure']); fs('num_journee');
</script>

</div>