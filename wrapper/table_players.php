<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/ManagerFXList.php";
include "../www/StatsBuilder.php";

header('Content-Type: text/html; charset='.sess_context::charset);

$db = dbc::connect();

if (!isset($choix_stat)) $choix_stat = 0;

//$sgb = new StatsGlobalBuilder($sess_context->getChampionnatId(), $sess_context->getChampionnatType());
$sgb           = JKCache::getCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", 24*60*60, "_FLUX_STATS_CHAMP_");
$best_teams    = $sgb->getBestTeams();
$most_matchs   = $sgb->getMostTeams();

$tbody = ""; $thead = "";

// En cas de besoin si pagination demand?e !!!
$delta = 10;
$page  = Wrapper::getRequest('page', 0);

$t = array();
if ($choix_stat != 0) array_push($t, array("id" => "sb_best", "onclick" => "xx({action: 'tables', id:'main', url:'table_players.php?choix_stat=0'});", "tooltip" => "Classement général"));
if ($choix_stat != 6) array_push($t, array("id" => "sb_besta", "onclick" => "xx({action: 'tables', id:'main', url:'table_players.php?choix_stat=6'});", "tooltip" => "Meilleurs attaquants"));
if ($choix_stat != 7) array_push($t, array("id" => "sb_bestd", "onclick" => "xx({action: 'tables', id:'main', url:'table_players.php?choix_stat=7'});", "tooltip" => "Meilleurs défenseurs"));
if ($choix_stat != 4) array_push($t, array("id" => "sb_bestea", "onclick" => "xx({action: 'tables', id:'main', url:'table_players.php?choix_stat=4'});", "tooltip" => "Meilleures attaques"));
if ($choix_stat != 5) array_push($t, array("id" => "sb_bested", "onclick" => "xx({action: 'tables', id:'main', url:'table_players.php?choix_stat=('});", "tooltip" => "Meilleures défenses"));
if ($sess_context->championnat['entity'] == "_NATIF_") array_push($t, array("id" => "sb_fb",
    "onclick" => "google_tracking('facebook.com');",
    "target" => "_blank",
    "href" => Wrapper::fb_tag("Accès au classement", "http://www.jorkers.com/wrapper/fb.php?idc=".$sess_context->getRealChampionnatId()) ,
    "tooltip" => "Publier sur Facebook"));

Wrapper::fab_button_menu($t);

Wrapper::template_box_start(12);

?> <div class="vgrid <?= $sess_context->getGestionSets() == 1 ? "" : "nosets" ?>" id="box"> <?

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU SYNTHESE JOUEURS
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 0)
{
	Wrapper::template_box_title("Classement joueurs");
	// <h2 class="grid leagues">Classement joueurs</h2>

	$fxlist = new FXListStatsJoueurs($sgb);
	$fxlist->FXSetFooter($sgb->getNbMatchs()." matchs joués dans le championnat sur ".$sgb->getNbJournees()." journées (".sprintf("%.2f", $sgb->getMoyMatchsJoues())." matchs/journ?e)");

	$th = array("pseudo" => "Joueur", "presence" => "R/O", "pourc_joues" => "J", "pourc_gagnes" => "G", "joues" => "J", "gagnes" => "G", "nuls" => "N", "perdus" => "P", "sets_joues" => "SJ", "sets_gagnes" => "SG", "sets_nuls" => "SN", "sets_perdus" => "SP", "sets_diff" => "SD", "forme_indice" => "F", "forme_last_indice" => "F+", "podium" => "1er", "polidor" => "2e", "moy_marquesA" => "AV+", "moy_encaissesD" => "AV-",  "diff" => "Diff", "fanny_in" => "FI", "fanny_out" => "FO");
	$cols  = array("pseudo" => 1, "sets_joues" => 1, "sets_gagnes" => 1, "sets_nuls" => 1, "sets_perdus" => 1, "sets_diff" => 1, "forme_indice" => 1, "forme_last_indice" => 1, "podium" => 1, "polidor" => 1, "moy_marquesA" => 1, "moy_encaissesD" => 1, "fanny_in" => 1, "fanny_out" => 1, "pourc_joues" => 1, "pourc_gagnes" => 1);
	if (true || $sess_context->getGestionSets() == 0) { unset($cols['sets_joues']); unset($cols['sets_gagnes']); unset($cols['sets_nuls']); unset($cols['sets_perdus']); unset($cols['sets_diff']); }
	if ($sess_context->getGestionMatchsNul() == 0) { /* unset($cols['matchs_nuls']);*/ $item['matchs_nuls'] = "&nbsp;"; }

	$tab = $fxlist && $fxlist->body ? $fxlist->body->tab : array();

	$nbr = 0; $nbo = 0;
	foreach($tab as $item) {
		if ($item == _FXSEPARATORWITHINIT_) continue;
		if ($item['presence'] == 0) $nbo++; else if ($item['presence'] == 1) $nbr++;
	}

	if ($nbr < 10)
	{
		$empty_row = array('id' => 0, 'pseudo' => 'z', 'presence' => 1); reset($cols);
		foreach($cols as $cle => $val) $empty_row[$cle] = isset($empty_row[$cle]) ? $empty_row[$cle] : "-";
		for($x=0; $x < (10-$nbr); $x++) $tab[] = $empty_row;
	}

	if ($nbo < 10)
	{
		$empty_row = array('id' => 0, 'pseudo' => 'z', 'presence' => 0); reset($cols);
		foreach($cols as $cle => $val) $empty_row[$cle] = isset($empty_row[$cle]) ? $empty_row[$cle] : "-";
		for($x=0; $x < (10-$nbo); $x++) $tab[] = $empty_row;
	}

	$tri1 = array(); $tri2 = array();
	foreach($tab as $item) {
		$tri1[] = $item['pseudo'];
		$tri2[] = $item['presence'];
	}
	array_multisort($tri2, SORT_DESC, $tri1, SORT_ASC, $tab);

	$i = 1; $nb_regular = 1; $nb_occaz = 1;
	foreach($tab as $item)
	{
		if ($item == _FXSEPARATORWITHINIT_) continue;

		$empty = $item['id'] == 0 ? true : false;
		$presence = $item['presence'] == 1 ? true : false;
		$medaille = isset($item['medaille']) ? Wrapper::getColorMedaille($item['medaille']) : "";

		$item['forme_indice']      = $empty || $item['joues'] == 0 ? 0 : Wrapper::extractFormeIndice($item['forme_indice']);
		$item['forme_last_indice'] = $empty || $item['joues'] == 0 ? 0 : Wrapper::extractFormeIndice($item['forme_last_indice']);

		$item['forme_indice']      = $empty ? '' : '<div class="bigrounded '.Wrapper::getColorFromFormeIndice($item['forme_indice']).'"><div class="formeind bf'.$item['forme_indice'].'" title="Forme actuelle"></div></div>';
		$item['forme_last_indice'] = $empty ? '' : '<div class="bigrounded '.Wrapper::getColorFromFormeIndice($item['forme_last_indice']).'"><div class="formeind bf'.$item['forme_last_indice'].'" title="Forme derni?re journ?e"></div></div>';

		$indice = $sess_context->isFreeXDisplay() && !$presence ? $nb_occaz : $i;
		$tmp = '<tr>'; $tbody .= "<tr id=\"tr_".$indice."\" class=\"".($presence ? "regular" : "occaz")." ".$medaille ."\" ".($empty ? "" : "onclick=\"mm({action:'stats', idp:'".$item['id']."'});\"").">";
		if (!isset($num) || (isset($num) && $num))
		{
			$tmp .= '<th class="c1"><div>N°</div></th>';
			$tbody .= '<td class="c1"><div>'.$indice.'</div></td>';
		}

		$j=2;
		reset($cols);
		foreach($cols as $cle => $val)
		{
			$item[$cle] = preg_replace("/..\/images/", "img", $item[$cle]);
			if ($cle == 'pourc_joues' || $cle == 'pourc_gagnes') $item[$cle] = preg_replace("/<.*>/", "", preg_replace("/<\/.*>/", "", $item[$cle]));
			if ($cle == 'pourc_joues' || $cle == 'pourc_gagnes') $item[$cle] = preg_replace("/[\. ].*\%/", "%", $item[$cle]);
			if ($cle == 'presence') $item[$cle] = $item[$cle] == 1 ? "R" : "O";

			if ($cle == 'pourc_joues') $item[$cle] = "<button class=\"button bigrounded ".($empty ? "disable" : "orange")."\">".($empty ? 0 : $item[$cle])."</button>";
			if ($cle == 'pourc_gagnes') $item[$cle] = "<button class=\"button bigrounded ".($empty ? "disable" : (intval(str_replace('%', '', $item[$cle])) > 50 ? "green" : "red"))."\">".($empty ? 0 : $item[$cle])."</button>";

			if ($cle == 'pseudo') $item[$cle] = "<a href=\"#\" onclick=\"mm({action:'stats', idp:'".$item['id']."'});\">".($empty ? "" : $item[$cle])."</a>";
			$tmp .= '<th class="c'.$j.'"><div>'.(isset($th[$cle]) ? $th[$cle] : $cle).'</div></th>';
			$tbody .= '<td class="c'.$j.'"><div>'.($item[$cle]."" == "-" ? ($cle != "nom" ? "" : "&nbsp;") : $item[$cle]).'</div></td>';
			$j++;
		}

		$tmp .= '</tr>'; $tbody .= '</tr>';
		if ($i == 1) $thead .= $tmp;
		if ($presence) $nb_regular++; else $nb_occaz++;
		$i++;
	}
?>

<table cellspacing="0" cellpadding="0" class="jkgrid" id="table_players">
<thead><?= $thead ?></thead>
<tbody><?= $tbody ?></tbody>
</table>

<div id="occazbox" class="noradius underline" style="float: right; margin: 5px 0px; padding: 5px 15px;"></div>

<script>
choices.build({ name: 'occazbox', c1: 'small selected', c2: 'small', callback: 'occaz_call', values: [ { v: 0, l: 'Occassionels', s: false }, { v: 1, l: 'Réguliers', s: true } ] });

occaz_call = function (name) { show_occaz('table_players', choices.getSelection(name) == 1 ? 'regular' : 'occaz', 999, 999, '', '' ); }
show_occaz('table_players', 'regular', 999, 999, '', '');
</script>

<?

}

// /////////////////////////////////////////////////////////////////////////////////////////////
// MEILLEURES ATTAQUES
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 4)
{
?>
<div class="bartable">
<h2 class="grid leagues">Meilleures Attaques - Nombre de buts moyen marqués par match</h2>
<?
	$best = $sgb->getBestAttaques();

	$max_attaque = 0;
	foreach($best as $p)
		if ($p->stat_attaque > $max_attaque) $max_attaque = $p->stat_attaque;

	$values = "";
	$k = 1;
	$tot = count($best);
	$delta = $tot > 8 ? 3 : 2;
	reset($best);
	foreach($best as $st)
		$values .= ($values == "" ? "" : ",")."{ plus: '<div>[".$st->matchs_joues." matchs]</div> <div>".$st->stat_attaque."</div>', c: '".($k <= $delta ? "green" : ($k > ($tot - $delta) ? "orange" : "blue"))."', o: ".$k++.", p: ".round((($st->stat_attaque * 100) / $max_attaque)).", l : '".Wrapper::stringEncode4JS($st->nom)."'}";

	for($x=1; $x <= (10 - $tot); $x++)
		$values .= ($values == "" ? "" : ",")."{ plus: '<div>[0 matchs]</div> <div>0</div>', c: 'lightgray', o: ".($tot+$x).", p: 0, l : ''}";
?>
<ul id="bestatt" class="best" style="margin: 0px; padding: 0px;"></ul>
</div>
<script>
bars.build({ name :'bestatt', tsize: 130, rsize: 267, msize: 190, values: [<?= $values ?>] });
</script>
<?
}

// /////////////////////////////////////////////////////////////////////////////////////////////
// MEILLEURES DEFENSES
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 5)
{
?>
<div class="bartable">
<h2 class="grid leagues">Meilleures Défenses - Nombre de buts moyen encaissés par match</h2>
<?
	$best = $sgb->getBestDefenses();

	$max_defense = 0;
	foreach($best as $p)
		if ($p->stat_defense > $max_defense) $max_defense = $p->stat_defense;

	$values = "";
	$k = 1;
	$tot = count($best);
	$delta = $tot > 8 ? 3 : 2;
	reset($best);
	foreach($best as $st)
		$values .= ($values == "" ? "" : ",")."{ plus: '<div>[".$st->matchs_joues." matchs]</div> <div>".$st->stat_defense."</div>', c: '".($k <= $delta ? "green" : ($k > ($tot - $delta) ? "orange" : "blue"))."', o: ".$k++.", p: ".round((($st->stat_defense * 100) / $max_defense)).", l : '".Wrapper::stringEncode4JS($st->nom)."'}";

	for($x=1; $x <= (10 - $tot); $x++)
		$values .= ($values == "" ? "" : ",")."{ plus: '<div>[0 matchs]</div> <div>0</div>', c: 'lightgray', o: ".($tot+$x).", p: 0, l : ''}";
?>
<ul id="bestatt" class="best" style="margin: 0px; padding: 0px;"></ul>
</div>
<script>
bars.build({ name :'bestatt', tsize: 130, rsize: 267, msize: 190, values: [<?= $values ?>] });
</script>
<?
}

// /////////////////////////////////////////////////////////////////////////////////////////////
// MEILLEURS ATTAQUANTS
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 6)
{
?>
<div class="bartable">
<h2 class="grid leagues">Meilleurs Attaquants - Nombre de buts moyen marqués par match</h2>
<?
	$best = $sgb->getBestJoueursAttaquants();

	$max_attaque = 0;
	foreach($best as $p)
		if ($p->moy_marquesA > $max_attaque) $max_attaque = $p->moy_marquesA;

	$values = "";
	$k = 1;
	$tot = count($best);
	$delta = $tot > 8 ? 3 : 2;
	reset($best);
	foreach($best as $st)
	$values .= ($values == "" ? "" : ",")."{ plus: '<div>[".$st->marquesA." buts]</div> <div>".$st->moy_marquesA."</div>', c: '".($k <= $delta ? "green" : ($k > ($tot - $delta) ? "orange" : "blue"))."', o: ".$k++.", p: ".($max_attaque == 0 ? 0 : round((($st->moy_marquesA * 100) / $max_attaque))).", l : '".Wrapper::stringEncode4JS($st->pseudo)."'}";

	for($x=1; $x <= (10 - $tot); $x++)
		$values .= ($values == "" ? "" : ",")."{ plus: '<div>[0 matchs]</div> <div>0</div>', c: 'lightgray', o: ".($tot+$x).", p: 0, l : ''}";
?>
<ul id="bestatt" class="best" style="margin: 0px; padding: 0px;"></ul>
</div>
<script>
bars.build({ name :'bestatt', tsize: 130, rsize: 267, msize: 190, values: [<?= $values ?>] });
</script>
<?
}

// /////////////////////////////////////////////////////////////////////////////////////////////
// MEILLEURS DEFENSEURS
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 7)
{
?>
<div class="bartable">
<h2 class="grid leagues">Meilleurs Défenseurs - Nombre de buts moyen encaissés par match</h2>
<?
	$best = $sgb->getBestJoueursDefenses();

	$max_defense = 0;
	foreach($best as $p)
		if ($p->moy_encaissesD > $max_defense) $max_defense = $p->moy_encaissesD;

	$values = "";
	$k = 1;
	$tot = count($best);
	$delta = $tot > 8 ? 3 : 2;
	reset($best);
	foreach($best as $st)
		$values .= ($values == "" ? "" : ",")."{ plus: '<div>[".$st->encaissesD." buts]</div> <div>".$st->moy_encaissesD."</div>', c: '".($k <= $delta ? "green" : ($k > ($tot - $delta) ? "orange" : "blue"))."',o: ".$k++.", p: ".round((($st->moy_encaissesD * 100) / $max_defense)).", l : '".Wrapper::stringEncode4JS($st->pseudo)."'}";

	for($x=1; $x <= (10 - $tot); $x++)
		$values .= ($values == "" ? "" : ",")."{ plus: '<div>[0 matchs]</div> <div>0</div>', c: 'lightgray', o: ".($tot+$x).", p: 0, l : ''}";
?>
<ul id="bestdef" class="best" style="margin: 0px; padding: 0px;"></ul>
</div>
<script>
bars.build({ name :'bestdef', tsize: 130, rsize: 267, msize: 190, values: [<?= $values ?>] });
</script>
<?
}

?>

</div>

<? Wrapper::template_box_end(); ?>
