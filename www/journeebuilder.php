<?

class JourneeBuilder
{
	var $nb_joueurs;
	var $joueurs;
	var $nom_joueurs;
	var $pseudo_joueurs;
	var $defenseurs;
	var $attaquants;
	var $fp;
	var $match_par_joueur;
	var $match_par_attaquant;
	var $match_par_defenseur;
	var $handicap_def;
	var $handicap_att;
	var $equipe1;
	var $equipe2;
	var $matchs;
	var $matchs_affected;
	var $max_matchs;

	////////////////////////////////////////////////////////////////////////
	// Constructeur
	////////////////////////////////////////////////////////////////////////
	function JourneeBuilder($championnat, $users_selected)
	{
		$this->getJoueursFromDB($championnat, $users_selected);

		$this->defenseurs = array();
		$this->attaquants = array();
		$this->match_par_joueur = array();
		$this->match_par_attaquant = array();
		$this->match_par_defenseur = array();
		$this->handicap_def = array();
		$this->handicap_att = array();
		$this->equipe1 = array();
		$this->equipe2 = array();
		$this->matchs = array();
        $this->matchs_affected = array();
		$this->max_matchs = 10000;
		$this->nb_joueurs = count($this->joueurs);

		foreach($this->joueurs as $j)
		{
			$this->defenseurs[] = $j;
			$this->attaquants[] = $j;
		}

		////////////////////////////////////////////////////////////////////////
		// M�lange des joueurs
		srand((float)microtime()*1000000);
		shuffle($this->defenseurs);
		shuffle($this->attaquants);
	}

	////////////////////////////////////////////////////////////////////////
	// Initialisateurs de compteurs
	////////////////////////////////////////////////////////////////////////
	function initMatchs()
	{
		if (isset($this->matchs_affected))
		{
			unset($this->matchs_affected);
			$this->matchs_affected = array();
		}

		foreach($this->joueurs as $j)
		{
			$this->match_par_joueur[$j] = 0;
			$this->match_par_attaquant[$j] = 0;
			$this->match_par_defenseur[$j] = 0;
			$this->handicap_def[$j] = 0;
			$this->handicap_att[$j] = 0;
		}
	}

	////////////////////////////////////////////////////////////////////////
	// Collecte des joueurs(D�fenseurs/Attaquants)
	////////////////////////////////////////////////////////////////////////
	function getJoueursFromDB($championnat, $users_selected)
	{
		$db = dbc::connect();
		$req = "SELECT * FROM jb_joueurs WHERE id_champ=".$championnat." AND id IN (".$users_selected.")";
		$res = dbc::execSql($req);
		while($row = mysqli_fetch_array($res))
		{
			$this->joueurs[] = $row['id'];
			$this->nom_joueurs[$row['id']] = $row['nom']." ".$row['prenom'];
			$this->pseudo_joueurs[$row['id']] = strlen($row['pseudo']) > 0 ? $row['pseudo'] : $row['nom']." ".$row['prenom'];
		}
		mysqli_free_result($res);
		mysqli_close($db);
	}

	////////////////////////////////////////////////////////////////////////
	// Cr�ation des matchs
	////////////////////////////////////////////////////////////////////////
	function getMatchs()
	{
		$this->buildAllTeams();
		$this->buildAllMatchs();

		// Sauvegarde des matchs
		$matchs_svg  = $this->matchs;
		$matchs_best = array();
		$satisfaction_best = 9999;

		// On cr�� les matchs jusqu'� ce que l'indice de satisfaction soit �gale � 0 ou
		// que l'on a atteint un nb d'it�ration max.
		$iteration = 0;
		do
		{
			$this->initMatchs();
			$this->computeMatchs2();
			$this->computeStats();
			$this->matchs = $matchs_svg;

			$satisfaction = $this->getIndiceSatisfaction();
			if ($satisfaction < $satisfaction_best)
			{
				$satisfaction_best = $satisfaction;
				$matchs_best = $this->matchs_affected;
			}
		}
		while ($satisfaction != 0 && $iteration++ < 0);

		// Quoi qu'il arrive on r�cup�re les best matchs
		$this->initMatchs();
		$this->matchs_affected = $matchs_best;
		$this->computeStats();

		reset($this->matchs_affected);
		return $this->matchs_affected;
	}

	////////////////////////////////////////////////////////////////////////
	// Fonctions pour les logs
	////////////////////////////////////////////////////////////////////////
	function xlog($str)	 { fwrite($this->fp, $str); }
	function xlogOpen()	 { $this->fp = fopen("c:\\log.txt", "w"); }
	function xlogClose() { fclose($this->fp); }
	////////////////////////////////////////////////////////////////////////

	function setMaxMatchs($max) {
		$this->max_matchs = $max;
	}

	function getMatchsParJoueur() {
		reset($this->match_par_joueur);
		return $this->match_par_joueur;
	}

	function getMatchsParAttaquant() {
		reset($this->match_par_attaquant);
		return $this->match_par_attaquant;
	}

	function getMatchsParDefenseur() {
		reset($this->match_par_defenseur);
		return $this->match_par_defenseur;
	}

    // ///////////////////////////////////////////////////////////////
    // Cr�er ttes les �quipes possibles avec tous les joueurs � dispo
    // ///////////////////////////////////////////////////////////////
	function buildAllTeams()
	{
		$ind = 0;
		foreach($this->defenseurs as $def)
		{
			foreach($this->attaquants as $att)
			{
				if ($def != $att)
				{
					$this->equipes1[] = $def."-".$att;
					$this->equipes2[] = $def."-".$att;
				}
			}
		}
	}

    // ///////////////////////////////////////////////////////////////
    // Cr�er tous les matchs possibles avec ttes les �quipes � dispo
    // ///////////////////////////////////////////////////////////////
	function buildAllMatchs()
	{
        foreach($this->equipes1 as $eq1)
        {
        	foreach($this->equipes2 as $eq2)
        	{
        		// 2 m�mes �quipes ne peuvent pas jouer ensemble
        		if ($eq1 != $eq2)
        		{
        			$j_eq1 = explode("-", $eq1, 2);
        			$j_eq2 = explode("-", $eq2, 2);
        			// Les joueurs des 2 �quipes doivent �tre tous diff�rents
        			if (!(strstr($eq2, $j_eq1[0]) || strstr($eq2, $j_eq1[1])))
        			{
        				// On ne garde qu'une seule occurence d'un match et tous les joueurs jouent � des postes diff�rents
        				if (!(	isset($this->matchs[$eq2."/".$eq1]) ||
        						isset($this->matchs[$eq1."/".$j_eq2[1]."-".$j_eq2[0]]) ||
        						isset($this->matchs[$j_eq2[1]."-".$j_eq2[0]."/".$eq1]) ||
        						isset($this->matchs[$j_eq1[1]."-".$j_eq1[0]."/".$eq2]) ||
        						isset($this->matchs[$eq2."/".$j_eq1[1]."-".$j_eq1[0]])
        						))
        					$this->matchs[$eq1."/".$eq2]=$eq1."/".$eq2;
        			}
        		}
        	}
		}
        shuffle($this->matchs);
	}

    // ///////////////////////////////////////////////////////////////
    // Pond�ration des matchs en fonction des matchs d�j� jou�s par
    // par rapport aux matchs qu'ils restent � jouer.
    // Affection d'un coefficient et d'un handicap � chaque match.
    // Coefficient :
    // ///////////////////////////////////////////////////////////////
    function getPonderationMatchs()
    {
    	$coefficients = array();

    	// On r�cup�re le dernier match affect�, match de r�f�rence pour le choix du prochain match
    	$last_match_affected = end($this->matchs_affected);
    	$equipes = explode("/", $last_match_affected);
    	$j_eq1 = explode("-", $equipes[0]);
    	$j_eq2 = explode("-", $equipes[1]);

    	// On parcours les matchs restants en p�nalisants les matchs dans lesquels on retrouve les m�mes joueurs
    	// et en p�nalisant encore + si le joueur rejoue � la m�me place.
    	foreach($this->matchs as $m)
    	{
    		$coefficients[$m] = 0;

    		if (strstr($m, $j_eq1[0]))		$coefficients[$m]++; // Si joueur1 rejoue
    		if (strstr($m, $j_eq1[0]."-"))	$coefficients[$m]++; // Si joueur1 rejoue au m�me poste

    		if (strstr($m, $j_eq1[1]))		$coefficients[$m]++;
    		if (strstr($m, "-".$j_eq1[1]))	$coefficients[$m]++;

    		if (strstr($m, $j_eq2[0]))		$coefficients[$m]++;
    		if (strstr($m, $j_eq2[0]."-"))	$coefficients[$m]++;

    		if (strstr($m, $j_eq2[1]))		$coefficients[$m]++;
    		if (strstr($m, "-".$j_eq2[1]))	$coefficients[$m]++;

    		// Si on retrouve une �quipe avec les m�mes joueurs mais aux postes diff�rents, on p�nalise un peu
    		if (strstr($m, "-".$j_eq1[0]) && strstr($m, $j_eq1[1]."-")) $coefficients[$m] +=0.2;
    		if (strstr($m, "-".$j_eq2[0]) && strstr($m, $j_eq2[1]."-")) $coefficients[$m] +=0.2;
    	}

    	return $coefficients;
    }

    // ///////////////////////////////////////////////////////////////
    //
    // ///////////////////////////////////////////////////////////////
    function getHandicapMatchs()
    {
    	$handicaps = array();

    	// On parcours les matchs restants en p�nalisants les matchs dans lesquels on retrouve les m�mes joueurs
    	// et en p�nalisant encore + si le joueur rejoue � la m�me place.
    	foreach($this->matchs as $m)
    	{
    		$equipes = explode("/", $m);
    		$j_eq1 = explode("-", $equipes[0]);
    		$j_eq2 = explode("-", $equipes[1]);

    		$handicaps[$m]  = $this->handicap_def[$j_eq1[0]]+$this->handicap_def[$j_eq1[1]]+$this->handicap_att[$j_eq1[0]]+$this->handicap_att[$j_eq1[1]];
    		$handicaps[$m] += $this->handicap_def[$j_eq2[0]]+$this->handicap_def[$j_eq2[1]]+$this->handicap_att[$j_eq2[0]]+$this->handicap_att[$j_eq2[1]];

    		// On affine en essayant de privil�gier le rapport attaque/d�fense
    		$affinement = 0;
    		$affinement += abs($this->handicap_def[$j_eq1[0]]-$this->handicap_att[$j_eq1[0]]);
    		$affinement += abs($this->handicap_def[$j_eq1[1]]-$this->handicap_att[$j_eq1[1]]);
    		$affinement += abs($this->handicap_def[$j_eq2[0]]-$this->handicap_att[$j_eq2[0]]);
    		$affinement += abs($this->handicap_def[$j_eq2[1]]-$this->handicap_att[$j_eq2[1]]);

    		$handicaps[$m] += $affinement/10;
    	}

    	return $handicaps;
    }

    // ///////////////////////////////////////////////////////////////
    // Extraction du meilleur prochain match
    // ///////////////////////////////////////////////////////////////
    function extractBestMatch($coeffs, $handicaps)
    {
    	$min_coeff = 9999;
    	$min_hand  = 9999;
    	$pre_selected_matchs = array();
    	$selected_matchs = array();

    	// Recherche du coefficient le moins p�nalisant
    	foreach($coeffs as $c)
    		if ($c < $min_coeff) $min_coeff = $c;

    	// R�cup�ration des matchs les moins p�nalis�s
    	reset($coeffs);
    	while(list($cle, $val) = each($coeffs))
    		if ($val == $min_coeff) $pre_selected_matchs[] = $cle;

    	// Recherche du + petit handicap
    	while(list($cle, $val) = each($pre_selected_matchs))
    		if ($handicaps[$val] < $min_hand) $min_hand = $handicaps[$val];

    	// R�cup�ration des matchs les moins handicap�s
    	foreach($pre_selected_matchs as $m)
    		if ($handicaps[$m] == $min_hand) $selected_matchs[] = $m;

    	// Choix du match
    	return $selected_matchs[array_rand($selected_matchs)];
    }

    // ///////////////////////////////////////////////////////////////
    // Recherche du meilleur prochain match
    // ///////////////////////////////////////////////////////////////
    function getBestNextMatch()
    {
    	if (count($this->matchs_affected) == 0)
    		$ret = $this->matchs[array_rand($this->matchs)];
    	else
    	{
    		$coeffs    = $this->getPonderationMatchs();
    		$handicaps = $this->getHandicapMatchs();

    		$ret = $this->extractBestMatch($coeffs, $handicaps);
    	}

    	return $ret;
    }

    // ///////////////////////////////////////////////////////////////
    // Supprime un match dans une liste de matchs
    // ///////////////////////////////////////////////////////////////
    function deleteMatchFromList($match, $listeMatchs)
    {
    	$res_liste = array();

    	if (count($listeMatchs) == 0) return $res_liste;

    	while($tmp = array_shift($listeMatchs))
    	{
    		if ($tmp != $match)
    			$res_liste[] = $tmp;
    	}

    	return $res_liste;
    }

    // ///////////////////////////////////////////////////////////////
    // Cr�er la liste des matchs � jouer
    // ///////////////////////////////////////////////////////////////
	function getOut($in)
	{
		$item = "";
		$out = array();

		foreach($in as $j) $item .= "[".$j."]";

		foreach($this->joueurs as $j)
			if (!strstr($item, "[".$j."]")) $out[] = $j;

		return $out;
	}

	// Nomination des joueurs qui vont jouer le prochain match parmi ceux qui sont dehors
	function getEntrant($out, $nb_entrant)
	{
		if ($nb_entrant < 4)
		{
			$entrant = $out;
		}
		else
		{
		}

		return $entrant;
	}

	function getJoueursMinMatchsJoues($joueurs)
	{
		$min = 999;
		foreach($joueurs as $j)
		{
			$cumul = $this->handicap_def[$j] + $this->handicap_att[$j];
			$min = min($min, $cumul);
		}

		foreach($joueurs as $j)
		{
			if (($this->handicap_def[$j] + $this->handicap_att[$j]) == $min) {
				$selected[] = $j;
			}
		}

		return $selected;
	}

	// Nomination des joueurs qui vont rester sur le terrain pour jouer le prochain match
	function getRestant($in, $nb_entrant)
	{
		$restant = array();

        if ($nb_entrant < 4)
        {
			// Il faut retirer ceux qui ont jou� le + de matchs et ensuite ceux qui sont rest� le + longtemps d'affil� sur le terrain
			$this->getJoueursMinMatchsJoues($in);
			$i = 0;
			shuffle($in);
			while($i < $nb_entrant)
			{
				$restant[] = $in[$i];
				$i++;
			}

			// Il faut trouver le meilleur ordonnancement de l'�quipe
        }
        else
        {
			$restant = array();
        }

		return $restant;
	}

	// Composition du meillieur match possible
	function getBestMatch($entrant, $restant)
	{
		foreach($entrant as $j) $in[] = $j;
		foreach($restant as $j) $in[] = $j;
		shuffle($in);

		return $in;
	}

	function computeMatchs()
	{

		// Choix des 4 premiers joueurs au hasard
		shuffle($this->joueurs);

		// Composition des joueurs IN/OUT
		$in[] = $this->joueurs[0];
		$in[] = $this->joueurs[1];
		$in[] = $this->joueurs[2];
		$in[] = $this->joueurs[3];
		$out  = $this->getOut($in);

		// Nb in/out par match
		$nb_entrant = min(4, count($out));

		$j = 0;
        while ($j < $this->max_matchs)
        {
			$m = $in[0]."-".$in[1]."/".$in[2]."-".$in[3];
        	$this->matchs_affected[] = $m;

        	$this->handicap_def[$in[0]]++;
        	$this->handicap_def[$in[2]]++;
        	$this->handicap_att[$in[1]]++;
        	$this->handicap_att[$in[3]]++;

			// Choix des entrants parmis ceux qui �taient OUT
			$entrant = $this->getEntrant($out, $nb_entrant);

			// Choix des restants parmis ceux qui ont d�j� jou�
			$restant = $this->getRestant($in, 4-$nb_entrant);

			// Composition du meilleur match possible
			$in = $this->getBestMatch($entrant, $restant);

			// Composition des joueurs OUT
			$out = $this->getOut($in);

			$j++;
        }
	}

    // ///////////////////////////////////////////////////////////////
    // Cr�er la liste des matchs � jouer
    // ///////////////////////////////////////////////////////////////
	function computeMatchs2()
	{
        reset($this->matchs);
        while (count($this->matchs) != 0)
        {
        	$nxt_match = $this->getBestNextMatch();

        	$this->matchs_affected[] = $nxt_match;
        	$this->matchs = $this->deleteMatchFromList($nxt_match, $this->matchs);

        	$equipes = explode("/", $nxt_match);
        	$j_eq1 = explode("-", $equipes[0]);
        	$j_eq2 = explode("-", $equipes[1]);

        	$this->handicap_def[$j_eq1[0]]++;
        	$this->handicap_def[$j_eq2[0]]++;
        	$this->handicap_att[$j_eq1[1]]++;
        	$this->handicap_att[$j_eq2[1]]++;

        	if (count($this->matchs_affected) >= $this->max_matchs) break;
        }
	}

    // ///////////////////////////////////////////////////////////////
    // Cr�er des stats li�s aux matchs et aux joueurs
    // ///////////////////////////////////////////////////////////////
	function computeStats()
	{
        foreach($this->matchs_affected as $match)
        {
        	$equipes = explode("/", $match);
        	$joueurs_eq1 = explode("-", $equipes[0]);
        	$joueurs_eq2 = explode("-", $equipes[1]);

        	$this->match_par_joueur[$joueurs_eq1[0]]++;
        	$this->match_par_joueur[$joueurs_eq1[1]]++;
        	$this->match_par_joueur[$joueurs_eq2[0]]++;
        	$this->match_par_joueur[$joueurs_eq2[1]]++;

        	$this->match_par_defenseur[$joueurs_eq1[0]]++;
        	$this->match_par_defenseur[$joueurs_eq2[0]]++;
        	$this->match_par_attaquant[$joueurs_eq1[1]]++;
        	$this->match_par_attaquant[$joueurs_eq2[1]]++;
        }
	}

	function explodeMatch($match)
	{
		$joueurs = array();

		$equipes = explode("/", $match);
		$joueurs_eq1 = explode("-", $equipes[0]);
		$joueurs_eq2 = explode("-", $equipes[1]);

		$joueurs[0] = $joueurs_eq1[0];
		$joueurs[1] = $joueurs_eq1[1];
		$joueurs[2] = $joueurs_eq2[0];
		$joueurs[3] = $joueurs_eq2[1];

		return $joueurs;
	}

	function getNomJoueursInMatch($match)
	{
		$nom = array();

		$equipes = explode("/", $match);
		$joueurs_eq1 = explode("-", $equipes[0]);
		$joueurs_eq2 = explode("-", $equipes[1]);

		$nom[0] = $this->nom_joueurs[$joueurs_eq1[0]];
		$nom[1] = $this->nom_joueurs[$joueurs_eq1[1]];
		$nom[2] = $this->nom_joueurs[$joueurs_eq2[0]];
		$nom[3] = $this->nom_joueurs[$joueurs_eq2[1]];

		return $nom;
	}

	function getPseudosInMatch($match)
	{
		$nom = array();

		$equipes = explode("/", $match);
		$joueurs_eq1 = explode("-", $equipes[0]);
		$joueurs_eq2 = explode("-", $equipes[1]);

		$nom[0] = $this->pseudo_joueurs[$joueurs_eq1[0]];
		$nom[1] = $this->pseudo_joueurs[$joueurs_eq1[1]];
		$nom[2] = $this->pseudo_joueurs[$joueurs_eq2[0]];
		$nom[3] = $this->pseudo_joueurs[$joueurs_eq2[1]];

		return $nom;
	}

	function getJoueursOut($match)
	{
		$equipes = explode("/", $match);
		$joueurs_eq1 = explode("-", $equipes[0]);
		$joueurs_eq2 = explode("-", $equipes[1]);

		$joueurs_out = array();

		foreach($this->joueurs as $j)
			if ($j <> $joueurs_eq1[0] && $j <> $joueurs_eq1[1] && $j <> $joueurs_eq2[0] && $j <> $joueurs_eq2[1])
				$joueurs_out[] = $this->pseudo_joueurs[$j];

		$res = "";
		sort($joueurs_out);
		foreach($joueurs_out as $j) $res .= ($res == "" ? "" : ", ").$j;

		return "[".$res."]";
	}

	function getNomJoueur($id) {
		return $this->nom_joueurs[$id];
	}

	function getPseudoJoueur($id) {
		return $this->pseudo_joueurs[$id];
	}

	function getNbMatchs() {
		return count($this->matchs_affected);
	}

	function getEstimedTime() {
		$estimed = $this->getNbMatchs()*15;
		$heure  = floor($estimed / 60);
		$minute = $estimed % 60;
		return $heure."h".($minute < 10 ? "0" : "")."".$minute." minutes";
	}

	function getIndiceSatisfaction()
	{
		$min_match = 999;
		$max_match = 0;
		foreach($this->match_par_joueur as $nb)
		{
			if ($nb > $max_match) $max_match = $nb;
			if ($nb < $min_match) $min_match = $nb;
		}

		return ($max_match - $min_match);
	}
}

?>
