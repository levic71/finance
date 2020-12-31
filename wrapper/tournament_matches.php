<?

require_once "../include/sess_context.php";

session_start();

require_once "common.php";
require_once "../include/inc_db.php";
require_once "../www/ManagerFXList.php";
require_once "../www/StatsBuilder.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$idj = Wrapper::getRequest('idj', 0);
if (strstr($idj, '|')) { $j = Wrapper::getPrevNextJournees($idj); if ($j == 0) $idj = 0; else { $idj = $j['id']; $date = ToolBox::mysqldate2date($j['date']); $name = $j['nom']; } }

$sess_context->setJourneeId($idj);

// Quel est le type de données que l'on va afficher : matchs de poules, de phase final ?
// P pour poule, F pour phase finale, SP pour syntèse poules
$options_type_matchs = isset($options_type_matchs) ? $options_type_matchs : ($sess_context->championnat['option_display_all_matchs'] == 1 ? "AM|0" : "P|1");

$items = explode('|', $options_type_matchs);
$type_matchs = $items[0];
$niveau_type = isset($items[1]) ? $items[1] : "";

// On récupère les infos de la journée
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$row = $sjs->getJournee();

$is_journee_alias = $sjs->isJourneeAlias($row);

// Si ce n'est pas une journee alias, on regarde si cette journee possède des alias
$all_alias = $sjs->getAllAliasJournee($is_journee_alias ? $row['id_journee_mere'] : "");

$nom_journee = $row['nom'];
$date_journee = $row['date'];
$exclude_matchs_journee = "";

if ($is_journee_alias)
{
	$tmp = explode('|', $row['id_matchs']);
	$matchs_journee = isset($tmp[1]) ? $tmp[1] : "";
	$journee_mere = $sjs->getJournee($row['id_journee_mere']);
	$id_journee_mere = $row['id_journee_mere'];
	$nb_poules    = $journee_mere['tournoi_nb_poules'];
	$phase_finale = $journee_mere['tournoi_phase_finale'];
	$consolante   = $journee_mere['tournoi_consolante'];
	$equipes_journee = $journee_mere['equipes'];
	$liste_poules = explode('|', $journee_mere['equipes']);
}
else
{
	$tmp = explode('|', $row['id_matchs']);
	$exclude_matchs_journee = isset($tmp[1]) ? $tmp[1] : "";
	$nb_poules    = $row['tournoi_nb_poules'];
	$phase_finale = $row['tournoi_phase_finale'];
	$consolante   = $row['tournoi_consolante'];
	$equipes_journee = $row['equipes'];
	$liste_poules = explode('|', $row['equipes']);
}

// Formatage du champs équipes pour prendre en compte les poules et les phases finales
$all_equipes = "";
$nb_equipes  = 0;

if ($type_matchs == "P")
{
	// Recherche de la poule à afficher
	$all_equipes = isset($liste_poules[$niveau_type-1]) ? $liste_poules[$niveau_type-1] : "";
}
else
{
	// Mise à plat du champ 'equipes' pour récupérer toutes les équipes sans distinction de poules
	$tmp = str_replace('|', ',', $equipes_journee);
	$items = explode(',', $tmp);
	foreach($items as $item)
		if ($item != "") $all_equipes .= $all_equipes == "" ? $item : ",".$item;
}

$classement_equipes = array();
$equipes = array();

// Recherche des équipes et création de stats vierge
if ($type_matchs == "SP")
{
	reset($liste_poules);
	while(list($cle, $equipes_poules) = each($liste_poules))
	{
		// On récupères les infos des equipes (avec init classement vierge si besoin)
		if ($equipes_poules != "")
		{
			$num_poule = $cle + 1;
			$classement_equipes[$num_poule] = "";
			$req = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id IN (".SQLServices::cleanIN($equipes_poules).") ORDER BY nom ASC";
			$res = dbc::execSql($req);
			while($eq = mysqli_fetch_array($res))
			{
				if ($classement_equipes[$num_poule] != "") $classement_equipes[$num_poule] .= "|";
				$equipes[$num_poule][$eq['id']] = $eq['nom'];
				$classement_equipes[$num_poule] .= $eq['id']."@".StatJourneeTeam::vierge();
				$nb_equipes++;
			}
		}
	}
} else if ($all_equipes != "") {
  	// On récupères les infos des equipes (avec init classement vierge si besoin)
	$classement_equipes[$niveau_type] = "";
	$req = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id IN (".SQLServices::cleanIN($all_equipes).") ORDER BY nom ASC";
	$res = dbc::execSql($req);
	while($eq = mysqli_fetch_array($res))
	{
		if ($classement_equipes[$niveau_type] != "") $classement_equipes[$niveau_type] .= "|";
		$equipes[$niveau_type][$eq['id']] = $eq['nom'];
		$classement_equipes[$niveau_type] .= $eq['id']."@".StatJourneeTeam::vierge();
		$nb_equipes++;
	}
}

// On essaie de trouver le classement des poules
$req = "SELECT * FROM jb_classement_poules WHERE id_champ=".$sess_context->getChampionnatId()." AND id_journee=".($is_journee_alias ? $id_journee_mere : $sess_context->getJourneeId());
$res = dbc::execSql($req);
while($stat_poule = mysqli_fetch_array($res))
{
	$niveau_poule = explode('|', $stat_poule['poule']);
	$classement_equipes[$niveau_poule[1]] = $stat_poule['classement_equipes'];
}

?>

<ul class="sidebar">
<? if ($sess_context->isAdmin() && $type_matchs != "X" && $type_matchs != "SP") { ?>
	<? if (!$is_journee_alias && $type_matchs == "P") { ?><li><a href="#" onclick="ajouter_match('<?= $options_type_matchs ?>');" id="new" class="ToolText" onmouseover="showtip('new');"><span>Ajouter un match</span></a></li><? } ?>
	<? if ($sess_context->isAdmin() && $type_matchs == "C") { ?>
		<li><a href="#" onclick="ajouter_match_barrage('C|-1');" id="newbarrage" class="ToolText" onmouseover="showtip('newbarrage');"><span>Ajouter un match de barrage</span></a></li>
	<? } ?>
<? } ?>

<? if ($sess_context->isAdmin()) { ?>
	<li><a href="#" onclick="go({action: 'days', id:'main', url:'edit_days.php?idd=<?= $idj ?>'});" id="sb_upd" class="ToolText" onmouseover="showtip('sb_upd');"><span>Modifier journée</span></a></li>
	<li><a href="#" onclick="synchro();" id="sb_sync" class="ToolText" onmouseover="showtip('sb_sync');"><span>Synchronisation joueurs/equipes/matchs</span></a></li>
	<li><a href="#" onclick="go({action: 'days', id:'main', url:'edit_days_do.php?del=0&idd=<?= $idj ?>', confirmdel:'1'});" id="sb_del" class="ToolText" onmouseover="showtip('sb_del');"><span>Supprimer journée</span></a></li>
<? } ?>

<? if ($sess_context->championnat['entity'] == "_NATIF_") { ?>
	<li class="fb"><a id="sb_fb" onclick="google_tracking('facebook.com');" target="_blank" class="ToolText" onmouseover="showtip('sb_fb');" href="http://www.facebook.com/dialog/feed?
		app_id=107452429322746&
		link=http://www.jorkers.com/wrapper/jk.php?idc=<?= $sess_context->getRealChampionnatId() ?>_<?= $idj ?>_<?= $date ?>_<?= $name ?>&
		picture=http://www.jorkers.com/wrapper/img/logo.png&
		name=<?= utf8_encode("» ".$label2." : Résultats") ?>&
  		caption=<?= utf8_encode($libelle_genre[$sess_context->getTypeSport()]) ?> :: <?= utf8_encode(($sess_context->isTournoiXDisplay() ? "Tournoi " : "Championnat ").$sess_context->getChampionnatNom()) ?>&
		description=<?= utf8_encode("Jorkers.com, solution de gestion de championnats et tournois de sports individuels et collectifs") ?>&
		message=<?= utf8_encode("Laisser un message !") ?>&
		redirect_uri=http://www.jorkers.com/wrapper/jk.php?idc=<?= $sess_context->getRealChampionnatId() ?>_<?= $idj ?>_<?= $date ?>_<?= $name ?>">
		<span>Publier sur son facebook</span>
	</a></li>
	<li><a href="#" onclick="mm({action: 'days'});" id="sb_back" class="swap ToolText" onmouseover="showtip('sb_back');"><span>Retour</span></a></li>
<? } ?>

</ul>

<h2 class="grid matches matches">
	<div class="daycontrol">
		<div class="dayprev"><img class="bt" src="img/icons/dark/appbar.navigate.previous.png" onclick="gotournoi('<?= $type_matchs == "P" || ($type_matchs == "Y" && $consolante > 0) ? "P|1" : $type_matchs ?>', '|prev');" /></div>
		<a href="#"><div class="daytitle"><?= Toolbox::mysqldate2date($date_journee).': '.ToolBox::conv_lib_journee($nom_journee) ?></div></a>
		<div class="daynext"><img class="bt" src="img/icons/dark/appbar.navigate.next.png" onclick="gotournoi('<?= $type_matchs == "P" || ($type_matchs == "Y" && $consolante > 0) ? "P|1" : $type_matchs ?>', '|next');" /></div>
	</div>
</h2>

<div class="choices grouped" id="tournoi_icons">
<? reset($libelle_phase_finale);
for($i=1; $i <= $nb_poules; $i++) { ?>
<button title="Groupe <?= $i ?>" class="button <?= $type_matchs == "P" && $niveau_type == $i ? "blue" : "gray" ?>" onclick="gophase('P|<?= $i ?>');"><?= ($nb_poules > 8 ? "<small>G" : ($nb_poules > 6 ? "Gr " : "Groupe ")).($sess_context->championnat['option_poule_lettre'] == 1 ? chr(ord('A')+$i-1) : $i).($nb_poules > 8 ? "</small>" : "") ?></button>
<? } ?>
<? if ($nb_poules > 1) { ?><button title="Synthèse groupe" class="button <?= $type_matchs == "SP" ? "blue" : "gray" ?>" onclick="gophase('SP|0');" style="padding: 4px 10px !important;"><img src="img/google/grid.png" /></button><? } ?>
<button title="Phase finale" class="button <?= $type_matchs == "F" ? "blue" : "gray" ?>" onclick="gophase('F|<?= _PHASE_PLAYOFF_ ?>');" style="padding: 4px 10px !important;"><img src="img/trophy_30.png" /></button>
<? if ($consolante > 0) { ?><button title="Consolante" class="button <?= $type_matchs == "Y" ? "blue" : "gray" ?>" onclick="gophase('Y|<?= _PHASE_CONSOLANTE2_ ?>');" style="padding: 4px 10px !important;"><img src="img/red_cross_30.png" /></button><? } ?>
<button title="Matchs de classement/Barrages" class="button <?= $type_matchs == "C" ? "blue" : "gray" ?>" onclick="gophase('C|<?= _PHASE_CONSOLANTE1_ ?>');" style="padding: 4px 10px !important;"><img src="img/google/pyramid1.png" /></button>
<button title="Bonus/Malus" class="button <?= $type_matchs == "B" ? "blue" : "gray" ?>" onclick="gophase('B');" style="padding: 4px 10px !important;"><img src="img/plusminus.png" /></button>
<button title="Classement" class="button <?= $type_matchs == "X" ? "blue" : "gray" ?>" onclick="gophase('X|0');" style="padding: 4px 10px !important;"><img src="img/google/star.png" /></button>
</div>

<?

// Init tables
$tbody = ""; $thead = "";


// ///////////////////////////////////////////////////////////
// BONUS/MALUS
// ///////////////////////////////////////////////////////////
if ($type_matchs == "B")
{
	$thead = "<tr><th class=\"c1\"><div><a href=\"#\">N°</a></div></th><th class=\"c2\"><div><a href=\"#\">Equipe</a></div></th><th class=\"c3\"><div><a href=\"#\"><img src=\"img/plusminus.png\" /></a></div></th></tr>";

	// Récupération des équipes
	$type_matchs = "";
	if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
	{
		// Formatage du champs équipes pour ne prendre que les equipes de poules pour les poules et toutes équipes pour la phase finale
		$equipes = "";

		$tmp = str_replace('|', ',', $row['equipes']);
		$items = explode(',', $tmp);
		foreach($items as $item)
			if ($item != "") $equipes .= $equipes == "" ? $item : ",".$item;
	}
	else
		$equipes = $row['equipes'];

	// Récupération des bonus
	$tab_bonus = array();
	if ($row['bonus'] != "") $tab_bonus = explode(',', $row['bonus']);

	$bonus = array();
	foreach($tab_bonus as $item)
	{
		$x = explode('=', $item);
		$bonus[$x[0]] = $x[1];
	}

	// Récupération des infos des equipes
	$ses = new SQLEquipesServices($sess_context->getRealChampionnatId());
	$equipes_infos = $ses->getListeEquipes($equipes);

	$lst_equipes = array();
	if ($equipes != "") $lst_equipes = explode(',', $equipes);

	$i = 0;
	foreach($lst_equipes as $item)
	{
		$default = isset($bonus[$item]) ? $bonus[$item] : 0;
		$tbody .= "<tr id=\"tr_".$i++."\"><td class=\"c1\"><div>".$i."</div></td><td class=\"c2\"><div>".$equipes_infos[$item]['nom']."</div></td><td class=\"c3\"><div><button class=\"button orange\" name=\"bonus\" id=\"bonus_".$item."\"".($sess_context->isAdmin() ? " onclick=\"minicalc.picker({ name: 'bonus_".$item."', start: -100, end: 100 });\">" : ">").$default."</button></div></td></tr>";
	}
?>
	<h2 class="grid leagues">Bonus/Malus <? if ($sess_context->isAdmin()) { ?><button style="float: right; margin: 6px;" onclick="set_bonus();" class="button green"><span>Valider</span></button><? } ?></h2>
<?
}



// ///////////////////////////////////////////////////////////
// MATCHS DE CLASSEMENT + BARRAGE
// ///////////////////////////////////////////////////////////
if ($type_matchs == "C")
{
	$thead = "<tr><th class=\"c1\"><div>Enjeu</div></th><th class=\"c2\"><div><a href=\"#\">Equipe 1</a></div></th><th class=\"c3\"><div><a href=\"#\">Score</a></div></th><th class=\"c4\"><div><a href=\"#\">Equipe 2</a></div></th>";
	if ($sess_context->isAdmin()) {
		$thead .= '<th class="edit"><div>&nbsp;</div></th><th class="edit"><div>&nbsp;</div></th><th class="edit"><div>&nbsp;</div></th>';
	}
	$thead .= "</tr>";

	$fxlist = new FXListMatchsClassementTournoiIII($sess_context->getChampionnatId(), $is_journee_alias ? $id_journee_mere : $sess_context->getJourneeId(), isset($equipes[$niveau_type]) ? count($equipes[$niveau_type]) : 0, $sess_context->isAdmin(), "AND niveau like 'C|%'", $consolante, $phase_finale);
	$i = 1;
//	print_r($fxlist->body->tab);
	while(list($cle, $item) = each($fxlist->body->tab)) {

		if ($sess_context->isAdmin()) {
			if (str_replace('<FONT CLASS="equipe_gagne">', '', str_replace('</FONT>', '', $item['nom1'])) == "-") {
				$item['match_id'] = -1;
			} else {
				$tmp = explode('\'', $item['action']);
				$item['match_id'] = str_replace("+WHERE+id%3D", "", $tmp[7]);
			}
		}

		// On remet les données "propres" (sans le html)
		$item['resultat'] = $item['res2'];

		// Choix du vainqueur pour les matches
		$vainqueur = StatsJourneeBuilder::kikiGagne($item);

		$resultat = Wrapper::formatScore($item);
		$item['div_nom1'] = "<div class=\"".($vainqueur == 2 ? "equipe_perdu" : "equipe_gagne").(sess_context::getGestionFanny() == 1 && $item['fanny'] == 1 ? " fanny" : "")."\">".$item['nom1']."</div>";
		$item['div_nom2'] = "<div class=\"".($vainqueur == 1 ? "equipe_perdu" : "equipe_gagne").(sess_context::getGestionFanny() == 1 && $item['fanny'] == 1 ? " fanny" : "")."\">".$item['nom2']."</div>";

		$visible = isset($item[10]) ? "" : "style=\"visibility: hidden;\"";
		$iconupd = "pencil_16.png";

		if (!isset($item['niveau'])) $item['niveau'] = '';
		if (!isset($item['nbset'])) $item['nbset'] = '1';

		$tbody .= "<tr id=\"tr_".$i."\"><td class=\"c1\"><div>".$item['niveau']."</div></td><td class=\"c2\"><div>".$item['div_nom1']."</div></td><td class=\"c3\"><div>".$resultat."</div></td><td class=\"c4\"><div>".$item['div_nom2']."</div></td>";
		if ($sess_context->isAdmin()) {
			$args = "?action=matches&idm=".$item['match_id']."&niveau=".(isset($item['master_niveau']) ? $item['master_niveau'] : $cle)."&idj=".$sess_context->getJourneeId()."&name=".$nom_journee."&date=".$date_journee;
			$tbody .= '<td class="edit" id="play"><div><a href="#" title="Saisie Live" '.$visible.' onclick="liveScoring('.$item['match_id'].', \''.(isset($item[10]) ? $item[10] : "-").'\', \''.(isset($item[12]) ? $item[12] : "-").'\', \''.$item['nbset'].'\', \''.$item['resultat'].'\');" class="full-circle"><img src="img/play_16.png" /></a></div></td>';
			$tbody .= '<td class="edit" id="upd"><div><a href="#" title="Editer" onclick="go({action: \'matches\', id:\'main\', url:\'edit_matches.php'.$args.'\'});" class="full-circle"><img src="img/'.$iconupd.'" /></a></div></td>';
			$tbody .= '<td class="edit" id="del"><div><a href="#" title="Supprimer" '.$visible.' onclick="go({action: \'matches\', id:\'main\', url:\'edit_matches_do.php'.$args.'\', confirmdel:\'1\'});" class="full-circle"><img src="img/trash_16.png" /></a></div></td>';
		}
		$tbody .= "</tr>";
	}
}


// ///////////////////////////////////////////////////////////
// MATCHS PHASE FINALE
// ///////////////////////////////////////////////////////////
if ($type_matchs == "F")
{
	$thead = "<tr><th class=\"c1\"><div>Enjeu</div></th><th class=\"c2\"><div><a href=\"#\">Equipe 1</a></div></th><th class=\"c3\"><div><a href=\"#\">Score</a></div></th><th class=\"c4\"><div><a href=\"#\">Equipe 2</a></div></th>";
	if ($sess_context->isAdmin()) {
		$thead .= '<th class="edit"><div>&nbsp;</div></th><th class="edit"><div>&nbsp;</div></th><th class="edit"><div>&nbsp;</div></th>';
	}
	$thead .= "</tr>";

	$fxlist1 = new FXListMatchsPlayOff($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $phase_finale, $sess_context->isAdmin(), $type_matchs);
	$i = 1;
//	print_r(array_reverse($fxlist1->body->tab));
	foreach(array_reverse($fxlist1->body->tab) as $item) {

		if ($item == _FXLINESEPARATOR_) continue;
		if (!isset($item['match_id'])) $item['match_id'] = -1;

		// On remet les données "propres" (sans le html)


		$item['nom1'] = isset($item[6]) && !isset($item['nom1']) ? $item[6] : str_replace('<FONT CLASS="equipe_gagne">', '', str_replace('</FONT>', '', $item['nom1'])); // Astuce
		$item['nom2'] = isset($item[8]) && !isset($item['nom2']) ? $item[8] : str_replace('<FONT CLASS="equipe_gagne">', '', str_replace('</FONT>', '', $item['nom2'])); // Astuce
		$item['resultat'] = $item['res2'];

		// Choix du vainqueur pour les matches
		$vainqueur = StatsJourneeBuilder::kikiGagne($item);

		$resultat = Wrapper::formatScore($item);
		$item['div_nom1'] = "<div class=\"".($vainqueur == 2 ? "equipe_perdu" : "equipe_gagne").(sess_context::getGestionFanny() == 1 && $item['fanny'] == 1 ? " fanny" : "")."\">".$item['nom1']."</div>";
		$item['div_nom2'] = "<div class=\"".($vainqueur == 1 ? "equipe_perdu" : "equipe_gagne").(sess_context::getGestionFanny() == 1 && $item['fanny'] == 1 ? " fanny" : "")."\">".$item['nom2']."</div>";

		$visible = $item['nom1'] != "-" ? "" : "style=\"visibility: hidden;\"";
		$iconupd = "pencil_16.png";

		$tbody .= "<tr id=\"tr_".$i."\"><td class=\"c1\"><div>".$item['libelle_niveau']."</div></td><td class=\"c2\"><div>".$item['div_nom1']."</div></td><td class=\"c3\"><div>".$resultat."</div></td><td class=\"c4\"><div>".$item['div_nom2']."</div></td>";
		if ($sess_context->isAdmin()) {
			$args = "?action=matches&idm=".$item['match_id']."&niveau=".$item['niveau']."&idj=".$sess_context->getJourneeId()."&name=".$nom_journee."&date=".$date_journee;
			$tbody .= '<td class="edit" id="play"><div><a href="#" title="Saisie Live" '.$visible.' onclick="liveScoring('.$item['match_id'].', \''.$item['nom1'].'\', \''.$item['nom2'].'\', \''.$item['nbset'].'\', \''.$item['resultat'].'\');" class="full-circle"><img src="img/play_16.png" /></a></div></td>';
			$tbody .= '<td class="edit" id="upd"><div><a href="#" title="Editer" onclick="go({action: \'matches\', id:\'main\', url:\'edit_matches.php'.$args.'\'});" class="full-circle"><img src="img/'.$iconupd.'" /></a></div></td>';
			$tbody .= '<td class="edit" id="del"><div><a href="#" title="Supprimer" '.$visible.' onclick="go({action: \'matches\', id:\'main\', url:\'edit_matches_do.php'.$args.'\', confirmdel:\'1\'});" class="full-circle"><img src="img/trash_16.png" /></a></div></td>';
		}
	}

	$fxlist2 = new FXListMatchsPlayOffIII($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $phase_finale, $sess_context->isAdmin(), $type_matchs);
	echo "<div class=\"pyramid ".(($type_matchs == "F" && $phase_finale >= 8) ? (($type_matchs == "F" && $phase_finale >= 16) ? "highcompact" : "compact") : "")."\">";
?>
	<h2 class="grid leagues">Phase finale<button style="float: right; margin: 6px;" onclick="toogle('box');" class="button green"><span>Mode liste</span></button></h2>
<?
	echo  str_replace('height="460"', 'height="760"', str_replace('height="230"', 'height="360"', str_replace('height="115"', 'height="175"', str_replace('height="60"', 'height="90"', str_replace('width="31"', '', str_replace('width="15"', '', $fxlist2->body->tab[0][0]))))))."</div>";
}



// ///////////////////////////////////////////////////////////
// MATCHS CONSOLANTE
// ///////////////////////////////////////////////////////////
if ($type_matchs == "Y" && $consolante > 0)
{
	$thead = "<tr><th class=\"c1\"><div>Enjeu</div></th><th class=\"c2\"><div><a href=\"#\">Equipe 1</a></div></th><th class=\"c3\"><div><a href=\"#\">Score</a></div></th><th class=\"c4\"><div><a href=\"#\">Equipe 2</a></div></th>";
	if ($sess_context->isAdmin()) {
		$thead .= '<th class="edit"><div>&nbsp;</div></th><th class="edit"><div>&nbsp;</div></th><th class="edit"><div>&nbsp;</div></th>';
	}
	$thead .= "</tr>";

	$fxlist1 = new FXListMatchsPlayOff($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $consolante, $sess_context->isAdmin(), $type_matchs);
	$i = 1;
//	print_r(array_reverse($fxlist1->body->tab));
	foreach(array_reverse($fxlist1->body->tab) as $item) {

		if ($item == _FXLINESEPARATOR_) continue;

		if (!isset($item['match_id']))
		{
			$item['match_id'] = -1;
		}

		// On remet les données "propres" (sans le html)
		$item['nom1'] = isset($item[9])  ? $item[9]  : str_replace('<FONT CLASS="equipe_gagne">', '', str_replace('</FONT>', '', $item['nom1'])); // Astuce
		$item['nom2'] = isset($item[11]) ? $item[11] : str_replace('<FONT CLASS="equipe_gagne">', '', str_replace('</FONT>', '', $item['nom2'])); // Astuce
		$item['resultat'] = $item['res2'];

		// Choix du vainqueur pour les matches
		$vainqueur = StatsJourneeBuilder::kikiGagne($item);

		$resultat = Wrapper::formatScore($item);
		$item['div_nom1'] = "<div class=\"".($vainqueur == 2 ? "equipe_perdu" : "equipe_gagne").(sess_context::getGestionFanny() == 1 && $item['fanny'] == 1 ? " fanny" : "")."\">".$item['nom1']."</div>";
		$item['div_nom2'] = "<div class=\"".($vainqueur == 1 ? "equipe_perdu" : "equipe_gagne").(sess_context::getGestionFanny() == 1 && $item['fanny'] == 1 ? " fanny" : "")."\">".$item['nom2']."</div>";

		$visible = $item['nom1'] != "-" ? "" : "style=\"visibility: hidden;\"";
		$iconupd = "pencil_16.png";

		$tbody .= "<tr id=\"tr_".$i."\"><td class=\"c1\"><div>".$item['libelle_niveau']."</div></td><td class=\"c2\"><div>".$item['div_nom1']."</div></td><td class=\"c3\"><div>".$resultat."</div></td><td class=\"c4\"><div>".$item['div_nom2']."</div></td>";
		if ($sess_context->isAdmin()) {
			$args = "?action=matches&idm=".$item['match_id']."&niveau=".$item['niveau']."&idj=".$sess_context->getJourneeId()."&name=".$nom_journee."&date=".$date_journee;
			$tbody .= '<td class="edit" id="play"><div><a href="#" title="Saisie Live" '.$visible.' onclick="liveScoring('.$item['match_id'].', \''.$item['nom1'].'\', \''.$item['nom2'].'\', \''.$item['nbset'].'\', \''.$item['resultat'].'\');" class="full-circle"><img src="img/play_16.png" /></a></div></td>';
			$tbody .= '<td class="edit" id="upd"><div><a href="#" title="Editer" onclick="go({action: \'matches\', id:\'main\', url:\'edit_matches.php'.$args.'\'});" class="full-circle"><img src="img/'.$iconupd.'" /></a></div></td>';
			$tbody .= '<td class="edit" id="del"><div><a href="#" title="Supprimer" '.$visible.' onclick="go({action: \'matches\', id:\'main\', url:\'edit_matches_do.php'.$args.'\', confirmdel:\'1\'});" class="full-circle"><img src="img/trash_16.png" /></a></div></td>';
		}
	}

	$fxlist2 = new FXListMatchsPlayOffIII($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $consolante, $sess_context->isAdmin(), $type_matchs);
	echo "<div class=\"pyramid ".(($type_matchs == "Y" && $consolante >= 8) ? (($type_matchs == "Y" && $consolante >= 16) ? "highcompact" : "compact") : "")."\">";
?>
	<h2 class="grid leagues">Consolante<button style="float: right; margin: 6px;" onclick="toogle('box');" class="button green"><span>Mode liste</span></button></h2>
<?
	echo $fxlist2->body->tab[0][0]."</div>";
}



// ///////////////////////////////////////////////////////////
// MATCHS POULES
// ///////////////////////////////////////////////////////////
if ($type_matchs == "P")
{
	$filtre_niveau     = " AND niveau='".$options_type_matchs."'";
//	$filtre_matchs_in  = ($is_journee_alias && $matchs_journee != "" ? " AND m.id IN (".SQLServices::cleanIN($matchs_journee).") " : "");
	$filtre_matchs_in  = "";
//	if ($is_journee_alias && $filtre_matchs_in == "") $filtre_matchs_in = "AND m.id IN (-1) ";
//	$filtre_matchs_out = ($exclude_matchs_journee == "" ? "" : " AND m.id NOT IN (".SQLServices::cleanIN($exclude_matchs_journee).")");
	$filtre_matchs_out  = "";
	$filtre = $filtre_niveau.$filtre_matchs_in.$filtre_matchs_out;


	$thead = "<tr><th class=\"c1\"><div>N°</div></th><th class=\"c2\"><div><a href=\"#\">Equipe 1</a></div></th><th class=\"c3\"><div><a href=\"#\">Score</a></div></th><th class=\"c4\"><div><a href=\"#\">Equipe 2</a></div></th>";
	if ($sess_context->isAdmin()) {
		$thead .= '<th class="edit"><div>&nbsp;</div></th><th class="edit"><div>&nbsp;</div></th><th class="edit"><div>&nbsp;</div></th>';
	}
	$thead .= "</tr>";

	$fxlist = new FXListMatchsPoules($sess_context->getChampionnatId(), $is_journee_alias ? $id_journee_mere : $sess_context->getJourneeId(), $sess_context->isAdmin() && false, $filtre);
	$i = 1;
	foreach($fxlist->body->tab as $item) {

		// On remet les données "propres" (sans le html)
		$item['resultat'] = $item[10];
		$item['nom1'] = $item[9];
		$item['nom2'] = $item[11];

		// Choix du vainqueur pour les matches
		$vainqueur = StatsJourneeBuilder::kikiGagne($item);

		$resultat = Wrapper::formatScore($item);
		$item['nom1'] = "<div class=\"".($vainqueur == 2 ? "equipe_perdu" : "equipe_gagne").(sess_context::getGestionFanny() == 1 && $item['fanny'] == 1 ? " fanny" : "")."\">".$item['nom1']."</div>";
		$item['nom2'] = "<div class=\"".($vainqueur == 1 ? "equipe_perdu" : "equipe_gagne").(sess_context::getGestionFanny() == 1 && $item['fanny'] == 1 ? " fanny" : "")."\">".$item['nom2']."</div>";

		$tbody .= "<tr id=\"tr_".$i."\"><td class=\"c1\"><div>".$i++."</div></td><td class=\"c2\"><div>".$item['nom1']."</div></td><td class=\"c3\"><div>".$resultat."</div></td><td class=\"c4\"><div>".$item['nom2']."</div></td>";
		if ($sess_context->isAdmin()) {
			$args = "?action=matches&idm=".$item['match_id']."&niveau=".$item['niveau']."&idj=".$sess_context->getJourneeId()."&name=".$nom_journee."&date=".$date_journee;
			$tbody .= '<td class="edit" id="play"><div><a href="#" title="Saisie Live" onclick="liveScoring('.$item['match_id'].', \''.$item[9].'\', \''.$item[11].'\', \''.$item['nbset'].'\', \''.$item[10].'\');" class="full-circle"><img src="img/play_16.png" /></a></div></td>';
			$tbody .= '<td class="edit" id="upd"><div><a href="#" title="Editer" onclick="go({action: \'matches\', id:\'main\', url:\'edit_matches.php'.$args.'\'});" class="full-circle"><img src="img/pencil_16.png" /></a></div></td>';
			$tbody .= '<td class="edit" id="del"><div><a href="#" title="Supprimer" onclick="go({action: \'matches\', id:\'main\', url:\'edit_matches_do.php'.$args.'\', confirmdel:\'1\'});" class="full-circle"><img src="img/trash_16.png" /></a></div></td>';
		}
		$tbody .= "</tr>";
	}
}


// ///////////////////////////////////////////////////////////
// SYNTHESE POULES
// ///////////////////////////////////////////////////////////
if ($type_matchs == "SP") {

	echo "<table border=\"0\" width=\"100%\" cellspacing=\"2\" cellpadding=\"0\">";
	$i = 0;
	$nb_poules = 3;
	reset($classement_equipes);
	while(list($cle, $classement) = each($classement_equipes))
	{
		if (($i % 2) == 0) echo "<tr valign=\"top\">";
		echo "<td id=\"slide".$nb_poules++."\" class=\"slide\" width=\"50%\">";
?>

<div id="box3" class="vgrid synthese" style="clear: both; margin-bottom: 2px;">
<h2 class="grid leagues">Poule <?= ($sess_context->championnat['option_poule_lettre'] == 1 ? chr(ord('A')+$cle-1) : $cle) ?></h2>
<table cellspacing="0" cellpadding="0" class="jkgrid matches_grid" id="table_groupe">
<thead><tr><th class="c1"><div>N°</div></th><th class="c2"><div>Equipe</div></th><th class="c3"><div>Pts</div></th><th class="c4"><div>J</div></th><th class="c5"><div>G</div></th><th class="c6"><div>P</div></th><th class="c7"><div>AVG</div></th></tr></thead>
<tbody>
<?
		$fxlist = new FXListMatchsStatsEquipesLight($classement, $equipes[$cle]);
		$k = 1;
		foreach($fxlist->body->tab as $item) { ?><tr id="tr_<?= ($k - 1)?>"><td class="c1"><div><?= $k++ ?></div></td><td class="c2"><div><a href="#" onclick="mm({action:'stats', idt:'<?= $item['id'] ?>'});"><?= $item['equipe'] ?></a></div></td><td class="c3"><div><button class="button small bigrounded orange"><?= $item['points'] ?></button></div></td><td class="c4"><div><?= $item['matchs_joues'] ?></div></td><td class="c5"><div><?= $item['matchs_gagnes'] ?></div></td><td class="c6"><div><?= $item['matchs_perdus'] ?></div></td><td class="c7"><div><button class="button small bigrounded <?= $item['diff'] >= 0 ? "green" : "red" ?>"><?= $item['diff'] ?></button></div></td></tr><? }
		echo "</table>";
		echo "</td>";
		$i++;
	}
?>
</tbody>
</table>
</div>
<? }



// ///////////////////////////////////////////////////////////
// CLASSEMENT TOURNOI
// ///////////////////////////////////////////////////////////
if ($type_matchs == "X") {

	$fxlist = new FXListClassementJourneeTournoi($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId(), $is_journee_alias ? $id_journee_mere : $sess_context->getJourneeId()); ?>

<div id="box3" class="vgrid" style="clear: both; margin-bottom: 5px;">
<h2 class="grid leagues">Classement</h2>
<table cellspacing="0" cellpadding="0" class="jkgrid matches_grid" id="table_groupe">
<thead><tr><th class="c1"><div>N°</div></th><th class="c2"><div>Equipe</div></th><th class="c3"><div>Pts</div></th><th class="c4"><div>J</div></th><th class="c5"><div>G</div></th><th class="c6"><div>N</div></th><th class="c7"><div>P</div></th><th class="c8"><div>AVG</div></th></tr></thead>
<tbody>
<? $i = 1; 	foreach($fxlist->body->tab as $item) {
	if ($item != _FXLINESEPARATOR_) {
		$tmp = explode('-', preg_replace("/\" class=\"blue\">/", "-", preg_replace("/ onmouse.*/", "", preg_replace("/<.*id_detail=/", "", str_replace("</A>", "", $item[1])))));
		$idt = isset($tmp[0]) ? $tmp[0] : 0;
		$eq = isset($tmp[1]) ? $tmp[1] : '-';
?><tr id="tr_<?= ($i - 1)?>">
		<td class="c1"><div><?= $i++ ?></div></td>
		<td class="c2"><div><a href="#" onclick="mm({action:'stats', idt:'<?= $idt ?>'});"><?= $eq ?></a></div></td>
		<td class="c3"><div><button class="button bigrounded orange"><?= $item['2'] ?></button></div></td>
		<td class="c4"><div><?= $item['3'] ?></div></td>
		<td class="c5"><div><?= $item['4'] ?></div></td>
		<td class="c6"><div><?= $sess_context->getGestionMatchsNul() ? $item['5'] : "-" ?></div></td>
		<td class="c7"><div><?= $sess_context->getGestionMatchsNul() ? $item['6'] : $item['5'] ?></div></td>
		<td class="c8"><div><button class="button bigrounded <?= ($sess_context->getGestionMatchsNul() ? $item['9'] : $item['12']) >= 0 ? "green" : "red" ?>"><?= $sess_context->getGestionMatchsNul() ? $item['9'] : $item['12'] ?></button></div></td>
	</tr>
<? } } ?>
</tbody>
</table>
</div>

<? } ?>



<?
// ///////////////////////////////////////////////////////////
// CLASSEMENT POULE
// ///////////////////////////////////////////////////////////
if ($type_matchs == "P" && isset($classement_equipes[$niveau_type]) && $classement_equipes[$niveau_type] != "") {

	$fxlist = new FXListMatchsStatsEquipes($classement_equipes[$niveau_type], $equipes[$niveau_type]); ?>

<div id="box3" class="vgrid" style="clear: both; margin-bottom: 5px;">
<h2 class="grid leagues">Classement</h2>
<table cellspacing="0" cellpadding="0" class="jkgrid matches_grid" id="table_groupe">
<thead><tr><th class="c1"><div>N°</div></th><th class="c2"><div>Equipe</div></th><th class="c3"><div>Pts</div></th><th class="c4"><div>J</div></th><th class="c5"><div>G</div></th><th class="c6"><div>N</div></th><th class="c7"><div>P</div></th><th class="c8"><div>AVG</div></th></tr></thead>
<tbody>
<? $i = 1; 	foreach($fxlist->body->tab as $item) { ?><tr id="tr_<?= ($i - 1)?>"><td class="c1"><div><?= $i++ ?></div></td><td class="c2"><div><a href="#" onclick="mm({action:'stats', idt:'<?= $item['id'] ?>'});"><?= $item['equipe'] ?></a></div></td><td class="c3"><div><button class="button bigrounded orange"><?= $item['points'] ?></button></div></td><td class="c4"><div><?= $item['matchs_joues'] ?></div></td><td class="c5"><div><?= $item['matchs_gagnes'] ?></div></td><td class="c6"><div><?= $sess_context->getGestionMatchsNul() ? $item['matchs_nuls'] : "-" ?></div></td><td class="c7"><div><?= $item['matchs_perdus'] ?></div></td><td class="c8"><div><button class="button bigrounded <?= $item['diff'] >= 0 ? "green" : "red" ?>"><?= $item['diff'] ?></button></div></td></tr><? } ?>
</tbody>
</table>
</div>

<? } ?>



<?
// ///////////////////////////////////////////////////////////
// TABLEAU GLOBAL
// ///////////////////////////////////////////////////////////
?>

<div class="<?= $sess_context->isAdmin() ? "" : "classic" ?> <?= $type_matchs == "C" ? "consolante" : ($type_matchs == "Y" || $type_matchs == "F" ?  "pyramid" : "") ?>" id="box">
<? if ($type_matchs == "C") { ?><h2 class="grid leagues">Matchs de classements/barrages</h2><? } ?>
<table cellspacing="0" cellpadding="0" class="jkgrid matches_tournoi matches" id="<?= $options_type_matchs == "B" ? "bonus" : "matches" ?>">
<thead><?= $thead ?></thead>
<tbody><?= $tbody ?></tbody>
</table>
</div>

<script type="text/javascript">
gophase = function(options_type_matchs) { gotournoi(options_type_matchs, ''); }

gotournoi = function(options_type_matchs, option) {
	xx({action: 'matches', id: 'main', tournoi: 1, url: 'tournament_matches.php?action=matches&page=1&idj=<?= $idj ?>'+option+'&name=<?= $nom_journee ?>&date=<?= $date_journee ?>&options_type_matchs='+options_type_matchs});
}

<? if ($sess_context->isAdmin()) { ?>

ajouter_match = function(options_type_matchs) {
<? if ($nb_equipes < 2) { ?>
	alert('Vous devez ajouter au moins 2 équipes !');
	return false;
<? } ?>
    go({action: 'matches', id:'main', url:'edit_matches.php?action=matches&page=1&idj=<?= $idj ?>'+'&name=<?= $nom_journee ?>&date=<?= $date_journee ?>&options_type_matchs='+options_type_matchs});
}

modifier_match = function(pkeys, action, niveau) {
	var date = '<?= $date_journee ?>';
	var name = '<?= $nom_journee ?>';
	var idj  = '<?= $idj ?>';
	var idm  = pkeys.replace('+WHERE+id%3D', '');

	go({action: 'matches', id:'main', url:'edit_matches.php?action=matches&idm='+idm+'&niveau='+niveau+'&idj='+idj+'&name='+name+'&date='+date});
}

supprimer_match = function(pkeys, action) {
	var date = '<?= $date_journee ?>';
	var name = '<?= $nom_journee ?>';
	var idj  = '<?= $idj ?>';
	var idm  = pkeys.replace('+WHERE+id%3D', '');
	var niveau = '<?= $options_type_matchs ?>';

	go({action: 'matches', id:'main', url:'edit_matches_do.php?action=matches&idm='+idm+'&niveau='+niveau+'&idj='+idj+'&name='+name+'&date='+date, confirmdel:'1'});
}

ajouter_match_barrage = function(niveau) {
	var date = '<?= $date_journee ?>';
	var name = '<?= $nom_journee ?>';
	var idj  = '<?= $idj ?>';

	go({action: 'matches', id:'main', url:'edit_matches.php?action=matches&niveau='+niveau+'&idj='+idj+'&name='+name+'&date='+date});
}

synchro = function(niveau) {
	xx({action: 'message', id:'main', url:'edit_matches_sync_do.php' });
}

set_bonus = function() {

	var bonus = '';
	var arr = new Array();

	arr = document.getElementsByName('bonus');
	for(var i = 0; i < arr.length; i++)
	{
		var obj = document.getElementsByName('bonus').item(i);
		var tmp = obj.id.split('_');
		var b = numbers.getValue(obj.id);
		if (b.indexOf('.') != -1) { alert('Merci de saisir des nombres entiers uniquement !'); return false; }
		bonus += (bonus == '' ? '' : ',') + tmp[1] + "=" + (b == '' ? '0' : b);
	}

	xx({action: 'message', id:'main', url:'edit_matches_bonus_do.php?bonus='+bonus});
}

function delJournee()
{
	if (confirm("Etes-vous sûr de vouloir supprimer cette journée ?"))
	{
	    document.forms[0].action = '<?= $is_journee_alias ? "journees_alias_supprimer_do.php" : "journees_supprimer_do2.php" ?>';
		document.forms[0].submit();

		return true;
	}

	return false;
}
<? } ?>
</script>