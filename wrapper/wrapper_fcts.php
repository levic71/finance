<?

class Wrapper
{

public static function Log($msg, $level = _DEBUG_)
{
	$level_log = _DEBUG_; 			// _DEBUG_, _INFO_, _WARM_, _ERROR_, _FATAL_

	ob_start();
	print_r($msg);
	$str = ob_get_contents();
	ob_end_clean();

	if ($level >= $level_log) {
		error_log(date('Y.m.d | H:i:s')." | ".$_SERVER['REMOTE_ADDR']." | ".$str."\n", 3, "../my.log");
	}
}

public static function clearLog()
{
	$fh = fopen('../my.log', 'w');
	fclose($fh);
}

public static function getRequest($name, $default)
{
	return (isset($_REQUEST[$name]) ? utf8_decode(urldecode($_REQUEST[$name])) : $default);
}

public static function getChampionnat($idc) {

    $sql = "SELECT c.gestion_buteurs, c.twitter, c.theme, c.forfait_penalite_bonus, c.forfait_penalite_malus, c.home_list_headcount, c.logo_font, c.logo_photo, c.entity entity, c.gestion_fanny, c.gestion_sets, c.tri_classement_general, c.type_sport, c.demo, c.gestion_nul, c.friends friends, c.type_lieu type_lieu, c.email email, c.login login, c.pwd pwd, c.description description, c.lieu lieu, c.gestionnaire gestionnaire, c.dt_creation dt_creation, c.valeur_victoire valeur_victoire, c.valeur_defaite valeur_defaite, c.valeur_nul valeur_nul, c.visu_journee visu_journee, c.news news, c.options options, c.id championnat_id, s.id saison_id, c.type type, c.nom championnat_nom, s.nom saison_nom FROM jb_championnat c, jb_saisons s WHERE c.id=".$idc." AND c.id=s.id_champ AND s.active=1";
    $res = dbc::execSQL($sql);

	if ($res) return mysqli_fetch_array($res);

	return null;
}

public static function formatNumber($n) {

	if ($n < 1000) return $n;

	return (round($n/1000, 1))."k";
}

public static function formatPhotoJoueur($photo1, $photo2 = "") { return ( $photo2 == "" ? ($photo1 == "" ? "img/user-icon.png" : $photo1) : $photo2); }
public static function formatPhotoEquipe($photo1, $photo2 = "") { return ( $photo2 == "" ? ($photo1 == "" ? "img/team-icon.png" : $photo1) : $photo2); }

public static function getPrevNextJournees($idj)
{
	global $sess_context;

	$tmp = explode("|", $idj);

	if (!isset($tmp[1]) || ($tmp[1] != "next" && $tmp[1] != "prev")) return 0;

	$journees = array();
    $select = "select id, date, nom from jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." ORDER BY date ASC";
    $res = dbc::execSQL($select);
    while($row = mysqli_fetch_array($res)) $journees[] = $row;

    $index_selected = -1;
    if (count($journees) > 0)
    {
        while(list($cle, $valeur) = each($journees))
        {
            if ($valeur['id'] == $tmp[0])
            {
                $index_selected = $cle;
                break;
            }
        }
    }

    if ($index_selected != -1 && $tmp[1] == "prev")
        if ($index_selected != 0) return $journees[--$index_selected];

    if ($index_selected != -1 && $tmp[1] == "next")
        if ($index_selected != (count($journees) - 1)) return $journees[++$index_selected];

	return $journees[$index_selected];
}

public static function setNextMatchTournoi($idm)
{
	$sql1 = "SELECT * FROM jb_matchs WHERE id=".$idm;
	$res1 = dbc::execSQL($sql1);
	if ($match1 = mysqli_fetch_array($res1)) {
		if ($match1['resultat'] != '' && $match1['resultat'] != '/') {
			$vainqueur = StatsJourneeBuilder::kikiGagne($match1);
			$sm = new StatMatch($match1['resultat'], $match1['nbset']);
			$score = $sm->getScore();
			$tmp = explode('|', $match1['niveau']);

			if ($tmp[1] == 1 || ($tmp[0] != 'Y' && $tmp[0] != 'F')) return;

			$niveau = $tmp[0]."|".floor($tmp[1]/2)."|".ceil($tmp[2]/2);
			$sql2 = "SELECT * FROM jb_matchs WHERE id_champ=".$match1['id_champ']." and id_journee=".$match1['id_journee']." and niveau='".$niveau."'";
			$res2 = dbc::execSQL($sql2);

			if ($match2 = mysqli_fetch_array($res2)) {
				$sql = "UPDATE jb_matchs SET  ".(($tmp[2] % 2) == 0 ? "id_equipe2" : "id_equipe1")."=".($vainqueur == 1 ? $match1['id_equipe1'] : $match1['id_equipe2'])."  WHERE id_champ=".$match2['id_champ']." AND id=".$match2['id'].";";
				$res = dbc::execSQL($sql);
			}
			else
			{
				$eq1 = 0;
				$eq2 = 0;

				if (($tmp[2] % 2) == 0)
					$eq2 = $vainqueur == 1 ? $match1['id_equipe1'] : $match1['id_equipe2'];
				else
					$eq1 = $vainqueur == 1 ? $match1['id_equipe1'] : $match1['id_equipe2'];

				$sql = "INSERT INTO jb_matchs (id_champ, id_journee, id_equipe1, id_equipe2, resultat, nbset, niveau) VALUES (".$match1['id_champ'].", ".$match1['id_journee'].", ".$eq1.", ".$eq2.", '0/0', 1, '".$niveau."');";
				$res = dbc::execSQL($sql);
			}
		}
	}
}

public static function getClassScore($score1, $score2, $eq)
{
	$ret = "";

	if ($score1 == "" || $score2 == "") return $ret;

	if ($eq == 1)
		$ret = ($score1 > $score2) ? "score_gagne" : "score_perdu";

	if ($eq == 2)
		$ret = ($score2 > $score1) ? "score_gagne" : "score_perdu";

	return $ret;
}

public static function formatScore($val)
{
	if (!isset($val['nbset']) || $val['nbset'] <  1) $val['nbset'] = 0;

	// Mise en forme du score
	$sm = new StatMatch($val['resultat'], $val['nbset']);
	$score = $sm->getScore();

	if (!isset($score[0][0])) $score[0][0] = "";
	if (!isset($score[0][1])) $score[0][1] = "";
	if (!isset($score[0][2])) $score[0][2] = "";
	if (!isset($score[0][3])) $score[0][3] = "";
	if (!isset($score[0][4])) $score[0][4] = "";
	if (!isset($score[1][0])) $score[1][0] = "";
	if (!isset($score[1][1])) $score[1][1] = "";
	if (!isset($score[1][2])) $score[1][2] = "";
	if (!isset($score[1][3])) $score[1][3] = "";
	if (!isset($score[1][4])) $score[1][4] = "";

	$lib = "<table cellpadding=\"0\" cellspacing=\"0\" class=\"score".($val['nbset'] > 1 ? " sets _".$val['nbset']."sets" : "")."\">";
	$lib .= "<tr>";

	// Gestion des forfaits d'?quipes pour l'affichage du score
	if ($score == -1 || $score == -2)
	{
		$lib .= "<td class=\"score_perdu\">forfait</td>";
	}
	else
	{
		if ($val['nbset'] >  1) {
			$lib .= "<td><ul><li class=\"".Wrapper::getClassScore($score[0][0], $score[0][1], 1)."\">".($score[0][0] == "" ? "-" : $score[0][0])."</li><li class=\"".Wrapper::getClassScore($score[0][0], $score[0][1], 2)."\">".($score[0][1] == "" ? "-" : $score[0][1])."</li></ul></td>";
			if ($val['nbset'] >= 2) $lib .= "<td><ul><li class=\"".Wrapper::getClassScore($score[1][0], $score[1][1], 1)."\">".($score[1][0] == "" ? "-" : $score[1][0])."</li><li class=\"".Wrapper::getClassScore($score[1][0], $score[1][1], 2)."\">".($score[1][1] == "" ? "-" : $score[1][1])."</li></ul></td>";
			if ($val['nbset'] >= 3) $lib .= "<td><ul><li class=\"".Wrapper::getClassScore($score[2][0], $score[2][1], 1)."\">".($score[2][0] == "" ? "-" : $score[2][0])."</li><li class=\"".Wrapper::getClassScore($score[2][0], $score[2][1], 2)."\">".($score[2][1] == "" ? "-" : $score[2][1])."</li></ul></td>";
			if ($val['nbset'] >= 4) $lib .= "<td><ul><li class=\"".Wrapper::getClassScore($score[3][0], $score[3][1], 1)."\">".($score[3][0] == "" ? "-" : $score[3][0])."</li><li class=\"".Wrapper::getClassScore($score[3][0], $score[3][1], 2)."\">".($score[3][1] == "" ? "-" : $score[3][1])."</li></ul></td>";
			if ($val['nbset'] >= 5) $lib .= "<td><ul><li class=\"".Wrapper::getClassScore($score[4][0], $score[4][1], 1)."\">".($score[4][0] == "" ? "-" : $score[4][0])."</li><li class=\"".Wrapper::getClassScore($score[4][0], $score[4][1], 2)."\">".($score[4][1] == "" ? "-" : $score[4][1])."</li></ul></td>";
		} else {
			$lib .= "<td class=\"".Wrapper::getClassScore($score[0][0], $score[0][1], 1)."\">".($score[0][0] == "" ? "-" : $score[0][0])."</td>";
			$lib .= "<td class=\"".Wrapper::getClassScore($score[0][0], $score[0][1], 2)."\">".($score[0][1] == "" ? "-" : $score[0][1])."</td>";
		}
	}
	$lib .= "</tr></table>";

	return $lib;
}

public static function displayMatchesStats($idj, $complete) {

	global $sess_context;

	// On recupere les infos de la journée
	$sql = "SELECT * FROM jb_journees WHERE id=".(isset($idj) && $idj != "" ? $idj : $sess_context->getJourneeId());
	$res = dbc::execSQL($sql);
	$row = mysqli_fetch_array($res);
//	if (!$row) return;

	$classement_joueurs = "";
	// Si le champ 'joueurs' est renseignié alors on affiche les stats des joueurs (en principe, jamais vide)
	if ($row['joueurs'] != "")
	{
		// On r?cup?res les infos des joueurs (avec init classement vierge si besoin)
		$req = "SELECT * FROM jb_joueurs WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id IN (".SQLServices::cleanIN($row['joueurs']).") ORDER BY pseudo ASC";
		$res = dbc::execSql($req);
		while($j = mysqli_fetch_array($res))
		{
			if ($classement_joueurs != "") $classement_joueurs .= "|";
			$joueurs[$j['id']] = strlen($j['pseudo']) > 0 ? $j['pseudo'] : $j['nom']." ".$j['prenom'];
			$classement_joueurs .= $j['id']."@".StatJourneeJoueur::vierge();
		}

		if ($row['classement_joueurs'] != "") $classement_joueurs = $row['classement_joueurs'];
	}

	$classement_equipes = "";
	// Si le champ 'equipes' est renseignié alors on affiche les stats des équipes
	if ($row['equipes'] != "")
	{
		// On récupéres les infos des joueurs (avec init classement vierge si besoin)
		$req = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id IN (".SQLServices::cleanIN($row['equipes']).") ORDER BY nom ASC";
		$res = dbc::execSql($req);
		while($eq = mysqli_fetch_array($res))
		{
			if ($classement_equipes != "") $classement_equipes .= "|";
			$equipes[$eq['id']] = $eq['nom'];
			$classement_equipes .= $eq['id']."@".StatJourneeTeam::vierge();
		}

		if ($row['classement_equipes'] != "") $classement_equipes = $row['classement_equipes'];
	}

	$t = array();
	if ($sess_context->isFreeXDisplay())
	{
		if ($classement_joueurs != "")
		{
			$cl = explode('|', $classement_joueurs);
			foreach($cl as $c)
			{
				$sjj = new StatJourneeJoueur();
				$sjj->init($c);

				$obj = get_object_vars($sjj);
				if (isset($joueurs[$obj['id']])) {
					$obj['joueur'] = $joueurs[$obj['id']];
					$obj['matchs_joues']  = $obj['matchs_jouesA'] + $obj['matchs_jouesD'];
					if ($obj['diff_attaquant'] > 0) $obj['diff_attaquant'] = "+".$obj['diff_attaquant'];
					if ($obj['diff_defenseur'] > 0) $obj['diff_defenseur'] = "+".$obj['diff_defenseur'];
					if ($obj['diff'] > 0) $obj['diff'] = "+".$obj['diff'];

					$t[] = $obj;
				}
			}
		}
	}

	// Stats Equipes (pas vraiment utile !!!!!!)
	if ($sess_context->isChampionnatXDisplay() && false)
	{
		if ($classement_equipes != "")
		{
			echo "<tr><td>";
			$fxlist = new FXListMatchsStatsEquipes($classement_equipes, $equipes);
			$fxlist->FXDisplay();
			echo "</td>";
		}
	}

?>

<div id="box3" class="vgrid" style="clear: both; margin-top: 20px;">
<h2 class="grid leagues">Statistiques journï¿½e</h2>
<table cellspacing="0" cellpadding="0" class="jkgrid matches_grid" id="table_players">
<thead><tr><th class="c1"><div>N?</div></th><th class="c2"><div>Joueur</div></th><th class="c3"><div>J</div></th><th class="c4"><div>G</div></th><th class="c5"><div>AVG</div></th><th class="c6"><div>FI</div></th><th class="c7"><div>FO</div></th></tr></thead>
<tbody>
<? $i = 1; foreach($t as $j) { ?><tr id="tr_<?= $i ?>" class="clickonit" onclick="mm({action:'stats', idp:'<?= $j['id'] ?>'});"><td class="c1"><div><?= $i++ ?></div></td><td class="c2"><div><?= $j['joueur'] ?></div></td><td class="c3"><div><?= $j['matchs_joues'] ?></div></td><td class="c4"><div><button class="button gray bigrounded"><?= $j['matchs_gagnes'] ?></button></div></td><td class="c5"><div><button class="button bigrounded <?= $j['diff'] >= 0 ? "green" : "red" ?>"><?= $j['diff'] ?></button></div></td><td class="c6"><div><?= $j['fanny_in'] ?></div></td><td class="c7"><div><?= $j['fanny_out'] ?></div></td></tr><? } ?>
<? if ($complete) for($x=$i; $x <= sess_context::getHomeListHeadcount(); $x++) { ?><tr id="tr_<?= $x ?>"><td class="c1"><div><?= $x ?></div></td><td class="c2"><div>-</div></td><td class="c3"><div>0</div></td><td class="c4"><div><button class="button bigrounded disable">0</button></div></td><td class="c5"><div><button class="button bigrounded disable">0</button></div></td><td class="c6"><div>-</div></td><td class="c7"><div>-</div></td></tr><? } ?>
</tbody>
</table>
</div>

<?

}

public static function reformatTournoiClassement($tab) {

	$x = 0;
	$newtab = array();
//	print_r($tab);
	foreach($tab as $item)
	{
		$newrow = array();
		if ($item['1'] == "X") continue;

		$newrow['id'] = preg_replace("/ onmouse.*/", "", preg_replace("/<.*id_detail=/", "", str_replace("</A>", "", $item[1])));
		$newrow['nom'] = $item[1];
		$newrow['points'] = $item[2];
		$newrow['matchs_joues'] = $item[3];
		$newrow['matchs_gagnes'] = $item[4];
		$newrow['matchs_nuls'] = "";
		$newrow['matchs_perdus'] = $item[5];
		$newrow['sets_joues'] = "";
		$newrow['sets_nuls'] = "";
		$newrow['sets_perdus'] = "";
		$newrow['sets_diff'] = "";
		$newrow['moy_classement'] = isset($item[13]) ? $item[13] : "";
		$newrow['buts_marques'] = $item[10];
		$newrow['buts_encaisses'] = $item[11];
		$newrow['diff'] = count($item) == 16 ? $item[12] : $item[9];

		$newtab[$x++] = $newrow;
	}

	return $newtab;
}

public static function getLastJourneePlayed($saison_id) {

	$ret = 0;

	$req = "SELECT date, id FROM jb_journees WHERE id_champ=".$saison_id." AND TO_DAYS(now())-TO_DAYS(date) >= 0 ORDER BY date DESC LIMIT 0,1";
	$res = dbc::execSQL($req);
	if ($row = mysqli_fetch_array($res)) {
		$ret = $row['id'];
	} else {
		$req = "SELECT date, id FROM jb_journees WHERE id_champ=".$saison_id." AND TO_DAYS(now())-TO_DAYS(date) <= 0 ORDER BY date ASC LIMIT 0,1";
		$res = dbc::execSQL($req);
		if ($row = mysqli_fetch_array($res)) $ret = $row['id'];
	}

	return $ret;
}

public static function stringEncode4JS($str) {
	return addslashes($str);
}

public static function string2DNS($str) {

	$name = trim($str, "-");
	$name = trim($name, " ");
	$name = strtr($name, 'ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½ï¿½', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
	$name = strtr($name, '+.,|!"ï¿½$%&/()=?^*ç°§;:_>][@);', '                             ');
	$name = str_replace('\\', '', $name);
	$name = str_replace('\'', '', $name);
	$name = str_replace('-', ' ', $name);  /* ' */
	while(substr_count($name,"  ") != 0) $name = str_replace("  ", " ", $name);
	$name = str_replace(' ', '-', strtolower($name));

	return $name;
}


public static function linearRegression($x, $y) {

  // calculate number points
  $n = count($x);

  // ensure both arrays of points are the same size
  if ($n != count($y)) {

    trigger_error("linear_regression(): Number of elements in coordinate arrays do not match.", E_USER_ERROR);

  }

  // calculate sums
  $x_sum = array_sum($x);
  $y_sum = array_sum($y);

  $xx_sum = 0;
  $xy_sum = 0;

  for($i = 0; $i < $n; $i++) {

    $xy_sum+=($x[$i]*$y[$i]);
    $xx_sum+=($x[$i]*$x[$i]);

  }

  // calculate slope (pente)
  $m = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));

  // calculate intercept
  $b = ($y_sum - ($m * $x_sum)) / $n;

  // return result
  return array("m"=>$m, "b"=>$b);
}


public static function getColorFromFormeIndice($indice) { return ($indice == 0 ? "disable" : ($indice == 1 || $indice == 8 ? "red" : ($indice == 2 ? "orange" : ($indice == 3 ? "yellow" : ($indice == 4 || $indice == 9 ? "green" : "blue"))))); }
public static function getColorFromSexeIndice($indice) { return ($indice == 1 ? "blue" : "rosy"); }
public static function getColorFromPourcentage($indice) { return ($indice >= 80 ? "blue" : ( $indice >= 60 ? "green" : ($indice >= 40 ? "yellow" : ($indice >= 20 ? "orange" : "red")))); }
public static function getIconIndiceFromPourcentage($indice) { return ($indice >= 80 ? "5" : ( $indice >= 60 ? "4" : ($indice >= 40 ? "3" : ($indice >= 20 ? "2" : "1")))); }
public static function extractFormeIndice($indice) { $str = (substr(substr($indice, 0, 34), 33)); if (strstr($indice, "blesse")) $str = 8; if (strstr($indice, "vacance")) $str = 9; return ($str == "." ? "0" : $str); }
public static function getColorMedaille($medaille) { global $libelle_medaille; return $libelle_medaille[$medaille == "" ? _NO_MEDAILLE_ : $medaille]; }

public static function isUserDataPrivate($user)     {
	global $sess_context;
	$ret = false;
	if (isset($user['confidentialite']) && $user['confidentialite'] == 2) $ret = true;
	if (isset($user['id']) && $sess_context->isUserConnected() && $user['id'] == $sess_context->user['id']) $ret = false;
	return $ret;
}
public static function isUserDataSemiPrivate($user) {
	global $sess_context;
	$ret = false;
	if (isset($user['confidentialite']) && $user['confidentialite'] == 1) $ret = true;
	if (isset($user['id']) && $sess_context->isUserConnected() && $user['id'] == $sess_context->user['id']) $ret = false;
	return $ret;
}
public static function isUserDataPublic($user) {
	global $sess_context;
	$ret = false;
	if (isset($user['confidentialite']) && $user['confidentialite'] == 0) $ret = true;
	if (isset($user['id']) && $sess_context->isUserConnected() && $user['id'] == $sess_context->user['id']) $ret = true;
	return $ret;
}

public static function resetChPwdToken($token) {
	$udt = "UPDATE jb_users SET reset_time=0, reset_count=0, reset_token='' WHERE removed=0 AND reset_token='".$token."'";
	$res = dbc::execSQL($udt);
}

public static function increaseCountChPwdToken($token) {
	$udt = "UPDATE jb_users SET reset_count=reset_count+1 WHERE removed=0 AND reset_token='".$token."'";
	$res = dbc::execSQL($udt);
}

public static function isChPwdValid($token) {
	$ret = false;

	$sql = "SELECT count(*) total FROM jb_users WHERE removed=0 AND reset_token='".$token."'";
    $res = dbc::execSQL($sql);

	if ($res) {
		$row = mysqli_fetch_array($res);
		if ($row['total'] >= 1) { // Au cas ou il y a d'ancien compte avec le meme mail
			$sql2 = "SELECT reset_time, reset_count FROM jb_users WHERE reset_token='".$token."'";
			$res2 = dbc::execSQL($sql2);
			$row2 = mysqli_fetch_array($res2);
			if ($row2['reset_count'] <= 10 && (time() - $row2['reset_time']) < 600)
			{
				Wrapper::increaseCountChPwdToken($token);
				$ret = true;
			}
			else
				Wrapper::resetChPwdToken($token);
		}
	}

	return $ret;
}

public static function fb_tag($name, $url)      {
	global $sess_context, $libelle_genre;
	$ret = "";

	$ret .= "http://www.facebook.com/dialog/feed?";
	$ret .= "app_id=107452429322746&";
	$ret .= "link=http://www.jorkers.com&";
	$ret .= "picture=http://www.jorkers.com/wrapper/img/logo.png&";
	$ret .= "name=".utf8_encode($name)."&";
	$ret .= "caption=".utf8_encode($libelle_genre[$sess_context->getTypeSport()])." :: ".utf8_encode(($sess_context->isTournoiXDisplay() ? "Tournoi " : "Championnat ").$sess_context->getChampionnatNom())."&";
	$ret .= "description=".utf8_encode("Jorkers.com, solution de gestion de championnats et tournois de sports individuels et collectifs")."&";
	$ret .= "message=".utf8_encode("Laisser un message !")."&";
	$ret .= "redirect_uri=".$url;

	return $ret;
}

public static function fab_button_menu($items) {

    if (count($items) == 0) return;

?>
    <nav class="fabmenu">
        <input type="checkbox" href="#" class="menu-open" name="menu-open" id="menu-open" />
        <? for($i=0; $i < count($items); $i++) { ?>
            <a href="<?= isset($items['href']) ? $items['href'] : "#" ?>" <?= isset($items['target']) ? 'target="'.$items['target'].'"' : "" ?> class="mdl-color--cyan menu-item <?= $items[$i]['id'] == "swap1" || $items[$i]['id'] == "swap2" ? "swap" : "" ?> <?= $items[$i]['id'] ?> ToolText" id="<?= $items[$i]['id'] ?>" onclick="<?= $items[$i]['onclick'] ?>" onmouseover="showtip('<?= $items[$i]['id'] ?>');"><span><?= $items[$i]['tooltip'] ?></span><? if (isset($items[$i]['puce']) && $items[$i]['puce'] != "") { ?><button class="button bigrounded red pucecounter"><?= $items[$i]['puce'] ?></button><? } ?></a>
        <? } ?>
<? if (count($items) > 1) { ?>
        <label class="menu-open-button" for="menu-open">
            <span class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--colored menu-open-button"><i class="material-icons">keyboard_arrow_up</i></span>
        </label>
<? } ?>
    </nav>
<?
}

public static function annuler_valider_buttons($items) {
	$items[0]['color'] = isset($items[0]['color']) ? $items[0]['color'] : "mdl-color-text--grey";
	$items[0]['libelle'] = isset($items[0]['libelle']) ? $items[0]['libelle'] : "Annuler";
	$items[1]['libelle'] = isset($items[1]['libelle']) ? $items[1]['libelle'] : "Valider";
	Wrapper::two_action_buttons($items);
}

public static function two_action_buttons($items) {

	if (count($items) != 2) return;

	?>
	<div <?= isset($item['id']) ? "id=\"".$item['id']."\"" : "" ?> class="mdl-card__actions mdl-card--border mdl-grid">
		<button class="mdl-button mdl-button--colored <?= isset($items[0]['color']) ? $items[0]['color'] : "" ?> mdl-js-button mdl-js-ripple-effect mdl-cell mdl-cell--6-col mdl-cell--2-col-phone mdl-typography--text-nowrap" onclick="<?= $items[0]['onclick'] ?>"><?= $items[0]['libelle'] ?></button>
		<button class="mdl-button mdl-button--colored <?= isset($items[1]['color']) ? $items[1]['color'] : "" ?> mdl-js-button mdl-js-ripple-effect mdl-cell mdl-cell--6-col mdl-cell--2-col-phone" onclick="<?= $items[1]['onclick'] ?>"><?= $items[1]['libelle'] ?></button>
	</div>
<?
}

public static function freezone_form($item) { echo $item['freezone']; }

public static function divider_form($item) { ?>
	<hr />
<? }

public static function textfield_form($item) { ?>
	<i class="mdl-textfield__icon material-icons"><?= $item['icon'] ?></i><input class="mdl-textfield__input" type="<?= isset($item['password']) ? "password" : "text" ?>" id="<?= $item['id'] ?>" value="<?= $item['value'] ?>" <?= isset($item['required']) ? "required" : ""?> <?= isset($item['autofocus']) ? "autofocus" : "" ?> />
	<label class="mdl-textfield__label" for="<?= $item['id'] ?>"><?= $item['libelle'] ?></label>
<? }

public static function checkbox_form($item) { ?>
	<label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="<?= $item['id'] ?>">
		<input type="checkbox" id="<?= $item['id'] ?>" <?= $item['checked'] == 1 ? "checked" : ""?> <?= isset($item['autofocus']) ? "autofocus" : "" ?> class="mdl-checkbox__input">
		<span class="mdl-checkbox__label"><?= $item['libelle'] ?></span>
	</label>
<? }

public static function textarea_form($item) { ?>
	<i class="mdl-textfield__icon material-icons"><?= $item['icon'] ?></i><textarea class="mdl-textfield__input" rows="3" type="text" id="<?= $item['id'] ?>" <?= isset($item['required']) ? "required" : "" ?>><?= $item['value'] ?></textarea>
	<label class="mdl-textfield__label" for="<?= $item['id'] ?>"><?= $item['libelle'] ?></label>
<? }

public static function textfield_place_form($item) { ?>
	<i class="mdl-textfield__icon material-icons"><?= $item['icon'] ?></i><input class="mdl-textfield__input" type="text" id="<?= $item['id'] ?>" value="<?= $item['value'] ?>" <?= isset($item['required']) ? "required" : "" ?> />
	<a href="#" style="float:left;" onclick="choosegooglemap();"><img title="Gï¿½olocalisation" src="img/download.png" /></a>
	<label class="mdl-textfield__label" for="<?= $item['id'] ?>"><?= $item['libelle'] ?></label>
<? }

public static function upload_image_form($item) { ?>
	<i class="mdl-textfield__icon material-icons"><?= $item['icon'] ?></i>
	<ul class="upload_target"><li><img style="width: 64px; height: 64px;" id="img_target" src="<?= $item['value'] ?>" /></li><li id="f1_upload_process"><img src="img/loader.gif" /></li><li id="f1_upload_ok"><img src="img/tick_32.png" /></li><li id="f1_upload_err"><img src="img/block_32.png" /></li></ul>
	<input type="hidden" name="<?= $item['id'] ?>" id="<?= $item['id'] ?>" value="<?= $item['value'] ?>" <?= isset($item['required']) ? "required" : "" ?> />

	<form name="uploadform" action="upload.php?target_image=img_target&target_upload=photo&<?= isset($item['extra']) ? $item['extra'] : "image" ?>=1" style="clear: both;" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="startUpload();" >
		<span id="f1_upload_form">
			<label for="myfile">&nbsp;</label><input name="myfile" id="myfile" type="file" size="30" /><button onclick="startUpload();" class="button blue">Upload</button>
		</span>
		<iframe id="upload_target" name="upload_target" src="vide.html" style="width:0;height:0;border:0px solid #fff;"></iframe>
	</form>

	<label class="mdl-textfield__label" for="<?= $item['id'] ?>"><?= $item['libelle'] ?></label>
<? }

public static function captcha_form($item) { ?>
	<i class="mdl-textfield__icon material-icons"><?= $item['icon'] ?></i>
	<div class="slide-to-unlock old-slider">
		<div id="<?= $item['id'] ?>" class="dragdealer">
			<div class="slide-text">slide to unlock</div>
			<div class="handle"></div>
		</div>
	</div>
	<label class="mdl-textfield__label" for="<?= $item['id'] ?>"><?= $item['libelle'] ?></label>
<? }

public static function captcha2_form($item) { ?>
	<i class="mdl-textfield__icon material-icons"><?= $item['icon'] ?></i>
	<input style="float: left; margin-top: 8px; margin-right: 15px;" type="text" id="<?= $item['id'] ?>" name="<?= $item['id'] ?>" size="32" maxlength="16" value="<?= $item['value'] ?>" /><img style="float: left;" src="../include/codeimage.php?<?= ToolBox::getRand(5) ?>" />
	<label class="mdl-textfield__label" for="<?= $item['id'] ?>"><?= $item['libelle'] ?></label>
<? }

public static function choice_component_form($item) { ?>
	<i class="mdl-textfield__icon material-icons"><?= $item['icon'] ?></i><div id="<?= $item['id'] ?>" <?= isset($item['grouped']) ? "class=\"grouped\"" : "" ?>></div>
	<label class="mdl-textfield__label" for="<?= $item['id'] ?>"><?= $item['libelle'] ?></label>
<? }

public static function number_component_form($item) { ?>
	<i class="mdl-textfield__icon material-icons"><?= $item['icon'] ?></i>
	<button class="button orange" id="<?= $item['id'] ?>" onclick="numbers.picker({ name: '<?= $item['id'] ?>', start: <?= $item['start'] ?>, end: <?= $item['end'] ?> });"><?= $item['value'] ?></button>
	<label class="mdl-textfield__label" for="<?= $item['id'] ?>"><?= $item['libelle'] ?></label>
<? }

public static function calendar_component_form($item) { ?>
	<i class="mdl-textfield__icon material-icons"><?= $item['icon'] ?></i>

	<div class="singlepicking">
		<button class="button blue" id="<?= $item['id'] ?>" onclick="calendar.picker({ name: '<?= $item['id'] ?>' });"><span><?= $item['value'] ?></span></button>
	</div>

	<label class="mdl-textfield__label" for="<?= $item['id'] ?>"><?= $item['libelle'] ?></label>
<? }

public static function item_form($item) {

	$classes  = "";
	$classes .= "mdl-textfield mdl-js-textfield mdl-textfield--floating-label mdl-cell ".($item['func'] == "divider_form" ? "mdl-divider " : "");
	$classes .= "mdl-cell--".$item['nb_col']."-col";
	$classes .= ($item['func'] == "calendar_component_form" || $item['func'] == "choice_component_form" || $item['func'] == "number_component_form" || $item['func'] == "captcha_form" || $item['func'] == "upload_image_form") ? " is-dirty " : "";
	$classes .= ($item['func'] == "captcha_form") ? " is-captcha " : "";
	$classes .= isset($item['required']) ? " mdl-textfield--required " : "";

	if (!isset($item['value'])) $item['value'] = "";
?>
	<div id="<?= $item['id']."-box" ?>" class="<?= $classes ?>"  <?= ($item['func'] == "checkbox_form") ? 'style="padding-left: 50px;"' : '' ?> >
		<? call_user_func("Wrapper::".$item['func'], $item); ?>
	</div>
<? }

public static function build_form($options) {

	Wrapper::template_box_start(isset($options['nb_col']) ? $options['nb_col'] : 12);
	Wrapper::template_box_title(isset($options['title'])  ? $options['title']  : "");

	if (isset($options['menu'])) { ?>
	<div class="mdl-card__menu">
		<?= $options['menu'] ?>
	</div>
	<? } ?>
	<div class="mdl-card__supporting-text form-group mdl-grid">
		<? foreach($options['items'] as $t) Wrapper::item_form($t); ?>
	</div>
	<? if (isset($options['actions'])) Wrapper::annuler_valider_buttons($options['actions']); ?>
<?
	Wrapper::template_box_end();
}

public static function template_box_start($nb_col) { ?>
	<div class="mdl-layout-spacer"></div>
	<div class="mdl-card mdl-shadow--6dp mdl-cell mdl-cell--<?= $nb_col ?>-col mdl-cell--12-tablet mdl-cell--4-col-phone mdl-cell--middle">
<? }

public static function template_box_end() { ?>
	</div>
	<div class="mdl-layout-spacer"></div>
<? }

public static function template_box_title($title) { ?>
	<div class="mdl-card__title mdl-color--primary mdl-color-text--white">
		<h2 class="mdl-cell mdl-cell--12-col mdl-card__title-text mdl-color--primary"><?= $title ?></h2>
	</div>
<? }

public static function javascript_form($otions) { }

}

?>
