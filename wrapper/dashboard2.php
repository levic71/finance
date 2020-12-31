<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/ManagerFXList.php";
include "../www/StatsBuilder.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$infos = JKCache::getCache("../cache/info_champ_".$sess_context->getRealChampionnatId()."_.txt", 600, "_FLUX_INFO_CHAMP_");

$sql = "SELECT * FROM jb_saisons WHERE id_champ=".$sess_context->getRealChampionnatId();
$res = dbc::execSql($sql);
$saisons = ""; while($row = mysqli_fetch_array($res)) $saisons .= ($saisons == "" ? "" : ",")."{ v: ".$row['id'].", l: '".Wrapper::stringEncode4JS($row['nom'])."', s: ".($row['id'] == $sess_context->getChampionnatId() ? "true" : "false")." }";

$nb_actions_roles = 0;
$sql = 'SELECT count(*) total FROM jb_roles WHERE id_champ='.$sess_context->getRealChampionnatId().' AND status = 0';
$res = dbc::execSql($sql);
if ($row = mysqli_fetch_array($res)) $nb_actions_roles = $row['total'];

$nb_actions_links = 0;
$sql = 'SELECT count(*) total FROM jb_user_player WHERE id_champ='.$sess_context->getRealChampionnatId().' AND status = 0';
$res = dbc::execSql($sql);
if ($row = mysqli_fetch_array($res)) $nb_actions_links = $row['total'];

?>

<div class="vgrid ">

<ul class="sidebar">
<? if ($sess_context->isAdmin()) { ?>
	<li id="settings"><a href="#" onclick="go({action: 'dashboard', id:'main', url:'edit_leagues.php?page=0&idl=<?= $sess_context->getRealChampionnatId() ?>&etape=1'});" id="sb_settings" class="ToolText" onmouseover="showtip('sb_settings');"><span>Paramètres</span></a></li>
	<li><a href="#" onclick="mm({action: 'seasons'});"                                            id="sb_saison" class="ToolText" onmouseover="showtip('sb_saison');"><span>Gestion des saisons</span></a></li>
	<li><a href="#" onclick="go({action: 'dashboard', id:'main', url:'admin_full_sync_do.php'});" id="sb_sync"   class="ToolText" onmouseover="showtip('sb_sync');"><span>Synchronisation journées</span></a></li>
	<li><a href="#" onclick="go({action: 'dashboard', id:'main', url:'admin_backup_do.php'});"    id="sb_backup" class="ToolText" onmouseover="showtip('sb_backup');"><span>Backup/Restore</span></a></li>
	<li><a href="#" onclick="go({action: 'dashboard', id:'main', url:'admin_stats_freq.php'});"   id="sb_stats"  class="ToolText" onmouseover="showtip('sb_stats');"><span>Statistiques fréquentation</span></a></li>
	<li><a href="#" onclick="mm({action: 'roles'});" id="sb_shield" class="ToolText" onmouseover="showtip('sb_shield');"><span>Droits d'administration</span><? if ($nb_actions_roles > 0) { ?><button class="button bigrounded red pucecounter"><?= $nb_actions_roles ?></button><? } ?></a></li>
	<li><a href="#" onclick="mm({action: 'links'});" id="sb_join2" class="ToolText" onmouseover="showtip('sb_join2');"><span>Rattachement joueurs</span><? if ($nb_actions_links > 0) { ?><button class="button bigrounded red pucecounter"><?= $nb_actions_links ?></button><? } ?></a></li>
<? } else { ?>
	<? if ($sess_context->isUserConnected()) { ?>
	<li class="mailme" id="mailme"><a href="#" onclick="go({action: 'mailme', id:'main', url:'contacter.php?type_mail=4'});" id="sb_join" class="ToolText" onmouseover="showtip('sb_join');"><span>Rejoindre le staff</span></a></li>
	<? } ?>
	<li class="mailme" id="mailme"><a href="#" onclick="go({action: 'mailme', id:'main', url:'contacter.php?type_mail=1'});" id="sb_mail" class="ToolText" onmouseover="showtip('sb_mail');"><span>Contacter le gérant</span></a></li>
<? } ?>
</ul>


<h2 class="grid dashboard">Dashboard - <?= $libelle_type[$sess_context->getChampionnatType()] ?><div style="float: right; margin: 8px 0px 0px;" id="saisons"></div></h2>
<script>
choices.build({ name: 'saisons', c1: 'blue', c2: 'white', callback: 'change_saison', singlepicking: true, removable: true, values: [ <?= $saisons ?> ] });
change_saison = function(name) { xx({action: 'message', id:'main', url:'table_change_season_do.php?ids='+choices.getSelection(name)}); }
</script>

<table id="dash" border="0" cellpadding="0" cellspacing="10">


<!-- <tr><td colspan="2" class="box-wrapper">
<div class="nav">
	<h3>Calendrier</h3>
	<img class="bt" style="margin-right: 3px;" src="img/icons/dark/appbar.navigate.previous.png" onclick="nav_prev('pages', 10);" />
	<ul id="pages">
	<?
	$sql = "SELECT * FROM jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." ORDER BY date, id";
	$res = dbc::execSQL($sql);
	$i = 1; while($row = mysqli_fetch_array($res)) echo "<li id=\"j".$row['id']."\"><button class=\"button ".(strtotime("now") > strtotime($row['date']) ? "blue" : "orange futur")."\" onclick=\"journee(this,".$row['id'].");\">".$i++."</button></li>";
	if ($i < 10) { while($i <= 10) echo "<li><button class=\"button disable\">".$i++."</button></li>"; }
	?>
	</ul>
	<img class="bt" style="margin-left: 3px;" src="img/icons/dark/appbar.navigate.next.png" onclick="nav_next('pages', 10);" />
</div>
</td></tr> -->

<tr valign="top">


<td style="width: 300px;" class="box-wrapper">
<h3 class="title">
	<!-- img class="bt" onclick="mm({action: 'tables'});" src="img/icons/dark/appbar.navigate.next.png" / -->
<? if ($sess_context->isFreeXDisplay()) { ?>
	<img id="more5" class="bt" onclick="toggle_all5(999);" src="img/icons/dark/appbar.add.png" />
	<img id="less5" class="bt" onclick="toggle_all5(<?= sess_context::getHomeListHeadcount() ?>);" src="img/icons/dark/appbar.minus.png" />
<? } else { ?>
	<img id="more1" class="bt" onclick="show_elts('table_teams', 999, <?= sess_context::getHomeListHeadcount() ?>, 'more1', 'less1');" src="img/icons/dark/appbar.add.png" />
	<img id="less1" class="bt" onclick="show_elts('table_teams', <?= sess_context::getHomeListHeadcount() ?>, <?= sess_context::getHomeListHeadcount() ?>, 'more1', 'less1');" src="img/icons/dark/appbar.minus.png" />
<? } ?>
    <span>Classement genéral<br /><small>&nbsp;</small></span>
</h3>

<?
$tbody = ""; $thead = "";

if ($sess_context->isFreeXDisplay()) {
	$sgb    = JKCache::getCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", 24*60*60, "_FLUX_STATS_CHAMP_");
	$fxlist = new FXListStatsJoueurs($sgb);
} else {
	$sgb = JKCache::getCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", 24*60*60, "_FLUX_STATS_CHAMP_");
	$best_teams_tournoi = $sgb->getBestTeamsByTournoiPoints();
	$best_teams_championnat = $sgb->getBestTeamsByPoints();

	if ($sess_context->isTournoiXDisplay())
		$fxlist = count($best_teams_tournoi) > 0 ? new FXListClassementGeneralTournoi($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId(), $best_teams_tournoi) : array();
	else
		$fxlist = count($best_teams_championnat) > 0 ? new FXListStatsTeamsII($best_teams_championnat) : array();
}

$tab = $fxlist && $fxlist->body ? $fxlist->body->tab : array();
if (count($tab) > 0 && $sess_context->isTournoiXDisplay()) $tab = Wrapper::reformatTournoiClassement($tab);

$th = array("nom" => "Equipe", "points" => "Pts", "diff" => "Diff");
if ($sess_context->isFreeXDisplay()) $th = array("pseudo" => "Joueur", "presence" => "R/O", "pourc_joues" => "J", "pourc_gagnes" => "G", "joues" => "J", "gagnes" => "G", "nuls" => "N", "perdus" => "P", "sets_joues" => "SJ", "sets_gagnes" => "SG", "sets_nuls" => "SN", "sets_perdus" => "SP", "sets_diff" => "SD", "forme_indice" => "F", "forme_last_indice" => "F+", "podium" => "1er", "polidor" => "2e", "moy_marquesA" => "AV+", "moy_encaissesD" => "AV-",  "diff" => "Diff", "fanny_in" => "FI", "fanny_out" => "FO");

$cols  = array("nom" => 1, "points" => 1, "diff" => 1);
if ($sess_context->isFreeXDisplay()) $cols  = array("pseudo" => 1, "pourc_joues" => 1, "pourc_gagnes" => 1);
if (true || $sess_context->getGestionSets() == 0) { unset($cols['sets_joues']); unset($cols['sets_gagnes']); unset($cols['sets_nuls']); unset($cols['sets_perdus']); unset($cols['sets_diff']); }
if ($sess_context->getGestionMatchsNul() == 0) { /* unset($cols['matchs_nuls']);*/ $item['matchs_nuls'] = "&nbsp;"; }

// Empty Line
$j = 2; $empty_line = "";
while (list($cle, $val) = each($cols)) {
	$c = "-";
	if ($cle == "pourc_joues" || $cle == "pourc_gagnes" || $cle == "points"|| $cle == "diff") $c = "<button class=\"button bigrounded disable\">0</button>";
	$empty_line .= "<td class=\"c".$j++."\"><div>".$c."</div></td>";
}

if ($sess_context->isFreeXDisplay()) {
	$nbr = 0; $nbo = 0;
	if (count($tab) > 0) {
		foreach($tab as $item) {
			if ($item == _FXSEPARATORWITHINIT_) continue;
			if ($item['presence'] == 0) $nbo++; else if ($item['presence'] == 1) $nbr++;
		}
	}
	if ($nbr < sess_context::getHomeListHeadcount())
	{
		$empty_row = array('id' => 0, 'forme_indice' => '', 'pseudo' => 'z', 'presence' => 1); reset($cols);
		while (list($cle, $val) = each($cols)) $empty_row[$cle] = isset($empty_row[$cle]) ? $empty_row[$cle] : "";
		for($x=0; $x < (sess_context::getHomeListHeadcount()-$nbr); $x++) $tab[] = $empty_row;
	}

	if ($nbo < sess_context::getHomeListHeadcount())
	{
		$empty_row = array('id' => 0, 'forme_indice' => '', 'pseudo' => 'z', 'presence' => 0); reset($cols);
		while (list($cle, $val) = each($cols)) $empty_row[$cle] = isset($empty_row[$cle]) ? $empty_row[$cle] : "";
		for($x=0; $x < (sess_context::getHomeListHeadcount()-$nbo); $x++) $tab[] = $empty_row;
	}

	$tri1 = array(); $tri2 = array();
	foreach($tab as $item) {
		$tri1[] = $item['pseudo'];
		$tri2[] = $item['presence'];
	}
	array_multisort($tri2, SORT_DESC, $tri1, SORT_ASC, $tab);
} else {
	$nb = 0;
	if (count($tab) > 0) {
		foreach($tab as $item) {
			if ($item == _FXSEPARATORWITHINIT_) continue;
			$nb++;
		}
	}
	$empty_row = array('id' => 0); reset($cols);
	while (list($cle, $val) = each($cols)) $empty_row[$cle] = isset($empty_row[$cle]) ? $empty_row[$cle] : "";
	for($x=0; $x < (sess_context::getHomeListHeadcount()-$nb); $x++) $tab[] = $empty_row;
}

$i = 1; $nb_regular = 1; $nb_occaz = 1;

foreach($tab as $item)
{
	if ($item == _FXSEPARATORWITHINIT_) continue;

	$tmp = '<tr>';

	$empty = $item['id'] == 0 ? true : false;
	$medaille = "";
	$presence = true;
	if ($sess_context->isFreeXDisplay()) {
		if (isset($item['medaille'])) $medaille = Wrapper::getColorMedaille($item['medaille']);
		$presence = $item['presence'] == 1 ? true : false;
		$item['forme_indice'] = $empty || $item['joues'] == 0 ? 0 : Wrapper::extractFormeIndice($item['forme_indice']);
		$item['forme_indice'] = $empty ? '' : '<div class="forme bigrounded '.Wrapper::getColorFromFormeIndice($item['forme_indice']).'"><div class="formeind bf'.$item['forme_indice'].'" title="Forme actuelle"></div></div>';
	}

	$onclick = "onclick=\"mm({action:'stats', ".($sess_context->isFreeXDisplay() ? "idp" : "idt").":'".$item['id']."'});\"";
	$indice = $sess_context->isFreeXDisplay() && !$presence ? $nb_occaz : $i;
	$tbody .= "<tr class=\"clickonit ".($presence ? "regular" : "occaz")." ".$medaille."\" id=\"tr_".$indice."\" ".$onclick."  >";

	if (!isset($num) || (isset($num) && $num))
	{
		$tmp .= '<th class="c1"><div>&nbsp;</div></th>';
		$tbody .= '<td class="c1"><div>'.$indice.'</div></td>';
	}

	$j=2;
	reset($cols);
	while (list($cle, $val) = each($cols))
	{
		if ($sess_context->isFreeXDisplay()) {
			if ($cle == 'pourc_joues' || $cle == 'pourc_gagnes') $item[$cle] = preg_replace("/<.*>/", "", preg_replace("/<\/.*>/", "", $item[$cle]));
			if ($cle == 'pourc_joues') $item[$cle] = "<button class=\"button bigrounded ".($empty ? "disable" : ($item[$cle] > 50 ? "gray" : "white"))."\">".($empty ? 0 : preg_replace("/[\. ].*\%/", "%", $item[$cle]))."</button>";
			if ($cle == 'pourc_gagnes') $item[$cle] = "<button class=\"button bigrounded ".($empty ? "disable" : ($item[$cle] > 50 ? "green" : "red"))."\">".($empty ? 0 : preg_replace("/[\. ].*\%/", "%", $item[$cle]))."</button>";
			if ($cle == 'presence') $item[$cle] = $item[$cle] == 1 ? "R" : "O";
//			if ($cle == 'pseudo') $item[$cle] = "<a href=\"#\" onclick=\"mm({action:'stats', idp:'".$item['id']."'});\">".($empty ? "" : $item[$cle])."</a>";
			if ($cle == 'pseudo') $item[$cle] = ($empty ? "" : $item['forme_indice'].$item[$cle]);
		} else {
			if ($cle == 'nom') $item[$cle] = preg_replace("/<.*>/", "", str_replace("</A>", "", $item[$cle]));
//			if ($cle == 'nom') $item[$cle] = "<a href=\"#\" onclick=\"mm({action:'stats', idt:'".$item['id']."'});\">".$item[$cle]."</a>";
			if ($cle == 'points' || $cle == 'diff') $item[$cle] = "<button class=\"button bigrounded ".($empty ? "disable" : ($cle == 'points' ? "gray" : ($item[$cle] >= 0 ? "green" : "red")))."\">".($empty ? 0 : preg_replace("/[\. ].*\%/", "%", $item[$cle]))."</button>";
		}

		$tmp .= '<th class="c'.$j.'"><div>'.(isset($th[$cle]) ? $th[$cle] : $cle).'</div></th>';
		$tbody .= '<td class="c'.$j.'"><div>'.($item[$cle] == "" ? ($cle != "nom" && $cle != "pseudo" ? "0" : "&nbsp;") : $item[$cle]).'</div></td>';
		$j++;
	}

	$tmp .= '</tr>'; $tbody .= '</tr>';
	if ($i == 1) $thead .= $tmp;

	if ($presence) $nb_regular++; else $nb_occaz++;
	$i++;
}



?>
<table cellspacing="0" cellpadding="0" class="jkgrid" id="<?= $sess_context->isFreeXDisplay() ? "table_players" : "table_teams" ?>">
<thead><?= $thead ?></thead>
<tbody><?= $tbody ?></tbody>
</table>

<? if ($sess_context->isFreeXDisplay()) { ?>
<div id="occazbox" class="noradius underline" style="float: right; margin: 5px 0px;"></div>
<? } else { ?>
<div id="sizebox" class="noradius underline" style="float: right; margin: 5px 0px;"></div>
<? } ?>

</td>

<td id="dashjournee" style="width: 360px;" class="box-wrapper"></td>

</tr>


<tr>
<td colspan="2" class="dashcounter box-wrapper">
<button id="b1" class="button blue"><div class="box"><div class="cnt"><?= Wrapper::formatNumber($infos['nb_saisons'])  ?></div><div class="txt">Saisons</div></div></button>
<button id="b2" class="button blue" onclick="mm({action: 'players'});"><div class="box"><div class="cnt"><?= Wrapper::formatNumber($infos['nb_joueurs'])  ?></div><div class="txt">Joueurs</div></div></button>
<button id="b3" class="button blue" onclick="mm({action: 'teams'});"><div class="box"><div class="cnt"><?= Wrapper::formatNumber($infos['nb_equipes'])  ?></div class="box"><div class="txt">Equipes</div></div></button>
<button id="b4" class="button blue" onclick="mm({action: 'days', grid: -1, tournoi: <?= $sess_context->isTournoiXDisplay() ? 1 : 0 ?>});"><div class="box"><div class="cnt"><?= Wrapper::formatNumber($infos['nb_journees']) ?></div><div class="txt">Journées</div></div></button>
<button id="b5" class="button blue"><div class="box"><div class="cnt"><?= Wrapper::formatNumber($infos['nb_matchs']) ?></div><div class="txt">Matchs</div></div></button>
</td>
</tr>

<? if ($sess_context->championnat['twitter'] != "") { ?>
<tr>
<td colspan="2" class="dashcounter box-wrapper" id="twitter_box">
</td>
</tr>
<? } ?>


</table>

<div>
</div>

</div>

<script>

<? if ($sess_context->isFreeXDisplay()) { ?>

choices.build({ name: 'occazbox', c1: 'small selected', c2: 'small', callback: 'occaz_call', values: [ { v: 0, l: 'Occassionels', s: false }, { v: 1, l: 'Réguliers', s: true } ] });

var compact = <?= sess_context::getHomeListHeadcount() ?>;

toggle_all5 = function(nb) {
	compact = nb;
	var option = choices.getSelection('occazbox') == 1 ? 'regular' : 'occaz';
	show_occaz('table_players', option, compact, <?= sess_context::getHomeListHeadcount() ?>, 'more5', 'less5');
}

occaz_call = function (name) {
	hide(choices.getSelection(name) == 1 ? 'more52' : 'more51'); hide(choices.getSelection(name) == 1 ? 'less52' : 'less51');
	show_occaz('table_players', choices.getSelection(name) == 1 ? 'regular' : 'occaz', compact, <?= sess_context::getHomeListHeadcount() ?>, 'more5', 'less5');
}

show_occaz('table_players', 'regular', <?= sess_context::getHomeListHeadcount() ?>, <?= sess_context::getHomeListHeadcount() ?>, 'more5', 'less5');

<? } else { ?>

show_elts('table_teams', <?= sess_context::getHomeListHeadcount() ?>, <?= sess_context::getHomeListHeadcount() ?>, 'more1', 'less1');

<? } ?>

nav_init('pages', 'j<?= Wrapper::getLastJourneePlayed($sess_context->getChampionnatId()) ?>', 10);

</script>
