<?

// /////////////////////////////////////////////////////////////////////////////
// CLASS pour les statistiques d'un Match
// /////////////////////////////////////////////////////////////////////////////
class StatMatch
{
	var $score;

	function __construct($score, $nbSet)
	{
		// Si forfait equipe1 ou equipe2
		if ($score == -1 || $score == -2)
		{
			$this->score = $score;
		}
		else
		{
			$this->score = array();

			$items = explode(',', $score);

			$i = 0;
			while($i < $nbSet)
			{
				$this->score[$i] = explode('/', $items[$i]);
				$i++;
			}
		}
	}

	function getScore()		{ return $this->score; }
}

// /////////////////////////////////////////////////////////////////////////////
// CLASS pour les statistiques d'une journée pour un joueur
// /////////////////////////////////////////////////////////////////////////////
class StatJourneeJoueur
{
	var $id;
	var $matchs_jouesA;
	var $matchs_jouesD;
	var $matchs_gagnes;
	var $matchs_nuls;
	var $matchs_perdus;
	var $matchs_gagnes_attaquant;
	var $matchs_gagnes_defenseur;
    var $matchs_alasuite;
	var $buts_marques;
	var $buts_encaisses;
	var $buts_marques_defenseur;
	var $buts_encaisses_attaquant;
    var $diff_attaquant;
    var $diff_defenseur;
    var $diff;
	var $fanny_in;
	var $fanny_out;
	var $justesse_gagnes;
	var $justesse_perdus;
	var $sets_joues;
	var $sets_gagnes;
	var $sets_nuls;
	var $sets_perdus;
	var $sets_diff;

	function __construct()
	{
		$this->id              = 0;
		$this->matchs_jouesA   = 0;
		$this->matchs_jouesD   = 0;
		$this->matchs_gagnes   = 0;
		$this->matchs_nuls     = 0;
		$this->matchs_perdus   = 0;
		$this->matchs_alasuite = 0;
		$this->buts_marques    = 0;
		$this->buts_encaisses  = 0;
		$this->diff_attaquant  = 0;
		$this->diff_defenseur  = 0;
		$this->diff            = 0;
		$this->fanny_in        = 0;
		$this->fanny_out       = 0;
		$this->justesse_gagnes = 0;
		$this->justesse_perdus = 0;
		$this->sets_joues      = 0;
		$this->sets_gagnes     = 0;
		$this->sets_nuls       = 0;
		$this->sets_perdus     = 0;
		$this->sets_diff       = 0;
		$this->matchs_gagnes_attaquant  = 0;
		$this->matchs_gagnes_defenseur  = 0;
		$this->buts_marques_defenseur   = 0;
		$this->buts_encaisses_attaquant = 0;
	}

	function init($stat)
	{
		$item1 = explode('@', $stat);
		$item2 = explode(',', $item1[1]);

		$this->id             = $item1[0];
		$this->matchs_jouesA  = $item2[0];
		$this->matchs_jouesD  = $item2[1];
		$this->matchs_gagnes  = $item2[2];
		$this->buts_marques   = $item2[3];
		$this->buts_encaisses_attaquant = $item2[4];
		$this->diff_attaquant = $item2[5];
		$this->buts_marques_defenseur   = $item2[6];
		$this->buts_encaisses = $item2[7];
		$this->diff_defenseur = $item2[8];
		$this->diff           = $item2[9];
		$this->fanny_in       = $item2[10];
		$this->fanny_out      = $item2[11];
		$this->justesse_gagnes = isset($item2[12]) ? $item2[12] : 0;
		$this->justesse_perdus = isset($item2[13]) ? $item2[13] : 0;
		$this->sets_joues     = isset($item2[14]) ? $item2[14] : 0;
		$this->sets_gagnes    = isset($item2[15]) ? $item2[15] : 0;
		$this->sets_perdus    = isset($item2[16]) ? $item2[16] : 0;
		$this->sets_diff      = isset($item2[17]) ? $item2[17] : 0;
		$this->matchs_nuls    = isset($item2[18]) ? $item2[18] : 0;
		$this->matchs_perdus  = isset($item2[19]) ? $item2[19] : 0;
		$this->matchs_nuls    = isset($item2[20]) ? $item2[20] : 0;
	}

	public static function vierge()
	{
		return "0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0";
	}
}

// /////////////////////////////////////////////////////////////////////////////
// CLASS pour les statistiques d'une journee pour une équipe
// /////////////////////////////////////////////////////////////////////////////
class StatJourneeTeam
{
	var $id;
	var $attaquant;
	var $defenseur;
	var $points;
	var $matchs_joues;
	var $matchs_gagnes;
	var $matchs_nuls;
	var $matchs_perdus;
	var $matchs_forfaits;
	var $sets_joues;
	var $sets_gagnes;
	var $sets_nuls;
	var $sets_perdus;
	var $sets_diff;
	var $buts_marques;
	var $buts_encaisses;
	var $diff;
	var $tournoi_classement;
	var $tournoi_points;
	var $fanny_in;
	var $fanny_out;
	var $justesse_gagnes;
	var $justesse_perdus;

	function __construct()
	{
		$this->id              = 0;
		$this->points          = 0;
		$this->matchs_joues    = 0;
		$this->matchs_gagnes   = 0;
		$this->matchs_nuls     = 0;
		$this->matchs_perdus   = 0;
		$this->matchs_forfaits = 0;
		$this->sets_joues      = 0;
		$this->sets_gagnes     = 0;
		$this->sets_nuls       = 0;
		$this->sets_perdus     = 0;
		$this->sets_diff       = 0;
		$this->buts_marques    = 0;
		$this->buts_encaisses  = 0;
		$this->diff            = 0;
		$this->tournoi_classement = 0;
		$this->tournoi_points  = 0;
		$this->fanny_in        = 0;
		$this->fanny_out       = 0;
		$this->justesse_gagnes = 0;
		$this->justesse_perdus = 0;
	}

	function init($stat)
	{
		$item1 = explode('@', $stat);
		$item2 = explode(',', $item1[1]);

		$this->id              = $item1[0];
		$this->points          = $item2[0];
		$this->matchs_joues    = $item2[1];
		$this->matchs_gagnes   = $item2[2];
		$this->matchs_nuls     = $item2[3];
		$this->matchs_perdus   = $item2[4];
		$this->sets_joues      = $item2[5];
		$this->sets_gagnes     = $item2[6];
		$this->sets_perdus     = $item2[7];
		$this->sets_diff       = $item2[8];
		$this->buts_marques    = $item2[9];
		$this->buts_encaisses  = $item2[10];
		$this->diff            = $item2[11];
		$this->tournoi_classement = $item2[12];
		$this->tournoi_points  = $item2[13];
		$this->fanny_in        = isset($item2[14]) ? $item2[14] : 0;
		$this->fanny_out       = isset($item2[15]) ? $item2[15] : 0;
		$this->justesse_gagnes = isset($item2[16]) ? $item2[16] : 0;
		$this->justesse_perdus = isset($item2[17]) ? $item2[17] : 0;
		$this->sets_nuls       = isset($item2[18]) ? $item2[18] : 0;
		$this->matchs_forfaits = isset($item2[19]) ? $item2[19] : 0;
	}

	public static function vierge()
	{
		return "0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0";
	}
}

// /////////////////////////////////////////////////////////////////////////////
// CLASS pour les statistiques globales d'un joueur
// /////////////////////////////////////////////////////////////////////////////
class StatGlobalJoueur
{
	var $id;
	var $nom;
	var $prenom;
	var $pseudo;
	var $dt_naissance;
	var $photo;
	var $presence;
	var $email;
	var $etat;
    var $joues;
    var $jouesA;
    var $jouesD;
    var $gagnes;
    var $nuls;
    var $perdus;
    var $marquesA;
    var $encaissesD;
    var $forme_participation;
    var $forme_joues;
    var $forme_gagnes;
    var $moy_marquesA;
    var $moy_encaissesD;
    var $pourc_joues;
    var $pourc_gagnes;
    var $pourc_nuls;
    var $pourc_perdus;
    var $forme_indice;
	var $forme_last_gagnes;
	var $forme_last_indice;
	var $forme_last_date;
	var $podium;
	var $polidor;
	var $evol_pourc_gagne;
	var $fanny_in;
	var $fanny_out;
	var $justesse_gagnes;
	var $justesse_perdus;
	var $sets_joues;
	var $sets_gagnes;
	var $sets_nuls;
	var $sets_perdus;
	var $sets_diff;
	var $medaille;

	function __construct()
	{
		$this->id                  = 0;
		$this->nom                 = "";
		$this->prenom              = "";
		$this->pseudo              = "";
		$this->dt_naissance        = "";
		$this->presence            = "";
		$this->email               = "";
		$this->etat                = 0;
       	$this->joues               = 0;
       	$this->jouesA              = 0;
       	$this->jouesD              = 0;
       	$this->gagnes              = 0;
       	$this->nuls                = 0;
       	$this->perdus              = 0;
       	$this->marquesA            = 0;
       	$this->encaissesD          = 0;
       	$this->forme_participation = 0;
       	$this->forme_joues         = 0;
       	$this->forme_gagnes        = 0;
       	$this->moy_marquesA        = 0;
       	$this->moy_encaissesD      = 0;
       	$this->pourc_joues         = 0;
       	$this->pourc_gagnes        = 0;
       	$this->pourc_nuls          = 0;
       	$this->pourc_perdus        = 0;
       	$this->forme_gagnes        = 0;
       	$this->forme_indice        = "<IMG SRC=../images/fleches/fleche0.gif BORDER=0 ALT=\"\" />";
		$this->forme_last_gagnes   = 0;
		$this->forme_last_indice   = "<IMG SRC=../images/fleches/fleche0.gif BORDER=0 ALT=\"\" />";
		$this->forme_last_date     = "";
		$this->podium              = 0;
		$this->polidor             = 0;
		$this->evol_pourc_gagne    = array();
		$this->fanny_in            = 0;
		$this->fanny_out           = 0;
		$this->justesse_gagnes     = 0;
		$this->justesse_perdus     = 0;
		$this->sets_joues          = 0;
		$this->sets_gagnes         = 0;
		$this->sets_perdus         = 0;
		$this->sets_nuls           = 0;
		$this->sets_diff           = 0;
		$this->medaille            = _NO_MEDAILLE_;
	}
}

// /////////////////////////////////////////////////////////////////////////////
// CLASS pour les statistiques globales d'une équipe
// /////////////////////////////////////////////////////////////////////////////
class StatGlobalTeam
{
	var $id;
	var $nom;
	var $nb_joueurs;
	var $joueurs;
	var $reguliere;
	var $points;
	var $matchs_joues;
	var $matchs_gagnes;
	var $matchs_nuls;
	var $matchs_perdus;
	var $matchs_forfaits;
	var $sets_joues;
	var $sets_gagnes;
	var $sets_nuls;
	var $sets_perdus;
	var $sets_diff;
	var $buts_marques;
	var $buts_encaisses;
	var $diff;
	var $pourc_gagnes;
	var $evol_classement;
	var $tournoi_classement;
	var $tournoi_points;
	var $tournoi_classement_moy;
	var $tournoi_nb_participation;
	var $fanny_in;
	var $fanny_out;
	var $justesse_gagnes;
	var $justesse_perdus;
	var $stat_attaque;
	var $stat_defense;
	var $stat_attdef_buts_marques;
	var $stat_attdef_buts_encaisses;
	var $stat_attdef_sets_joues;
	var $bonus;

	function __construct()
	{
		$this->id              = 0;
		$this->nom             = "";
		$this->nb_joueurs      = 0;
		$this->joueurs         = "";
		$this->reguliere       = 0;
		$this->points          = 0;
		$this->matchs_joues    = 0;
		$this->matchs_gagnes   = 0;
		$this->matchs_nuls     = 0;
		$this->matchs_perdus   = 0;
		$this->matchs_forfaits = 0;
		$this->sets_joues      = 0;
		$this->sets_gagnes     = 0;
		$this->sets_nuls       = 0;
		$this->sets_perdus     = 0;
		$this->sets_diff       = 0;
		$this->buts_marques    = 0;
		$this->buts_encaisses  = 0;
		$this->diff            = 0;
		$this->pourc_gagnes    = 0;
		$this->pourc_nuls      = 0;
		$this->pourc_perdus    = 0;
		$this->stat_attaque    = 0;
		$this->stat_defense    = 0;
		$this->stat_attdef_buts_marques   = 0;
		$this->stat_attdef_buts_encaisses = 0;
		$this->stat_attdef_sets_joues     = 0;
		$this->evol_classement = array();
		$this->fanny_in        = 0;
		$this->fanny_out       = 0;
		$this->justesse_gagnes = 0;
		$this->justesse_perdus = 0;
		$this->tournoi_classement       = 0;
		$this->tournoi_points           = 0;
		$this->tournoi_classement_moy   = 0;
		$this->tournoi_nb_participation = 0;
		$this->bonus = 0;
	}
}

// ///////////////////////////////////////////////////////////////////////////////////////////
// Statistiques d'une JOURNEE
// ///////////////////////////////////////////////////////////////////////////////////////////
// Génère le classement d'une journée pour les joueurs et les équipes
// ///////////////////////////////////////////////////////////////////////////////////////////
class StatsJourneeBuilder
{
	var $championnat;
	var $journee;		   // Si =0 alors sur toutes les journées du championnat
	var $type_championnat;
	var $statsJoueurs;
	var $statsTeams;
	var $filtre_matchs;
	var $synchronisation;
	var $journee_info;
	var $nb_matchs;
	var $gestion_nul;
	var $val_victoire;
	var $val_defaite;
	var $val_nul;
	var $forfait_penalite_bonus;
	var $forfait_penalite_malus;
	var $is_journee_alias;
	var $journee_mere_info;
	var $gavgp;

	function __construct($championnat, $journee, $type_championnat = _TYPE_LIBRE_, $filtre_matchs = "")
	{
		$this->championnat      = $championnat;
		$this->journee          = $journee;
		$this->type_championnat = $type_championnat;
		$this->filtre_matchs    = $filtre_matchs;
		$this->statsJoueurs     = array();
		$this->statsTeams       = array();
		$this->synchronisation  = 0;
		$this->nb_matchs        = 0;
		$this->gavgp            = sess_context::isGoalAverageParticulier();
		$this->gestion_nul      = sess_context::getGestionMatchsNul();
		$this->val_victoire     = sess_context::getValeurVictoireMatch();
		$this->val_defaite      = sess_context::getValeurDefaiteMatch();
		$this->val_nul          = sess_context::getValeurNulMatch();
		$this->forfait_penalite_bonus = sess_context::getForfaitPenaliteBonus();
		$this->forfait_penalite_malus = sess_context::getForfaitPenaliteMalus();

		// On récupère toutes les infos de la journee à traiter
		$sjs = new SQLJourneesServices($this->championnat, $this->journee);
		$this->journee_info = $sjs->getJournee();

		// Attention si journee alias il faut prendre les equipes de la journee mere
		$this->is_journee_alias = ($this->journee_info['id_journee_mere'] == "" || $this->journee_info['id_journee_mere'] == "0" ? false : true);
		if ($this->is_journee_alias)
		{
			$sjs2 = new SQLJourneesServices($this->championnat, $this->journee_info['id_journee_mere']);
			$this->journee_mere_info = $sjs2->getJournee();
			$this->journee_info['equipes'] = $this->journee_mere_info['equipes'];
		}

		// Calcal des stats
		$this->ComputeStats();
	}

	function initStatsPlayers()
	{
		if ($this->journee_info['joueurs'] == "" && $this->type_championnat != _TYPE_TOURNOI_ && $this->type_championnat != _TYPE_CHAMPIONNAT_) ToolBox::alert('Il faudrait synchroniser matchs et et joueurs de la journée ...');

		// On initialise toutes les infos des joueurs qui participent à cette journée
		$joueurs_engages = explode(',', $this->journee_info['joueurs']);
		foreach($joueurs_engages as $j)
		{
			if ($j == "") continue;
			$this->statsJoueurs[$j] = new StatJourneeJoueur();
			$this->statsJoueurs[$j]->id = $j;
 		}

		// On créé un joueur fictif pour les équipes sans joueurs
		$this->statsJoueurs[-1] = new StatJourneeJoueur();
		$this->statsJoueurs[-1]->id = -1;
	}

	function initStatsTeams()
	{
		// Récupération des équipes
		$nb_eq = 1;
		if ($this->journee_info['equipes'] == "")
		{
			$req = "SELECT * FROM jb_matchs WHERE id_champ=".$this->championnat." AND id_journee=".$this->journee." ".$this->filtre_matchs;
			$res = dbc::execSql($req);
			$nb_eq = 0;
			while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
			{
				$eq[$row['id_equipe1']] = $row['id_equipe1'];
				$eq[$row['id_equipe2']] = $row['id_equipe2'];
				$nb_eq++;
			}

			if ($nb_eq > 0)
				foreach($eq as $e) $this->journee_info['equipes'] .= ($this->journee_info['equipes'] == "" ? "" : ",").$e;
		}

		// Init des équipes
		if ($this->type_championnat == _TYPE_TOURNOI_)
		{
			$selected_equipes = "";
			$tmp = str_replace('|', ',', $this->journee_info['equipes']);
			$items = explode(',', $tmp);
			foreach($items as $item)
				if ($item != "") $selected_equipes .= $selected_equipes == "" ? $item : ",".$item;
		}
		else
			$selected_equipes = $this->journee_info['equipes'];

		// On initialise toutes les infos des équipes qui participent à cette journée
		if ($nb_eq > 0)
		{
			$req = "SELECT * FROM jb_equipes WHERE id IN (".SQLServices::cleanIN($selected_equipes).")";
			$res = dbc::execSql($req);
			while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
			{
				$this->statsTeams[$row['id']] = new StatJourneeTeam();
				$this->statsTeams[$row['id']]->id = $row['id'];
				if ($row['nb_joueurs'] > 1) // pas > 0 sinon stats pas ok, faudrait voir pourquoi
				{
					$items = explode('|', $row['joueurs']);
					$this->statsTeams[$row['id']]->defenseur = $items[0];
					$this->statsTeams[$row['id']]->attaquant = isset($items[1]) ? $items[1] : -1;

					// Pour les tournois/championnats, on force la création des stats des joueurs
					if ($this->type_championnat == _TYPE_TOURNOI_)
					{
						foreach($items as $elt)
						{
							$this->statsJoueurs[$elt] = new StatJourneeJoueur();
							$this->statsJoueurs[$elt]->id = $elt;
						}
					}
				}
				else // Pour les équipes qui n'ont de joueurs attitrés
				{
					$this->statsTeams[$row['id']]->defenseur = -1;
					$this->statsTeams[$row['id']]->attaquant = -1;
				}
			}
		}
	}

	function SQLUpdateClassementJournee()
	{
		if (!$this->is_journee_alias)
		{
			$update = "UPDATE jb_journees set classement_joueurs='".$this->getClassementPlayers()."', classement_equipes='".$this->getClassementTeams()."' WHERE id=".$this->journee;
			$res = dbc::execSQL($update);
		}
	}

	function SQLUpdateClassementJourneeTournoi($poule)
	{
		// Si matchs de poules alors maj stats poules, sinon autre matchs (classements ou phase finale) byebye ...
		$items = explode('|', $poule);
		if ($items[0] != "P") return;

		$id_j = $this->is_journee_alias ? $this->journee_info['id_journee_mere'] : $this->journee;

		// Mise à jour des stats locales à la poules
		$select = "SELECT * FROM jb_classement_poules WHERE id_champ=".$this->championnat." AND id_journee=".$id_j." AND poule='".$poule."'";
		$res = dbc::execSQL($select);

		if ($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
			$req = "UPDATE jb_classement_poules set classement_joueurs='".$this->getClassementPlayers()."', classement_equipes='".$this->getPoulesClassementTeams($poule)."' WHERE id=".$row['id'];
		else
			$req = "INSERT INTO jb_classement_poules (id_champ, id_journee, poule, classement_joueurs, classement_equipes) VALUES (".$this->championnat.", ".$id_j.", '".$poule."', '".$this->getClassementPlayers()."', '".$this->getPoulesClassementTeams($poule)."')";

		$res = dbc::execSQL($req);
	}

	function ComputeStats()
	{
		$this->initStatsPlayers();
		$this->initStatsTeams();

		// On prend tous les matchs de la journée et on les analyse
		$id_j = $this->type_championnat == _TYPE_TOURNOI_ && $this->is_journee_alias ? $this->journee_info['id_journee_mere'] : $this->journee;
		$req = "SELECT * FROM jb_matchs WHERE id_champ=".$this->championnat." AND id_journee=".$id_j." ".$this->filtre_matchs;

		$res = dbc::execSql($req);
		$ind = 0;
		while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
		{
			// Recherche du vainqueur du matchs
			$vainqueur = StatsJourneeBuilder::kikigagne($row);

			// On zappe si le match est planifié mais non joué (ex: eq1 0-0 eq2)
			if ($vainqueur == 99) continue;

			// Caractéristiques match
			if ($this->type_championnat == _TYPE_TOURNOI_)
			{
				$items  = explode('|', $row['niveau']);
				$type_match  = $items[0];
				$niveau_type = $items[1];

				$points = explode('|', $row['score_points']);
				$victoire = $points[0];
				$defaite  = $points[1];
			}

			// Identification équipes
			$e1 = $row['id_equipe1']; // Equipe 1
			$e2 = $row['id_equipe2']; // Equipe 2

			// Exit si statsTeams inexistant
			if ((!isset($this->statsTeams[$e1]) || !isset($this->statsTeams[$e2])))
			{
				$this->synchronisation = 1;
				continue;
			}

			$j1 = 0; // Défenseur equipe 1
			$j2 = 0; // Attaquant equipe 1
			$j3 = 0; // Défenseur equipe 2
			$j4 = 0; // Attaquant equipe 2

			// Identification joueurs par défaut (si rien dans le champ 'surleterrain')
			if ($this->type_championnat == _TYPE_LIBRE_) {
				$j1 = $this->statsTeams[$e1]->defenseur;
				$j2 = $this->statsTeams[$e1]->attaquant;
				$j3 = $this->statsTeams[$e2]->defenseur;
				$j4 = $this->statsTeams[$e2]->attaquant;

				// Réaffectation attaquant/défenseur equipe1 en fct des positions réelles des joueurs sur le terrain
				if ($row['surleterrain1'] != "")
				{
					$item1 = explode('|', $row['surleterrain1']);
					$j1 = $item1[0];
					$j2 = $item1[1];
				}
				// Réaffectation attaquant/défenseur equipe2 en fct des positions réelles des joueurs sur le terrain
				if ($row['surleterrain2'] != "")
				{
					$item2 = explode('|', $row['surleterrain2']);
					$j3 = $item2[0];
					$j4 = $item2[1];
				}

				// Exit si statsJoueurs inexistant
				if (!isset($this->statsJoueurs[$j1]) || !isset($this->statsJoueurs[$j2]) || !isset($this->statsJoueurs[$j3]) || !isset($this->statsJoueurs[$j4]))
				{
					$this->synchronisation = 1;
					continue;
				}
			}

			// Transformation du résultat du match en score
			$sm = new StatMatch($row['resultat'], $row['nbset']);
			$score = $sm->getScore();

			// Stats générales joueurs
			if ($this->type_championnat == _TYPE_LIBRE_) {
				$this->statsJoueurs[$j1]->matchs_jouesD++;
				$this->statsJoueurs[$j2]->matchs_jouesA++;
				$this->statsJoueurs[$j3]->matchs_jouesD++;
				$this->statsJoueurs[$j4]->matchs_jouesA++;
			}

			// Stats générales équipes
			$this->statsTeams[$e1]->matchs_joues++;
			$this->statsTeams[$e2]->matchs_joues++;

			// Stats Buts pour tous les sets ... (sauf sur forfait)
			if ($score != -1 && $score != -2)
			{
				$this->cumulScores($j1, $j2, $j3, $j4, $e1, $e2, $score[0][0], $score[0][1]);
				if ($row['nbset'] >= 2) $this->cumulScores($j1, $j2, $j3, $j4, $e1, $e2, $score[1][0], $score[1][1]);
				if ($row['nbset'] >= 3) $this->cumulScores($j1, $j2, $j3, $j4, $e1, $e2, $score[2][0], $score[2][1]);
				if ($row['nbset'] >= 4) $this->cumulScores($j1, $j2, $j3, $j4, $e1, $e2, $score[3][0], $score[3][1]);
				if ($row['nbset'] == 5) $this->cumulScores($j1, $j2, $j3, $j4, $e1, $e2, $score[4][0], $score[4][1]);
			} else {
				$this->statsTeams[$score == -1 ? $e1 : $e2]->diff -= $this->forfait_penalite_malus;
				$this->statsTeams[$score == -1 ? $e2 : $e1]->diff += $this->forfait_penalite_bonus;
				if ($this->type_championnat == _TYPE_LIBRE_) {
					$this->statsJoueurs[$score == -1 ? $j1 : $j3]->buts_encaisses += $this->forfait_penalite_malus;
					$this->statsJoueurs[$score == -1 ? $j2 : $j4]->buts_encaisses_attaquant += $this->forfait_penalite_malus;
					$this->statsJoueurs[$score == -1 ? $j3 : $j1]->buts_marques_defenseur += $this->forfait_penalite_bonus;
					$this->statsJoueurs[$score == -1 ? $j4 : $j2]->buts_marques += $this->forfait_penalite_bonus;
				}
			}

			// Pour savoir si un match a été gagné de justesse (sauf sur forfait)
			$de_justesse = 0;
			if ($score != -1 && $score != -2)
			{
				if ($row['nbset'] < 2 && abs($score[0][0] - $score[0][1]) == 1) $de_justesse = 1;
				if ($row['nbset'] == 5) $de_justesse = 1;
			}

			// Calcul des stats
			if ($vainqueur == 1)
			{
				if ($this->type_championnat == _TYPE_TOURNOI_ && (($type_match == "C" && $niveau_type != -1) || ($type_match == "F" && $niveau_type == "1") || ($type_match == "Y" && $niveau_type == "1")))
				{
					$this->statsTeams[$e1]->tournoi_classement = $type_match == "Y" ? ($this->journee_info['tournoi_phase_finale']*2)+1 : $niveau_type;
					$this->statsTeams[$e1]->tournoi_points     = $victoire;
					$this->statsTeams[$e2]->tournoi_classement = ($type_match == "Y" ? ($this->journee_info['tournoi_phase_finale']*2)+1 : $niveau_type) + 1;
					$this->statsTeams[$e2]->tournoi_points     = $defaite;
				}

				if ($this->type_championnat == _TYPE_LIBRE_) {
					$this->statsJoueurs[$j1]->matchs_gagnes++;
					$this->statsJoueurs[$j2]->matchs_gagnes++;
					$this->statsJoueurs[$j3]->matchs_perdus++;
					$this->statsJoueurs[$j4]->matchs_perdus++;
					$this->statsJoueurs[$j1]->matchs_gagnes_defenseur++;
					$this->statsJoueurs[$j2]->matchs_gagnes_attaquant++;
				}

				$this->statsTeams[$e1]->matchs_gagnes++;
				$this->statsTeams[$e2]->matchs_perdus++;
				if ($score == -2) $this->statsTeams[$e2]->matchs_forfaits++;

				if ($row['fanny'] == 1)
				{
					if ($this->type_championnat == _TYPE_LIBRE_) {
						$this->statsJoueurs[$j1]->fanny_out++;
						$this->statsJoueurs[$j2]->fanny_out++;
						$this->statsJoueurs[$j3]->fanny_in++;
						$this->statsJoueurs[$j4]->fanny_in++;
					}

					$this->statsTeams[$e1]->fanny_out++;
					$this->statsTeams[$e2]->fanny_in++;
				}

				if ($de_justesse == 1)
				{
					if ($this->type_championnat == _TYPE_LIBRE_) {
						$this->statsJoueurs[$j1]->justesse_gagnes++;
						$this->statsJoueurs[$j2]->justesse_gagnes++;
						$this->statsJoueurs[$j3]->justesse_perdus++;
						$this->statsJoueurs[$j4]->justesse_perdus++;
					}

					$this->statsTeams[$e1]->justesse_gagnes++;
					$this->statsTeams[$e2]->justesse_perdus++;
				}
			}
			else if ($vainqueur == 2)
			{
				if ($this->type_championnat == _TYPE_TOURNOI_ && (($type_match == "C" && $niveau_type != -1) || ($type_match == "F" && $niveau_type == "1")))
				{
					$this->statsTeams[$e2]->tournoi_classement = $type_match == "Y" ? ($this->journee_info['tournoi_phase_finale']*2)+1 : $niveau_type;
					$this->statsTeams[$e2]->tournoi_points     = $victoire;
					$this->statsTeams[$e1]->tournoi_classement = ($type_match == "Y" ? ($this->journee_info['tournoi_phase_finale']*2)+1 : $niveau_type) + 1;
					$this->statsTeams[$e1]->tournoi_points     = $defaite;
				}

				if ($this->type_championnat == _TYPE_LIBRE_) {
					$this->statsJoueurs[$j1]->matchs_perdus++;
					$this->statsJoueurs[$j2]->matchs_perdus++;
					$this->statsJoueurs[$j3]->matchs_gagnes++;
					$this->statsJoueurs[$j4]->matchs_gagnes++;
					$this->statsJoueurs[$j3]->matchs_gagnes_defenseur++;
					$this->statsJoueurs[$j4]->matchs_gagnes_attaquant++;
				}

				$this->statsTeams[$e1]->matchs_perdus++;
				$this->statsTeams[$e2]->matchs_gagnes++;
				if ($score == -1) $this->statsTeams[$e1]->matchs_forfaits++;

				if ($row['fanny'] == 1)
				{
					if ($this->type_championnat == _TYPE_LIBRE_) {
						$this->statsJoueurs[$j1]->fanny_in++;
						$this->statsJoueurs[$j2]->fanny_in++;
						$this->statsJoueurs[$j3]->fanny_out++;
						$this->statsJoueurs[$j4]->fanny_out++;
					}

					$this->statsTeams[$e1]->fanny_in++;
					$this->statsTeams[$e2]->fanny_out++;
				}
				if ($de_justesse == 1)
				{
					if ($this->type_championnat == _TYPE_LIBRE_) {
						$this->statsJoueurs[$j1]->justesse_perdus++;
						$this->statsJoueurs[$j2]->justesse_perdus++;
						$this->statsJoueurs[$j3]->justesse_gagnes++;
						$this->statsJoueurs[$j4]->justesse_gagnes++;
					}

					$this->statsTeams[$e1]->justesse_perdus++;
					$this->statsTeams[$e2]->justesse_gagnes++;
				}
			}
			else if ($vainqueur == 0 && $this->gestion_nul == 1)
			{
				if ($this->type_championnat == _TYPE_LIBRE_) {
					$this->statsJoueurs[$j1]->matchs_nuls++;
					$this->statsJoueurs[$j2]->matchs_nuls++;
					$this->statsJoueurs[$j3]->matchs_nuls++;
					$this->statsJoueurs[$j4]->matchs_nuls++;
				}

				$this->statsTeams[$e1]->matchs_nuls++;
				$this->statsTeams[$e2]->matchs_nuls++;
			}

			if ($this->type_championnat == _TYPE_LIBRE_) {
				$this->calculDiff($j1);
				$this->calculDiff($j2);
				$this->calculDiff($j3);
				$this->calculDiff($j4);
			}

			$this->statsTeams[$e1]->points = ($this->statsTeams[$e1]->matchs_gagnes * $this->val_victoire) + (($this->statsTeams[$e1]->matchs_perdus - $this->statsTeams[$e1]->matchs_forfaits) * $this->val_defaite) + $this->gestion_nul * ($this->statsTeams[$e1]->matchs_nuls * $this->val_nul);
			$this->statsTeams[$e2]->points = ($this->statsTeams[$e2]->matchs_gagnes * $this->val_victoire) + (($this->statsTeams[$e2]->matchs_perdus - $this->statsTeams[$e2]->matchs_forfaits) * $this->val_defaite) + $this->gestion_nul * ($this->statsTeams[$e2]->matchs_nuls * $this->val_nul);

			$this->nb_matchs++;
		}

		if (isset($this->statsJoueurs[-1])) unset($this->statsJoueurs[-1]);
		mysqli_free_result($res);
	}

	function calculDiff($joueur)
	{
		$this->statsJoueurs[$joueur]->diff_attaquant = $this->statsJoueurs[$joueur]->buts_marques - $this->statsJoueurs[$joueur]->buts_encaisses_attaquant;
		$this->statsJoueurs[$joueur]->diff_defenseur = $this->statsJoueurs[$joueur]->buts_marques_defenseur - $this->statsJoueurs[$joueur]->buts_encaisses;
		$this->statsJoueurs[$joueur]->diff           = $this->statsJoueurs[$joueur]->diff_attaquant + $this->statsJoueurs[$joueur]->diff_defenseur;
	}

	function cumulScores($j1, $j2, $j3, $j4, $e1, $e2, $score1, $score2)
	{
		if ($this->type_championnat == _TYPE_LIBRE_) {
			$this->statsJoueurs[$j1]->buts_encaisses += $score2;
			$this->statsJoueurs[$j2]->buts_marques   += $score1;
			$this->statsJoueurs[$j3]->buts_encaisses += $score1;
			$this->statsJoueurs[$j4]->buts_marques   += $score2;
	        $this->statsJoueurs[$j1]->buts_marques_defenseur   += $score1;
	        $this->statsJoueurs[$j2]->buts_encaisses_attaquant += $score2;
	        $this->statsJoueurs[$j3]->buts_marques_defenseur   += $score2;
	        $this->statsJoueurs[$j4]->buts_encaisses_attaquant += $score1;
		}

		$this->statsTeams[$e1]->buts_marques   += $score1;
		$this->statsTeams[$e1]->buts_encaisses += $score2;
		$this->statsTeams[$e1]->diff           += ($score1 - $score2);
		$this->statsTeams[$e2]->buts_marques   += $score2;
		$this->statsTeams[$e2]->buts_encaisses += $score1;
		$this->statsTeams[$e2]->diff           += ($score2 - $score1);

		if ($score1 != 0 || $score2 != 0)
		{
			if ($this->type_championnat == _TYPE_LIBRE_) {
				$this->statsJoueurs[$j1]->sets_joues++;
				$this->statsJoueurs[$j2]->sets_joues++;
				$this->statsJoueurs[$j3]->sets_joues++;
				$this->statsJoueurs[$j4]->sets_joues++;
			}

			$this->statsTeams[$e1]->sets_joues++;
			$this->statsTeams[$e2]->sets_joues++;

			if ($score1 > $score2)
			{
				if ($this->type_championnat == _TYPE_LIBRE_) {
					$this->statsJoueurs[$j1]->sets_gagnes++;
					$this->statsJoueurs[$j2]->sets_gagnes++;
					$this->statsJoueurs[$j3]->sets_perdus++;
					$this->statsJoueurs[$j4]->sets_perdus++;
				}

				$this->statsTeams[$e1]->sets_gagnes++;
				$this->statsTeams[$e2]->sets_perdus++;
			}
			if ($score1 < $score2)
			{
				if ($this->type_championnat == _TYPE_LIBRE_) {
					$this->statsJoueurs[$j1]->sets_perdus++;
					$this->statsJoueurs[$j2]->sets_perdus++;
					$this->statsJoueurs[$j3]->sets_gagnes++;
					$this->statsJoueurs[$j4]->sets_gagnes++;
				}

				$this->statsTeams[$e1]->sets_perdus++;
				$this->statsTeams[$e2]->sets_gagnes++;
			}
			if ($score1 == $score2)
			{
				if ($this->type_championnat == _TYPE_LIBRE_) {
					$this->statsJoueurs[$j1]->sets_nuls++;
					$this->statsJoueurs[$j2]->sets_nuls++;
					$this->statsJoueurs[$j3]->sets_nuls++;
					$this->statsJoueurs[$j4]->sets_nuls++;
				}

				$this->statsTeams[$e1]->sets_nuls++;
				$this->statsTeams[$e2]->sets_nuls++;
			}

			if ($this->type_championnat == _TYPE_LIBRE_) {
				$this->statsJoueurs[$j1]->sets_diff = $this->statsJoueurs[$j1]->sets_gagnes - $this->statsJoueurs[$j1]->sets_perdus;
				$this->statsJoueurs[$j2]->sets_diff = $this->statsJoueurs[$j2]->sets_gagnes - $this->statsJoueurs[$j2]->sets_perdus;
				$this->statsJoueurs[$j3]->sets_diff = $this->statsJoueurs[$j3]->sets_gagnes - $this->statsJoueurs[$j3]->sets_perdus;
				$this->statsJoueurs[$j4]->sets_diff = $this->statsJoueurs[$j4]->sets_gagnes - $this->statsJoueurs[$j4]->sets_perdus;
			}

			$this->statsTeams[$e1]->sets_diff = $this->statsTeams[$e1]->sets_gagnes - $this->statsTeams[$e1]->sets_perdus;
			$this->statsTeams[$e2]->sets_diff = $this->statsTeams[$e2]->sets_gagnes - $this->statsTeams[$e2]->sets_perdus;
		}
	}

	public static function kikiGagne($match)
	{
		// Détermination du vainqueur du match
		$set_gagne_eq1 = 0;
		$set_gagne_eq2 = 0;

		if (!isset($match['nbset']) || $match['nbset'] < 1) return 99;

		// Transformation du résultat du match en score
		$sm = new StatMatch($match['resultat'], $match['nbset']);
		$score = $sm->getScore();

		// Gestion des forfaits équipes
		if ($score == -1 || $score == -2)
			return ($score == -1 ? 2 : 1);

		// Gestion des matchs planifiés non encore joués
		$match_non_joue = 1;
		for($i = 0; $i < $match['nbset']; $i++)
		{
			if ((isset($score[$i][0]) && $score[$i][0] != 0) || (isset($score[$i][1]) && $score[$i][1] != 0))
				$match_non_joue = 0;
		}
		if ($match_non_joue == 1 && (!isset($match['match_joue']) || $match['match_joue'] == 0)) return 99;

		// On compare chaque set (les sets non joués sont à zéro)
		if ($score[0][0] > $score[0][1])
			$set_gagne_eq1++;
		else if ($score[0][0] < $score[0][1])
			$set_gagne_eq2++;

		// Match qui s est terminé aux tirs au but
		if ($match['nbset'] == 1 && $score[0][0] == $score[0][1])
		{
			if ($match['penaltys'] != "")
			{
				$tirs = explode('|', $match['penaltys']);
				if ($tirs[0] > $tirs[1])
					$set_gagne_eq1++;
				else if ($tirs[0] < $tirs[1])
					$set_gagne_eq2++;
			}
		}

		if ($match['nbset'] >= 2)
		{
			if ($score[1][0] > $score[1][1]) $set_gagne_eq1++; else if ($score[1][0] < $score[1][1]) $set_gagne_eq2++;
		}
		if ($match['nbset'] >= 3)
		{
			if ($score[2][0] > $score[2][1]) $set_gagne_eq1++; else if ($score[2][0] < $score[2][1]) $set_gagne_eq2++;
		}
		if ($match['nbset'] >= 4)
		{
			if ($score[3][0] > $score[3][1]) $set_gagne_eq1++; else if ($score[3][0] < $score[3][1]) $set_gagne_eq2++;
		}
		if ($match['nbset'] == 5)
		{
			if ($score[4][0] > $score[4][1]) $set_gagne_eq1++; else if ($score[4][0] < $score[4][1]) $set_gagne_eq2++;
		}

		if ($set_gagne_eq1 > $set_gagne_eq2)
			return 1;
		else if ($set_gagne_eq1 < $set_gagne_eq2)
			return 2;
		else
			return 0;
	}

	function formatClassementPlayers($classement)
	{
		$ret = "";
        while(list($cle, $val) = each($classement))
        {
			if ($ret != "") $ret .= "|";
            $ret .= $cle."@";
			$ret .= $this->statsJoueurs[$cle]->matchs_jouesA.",";
			$ret .= $this->statsJoueurs[$cle]->matchs_jouesD.",";
			$ret .= $this->statsJoueurs[$cle]->matchs_gagnes.",";
			$ret .= $this->statsJoueurs[$cle]->buts_marques.",";
			$ret .= $this->statsJoueurs[$cle]->buts_encaisses_attaquant.",";
			$ret .= $this->statsJoueurs[$cle]->diff_attaquant.",";
			$ret .= $this->statsJoueurs[$cle]->buts_marques_defenseur.",";
			$ret .= $this->statsJoueurs[$cle]->buts_encaisses.",";
			$ret .= $this->statsJoueurs[$cle]->diff_defenseur.",";
            $ret .= $this->statsJoueurs[$cle]->diff.",";
            $ret .= $this->statsJoueurs[$cle]->fanny_in.",";
            $ret .= $this->statsJoueurs[$cle]->fanny_out.",";
            $ret .= $this->statsJoueurs[$cle]->justesse_gagnes.",";
            $ret .= $this->statsJoueurs[$cle]->justesse_perdus.",";
            $ret .= $this->statsJoueurs[$cle]->sets_joues.",";
            $ret .= $this->statsJoueurs[$cle]->sets_gagnes.",";
            $ret .= $this->statsJoueurs[$cle]->sets_perdus.",";
            $ret .= $this->statsJoueurs[$cle]->sets_diff.",";
            $ret .= $this->statsJoueurs[$cle]->matchs_nuls.",";
            $ret .= $this->statsJoueurs[$cle]->matchs_perdus.",";
            $ret .= $this->statsJoueurs[$cle]->sets_nuls;
        }

		return $ret;
	}

	function getClassementPlayers()
	{
        $classement = array();

		reset($this->statsJoueurs);
		while(list($cle, $val) = each($this->statsJoueurs))
			$classement[$cle] = (($this->statsJoueurs[$cle]->matchs_jouesA+$this->statsJoueurs[$cle]->matchs_jouesD) > 0 ? "1" : "0").sprintf("%03s", $this->statsJoueurs[$cle]->matchs_gagnes).sprintf("%04s", 1000+$this->statsJoueurs[$cle]->diff).sprintf("%04s", 1000+$this->statsJoueurs[$cle]->buts_marques);

		arsort($classement);

        return $this->formatClassementPlayers($classement);
	}

	function formatClassementTeams($classement)
	{
		$ret = "";
        while(list($cle, $val) = each($classement))
        {
			if ($ret != "") $ret .= "|";
            $ret .= $cle."@";
			$ret .= $this->statsTeams[$cle]->points.",";
			$ret .= $this->statsTeams[$cle]->matchs_joues.",";
			$ret .= $this->statsTeams[$cle]->matchs_gagnes.",";
			$ret .= $this->statsTeams[$cle]->matchs_nuls.",";
			$ret .= $this->statsTeams[$cle]->matchs_perdus.",";
			$ret .= $this->statsTeams[$cle]->sets_joues.",";
			$ret .= $this->statsTeams[$cle]->sets_gagnes.",";
			$ret .= $this->statsTeams[$cle]->sets_perdus.",";
			$ret .= $this->statsTeams[$cle]->sets_diff.",";
			$ret .= $this->statsTeams[$cle]->buts_marques.",";
			$ret .= $this->statsTeams[$cle]->buts_encaisses.",";
            $ret .= $this->statsTeams[$cle]->diff.",";
			$ret .= $this->statsTeams[$cle]->tournoi_classement.",";
			$ret .= $this->statsTeams[$cle]->tournoi_points.",";
			$ret .= $this->statsTeams[$cle]->fanny_in.",";
			$ret .= $this->statsTeams[$cle]->fanny_out.",";
			$ret .= $this->statsTeams[$cle]->justesse_gagnes.",";
			$ret .= $this->statsTeams[$cle]->justesse_perdus.",";
			$ret .= $this->statsTeams[$cle]->sets_nuls.",";
			$ret .= $this->statsTeams[$cle]->matchs_forfaits;
        }

		return $ret;
	}

	function getClassementTeams()
	{
        $classement = array();

		if (count($this->statsTeams) == 0) return "";

		reset($this->statsTeams);
		while(list($cle, $val) = each($this->statsTeams))
		{
			if ($this->type_championnat == _TYPE_TOURNOI_)
				$classement[$cle] = sprintf("%03s", $this->statsTeams[$cle]->tournoi_classement).sprintf("%05s", 10000+$this->statsTeams[$cle]->tournoi_points).sprintf("%05s", 10000+$this->statsTeams[$cle]->buts_marques);
			else
				$classement[$cle] = sprintf("%03s", $this->statsTeams[$cle]->points).sprintf("%05s", 10000+$this->statsTeams[$cle]->diff).sprintf("%05s", 10000+$this->statsTeams[$cle]->buts_marques);
//			$classement[$cle] = sprintf("%03s", $this->statsTeams[$cle]->matchs_gagnes).sprintf("%03s", 200+$this->statsTeams[$cle]->diff).sprintf("%03s", 200+$this->statsTeams[$cle]->buts_marques);
		}

		arsort($classement);

        return $this->formatClassementTeams($classement);
	}

	function getPoulesClassementTeams($poule)
	{
		// Recherche du n° de la poule à traiter
		$items = explode('|', $poule);
		$num_poule = $items[1];

        $classement  = array();

		if (count($this->statsTeams) == 0) return "";

		// On récupère les equipes de cette poule
		$poules = explode('|', $this->journee_info['equipes']);

		if (!isset($poules[($num_poule - 1)])) return "";
		$liste_des_equipes = explode(',', $poules[($num_poule - 1)]);

		// Création d'un tableau pour le tri
		reset($this->statsTeams);
		while(list($cle, $val) = each($this->statsTeams))
		{
			if (array_search($cle, $liste_des_equipes) >= 0)
			{
				if ($this->nb_matchs > 0 && $this->statsTeams[$cle]->matchs_joues == 0)
					$classement[$cle] = sprintf("%03s", -1).sprintf("%05s", 10000+$this->statsTeams[$cle]->diff);
				else
					$classement[$cle] = sprintf("%03s", $this->statsTeams[$cle]->points).sprintf("%02s", 0).sprintf("%05s", 10000+$this->statsTeams[$cle]->diff).sprintf("%02s", 0);
			}
		}

		// Tri descendant des équipes
		arsort($classement);

		$cs = $classement;

		// Calcul du goal average + goal average particulier si souhaité pour affiner le classement
		while(list($cle, $val) = each($classement))
		{
			reset($cs);

			while(list($cle2, $val2) = each($cs))
			{
				if (substr($val, 0, 3) == substr($val2, 0, 3))
				{
					$req = "SELECT * FROM jb_matchs WHERE id_champ=".$this->championnat." AND id_journee=".$this->journee." AND niveau like 'P|%' AND ((id_equipe1=".$cle2." && id_equipe2=".$cle.") || (id_equipe1=".$cle." && id_equipe2=".$cle2."))";
					$res = dbc::execSql($req);
					while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
					{
						$vainqueur = $this->kikigagne($row);
						if (($vainqueur == 1 && $row['id_equipe1'] == $cle) || ($vainqueur == 2 && $row['id_equipe2'] == $cle))
						{
							$classement[$cle] = substr($val, 0, 3).sprintf("%02s", $this->gavgp ? substr($val, 3, 2) + 1 : 0).substr($val, 5, 5).sprintf("%02s", substr($val, 3, 2) + 1);
						}
					}
				}
			}
		}

		if ($this->gavgp);

		// Recherche des égalités parfaites (pour les tournois) ou si gestion gavgp
		$tmp_val = "";
		$tmp_cle = "";
		while(list($cle, $val) = each($classement))
		{
			if ($val == $tmp_val)
			{
				$req = "SELECT * FROM jb_matchs WHERE id_champ=".$this->championnat." AND id_journee=".$this->journee." AND ((id_equipe1=".$tmp_cle." && id_equipe2=".$cle.") || (id_equipe1=".$cle." && id_equipe2=".$tmp_cle."))";
				$res = dbc::execSql($req);
				while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
				{
					$vainqueur = $this->kikigagne($row);

					// Inverser l'ordre si l'équipe du dessous à gagner le match contre l'équipe du dessus
					if (($vainqueur == 1 && $row['id_equipe1'] == $cle) || ($vainqueur == 2 && $row['id_equipe2'] == $cle))
						$classement[$cle] = $val."+";
					else
						$classement[$tmp_cle] = $val."+";
				}
			}

			$tmp_val = $val;
			$tmp_cle = $cle;
		}

		// Tri descendant des équipes
		arsort($classement);

        return $this->formatClassementTeams($classement);
	}
}

// ///////////////////////////////////////////////////////////////////////////////////////////
// Statistiques d'un JOUEUR
// ///////////////////////////////////////////////////////////////////////////////////////////
// Exploitation du classement des journees
// ///////////////////////////////////////////////////////////////////////////////////////////
class StatsGlobalBuilder
{
	var $championnat;
	var $type_championnat;
	var $type_sport;
	var $id_joueurs;
	var $stats_joueurs;
	var $nom_joueurs;
	var $nb_matchs;
	var $nb_journees;
	var $max_gagnes_reg = 0;
	var $max_podium_reg = 0;
	var $max_gagnes_inv = 0;
	var $max_podium_inv = 0;

	var $stats_equipes;
	var $best_equipes;
	var $most_equipes;
	var $best_attaques;
	var $best_defenses;
	var $nb_real_teams;

	function __construct($championnat, $type_championnat = _TYPE_LIBRE_, $type_sport = _TS_JORKYBALL_)
	{
		$d1 = date("U");
		$this->championnat      = $championnat;
		$this->type_championnat = $type_championnat;
		$this->type_sport       = $type_sport;

		$this->id_joueurs    = array();
		$this->nom_joueurs   = array();
		$this->best_equipes  = array();
		$this->most_equipes  = array();
		$this->best_attaques = array();
		$this->best_defenses = array();
		$this->nb_real_teams = 0;

        // Récupération du nombre total de matchs joués dans le championnat
        $req = "SELECT count(*) total FROM jb_matchs WHERE id_champ=".$this->championnat;
        $res = dbc::execSQL($req);
        $this->nb_matchs = mysqli_fetch_array($res, MYSQLI_ASSOC);

        // Récupération des infos de la saison active
        $req = "SELECT * FROM jb_saisons WHERE id=".$this->championnat;
        $res = dbc::execSQL($req);
        $saison = mysqli_fetch_array($res, MYSQLI_ASSOC);

        // Récupération de tous les joueurs du championnat
   		if ($this->type_championnat == _TYPE_LIBRE_)
		{
			$filtre = (isset($saison['joueurs']) && $saison['joueurs'] != "" ? " AND id IN (".SQLServices::cleanIN($saison['joueurs']).")" : "");
		}
		// Pour les tournois ou championnats on récupère la liste des joueurs à partir de la liste des équipes de la saison en cours
		else
		{
			$lst = "";
			if ($saison['equipes'] != "")
			{
				$req = "SELECT * FROM jb_equipes WHERE id IN (".SQLServices::cleanIN($saison['equipes']).")";
				$res = dbc::execSql($req);
				while ($equipes = mysqli_fetch_array($res, MYSQLI_ASSOC))
					if ($equipes['joueurs'] != "") $lst .= ($lst == "" ? "" : ",").$equipes['joueurs'];
			}

			$filtre = ($lst != "" ? " AND id IN (".SQLServices::cleanIN(str_replace("|", ",", $lst)).")" : "");
		}

        $req = "SELECT * FROM jb_joueurs WHERE id_champ=".$saison['id_champ'].str_replace(',,', ',', $filtre);
        $res = dbc::execSQL($req);
        while($joueur = mysqli_fetch_array($res, MYSQLI_ASSOC))
        {
        	$this->id_joueurs[$joueur['pseudo']]              = $joueur['id'];
        	$this->nom_joueurs[$joueur['id']]                 = $joueur['pseudo'];
        	$this->stats_joueurs[$joueur['id']]               = new StatGlobalJoueur();
        	$this->stats_joueurs[$joueur['id']]->id           = $joueur['id'];
        	$this->stats_joueurs[$joueur['id']]->nom          = $joueur['nom'];
        	$this->stats_joueurs[$joueur['id']]->prenom       = $joueur['prenom'];
        	$this->stats_joueurs[$joueur['id']]->pseudo       = $joueur['pseudo'];
        	$this->stats_joueurs[$joueur['id']]->dt_naissance = $joueur['dt_naissance'];
        	$this->stats_joueurs[$joueur['id']]->photo        = $joueur['photo'];
        	$this->stats_joueurs[$joueur['id']]->presence     = $joueur['presence'];
        	$this->stats_joueurs[$joueur['id']]->email        = $joueur['email'];
        	$this->stats_joueurs[$joueur['id']]->etat         = $joueur['etat'];
        }

        // Récupération de toutes les équipes du championnat
        $req = "SELECT * FROM jb_equipes WHERE id_champ=".$saison['id_champ'].($saison['equipes'] != "" ? " AND id IN (".SQLServices::cleanIN($saison['equipes']).")" : "");
        $res = dbc::execSQL($req);
        while($equipe = mysqli_fetch_array($res, MYSQLI_ASSOC))
        {
        	// Pour les championnats de type libre, on retire les équipes qui ne contiennent pas des joueurs de la saison courante
        	if ($type_championnat == _TYPE_LIBRE_)
        	{
        		$joueurs_equipe = explode('|', $equipe['joueurs']);
        		$lets_continue = 0;
        		foreach($joueurs_equipe as $je)
	        		if (!isset($this->stats_joueurs[$je])) $lets_continue = 1;
	        	if ($lets_continue == 1) continue;
        	}

        	$this->stats_equipes[$equipe['id']]                 = new StatGlobalTeam();
        	$this->stats_equipes[$equipe['id']]->id             = $equipe['id'];
        	$this->stats_equipes[$equipe['id']]->nom            = $equipe['nom'];
        	$this->stats_equipes[$equipe['id']]->nb_joueurs     = $equipe['nb_joueurs'];
        	$this->stats_equipes[$equipe['id']]->joueurs        = $equipe['joueurs'];
			if ($equipe['nb_joueurs'] > 1)
			{
				$items = explode('|', $equipe['joueurs']);
	        	$this->stats_equipes[$equipe['id']]->defenseur      = $items[0];
	        	$this->stats_equipes[$equipe['id']]->attaquant      = $items[1];
				$this->stats_equipes[$equipe['id']]->reguliere      = (isset($this->stats_joueurs[$items[0]]->presence) && $this->stats_joueurs[$items[0]]->presence == 1 && isset($this->stats_joueurs[$items[1]]->presence) && $this->stats_joueurs[$items[1]]->presence == 1) ? 1 : 0;
			}
			else
			{
	        	$this->stats_equipes[$equipe['id']]->defenseur      = "";
	        	$this->stats_equipes[$equipe['id']]->attaquant      = "";
				$this->stats_equipes[$equipe['id']]->reguliere      = 1;
			}

			$this->best_equipes[$equipe['id']]['pourc_gagnes'] = 0;
			$this->best_equipes[$equipe['id']]['id']           = $equipe['id'];
			$this->most_equipes[$equipe['id']]['joues']        = 0;
			$this->most_equipes[$equipe['id']]['id']           = $equipe['id'];
			$this->best_attaques[$equipe['id']]['attaque']     = 0;
			$this->best_attaques[$equipe['id']]['id']          = $equipe['id'];
			$this->best_defenses[$equipe['id']]['defense']     = 0;
			$this->best_defenses[$equipe['id']]['id']          = $equipe['id'];
        }

        // Récupération de tous les classements de toutes les journees du championnat
        $req = "SELECT date, classement_joueurs, classement_equipes, virtuelle, bonus FROM jb_journees WHERE id_champ=".$this->championnat." ORDER BY date";
        $res = dbc::execSQL($req);
        $this->nb_journees = mysqli_num_rows($res);

		// Compteur de nb de journées jouées
        $nb_journee = 0;
        while($j = mysqli_fetch_array($res))
        {
			// Traitement des stats joueurs
			$this->computeStatsPlayers($nb_journee, $j['date'], $j['classement_joueurs']);

			// Traitement des stats équipes
			$this->computeStatsTeams($nb_journee, $j['date'], $j['classement_equipes'], $j['virtuelle'], $j['bonus']);

        	$nb_journee++;
        }

		// Calcule des stats complémentaires pour les joueurs/équipes
		$this->computeMoreStatsPlayers();
		$this->computeMoreStatsTeams($nb_journee);
		$d2 = date("U");
		//echo $d2."-".$d1;
	}

	function computeStatsTeams($nb_journee, $date_journee, $classement, $virtuelle, $bonus)
	{
		if ($classement == "") return;

		// On affecte le pourcentage de victoire à "" pour cette date à toutes les équipes (utile pour le graph)
//	    reset($this->stats_equipes);
//	    while(list($id, $val) = each($this->stats_equipes)) $this->stats_equipes[$val]->evol_classement[$date_journee] = "";

		$podium = 1;
		$sjt = new StatJourneeTeam();
        $cl  = explode('|', $classement);
		foreach($cl as $c)
        {
			// Init objet statjourneeteam
			$sjt->init($c);

			// Attention une équipe peut être supprimée mais les stats la concernant sont tjs présentes dans les journées
			if (!isset($this->stats_equipes[$sjt->id])) continue;

       		$this->stats_equipes[$sjt->id]->points          += $sjt->points;
       		$this->stats_equipes[$sjt->id]->matchs_joues    += $sjt->matchs_joues;
       		$this->stats_equipes[$sjt->id]->matchs_gagnes   += $sjt->matchs_gagnes;
       		$this->stats_equipes[$sjt->id]->matchs_perdus   += $sjt->matchs_perdus;
       		$this->stats_equipes[$sjt->id]->matchs_nuls     += $sjt->matchs_nuls;
       		$this->stats_equipes[$sjt->id]->sets_joues      += $sjt->sets_joues;
       		$this->stats_equipes[$sjt->id]->sets_gagnes     += $sjt->sets_gagnes;
       		$this->stats_equipes[$sjt->id]->sets_nuls       += $sjt->sets_nuls;
       		$this->stats_equipes[$sjt->id]->sets_perdus     += $sjt->sets_perdus;
       		$this->stats_equipes[$sjt->id]->sets_diff       += $sjt->sets_diff;
       		$this->stats_equipes[$sjt->id]->buts_marques    += $sjt->buts_marques;
       		$this->stats_equipes[$sjt->id]->buts_encaisses  += $sjt->buts_encaisses;
       		$this->stats_equipes[$sjt->id]->diff            += $sjt->diff;
       		$this->stats_equipes[$sjt->id]->tournoi_points  += $sjt->tournoi_points;
       		$this->stats_equipes[$sjt->id]->fanny_in        += $sjt->fanny_in;
       		$this->stats_equipes[$sjt->id]->fanny_out       += $sjt->fanny_out;
       		$this->stats_equipes[$sjt->id]->justesse_gagnes += $sjt->justesse_gagnes;
       		$this->stats_equipes[$sjt->id]->justesse_perdus += $sjt->justesse_perdus;
       		$this->stats_equipes[$sjt->id]->matchs_forfaits += $sjt->matchs_forfaits;
       		if ($virtuelle == 0)
			{
			  	$this->stats_equipes[$sjt->id]->stat_attdef_buts_marques   += $sjt->buts_marques;
       			$this->stats_equipes[$sjt->id]->stat_attdef_buts_encaisses += $sjt->buts_encaisses;
       			$this->stats_equipes[$sjt->id]->stat_attdef_sets_joues     += $sjt->sets_joues;
			}
			if ($this->stats_equipes[$sjt->id]->matchs_joues)
			{
	       		$this->stats_equipes[$sjt->id]->pourc_gagnes = floor(($this->stats_equipes[$sjt->id]->matchs_gagnes * 100) / $this->stats_equipes[$sjt->id]->matchs_joues);
	       		$this->stats_equipes[$sjt->id]->pourc_nuls   = floor(($this->stats_equipes[$sjt->id]->matchs_nuls   * 100) / $this->stats_equipes[$sjt->id]->matchs_joues);
	       		$this->stats_equipes[$sjt->id]->pourc_perdus = floor(($this->stats_equipes[$sjt->id]->matchs_perdus * 100) / $this->stats_equipes[$sjt->id]->matchs_joues);
	       	}
			$this->stats_equipes[$sjt->id]->tournoi_classement_moy += $sjt->tournoi_classement;
			if ($sjt->tournoi_classement > 0)
				$this->stats_equipes[$sjt->id]->tournoi_nb_participation++;

			// Tableau annexe (et redondant) permettant un tri rapide sur meilleure équipe/équipe le plus sur le terreain
			$this->best_equipes[$sjt->id]['pourc_gagnes'] = $this->stats_equipes[$sjt->id]->pourc_gagnes;
			$this->most_equipes[$sjt->id]['joues']        = $this->stats_equipes[$sjt->id]->matchs_joues;
			if ($this->stats_equipes[$sjt->id]->stat_attdef_sets_joues > 0) $this->stats_equipes[$sjt->id]->stat_attaque = $this->stats_equipes[$sjt->id]->stat_attdef_buts_marques   / $this->stats_equipes[$sjt->id]->stat_attdef_sets_joues;
			if ($this->stats_equipes[$sjt->id]->stat_attdef_sets_joues > 0) $this->stats_equipes[$sjt->id]->stat_defense = $this->stats_equipes[$sjt->id]->stat_attdef_buts_encaisses / $this->stats_equipes[$sjt->id]->stat_attdef_sets_joues;

			// On garde le classement de chaque équipe pour courbe d'évolution classement
			if ($this->type_championnat == _TYPE_TOURNOI_)
			{
				if ($sjt->tournoi_classement > 0)
					$this->stats_equipes[$sjt->id]->evol_classement[$date_journee] = $sjt->tournoi_classement;
				else
					$this->stats_equipes[$sjt->id]->evol_classement[$date_journee] = "";
			}
			else
			{
				if ($sjt->matchs_joues > 0)
					$this->stats_equipes[$sjt->id]->evol_classement[$date_journee] = $podium++;
			}
		}

		$bm = array();
		$bm = explode(',', $bonus);
		foreach($bm as $item)
		{
			if ($item != "")
			{
				$vals = explode('=', $item);
				if (isset($vals[1]))
					$this->stats_equipes[$vals[0]]->bonus = $vals[1];
			}
		}
	}

	function computeMoreStatsTeams()
	{
		// Variables temporaires pour tri multi-dimensionnel
		$order_best  = array();
		$order_joues = array();
		$order_most  = array();
		$order_attaque = array();
		$order_defense = array();

		// Si pas d'équipe ...
		if (!is_array($this->stats_equipes) || count(array($this->stats_equipes)) == 0) return;

        reset($this->stats_equipes);
        while(list($id, $val) = each($this->stats_equipes))
        {
			if ($this->stats_equipes[$id]->tournoi_nb_participation > 0)
				$this->stats_equipes[$id]->tournoi_classement_moy = sprintf("%.2f", ($this->stats_equipes[$id]->tournoi_classement_moy / $this->stats_equipes[$id]->tournoi_nb_participation));
			if ($val->matchs_joues > 0) $this->nb_real_teams++;
			$order_best[$id]  = $val->pourc_gagnes;
			$order_joues[$id] = $val->matchs_joues;
			$order_most[$id]  = $val->matchs_joues;
			$order_attaque[$id]  = $val->stat_attaque;
			$order_defense[$id]  = $val->stat_defense;

			// Après utilisation pour les stats, on formatte pour la présentation
			$this->stats_equipes[$id]->stat_attaque = sprintf("%.2f", $this->stats_equipes[$id]->stat_attaque);
			$this->stats_equipes[$id]->stat_defense = sprintf("%.2f", $this->stats_equipes[$id]->stat_defense);
		}

		// Trie des tableaux
		array_multisort($order_best, SORT_DESC, $order_joues, SORT_DESC, $this->best_equipes);
		array_multisort($order_most, SORT_DESC, $order_joues, SORT_DESC, $this->most_equipes);
		array_multisort($order_attaque, SORT_DESC, $order_joues, SORT_DESC, $this->best_attaques);
		array_multisort($order_defense, SORT_ASC, $order_joues, SORT_DESC, $this->best_defenses);
	}

	function computeStatsPlayers($nb_journee, $date_journee, $classement)
	{
		// Si classement vide alors on ne calcule rien
		if ($classement == "") return;

		// On affecte le pourcentage de victoire à "" pour cette date à tous les joueurs (utile pour le graph)
	    reset($this->id_joueurs);
	    while(list($id, $val) = each($this->id_joueurs)) $this->stats_joueurs[$val]->evol_pourc_gagne[$date_journee] = "";

		// Compteur pour le classement sur la journée
		$k   = 0;
		$sjj = new StatJourneeJoueur();
        $cl  = explode('|', $classement);
		foreach($cl as $c)
        {
			// Init objet statjourneejoueur
			$sjj->init($c);

			// Attention un joueur peut être supprimé mais les stats le concernant sont tjs présentes dans les journées
			if (!isset($this->stats_joueurs[$sjj->id])) continue;

			// Calcul matchs joués
			$matchs_joues = $sjj->matchs_jouesA + $sjj->matchs_jouesD;

			// Si le joueur n'a joué aucun match alors on passe
			if ($matchs_joues == 0) continue;

       		$this->stats_joueurs[$sjj->id]->joues      += $matchs_joues;
       		$this->stats_joueurs[$sjj->id]->jouesA     += $sjj->matchs_jouesA;
       		$this->stats_joueurs[$sjj->id]->jouesD     += $sjj->matchs_jouesD;
       		$this->stats_joueurs[$sjj->id]->gagnes     += $sjj->matchs_gagnes;
       		$this->stats_joueurs[$sjj->id]->nuls       += $sjj->matchs_nuls;
       		$this->stats_joueurs[$sjj->id]->perdus     += $sjj->matchs_perdus;
       		$this->stats_joueurs[$sjj->id]->marquesA   += $sjj->buts_marques;
       		$this->stats_joueurs[$sjj->id]->encaissesD += $sjj->buts_encaisses;
			$this->stats_joueurs[$sjj->id]->fanny_in   += $sjj->fanny_in;
			$this->stats_joueurs[$sjj->id]->fanny_out  += $sjj->fanny_out;
			$this->stats_joueurs[$sjj->id]->justesse_gagnes += $sjj->justesse_gagnes;
			$this->stats_joueurs[$sjj->id]->justesse_perdus += $sjj->justesse_perdus;
       		$this->stats_joueurs[$sjj->id]->sets_joues += $sjj->sets_joues;
       		$this->stats_joueurs[$sjj->id]->sets_gagnes+= $sjj->sets_gagnes;
       		$this->stats_joueurs[$sjj->id]->sets_nuls  += $sjj->sets_nuls;
       		$this->stats_joueurs[$sjj->id]->sets_perdus+= $sjj->sets_perdus;
       		$this->stats_joueurs[$sjj->id]->sets_diff  += $sjj->sets_diff;
			if ($matchs_joues != 0)
				$this->stats_joueurs[$sjj->id]->forme_last_gagnes = floor(($sjj->matchs_gagnes * 100) / $matchs_joues);
			$this->stats_joueurs[$sjj->id]->forme_last_date = $date_journee;

       		// On fait des micros stats sur les 4 dernières journees (etat de forme du joueur)
       		if ($nb_journee > ($this->nb_journees - 5))
       		{
       			$this->stats_joueurs[$sjj->id]->forme_participation++;
       			$this->stats_joueurs[$sjj->id]->forme_joues  += $matchs_joues;
       			$this->stats_joueurs[$sjj->id]->forme_gagnes += $sjj->matchs_gagnes;
       		}

			// Statistiques podium
			if ($k == 0)
				$this->stats_joueurs[$sjj->id]->podium++;
			else if ($k == 1)
				$this->stats_joueurs[$sjj->id]->polidor++;

			// On garde les pourcentages de victoires de chaque journée pour courbe d'évolution
			if ($matchs_joues != 0)
				$this->stats_joueurs[$sjj->id]->evol_pourc_gagne[$date_journee] = floor(($sjj->matchs_gagnes * 100) / $matchs_joues);

			$k++;
		}
	}

	function computeMoreStatsPlayers()
	{
		// Si pas de joueurs ...
		if (!is_array($this->stats_joueurs) || count(array($this->stats_joueurs)) == 0) return;

        reset($this->stats_joueurs);
        while(list($id, $val) = each($this->stats_joueurs))
        {
        	$this->stats_joueurs[$id]->lib_presence = $this->stats_joueurs[$id]->presence == 1 ? "Régulier" : "Invité";

        	if ($this->stats_joueurs[$id]->jouesA != 0)
				$this->stats_joueurs[$id]->moy_marquesA = sprintf("%.2f", $this->stats_joueurs[$id]->marquesA / $this->stats_joueurs[$id]->jouesA);
        	if ($this->stats_joueurs[$id]->jouesD != 0)
				$this->stats_joueurs[$id]->moy_encaissesD = sprintf("%.2f", $this->stats_joueurs[$id]->encaissesD / $this->stats_joueurs[$id]->jouesD);

        	if ($this->stats_joueurs[$id]->joues != 0)
        	{
        		if ($this->stats_joueurs[$id]->joues != 0)
        		{
	        		$this->stats_joueurs[$id]->pourc_gagnes = ($this->stats_joueurs[$id]->gagnes * 100) / $this->stats_joueurs[$id]->joues;
	        		$this->stats_joueurs[$id]->pourc_nuls   = ($this->stats_joueurs[$id]->nuls   * 100) / $this->stats_joueurs[$id]->joues;
	        		$this->stats_joueurs[$id]->pourc_perdus = ($this->stats_joueurs[$id]->perdus * 100) / $this->stats_joueurs[$id]->joues;
	        	}
        		if ($this->nb_matchs['total'] != 0)
	        		$this->stats_joueurs[$id]->pourc_joues  = floor(($this->stats_joueurs[$id]->joues * 100) / $this->nb_matchs['total']);
				if ($this->stats_joueurs[$id]->presence == 1)
				{
					$this->max_gagnes_reg = max($this->max_gagnes_reg, $this->stats_joueurs[$id]->pourc_gagnes);
					$this->max_podium_reg = max($this->max_podium_reg, $this->stats_joueurs[$id]->podium);
				}
				else
				{
					$this->max_gagnes_inv = max($this->max_gagnes_inv, $this->stats_joueurs[$id]->pourc_gagnes);
					$this->max_podium_inv = max($this->max_podium_inv, $this->stats_joueurs[$id]->podium);
				}
        	}

			// Gestion de la forme sur le dernier match joué par rapport à la moyenne générale des matchs gagnés
			$this->calculeFormeLastDay($id);

			// Calcul la forme globale du joueur
        	$this->calculeIndiceForme($id);
		}

		// Attributions des medailles
		$medailles = $this->getBestMedaillePlayer();
	}

	function calculeIndiceForme($id)
	{
		// Si 1 seule journée jouée on prend la forme de la dernière journee
		if ($this->stats_joueurs[$id]->forme_participation < 2) {
			$this->stats_joueurs[$id]->forme_indice = $this->stats_joueurs[$id]->forme_last_indice;
			return;
		}

		$i = 1;
		$tab1 = array();
		$tab2 = array();
		foreach($this->stats_joueurs[$id]->evol_pourc_gagne as $item) {
			if (is_numeric($item)) {
				$tab1[] = $item;
				$tab2[] = $i++;
			}
		}

		if (count($tab1) < 2) {
			$this->stats_joueurs[$id]->forme_indice = $this->stats_joueurs[$id]->forme_last_indice;
			return;
		}

		$LR = Wrapper::linearRegression($tab2, $tab1);

		if ($this->stats_joueurs[$id]->forme_joues != 0)
	    	$this->stats_joueurs[$id]->forme_gagnes = floor(($this->stats_joueurs[$id]->forme_gagnes * 100) / $this->stats_joueurs[$id]->forme_joues);

		$alt = "Etat de forme sur les 4 dernières journées";
		$rank = 2;

		if ($LR['b'] > 80) $rank = 5;
		else if ($LR['b'] > 60) $rank = 4;
		else if ($LR['b'] > 40) $rank = 3;
		else if ($LR['b'] > 20) $rank = 2;
		else $rank = 1;

		if ($LR['m'] > 20) $pente = 2;
		else if ($LR['m'] > 5) $pente = 1;
		else if ($LR['m'] > -5) $pente = 0;
		else if ($LR['m'] > -20) $pente = -1;
		else $pente = -2;

		$rank += $pente;
		$rank = max(min($rank, 5), 1);

		if ($this->stats_joueurs[$id]->etat == 1) $rank = 8; // Blessé
		if ($this->stats_joueurs[$id]->etat == 2) $rank = 9; // Vacance

		$this->stats_joueurs[$id]->forme_indice = "<IMG SRC=../images/fleches/fleche".$rank.".gif ALT=\"".$alt."\" BORDER=0 />";
	}

	function calculeIndiceFormeOld($id)
	{
		// Si on a été présent au moins à 3 des 4 dernières journées alors on calcule la forme du joueur sinon on prend la forme de la dernière journee
		if ($this->stats_joueurs[$id]->forme_participation < 3) {
			$this->stats_joueurs[$id]->forme_indice = $this->stats_joueurs[$id]->forme_last_indice;
			return;
		}

		// On récupère toutes les valeurs non nulles sur les 4 dernières journées
		$xtab =array();
		$tab = array_reverse($this->stats_joueurs[$id]->evol_pourc_gagne);
		$item = array_shift($tab);
		if ($item != "") $xtab[] = $item;
		$item = array_shift($tab);
		if ($item != "") $xtab[] = $item;
		$item = array_shift($tab);
		if ($item != "") $xtab[] = $item;
		$item = array_shift($tab);
		if ($item != "") $xtab[] = $item;
		$xxtab = array_reverse($xtab);

		// On calcule la somme des variations
		$indice = 0;
		for($i=0; $i < count($xxtab) - 1; $i++) $indice += $xxtab[$i+1] - $xxtab[$i];

		if ($this->stats_joueurs[$id]->forme_joues != 0)
	    	$this->stats_joueurs[$id]->forme_gagnes = floor(($this->stats_joueurs[$id]->forme_gagnes * 100) / $this->stats_joueurs[$id]->forme_joues);

		$alt = "Etat de forme sur les 4 dernières journées";
		$rank = 2;
		if ($indice >= -5 && $indice <= 5) $rank = 3;
		else if ($indice < -20)	$rank = 1;
		else if ($indice < -5)	$rank = 2;
		else if ($indice > 20)	$rank = 5;
		else if ($indice > 5)	$rank = 4;

		$this->stats_joueurs[$id]->forme_indice = "<IMG SRC=../images/fleches/fleche".$rank.".gif ALT=\"".$alt."\" BORDER=0 />";
	}

	function calculeFormeLastDay($id)
	{
		$alt = "Dernière journée jouée : ".$this->stats_joueurs[$id]->forme_last_date." [".$this->stats_joueurs[$id]->forme_last_gagnes." % matchs gagnés]";
		$indice = $this->stats_joueurs[$id]->forme_last_gagnes - $this->stats_joueurs[$id]->pourc_gagnes;
		$rank = 2;
        if ($indice >= -5 && $indice <= 5) 		$rank = 3;
        else if ($indice < -20)	$rank = 1;
       	else if ($indice < -5)	$rank = 2;
       	else if ($indice > 20)	$rank = 5;
       	else if ($indice > 5)	$rank = 4;

		$this->stats_joueurs[$id]->forme_last_indice = "<IMG SRC=../images/fleches/fleche".$rank.".gif ALT=\"".$alt."\" BORDER=0 />";
	}

	function getIdPlayers() {
		return $this->id_joueurs;
	}

	function getPlayersName() {
		return $this->nom_joueurs;
	}

	function getNbMatchs() {
		return $this->nb_matchs['total'];
	}

	function getNbJournees() {
		return $this->nb_journees;
	}

	function getMoyMatchsJoues() {
		return ($this->getNbJournees() == 0 ? 0 : $this->getNbMatchs() / $this->getNbJournees());
	}

	function getMoyMatchsTeam() {
		return ($this->nb_real_teams == 0 ? 0 : $this->getNbMatchs() / $this->nb_real_teams);
	}

	function getStatsPlayers() {
		return $this->stats_joueurs;
	}

	function getStatsPlayer($id) {
		return $this->stats_joueurs[$id];
	}

	function isBestPerformeur($id) {
		if ($this->stats_joueurs[$id]->joues == 0) return false;

		if ($this->stats_joueurs[$id]->presence == 1)
			return ($this->stats_joueurs[$id]->pourc_gagnes < $this->max_gagnes_reg ? false : true);
		else
			return ($this->stats_joueurs[$id]->pourc_gagnes < $this->max_gagnes_inv ? false : true);
	}

	function isBestMedaille($id) {
		if ($this->stats_joueurs[$id]->joues == 0) return false;

		if ($this->stats_joueurs[$id]->presence == 1)
			return ($this->stats_joueurs[$id]->podium < $this->max_podium_reg ? false : true);
		else
			return ($this->stats_joueurs[$id]->podium < $this->max_podium_inv ? false : true);
	}

	function getStatsTeams($id_equipe = "") {
		return ($id_equipe == "" ? $this->stats_equipes : $this->stats_equipes[$id_equipe]);
	}

	function getBestTeams($id_joueur = "")
	{
		$res = array();
		$sort1 = array();
		$sort2 = array();
		$sort3 = array();
		reset($this->best_equipes);
		while(list($cle, $val) = each($this->best_equipes))
		{
			$id = $val['id'];
			if ($id_joueur != "" && $this->stats_equipes[$id]->defenseur != $id_joueur && $this->stats_equipes[$id]->attaquant != $id_joueur) continue;
			if ($this->stats_equipes[$id]->matchs_joues == 0 || $this->stats_equipes[$id]->pourc_gagnes == 0) continue;
			if ($this->stats_equipes[$id]->matchs_joues < $this->getMoyMatchsTeam()) continue;
			$res[] = $this->stats_equipes[$id];
			$sort1[$id] = $this->stats_equipes[$id]->pourc_gagnes;
			$sort2[$id] = $this->stats_equipes[$id]->matchs_joues;
			$sort3[$id] = $this->stats_equipes[$id]->diff;
		}

		// Tri sur le % matchs gagnés + matchs joués + goal average
		array_multisort($sort1, SORT_DESC, $sort2, SORT_DESC, $sort3, SORT_DESC, $res);

		return $res;
	}

	function getBestTeamsFull($id_joueur = "")
	{
		$res = array();
		reset($this->best_equipes);
		while(list($cle, $val) = each($this->best_equipes))
		{
			$id = $val['id'];
			if ($id_joueur != "" && $this->stats_equipes[$id]->defenseur != $id_joueur && $this->stats_equipes[$id]->attaquant != $id_joueur) continue;
			$res[] = $this->stats_equipes[$id];
		}
		return $res;
	}

	function getBestTeamsByTournoiPoints($method = 1)
	{
		$res   = array();
		$sort1 = array();
		$sort2 = array();
		$sort3 = array();
		reset($this->best_equipes);
		while(list($cle, $val) = each($this->best_equipes))
		{
			$id = $val['id'];
			$this->stats_equipes[$id]->tournoi_points += $this->stats_equipes[$id]->bonus;
			$res[$id]   = $this->stats_equipes[$id];
			$sort1[$id] = ($this->nb_matchs['total'] > 0 && $this->stats_equipes[$id]->matchs_joues == 0 && $this->stats_equipes[$id]->tournoi_points == 0) ? -1 : $this->stats_equipes[$id]->tournoi_points;
			$sort2[$id] = $this->stats_equipes[$id]->tournoi_classement_moy;
			$sort3[$id] = $this->stats_equipes[$id]->diff;
		}

		// Methode 1 : Tri sur le nombre de point, le goal average et la moyenne du classement
		if ($method == 1)
			array_multisort($sort1, SORT_DESC, $sort3, SORT_DESC, $sort2, SORT_ASC, $res);

		// Methode 2 : Tri sur le nombre de point, la moyenne du classement et le goal average
		if ($method == 2)
			array_multisort($sort1, SORT_DESC, $sort2, SORT_ASC, $sort3, SORT_DESC, $res);

		return $res;
	}

	function getBestTeamsByPoints()
	{
		global $libelle_genre;
		$res   = array();
		$sort1 = array();
		$sort2 = array();
		$sort3 = array();
		$sort4 = array();
		reset($this->best_equipes);
		while(list($cle, $val) = each($this->best_equipes))
		{
			$id = $val['id'];
			$res[$id]   = $this->stats_equipes[$id];
			if ($this->type_sport == _TS_FOOTBALL_)
			{
				$sort1[$id] = ($this->nb_matchs['total'] > 0 && $this->stats_equipes[$id]->matchs_joues == 0) ? -1 : $this->stats_equipes[$id]->points;
				$sort2[$id] = $this->stats_equipes[$id]->diff;
				$sort3[$id] = $this->stats_equipes[$id]->buts_marques;
			}
			else
			{
				$sort1[$id] = ($this->nb_matchs['total'] > 0 && $this->stats_equipes[$id]->matchs_joues == 0) ? -1 : $this->stats_equipes[$id]->points;
				$sort2[$id] = $this->stats_equipes[$id]->tournoi_classement_moy;
				$sort3[$id] = $this->stats_equipes[$id]->sets_diff;
				$sort4[$id] = $this->stats_equipes[$id]->diff;
			}
		}

		// Tri sur le nombre de point, la moyenne du classement et le goal average
		if ($this->type_sport == _TS_FOOTBALL_)
			array_multisort($sort1, SORT_DESC, $sort2, SORT_DESC, $sort3, SORT_DESC, $res);
		else
			array_multisort($sort1, SORT_DESC, $sort2, SORT_ASC, $sort3, SORT_DESC, $sort4, SORT_DESC, $res);

		return $res;
	}

	function getMostTeams($id_joueur = "")
	{
		$res = array();
		reset($this->most_equipes);
		while(list($cle, $val) = each($this->most_equipes))
		{
			$id = $val['id'];
			if ($id_joueur != "" && $this->stats_equipes[$id]->defenseur != $id_joueur && $this->stats_equipes[$id]->attaquant != $id_joueur) continue;
			$res[] = $this->stats_equipes[$id];
		}
		return $res;
	}

	function getBestAttaques()
	{
		$res = array();
		reset($this->best_attaques);
		while(list($cle, $val) = each($this->best_attaques))
		{
			$id = $val['id'];
			if ($this->stats_equipes[$id]->matchs_joues > 0) $res[] = $this->stats_equipes[$id];
		}
		return $res;
	}

	function getBestDefenses()
	{
		$res = array();
		reset($this->best_defenses);
		while(list($cle, $val) = each($this->best_defenses))
		{
			$id = $val['id'];
			if ($this->stats_equipes[$id]->matchs_joues > 0) $res[] = $this->stats_equipes[$id];
		}
		return $res;
	}

	function getBestMedaillePlayer()
	{
		$res   = array();
		$sort1 = array();

		// Si pas de joueurs ...
		if (!is_array($this->stats_joueurs) || count(array($this->stats_joueurs)) == 0) return;

		reset($this->stats_joueurs);
		while(list($cle, $val) = each($this->stats_joueurs))
		{
			if ($val->joues > 0 && $val->presence == 1)
			{
				$points = $val->podium*2 + $val->polidor + ($val->pourc_gagnes > 80 ? 10 : ($val->pourc_gagnes > 60 ? 7 : ($val->pourc_gagnes > 40 ? 4 : ($val->pourc_gagnes > 20 ? 2 : 1))));
				$res[$cle]   = array("id" => $val->id, "nom" => $val->pseudo, "points" => $points);
				$sort1[$cle] = $points;
			}
		}

		// Tri sur le nombre de buts encaissés et le goal average
		array_multisort($sort1, SORT_DESC, $res);

		if (count($res) >= 1 && isset($this->stats_joueurs[$res[0]['id']]))  $this->stats_joueurs[$res[0]['id']]->medaille = _GOLD_MEDAILLE_;
		if (count($res) >= 2 && isset($this->stats_joueurs[$res[1]['id']]))  $this->stats_joueurs[$res[1]['id']]->medaille = _SILVER_MEDAILLE_;
		if (count($res) >= 3 && isset($this->stats_joueurs[$res[2]['id']]))  $this->stats_joueurs[$res[2]['id']]->medaille = _BRONZE_MEDAILLE_;

		return $res;
	}

	function getBestJoueursDefensesAttaquants($option)
	{
		$res   = array();
		$sort1 = array();
		reset($this->stats_joueurs);
		while(list($cle, $val) = each($this->stats_joueurs))
		{
			if ($val->joues > 0)
			{
				$val->nom  = $val->nom." ".$val->prenom;
				$res[$cle]   = $val;
				$sort1[$cle] = $option == 0 ? $val->moy_encaissesD : $val->moy_marquesA;
			}
		}

		// Tri sur le nombre de buts encaissés et le goal average
		array_multisort($sort1, $option == 0 ? SORT_ASC : SORT_DESC, $res);

		return $res;
	}

	function getBestJoueursDefenses() { return $this->getBestJoueursDefensesAttaquants(0); }
	function getBestJoueursAttaquants() { return $this->getBestJoueursDefensesAttaquants(1); }
}

?>
