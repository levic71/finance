<?

// /////////////////////////////////////////////////////////////////////////////////////
// CLASSEMENT SQLServices
// /////////////////////////////////////////////////////////////////////////////////////
class SQLServices
{
	var $championnat;

	function __construct($championnat)
	{
		$this->championnat = $championnat;
	}

	public static function cleanIN($str) {

		if ($str == "" || !strstr($str, ',')) return $str;

		$res = "";

		$tmp = explode(",", $str);
		foreach($tmp as $item) {
			$val = trim($item);
			if ($val != "") $res .= ($res == "" ? "" : ",").$item;
		}

		return $res;
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// SERVICES CHAMPIONNATS
// /////////////////////////////////////////////////////////////////////////////////////
class SQLChampionnatsServices extends SQLServices
{
	function __construct($championnat = -1)
	{
		parent::__construct($championnat);
	}

	function getChampionnat($id = "")
	{
		$req = "SELECT c.gestion_buteurs, c.twitter, c.theme, c.forfait_penalite_bonus, c.forfait_penalite_malus, c.home_list_headcount, c.logo_font, c.logo_photo, c.zoom, c.lat, c.lng, c.type_gestionnaire, c.gestion_fanny, c.gestion_sets, c.tri_classement_general, c.type_sport, c.demo, c.gestion_nul, c.friends friends, c.type_lieu type_lieu, c.email email, c.login login, c.pwd pwd, c.description description, c.lieu lieu, c.gestionnaire gestionnaire, c.dt_creation dt_creation, c.valeur_victoire valeur_victoire, c.valeur_defaite valeur_defaite, c.valeur_nul valeur_nul, c.visu_journee visu_journee, c.news news, c.options options, c.id championnat_id, s.id saison_id, c.type type, c.nom championnat_nom, s.nom saison_nom FROM jb_championnat c, jb_saisons s WHERE c.id=".($id == "" ? $this->championnat : $id)." AND c.id=s.id_champ AND s.active=1";
		$res = dbc::execSql($req);
		$row = mysqli_fetch_array($res);

		return $row;
	}

	function getChampionnatByNom($nom)
	{
		$req = "SELECT c.logo_font, c.logo_photo, c.zoom, c.lat, c.lng, c.type_gestionnaire, c.gestion_fanny, c.gestion_sets, c.tri_classement_general, c.type_sport, c.gestion_nul, c.friends friends, c.id id_champ, c.type_lieu type_lieu, c.email email, c.login login, c.pwd pwd, c.description description, c.lieu lieu, c.gestionnaire gestionnaire, c.dt_creation dt_creation, c.valeur_victoire valeur_victoire, c.valeur_defaite valeur_defaite, c.valeur_nul valeur_nul, c.visu_journee visu_journee, c.news news, c.options options, c.id championnat_id, s.id saison_id, c.type type, c.nom championnat_nom, s.nom saison_nom FROM jb_championnat c, jb_saisons s WHERE c.nom='".$nom."' AND c.id=s.id_champ AND s.active=1";
		$res = dbc::execSql($req);
		$row = mysqli_fetch_array($res);

		return $row;
	}

	function getChampionnatByPKeysWhere($pkeys_where)
	{
		$req = "SELECT * FROM jb_championnat ".$pkeys_where;
		$res = dbc::execSql($req);
		$row = mysqli_fetch_array($res);

		return $row;
	}

	function getNbChampionnatsParType($type = 0)
	{
		$nb = 0;

		$req = "SELECT COUNT(*) total FROM jb_championnat WHERE type=".$type;
		$res = dbc::execSQL($req);
		if ($row = mysqli_fetch_array($res)) $nb = $row['total'];

		return $nb;
	}

	function getNbSaisons()
	{
		$nb = 0;

		$req = "SELECT COUNT(*) total FROM jb_saisons ".($this->championnat != -1 ? "WHERE id_champ=".$this->championnat : "");
		$res = dbc::execSQL($req);
		if ($row = mysqli_fetch_array($res)) $nb = $row['total'];

		return $nb;
	}

	function getNbJoueurs()
	{
		$nb = 0;

		$req = "SELECT COUNT(*) total FROM jb_joueurs ".($this->championnat != -1 ? "WHERE id_champ=".$this->championnat : "");
		$res = dbc::execSQL($req);
		if ($row = mysqli_fetch_array($res)) $nb = $row['total'];

		return $nb;
	}

	function getNbEquipes()
	{
		$nb = 0;

		$req = "SELECT COUNT(*) total FROM jb_equipes ".($this->championnat != -1 ? "WHERE id_champ=".$this->championnat : "");
		$res = dbc::execSQL($req);
		if ($row = mysqli_fetch_array($res)) $nb = $row['total'];

		return $nb;
	}

	function getNbJourneesGen($where = "")
	{
		$nb = 0;

		$all_saisons = $this->getAllSaisons($this->championnat);
		if ($all_saisons != "")
		{
			$req = "SELECT COUNT(*) total FROM jb_journees ".($this->championnat != -1 ? "WHERE id_champ IN (".SQLServices::cleanIN($this->getAllSaisons($this->championnat)).") ".$where : "");
			$res = dbc::execSQL($req);
			if ($row = mysqli_fetch_array($res)) $nb = $row['total'];
		}
		else
		{
			$req = "SELECT COUNT(*) total FROM jb_journees";
			$res = dbc::execSQL($req);
			if ($row = mysqli_fetch_array($res)) $nb = $row['total'];
		}

		return $nb;
	}

	function getNbJournees()
	{
		return $this->getNbJourneesGen("");
	}

	function getNbJourneesTranche1()
	{
		return $this->getNbJourneesGen(" AND TO_DAYS(now())-TO_DAYS(date) < 15");
	}

	function getNbJourneesTranche2()
	{
		return $this->getNbJourneesGen(" AND TO_DAYS(now())-TO_DAYS(date) < 45");
	}

	function getNbJourneesTranche3()
	{
		return $this->getNbJourneesGen(" AND TO_DAYS(now())-TO_DAYS(date) < 90");
	}

	function getNbMatchs()
	{
		$nb = 0;

		$all_saisons = $this->getAllSaisons($this->championnat);
		if ($all_saisons != "")
		{
			$req = "SELECT COUNT(*) total FROM jb_matchs ".($this->championnat != -1 ? "WHERE id_champ IN (".SQLServices::cleanIN($this->getAllSaisons($this->championnat)).")" : "");
			$res = dbc::execSQL($req);
			if ($row = mysqli_fetch_array($res)) $nb = $row['total'];
		}
		else
		{
			$req = "SELECT COUNT(*) total FROM jb_matchs";
			$res = dbc::execSQL($req);
			if ($row = mysqli_fetch_array($res)) $nb = $row['total'];
		}

		return $nb;
	}

	function getNbMatchsJoues()
	{
		$nb = 0;

		$all_saisons = $this->getAllSaisons($this->championnat);
		if ($all_saisons != "")
		{
			$req = "SELECT COUNT(*) total FROM jb_matchs WHERE (match_joue = 1 OR resultat != \"0/0\") ".($this->championnat != -1 ? " AND id_champ IN (".SQLServices::cleanIN($this->getAllSaisons($this->championnat)).")" : "");
			$res = dbc::execSQL($req);
			if ($row = mysqli_fetch_array($res)) $nb = $row['total'];
		}
		else
		{
			$req = "SELECT COUNT(*) total FROM jb_matchs WHERE (match_joue = 1 OR resultat != \"0/0\") ";
			$res = dbc::execSQL($req);
			if ($row = mysqli_fetch_array($res)) $nb = $row['total'];
		}

		return $nb;
	}

	function getNbMessages()
	{
		$nb = 0;

		$req = "SELECT COUNT(*) total FROM jb_forum WHERE del=0 ".($this->championnat != -1 ? "AND id_champ=".$this->championnat : "");
		$res = dbc::execSQL($req);
		if ($row = mysqli_fetch_array($res)) $nb = $row['total'];

		return $nb;
	}

	function getAllChampionnats($light = false, $where = "")
	{
		$lst = array();

		$req = "SELECT * FROM jb_championnat ".$where." ".($where != "" ? "AND" : "WHERE")." demo=0 AND actif=1 ORDER BY nom";
		$res = dbc::execSql($req);
		while($row = mysqli_fetch_array($res))
		{
		    if ($light)
    			$lst[$row['id']] = array("id" => $row['id'], "nom" => $row['nom'], "type" => $row['type']);
		    else
    			$lst[$row['id']] = $row;
		}

		return $lst;
	}

	function getAllChampionnatsByTown($ville)
	{
		return $this->getAllChampionnats(true, "WHERE demo=0 AND actif=1 AND type_lieu=0 AND UPPER(lieu) LIKE '%".strtoupper($ville)."%'");
	}

	function getMostActiveChampionnats()
	{
		$lst = array();
		$s = array();
		$d = array();

		$req = "SELECT * FROM jb_championnat WHERE demo=0 ORDER BY dt_creation";
		$res = dbc::execSql($req);
		while($row = mysqli_fetch_array($res))
		{
			$this->championnat = $row['id'];
			$nb_journees_total = $this->getNbJournees();
			$nb_journees_T1 = $this->getNbJourneesTranche1();
			$nb_journees_T2 = $this->getNbJourneesTranche2() - $nb_journees_T1;
			$nb_journees_T3 = $this->getNbJourneesTranche3() - $nb_journees_T2 - $nb_journees_T1;
			$nb_journees_T4 = $nb_journees_total - $nb_journees_T3 - $nb_journees_T2 - $nb_journees_T1;
			$pointsmax = $nb_journees_total * 20 + $this->getNbMatchsJoues() * 3 + $this->getNbMessages();
			$points = ($nb_journees_T1 * 20 * 1) + floor($nb_journees_T2 * 20 * 0.50) + floor($nb_journees_T3 * 20 * 0.25) + floor($nb_journees_T4 * 20 * 0.125) + $this->getNbMatchsJoues() * 5 + floor($this->getNbMessages() * 0.5);

			$req2 = "SELECT TO_DAYS(NOW())-TO_DAYS(date) mydate FROM jb_journees WHERE id_champ IN (".SQLServices::cleanIN($this->getAllSaisons($this->championnat)).") ORDER BY date DESC LIMIT 0,1";
			$res2 = dbc::execSql($req2);
			if ($row2 = mysqli_fetch_array($res2)) {
				$coeff = ceil($row2['mydate'] / 400);
				if ($coeff == 0) $coeff = 1;
				$points = ceil($points / $coeff);
			}

			$lst[$row['id']] = array('id' => $row['id'], 'nom' => $row['nom'], 'points' => $points, 'pointsmax' => $pointsmax, 'type' => $row['type'], 'dt_creation' => $row['dt_creation'], 'actif' => $row['actif'], 'email' => $row['email'], 'special' => $row['special'], 'pronostic' => $row['pronostic'], 'ref_champ' => $row['ref_champ']);
			$s[$row['id']] = array('points' => $points);
			$d[$row['id']] = $row['dt_creation'];
  		}
		array_multisort($s, SORT_DESC, $d, SORT_DESC, $lst);

		return $lst;
	}

	function getLastChampionnatsCrees()
	{
		$lst = array();

		$req = "SELECT id, nom, dt_creation, type FROM jb_championnat WHERE demo=0 AND actif=1 ORDER BY CONCAT(dt_creation, LPAD(id, 10, '0')) DESC";
		$res = dbc::execSql($req);
		while($row = mysqli_fetch_array($res))
		{
			$this->championnat = $row['id'];
			$nb_journees_total = $this->getNbJournees();
			$nb_journees_T1 = $this->getNbJourneesTranche1();
			$nb_journees_T2 = $this->getNbJourneesTranche2() - $nb_journees_T1;
			$nb_journees_T3 = $this->getNbJourneesTranche3() - $nb_journees_T2 - $nb_journees_T1;
			$nb_journees_T4 = $nb_journees_total - $nb_journees_T3 - $nb_journees_T2 - $nb_journees_T1;
			$points = ($nb_journees_T1 * 20 * 1) + floor($nb_journees_T2 * 20 * 0.50) + floor($nb_journees_T3 * 20 * 0.25) + floor($nb_journees_T4 * 20 * 0.125) + $this->getNbMatchsJoues() * 5 + floor($this->getNbMessages() * 0.5);
			$row['points'] = $points;
			$lst[$row['id']] = $row;
		}

		return $lst;
	}

	function getNbChampionnats()
	{
		$req = "SELECT count(*) count FROM jb_championnat";
		$res = dbc::execSql($req);
		$row = mysqli_fetch_array($res);

		return $row['count'];
	}

	function getAllSaisons()
	{
		$ret = "";
		$req = "SELECT * FROM jb_saisons WHERE id_champ=".$this->championnat;
		$res = dbc::execSql($req);
		while($row = mysqli_fetch_array($res))
			$ret .= ($ret == "" ? "" : ",").$row['id'];

		return $ret;
	}

	function getSaisonActive()
	{
		// Récupération de la saison courante
		$req = "SELECT * FROM jb_saisons WHERE id_champ=".$this->championnat." AND active=1";
		$res = dbc::execSql($req);
		$row = mysqli_fetch_array($res);

		return $row;
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// SERVICES SAISONS
// /////////////////////////////////////////////////////////////////////////////////////
class SQLSaisonsServices extends SQLServices
{
	var $saison;

	function __construct($championnat, $saison = -1)
	{
		parent::__construct($championnat);
		$this->saison = $saison;
	}

	function getSaison()
	{
		// Récupération de la saison courante
		$req = "SELECT * FROM jb_saisons WHERE id_champ=".$this->championnat." AND id=".$this->saison;
		$res = dbc::execSql($req);
		$row = mysqli_fetch_array($res);

		return $row;
	}

	function getNbJournees()
	{
		$nb = 0;

		$req = "SELECT COUNT(*) total FROM jb_journees WHERE id_champ = ".$this->saison;
		$res = dbc::execSQL($req);
		if ($row = mysqli_fetch_array($res)) $nb = $row['total'];

		return $nb;
	}

	function getListeEquipes()
	{
		$liste = array();

		// Récupération de la saison courante
		$row = $this->getSaison();

		// Récupération des infos des équipes de la saison
		$ses = new SQLEquipesServices($this->championnat);
		$liste = $ses->getListeEquipes($row['equipes']);

		return $liste;
	}

	function getListeJoueurs()
	{
		$liste = array();

		// Récupération de la saison courante
		$row = $this->getSaison();

		// Récupération des infos des équipes de la saison
		if ($row['joueurs'] != "")
		{
			$sjs = new SQLJoueursServices($this->championnat);
			$liste = $sjs->getListeJoueursFromIds($row['joueurs']);
		}

		return $liste;
	}

	function getListeVainqueurs()
	{
		$vainqueurs = array();

		$equipes = $this->getListeEquipes();

		$req = "SELECT * FROM jb_journees WHERE id_champ=".$this->saison." ORDER BY date ASC";
		$res = dbc::execSql($req);

		while($row = mysqli_fetch_array($res))
		{
			$best = 0;
			// Recherche du vainqueur dans le classement
			if ($row['classement_equipes'] != "")
			{
				// Affectation des valeurs issues des stats
				$items = explode('|', $row['classement_equipes']);
				foreach($items as $stat)
				{
					$st = new StatJourneeTeam();
					$st->init($stat);

					// Si =0 alors le classement de cette équipe n'est pas encore connu
					if ($st->tournoi_classement == 0) continue;

					if ($st->tournoi_classement == 1) $best = $st->id;
				}
			}

			$row['vainqueur'] = $equipes[$best]['nom'];
			$vainqueurs[$row['id']] = $row;
		}

		return $vainqueurs;
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// SERVICES JOUEURS
// /////////////////////////////////////////////////////////////////////////////////////
class SQLJoueursServices extends SQLServices
{
	function __construct($championnat)
	{
		parent::__construct($championnat);
	}

	function getJoueur($id)
	{
		$req = "SELECT * FROM jb_joueurs WHERE id_champ=".$this->championnat." AND id=".$id;
		$res = dbc::execSql($req);
		$joueur = mysqli_fetch_array($res);

		return $joueur;
	}

	function getJoueurByPseudo($pseudo)
	{
		$req = "SELECT * FROM jb_joueurs WHERE id_champ=".$this->championnat." AND pseudo='".$pseudo."'";
		$res = dbc::execSql($req);
		$joueur = mysqli_fetch_array($res);

		return $joueur;
	}

	function getListeJoueursFromIds($ids = "")
	{
		$joueurs = array();

		$req = "SELECT * FROM jb_joueurs WHERE id_champ=".$this->championnat." AND id IN (".SQLServices::cleanIN($ids).")";
		$res = dbc::execSql($req);

		while($row = mysqli_fetch_array($res))
			$joueurs[$row['id']] = $row;

		return $joueurs;
	}

	function getListeJoueursGen($presence = "")
	{
		$joueurs = array();

		$req = "SELECT * FROM jb_joueurs WHERE id_champ=".$this->championnat." ".($presence == "" ? "" : "AND presence=".$presence)." ORDER BY nom";
		$res = dbc::execSql($req);

		while($row = mysqli_fetch_array($res))
			$joueurs[$row['id']] = $row;

		return $joueurs;
	}

	function getListeJoueurs()
	{
		return $this->getListeJoueursGen();
	}

	function getListeJoueursReguliers()
	{
		return $this->getListeJoueursGen(1);
	}

	function getListeJoueursOccasionnels()
	{
		return $this->getListeJoueursGen(0);
	}

	function getListeJoueursByEquipes($liste_equipes)
	{
		$liste_joueurs = "";

		if ($liste_equipes != "")
		{
			$j_selected = array();
			$req = "SELECT * FROM jb_equipes WHERE id IN (".SQLServices::cleanIN($liste_equipes).")";
			$res = dbc::execSQL($req);
			while($row = mysqli_fetch_array($res))
			{
				if ($row['nb_joueurs'] >= 2)
				{
					$item = explode('|', $row['joueurs']);
					foreach($item as $j) $j_selected[$j] = $j;
				}
			}
			foreach($j_selected as $j) $liste_joueurs .= ($liste_joueurs == "" ? "" : ",").$j;
		}

		return $liste_joueurs;
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// SERVICES EQUIPES
// /////////////////////////////////////////////////////////////////////////////////////
class SQLEquipesServices extends SQLServices
{
	function __construct($championnat)
	{
		parent::__construct($championnat);
	}

	function getEquipe($id)
	{
		$req = "SELECT * FROM jb_equipes WHERE id_champ=".$this->championnat." AND id=".$id;
		$res = dbc::execSql($req);
		$equipe = mysqli_fetch_array($res);

		return $equipe;
	}

	function getListeEquipes($ids = "")
	{
		$liste = array();

		// Formattage des 'ids'
		$all_equipes = "";
		if (is_array($ids))
		{
			foreach($ids as $id)
				$all_equipes .= ($all_equipes == "" ? "" : ",").$id;
		}
		else
			$all_equipes = $ids;

		// Récupération de la liste des équipes
		$req = "SELECT * FROM jb_equipes WHERE id_champ=".$this->championnat." ".($all_equipes == "" ? "" : "AND id IN (".SQLServices::cleanIN($all_equipes).")")." ORDER BY nom ASC";
		$res = dbc::execSql($req);
		while($equipe = mysqli_fetch_array($res))
			$liste[$equipe['id']] = $equipe;

		return $liste;
	}

	function getStatsConfrontations($id_equipe)
	{
		$tri1  = array();
		$tri2  = array();
		$tri3  = array();
		$tri4  = array();
		$liste = array();

		// récup infos saison
		$req = "SELECT * FROM jb_saisons WHERE id=".$this->championnat;
		$res = dbc::execSql($req);
		$saison = mysqli_fetch_array($res);

		$listes_equipes = array();
		// Récup équipes pour championat/tournois
		if ($saison['equipes'] != "")
		{
			$req = "SELECT * FROM jb_equipes WHERE id_champ=".$saison['id_champ']." AND id IN(".$saison['equipes'].") ORDER BY nom ASC";
			$res = dbc::execSql($req);
			while($equipe = mysqli_fetch_array($res))
				$listes_equipes[$equipe['id']] = $equipe;
		}
		// Récup équipes pour libre
		else
		{
			$req = "SELECT * FROM jb_equipes WHERE id_champ=".$saison['id_champ']." ORDER BY nom ASC";
			$res = dbc::execSql($req);
			while($equipe = mysqli_fetch_array($res))
				$listes_equipes[$equipe['id']] = $equipe;
		}

		$req = "SELECT * FROM jb_matchs WHERE id_champ=".$this->championnat." AND (id_equipe1=".$id_equipe." OR id_equipe2=".$id_equipe.")";
		$res = dbc::execSql($req);
		while($match = mysqli_fetch_array($res))
		{
			$vainqueur = StatsJourneeBuilder::kikiGagne($match);

			// On zappe si le match est planifié mais non joué (ex: eq1 0-0 eq2)
			if ($vainqueur == 99) continue;

			$id_adversaire = $match['id_equipe1'] == $id_equipe ? $match['id_equipe2'] : $match['id_equipe1'];
			if (!isset($liste[$id_adversaire]['equipe']))
			{
				$liste[$id_adversaire]['equipe'] = $listes_equipes[$id_adversaire]['nom'];
				$liste[$id_adversaire]['joues']  = 0;
				$liste[$id_adversaire]['gagnes'] = 0;
				$liste[$id_adversaire]['nuls'] = 0;
				$liste[$id_adversaire]['perdus'] = 0;
			}
			$liste[$id_adversaire]['joues']++;

			if ($match['id_equipe1'] == $id_equipe && $vainqueur == 1) $liste[$id_adversaire]['gagnes']++;
			else if ($match['id_equipe2'] == $id_equipe && $vainqueur == 1) $liste[$id_adversaire]['perdus']++;
			else if ($match['id_equipe1'] == $id_equipe && $vainqueur == 2) $liste[$id_adversaire]['perdus']++;
			else if ($match['id_equipe2'] == $id_equipe && $vainqueur == 2) $liste[$id_adversaire]['gagnes']++;
			else $liste[$id_adversaire]['nuls']++;

			$tri1[$id_adversaire] = $liste[$id_adversaire]['joues'];
			$tri2[$id_adversaire] = $liste[$id_adversaire]['gagnes'];
			$tri3[$id_adversaire] = $liste[$id_adversaire]['nuls'];
			$tri4[$id_adversaire] = $liste[$id_adversaire]['perdus'];
		}

		array_multisort($tri1, SORT_DESC, $tri2, SORT_DESC, $tri3, SORT_DESC, $tri4, SORT_DESC, $liste);

		return $liste;
	}

	function checkTeam($noms, $defenseur, $attaquant)
	{
		$nom_equipe = str_replace('\'', '\\\'', $noms[$defenseur]."-".$noms[$attaquant]);
		$req = "SELECT count(*) total FROM jb_equipes WHERE id_champ=".$this->championnat." AND joueurs='".$defenseur."|".$attaquant."'";
		$res = dbc::execSQL($req);
		$row = mysqli_fetch_array($res);
		mysqli_free_result($res);

		if ($row['total'] == 0)
		{
			$insert = "INSERT INTO jb_equipes (id_champ, joueurs, nb_joueurs, nom) VALUES (".$this->championnat.", '".$defenseur."|".$attaquant."', 2, '".$nom_equipe."')";
			$res = dbc::execSQL($insert);
		}
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// SERVICES JOURNEES
// /////////////////////////////////////////////////////////////////////////////////////
class SQLJourneesServices extends SQLServices
{
	var $journee;

	function __construct($championnat, $journee)
	{
		parent::__construct($championnat);

		$this->journee = $journee;
	}

	function getJournee($id_journee = "")
	{
		$req = "SELECT * FROM jb_journees WHERE id_champ=".$this->championnat." AND id=".($id_journee == "" ? $this->journee : $id_journee);
		$res = dbc::execSql($req);
		$journee = mysqli_fetch_array($res);

		return $journee;
	}

	function getAllNoneAliasJournee()
	{
		$lst = array();

		$req = "SELECT * FROM jb_journees WHERE id_champ=".$this->championnat." AND id_journee_mere=0";
		$res = dbc::execSql($req);
		while($row = mysqli_fetch_array($res))
			$lst[] = $row;

		return $lst;
	}

	function getAllAliasJournee($id_journee = "")
	{
		$lst = array();

		$req = "SELECT * FROM jb_journees WHERE id_champ=".$this->championnat." AND id_journee_mere=".($id_journee == "" ? $this->journee : $id_journee);
		$res = dbc::execSql($req);
		while($row = mysqli_fetch_array($res))
			$lst[] = $row;

		return $lst;
	}

	function isJourneeAlias($journee)
	{
		$is_journee_alias = ($journee['id_journee_mere'] == "" || $journee['id_journee_mere'] == "0" ? false : true);

		return $is_journee_alias;
	}

	function getNomJournee($nom)
	{
		$items = explode(':', $nom);
		$num_journees = $items[0];
		$nom_journee  = isset($items[1]) ? $items[1] : "";

		$lib_journee = $nom_journee != "" ? $nom_journee : $num_journees.($num_journees == '1' ? "ère" : "ème")." journée";

		return $lib_journee;
	}

	function getJourneeByDate($date)
	{
		$req = "SELECT * from jb_journees WHERE id_champ=".$this->championnat." AND date='".$date."';";
		$res = dbc::execSQL($req);
		$journee = mysqli_fetch_array($res);

		return $journee;
	}

	function getListeLast4Journees($date)
	{
		$lst = array();

		$req = "SELECT * from jb_journees WHERE id_champ=".$this->championnat." AND date < '".$date."' ORDER BY date DESC LIMIT 0,4";
		$res = dbc::execSQL($req);
		while($row = mysqli_fetch_array($res))
		{
			$row['nom'] = ToolBox::conv_lib_journee($row['nom']);
			$lst[$row['id']] = $row;
		}

		return $lst;
	}

	function getListeNext4Journees($date)
	{
		$lst = array();

		$req = "SELECT * from jb_journees WHERE id_champ=".$this->championnat." AND date >= '".$date."' ORDER BY date ASC LIMIT 0,4";
		$res = dbc::execSQL($req);
		while($row = mysqli_fetch_array($res))
		{
			$row['nom'] = ToolBox::conv_lib_journee($row['nom']);
			$lst[$row['id']] = $row;
		}

		return $lst;
	}

	function getListeJourneesGen($date_debut, $date_fin, $option = 0)
	{
		$lst = array();

		$req = "SELECT * from jb_journees WHERE id_champ IN (".SQLServices::cleanIN($this->championnat).") AND date between '".$date_debut."' AND  '".$date_fin."';";
		$res = dbc::execSQL($req);
		while($row = mysqli_fetch_array($res))
		{
			if ($option == 0)
				$lst[$row['id']] = $row;
			else if ($option == 1)
				$lst[$row['date']] = $row;
			else
				$lst[] = $row;
		}

		return $lst;
	}

	function getAllMatchs()
	{
		$lst = array();

		$req = "SELECT * from jb_matchs WHERE id_champ=".$this->championnat." AND id_journee=".$this->journee.";";
		$res = dbc::execSQL($req);
		while($row = mysqli_fetch_array($res))
			$lst[] = $row;

		return $lst;
	}

	function getListeJournees($date_debut, $date_fin)
	{
		return $this->getListeJourneesGen($date_debut, $date_fin, 0);
	}

	function getListeJourneesIndexedByDate($date_debut, $date_fin)
	{
		return $this->getListeJourneesGen($date_debut, $date_fin, 1);
	}

	function delJournee()
	{
		// S'il y a des matchs on les supprime
		$delete = "DELETE FROM jb_matchs WHERE id_journee=".$this->journee." AND id_champ=".$this->championnat;
		$res = dbc::execSQL($delete);

		// S'il y a des classements de poules on les supprime
		$delete = "DELETE FROM jb_classement_poules WHERE id_journee=".$this->journee." AND id_champ=".$this->championnat;
		$res = dbc::execSQL($delete);

		// S'il y a des alias on les supprime
		$delete = "DELETE FROM jb_journees WHERE id_journee_mere=".$this->journee." AND id_champ=".$this->championnat;
		$res = dbc::execSQL($delete);

		// On supprime la journée
		$delete = "DELETE FROM jb_journees WHERE id=".$this->journee." AND id_champ=".$this->championnat;
		$res = dbc::execSQL($delete);
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// SERVICES MATCHS
// /////////////////////////////////////////////////////////////////////////////////////
class SQLMatchsServices extends SQLServices
{
	var $journee;
	var $match;

	function __construct($championnat, $journee, $match)
	{
		parent::__construct($championnat);
		$this->journee = $journee;
		$this->match   = $match;
	}

	function getMatch()
	{
		$select = "SELECT * FROM jb_matchs WHERE id_champ=".$this->championnat." AND id_journee=".$this->journee." AND id=".$this->match;
		$res = dbc::execSQL($select);
		$row_match = mysqli_fetch_array($res);

		return $row_match;
	}

	function getMatchByNiveau($niveau)
	{
		$select = "SELECT * FROM jb_matchs WHERE id_champ=".$this->championnat." AND id_journee=".$this->journee." AND niveau='".$niveau."'";
		$res = dbc::execSQL($select);
		$row_match = mysqli_fetch_array($res);

		return $row_match;
	}

	// On cherche la première équipe qui n'a encore joué avec la première équipe disponible
	function getMatchsFIFO($equipes, $matchs)
    {
    	$ret = null;

    	$lst1 = $equipes;
    	$lst2 = $equipes;

    	$trouve = false;
    	reset($lst1);
    	foreach($lst1 as $e1)
    	{
    		reset($lst2);
    		foreach($lst2 as $e2)
    		{
    			if ($e1 != $e2)
    			{
    				if (!isset($matchs[$e1."-".$e2]) && !isset($matchs[$e2."-".$e1]))
    				{
    					$ret = $e1."-".$e2;
    					$trouve = true;
    					break;
    				}
    			}
    		}
    		if ($trouve) break;
    	}
    	return $ret;
    }

	// On cherche à faire des matchs equipes fortes contre equipe faible au debut ...
	function getMatchsSTRONGvsFREAK($equipes, $matchs, $compteur, $ponderation)
    {
    	$ret = null;

    	$lst1 = $equipes;
    	$lst2 = $equipes;

 	//	array_multisort($sort1, SORT_DESC, $lst2);

    	$delta = -1;

    	$trouve = false;
    	reset($lst1);
    	foreach($lst1 as $e1)
    	{
    		reset($lst2);
//    		array_multisort($compteur, SORT_ASC, $lst2);
    		foreach($lst2 as $e2)
    		{
    			if ($e1 != $e2)
    			{
    				if (!isset($matchs[$e1."-".$e2]) && !isset($matchs[$e2."-".$e1]))
    				{
    					if (abs($ponderation[$e1] - $ponderation[$e2]) > $delta)
    					{
	    					$ret = $e1."-".$e2;
//	    					echo $ret."<br />";
	    					$delta = abs($ponderation[$e1] - $ponderation[$e2]);
	    					$trouve = true;
	    				}
    				}
    			}
    		}
    		if ($trouve) break;
    	}
    	return $ret;
    }

	function getMatchs2Play($equipes, $matchs, $compteur, $ponderation)
	{
//		return $this->getMatchsFIFO($equipes, $matchs);
		return $this->getMatchsSTRONGvsFREAK($equipes, $matchs, $compteur, $ponderation);
	}

    function getRoulementEquipes($equipes, $match, $compteur)
    {
    	$ret = null;

    	$equipes_sel = explode('-', $match);

    	foreach($equipes as $e)
    	{
    		if ($e != $equipes_sel[0] && $e != $equipes_sel[1])
    			$ret[] = $e;
    	}
    	$ret[] = $equipes_sel[0];
    	$ret[] = $equipes_sel[1];

    	return $ret;
    }

    function getOrganizedMatchs($equipes)
    {
    	$matchs_affectes = array();

    	$compteur = array();
    	foreach($equipes as $e)
    		$compteur[$e] = 0;

    	// On affecte une pondération à chaque equipe, l'équipe du haut de la liste est considérée conme tête de série (chapo 1) et ainsi de suite ...
    	// + la ponderation est haute + l'equipe est faible.
    	$i = 0;
    	$ponderation = array();
    	foreach($equipes as $e)
    		$ponderation[$e] = $i++;

    	$m = $this->getMatchs2Play($equipes, $matchs_affectes, $compteur, $ponderation);
    	while($m != null)
    	{
    		$matchs_affectes[$m] = $m;

    		$tmp = explode('-', $m);
    		$compteur[$tmp[0]]++;
    		$compteur[$tmp[1]]++;

    		$equipes = $this->getRoulementEquipes($equipes, $m, $compteur);

    		$m = $this->getMatchs2Play($equipes, $matchs_affectes, $compteur, $ponderation);
    	}

    	return $matchs_affectes;
    }

	function createMatchsPoulesTournoi($poules, $ar = 1)
	{
		$liste_poules = explode('|', $poules);

		$delete = "DELETE FROM jb_matchs WHERE id_champ=".$this->championnat." AND id_journee=".$this->journee." AND niveau LIKE 'P|%';";
		$res = dbc::execSQL($delete);

		$i = 1;
		foreach($liste_poules as $ma_poule)
		{
			$p = explode(',', $ma_poule);
            $matchs = $this->getOrganizedMatchs($p);
            foreach($matchs as $m)
            {
                $items = explode('-', $m);
    			$insert = "INSERT INTO jb_matchs (id_champ, id_journee, id_equipe1, id_equipe2, nbset, resultat, niveau, score_points) VALUES (".$this->championnat.", ".$this->journee.", ".$items[0].", ".$items[1].", 1, '0/0', 'P|".$i."', '0|0');";
    	   		$res = dbc::execSQL($insert);
    	   	}
   	   		$i++;
		}
		if ($ar == 0)
		{
    		$i = 1;
    		foreach($liste_poules as $ma_poule)
    		{
    			$p = explode(',', $ma_poule);
                $matchs = $this->getOrganizedMatchs($p);
                foreach($matchs as $m)
                {
                    $items = explode('-', $m);
        			$insert = "INSERT INTO jb_matchs (id_champ, id_journee, id_equipe1, id_equipe2, nbset, resultat, niveau, score_points) VALUES (".$this->championnat.", ".$this->journee.", ".$items[1].", ".$items[0].", 1, '0/0', 'P|".$i."', '0|0');";
        	   		$res = dbc::execSQL($insert);
        	   	}
       	   		$i++;
    		}
		}
/* METHODE 1
		$i = 1;
		foreach($liste_poules as $ma_poule)
		{
			$l_equipes = explode(',', $ma_poule);

			$equipes_dual = $l_equipes;
			while(list($cle, $mon_equipe) = each($l_equipes))
			{
				reset($equipes_dual);
				foreach($equipes_dual as $mon_equipe_dual)
				{
					if ($mon_equipe != "" && $mon_equipe_dual != "" && $mon_equipe != $mon_equipe_dual)
					{
						$insert = "INSERT INTO jb_matchs (id_champ, id_journee, id_equipe1, id_equipe2, nbset, resultat, niveau, score_points) VALUES (".$this->championnat.", ".$this->journee.", ".$mon_equipe.", ".$mon_equipe_dual.", 1, '0/0', 'P|".$i."', '0|0');";
						$res = dbc::execSQL($insert);
						if ($ar == 0)
						{
							$insert = "INSERT INTO jb_matchs (id_champ, id_journee, id_equipe2, id_equipe1, nbset, resultat, niveau, score_points) VALUES (".$this->championnat.", ".$this->journee.", ".$mon_equipe.", ".$mon_equipe_dual.", 1, '0/0', 'P|".$i."', '0|0');";
							$res = dbc::execSQL($insert);
						}
					}
				}
				unset($equipes_dual[$cle]);
			}
			$i++;
		}
*/
	}

	function getListeFannys()
	{
		$lst = array();

	    $req = "SELECT *, jb_matchs.id id_match FROM jb_matchs, jb_journees WHERE jb_matchs.id_journee=jb_journees.id AND jb_matchs.id_champ=".$this->championnat." AND fanny=1 ORDER BY date DESC;";
		$res = dbc::execSQL($req);
		while($row = mysqli_fetch_array($res))
			$lst[$row['id_match']] = $row;

		return $lst;
	}

	function getLastMatchsForTicker()
	{
		$lst = array();

	    $req = "SELECT *, eq1.nom nom1, eq2.nom nom2 FROM jb_matchs, jb_equipes eq1, jb_equipes eq2 WHERE id_equipe1=eq1.id AND id_equipe2=eq2.id AND id_journee=".$this->journee." AND jb_matchs.id_champ=".$this->championnat." ORDER BY jb_matchs.id DESC LIMIT 0,10";
		$res = dbc::execSQL($req);
		while($row = mysqli_fetch_array($res))
			$lst[$row['id']] = $row;

		return $lst;
	}

	function getLastMatchs()
	{
		$lst = array();

	    $req = "SELECT m.*, j.date date, eq1.nom nom1, eq2.nom nom2 FROM jb_matchs m, jb_journees j, jb_equipes eq1, jb_equipes eq2 WHERE m.id_journee=j.id AND m.id_equipe1=eq1.id AND m.id_equipe2=eq2.id AND m.id_champ=".$this->championnat." ORDER BY j.date DESC, m.id DESC LIMIT 0,10";
		$res = dbc::execSQL($req);
		while($row = mysqli_fetch_array($res))
			$lst[$row['id']] = $row;

		return $lst;
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// SERVICES FORUM
// /////////////////////////////////////////////////////////////////////////////////////
class SQLForumServices extends SQLServices
{
	function __construct($championnat)
	{
		parent::__construct($championnat);
	}

	function getMessage($id)
	{
		$req = "SELECT * FROM jb_forum WHERE id_champ=".$this->championnat." AND del=0 AND id=".$id;
		$res = dbc::execSQL($req);
		$row = mysqli_fetch_array($res);

		return $row;
	}

	function getReponses($id)
	{
		$lst = array();

		$req = "SELECT * FROM jb_forum WHERE id_champ=".$this->championnat." AND in_response=".$id." AND del=0 ORDER BY last_reponse DESC";
		$res = dbc::execSQL($req);
		while($row = mysqli_fetch_array($res))
			$lst[$row['id']] = $row;

		return $lst;
	}

	function getListeMessages($limits = "")
	{
		$lst = array();

		$req = "SELECT * FROM jb_forum WHERE id_champ=".$this->championnat." AND in_response=0 AND del=0 ORDER BY last_reponse DESC ".$limits;
		$res = dbc::execSQL($req);
		while($row = mysqli_fetch_array($res))
			$lst[$row['id']] = $row;

		return $lst;
	}

	function getListeLastMessages($limits = "")
	{
		$lst = array();

		$req = "SELECT * FROM jb_forum WHERE id_champ=".$this->championnat." AND del=0 ORDER BY date DESC ".$limits;
		$res = dbc::execSQL($req);
		while($row = mysqli_fetch_array($res))
			$lst[$row['id']] = $row;

		return $lst;
	}

	function getLastPhoto()
	{
		$req = "SELECT * FROM jb_forum WHERE id_champ=0 AND title LIKE '{photo}%' AND in_response=0 AND del=0 ORDER BY date DESC";
		$res = dbc::execSQL($req);
		$row = mysqli_fetch_array($res);

		return $row;
	}

	function getListePhotosFull($limits = "")
	{
		$lst = array();

		$req = "SELECT * FROM jb_forum WHERE id_champ=0 AND title LIKE '{photo}%' AND in_response=0 AND del=0 ORDER BY date DESC ".$limits;
		$res = dbc::execSQL($req);
		while($row = mysqli_fetch_array($res))
			$lst[$row['id']] = $row;


		return $lst;
	}

	function getListeChroniques($limits = "")
	{
		$lst = array();

		$req = "SELECT * FROM jb_forum WHERE id_champ=0 AND title LIKE '{chronique}%' AND in_response=0 AND del=0 ORDER BY date DESC ".$limits;
		$res = dbc::execSQL($req);
		while($row = mysqli_fetch_array($res))
			$lst[$row['id']] = $row;


		return $lst;
	}

	function getLastChronique()
	{
		$req = "SELECT * FROM jb_forum WHERE id_champ=0 AND title LIKE '{chronique}%' AND in_response=0 AND del=0 ORDER BY date DESC";
		$res = dbc::execSQL($req);
		$row = mysqli_fetch_array($res);

		return $row;
	}

	function getListeMessagesLeSaviezVous($limits = "")
	{
		$lst = array();

		$req = "SELECT * from jb_forum WHERE id_champ=0 AND in_response=0 AND del=0 AND title LIKE '{%_help%}%' ORDER BY title ASC ".$limits;
		$res = dbc::execSQL($req);
		while($row = mysqli_fetch_array($res))
			$lst[$row['id']] = (stristr($row['title'], "_hr") ? "<hr />" : "")."<div><a href=\"../www/forum_message.php?id_msg=".$row['id']."&amp;dual=3#bottom\">".ereg_replace("\{.*\}", "", $row['title'])."</a></div>";

		return $lst;
	}

	function getListeMessagesLeSaviezVousFull($limits = "")
	{
		$lst = array();

		$req = "SELECT * from jb_forum WHERE id_champ=0 AND in_response=0 AND del=0 AND title LIKE '{%_help%}%' ORDER BY title ASC ".$limits;
		$res = dbc::execSQL($req);
		while($row = mysqli_fetch_array($res))
			$lst[$row['id']] = $row;


		return $lst;
	}

	function getListeMessagesForumHome($limits = "")
	{
		$lst = array();

		$req = "SELECT id, date, nom, title, message from jb_forum WHERE id_champ=0 AND title NOT LIKE '{%}%' ORDER BY date DESC ".$limits;

		$res = dbc::execSQL($req);
		while($row = mysqli_fetch_array($res))
			$lst[$row['id']] = $row;

		return $lst;
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// SERVICES ALBUMS
// /////////////////////////////////////////////////////////////////////////////////////
class SQLAlbumsServices extends SQLServices
{
	function __construct($championnat = -1)
	{
		parent::__construct($championnat);
	}

	function getPhoto($id = "")
	{
		$req = "SELECT * FROM jb_albums WHERE id_champ=".$this->championnat." AND id=".$id;
		$res = dbc::execSql($req);
		$row = mysqli_fetch_array($res);

		return $row;
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// SERVICES ACTUALITES
// /////////////////////////////////////////////////////////////////////////////////////
class SQLActualitesServices extends SQLServices
{
	function __construct($championnat = -1)
	{
		parent::__construct($championnat);
	}

	function getActualitesAlaUne()
	{
		$lst = array();

		$req = "SELECT * FROM jb_actualites WHERE alaune=1 ORDER BY date DESC";
		$res = dbc::execSql($req);
		while($row = mysqli_fetch_array($res))
			$lst[$row['id']] = $row;

		return $lst;
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// SERVICES VIDEOS
// /////////////////////////////////////////////////////////////////////////////////////
class SQLVideossServices extends SQLServices
{
	function __construct($championnat = -1)
	{
		parent::__construct($championnat);
	}

	function getVideos()
	{
		$lst = array();

		$req = "SELECT * FROM jb_videos ORDER BY date DESC";
		$res = dbc::execSql($req);
		while($row = mysqli_fetch_array($res))
			$lst[$row['id']] = $row;

		return $lst;
	}
}

// /////////////////////////////////////////////////////////////////////////////////////
// SERVICES ALBUMS THEMES
// /////////////////////////////////////////////////////////////////////////////////////
class SQLAlbumsThemesServices extends SQLServices
{
	var $id_theme;

	function __construct($championnat = -1, $id_theme = -1)
	{
		parent::__construct($championnat);
		$this->id_theme = $id_theme;
	}

	function getAlbumTheme($id = "")
	{
		if ($id == "" && $this->id_theme != "") $id = $this->id_theme;

		$req = "SELECT * FROM jb_albums_themes WHERE id_champ=".$this->championnat." AND id=".$id;
		$res = dbc::execSql($req);
		$row = mysqli_fetch_array($res);

		return $row;
	}

	function getPhotos()
	{
		$lst = array();

		$req = "SELECT * FROM jb_albums WHERE id_champ=".$this->championnat." AND id_theme=".$this->id_theme;
		$res = dbc::execSql($req);
		while($row = mysqli_fetch_array($res))
			$lst[$row['id']] = $row;

		return $lst;
	}

	function getAllThemes()
	{
		$lst = array();

		$req = "SELECT * FROM jb_albums_themes WHERE id_champ=".$this->championnat." ORDER BY date DESC";
		$res = dbc::execSql($req);
		while($row = mysqli_fetch_array($res))
			$lst[$row['id']] = $row;

		return $lst;
	}

	function getFirstTheme()
	{
		$first = -1;

		$req = "SELECT * FROM jb_albums_themes WHERE id_champ=".$this->championnat;
		$res = dbc::execSql($req);
		if ($row = mysqli_fetch_array($res))	$first = $row['id'];

		return $first;
	}

	function getXMLFilename()
	{
		$album_theme = $this->getAlbumTheme();

		return "../xml/album_".$this->championnat."_".$this->id_theme."_".ToolBox::mysqldate2datetime($album_theme['last_modif'])."_.xml";
	}

	function getXMLPhotos()
	{
		$photos = $this->getPhotos();

		$res = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
		$res .= "<SIMPLEVIEWER_DATA
					maxImageDimension=\"400\"
					textColor=\"0x000000\"
					frameColor=\"0xCCCCCC\"
					bgColor=\"0x000000\"
					frameWidth=\"10\"
					stagePadding=\"10\"
					thumbnailColumns=\"2\"
					thumbnailRows=\"5\"
					navPosition=\"right\"
					navDirection=\"LTR\"
					title=\"\"
					imagePath=\"../uploads/\"
					thumbPath=\"../thumbs/\">\n";
		foreach($photos as $img)
		{
			$res .= "<IMAGE>\n";
			$res .= "\t<NAME>".str_replace('../uploads/', '', $img['photo'])."</NAME>\n";
			$res .= "\t<CAPTION><![CDATA[<B>".$img['commentaire']."</B>]]></CAPTION>\n";
			$res .= "</IMAGE>\n";
//			$thumb = ImageBox::thumbImageSquareResize($img['photo'], str_replace('uploads', 'thumbs', $img['photo']), 100, 45, 45);
		}

		$res .= "</SIMPLEVIEWER_DATA>";

		return $res;
	}
}

?>
