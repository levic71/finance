<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/ManagerFXList.php";
include "../www/StatsBuilder.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$idt         = Wrapper::getRequest('idt', 0);
$sgb         = JKCache::getCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", 24*60*60, "_FLUX_STATS_CHAMP_");
$stats_teams = $sgb->getStatsTeams();
$bests_teams = $sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? $sgb->getBestTeamsByTournoiPoints() : ($sess_context->getChampionnatType() == _TYPE_CHAMPIONNAT_ ? $sgb->getBestTeamsByPoints() : array());

if (!isset($stats_teams[$idt])) { exit(0); }

$xt = $stats_teams[$idt];

$rank = 1;
foreach($bests_teams as $stat)
{
	if ($stat->id == $xt->id) break;
	$rank++;
}

$evol = "";
if (count($xt->evol_classement) > 0 || true)
{
	reset($xt->evol_classement);
	$moy = 0;
	$nb = 0;
	$q1 = "";
	$q2 = "";
	$q11 = "";
	$q22 = "";
	$tips_labels = "";
	$steps = ceil(count($stats_teams) / 5);
	foreach($xt->evol_classement as $cle => $val)
	{
		if (strlen($val."") > 0) {
			$tmp = $val == "" ? "@" : -$val;
			$q1 .= $q1 == "" ? $tmp : "§".$tmp;
			$myval = 100 - (($val - 1) * 100 / ($steps * 5));
			$q11 .= ($q11 == "" ? "" : ",").$myval;
			$tips_labels .= ($tips_labels == "" ? "" : ",")."'N° ".$val."'";
			$tmp = explode("-", $cle);
			$q2 .= ($q2 == "" ? "" : "§").$tmp[2]."-".$tmp[1]."-".substr($tmp[0], 2);
			$q22 .= ($q22 == "" ? "" : ",")."'".$tmp[2]."-".$tmp[1]."-".substr($tmp[0], 2)."'";
			$moy += $myval;
			$nb++;
		}
	}
	$q1 = str_replace('@', '', $q1);

	// On met une ligne droite si une seule donnée doit être affichée
	if (!strstr($q1, "§") && $q1 != "")
	{
		$q1 = $q1."§".$q1;
		$q2 = $q2."§".$q2;
		$moy = 0;
	}
	else if ($q1 == "")
	{
		$q1 = "-".count($stats_teams)."§-".count($stats_teams);
		$q2 = "0§0";
		$moy = 0;
	}
	else
	{
		$moy = $nb > 0 ? round($moy / $nb) : 0;
	}
	$q11 = "[".$q11."]";
	$q22 = "[".$q22."]";
	$tips_labels = "[".$tips_labels."]";

	$yaxe_labels = "";
	for($x = 1; $x <= 5; $x++) $yaxe_labels .= ($yaxe_labels == "" ? "" : ",")."'".(($steps * 5) - $x * $steps + 1)."'";
	$yaxe_labels = 	"['', ".$yaxe_labels."]";

	$__width = 480;
	$__height= 220;
	$evol = "<img src=\"./stats_team_perf.php?reverse=1&scale1=".(-count($stats_teams))."&scale2=0&chart=1&datas1=".$q1."&datas2=".$q2."&legendes=Evolution+classement§Moyenne\" width=\"".$__width."\" height=\"".$__height."\" />";
}

// Récupération des infos de l équipe
$ses = new SQLEquipesServices($sess_context->getRealChampionnatId());
$e = $ses->getEquipe($idt);

?>

<div id="efiche" class="bartable compact">

<ul class="sidebar">
<? if ($sess_context->championnat['entity'] == "_NATIF_") { ?>
	<li><a id="sb_fb" onclick="google_tracking('facebook.com');" target="_blank" class="ToolText" onmouseover="showtip('sb_fb');" href="http://www.facebook.com/dialog/feed?
  app_id=107452429322746&
  link=http://www.jorkers.com&
  picture=http://www.jorkers.com/wrapper/img/logo.png&
  name=<?= utf8_encode("» Statistiques équipe : ".$xt->nom) ?>&
  caption=<?= utf8_encode($libelle_genre[$sess_context->getTypeSport()]) ?> :: <?= utf8_encode(($sess_context->isTournoiXDisplay() ? "Tournoi " : "Championnat ").$sess_context->getChampionnatNom()) ?>&
  description=<?= utf8_encode("Jorkers.com, solution de gestion de championnats et tournois de sports individuels et collectifs") ?>&
  message=<?= utf8_encode("Laisser un message !") ?>&
  redirect_uri=http://www.jorkers.com/wrapper/fb.php?idc=<?= $sess_context->getRealChampionnatId()."_t".$idt ?>"><span>Publier sur Facebook</span></a></li>
<? } ?>
	<li><a href="#" onclick="mm({action: 'teams'});" id="sb_back" class="swap ToolText" onmouseover="showtip('sb_back');"><span>Retour</span></a></li>
</ul>

<h2 class="grid tables">
	<?= $xt->nom ?>
<? if ($sess_context->getChampionnatType() != _TYPE_LIBRE_) { ?>
	<button class="button orange right" style="margin-bottom: 0px;"><?= $xt->points ?> Pts</button><button class="button blue right" style="margin-bottom: 0px;">Classement général: <?= $rank ?></button>
<? } ?>
</h2>


<table border="0" style="width: 100%;border-radius: 0px 0px 10px 10px; -moz-border-radius: 0px 0px 10px 10px; -webkit-border-radius: 0px 0px 10px 10px; " class="dash_player  box-wrapper">
	<tr valign="center">
		<td id="legend">
			<div class="btj2 button black" style="width: auto;" onclick="mm({action:'stats', idt:<?= $idt ?>});">
				<img src="<?= Wrapper::formatPhotoEquipe($e['photo']) ?>" />
				<div><?= $xt->nom  ?></div>
			</div>
			<div class="skills">
				<ul id="skills_list"></ul>
			</div>
		</td>
		<td><div id="diagram"></div></td>
	</tr>
</table>

<!-- Pour les championnats classiques, on devrait avoir une courbe qui reflete l evolution dans le classement général et non un classement par journee -->
<div class="dashcounter box-wrapper subbar" style="margin-top: 10px; min-height: 50px;"><div class="title">Classement par journée</div><div id="perfgraph"></div></div>


<? 	$k = 0; $items = ""; if ($e['nb_joueurs'] > 0) { ?>
<br />
<h2 class="grid tables">Joueurs <div style="float: right; width: 200px;"><div style="width: 10px; height: 10px; float: left; margin: 14px 10px 0px;" class="blue"></div><span style="float: left;">Capitaine</span><div style="width: 10px; height: 10px; float: left; margin: 14px 10px 0px;" class="orange"></div><span style="float: left;">Adjoint</span></div></h2>
<?
	$js = SQLServices::cleanIN(str_replace('|', ',', $e['joueurs']));

	if ($js != "") {

		$t = array();
		$req = "SELECT u.*, j.id idp FROM jb_users u, jb_user_player up, jb_joueurs j WHERE j.id IN (".$js.") AND j.id = up.id_player AND up.id_user = u.id AND up.status=1";
		$res = dbc::execSql($req);
		while($row = mysqli_fetch_array($res))  $t[$row['idp']] = $row;

		$sjs = new SQLJoueursServices($sess_context->getRealChampionnatId());
		$id_joueurs = explode(',', $js);
		foreach($id_joueurs as $id)
		{
			$joueur = $sjs->getJoueur($id);
			$age	= isset($t[$id]) ? ToolBox::date2age($t[$id]['date_nais']) : ToolBox::date2age($joueur['dt_naissance']);
			$photo  = Wrapper::formatPhotoJoueur($joueur['photo'], isset($t[$id]) && $t[$id]['photo'] != "" ? $t[$id]['photo'] : "");
			$pseudo = isset($t[$id]) ? $t[$id]['pseudo'] : $joueur['pseudo'];
			$items .= "<li class=\"btj2 button ".($e['capitaine'] == $joueur['id'] ? "blue" : ($e['adjoint'] == $joueur['id'] ? "orange" : "black"))."\" onclick=\"mm({action:'stats', idp:".$joueur['id']." });\"><img src=\"".$photo."\" /><div><span>".$pseudo."</span> (".$age." ans)</div></li>";
			$k++;
		}
	}

	if ($k > 0 && ($k % ($k > 4 ? 5 : 4)) != 0) for($x=0; $x < (($k > 4 ? 5 : 4) - ($k % ($k > 4 ? 5 : 4))); $x++) $items .= "<li class=\"btj2 button disable\"></li>"; // soit 4 par lignes soit 5 par lignes
}

$req = "SELECT COUNT(*) total FROM jb_matchs WHERE fanny=1 AND id_champ=".$sess_context->getChampionnatId();
$res = dbc::execSql($req);
$row = mysqli_fetch_array($res);
$fanny_total = $row['total'];
$pourc_fanny_in  = $fanny_total == 0 ? 0 : ($xt->fanny_in  * 100) / $fanny_total;
$pourc_fanny_out = $fanny_total == 0 ? 0 : ($xt->fanny_out * 100) / $fanny_total;
$pourc_justesse_gagnes = $xt->matchs_joues == 0 ? 0 : ($xt->justesse_gagnes * 100) / $xt->matchs_joues;
$pourc_justesse_perdus = $xt->matchs_joues == 0 ? 0 : ($xt->justesse_perdus * 100) / $xt->matchs_joues;

?>


<ul class="trombinoscope <?= $k > 4 ? "huge" : "" ?>"><?= $items ?></ul>


<div id="bloc2" class="dashcounter box-wrapper subbar">
	<div class="title" style="width: 655px;">Buts</div>
	<button id="bf11" class="pourc button blue"><div class="box"><div class="cnt"><?= $xt->buts_marques ?></div><div class="txt">Marqués</div></div></button>
	<button id="bf12" class="pourc button orange"><div class="box"><div class="cnt"><?= $xt->buts_encaisses ?></div><div class="txt">Encaissés</div></div></button>
	<button id="bf7"  class="pourc button <?= $xt->diff >= 0 ? "green" : "red" ?>"><div class="box"><div class="cnt"><?= $xt->diff ?></div><div class="txt">Différence</div></div></button>
	<button class="button disable"></button>
	<button id="b12" class="button gray" style="text-align: center;" onclick="rmCN('efiche', 'compact');"><div class="box"><div class="cnt">More</div></div></button>
	<button id="b13" class="button gray" style="text-align: center;" onclick="addCN('efiche', 'compact');"><div class="box"><div class="cnt">Less</div></div></button>
</div>


<? if ($sess_context->getGestionSets()) { ?>
		<div id="bloc4" class="dashcounter box-wrapper subbar">
			<div class="title" style="width: 655px;">Sets</div>
			<button id="bf11" class="pourc button blue"><div class="box"><div class="cnt"><?= round($xt->sets_gagnes) ?></div><div class="txt">Gagnés</div></div></button>
			<button id="bf12" class="pourc button orange"><div class="box"><div class="cnt"><?= round($xt->sets_perdus) ?></div><div class="txt">Perdus</div></div></button>
			<button id="bf7" class="pourc button <?= $xt->sets_diff >= 0 ? "green" : "red" ?>"><div class="box"><div class="cnt"><?= round($xt->sets_diff) ?></div><div class="txt">Différence</div></div></button>
			<button class="button disable"></button>
			<button class="button disable"></button>
		</div>
<? } ?>

<div id="bloc3" class="dashcounter box-wrapper subbar" style="height: 65px;">
	<div class="title" style="width: 655px;">Confrontations (% matchs gagnés)</div>
<? $fxlist = new FXListStatsConfrontations($sess_context->getChampionnatId(), $idt, 10);
$tab = $fxlist && $fxlist->body ? $fxlist->body->tab : array();
$i = 1;
foreach($tab as $t) { ?>
	<button class="pourc button purple">
        <div class="box"><div class="cnt"><?= round($t['gagnes'] == 0 ? 0 : ($t['gagnes'] * 100) / $t['gagnes']) ?>%</div>
            <div class="txt"><?= Wrapper::stringEncode4JS($t['equipe']) ?></div></div></button>
<? if ($i++ >= 5) break; } ?>
<? for($k=$i; $k<=5; $k++) { ?> <button class="button disable"></button><? } ?>
</div>


<?
if (false && $sess_context->getGestionFanny()) {
$tbody = ""; $thead = "";
$mybar6_values = "";
$req = "SELECT m.fanny, m.nbset, DATE_FORMAT(j.date, '%d/%m/%y') date, e1.nom nom1, m.resultat resultat, e2.nom nom2 FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE m.fanny=1 AND e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND (m.id_equipe1=".$idt." OR m.id_equipe2=".$idt.") AND m.id_champ=".$sess_context->getChampionnatId()." ORDER BY date DESC LIMIT 0,4";
$res = dbc::execSql($req);
$i = 1; while ($row = mysqli_fetch_array($res))
	$tbody .= '<tr><td class="c1"><div>'.$i++.'</div></td><td class="c2"><div>'.$row['nom1'].'</div></td><td class="c3"><div>'.Wrapper::formatScore($row).'</div></td><td class="c4"><div>'.$row['nom2'].'</div></td><td class="c5"><div>'.$row['date'].'</div></td></tr>';
?>
<br />
<div id="box" class="classic">
<h2 class="grid tables">Fannys <button class="button gray right right" onclick="mm({action: 'fannys', idt: <?= $idt ?>});">Tous les fannys</button></h2>
<table cellspacing="0" cellpadding="0" class="jkgrid fannys" id="matches">
<thead><tr><th class="c1"><div>N°</div></th><th class="c2"><div>Equipe1</div></th><th class="c3"><div>Score</div></th><th class="c4"><div>Equipe2</div></th><th class="c5"><div>&nbsp;</div></th></tr></thead>
<tbody><?= $tbody ?></tbody>
</table>
</div>
<? } ?>


<? $pourc_buts_out = $xt->matchs_joues == 0 ? 0 : round(($xt->buts_marques * 100)/($xt->buts_marques + $xt->buts_encaisses)); ?>
<? $pourc_buts_in = $xt->matchs_joues == 0 ? 0 : 100 - $pourc_buts_out; ?>

<script>
<? if ($sess_context->getGestionFanny()) { ?>
o.init({ name: 'diagram', skills_list: 'skills_list', size: 400, cc_size: 75, rad: 62, data: [ { v: [<?= $xt->pourc_gagnes ?>, <?= $xt->matchs_joues == 0 ? 0 : 100-$xt->pourc_gagnes-$xt->pourc_nuls ?><?= $sess_context->getGestionMatchsNul() ? ",".$xt->pourc_nuls : ""?>], t: ['Matchs gagnés','Matchs perdus','Matchs nuls'], c: ['#ED0086',{bg:'#EDBED9',fg:'#ED0086',op:0.5},{bg:'#aaa',op:0.5}] }, { v: [<?= $pourc_buts_out ?>,<?= $pourc_buts_in ?>], t: [{lbl:'Buts marqués', skl:'<?= $xt->buts_marques ?> buts\n marqués'}, {lbl:'Buts encaissés',skl:'<?= $xt->buts_encaisses ?> buts\n encaissés'}], c: ['#08A7DC',{bg:'#CDF0FC', fg:'#08A7DC', op:0.5}] }, { rs: 270+(<?= round($pourc_justesse_gagnes) ?>*3.6), v: [<?= round($pourc_justesse_gagnes) ?>,<?= round($pourc_justesse_perdus) ?>], t: ['Gagnés de\n justesse', 'Perdus de\n justesse'], c: ["#FFBF67","#FF6F67"] }, { rs: 90+(<?= round($pourc_fanny_in) ?>*3.6), v: [<?= round($pourc_fanny_in) ?>, <?= round($pourc_fanny_out) ?>], t: ['Fannys pris','Fannys donnés'], c: [{bg:"#FFE700", fg:"#6B6000"}, {bg:"#AFF53D",fg:"#496619"}] } ] });
<? } else { ?>
o.init({ name: 'diagram', skills_list: 'skills_list', size: 400, cc_size: 75, rad: 62, data: [ { v: [<?= $xt->pourc_gagnes ?>, <?= 100-$xt->pourc_gagnes-$xt->pourc_nuls ?><?= $sess_context->getGestionMatchsNul() ? ",".$xt->pourc_nuls : ""?>], t: ['Matchs gagnés','Matchs perdus','Matchs nuls'], c: ['#ED0086',{bg:'#EDBED9',fg:'#ED0086',op:0.5},{bg:'#aaa',op:0.5}] }, { v: [<?= $pourc_buts_out ?>,<?= $pourc_buts_in ?>], t: [{lbl:'Buts marqués', skl:'<?= $xt->buts_marques ?> buts\n marqués'}, {lbl:'Buts encaissés',skl:'<?= $xt->buts_encaisses ?> buts\n encaissés'}], c: ['#08A7DC',{bg:'#CDF0FC', fg:'#08A7DC', op:0.5}] }, { rs: 270+(<?= round($pourc_justesse_gagnes) ?>*3.6), v: [<?= round($pourc_justesse_gagnes) ?>,<?= round($pourc_justesse_perdus) ?>], t: ['Gagnés de\n justesse', 'Perdus de\n justesse'], c: ["#FFBF67","#FF6F67"] } ] });
<? } ?>
drawAnalytics({ name: "perfgraph", width: 670, height: 200, lblext: ' ', avg: <?= $moy == 0 ? "0.1" : $moy ?>, labels: <?= $q22 ?>, data: <?= $q11 ?>, yaxe_labels: <?= $yaxe_labels ?>, tips_labels: <?= $tips_labels ?>, overmax: 100 });
</script>


</div>
