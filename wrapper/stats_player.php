<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/ManagerFXList.php";
include "../www/StatsBuilder.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$idp           = Wrapper::getRequest('idp', 0);
$trombinoscope = Wrapper::getRequest('trombinoscope', 0);
$sgb           = JKCache::getCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", 24*60*60, "_FLUX_STATS_CHAMP_");
$best_teams    = $sgb->getBestTeams($idp);
$most_matchs   = $sgb->getMostTeams($idp);

$id_saison = $sess_context->getChampionnatId();
$req = "SELECT * FROM jb_saisons WHERE id=".$id_saison;
$res = dbc::execSql($req);
$saison = mysqli_fetch_array($res);


// /////////////////////////////////////////////////////
// TROMBINOSCOPE
// /////////////////////////////////////////////////////
if ($trombinoscope == 1)
{
	if ($sess_context->isFreeXDisplay())
		$filtre = (isset($saison['joueurs']) && $saison['joueurs'] != "" ? " AND id IN (".SQLServices::cleanIN($saison['joueurs']).")" : "");
//		$filtre = (isset($saison['joueurs']) && $saison['joueurs'] != "" ? " AND j.id IN (".SQLServices::cleanIN($saison['joueurs']).")" : "");
	else
	{
		$j = "";
		if ($saison['joueurs'] != "")
		{
			$tmp = explode(",", $saison['joueurs']);
			foreach($tmp as $item)
				if ($item != "") $j .= ($j != "" ? "," : "").$item;
		}
		$filtre = ($j != "" ? " AND id IN (".SQLServices::cleanIN($j).")" : "");
//		$filtre = ($j != "" ? " AND j.id IN (".SQLServices::cleanIN($j).")" : "");
	}

?>

<div>
<ul class="sidebar">
	<li><a href="#" onclick="mm({action: 'players'});" id="sb_back" class="swap ToolText" onmouseover="showtip('sb_back');"><span>Retour</span></a></li>
</ul>
<h2 class="grid tables">Trombinoscope</h2>
<ul class="trombinoscope">
<?
	$up = array();
	$sql = 'SELECT u.photo, u.pseudo, up.id_player FROM jb_users u, jb_user_player up WHERE up.id_champ='.$sess_context->getRealChampionnatId().' AND u.id = up.id_user AND up.status=1';
	$res = dbc::execSql($sql);
	while($row = mysqli_fetch_array($res)) {
		$up[$row['id_player']] = $row;
	}

	$sql = 'SELECT *, id id_player, photo jphoto, "" uphoto, pseudo jpseudo, "" upseudo, CONCAT(nom, " ", prenom) name FROM jb_joueurs WHERE id_champ='.$sess_context->getRealChampionnatId().' '.$filtre.' ORDER BY nom';
//	$sql = 'SELECT *, j.photo jphoto, j.pseudo jpseudo, CONCAT(j.nom, " ", j.prenom) jname FROM jb_joueurs j LEFT JOIN (SELECT *, u.photo uphoto, u.pseudo upseudo, CONCAT(u.nom, " ", u.prenom) uname FROM jb_user_player up, jb_users u WHERE u.id = up.id_user AND up.status=1) xx ON j.id = xx.id_player WHERE j.id_champ='.$sess_context->getRealChampionnatId().' '.$filtre.' ORDER BY jname';

	$res = dbc::execSql($sql);
	while($row = mysqli_fetch_array($res)) {
		if (isset($up[$row['id_player']])) {
			$row['uphoto'] = $up[$row['id_player']]['photo'];
			$row['jpseudo'] = $up[$row['id_player']]['pseudo'];
		}
		echo "<li class=\"btj2 button black ".Wrapper::getColorMedaille($sgb->stats_joueurs[$row['id_player']]->medaille)."\" onclick=\"mm({action:'stats', idp:".$row['id_player']." });\"><img src=\"".Wrapper::formatPhotoJoueur($row['jphoto'], $row['uphoto'])."\" height=\"125\" width=\"125\" /><div>".($row['upseudo'] != "" ? $row['upseudo'] : $row['jpseudo'])."</div></li>";
	}

?>
</ul>
</div>
<?
	exit(0);
}
// /////////////////////////////////////////////////////


// /////////////////////////////////////////////////////
// Stats individuelles
// /////////////////////////////////////////////////////
$tab = array();

$req = "SELECT * FROM jb_equipes WHERE (joueurs='".$idp."' OR joueurs LIKE '".$idp."|%' OR joueurs LIKE '%|".$idp."' OR joueurs LIKE '%|".$idp."|%') AND id_champ=".$sess_context->getRealChampionnatId()." ".($sess_context->isFreeXDisplay() ? "" : "AND id IN (".SQLServices::cleanIN($saison['equipes']).")");
$res = dbc::execSql($req);
while ($row = mysqli_fetch_array($res)) $tab[] = $row;

if (isset($sgb->stats_joueurs[$idp])) {
	$j = $sgb->stats_joueurs[$idp];
} else {
	$j = new StatGlobalJoueur();
	$j->id = $idp;
	$sps = new SQLJoueursServices($sess_context->getRealChampionnatId());
	$p = $sps->getJoueur($idp);
	$j->photo    = $p['photo'];
	$j->prenom   = $p['prenom'];
	$j->nom      = $p['nom'];
	$j->pseudo   = $p['pseudo'];
	$j->medaille = _NO_MEDAILLE_;
}

$j->forme_indice = $j->joues == 0 ? 0 : Wrapper::extractFormeIndice($j->forme_indice);
$j->forme_last_indice = $j->joues == 0 ? 0 : Wrapper::extractFormeIndice($j->forme_last_indice);
$j->p_att = ($j->jouesA+$j->jouesD) == 0 ? 0 : ($j->jouesA*100)/($j->jouesA+$j->jouesD);
$j->p_def = ($j->jouesA+$j->jouesD) == 0 ? 0 : 100-$j->p_att;

if (true || $sess_context->isFreeXDisplay()) {
	$req = "SELECT COUNT(*) total FROM jb_matchs WHERE fanny=1 AND id_champ=".$sess_context->getChampionnatId();
	$res = dbc::execSql($req);
	$row = mysqli_fetch_array($res);
	$fanny_total = $row['total'];

	$j->fanny_in  = $fanny_total == 0 ? 0 : ($j->fanny_in  * 100) / $fanny_total;
	$j->fanny_out = $fanny_total == 0 ? 0 : ($j->fanny_out * 100) / $fanny_total;
	$j->fanny_xxx = 100 - $j->fanny_in - $j->fanny_out;

	$j->justesse_gagnes = $j->joues == 0 ? 0 : ($j->justesse_gagnes * 100) / $j->joues;
	$j->justesse_perdus = $j->joues == 0 ? 0 : ($j->justesse_perdus * 100) / $j->joues;
	$j->justesse_xxx    = 100 - $j->justesse_gagnes - $j->justesse_perdus;

	$moy = 0;
	$nb  = 0;
	reset($j->evol_pourc_gagne);
	$q1 = "";
	$q2 = "";
	$q11 = "";
	$q22 = "";
	foreach($j->evol_pourc_gagne as $cle => $val)
	{
		if (strlen($val."") > 0) {
			$q1 .= ($q1 == "") ? $val : "§".$val;
			$q11 .= ($q11 == "") ? $val : ",".$val;
			$tmp = explode("-", $cle);
			$q2 .= ($q2 == "" ? "" : "§").$tmp[2]."-".$tmp[1]."-".substr($tmp[0], 2);
			$q22 .= ($q22 == "" ? "" : ",")."'".$tmp[2]."-".$tmp[1]."-".substr($tmp[0], 2)."'";
			$moy += $val;
			$nb++;
		}
	}

	// Si le joueur n'a joué aucun match pour l'instant
	if ($q1 == "")
	{
		$q1 = "-1§-1";
		$q2 = "0§0";
		$moy = 0;
	}
	else
	{
		$q1 = "-1§".$q1;
		$q2 = "0§".$q2;
		$moy = $nb > 0 ? round($moy / $nb) : 0;
	}

	$q11 = "[".$q11."]";
	$q22 = "[".$q22."]";

	$__width = 480;
	$__height= 220;

//	$j->evol = "<img src=\"./stats_player_perf.php?moy=".$moy."&chart=1&datas1=".$q1."&datas2=".$q2."&legendes=Pourcentage+victoires§Moyenne\" width=\"".$__width."\" height=\"".$__height."\" />";
}

$patronyme = $j->prenom." ".$j->nom;
$photo = Wrapper::formatPhotoJoueur($j->photo);
$user_linked = false;
$req = "SELECT u.* FROM jb_users u, jb_user_player up, jb_joueurs j WHERE j.id =".$idp." AND j.id = up.id_player AND up.id_user = u.id AND up.status=1";
$res = dbc::execSql($req);
if ($user = mysqli_fetch_array($res)) {
	$user_linked = true;
	$patronyme = $user['prenom']." ".$user['nom'];
	$photo = $user['photo'] == "" ? $photo : $user['photo'];
}


$title  = Wrapper::card_box_getH2Title(array("title" => "<span>".$patronyme."<br /><small> as ".$j->pseudo."</small></span>"));
$menu   = Wrapper::card_box_getIconButton(array("id" => "btediter", "icon" => "contacts", "label" => "Trombinoscope", "onclick" => "xx({action: 'stats', id:'main', url:'stats_player.php?trombinoscope=1&idp=".$idp."'});" ));
$content = '
<div class="mdl-grid">
	<div id="dashcounter" class="mdl-cell mdl-cell--12-col">
		<div class="btj2 button black '.Wrapper::getColorMedaille($j->medaille).'" style="width: auto;" onclick="mm({action:\'stats\', idp:".$idp."});">
			<img src="'.$photo.'" />
		</div>
	</div>
	<div id="dashcounter" class="mdl-cell mdl-cell--12-col">
		<button id="b1" class="button blue"><i class="material-icons">grid_on</i><div class="cnt">0</div><div class="txt">Saisons</div></button>
		<button id="b2" class="button blue" onclick="mm({action: \'players\'});"><i class="material-icons">person</i><div class="cnt">0</div><div class="txt">Joueurs</div></button>
		<button id="b3" class="button blue" onclick="mm({action: \'teams\'});"><i class="material-icons">people</i><div class="cnt">0</div class="box"><div class="txt">Equipes</div></button>
	</div>
</div>
';
// Card_box Billboard général
Wrapper::card_box_6c(array("id" => "billboard", "title" => $title, "menu" => $menu, "content" => $content));


$title  = Wrapper::card_box_getH2Title(array("title" => "Statistiques"));
$menu   = '';
$content = '
<div class="mdl-grid">
	<div id="diagram" class="mdl-cell mdl-cell--12-col"></div>
	<div class="mdl-cell mdl-cell--12-col">
		<div class="skills">
			<ul id="skills_list"></ul>
		</div>
	</div>
</div>
';
// Card_box Billboard général
Wrapper::card_box_6c(array("id" => "billboard", "title" => $title, "menu" => $menu, "content" => $content));

?>


<div id="jfiche" class="bartable compact">


<ul class="sidebar">
<? if ($sess_context->isUserConnected() && !$user_linked) { ?>
	<li class="mailme" id="mailme"><a href="#" onclick="go({action: 'mailme', id:'main', url:'contacter.php?type_mail=5&idp=<?= $idp ?>'});" id="sb_join2" class="ToolText" onmouseover="showtip('sb_join2');"><span>Demander rattachement</span></a></li>
<? } ?>
<? if ($sess_context->championnat['entity'] == "_NATIF_") { ?>
	<li><a id="sb_fb" onclick="google_tracking('facebook.com');" target="_blank" class="ToolText" onmouseover="showtip('sb_fb');" href="http://www.facebook.com/dialog/feed?
  app_id=107452429322746&
  link=http://www.jorkers.com&
  picture=http://www.jorkers.com/wrapper/img/logo.png&
  name=<?= utf8_encode("» Statistiques joueur : ".$patronyme) ?>&
  caption=<?= utf8_encode($libelle_genre[$sess_context->getTypeSport()]) ?> :: <?= utf8_encode(($sess_context->isTournoiXDisplay() ? "Tournoi " : "Championnat ").$sess_context->getChampionnatNom()) ?>&
  description=<?= utf8_encode("Jorkers.com, solution de gestion de championnats et tournois de sports individuels et collectifs") ?>&
  message=<?= utf8_encode("Laisser un message !") ?>&
  redirect_uri=http://www.jorkers.com/wrapper/fb.php?idc=<?= $sess_context->getRealChampionnatId()."_p".$idp ?>"><span>Publier sur Facebook</span></a></li>
<? } ?>
	<li><a href="#" onclick="mm({action: 'players'});" id="sb_back" class="swap ToolText" onmouseover="showtip('sb_back');"><span>Retour</span></a></li>
</ul>




<? if ($sess_context->isFreeXDisplay()) { ?>
	<div id="bloc1" class="dashcounter box-wrapper subbar">
		<div class="title">Forme</div><div class="title" style="width: 390px;">Morphologie</div>
		<button id="bf<?= $j->forme_indice ?>" class="button <?= Wrapper::getColorFromFormeIndice($j->forme_indice) ?>"><div class="box"><div class="txt">Forme actuelle</div></div></button>
		<button id="bf<?= $j->forme_last_indice ?>" class="button <?= Wrapper::getColorFromFormeIndice($j->forme_last_indice) ?>"><div class="box"><div class="txt">Dernière journée</div></div></button>
		<? if ($user_linked) { ?>
			<? if (Wrapper::isUserDataPublic($user)) { ?>
				<button id="b6" class="button <?= Wrapper::getColorFromSexeIndice($user['sexe']) ?>"><div class="box"><div class="cnt"><?= Wrapper::formatNumber(Toolbox::date2age($user['date_nais'])) ?></div><div class="txt">Ans</div></div></button>
				<button id="b7" class="button blue"><div class="box"><div class="cnt"><?= Wrapper::formatNumber($user['taille']) ?></div><div class="txt">Cm</div></div></button>
				<button id="b9" class="button blue"><div class="box"><div class="cnt"><?= Wrapper::formatNumber($user['poids']) ?></div><div class="txt">Kg</div></div></button>
			<? } else { ?>
				<button id="b6" class="button disable pucelock" onclick="alert('Information confidentielle !');"><div class="box"><div class="cnt">-</div><div class="txt">Ans</div></div></button>
				<button id="b7" class="button disable pucelock" onclick="alert('Information confidentielle !');"><div class="box"><div class="cnt">-</div><div class="txt">Cm</div></div></button>
				<button id="b9" class="button disable pucelock" onclick="alert('Information confidentielle !');"><div class="box"><div class="cnt">-</div><div class="txt">Kg</div></div></button>
			<? } ?>
		<? } else { ?>
			<button id="b6" class="button disable puceinfo" onclick="alert('Si vous êtes ce joueur, connectez-vous ou inscrivez-vous et demandez le rattachement au gestionnaire,\nvous pourrez ainsi compléter ces informations !');"><div class="box"><div class="cnt">-</div><div class="txt">Ans</div></div></button>
			<button id="b7" class="button disable puceinfo" onclick="alert('Si vous êtes ce joueur, connectez-vous ou inscrivez-vous et demandez le rattachement au gestionnaire,\nvous pourrez ainsi compléter ces informations !');"><div class="box"><div class="cnt">-</div><div class="txt">Cm</div></div></button>
			<button id="b9" class="button disable puceinfo" onclick="alert('Si vous êtes ce joueur, connectez-vous ou inscrivez-vous et demandez le rattachement au gestionnaire,\nvous pourrez ainsi compléter ces informations !');"><div class="box"><div class="cnt">-</div><div class="txt">Kg</div></div></button>
		<? } ?>
	</div>
<? } else { ?>
	<div id="bloc1" class="dashcounter box-wrapper subbar">
		<div class="title">Forme</div><div class="title" style="width: 390px;">Morphologie</div>
		<button id="bf<?= $j->forme_indice ?>" class="button <?= Wrapper::getColorFromFormeIndice($j->forme_indice) ?>"><div class="box"><div class="txt">Forme actuelle</div></div></button>
		<button id="bf<?= $j->forme_last_indice ?>" class="button <?= Wrapper::getColorFromFormeIndice($j->forme_last_indice) ?>"><div class="box"><div class="txt">Dernière journée</div></div></button>
		<? if ($user_linked) { ?>
			<? if (Wrapper::isUserDataPublic($user)) { ?>
				<button id="b6" class="button <?= Wrapper::getColorFromSexeIndice($user['sexe']) ?>"><div class="box"><div class="cnt"><?= Wrapper::formatNumber(Toolbox::date2age($user['date_nais'])) ?></div><div class="txt">Ans</div></div></button>
				<button id="b7" class="button blue"><div class="box"><div class="cnt"><?= Wrapper::formatNumber($user['taille']) ?></div><div class="txt">Cm</div></div></button>
				<button id="b9" class="button blue"><div class="box"><div class="cnt"><?= Wrapper::formatNumber($user['poids']) ?></div><div class="txt">Kg</div></div></button>
			<? } else { ?>
				<button id="b6" class="button disable pucelock" onclick="alert('Information confidentielle !');"><div class="box"><div class="cnt">-</div><div class="txt">Ans</div></div></button>
				<button id="b7" class="button disable pucelock" onclick="alert('Information confidentielle !');"><div class="box"><div class="cnt">-</div><div class="txt">Cm</div></div></button>
				<button id="b9" class="button disable pucelock" onclick="alert('Information confidentielle !');"><div class="box"><div class="cnt">-</div><div class="txt">Kg</div></div></button>
			<? } ?>
		<? } else { ?>
			<button id="b6" class="button disable puceinfo" onclick="alert('Si vous êtes ce joueur, connectez-vous ou inscrivez-vous et demandez le rattachement au gestionnaire,\nvous pourrez ainsi compléter ces informations !');"><div class="box"><div class="cnt">-</div><div class="txt">Ans</div></div></button>
			<button id="b7" class="button disable puceinfo" onclick="alert('Si vous êtes ce joueur, connectez-vous ou inscrivez-vous et demandez le rattachement au gestionnaire,\nvous pourrez ainsi compléter ces informations !');"><div class="box"><div class="cnt">-</div><div class="txt">Cm</div></div></button>
			<button id="b9" class="button disable puceinfo" onclick="alert('Si vous êtes ce joueur, connectez-vous ou inscrivez-vous et demandez le rattachement au gestionnaire,\nvous pourrez ainsi compléter ces informations !');"><div class="box"><div class="cnt">-</div><div class="txt">Kg</div></div></button>
		<? } ?>
	</div>
<? } ?>


<div class="dashcounter box-wrapper subbar" style="margin-top: 10px; min-height: 50px;"><div class="title">Performance</div><div id="perfgraph"></div></div>


<? if (!$sess_context->isFreeXDisplay()) { ?>
	<div class="box-wrapper subbar" style="margin-top: 10px; min-height: 50px;"><div class="title">Membre de l'équipe</div><div style="margin-top: 10px;">
	<?
		foreach($tab as $e) {
			echo "<button class=\"btj button ".($idp == $e['capitaine'] ? "blue" : ($idp == $e['adjoint'] ? "orange" : "gray"))."\" onclick=\"mm({action: 'stats', idt: ".$e['id']." });\">".$e['nom']."".($idp == $e['capitaine'] ? "<br /><div>Capitaine</div>" : ($idp == $e['adjoint'] ? "<br /><div>Adjoint</div>" : "" ))."</button>";
		}
	?>
	</div></div>
<? } ?>


<? if ($sess_context->isFreeXDisplay()) { ?>

	<div id="bloc2" class="dashcounter box-wrapper subbar">
		<div class="title" style="width: 655px;">Position</div>
		<button id="bf10" class="pourc button purple"><div class="box"><div class="cnt"><?= round($j->p_att) ?>%</div><div class="txt">Attaquant</div></div></button>
		<button id="bf99" class="pourc button purple"><div class="box"><div class="cnt"><?= round($j->p_def) ?>%</div><div class="txt">Défenseur</div></div></button>
		<button id="bf13" class="pourc button orange" onclick="alert('Nombre de fois 1er dans une journée');"><div class="box"><div class="cnt"><?= round($j->podium) ?></div><div class="txt">#1</div></div></button>
		<button id="bf13" class="pourc button orange" onclick="alert('Nombre de fois 2ième dans une journée');"><div class="box"><div class="cnt"><?= round($j->polidor) ?></div><div class="txt">#2</div></div></button>
		<button id="b12" class="button gray" style="text-align: center;" onclick="rmCN('jfiche', 'compact');"><div class="box"><div class="cnt">More</div></div></button>
		<button id="b13" class="button gray" style="text-align: center;" onclick="addCN('jfiche', 'compact');"><div class="box"><div class="cnt">Less</div></div></button>
	</div>
<? } ?>


<? if ($sess_context->getGestionSets()) { ?>
	<div id="bloc4" class="dashcounter box-wrapper subbar">
		<div class="title" style="width: 655px;">Sets</div>
		<button id="bf11" class="pourc button blue"><div class="box"><div class="cnt"><?= round($j->sets_gagnes) ?></div><div class="txt">Gagnés</div></div></button>
		<button id="bf12" class="pourc button orange"><div class="box"><div class="cnt"><?= round($j->sets_perdus) ?></div><div class="txt">Perdus</div></div></button>
		<button id="bf7" class="pourc button <?= $j->sets_diff >= 0 ? "green" : "red" ?>"><div class="box"><div class="cnt"><?= round($j->sets_diff) ?></div><div class="txt">Différence</div></div></button>
		<button class="button disable"></button>
		<button class="button disable"></button>
	</div>
<? } ?>


<? if (!$sess_context->isTournoiXDisplay()) { ?>

	<div id="bloc3" class="dashcounter box-wrapper subbar">
		<div class="title" style="width: 655px;">Equipes les + performantes (% matchs gagnés)</div>
<? $i = 1; foreach($best_teams as $t) { ?>
		<button class="pourc button purple" onclick="mm({action:'stats', idt:'<?= $t->id ?>'});"><div class="box"><div class="cnt"><?= $t->pourc_gagnes ?>%</div><div class="txt"><?= Wrapper::stringEncode4JS($t->nom) ?></div></div></button>
<? if ($i++ >= 5) break; } ?>
<? for($k=$i; $k<=5; $k++) { ?> <button class="button disable"></button><? } ?>

		<div class="title" style="width: 655px;">Equipes les + sur le terrain (% matchs gagnés)</div>
<? $i = 1; foreach($most_matchs as $t) { ?>
		<button class="pourc button orange" onclick="mm({action:'stats', idt:'<?= $t->id ?>'});"><div class="box"><div class="cnt"><?= $t->pourc_gagnes ?>%</div><div class="txt"><?= Wrapper::stringEncode4JS($t->nom) ?></div></div></button>
<? if ($i++ >= 5) break; } ?>
<? for($k=$i; $k<=5; $k++) { ?> <button class="button disable"></button><? } ?>
	</div>


<? if (false && $sess_context->getGestionFanny()) {
$tbody = ""; $thead = "";
$req = "SELECT m.fanny, m.nbset, DATE_FORMAT(j.date, '%d/%m/%y') date, e1.nom nom1, m.resultat resultat, e2.nom nom2 FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE m.fanny=1 AND e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND (e1.joueurs like '".$idp."|%' OR e1.joueurs like '%|".$idp."|%' OR e1.joueurs like '%|".$idp."' OR e2.joueurs like '".$idp."|%' OR e2.joueurs like '%|".$idp."|%' OR e2.joueurs like '%|".$idp."') AND m.id_champ=".$sess_context->getChampionnatId()." ORDER BY date DESC LIMIT 0,4";
$res = dbc::execSql($req);
$i = 1; while ($row = mysqli_fetch_array($res))
	$tbody .= '<tr><td class="c1"><div>'.$i++.'</div></td><td class="c2"><div>'.$row['nom1'].'</div></td><td class="c3"><div>'.Wrapper::formatScore($row).'</div></td><td class="c4"><div>'.$row['nom2'].'</div></td><td class="c5"><div>'.$row['date'].'</div></td></tr>';
?>
<br />
<div id="box" class="classic">
<h2 class="grid tables">Fannys <button class="button gray right right" onclick="mm({action: 'fannys', idp: <?= $idp ?>});">Tous les fannys</button></h2>
<table cellspacing="0" cellpadding="0" class="jkgrid fannys" id="matches">
<thead><tr><th class="c1"><div>N°</div></th><th class="c2"><div>Equipe1</div></th><th class="c3"><div>Score</div></th><th class="c4"><div>Equipe2</div></th><th class="c5"><div>&nbsp;</div></th></tr></thead>
<tbody><?= $tbody ?></tbody>
</table>
</div>
<? } ?>


<? } ?>

<? // Raphel composnent => code include in component.js + raphael.min.js ?>
<script>
<? if ($sess_context->getGestionFanny()) { ?>
o.init({ name: 'diagram', skills_list: 'skills_list', size: 340, cc_size: 45, rad: 25, data: [ { rs: 180+(<?= round($j->pourc_joues)/2 ?>*3.6), v: <?= round($j->pourc_joues) ?>, t: 'Matchs joués', c: "#ED0086" }, { rs: 180+(<?= round($j->pourc_gagnes)/2 ?>*3.6), v: <?= round($j->pourc_gagnes) ?>, t: 'Matchs gagnés', c: "#08A7DC" }, { rs: 270+(<?= round($j->justesse_gagnes) ?>*3.6), v: [<?= round($j->justesse_gagnes) ?>,<?= round($j->justesse_perdus) ?>], t: ['Gagnés de\n justesse', 'Perdus de\n justesse'], c: ["#FFBF67","#FF6F67"] }, { rs: 90+(<?= round($j->fanny_in) ?>*3.6), v: [<?= round($j->fanny_in) ?>, <?= round($j->fanny_out) ?>], t: ['Fannys pris','Fannys donnés'], c: [{bg:"#FFE700", fg:"#6B6000"}, {bg:"#AFF53D",fg:"#496619"}] } ] });
<? } else { ?>
o.init({ name: 'diagram', skills_list: 'skills_list', size: 340, cc_size: 45, rad: 25, data: [ { rs: 180+(<?= round($j->pourc_joues)/2 ?>*3.6), v: <?= round($j->pourc_joues) ?>, t: 'Matchs joués', c: "#ED0086" }, { rs: 180+(<?= round($j->pourc_gagnes)/2 ?>*3.6), v: <?= round($j->pourc_gagnes) ?>, t: 'Matchs gagnés', c: "#08A7DC" }, { v: <?= round($j->justesse_gagnes) ?>, t: 'Gagnés de\n justesse', c: "#FFE700" }, { v: <?= round($j->justesse_perdus) ?>, t: 'Perdus de\n justesse', c: "#AFF53D" } ] });
<? } ?>
drawAnalytics({ name: "perfgraph", width: 670, height: 200, avg: <?= $moy == 0 ? "0.1" : $moy ?>, labels: <?= $q22 ?>, data: <?= $q11 ?>, overmax: 100 });
</script>


</div>