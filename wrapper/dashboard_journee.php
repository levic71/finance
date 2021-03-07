<?

require_once "../include/sess_context.php";

session_start();

require_once "common.php";
require_once "../include/inc_db.php";
require_once "../www/ManagerFXList.php";
require_once "../www/StatsBuilder.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$id_journee  = isset($_REQUEST['id_journee']) ? $_REQUEST['id_journee'] : 0;

$real_id    = $sess_context->getRealChampionnatId();
$type_champ = $sess_context->getChampionnatType();
$saison_id  = $sess_context->getChampionnatId();

if (strstr($id_journee, '|')) {
    $j = Wrapper::getPrevNextJournees($id_journee);
    $id_journee = $j == 0 ? 0 : $j['id'];
}

if ($id_journee == 0) $id_journee = Wrapper::getLastJourneePlayed($sess_context->getChampionnatId());
//if (!isset($id_journee) || $id_journee == "0") { echo "<div style=\"color: #666; margin: 40px 0px 0px 80px;\">Aucune journée</div>"; exit(0); }

// On récupère les infos de la journée
$select = "SELECT * FROM jb_journees WHERE id=".$id_journee;
$res = dbc::execSQL($select);
$journee = mysqli_fetch_array($res);

if (!$journee) $journee = array("id" => "0", "date" => date("Y-m-d"), "nom" => "Pas de journée", "tournoi_phase_finale" => "X|0");
if (!isset($options_type_matchs)) $options_type_matchs = "X|0";

?>




<? if (!(!isset($id_journee) || $id_journee == "0") && false) { ?>
	<img class="bt" onclick="mm({action:'matches', tournoi: <?= $sess_context->isTournoiXDisplay() ? 1 : 0 ?>, idj:'<?= $journee['id'] ?>', name:'<?= $journee['nom'] ?>', date:'<?= ToolBox::mysqldate2date($journee['date']) ?>'});" src="img/icons/dark/appbar.magnify.png" />
<? } ?>



<?

$bottom = "";

if ($sess_context->isTournoiXDisplay()) {
	$bottom .= Wrapper::card_box_getIconButton(array("id" => "more21", "icon" => "arrow_drop_down_circle", "label" => "Afficher plus",  "onclick" => "toggle_all2(999);" ));
//	$bottom .= Wrapper::card_box_getIconButton(array("id" => "less21", "icon" => "unfold_less", "label" => "Afficher moins", "onclick" => "toggle_all2(".sess_context::getHomeListHeadcount().");" ));
} else if ($sess_context->isFreeXDisplay()) {
	$bottom .= Wrapper::card_box_getIconButton(array("id" => "more32", "icon" => "arrow_drop_down_circle", "label" => "Afficher plus",  "onclick" => "toggle_all(999);" ));
//	$bottom .= Wrapper::card_box_getIconButton(array("id" => "less32", "icon" => "unfold_less", "label" => "Afficher moins", "onclick" => "toggle_all(".sess_context::getHomeListHeadcount().");" ));
} else {
	$bottom .= Wrapper::card_box_getIconButton(array("id" => "btexpand",  "icon" => "arrow_drop_down_circle",  "label" => "Afficher plus", "onclick" => "moreorless('btexpand');" ));
//	$bottom .= Wrapper::card_box_getIconButton(array("id" => "btompress", "icon" => "unfold_less", "label" => "Afficher moins", "onclick" => "show_elts('matches',".sess_context::getHomeListHeadcount().");"));
}



$content  = '';
$content .= '<table id="matches" cellspacing="0" cellpadding="0" class="jkgrid99 '.($sess_context->isTournoiXDisplay() ? "phasefinale" : "").'type_'.$type_champ.'" style="width: 100%;">';
$content .= '<thead><tr>';
if ($sess_context->isTournoiXDisplay())
	$content .= '<th class="c1"><div>&nbsp;</div></th><th class="c2"><div>Matchs</div></th><th class="c3"><div>&nbsp</div></th>';
else
	$content .= '<th class="c2"><div>Locaux</div></th><th class="c3"><div>&nbsp</div></th><th class="c4"><div>Visiteurs</div></th>';
$content .= '</tr></thead><tbody>';

if ($sess_context->isTournoiXDisplay())
	$content .= journee_tournoi_display($journee, $saison_id, $real_id, $options_type_matchs);
else
	$content .= journee_display($journee, $saison_id, "");

$content .= '</tbody></table>';

?>




<? if ($sess_context->isFreeXDisplay()) {
	$res = Wrapper::getDisplayMatchesStats($journee['id'], true, false);
	$content .= $res;
	$bottom  .= '<div id="classement" class="underline" style="margin: 5px 10px;"></div>';
?>

<script>
choices.build({ name: 'classement', c1: 'small selected', c2: 'small', callback: 'swap_box', values: [ { v: 0, l: 'Classement', s: false }, { v: 1, l: 'Matchs', s: true }] });

var compact3 = <?= sess_context::getHomeListHeadcount() ?>;

swap_box = function(name) {
	hide(choices.getSelection(name) == 1 ? 'table_players_day' : 'matches'); hide(choices.getSelection(name) == 1 ? 'more31' : 'more32'); hide(choices.getSelection(name) == 1 ? 'less31' : 'less32');
	show(choices.getSelection(name) == 1 ? 'matches' : 'table_players_day'); show(choices.getSelection(name) == 1 ? 'more32' : 'more31'); show(choices.getSelection(name) == 1 ? 'less32' : 'less31');
	show_elts(choices.getSelection(name) == 1 ? 'matches' : 'table_players_day', compact3, <?= sess_context::getHomeListHeadcount() ?>, choices.getSelection(name) == 1 ? 'more32' : 'more31', choices.getSelection(name) == 1 ? 'less32' : 'less31');
}

toggle_all = function(nb) {
	compact3 = nb;
	show_elts('table_players_day', compact3, <?= sess_context::getHomeListHeadcount() ?>, 'more31', 'less31');
	show_elts('matches', compact3, <?= sess_context::getHomeListHeadcount() ?>, 'more32', 'less32');
	hide(choices.getSelection('classement') == 1 ? 'more31' : 'more32');
	hide(choices.getSelection('classement') == 1 ? 'less31' : 'less32');
}

hide('table_players_day'); hide('more31'); hide('less31'); hide('more32'); hide('less32');
show_elts('matches', <?= sess_context::getHomeListHeadcount() ?>, <?= sess_context::getHomeListHeadcount() ?>, 'more32', 'less32');

</script>
<? } ?>




<? if ($sess_context->isChampionnatXDisplay()) { ?>
<script>
moreorless = function(n) {
	if (n == 'btexpand')
		show_elts('matches', 999);
	else
		show_elts('matches', <?= sess_context::getHomeListHeadcount() ?>, <?= sess_context::getHomeListHeadcount() ?>, 'more4', 'less4');
	hide(n);
}
moreorless(0);
</script>
<? } ?>




<?

if ($sess_context->isTournoiXDisplay()) {
	
	$content .= "<table id=\"classementjournee\" cellspacing=\"0\" cellpadding=\"0\" class=\"jkgrid99 matchespoules\" style=\"width: 100%;\">";
	$content .= journee_tournoi_classement($journee, $saison_id, $real_id, $sess_context->getGestionMatchsNul());
	$content .= "</table>";
	$bottom .= "<div id=\"classement\" class=\"underline\" style=\"margin: 5px 0px;\"></div>";

?>

<script>
choices.build({ name: 'classement', c1: 'small selected', c2: 'small', callback: 'swap_box2', values: [ { v: 0, l: 'Classement', s: false }, { v: 1, l: 'Phase finale', s: true } ] });

var compact2 = <?= sess_context::getHomeListHeadcount() ?>;

swap_box2 = function(name) {
	hide(choices.getSelection(name) == 1 ? 'classementjournee' : 'matches'); hide(choices.getSelection(name) == 1 ? 'more21' : 'more22'); hide(choices.getSelection(name) == 1 ? 'less21' : 'less22');
	show(choices.getSelection(name) == 1 ? 'matches' : 'classementjournee'); show(choices.getSelection(name) == 1 ? 'more22' : 'more21'); show(choices.getSelection(name) == 1 ? 'less22' : 'less21');
	show_elts(choices.getSelection(name) == 1 ? 'matches' : 'classementjournee', compact2, <?= sess_context::getHomeListHeadcount() ?>, choices.getSelection(name) == 1 ? 'more22' : 'more21', choices.getSelection(name) == 1 ? 'less22' : 'less21');

}

toggle_all2 = function(nb) {
	compact2 = nb;
	show_elts('classementjournee', compact2, <?= sess_context::getHomeListHeadcount() ?>, 'more21', 'less21');
	show_elts('matches', compact2, <?= sess_context::getHomeListHeadcount() ?>, 'more22', 'less22');
	hide(choices.getSelection('classement') == 1 ? 'more21' : 'more22');
	hide(choices.getSelection('classement') == 1 ? 'less21' : 'less22');
}

hide('classementjournee'); hide('more21'); hide('less21'); hide('more22'); hide('less22');
show_elts('matches', <?= sess_context::getHomeListHeadcount() ?>, <?= sess_context::getHomeListHeadcount() ?>, 'more22', 'less22');
</script>

<? } ?>



<?

$title   = Wrapper::card_box_getH2Title(array("title" => "<span>".ToolBox::conv_lib_journee($journee['nom'])."<br /><small>".ToolBox::mysqldate2date($journee['date'])."</small></span>"));
$menu    = Wrapper::card_box_getIconButton(array("id" => "btprev", "icon" => "navigate_before", "label" => "Journée précédente", "onclick" => "journee(null, '".$id_journee."|prev');" ));
$menu   .= Wrapper::card_box_getIconButton(array("id" => "btnext", "icon" => "navigate_next",   "label" => "Journée suivante",   "onclick" => "journee(null, '".$id_journee."|next');" ));
Wrapper::card_box_frameless(array("id" => "card_sante", "bottom_text" => $bottom, "title" => $title, "menu" => $menu, "content" => $content));

?>


<?

function journee_tournoi_classement($journee, $saison_id, $champ_id, $matchs_nul)
{
	$res  = "<thead><tr><th class=\"c1\"><div>&nbsp;</div></th><th class=\"c2\"><div>Equipe</div></th><th class=\"c3\"><div>Pts</div></th><th class=\"c4\"><div>Diff</div></th></tr></thead>";
	$res .= "<tbody>";

	$fxlist = new FXListClassementJourneeTournoi($champ_id, $saison_id, $journee['id']);
	$i = 1; foreach($fxlist->body->tab as $item) {
		if ($item != _FXLINESEPARATOR_) {
//			if ($i > 10) break;
			$tmp = explode('-', preg_replace("/\" class=\"blue\">/", "-", preg_replace("/ onmouse.*/", "", preg_replace("/<.*id_detail=/", "", str_replace("</A>", "", $item[1])))));
			$idt = isset($tmp[0]) ? $tmp[0] : 0;
			$eq = isset($tmp[1]) ? $tmp[1] : '-';
			if ($idt != '' && $idt != 0) {

				$res .= "<tr id=\"tr_".$i."\" class=\"clickonit\" onclick=\"mm({action:'stats', idt:'".$idt."'});\">";
				$res .= "<td class=\"c1\"><div>".$i++."</div></td>";
				$res .= "<td class=\"c2\"><div>".$eq."</div></td>";
				$res .= "<td class=\"c3\"><div><button class=\"button bigrounded gray\">".$item['2']."</button></div></td>";
				$res .= "<td class=\"c4\"><div><button class=\"button bigrounded ".(($matchs_nul ? $item['9'] : $item['12']) >= 0 ? "green" : "red")."\">".($matchs_nul ? $item['9'] : $item['12'])."</button></div></td>";
				$res .= "</tr>";
			}
		}
	}

	for($x = $i; $x <= sess_context::getHomeListHeadcount(); $x++)
		$res .= "<tr id=\"tr_".$x."\"><td class=\"c1\"><div>".$x."</div></td><td class=\"c2\"><div>-</div></td><td class=\"c3\"><div><button class=\"button bigrounded disable\">0</button></div></td><td class=\"c4\"><div><button class=\"button bigrounded disable\">0</button></div></td></tr>";

	$res .= "</tbody>";

	return $res;
}

function journee_tournoi_display($journee, $saison_id, $champ_id, $options_type_matchs)
{
	$str = "";
	$i = 1;
	$fxlist = new FXListMatchsPlayOff($saison_id, $journee['id'], $journee['tournoi_phase_finale'], false, "F");
	foreach(array_reverse($fxlist->body->tab) as $item) {

//		if ($i >= 8) break; // On affiche max les 8ieme

		if ($item == _FXLINESEPARATOR_) continue;
		if (!isset($item['match_id'])) $item['match_id'] = -1;

		// On remet les données "propres" (sans le html)
		$item['nom1'] = isset($item[6]) ? $item[6] : str_replace('<FONT CLASS="equipe_gagne">', '', str_replace('</FONT>', '', $item['nom1'])); // Astuce
		$item['nom2'] = isset($item[8]) ? $item[8] : str_replace('<FONT CLASS="equipe_gagne">', '', str_replace('</FONT>', '', $item['nom2'])); // Astuce
		$item['resultat'] = $item['res2'];

		// Choix du vainqueur pour les matches
		$vainqueur = StatsJourneeBuilder::kikiGagne($item);

		$resultat = Wrapper::formatScore($item);
		$item['div_nom1'] = "<span class=\"".($vainqueur == 2 ? "equipe_perdu" : "equipe_gagne").(sess_context::getGestionFanny() == 1 && $item['fanny'] == 1 ? " fanny" : "")."\">".$item['nom1']."</span>";
		$item['div_nom2'] = "<span class=\"".($vainqueur == 1 ? "equipe_perdu" : "equipe_gagne").(sess_context::getGestionFanny() == 1 && $item['fanny'] == 1 ? " fanny" : "")."\">".$item['nom2']."</span>";

//		echo "<tr id=\"tr_".$i++."\"><td class=\"c1\"><div>".($item['niveau'] == "F|1|1" ? "F" : "&frac1".substr($item['niveau'], 2, 1).";")."</div></td><td class=\"c2\"><div>".$item['div_nom1']."</div></td><td class=\"c3\"><div>".$resultat."</div></td><td class=\"c4\"><div>".$item['div_nom2']."</div></td>";
		$str .= "<tr id=\"tr_".$i++."\"><td class=\"c1\"><div>".($item['niveau'] == "F|1|1" ? "F" : "&frac1".substr($item['niveau'], 2, 1).";")."</div></td>";
		$str .= "<td class=\"c2\"><div><ul><li>".$item['div_nom1']."</li><li>".$item['div_nom2']."</li></ul></div></td><td class=\"c3\"><div>".$resultat."</div></td>";
	}

	$match_vide = array ("penaltys" => 0, "prolongation" => 0, "match_joue" => 0, "resultat" => "0/0", "fanny" => 0, "nbset" => 1);
	for($x = $i; $x <= sess_context::getHomeListHeadcount(); $x++) $str .= "<tr id=\"tr_".$x."\"><td class=\"c1\"><div>-</div></td><td class=\"c2\"><div>-</div></td><td class=\"c3\"><div>".Wrapper::formatScore($match_vide)."</div></td></tr>";

	return $str;
}

function journee_display($journee, $saison_id, $filtre)
{
	$str = "";
	$k = 1;
	$req = "SELECT m.penaltys, m.prolongation, m.id match_id, j.date mdate, m.match_joue, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2, m.fanny, m.nbset, m.play_date FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_journee=".$journee['id']." AND m.id_champ=".$saison_id." ".$filtre;
	if ($res = dbc::execSql($req)) {
		while($row = mysqli_fetch_array($res))
		{
			// Choix du vainqueur
			$vainqueur = StatsJourneeBuilder::kikiGagne($row);
			$str .= "<tr>";
			$str .= "<td class=\"c2 ".($vainqueur == 1 ? "equipe_gagne" : "equipe_perdu")."\"><div>".$row['nom1']."</div></td>";
			$str .= "<td class=\"c3 score\"><div>".Wrapper::formatScore($row)."</div></td>";
			$str .= "<td class=\"c4 ".($vainqueur == 2 ? "equipe_gagne" : "equipe_perdu")."\"><div>".$row['nom2']."</div></td>";
			$str .= "</tr>";
			$k++;
		}
	}

	$match_vide = array ("penaltys" => 0, "prolongation" => 0, "match_joue" => 0, "resultat" => "0/0", "fanny" => 0, "nbset" => 1);
	for($x = $k; $x <= sess_context::getHomeListHeadcount(); $x++) $str .= "<tr><td class=\"c2\"><div>-</div></td><td class=\"c3 score\"><div>".Wrapper::formatScore($match_vide)."</div></td><td class=\"c4\"><div>-</div></td></tr>";

	return $str;
}

?>