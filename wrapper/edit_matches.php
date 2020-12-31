<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/StatsBuilder.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$idm = isset($_REQUEST['idm']) && is_numeric($_REQUEST['idm']) && $_REQUEST['idm'] > 0 ? $_REQUEST['idm'] : 0;
$modifier = $idm > 0 ? true : false;


// Récupération des informations de la journée
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$row = $sjs->getJournee();

$is_journee_alias = ($row['id_journee_mere'] == "" || $row['id_journee_mere'] == "0" ? false : true);

// Attention, si journée alias, prendre les infos de la journée mère (équipes)
$real_id_journee = $is_journee_alias ? $row['id_journee_mere'] : $row['id'] ;

// Si pref_saisie = 0 alors la création de la journée a été faites avec choix des joueurs, si = 1 avec choix des équipes
$pref_saisie = $row['pref_saisie'];

if ($is_journee_alias)
{
	$sjs2 = new SQLJourneesServices($sess_context->getChampionnatId(), $real_id_journee);
	$row2 = $sjs2->getJournee();
	$row['equipes'] = $row2['equipes'];
	$pref_saisie = $row2['pref_saisie'];
}

// Récupération des équipes
$selected_eq1 = "";
$selected_eq2 = "";
$type_matchs = "";
if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
{
	// Pour résoudre pb avec la page matchs_tournoi.php
	if (isset($niveau)) $options_type_matchs = $niveau;

	$items = explode('|', $options_type_matchs);
	$type_matchs = $items[0];
	$niveau_type = isset($items[1]) ? $items[1] : "";
	$ordre       = isset($items[2]) ? $items[2] : 0;

	// Formatage du champs équipes pour ne prendre que les equipes de poules pour les poules et toutes équipes pour la phase finale
	$equipes = "";
	if ($type_matchs == "P" || $type_matchs == "SP")
	{
		$tmp = explode('|', $row['equipes']);
		$equipes = isset($tmp[$niveau_type-1]) ? $tmp[$niveau_type-1] : "";
	}
	else
	{
		$tmp = str_replace('|', ',', $row['equipes']);
		$items = explode(',', $tmp);
		foreach($items as $item)
			if ($item != "") $equipes .= $equipes == "" ? $item : ",".$item;

		// Sur la phase finale, on cherche à connaitre les équipes par défaut (ex: pour la finale, on prend les 2 vainqueurs des demis)
		if (!$modifier)
		{
			$sms = new SQLMatchsServices($sess_context->getChampionnatId(), $real_id_journee, -1);
			// Choix equipe1
			$row_match = $sms->getMatchByNiveau("F|".($niveau_type*2)."|".(($ordre*2) - 1));
			if ($row_match) $selected_eq1 = StatsJourneeBuilder::kikiGagne($row_match) == 1 ? $row_match['id_equipe1'] : $row_match['id_equipe2'];
			// Choix equipe2
			$row_match = $sms->getMatchByNiveau("F|".($niveau_type*2)."|".($ordre*2));
			if ($row_match) $selected_eq2 = StatsJourneeBuilder::kikiGagne($row_match) == 1 ? $row_match['id_equipe1'] : $row_match['id_equipe2'];
		}
	}
}
else
	$equipes = $row['equipes'];

// Liste des joueurs
$tab_jj = explode(',', $row['joueurs']);

// Liste des équipes possibles
$eq = array();

// Récupération des informations des équipes qui participent à cette journée
if ($pref_saisie == 0) // Journée saisie avec sélection de joueurs
{
	$select = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId();
	$res = dbc::execSQL($select);
	while($row = mysqli_fetch_array($res))
	{
		$items = explode('|', $row['joueurs']);

		// Il faut qu'au moins 2 des joueurs de l'équipe soit dans la liste des joueurs sélectionnés de cette journée
		$nb = 0;
		foreach($items as $j)
			if (ToolBox::findInArray($j, $tab_jj)) $nb++;

		if ($nb > 1) $eq[$row['nom']] = $row;
	}
}
else // Journée saisie avec sélection d'équipes
{
	if ($equipes != "")
	{
		$select = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id IN (".SQLServices::cleanIN($equipes).")";
		$res = dbc::execSQL($select);
		while($row = mysqli_fetch_array($res)) $eq[$row['nom']] = $row;
	}
}

// Tri des equipes
if (count($eq) > 0) ksort($eq);

// Valeur des d'une victoire/défaite pour un match de type tournoi (classement+finale)
$points_victoire = 0;
$points_defaite  = 0;
$match_joue      = 0;
$prolongation    = 0;
$tirs_au_but     = 0;
$tirs1           = "";
$tirs2           = "";
$play_date       = "";
$play_time       = "";

// Récupération des infos si match à modifier
if ($modifier)
{
	$sms = new SQLMatchsServices($sess_context->getChampionnatId(), $real_id_journee, $idm);
	$row_match = $sms->getMatch();
//	print_r($row_match);
	$play_date    = $row_match['play_date'];
	$play_time    = $row_match['play_time'];
	$selected_eq1 = $row_match['id_equipe1'];
	$selected_eq2 = $row_match['id_equipe2'];
	$match_joue   = $row_match['match_joue'];
	$prolongation = $row_match['prolongation'];
	$tirs_au_but  = $row_match['penaltys'] != ""  && $row_match['penaltys'] != "0" && $row_match['penaltys'] != "|" ? 1 : 0;
	if ($row_match['penaltys'] != "" && $row_match['penaltys'] != "0" && $row_match['penaltys'] != "|")
	{
		$tmp = explode('|', $row_match['penaltys']);
		$tirs1 = $tmp[0];
		$tirs2 = $tmp[1];
	}

	if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
	{
		$items = explode('|', $row_match['niveau']);
		$type_matchs = $items[0];
		$niveau_type = $items[1];

		if ($type_matchs == "C" || ($type_matchs == "F" && $niveau_type == "1") || ($type_matchs == "Y" && $niveau_type == "1"))
		{
			$items = explode('|', $row_match['score_points']);
			$points_victoire = isset($items[0]) && $items[0] != '' ? $items[0] : 0;
			$points_defaite  = isset($items[1]) && $items[0] != '' ? $items[1] : 0;
		}
	}
}

$score[0][0] = "";
$score[0][1] = "";
$nbset = $modifier ? $row_match['nbset'] : 1;
if ($modifier)
{
	$sm = new StatMatch($row_match['resultat'], $row_match['nbset']);
	$score = $sm->getScore();

	// Si forfait equipe1 ou equipe2
	if ($score == -1 || $score == -2)
	{
		$nb_set = 1;
		$score[0][0] = "";
		$score[0][1] = "";
	}
	if (!isset($score[0][0]) || $score[0][0] == "") $score[0][0] = 0;
	if (!isset($score[0][1]) || $score[0][1] == "") $score[0][1] = 0;
}

for($i=$nbset; $i < 5; $i++) { $score[$i][0] = ""; $score[$i][1] = ""; }


$select1 = ""; $nom1 = ""; reset($eq);
while(list($cle, $valeur) = each($eq)) {
	$select1 .= "<option value=\"".$valeur['id']."|".$valeur['joueurs']."\" ".($valeur['id'] == $selected_eq1 ? "selected=\"selected\"" : "").">".$cle."</option>";
	if ($valeur['id'] == $selected_eq1) $nom1 = $cle;
}

$select2 = ""; $nom2 = ""; reset($eq);
while(list($cle, $valeur) = each($eq)) {
	$select2 .= "<option value=\"".$valeur['id']."|".$valeur['joueurs']."\" ".($valeur['id'] == $selected_eq2 ? "selected=\"selected\"" : "").">".$cle."</option>";
	if ($valeur['id'] == $selected_eq2) $nom2 = $cle;
}

$select1 = "<select id=\"equipe1\" name=\"equipe1\" onchange=\"update_sm(1, ".$sess_context->getChampionnatType().");\">".$select1."</select>";
$select2 = "<select id=\"equipe2\" name=\"equipe2\" onchange=\"update_sm(2, ".$sess_context->getChampionnatType().");\">".$select2."</select>";

?>

<div id="edit_matches" class="edit">

<h2 class="grid">
	<table border="0" cellpadding="0">
		<tr>
			<td><?= $modifier ? "Modifier un match" : "Ajout d'un match" ?></td>
<? if ($modifier) { ?>
			<td style="padding-left: 165px;"><button id="playmatch" class="button blue" onclick="liveScoring(<?= $idm ?>, '<?= $nom1 ?>', '<?= $nom2 ?>', '<?= $row_match['nbset'] ?>', '<?= $row_match['resultat'] ?>');" title="Saisie Live"><img src="img/playback_play.png" /></button></td>
<? } else { ?>
			<td style="padding-left: 165px;"><button id="playmatch" class="button blue" onclick="alert('Live scoring disponible uniquement après insertion');" title="Saisie Live"><img src="img/playback_play.png" /></button></td>
<? } ?>
			<td style="padding-left: 20px;"><button id="opt1" class="button blue" onclick="show_opts();"><img src="img/equalizer.png" /></button><button id="opt2" class="button blue" onclick="hide_opts();" style="display: none;"><img src="img/equalizer.png" /></button></td>
			<td style="padding-left: 20px;" class="grouped" id="nbsetsbuttons"><button class="button gray">Set</button><button class="button gray" onclick="setsets(1);" id="nbset1" name="nbset" value="1"> 1 </button><button class="button gray" onclick="setsets(2);" id="nbset2" name="nbset" value="2"> 2 </button><button class="button gray" onclick="setsets(3);" id="nbset3" name="nbset" value="3"> 3 </button><button class="button gray" onclick="setsets(4);" id="nbset4" name="nbset" value="4"> 4 </button><button class="button gray" onclick="setsets(5);" id="nbset5" name="nbset" value="5"> 5 </button></td>
		</tr>
	</table>
</h2>

<table cellspacing="0" cellpadding="0" class="jkgrid" id="matches">
<thead>
<tr><th class="c1"><div>forfait</div></th><th class="c2"><div>Equipe 1</div></th><th class="c3"><div>Score</div></th><th class="c4"><div>Equipe 2</div></th><th class="c5"><div>forfait</div></th></tr>
</thead>
<tbody>
<tr id="set1" class="trset">
	<td class="c1"><div><input type="checkbox" id="forfait1" value="-1" <?= $score == -1 ? "checked=\"checked\"" : "" ?> /></div></td>
	<td class="c2"><div><?= $select1 ?></div></td>
	<td class="c3"><div class="singlepicking"><button class="button black" id="score1" onclick="numbers.picker({ name: 'score1' });"><span><?= $score[0][0] ?></span></button> - <button class="button black mirror" id="score2" onclick="numbers.picker({ name: 'score2' });"><span><?= $score[0][1] ?></span></button></td>
	<td class="c4"><div><?= $select2 ?></div></td>
	<td class="c5"><div><input type="checkbox" id="forfait2" value="-2" <?= $score == -2 ? "checked=\"checked\"" : "" ?> /></div></td>
</tr>
<tr id="set2" class="trset"><td class="c1"><div></div></td><td class="c2"><div></div></td><td class="c3"><div><div class="singlepicking"><button class="button black" id="score3" onclick="numbers.picker({ name: 'score3' });"><span><?= $score[1][0] ?></span></button> - <button class="button black mirror" id="score4" onclick="numbers.picker({ name: 'score4' });"><span><?= $score[1][1] ?></span></button></div></td><td class="c4"><div></div></td><td class="c5"><div></div></td></tr>
<tr id="set3" class="trset"><td class="c1"><div></div></td><td class="c2"><div></div></td><td class="c3"><div><div class="singlepicking"><button class="button black" id="score5" onclick="numbers.picker({ name: 'score5' });"><span><?= $score[2][0] ?></span></button> - <button class="button black mirror" id="score6" onclick="numbers.picker({ name: 'score6' });"><span><?= $score[2][1] ?></span></button></div></td><td class="c4"><div></div></td><td class="c5"><div></div></td></tr>
<tr id="set4" class="trset"><td class="c1"><div></div></td><td class="c2"><div></div></td><td class="c3"><div><div class="singlepicking"><button class="button black" id="score7" onclick="numbers.picker({ name: 'score7' });"><span><?= $score[3][0] ?></span></button> - <button class="button black mirror" id="score8" onclick="numbers.picker({ name: 'score8' });"><span><?= $score[3][1] ?></span></button></div></td><td class="c4"><div></div></td><td class="c5"><div></div></td></tr>
<tr id="set5" class="trset"><td class="c1"><div></div></td><td class="c2"><div></div></td><td class="c3"><div><div class="singlepicking"><button class="button black" id="score9" onclick="numbers.picker({ name: 'score9' });"><span><?= $score[4][0] ?></span></button> - <button class="button black mirror" id="score10" onclick="numbers.picker({ name: 'score10' });"><span><?= $score[4][1] ?></span></button></div></td><td class="c4"><div></div></td><td class="c5"><div></div></td></tr>

<tr id="match_options_1" style="display: none;"><td class="c10" colspan="2"><div>Match joué</div></td><td class="c11" colspan="3"><div><div id="match_joue" class="grouped" style="width: 160px; float: left;"></div> <small style="float: left;">(Force match joué pour un résultat 0-0)</small></div></td></tr>
<tr id="match_options_2" style="display: none;"><td class="c10" colspan="2"><div>Prolongation jouée</div><td class="c11" colspan="3"><div><div id="prolongation" class="grouped" style="width: 160px;"></div></div></td></tr>
<tr id="match_options_3" style="display: none;"><td class="c10" colspan="2"><div>Scéance de tirs au but</div><td class="c11" colspan="3"><div><div id="tirs_au_but" class="grouped" style="float:left; width: 160px;"></div>

		<div id="penalty" class="singlepicking" style="float: left; width: 200px; <?= $tirs_au_but == 1 ? "" : "display:none;" ?>">
			<button class="button black" id="tirs1" onclick="numbers.picker({ name: 'tirs1' });"><span><?= $tirs1 ?></span></button>
			-
			<button class="button black" id="tirs2" onclick="numbers.picker({ name: 'tirs2' });"><span><?= $tirs2 ?></span></button>
		</div>

</div></td></tr>

<tr id="match_options_4" style="display: none;">
	<td class="c10" colspan="2"><div>Date/Heure</div></td>
	<td class="c11" colspan="3"><div class="singlepicking"><button class="button blue" id="play_date" onclick="calendar.picker({ name: 'play_date' });"><span><?= $play_date ?></span></button><small>JJ/MM/AAAA</small><button style="margin-left: 15px;" id="play_time" class="button blue" onclick="clock.picker({ name: 'play_time' });"><span><?= $play_time ?></span></button><small>HH:MM</small><small>(Si différent de journée)</small></div></td>
</tr>

<? if (($sess_context->getChampionnatType() == _TYPE_TOURNOI_ ) && ($type_matchs == "C" || ($type_matchs == "F" && $niveau_type == "1") || ($type_matchs == "Y" && $niveau_type == "1"))) { ?>
	<tr class="points singlepicking"><td class="c20" class="singlepicking"><div>Nb points pour le vainqueur</div></td><td class="c21"><div><button class="button orange" id="ptsvictoire" onclick="numbers.picker({ name: 'ptsvictoire' });"><span><?= $points_victoire ?></span></button> - <button class="button orange mirror" id="ptsdefaite" onclick="numbers.picker({ name: 'ptsdefaite' });"><span><?= $points_defaite ?></span></button></div></td><td  class="c22"><div>Nb points pour le perdant</div></td></tr>
<? } ?>

</tbody>

</table>

<div class="actions grouped_inv">
<button onclick="return validate_and_submit();" class="button green"><?= $modifier ? "Valider" : "Ajouter" ?></button>
<button onclick="return annuler();" class="button gray">Annuler</button>
</div>

<script>

choices.build({ name: 'match_joue',  c1: 'blue', c2: 'white', values: [{ v: 0, l: 'Non', s: <?= $match_joue == 0 ? "true" : "false" ?> }, { v: 1, l: 'Oui', s: <?= $match_joue == 1 ? "true" : "false" ?> }] });
choices.build({ name: 'prolongation',  c1: 'blue', c2: 'white', values: [{ v: 0, l: 'Non', s: <?= $prolongation == 0 ? "true" : "false" ?> }, { v: 1, l: 'Oui', s: <?= $prolongation == 1 ? "true" : "false" ?> }] });
choices.build({ name: 'tirs_au_but',  c1: 'blue', c2: 'white', callback: 'tirsaubut', values: [{ v: 0, l: 'Non', s: <?= $tirs_au_but == 0 ? "true" : "false" ?> }, { v: 1, l: 'Oui', s: <?= $tirs_au_but == 1 ? "true" : "false" ?> }] });

setsets = function(x) { for(var j=1; j < 6; j++) el('nbset'+j).className='button gray'; el('nbset'+x).className='button blue'; for(var i=1; i <= x; i++) show('set'+i); for(var i=(x+1); i < 6; i++) hide('set'+i); return false; }
tirsaubut = function() { if (choices.getSelection('tirs_au_but') == 0) hide('penalty'); else show('penalty'); }

<? if ($sess_context->getChampionnatType() != _TYPE_LIBRE_ ) { ?>
update_sm(1, <?= $sess_context->getChampionnatType() ?>);
update_sm(2, <?= $sess_context->getChampionnatType() ?>);
<? } ?>

show_opts = function() {
	show('match_options_1');
	show('match_options_2');
	show('match_options_3');
	show('match_options_4');
	hide('opt1');
	show('opt2');
}

hide_opts = function() {
	hide('match_options_1');
	hide('match_options_2');
	hide('match_options_3');
	hide('match_options_4');
	show('opt1');
	hide('opt2');
}

validate_and_submit = function()
{
	var play_date = calendar.getValue('play_date');
	var play_time = clock.getValue('play_time');
	var tirs_au_but  = choices.getSelection('tirs_au_but');
	var tirs1 = numbers.getValue('tirs1');
	var tirs2 = numbers.getValue('tirs2');
	var score1_zip = numbers.getValue('score1');
	var score2_zip = numbers.getValue('score2');
	var score3_zip = numbers.getValue('score3');
	var score4_zip = numbers.getValue('score4');
	var score5_zip = numbers.getValue('score5');
	var score6_zip = numbers.getValue('score6');
	var score7_zip = numbers.getValue('score7');
	var score8_zip = numbers.getValue('score8');
	var score9_zip = numbers.getValue('score9');
	var score10_zip = numbers.getValue('score10');
<? if (($sess_context->getChampionnatType() == _TYPE_TOURNOI_ ) && ($type_matchs == "C" || ($type_matchs == "F" && $niveau_type == "1") || ($type_matchs == "Y" && $niveau_type == "1"))) { ?>
	var points_victoire = numbers.getValue('ptsvictoire');
	var points_defaite  = numbers.getValue('ptsdefaite');
<? } else { ?>
	var points_victoire = 0;
	var points_defaite  = 0;
<? } ?>

    if (play_date != '' && !check_JJMMAAAA(play_date, 'Date'))
		return false;

    if (el('equipe1').value == el('equipe2').value)
    {
        alert('Vous devez sélectionner 2 équipes différentes ...');
        return false;
    }

	var eq1 = el('equipe1').value.split('|');
	var eq2 = el('equipe2').value.split('|');

	if (el('forfait1').checked &&  el('forfait2').checked)
	{
        alert('Les 2 équipes ne peuvent pas être fortaites ensemble ...');
        return false;
	}

<? if ($sess_context->getChampionnatType() == _TYPE_LIBRE_) { ?>
	if (eq1[1] == eq2[1] || eq1[1] == eq2[2] || eq1[2] == eq2[1] || eq1[2] == eq2[2])
    {
        alert('Vous devez sélectionner 2 équipes dont les joueurs sont tous différents ...');
        return false;
    }
<? } ?>

	if (score1_zip != '' && !check_num(score1_zip, 'score1', 0, 9999)) return false;
	if (score1_zip != '' && !check_num(score2_zip, 'score2', 0, 9999)) return false;

	nbset = 1;
	for(var j=1; j < 6; j++)
		if (el('nbset'+j).className == 'button blue') nbset = j;

	if (nbset == 2 && score3_zip != '' && !check_num(score3_zip, 'score3', 0, 9999)) return false;
	if (nbset == 2 && score4_zip != '' && !check_num(score4_zip, 'score4', 0, 9999)) return false;
	if (nbset == 3 && score5_zip != '' && !check_num(score5_zip, 'score5', 0, 9999)) return false;
	if (nbset == 3 && score6_zip != '' && !check_num(score6_zip, 'score6', 0, 9999)) return false;
	if (nbset == 4 && score7_zip != '' && !check_num(score7_zip, 'score7', 0, 9999)) return false;
	if (nbset == 4 && score8_zip != '' && !check_num(score8_zip, 'score8', 0, 9999)) return false;
	if (nbset == 5 && score9_zip != '' && !check_num(score9_zip, 'score9', 0, 9999)) return false;
	if (nbset == 5 && score10_zip != '' && !check_num(score10_zip, 'score10', 0, 9999)) return false;

<? if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_) { ?>
	if (tirs_au_but == 1)
	{
		if (tirs1 == '' || tirs2 == '') {
	        alert('Vous devez au sélectionner au moins une valeur dans les tirs au buts ...');
	        return false;
		}
		if (tirs1 == tirs2)  {
	        alert('Pour les tirs au but, vous devez au sélectionner 2 scores différents ...');
	        return false;
		}
	}
<? } ?>

	var match_joue   = choices.getSelection('match_joue');
	var prolongation = choices.getSelection('prolongation');
	var matches_opt = 'tirs1='+tirs1+'&tirs2='+tirs2+'&play_date='+play_date+'&play_time='+play_time+'&match_joue='+match_joue+'&prolongation='+prolongation+'&tirs_au_but='+tirs_au_but+'&';
	matches_opt += 'score1_zip='+score1_zip+'&';
	matches_opt += 'score2_zip='+score2_zip+'&';
	matches_opt += 'score3_zip='+score3_zip+'&';
	matches_opt += 'score4_zip='+score4_zip+'&';
	matches_opt += 'score5_zip='+score5_zip+'&';
	matches_opt += 'score6_zip='+score6_zip+'&';
	matches_opt += 'score7_zip='+score7_zip+'&';
	matches_opt += 'score8_zip='+score8_zip+'&';
	matches_opt += 'score9_zip='+score9_zip+'&';
	matches_opt += 'score10_zip='+score10_zip+'&';
	matches_opt += 'points_victoire='+points_victoire+'&';
	matches_opt += 'points_defaite='+points_defaite+'&';

	params = '?'+matches_opt+'<?= $sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "options_type_matchs=".$options_type_matchs."&" : "" ?>nbset='+nbset+'&eq1='+eq1[0]+'&eq2='+eq2[0]+attrs(['forfait1', 'forfait2']);
	go({id:'main', url:'edit_matches_do.php'+params+'&idm=<?= $idm ?>&upd=<?= $modifier ? 1 : 0 ?>'});

	return true;
}

annuler = function()
{
<? if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_) { ?>
	xx({action: 'matches', id: 'main', tournoi: 1, url: 'tournament_matches.php?action=matches&page=1&idj=<?= $_REQUEST['idj'] ?>&name=<?= urlencode($_REQUEST['name']) ?>&date=<?= $_REQUEST['date'] ?>&options_type_matchs=<?= $options_type_matchs ?>'});
<? } else { ?>
	mm({action: 'matches', idj: <?= $_REQUEST['idj'] ?>, name: '<?= urlencode($_REQUEST['name']) ?>', date: '<?= $_REQUEST['date'] ?>', page: <?= $_REQUEST['page'] ?> });
<? } ?>

	return true;
}
setsets(<?= $nbset ?>);
</script>

</div>