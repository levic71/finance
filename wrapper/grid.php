<?

require_once "../include/sess_context.php";

session_start();

header('Content-Type: text/html; charset='.sess_context::charset);

if (isset($_SESSION["sess_context"])) $sess_context = $_SESSION["sess_context"];

// if (!isset($sess_context)) { echo "<script>window.location.reload( false );</script>"; exit(0); }
if (!isset($sess_context)) { exit(0); }

require_once "../include/constantes.php";
require_once "../include/toolbox.php";
require_once "../include/inc_db.php";
require_once "../include/cache_manager.php";
require_once "../www/SQLServices.php";
require_once "../www/StatsBuilder.php";
require_once "wrapper_fcts.php";

$db = dbc::connect();

$_action_   = Wrapper::getRequest('action',  "leagues");
$page       = Wrapper::getRequest('page',    0);
$sort       = Wrapper::getRequest('sort',    '');
$name       = Wrapper::getRequest('name',    '');
$date       = Wrapper::getRequest('date',    '');
$search     = Wrapper::getRequest('search',  '');
$favoris    = Wrapper::getRequest('favoris', 0);
$sport_sort = Wrapper::getRequest('sport_sort', 99);
$filtre_type_champ = Wrapper::getRequest('filtre_type_champ', 9);

$delta    = $_action_ == "matches" ? 999 : 10;
$opt_edit = "?page=".$page;
$sqlc     = "";
$trclick = "";
$right_menu = "";

$idj = Wrapper::getRequest('idj', 0);
$idp = Wrapper::getRequest('idp', 0);
$idt = Wrapper::getRequest('idt', 0);
if ($_action_ == "matches" && strstr($idj, '|')) { $j = Wrapper::getPrevNextJournees($idj); if ($j == 0) $idj = 0; else { $idj = $j['id']; $date = ToolBox::mysqldate2date($j['date']); $name = $j['nom']; } }
if ($_action_ == "matches" && $idj == 0) $_action_ = "days";


// EDITION BUTTONS MANAGEMENT
$light_admin = ($sess_context->isAdmin() && $sess_context->championnat['entity'] != "_NATIF_");
$editable    = ($sess_context->isAdmin() && ($_action_ == "links" || $_action_ == "roles" || $_action_ == "seasons" || $_action_ == "days" || $_action_ == "matches" || ($_action_ == "teams" && !$light_admin) || ($_action_ == "players" && !$light_admin)));
$new_enable  = ($sess_context->isAdmin() && ($_action_ == "seasons" || $_action_ == "days" || $_action_ == "matches" || ($_action_ == "teams" && !$light_admin) || ($_action_ == "players" && !$light_admin)) || $_action_ == "tchat");


$new_tip = "Nouveau"; $upd_tip = "Modifier"; $del_tip = "Supprimer";
if ($_action_ == "leagues")
{
	$sort1   = "<div id=\"champ_sort\"></div>";
	$sort2   = "<div id=\"sport_sort\"></div>";
	$label   = "Annuaire";
	$right_menu = "".$sort2." ".$sort1;
	$filtre_type_sport = $sport_sort == 99 ? "" : " AND type_sport=".$sport_sort;
	$filtre  = " WHERE entity='_NATIF_' AND actif = 1 AND nom != '' ".($search != "" ? " AND (nom LIKE '%".$search."%' OR lieu LIKE '%".$search."%')" : "").($filtre_type_champ != 9 && $filtre_type_champ != 6 ? " AND type = ".$filtre_type_champ : "").($filtre_type_champ == 6 ? " AND id IN (".SQLServices::cleanIN($favoris).")" : "").$filtre_type_sport;
	$new_tip = "Nouvelle comp&eacute;tition"; $upd_tip = "Modifier comp&eacute;tition"; $del_tip = "Supprimer comp&eacute;tition";
	$sort    = $sort == '' ? '0points' : $sort;
	$nosort  = array("fav" => 1);
	$order   = 'ORDER BY '.(substr($sort, 1) == "new_date" ? "dt_creation" : substr($sort, 1)).' '.(substr($sort, 0, 1) == '1' ? 'ASC' : 'DESC').', nom';
	$sql     = 'SELECT *, nom nom2, CONCAT("<button id=\"fav_", id, "\" class=\"mdl-button mdl-js-button mdl-button--icon\" onclick=\"setfav(", id, ");\"><i class=\"material-icons\">star</i></button>") fav, CONCAT("<button class=\"button orange\">", DATE_FORMAT(dt_creation, \'%d/%m/%y\'), "</button>") new_date, CONCAT("<button id=\"fav_", id, "\" class=\"mdl-button mdl-js-button mdl-button--icon\" onclick=\"mm({action: \'reload\', idc: ", id, "});\"><i class=\"material-icons\">play_circle_outline</i></button>") go, ELT(type+1, "<div class=\"libre\" />", "<div class=\"champ\" />", "<div class=\"tournoi\" />") icon FROM jb_championnat '.$filtre.' '.$order;
	$th      = array("points" => "Points", "type_sport" => "", "nom" => "Nom", "lieu" => "Lieu", "fav" => "", "new_date" => "Cr&eacute;&eacute; le", "go" => "&nbsp;");
	$cols    = array("points" => 1, "type_sport" => 1, "nom" => 1, "lieu" => 1, "fav" => 1, "new_date" => 1, "go" => 1);
	$trclick = "mm({ action: 'reload', idc: _id_ })";
}
else if ($_action_ == "seasons")
{
	$label = "Saisons";
	$filtre = "";
	$new_tip = "Nouvelle saison"; $upd_tip = "Modifier saison"; $del_tip = "Supprimer saison";
	$sort    = $sort == '' ? '1date_creation2' : $sort;
	$nosort  = array();
	$order   = 'ORDER BY '.(substr($sort, 1) == "date_creation2" ? "date_creation" : substr($sort, 1)).' '.(substr($sort, 0, 1) == '1' ? 'ASC' : 'DESC');
	$sql   = 'SELECT *, ELT(active+1, "-", "<img src=\"img/tick_16.png\" />") active, CONCAT("<button class=\"button orange\">", DATE_FORMAT(date_creation, \'%d/%m/%y\'), "</button>") date_creation2 FROM jb_saisons WHERE id_champ='.$sess_context->getRealChampionnatId().' '.$filtre.' '.$order;
	$th    = array("nom" => "Nom", "date_creation2" => "Date création", "active" => "Active");
	$cols  = array("nom" => 1, "date_creation2" => 1, "active" => 1);
}
else if ($_action_ == "roles")
{
	$label = "Droits d'administration";
	$filtre = " AND (nom LIKE '%".$search."%' OR prenom LIKE '%".$search."%' OR pseudo LIKE '%".$search."%')";
	$new_tip = "Nouveau droit"; $upd_tip = "Modifier droit"; $del_tip = "Supprimer droit";
	$sort    = "";
	$nosort  = array();
	$order   = '';
	$sql   = 'SELECT r.id id, CONCAT(u.nom, " ", u.prenom) nom, u.pseudo pseudo, r.role role, r.role role2, ELT(r.status+1, "<img src=\"img/block_16.png\" />", "<img src=\"img/tick_16.png\" />") status FROM jb_roles r, jb_users u WHERE r.id_champ='.$sess_context->getRealChampionnatId().' AND r.id_user = u.id '.$filtre.' '.$order;
	$th    = array("nom" => "Nom", "pseudo" => "Pseudo", "role" => "Role", "status" => "Actif");
	$cols  = array("nom" => 1, "pseudo" => 1, "role" => 1, "status" => 1);
}
else if ($_action_ == "links")
{
	$label = "Rattachement joueurs";
	$filtre = " AND (u.nom LIKE '%".$search."%' OR u.prenom LIKE '%".$search."%' OR u.pseudo LIKE '%".$search."%')";
	$new_tip = "Nouveau rattachement"; $upd_tip = "Modifier rattachement"; $del_tip = "Supprimer rattachement";
	$sort    = $sort == '' ? '1player' : $sort;
	$nosort  = array();
	$order = 'ORDER BY '.substr($sort, 1).' '.(substr($sort, 0, 1) == '1' ? 'ASC' : 'DESC');
	$sqlc  = 'SELECT count(*) total FROM jb_user_player up, jb_users u, jb_joueurs p WHERE up.id_champ='.$sess_context->getRealChampionnatId().' AND up.id_player=p.id AND up.id_user = u.id '.$filtre;
	$sql   = 'SELECT up.id id, CONCAT("<span>", u.nom, " ", u.prenom, "</span><small>alias ", u.pseudo, "</small>") user, CONCAT("<span>", p.nom, " ", p.prenom, "</span><small>alias ", p.pseudo, "</small>") player, ELT(up.status+1, "<img src=\"img/block_16.png\" />", "<img src=\"img/tick_16.png\" />") status FROM jb_user_player up, jb_users u, jb_joueurs p WHERE up.id_champ='.$sess_context->getRealChampionnatId().' AND up.id_player=p.id AND up.id_user = u.id '.$filtre.' '.$order;
	$th    = array("player" => "Joueur", "user" => "Utisateur", "status" => "Rattachement");
	$cols  = array("player" => 1, "user" => 1, "status" => 1);
}
else if ($_action_ == "players")
{
	$id_saison = $sess_context->getChampionnatId();
	$req = "SELECT * FROM jb_saisons WHERE id=".$id_saison;
	$res = dbc::execSql($req);
	$saison = mysqli_fetch_array($res);

	$up = array();
	$sql = 'SELECT u.photo, CONCAT(nom, " ", prenom) name, u.pseudo, up.id_player FROM jb_users u, jb_user_player up WHERE up.id_champ='.$sess_context->getRealChampionnatId().' AND u.id = up.id_user AND up.status=1';
	$res = dbc::execSql($sql);
	while($row = mysqli_fetch_array($res)) {
		$up[$row['id_player']] = $row;
	}

	if ($sess_context->isFreeXDisplay()) {
		$filtre = (isset($saison['joueurs']) && $saison['joueurs'] != "" ? " AND id IN (".SQLServices::cleanIN($saison['joueurs']).")" : "");
	}
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
	}

	$right_menu  = "<button id=\"trombi\" onclick=\"xx({action: 'stats', id:'main', url:'stats_player.php?trombinoscope=1%26idp=".$idp."'});\" class=\"mdl-button mdl-button--icon material-icons mdl-badge mdl-badge--overlap\" data-badge=\"X\">contacts</button><div class=\"mdl-tooltip\" for=\"trombi\">Trombinoscope</div>";

	$label = "Joueurs";
	$new_tip = "Nouveau joueur"; $upd_tip = "Modifier joueur"; $del_tip = "Supprimer joueur";

	$sort  = $sort == '' ? '1name' : $sort;
	$nosort  = array();
	$filtre .= $search != "" ? " AND (nom LIKE '%".$search."%' OR pseudo LIKE '%".$search."%' OR prenom LIKE '%".$search."%')" : "";
	$order = 'ORDER BY '.(substr($sort, 1) == "name" ? "nom" : substr($sort, 1)).' '.(substr($sort, 0, 1) == '1' ? 'ASC' : 'DESC');
	$str   = 'CONCAT("<a href=\"#\" onclick=\"mm({action:\'stats\', idp:\'", id, "\'});\" class=\"full-circle\"><img src=\"img/statistics_16.png\" /><span class=\"bullet\"></span></a>") go, CONCAT(nom, " ", prenom) name, CONCAT("<img src=\"", IF(photo="", "img/user-img.png", photo), "\" />") portrait';
	$sql   = 'SELECT *, '.$str.' FROM jb_joueurs WHERE id_champ='.$sess_context->getRealChampionnatId().' '.$filtre.' '.$order;
	$th    = array("portrait" => "&nbsp;", "name" => "Nom", "pseudo" => "Pseudo", "go" => "&nbsp;");
	$cols  = array("portrait" => 1, "name" => 1, "pseudo" => 1, "go" => 1);
	$trclick = "mm({action:'stats', idp: '_id_'});";
}
else if ($_action_ == "teams")
{
	$id_saison = $sess_context->getChampionnatId();
	$req = "SELECT * FROM jb_saisons WHERE id=".$id_saison;
	$res = dbc::execSql($req);
	$saison = mysqli_fetch_array($res);

	$filtre = "";
	if (!$sess_context->isFreeXDisplay() && $saison['equipes'] != "") $filtre .= " AND id IN (".SQLServices::cleanIN($saison['equipes']).")";
	$filtre .= $search != "" ? " AND (nom LIKE '%".$search."%')" : "";

	$label = "Equipes";
	$new_tip = "Nouvelle équipe"; $upd_tip = "Modifier équipe"; $del_tip = "Supprimer équipe";
	$sort  = $sort == '' ? '1nom' : $sort;
	$nosort  = array("js" => 1);
	$order = 'ORDER BY '.(substr($sort, 1) == "js" ? "nb_joueurs" : substr($sort, 1)).' '.(substr($sort, 0, 1) == '1' ? 'ASC' : 'DESC');
	$sql   = 'SELECT *, CONCAT("<img src=\"", IF(photo="", "img/team-icon.png", photo), "\" />") portrait, CONCAT("<button id=\"b12\" class=\"button blue\"><div class=\"box\">", nb_joueurs, "</div></button>") js, CONCAT("<a href=\"#\" onclick=\"mm({action:\'stats\', idt:\'", id, "\'});\" class=\"full-circle\"><img src=\"img/statistics_16.png\" /><span class=\"bullet\"></span></a>") go FROM jb_equipes WHERE id_champ='.$sess_context->getRealChampionnatId().' '.$filtre.' '.$order;
	$th    = array("portrait" => "&nbsp;", "nom" => "Nom", "js" => "Nb joueurs", "go" => "&nbsp;");
	$cols  = array("portrait" => 1, "nom" => 1, "js" => 1, "go" => 1);
	$trclick = "mm({action:'stats', idt: '_id_'});";
}
else if ($_action_ == "days")
{
	$label = "Journées";
	$new_tip = "Nouvelle journée";
	$filtre = $search != "" ? " AND (nom LIKE '%".$search."%')" : "";
	$sort    = $sort == '' ? '1mydate' : $sort;
	$nosort  = array();
	$order   = 'ORDER BY '.(substr($sort, 1) == "mydate" ? "date" : substr($sort, 1)).' '.(substr($sort, 0, 1) == '1' ? 'ASC' : 'DESC');
	$sql   = 'SELECT *, DATE_FORMAT(date, \'%d/%m/%Y\') fdate, CONCAT("<button class=\"button orange\">", DATE_FORMAT(date, \'%d/%m/%Y\'), "</button>") mydate, CONCAT("<a href=\"#\" onclick=\"mm({action:\'matches\', tournoi: '.($sess_context->isTournoiXDisplay() ? 1 : 0).', idj:\'", id,"\', name:\'", nom,"\', date:\'", DATE_FORMAT(date, \'%d/%m/%Y\'),"\'});\" class=\"full-circle\"><img src=\"img/ballon.png\" /><span class=\"bullet\"></span></a>") go FROM jb_journees WHERE id_champ='.$sess_context->getChampionnatId()." ".$filtre." ".$order;
	$th    = array("mydate" => "Date", "nom" => "Nom", "go" => "&nbsp;");
	$cols  = array("mydate" => 1, "nom" => 1, "go" => 1);
	$trclick = "mm({action:'matches', tournoi: ".($sess_context->isTournoiXDisplay() ? 1 : 0).", idj: '_id_', name: '_name_', date: '_date_'});";
}
else if ($_action_ == "matches")
{
	$label2 = $date.': '.ToolBox::conv_lib_journee($name);
	$label = '<div class="daycontrol"><div class="dayprev"><img class="bt" src="img/icons/dark/appbar.navigate.previous.png" onclick="mm({action:\'matches\', idj:\''.$idj.'|prev\', name:\'\', date:\'\'});" /></div><a href="#"><div class="daytitle">'.$label2.'</div></a><div class="daynext"><img class="bt" src="img/icons/dark/appbar.navigate.next.png" onclick="mm({action:\'matches\', idj:\''.$idj.'|next\', name:\'\', date:\'\'});" /></div></div>';
	$new_tip = "Nouveau match"; $upd_tip = "Modifier journée"; $del_tip = "Supprimer journée";
	$filtre = "";
	$sess_context->setJourneeId($idj);
	$nosort  = array("nom1" => 1, "resultat" => 1, "nom2" => 1);
	$sql   = "SELECT m.journal_empty, m.penaltys, m.prolongation, m.id match_id, j.date mdate, m.match_joue, j.date date, e1.nom nom1, m.resultat resultat, e2.nom nom2, m.fanny, m.nbset FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND m.id_journee=".$idj." AND m.id_champ=".$sess_context->getChampionnatId()." ".$filtre;
	$th    = array("nom1" => "Equipe 1", "resultat" => "Score", "nom2" => "Equipe 2", "journal_empty" => "&nbsp;");
	$cols  = array("nom1" => 1, "resultat" => 1, "nom2" => 1, "journal_empty" => 1);
	if ($sess_context->isAdmin()) unset($cols["journal_empty"]);
	$opt_edit .= "&idj=".$idj."&date=".$date."&name=".$name;
}
else if ($_action_ == "fannys")
{
	$onclick = "";
	if ($idp != 0) $onclick = "mm({action:'stats', idp:'".$idp."'});";
	if ($idt != 0) $onclick = "mm({action:'stats', idt:'".$idt."'});";
	$label = 'Fannys <button class="button gray right" onclick="'.$onclick.'">Retour</button>';
	$nosort  = array("nom1" => 1, "resultat" => 1, "nom2" => 1);
	if ($idt != 0)
		$sql   = "SELECT m.fanny, m.nbset, DATE_FORMAT(j.date, '%d/%m/%y') date, e1.nom nom1, m.resultat resultat, e2.nom nom2 FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE m.fanny=1 AND e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND (m.id_equipe1=".$idt." OR m.id_equipe2=".$idt.") AND m.id_champ=".$sess_context->getChampionnatId()." ORDER BY date";
	else
		$sql   = "SELECT m.fanny, m.nbset, DATE_FORMAT(j.date, '%d/%m/%y') date, e1.nom nom1, m.resultat resultat, e2.nom nom2 FROM jb_matchs m, jb_equipes e1, jb_equipes e2, jb_journees j WHERE m.fanny=1 AND e1.id=m.id_equipe1 AND e2.id=m.id_equipe2 AND m.id_journee=j.id AND (e1.joueurs like '".$idp."|%' OR e1.joueurs like '%|".$idp."|%' OR e1.joueurs like '%|".$idp."' OR e2.joueurs like '".$idp."|%' OR e2.joueurs like '%|".$idp."|%' OR e2.joueurs like '%|".$idp."') AND m.id_champ=".$sess_context->getChampionnatId()." ORDER BY date";
	$th    = array("nom1" => "Equipe 1", "resultat" => "Score", "nom2" => "Equipe 2", "date" => "");
	$cols  = array("nom1" => 1, "resultat" => 1, "nom2" => 1, "date" => 1);
	$opt_edit .= "&idp=".$idp;
}
else if ($_action_ == "tchat")
{
	$label = "Tchat";
	$new_tip = "Nouveau message"; $upd_tip = "Modifier message"; $del_tip = "Supprimer message";
	$filtre = $search != "" ? " AND (title LIKE '%".$search."%' OR nom LIKE '%".$search."%')" : "";
	$sort    = $sort == '' ? '0date' : $sort;
	$nosort  = array("nb_lectures" => 1, "nb_reponses" => 1);
	$order   = 'ORDER BY '.(substr($sort, 1) == "mydate" ? "date" : substr($sort, 1)).' '.(substr($sort, 0, 1) == '1' ? 'ASC' : 'DESC');
	$sql   = 'SELECT *, CONCAT("<button class=\"button orange\">", DATE_FORMAT(date, \'%d/%m/%y\'), "</button>") mydate, CONCAT("<a href=\"#\" onclick=\"go({action: \'tchat\', id:\'main\', url:\'edit_tchat.php?idp=", id, "\' });\" class=\"full-circle\"><img src=\"img/magnifying.png\" /><span class=\"bullet\"></span></a>") go FROM jb_forum WHERE id_champ='.$sess_context->getRealChampionnatId().' AND in_response=0 AND del != 1 '.$filtre.' '.$order;
	$th    = array("mydate" => "Date", "title" => "Titre", "nom" => "Auteur", "nb_lectures" => "L", "nb_reponses" => "R", "go" => "&nbsp;");
	$cols  = array("mydate" => 1, "title" => 1, "nom" => 1, "nb_lectures" => 1, "nb_reponses" => 1, "go" => 1);
	$trclick = "go({action: 'tchat', id:'main', url:'edit_tchat.php?idp=_id_' });";
}
else
	exit(0);


// sql request for count elements
$req = dbc::execSQL($sqlc != "" ? $sqlc : preg_replace("/SELECT.*FROM/i", "SELECT COUNT(*) total FROM", $sql));
$data = mysqli_fetch_assoc($req);
$total = $data['total'];
if ($page*$delta > $total) $page = 0;

if ($_action_ == "players") $right_menu = str_replace("X", $total, $right_menu);

// page parameters
$start = $page*$delta;
$select = $sql.($sess_context->isFreeXDisplay() && $_action_ == "teams" ? "" : " LIMIT ".$start.", ".$delta);
// if ($_SERVER['REMOTE_ADDR'] == "88.183.221.28") echo $select;

// Init tables
$tbody = ""; $thead = ""; $empty_line = "";
$i = 1;

if ($total > 0) {

	$lines = array();
	$req = dbc::execSql($select);
	while($data = mysqli_fetch_assoc($req)) $lines[] = $data;

	if ($sess_context->isFreeXDisplay() && $_action_ == "teams") {
		$lines = filterTeamsList($lines, $saison['joueurs']);
		$total = count($lines);
		$lines = filterList($lines, $start, $delta);
	}

	// Title of table
	$begin = $page+1;
	$end   = ceil($total/$delta);
	$badge = ($total == 0 || ($begin == 1 && $end == 1 ) ? "" : ($total == 0 ? "0" : ($page+1)." sur ".ceil($total/$delta)));

	foreach($lines as $data)
	{
		$onclick = "";

		if (!$editable) {
			if (isset($data['id'])) $onclick = str_replace('_id_', $data['id'], $trclick);
			if ($_action_ == "leagues") $onclick = str_replace('_dns_', 'http://'.Wrapper::string2DNS($data['nom2']).'.jorkers.com/wrapper/', $onclick);
			if ($_action_ == "days") $onclick = str_replace('_name_', $data['nom'], $onclick);
			if ($_action_ == "days") $onclick = str_replace('_date_', $data['fdate'], $onclick);
		}

		$tmp = '<tr>'; $tbody .= '<tr id="tr_'.$i.'"  '.($onclick != "" ? 'onclick="'.$onclick.'" class="clickonit"' : '').'>';
		if (!isset($num) || (isset($num) && $num))
		{
			$tmp .= '<th class="c1"><div>N°</div></th>';
			$tbody .= '<td class="c1"><div>'.(($page*$delta)+$i).'</div></td>';
		}

		// Choix du vainqueur pour les matches
		$vainqueur = ($_action_ == "matches" || $_action_ == "fannys") ? StatsJourneeBuilder::kikiGagne($data) : 0;

		reset($cols); $j = 2;
		foreach($cols as $cle => $val) {
		
			$class = "";
			if ($_action_ == "roles" && $cle == "go" && $sess_context->isOnlyDeputy() && $data['role'] == _ROLE_ADMIN_) $class="hideme";
			if ($_action_ == "players" && $cle == "name" && isset($up[$data['id']])) $data[$cle] = $up[$data['id']]['name'];
			if ($_action_ == "players" && $cle == "portrait" && isset($up[$data['id']])) $data[$cle] = $up[$data['id']]['photo'] == "" ? $data[$cle] : "<img src=\"".$up[$data['id']]['photo']."\" />";
//			if (!$sess_context->isSuperUser() && $_action_ == "leagues" && $cle == "go") $data[$cle] = '<a href="http://'.Wrapper::string2DNS($data['nom2']).'.jorkers.com/wrapper/jk.php?idc='.$data['id'].'" class="full-circle"><span class="bullet"></span></a>';
			if ($_action_ == "leagues" && $cle == "nom") $data[$cle] = '<span>'.($data[$cle]).'</span><small>'.$libelle_type[$data['type']].'</small>';
			if ($_action_ == "leagues" && $cle == "type_sport") $data[$cle] = '<img src="img/sports/'.($icon_genre[$data[$cle]]).'" height="24" width="32" />';
			if ($_action_ == "leagues" && $cle == "points") $data[$cle] = '<button class="button '.($data[$cle] > 1000 ? "blue" : ( $data[$cle] > 500 ? "green" : ($data[$cle] > 250 ? "yellow" : ($data[$cle] > 100 ? "orange" : ($data[$cle] > 0 ? "red" : "black"))))).'">'.($data[$cle]).'</button>';
			if ($_action_ == "days" && $cle == "nom") $data[$cle] = ToolBox::conv_lib_journee($data[$cle]);
			if ($_action_ == "days" && $cle == "go") $data[$cle] = str_replace("''", "'", $data[$cle]);
			if (($_action_ == "matches" || $_action_ == "fannys") && $cle == "resultat") { $resultat = $data[$cle]; $data[$cle] = Wrapper::formatScore($data); }
			if (($_action_ == "matches" || $_action_ == "fannys") && $cle == "nom1") { $nom1 = $data[$cle]; $data[$cle] = "<div class=\"".($vainqueur == 2 ? "equipe_perdu" : "equipe_gagne").(sess_context::getGestionFanny() == 1 && $data['fanny'] == 1 ? " fanny" : "")."\">".$data[$cle]."</div>"; }
			if (($_action_ == "matches" || $_action_ == "fannys") && $cle == "nom2") { $nom2 = $data[$cle]; $data[$cle] = "<div class=\"".($vainqueur == 1 ? "equipe_perdu" : "equipe_gagne").(sess_context::getGestionFanny() == 1 && $data['fanny'] == 1 ? " fanny" : "")."\">".$data[$cle]."</div>"; }
			if ($_action_ == "matches" && $cle == "journal_empty") $data[$cle] = $data[$cle] == 1 ? '<a href="#" title="Saisie Live" onclick="viewScoring('.$data['match_id'].', \''.$nom1.'\', \''.$nom2.'\', \''.$data['nbset'].'\', \''.$resultat.'\');" class="full-circle"><img src="img/play_16.png" /></a>' : '';
			if ($_action_ == "tchat" && $cle == "nb_lectures") $data[$cle] = "<button class=\"button bigrounded blue\">".$data[$cle]."</button>";
			if ($_action_ == "tchat" && $cle == "nb_reponses") $data[$cle] = "<button class=\"button bigrounded orange\">".$data[$cle]."</button>";
			if ($_action_ == "roles" && $cle == "role") $data[$cle] = $libelle_role[$data[$cle]];

			$sortclick = "onclick=\"sort_col('".(substr($sort, 1) == $cle && substr($sort, 0, 1) == '1' ? "0" : "1").$cle."');\"";
			if (isset($nosort[$cle])) $sortclick = "";
			$tmp .= '<th class="c'.$j.''.($cle == "go" ? " edit go" : "").'"><div>'.($cle == "go" ? '' : '<a href="#" '.$sortclick.' '.(substr($sort, 1) == $cle ? 'class="'.(substr($sort, 0, 1) == '1' ? 'asc' : 'desc').'"' : '').'>').(isset($th[$cle]) ? $th[$cle] : $cle).($cle == "go" ? '' : '</a>').'</div></th>';
			$tbody .= '<td class="c'.$j.''.($cle == "go" ? " edit go" : "").' '.$class.'"><div>'.($data[$cle] == "" ? "&nbsp;" : $data[$cle]).'</div></td>';
			if ($i == 1) $empty_line .= '<td class="c'.$j.''.($cle == "go" ? " edit go" : "").' '.$class.'"><div></div></td>';
			$j++;
		}

		if ($editable) {
			$tmp .= '<th class="edit"><div>&nbsp;</div></th><th class="edit"><div>&nbsp;</div></th>'.($_action_ == "matches" ? "<th class=\"edit\"><div>&nbsp;</div></th>": "");
			$class = $_action_ == "roles" && $sess_context->isOnlyDeputy() && $data['role2'] == _ROLE_ADMIN_ ? "hideme" : "";

			if ($_action_ == "matches")      $args = $opt_edit."&idm=".$data['match_id'];
			else if ($_action_ == "roles")   $args = $opt_edit."&idr=".$data['id'];
			else if ($_action_ == "links")   $args = $opt_edit."&idl=".$data['id'];
			else if ($_action_ == "players") $args = $opt_edit."&idp=".$data['id'];
			else if ($_action_ == "teams")   $args = $opt_edit."&idt=".$data['id'];
			else if ($_action_ == "days")    $args = $opt_edit."&idd=".$data['id'];
			else if ($_action_ == "seasons") $args = $opt_edit."&ids=".$data['id'];
			else $args = $opt_edit;

			if ($_action_ == "matches") $tbody .= '<td class="edit" id="play"><div><a href="#" title="Saisie Live" onclick="liveScoring('.$data['match_id'].', \''.$nom1.'\', \''.$nom2.'\', \''.$data['nbset'].'\', \''.$resultat.'\');" class="full-circle"><img src="img/play_16.png" /></a></div></td>';
			$tbody .= '<td class="edit '.$class.'" id="upd"><div><a href="#" title="Editer" onclick="go({action: \''.$_action_.'\', id:\'main\', url:\'edit_'.$_action_.'.php'.$args.'\'});" class="full-circle"><img src="img/pencil_16.png" /></a></div></td>';
			$tbody .= '<td class="edit '.$class.'" id="del"><div><a href="#" title="Supprimer" onclick="go({action: \''.$_action_.'\', id:\'main\', url:\'edit_'.$_action_.'_do.php'.$args.'\', confirmdel:\'1\'});" class="full-circle"><img src="img/trash_16.png" /></a></div></td>';
			if ($i == 1) {
				if ($_action_ == "matches") $empty_line .= '<td class="edit" id="play"><div></div></td>';
				$empty_line .= '<td class="edit '.$class.'" id="upd"><div></div></td><td class="edit '.$class.'" id="del"><div></div></td>';
			}
		}

		$tmp .= '</tr>'; $tbody .= '</tr>';
		if ($i == 1) $thead .= $tmp;

		$i++;
	}
}
else {
		$j = 2;

		while (list($cle, $val) = each($cols))
		{
			$class = "";
			$thead .= '<th class="c'.$j.''.($cle == "go" ? " edit go" : "").'"><div>'.(isset($th[$cle]) ? $th[$cle] : $cle).'</div></th>';
			$empty_line .= '<td class="c'.$j.''.($cle == "go" ? " edit go" : "").' '.$class.'"><div></div></td>';
			$j++;
		}

		if ($editable) {
			$class = $_action_ == "roles" && $sess_context->isOnlyDeputy() && $data['role2'] == _ROLE_ADMIN_ ? "hideme" : "";
			$thead .= '<th class="edit"><div>&nbsp;</div></th><th class="edit"><div>&nbsp;</div></th>'.($_action_ == "matches" ? "<th class=\"edit\"><div>&nbsp;</div></th>": "");
			$empty_line .= '<td class="edit '.$class.'" id="upd"><div></div></td><td class="edit '.$class.'" id="del"><div></div></td>';
			if ($_action_ == "matches") $empty_line .= '<td class="edit" id="play"><div></div></td>';
		}

		$thead = '<tr>'.((!isset($num) || (isset($num) && $num)) ? '<th class="c1"><div>N?</div></th>' : '').$thead.'</tr>';
}

// On complete avec des lignes vides
$nb_empty_line = $_action_ == "matches" && $i <= 3 ? 3 : ($_action_ == "matches" ? 0 : $delta);
for($x = $i; $x <= $nb_empty_line; $x++) $tbody .= '<tr id="tr_'.(($page*$delta)+$x).'"><td class="c1"><div>'.(($page*$delta)+$x).'</div></td>'.$empty_line.'</tr>';

$t = array();
if ($new_enable) array_push($t, array("id" => "new", "onclick" => "go({action: '".$_action_."', id:'main', url:'edit_".$_action_.".php".$opt_edit."'});", "tooltip" => $new_tip));
if ($_action_ == "leagues") array_push($t, array("id" => "sb_map", "onclick" => "go({id:'main', url:'jk_map.php'});", "tooltip" => "Mappemonde"));
if ($_action_ == "matches" && $editable) array_push($t, array("id" => "sb_upd", "onclick" => "go({action: 'days', id:'main', url:'edit_days.php?idd=".$idj."'});", "tooltip" => $upd_tip));
if ($_action_ == "matches" && $editable) array_push($t, array("id" => "sb_del", "onclick" => "go({action: 'days', id:'main', url:'edit_days_do.php?del=0&idd=".$idj."', confirmdel:'1'});", "tooltip" => $del_tip));

if ($sess_context->championnat['entity'] == "_NATIF_" && $_action_ == "matches")
    array_push($t, array(
        "id" => "sb_fb",
        "onclick" => "google_tracking('facebook.com');",
        "target" => "_blank",
        "href" => "http://www.facebook.com/dialog/feed?" .
            "app_id=107452429322746&" .
            "link=http://www.jorkers.com/wrapper/fb.php?idc=".$sess_context->getRealChampionnatId()."_".$idj."_".$date."_".$name."&" .
            "picture=http://www.jorkers.com/wrapper/img/logo.png&" .
            "name=".utf8_encode("? ".$label2." : Résultats")."&" .
            "caption=".utf8_encode($libelle_genre[$sess_context->getTypeSport()])." :: ".utf8_encode(($sess_context->isTournoiXDisplay() ? "Tournoi " : "Championnat ").$sess_context->getChampionnatNom())."&" .
            "description=".utf8_encode("Jorkers.com, solution de gestion de championnats et tournois de sports individuels et collectifs")."&" .
            "message=".utf8_encode("Laisser un message !")."&" .
            "redirect_uri=http://www.jorkers.com/wrapper/fb.php?idc=".$sess_context->getRealChampionnatId()."_".$idj."_".$date."_".$name,
        "tooltip" => "Publier sur son facebook"));

if (($_action_ == "players") && $editable) array_push($t, array("id" => "sb_link_player", "onclick" => "go({action: '".$_action_."', id:'main', url:'link_to_player.php'});", "tooltip" => "Ajouter un joueur inscrit"));
if (($_action_ == "players" || $_action_ == "teams") && $editable) array_push($t, array("id" => "sb_mail2", "onclick" => "go({action: '".$_action_."', id:'main', url:'contacter.php?type_mail=3'});", "tooltip" => "Envoyer un mail"));
if ($sess_context->championnat['entity'] == "_NATIF_" && $_action_ == "matches" && $editable) array_push($t, array("id" => "sb_mail2", "onclick" => "go({action: 'days', id:'main', url:'contacter.php?type_mail=2&idd=".$idj."&name=".$name."&date=".$date."'});", "tooltip" => "Envoyer un mail"));

if (false && $_action_ == "matches") array_push($t, array("id" => "swap2", "onclick" => "mm({action: 'days'});", "tooltip" => "Afficher calendrier"));

if ($sess_context->isChampionnatXDisplay() && $_action_ == "days" && $editable) array_push($t, array("id" => "sb_buildall", "onclick" => "go({action: 'buildallmatches', id:'main', url:'edit_days_build_all_matches.php'});", "tooltip" => "Créer tous les matchs d'une saison"));
if ($_action_ == "days") array_push($t, array("id" => "swap1", "onclick" => "swap_cal();", "tooltip" => "Afficher en mode liste"));
if ($_action_ == "roles" || $_action_ == "links") array_push($t, array("id" => "sb_back", "onclick" => "mm({action: 'dashboard'});", "tooltip" => "Retour"));
if ($_action_ == "matches") array_push($t, array("id" => "sb_back", "onclick" => "mm({action: 'days'});", "tooltip" => "Retour"));

Wrapper::fab_button_menu($t);

?>

<div class="mdl-card mdl-shadow--2dp mdl-cell mdl-cell--12-col">

<? if ($_action_ == "days" && $sess_context->isAdmin()) { ?><input type="hidden" id="ad" name="ad" value="1" /><? } ?>

<div class="<?= $sess_context->isAdmin() && $editable ? "" : "classic" ?>" id="box">

<div class="mdl-card__title mdl-color--primary mdl-color-text--white">
    <h2 class="mdl-card__title-text mdl-color--primary grid <?= $_action_ == "fannys" ? "matches" : $_action_ ?>"><?= $label ?></h2>
</div>

<div class="mdl-card__menu">
<? if ($_action_ != "matches") { ?>
<div class="mdl-textfield mdl-js-textfield mdl-textfield--expandable" id="search_area">
  <label class="mdl-button mdl-js-button mdl-button--icon" for="search">
	  <i class="material-icons">search</i>
	</label>
	<div class="mdl-textfield__expandable-holder">
	  <input class="mdl-textfield__input" type="search" id="search"  value="<?= $search ?>" placeholder="Recherche ..."  />
	  <label class="mdl-textfield__label" for="search">Search</label>
	</div>
  </div>
<? } ?>
<?= $right_menu ?>
</div>

<table cellspacing="0" cellpadding="0" class="jkgrid <?= $_action_ == "fannys" ? "fannys" : "" ?>" id="<?= $_action_ == "fannys" ? "matches" : $_action_ ?>">
<thead><?= $thead ?></thead>
<tbody><?= $tbody ?></tbody>
</table>

<?

$moreopt = "";
if ($_action_ == "fannys")  $moreopt .= ", idp: ".$idp;
if ($_action_ == "leagues") $moreopt .= ", filtre_type_champ: '".$filtre_type_champ."'";

?>

<? if ($total > 0 && !($begin == 1 && $end == 1)) { ?>
<div class="mdl-dialog__actions pagination">
	<button id="ctrl4" class="mdl-button mdl-js-button mdl-button--icon <?= ($page+1)*$delta < $total ? "disable" : "disable" ?>" <?= ($page+1)*$delta < $total ? "onclick=\"mm({action:'".$_action_."', page:'".(floor($total / $delta) - (($total % $delta) == 0 ? 1 : 0))."', sport_sort: ".$sport_sort.", favoris: getfav(), search: ".($search == "" ? "0" : "1").", sort: '".$sort."', next:'1' ".$moreopt." })\"" : "" ?> >
	  <i class="material-icons">skip_next</i>
	</button>
	<button id="ctrl3" class="mdl-button mdl-js-button mdl-button--icon <?= ($page+1)*$delta < $total ? "disable" : "disable" ?>" <?= ($page+1)*$delta < $total ? "onclick=\"mm({action:'".$_action_."', page:'".($page+1)."', sport_sort: ".$sport_sort.", favoris: getfav(), search: ".($search == "" ? "0" : "1").", sort: '".$sort."', next:'1' ".$moreopt." })\"" : "" ?> >
	  <i class="material-icons">navigate_next</i>
	</button>
	<button id="ctrl2" class="mdl-button mdl-js-button mdl-button--icon <?= $page > 0 ? "disable" : "disable" ?>" <?= $page > 0 ? "onclick=\"mm({action:'".$_action_."', page: '".($page-1)."', sport_sort: ".$sport_sort.", favoris: getfav(), search: ".($search == "" ? "0" : "1").", sort: '".$sort."', prev:'1' ".$moreopt." })\"" : "" ?> >
	  <i class="material-icons">navigate_before</i>
	</button>
	<button id="ctrl1" class="mdl-button mdl-js-button mdl-button--icon <?= $page > 0 ? "disable" : "disable" ?>" <?= $page > 0 ? "onclick=\"mm({action:'".$_action_."', page: '0', sport_sort: ".$sport_sort.", favoris: getfav(), search: ".($search == "" ? "0" : "1").", sort: '".$sort."', prev:'1' ".$moreopt." })\"" : "" ?> >
	  <i class="material-icons">skip_previous</i>
	</button>
	<span><?= $badge ?></span>
</div>
<? } ?>

</div>

<div id="box2"></div>

<? if ($_action_ == "matches" && $sess_context->isFreeXDisplay()) Wrapper::displayMatchesStats($sess_context->getJourneeId(), false); ?>

<? if ($_action_ == "leagues") { ?><script>showfavs();</script><? } ?>

</div>


<script>
document.getElementById('search').addEventListener('keypress', function(event) {
    if (event.keyCode == 13) {
		event.preventDefault();
		mm({action:'<?= $_action_ ?>', page:'<?= $page ?>', sport_sort: <?= $sport_sort ?>, favoris: getfav(), sort: '<?= $sort ?>', search:'1' <?= $moreopt ?> <?= $_action_ == "leagues" ? ", filtre_type_champ: '".$filtre_type_champ."'" : "" ?>});
	}
});

sort_col = function(col) {
	mm({action:'<?= $_action_ ?>', page: '<?= $page ?>', sport_sort: <?= $sport_sort ?>, favoris: getfav(), search: <?= $search == "" ? "0" : "1" ?>, sort: col <?= $moreopt ?> });
}

champ_sort = function(name) {
	var type = choices.getSelection(name);
	mm({action:'<?= $_action_ ?>', page: '0', sport_sort: <?= $sport_sort ?>, favoris: getfav(), sort: '<?= $sort ?>', search: ' <?= ($search == "" ? "0" : "1") ?>', filtre_type_champ: type });
}

sport_sort = function(name) {
	var type = choices.getSelection(name);
	mm({action:'<?= $_action_ ?>', page: '0', sport_sort: type, favoris: getfav(), sort: '<?= $sort ?>', search: ' <?= ($search == "" ? "0" : "1") ?>', filtre_type_champ: <?= $filtre_type_champ ?> });
}

<? if ($_action_ == "leagues") { ?>
choices.build({ name: 'champ_sort',  c1: 'blue purple-title-card', c2: 'white', singlepicking: true, removable: true, callback: 'champ_sort', values: [ { v: 0, l: 'Libres', s: <?= $filtre_type_champ == 0 ? "true" : "false" ?> }, { v: 1, l: 'Championnats', s: <?= $filtre_type_champ == 1 ? "true" : "false" ?> }, { v: 2, l: 'Tournois', s: <?= $filtre_type_champ == 2 ? "true" : "false" ?> }, { v: 6, l: 'Favoris', s: <?= $filtre_type_champ == 6 ? "true" : "false" ?> }, { v: 9, l: 'Comp&eacute;titions ', s: <?= $filtre_type_champ == 9 ? "true" : "false" ?> } ] });
<? $sports = "{ v: 99, l: 'Sports ', s: ".($sport_sort == 99 ? "true" : "false")."}"; reset($libelle_genre); while (list($cle, $val) = each($libelle_genre)) { $sports .= ($sports == "" ? "" : ",")."{ v: '".$cle."', l: '".Wrapper::stringEncode4JS($val)."', s: ".($cle == $sport_sort ? "true" : "false")." }"; } ?>
choices.build({ name: 'sport_sort',  c1: 'blue purple-title-card', c2: 'white', singlepicking: true, removable: true, callback: 'sport_sort', values: [ <?= $sports ?> ] });
<? } ?>
</script>

<?

function filterTeamsList($list, $joueurs)
{
	$tab = array();

	$j = array();
	$tmp = explode(",", $joueurs);
	foreach($tmp as $t) $j[$t] = $t;

	foreach($list as $item)
	{
		$ok = true;
		$tmp2 = explode("|", $item['joueurs']);
		foreach($tmp2 as $x) if (!isset($j[$x])) $ok = false;

		if ($ok) $tab[] = $item;
	}

	return $tab;
}

function filterList($list, $start, $delta)
{

	$tab = array();

	$i = 0;
	foreach($list as $item)
	{
		if ($i >= $start && $i < ($start+$delta)) $tab[] = $item;
		$i++;
	}

	return $tab;
}



?>