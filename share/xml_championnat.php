<?

if (!isset($id_championnat) || $id_championnat == "" || !is_numeric($id_championnat))
{
	header("Content-type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"".sess_context::xml_charset."\"?>\n";
	echo "<CHAMPIONNAT ID=\"-1\" NOM=\"Championnat invalide\" URL=\"http://www.jorkers.com\">\n";
	echo "</CHAMPIONNAT>\n";
	exit();
}

include "../include/sess_context.php";
include "../include/cache_manager.php";

session_start();

session_register("sess_context");
if (!isset($jb_langue)) setcookie("jb_langue", "fr", time()+(3600*24*30*6));

include "../include/constantes.php";
include "../include/toolbox.php";
include "../include/inc_db.php";
include "../www/ManagerFXList.php";
include "../www/StatsBuilder.php";
include "../www/SQLServices.php";
include "../wrapper/wrapper_fcts.php";

$db = dbc::connect();

$scs = new SQLChampionnatsServices($id_championnat);
$championnat = $scs->getChampionnat();


header("Content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"".sess_context::xml_charset."\"?>\n";
echo "<JORKERS URL=\"http://www.jorkers.com\" TIMESTAMP=\"".mktime()."\" DATE=\"". date('l jS \of F Y h:i:s A')."\">\n";
if ($championnat)
{
	$championnat['login'] = "";
	$championnat['pwd']   = "";

	$ses = new SQLEquipesServices($championnat['championnat_id']);
	$sps = new SQLJoueursServices($championnat['championnat_id']);

	Toolbox::trackUser($championnat['championnat_id'], _TRACK_PDF_);

	echo "<CHAMPIONNAT ID=\"".$championnat['championnat_id']."\" NOM=\"".$championnat['championnat_nom']."\" LIEU=\"".$championnat['lieu']."\" DESCRIPTION=\"".$championnat['description']."\" TYPE=\"".$championnat['type']."\" GESTIONNAIRE=\"".$championnat['gestionnaire']."\" CREATION=\"".$championnat['dt_creation']."\" URL=\"http://www.jorkers.com/www/championnat_redirect.php?champ=".$championnat['championnat_id']."\">\n";

	echo "<SAISONS>\n";
	$req = "SELECT * FROM jb_saisons WHERE id_champ=".$championnat['championnat_id'];
	$res = dbc::execSql($req);
	while($saison = mysql_fetch_array($res))
	{
		echo "<SAISON ID=\"".$saison['id']."\" NOM=\"".$saison['nom']."\" ACTIVE=\"".$saison['active']."\" CREATION=\"".$saison['date_creation']."\"></SAISON>\n";
	}

	echo "</SAISONS>\n";


	// Recherche de la saison active
	$saison = $scs->getSaisonActive();
	$sjs = new SQLJourneesServices($saison['id'], -1);
	echo "<SAISON_ACTIVE ID=\"".$saison['id']."\" NOM=\"".$saison['nom']."\" ";

	// Recherche de la dernière journée
	$journees = $sjs->getListeLast4Journees(date("Y-m-d"));
	if (count($journees) > 0)
	{
		while(list($cle, $val) = each($journees))
		{
			echo "LAST_JOURNEE=\"".$val['date']."\" ";
			break;
		}
	}
	else
		echo "LAST_JOURNEE=\"-\" ";

	// Recherche de la prochaine journée
	$journees = $sjs->getListeNext4Journees(date("Y-m-d"));
	if (count($journees) > 0)
	{
		while(list($cle, $val) = each($journees))
		{
			echo "NEXT_JOURNEE=\"".$val['date']."\" ";
			break;
		}
	}
	else
		echo "NEXT_JOURNEE=\"-\" ";

	echo ">\n";



	echo "<EQUIPES>\n";
	foreach($ses->getListeEquipes() as $equipe)
	{
		echo "<EQUIPE ID=\"".$equipe['id']."\" EXTERNAL_ID=\"".$equipe['external_id']."\" NOM=\"".$equipe['nom']."\" NB_JOUEURS=\"".$equipe['nb_joueurs']."\" PHOTO=\"".($equipe['photo'] == "" ? "" : "http://www.jorkers.com/").str_replace("../", "", $equipe['photo'])."\">\n";
		echo "<JOUEURS>\n";
		$liste_joueurs = $sps->getListeJoueursByEquipes($equipe['id']);
		if ($liste_joueurs != "")
		{
			$tmp = explode(",", $liste_joueurs);
			foreach($tmp as $j)
			{
				$joueur = $sps->getJoueur($j);
				echo "<JOUEUR ID=\"".$joueur['id']."\" NOM=\"".$joueur['nom']."\" PRENOM=\"".$joueur['prenom']."\" NAISSANCE=\"".$joueur['dt_naissance']."\" PSEUDO=\"".$joueur['pseudo']."\" PHOTO=\"".($joueur['photo'] == "" ? "" : "http://www.jorkers.com/").str_replace("../", "", $joueur['photo'])."\" />\n";
			}
		}
		echo "</JOUEURS>\n";
		echo "</EQUIPE>\n";
	}
	echo "</EQUIPES>\n";



	echo "<CLASSEMENT>\n";

	$sgb = JKCache::getCache("../cache/stats_champ_".$championnat['championnat_id']."_".$saison['id'].".txt", 24*60*60, "_FLUX_STATS_CHAMP_");
	$best_teams_tournoi = $sgb->getBestTeamsByTournoiPoints();
	$best_teams_championnat = $sgb->getBestTeamsByPoints();

	if ($championnat['type'] == _TYPE_TOURNOI_)
		$fxlist = new FXListClassementGeneralTournoi($championnat['championnat_id'], $saison['id'], $best_teams_tournoi);

	if ($championnat['type'] == _TYPE_LIBRE_)
		$fxlist = new FXListStatsJoueurs($sgb);

	if ($championnat['type'] == _TYPE_CHAMPIONNAT_)
		$fxlist = new FXListStatsTeamsII($best_teams_championnat);

	$i = 1;
	foreach($fxlist->body->tab as $item)
	{
		if ($championnat['type'] == _TYPE_TOURNOI_ && $item['1'] == "X") continue;
		if ($championnat['type'] == _TYPE_LIBRE_ && $item['pseudo'] == "F") continue;

		$htmlcontent = "<RANG NUM=\"".$i."\" ";
		if ($championnat['type'] == _TYPE_TOURNOI_)
		{
		}
		if ($championnat['type'] == _TYPE_LIBRE_)
		{
			$htmlcontent .= "ID=\"".$item['id']."\" ";
			$htmlcontent .= "NOM=\"".$item['nom']."\" ";
			$htmlcontent .= "PRENOM=\"".$item['prenom']."\" ";
			$htmlcontent .= "PSEUDO=\"".$item['pseudo']."\" ";
			$htmlcontent .= "DATE_NAIS=\"".$item['dt_naissance']."\" ";
			$htmlcontent .= "PHOTO=\"".$item['photo']."\" ";
			$htmlcontent .= "PRESENCE=\"".$item['presence']."\" ";
			$htmlcontent .= "ETAT=\"".$item['etat']."\" ";
			$htmlcontent .= "MATCHS_JOUES=\"".$item['joues']."\" ";
			$htmlcontent .= "MATCHS_JOUES_ATTAQUANT=\"".$item['jouesA']."\" ";
			$htmlcontent .= "MATCHS_JOUES_DEFENSEUR=\"".$item['jouesD']."\" ";
			$htmlcontent .= "MATCHS_GAGNES=\"".$item['gagnes']."\" ";
			$htmlcontent .= "MATCHS_NULS=\"".$item['nuls']."\" ";
			$htmlcontent .= "MATCHS_PERDUS=\"".$item['perdus']."\" ";
			$htmlcontent .= "B_MARQUES=\"".$item['marquesA']."\" ";
			$htmlcontent .= "B_ENCAISSES=\"".$item['encaissesD']."\" ";
			$htmlcontent .= "FORME_PARTICIPATION=\"".$item['forme_participation']."\" ";
			$htmlcontent .= "FORME_LAST_JOURNEE=\"".$item['forme_joues']."\" ";
			$htmlcontent .= "AVG_BUT_MARQUES=\"".$item['moy_marquesA']."\" ";
			$htmlcontent .= "AVG_BUT_ENCAISSES=\"".$item['moy_encaissesD']."\" ";
			$htmlcontent .= "POURC_MATCHS_JOUES=\"".ereg_replace("<.*>", "", str_replace("</A>", "", $item['pourc_joues']))."\" ";
			$htmlcontent .= "POURC_MATCHS_GAGNES=\"".ereg_replace("<.*>", "", str_replace("</A>", "", $item['pourc_gagnes']))."\" ";
			$htmlcontent .= "POURC_MATCHS_NULS=\"".ereg_replace("<.*>", "", str_replace("</A>", "", $item['pourc_nuls']))."\" ";
			$htmlcontent .= "POURC_MATCHS_PERDUS=\"".ereg_replace("<.*>", "", str_replace("</A>", "", $item['pourc_perdus']))."\" ";
			$htmlcontent .= "PODIUM=\"".$item['podium']."\" ";
			$htmlcontent .= "POLIDOR=\"".$item['polidor']."\" ";
			$htmlcontent .= "FANNY_IN=\"".$item['fanny_in']."\" ";
			$htmlcontent .= "FANNY_OUT=\"".$item['fanny_out']."\" ";
			$htmlcontent .= "SETS_JOUES=\"".$item['sets_joues']."\" ";
			$htmlcontent .= "SETS_GAGNES=\"".$item['sets_gagnes']."\" ";
			$htmlcontent .= "SETS_PERDUS=\"".$item['sets_perdus']."\" ";
			$htmlcontent .= "SETS_NULS=\"".$item['sets_nuls']."\" ";
			$htmlcontent .= "SETS_DIFF=\"".$item['sets_diff']."\" ";
		}
		if ($championnat['type'] == _TYPE_CHAMPIONNAT_)
		{
			$htmlcontent .= "ID=\"".$item['id']."\" ";
			$htmlcontent .= "NOM=\"".ereg_replace("<.*>", "", str_replace("</A>", "", $item['nom']))."\" ";
			$htmlcontent .= "POINTS=\"".$item['points']."\" ";
			$htmlcontent .= "M_JOUES=\"".$item['matchs_joues']."\" ";
			$htmlcontent .= "M_GAGNES=\"".$item['matchs_gagnes']."\" ";
			$htmlcontent .= "M_NULS=\"".$item['matchs_nuls']."\" ";
			$htmlcontent .= "M_PERDUS=\"".$item['matchs_perdus']."\" ";
			$htmlcontent .= "S_JOUES=\"".$item['sets_joues']."\" ";
			$htmlcontent .= "S_GAGNES=\"".$item['sets_gagnes']."\" ";
			$htmlcontent .= "S_NULS=\"".$item['sets_nuls']."\" ";
			$htmlcontent .= "S_PERDUS=\"".$item['sets_perdus']."\" ";
			$htmlcontent .= "B_MARQUES=\"".$item['buts_marques']."\" ";
			$htmlcontent .= "B_ENCAISSES=\"".$item['buts_encaisses']."\" ";
			$htmlcontent .= "DIFF=\"".$item['diff']."\" ";
		}
		$htmlcontent .= "></RANG>\n";
		echo $htmlcontent;

		$i++;
	}

	echo "</CLASSEMENT>\n";




	echo "<JOURNEES>\n";
	foreach($sjs->getAllNoneAliasJournee() as $j)
	{
		$journee_jouee = 1;
		$matchs_txt = "";
		if ($championnat['type'] == _TYPE_CHAMPIONNAT_)
		{
			$sjs = new SQLJourneesServices($saison['id'], $j['id']);
			foreach($sjs->getAllMatchs() as $match)
			{
				$sm = new StatMatch($match['resultat'], $match['nbset']);
				$score = $sm->getScore();

				// Gestion des forfaits équipes
				if ($score == -1 || $score == -2)
					return ($score == -1 ? 2 : 1);

				// Gestion des matchs planifiés non encore joués
				$match_joue = 1;
				for($i = 0; $i < $match['nbset']; $i++)
				{
					if ((isset($score[$i][0]) && $score[$i][0] == 0) && (isset($score[$i][1]) && $score[$i][1] == 0))
						$match_joue = 0;
				}

				if ($match_joue == 0 && $match['match_joue'] == 0) $journee_jouee = 0;

				$matchs_txt .= "<MATCH ID=\"".$match['id']."\" JOUE=\"".$match['match_joue']."\" EQ1=\"".$match['id_equipe1']."\" EQ2=\"".$match['id_equipe2']."\" NB_SETS=\"".$match['nbset']."\" SCORE=\"".$match['resultat']."\" DATE=\"".$match['play_date']."\" HEURE=\"".$match['play_time']."\" />\n";
			}
		}
		else
		{
			$journee_jouee = 0;
		}

		echo "<JOURNEE ID=\"".$j['id']."\" JOURNEE_JOUEE=\"".$journee_jouee."\" NOM=\"".$j['nom']."\" DATE=\"".$j['date']."\" HEURE=\"".$j['heure']."\" EQUIPES=\"".$j['equipes']."\">\n";
		echo $matchs_txt;
		echo "</JOURNEE>\n";
	}
	echo "</JOURNEES>\n";




	echo "</SAISON_ACTIVE>\n";

	echo "</CHAMPIONNAT>\n";
}

echo "</JORKERS>\n";

mysql_close($db);

?>
