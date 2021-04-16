<?

require_once "../include/sess_context.php";

session_start();

require_once "common.php";
require_once "../include/inc_db.php";
require_once "../www/ManagerFXList.php";
require_once "../www/StatsBuilder.php";

if ($sess_context->getChampionnatType() == _TYPE_LIBRE_) Toolbox::do_redirect_location("table_players.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

if (!isset($choix_stat)) $choix_stat = 0;

$sgb = JKCache::getCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", 24*60*60, "_FLUX_STATS_CHAMP_");

$best_teams_tournoi = $sgb->getBestTeamsByTournoiPoints();
$best_teams_championnat = $sgb->getBestTeamsByPoints();

$tbody = ""; $thead = "";

// En cas de besoin si pagination demandée !!!
$delta = 10;
$page  = Wrapper::getRequest('page', 0);

$t = array();
if ($choix_stat != 0) array_push($t, array("id" => "sb_beste", "onclick" => "xx({action: 'tables', id:'main', url:'table_teams.php?choix_stat=0'});", "tooltip" => "Classement général"));
if ($sess_context->championnat['entity'] == "_NATIF_" && $choix_stat == 0) array_push($t, array("id" => "sb_export", "onclick" => "toogle('redirect');", "tooltip" => "Afficher ce classement sur un autre site"));
if ($choix_stat != 2) array_push($t, array("id" => "sb_bestea", "onclick" => "xx({action: 'tables', id:'main', url:'table_teams.php?choix_stat=2'});", "tooltip" => "Meilleures attaques"));
if ($choix_stat != 3) array_push($t, array("id" => "sb_bested", "onclick" => "xx({action: 'tables', id:'main', url:'table_teams.php?choix_stat=3'});", "tooltip" => "Meilleures défenses"));
if ($sess_context->championnat['entity'] == "_NATIF_") array_push($t, array("id" => "sb_fb",
    "onclick" => "google_tracking('facebook.com');",
    "target" => "_blank",
    "href" => "http://www.facebook.com/dialog/feed?" .
    "app_id=107452429322746&link=http://www.jorkers.com&picture=http://www.jorkers.com/wrapper/img/logo.png&"  .
    "name=".utf8_encode("? Acc?s au classement")."&" .
    "caption=".utf8_encode($libelle_genre[$sess_context->getTypeSport()])." :: ".utf8_encode(($sess_context->isTournoiXDisplay() ? "Tournoi " : "Championnat ").$sess_context->getChampionnatNom())."&" .
    "description=".utf8_encode("Jorkers.com, solution de gestion de championnats et tournois de sports individuels et collectifs")."&" .
    "message=".utf8_encode("Laisser un message !")."&" .
    "redirect_uri=http://www.jorkers.com/wrapper/fb.php?idc=".$sess_context->getRealChampionnatId(),
    "tooltip" => "Publier sur Facebook"));

Wrapper::fab_button_menu($t);

Wrapper::template_box_start(12);

?> <div class="vgrid <?= $sess_context->getGestionSets() ? "" : "nosets" ?> type_<?= $sess_context->getChampionnatType() ?> <?= $sess_context->getGestionMatchsNul() ? "gestion_nuls" : "" ?> <?= $sess_context->getGestionSets() ? "gestion_sets" : "" ?>" id="box"> <?

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU SYNTHESE EQUIPES
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 0)
{ 
	
	$mytitle = '
	<span>Classement équipes</span>
	<div class="mdl-card__menu">
		<span class="mdl-chip"><a href="../wpdf2/pdf_classement.php?champ='.$sess_context->getRealChampionnatId().'&format=A5" target="_blank"><img class="mdl-chip__contact" src="img/adobe-pdf-icon-a5.png" /></a></span>
		<span class="mdl-chip"><a href="../wpdf2/pdf_classement.php?champ='.$sess_context->getRealChampionnatId().'&format=A4" target="_blank"><img class="mdl-chip__contact" src="img/adobe-pdf-icon-a4.png" /></a></span>
		<span class="mdl-chip"><a href="../wpdf2/pdf_classement.php?champ='.$sess_context->getRealChampionnatId().'&format=A3" target="_blank"><img class="mdl-chip__contact" src="img/adobe-pdf-icon-a3.png" /></a></span>
	</div>';

	Wrapper::template_box_title($mytitle);

	$th = array("nom" => "Equipe", "points" => "Pts", "matchs_joues" => "J", "matchs_gagnes" => "G", "matchs_nuls" => "N", "matchs_perdus" => "P", "sets_joues" => "SJ", "sets_gagnes" => "SG", "sets_nuls" => "SN", "sets_perdus" => "SP", "sets_diff" => "SD", "buts_marques" => "p", "buts_encaisses" => "c",  "moy_classement" => "Cm", "diff" => "Diff");

	if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
		$cols = array("nom" => 1, "points" => 1, "matchs_joues" => 1, "matchs_gagnes" => 1, "matchs_nuls" => 1, "matchs_perdus" => 1, "sets_joues" => 1, "sets_gagnes" => 1, "sets_nuls" => 1, "sets_perdus" => 1, "sets_diff" => 1, "buts_marques" => 1, "buts_encaisses" => 1, "moy_classement" => 1, "diff" => 1);
	else
		$cols = array("nom" => 1, "points" => 1, "matchs_joues" => 1, "matchs_gagnes" => 1, "matchs_nuls" => 1, "matchs_perdus" => 1, "sets_joues" => 1, "sets_gagnes" => 1, "sets_nuls" => 1, "sets_perdus" => 1, "sets_diff" => 1, "buts_marques" => 1, "buts_encaisses" => 1, "diff" => 1);

	if (true || !$sess_context->getGestionSets()) { unset($cols['sets_joues']); unset($cols['sets_gagnes']); unset($cols['sets_nuls']); unset($cols['sets_perdus']); unset($cols['sets_diff']); }

	$tab = array();
	if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_) {
		if (count($best_teams_tournoi) > 0) {
			$fxlist = new FXListClassementGeneralTournoi($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId(), $best_teams_tournoi);
			$tab = Wrapper::reformatTournoiClassement($fxlist->body->tab);
		}
	}
	else {
		if (count($best_teams_championnat) > 0) {
			$fxlist = new FXListStatsTeamsII($best_teams_championnat);
			$tab = $fxlist->body->tab;
		}
	}

	if (count($tab) < 10) {
		$empty_row = array('id' => 0); reset($cols);
		while (list($cle, $val) = each($cols)) $empty_row[$cle] = "-";

		$c = count($tab);
		for($x=0; $x < (10-$c); $x++) $tab[] = $empty_row;
	}

	$i = 1;
	foreach($tab as $item)
	{
		if ($sess_context->getChampionnatType() == _TYPE_LIBRE_ && $item['pseudo'] == "F") continue;

		$empty = $item['id'] == 0 ? true : false;

		$tmp = '<tr>'; $tbody .= '<tr id="tr_'.$i.'" '.($empty ? '' : 'class="clickonit" onclick="mm({action:\'stats\', idt:\''.$item['id'].'\'});"').'>';
		if (!isset($num) || (isset($num) && $num))
		{
			$tmp .= '<th class="c1"><div>N°</div></th>';
			$tbody .= '<td class="c1"><div>'.$i.'</div></td>';
		}

		$j=2;

		reset($cols);
		foreach($cols as $cle => $val)
		{
			if (!$empty && $cle == 'nom') $item[$cle] = preg_replace("/<.*>/", "", str_replace("</A>", "", $item[$cle]));
			if (!$empty && $cle == 'nom') $item[$cle] = "<a href=\"#\" onclick=\"mm({action:'stats', idt:'".$item['id']."'});\">".$item[$cle]."</a>";
			if ($cle == 'points') $item[$cle] = "<button class=\"button ".($empty ? "disable" : "orange")." bigrounded\">".($empty ? 0 : $item[$cle])."</button>";
			if ($cle == 'diff') $item[$cle] = "<button class=\"button bigrounded ".($empty ? "disable" : (intval($item[$cle])>=0 ? 'green' : 'red'))."\">".($empty ? 0 : $item[$cle])."</button>";
			$tmp .= '<th class="c'.$j.'"><div>'.(isset($th[$cle]) ? $th[$cle] : $cle).'</div></th>';
			$tbody .= '<td class="c'.$j.'"><div>'.($item[$cle]."" == "-" ? ($cle != "nom" ? "" : "&nbsp;") : $item[$cle]).'</div></td>';
			$j++;
		}

		$tmp .= '</tr>'; $tbody .= '</tr>';
		if ($i == 1) $thead .= $tmp;
		$i++;
	}

?>
<? $url = "http://www.jorkers.com/www/classement_redirect.php?champ=".$sess_context->getRealChampionnatId()."&view=".$sess_context->getChampionnatType(); ?>
<div id="redirect" style="display: none; padding: 10px 0px 10px 100px;">
<div style="width: 120px; text-align: right; float: left; color: #666;">Url direct :</div><div><input type="text" size="80" value="<?= $url ?>" onclick="javascript:this.focus();this.select();" readonly="readonly"></div>
<div style="width: 120px; text-align: right; float: left; color: #666;">Code embarqu? :</div><div><input type="text" size="80" value='<iframe  scrolling="auto" frameborder="0" marginwidth="0" marginheight="0" height="600" width="740" src="<?= $url ?>"></iframe>' onclick="javascript:this.focus();this.select();" readonly="readonly"></div>
</div>

<table cellspacing="0" cellpadding="0" class="jkgrid" id="table_teams">
<thead><?= $thead ?></thead>
<tbody><?= $tbody ?></tbody>
</table>

<?
}

// /////////////////////////////////////////////////////////////////////////////////////////////
// TABLEAU SYNTHESE JOUEURS
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 1)
{
	if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
		$fxlist = new FXListStatsTournoiJoueurs($sgb);
	else
		$fxlist = new FXListStatsJoueurs($sgb);
	$fxlist->FXSetTitle("STATS");
	$fxlist->FXSetFooter($sgb->getNbMatchs()." matchs jou?s dans le championnat sur ".$sgb->getNbJournees()." journ?es (".sprintf("%.2f", $sgb->getMoyMatchsJoues())." matchs/journ?e)");
	$fxlist->FXDisplay();
}

// /////////////////////////////////////////////////////////////////////////////////////////////
// MEILLEURES ATTAQUES
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 2)
{
	$fxlist = new FXListStatsAttDef($sgb->getBestAttaques(), 1);
?>
<div class="bartable">
<h2 class="grid leagues">Meilleures Attaques - Nombre de buts moyen marqu?s par match</h2>
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
bars.build({ name :'bestatt', tsize: 160, rsize: 237, msize: 190, values: [<?= $values ?>] });
</script>
<?
}


// /////////////////////////////////////////////////////////////////////////////////////////////
// MEILLEURES DEFENSES
// /////////////////////////////////////////////////////////////////////////////////////////////
if ($choix_stat == 3)
{
?>
<div class="bartable">
<h2 class="grid leagues">Meilleures D?fenses - Nombre de buts moyen encaiss?s par match</h2>
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
bars.build({ name :'bestatt', tsize: 160, rsize: 237, msize: 190, values: [<?= $values ?>] });
</script>
<?
}

?>

</div>

<? Wrapper::template_box_end(); ?>

