<?

function reformat($separateur, $chaine)
{
	// Reformatage des joueurs de la journée
	$tab = explode($separateur, $chaine);
	sort($tab);
	$res = "";
	foreach($tab as $elt) $res .= ($res == "" ? "" : $separateur).$elt;

	return $res;
}

function synchronize_journees($id_championnat, $type_championnat, $id_saison, $confirm = "no", $id_journee = "")
{
	$tab = array();

	// En attendant de corriger le pb
	if ($type_championnat == _TYPE_TOURNOI_) return $tab;

	// On récupère les ids de toutes les équipes
	$req = "SELECT * FROM jb_equipes WHERE id_champ=".$id_championnat;
	$res = dbc::execSQL($req);
	while($row = mysqli_fetch_array($res))
		$equipes[$row['id']] = $row['joueurs'];

	// On parcours toutes les journées ou la journee choisie
	$i = 1;
	$req = "SELECT * FROM jb_journees WHERE id_champ=".$id_saison." ".($id_journee == "" ? "" : "AND id=".$id_journee)." ORDER BY date ASC";
	$journee = dbc::execSQL($req);
	while($row_j = mysqli_fetch_array($journee))
	{
		unset($j2add);
		unset($e2add);
		unset($poule);

		$j2add = array();
		$e2add = array();
		$poule = array();

		// Init des poules pour les tournois
		if ($type_championnat == _TYPE_TOURNOI_)
		{
			for($y = 1; $y <= $row_j['tournoi_nb_poules']; $y++) $poule[$y] = "";
		}

		$status = "<img src=../images/ok.gif alt=\"OK\" title=\"OK\" border=0>";

		// On récupère les matchs réellement pour en déduire les joueurs et les équipes qui ont joués
	    $req = "SELECT * FROM jb_matchs WHERE id_champ=".$id_saison." AND id_journee=".$row_j['id'];
	    $res = dbc::execSQL($req);

	    // S'il n'y a aucun match pour une journée alors on continue sans rien faire
		if (mysqli_num_rows($res) == 0)
		{
			$status = "<img src=../images/ok.gif onmouseover=\"show_info_upright('<BR>Pas de matchs.<BR>', event);\" onmouseout=\"close_info();\" border=0>";
			$tab[] = array($i, "update journee id=".$row_j['id']." nom=".$row_j['nom']." du ".$row_j['date'], $status);
			$i++;
			continue;
		}

		// On parcours les matchs joués
	    while($row = mysqli_fetch_array($res))
	    {
			// Récupération des id des joueurs de l'équipe1
		    if (isset($equipes[$row['id_equipe1']]))
		    {
				$items = explode('|', $row['surleterrain1'] != "" ? $row['surleterrain1'] : $equipes[$row['id_equipe1']]);
				if (count($items) >= 2)
				{
				    $j2add[$items[0]] = $items[0];
				    $j2add[$items[1]] = $items[1];
				}
				// Récupération des id des équipes
				$e2add[$row['id_equipe1']] = $row['id_equipe1'];

				// Pour les tournoi, mémorisation de la poule de l'équipe
				if ($type_championnat == _TYPE_TOURNOI_)
				{
					$ex = explode('|', $row['niveau']);
					if ($ex[0] == "P") $poule[$ex[1]][$row['id_equipe1']] = $row['id_equipe1'];
				}
			}

			// Récupération des id des joueurs de l'équipe2
		    if (isset($equipes[$row['id_equipe2']]))
		    {
				$items = explode('|', $row['surleterrain2'] != "" ? $row['surleterrain2'] : $equipes[$row['id_equipe2']]);
				if (count($items) >= 2)
				{
				    $j2add[$items[0]] = $items[0];
				    $j2add[$items[1]] = $items[1];
				}

				// Récupération des id des équipes
				$e2add[$row['id_equipe2']] = $row['id_equipe2'];

				// Pour les tournoi, mémorisation de la poule de l'équipe
				if ($type_championnat == _TYPE_TOURNOI_)
				{
					$ex = explode('|', $row['niveau']);
					if ($ex[0] == "P") $poule[$ex[1]][$row['id_equipe2']] = $row['id_equipe2'];
				}
			}
	    }

		// Mise bout a bout des joueurs
		$joueurs2set = "";
		sort($j2add);
		foreach($j2add as $j) $joueurs2set .= ($joueurs2set == "" ? "" : ",").$j;

		// Mise bout a bout des equipes
		$equipes2set = "";
		if ($type_championnat == _TYPE_TOURNOI_)
		{
			for($y = 1; $y <= $row_j['tournoi_nb_poules']; $y++)
			{
				if (is_array($poule[$y]))
				{
					sort($poule[$y]);
					$str = "";
				    foreach($poule[$y] as $team)
						$str .= ($str == "" ? "" : ",").$team;
					$equipes2set .= ($equipes2set == "" ? "" : "|").$str;
				}
			}
		}
		else
		{
			sort($e2add);
			foreach($e2add as $e) $equipes2set .= ($equipes2set == "" ? "" : ",").$e;
		}

		// Reformatage des joueurs et des équipes de la journée (tri pour comparaison)
		$row_j['joueurs'] = reformat(",", $row_j['joueurs']);
		if ($type_championnat != _TYPE_TOURNOI_) $row_j['equipes'] = reformat(",", $row_j['equipes']);

		$status2 = "";
	    // Mise à jour des joueurs/equipes en fct des matchs joués
		if ($joueurs2set != $row_j['joueurs']) $status2 .= "<br>[changement joueurs]<BR>[old=".$row_j['joueurs']."]<BR>[new=".$joueurs2set."]";
		if ($equipes2set != $row_j['equipes']) $status2 .= "<br>[changement equipes]<BR>[old=".$row_j['equipes']."]<BR>[new=".$equipes2set."]";
 	    $update = "UPDATE jb_journees SET nom='".$i."', joueurs='".$joueurs2set."', equipes='".$equipes2set."' WHERE id=".$row_j['id'];
	    if ($confirm == "yes" && ($joueurs2set != $row_j['joueurs'] || $equipes2set != $row_j['equipes'])) $res = dbc::execSQL($update);

	    // Mise à jour des stats de la journée
	    $stats = new StatsJourneeBuilder($id_saison, $row_j['id'], $type_championnat);
	    if ($stats->getClassementPlayers() != $row_j['classement_joueurs']) $status2 .= "<br>[changement classement joueurs]<BR>[old=".$row_j['classement_joueurs']."]<BR>[new=".$stats->getClassementPlayers()."]";
	    if ($stats->getClassementTeams()   != $row_j['classement_equipes']) $status2 .= "<br>[changement classement équipes]<BR>[old=".$row_j['classement_equipes']."]<BR>[new=".$stats->getClassementTeams()."]";
		if ($confirm == "yes" && ($stats->getClassementPlayers() != $row_j['classement_joueurs'] || $stats->getClassementTeams() != $row_j['classement_equipes'])) $stats->SQLUpdateClassementJournee();

		// Mise des statistiques de poules pour les journées de tournoi
		if ($type_championnat == _TYPE_TOURNOI_)
		{
			for($k=1; $k <= $row_j['tournoi_nb_poules']; $k++)
			{
				$options_type_matchs = "P|".$k;
				$stats = new StatsJourneeBuilder($id_saison, $row_j['id'], $type_championnat, "AND niveau='".$options_type_matchs."'");
				if ($confirm == "yes") $stats->SQLUpdateClassementJourneeTournoi($options_type_matchs);
			}
		}

		if ($status2 != "") $status = "<img src=../images/nok.gif onmouseover=\"show_info_upright('".$status2."<BR>', event);\" onmouseout=\"close_info();\" border=0>";
		$tab[] = array($i, "update journee id=".$row_j['id']." nom=".$row_j['nom']." du ".$row_j['date'], $status);

		$i++;
	}

	if ($confirm == "yes") JKCache::delCache("../cache/stats_champ_".$id_championnat."_".$id_saison.".txt", "_FLUX_STATS_CHAMP_");

	return $tab;
}

?>
