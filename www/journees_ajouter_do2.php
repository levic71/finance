<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

function getPlayersName($championnat)
{
	global $nom_des_joueurs, $pseudo_des_joueurs;

	$req = "SELECT id, nom, prenom, pseudo FROM jb_joueurs WHERE id_champ=".$championnat;
	$res = dbc::execSQL($req);

	if ($res)
	{
		while($row = mysql_fetch_array($res))
		{
			$players_name[$row['id']] = strlen($row['pseudo']) > 0 ? $row['pseudo'] : $row['nom']." ".$row['prenom'];
		}
	}

	mysql_free_result($res);

	return $players_name;
}

function getIdTeam($championnat, $defenseur, $attaquant)
{
	$req = "SELECT * FROM jb_equipes WHERE id_champ=".$championnat." AND joueurs='".$defenseur."|".$attaquant."'";
	$res = dbc::execSQL($req);
	$row = mysql_fetch_array($res);

	mysql_free_result($res);

	return $row['id'];
}

function insertMatch($championnat, $journee, $equipe1, $equipe2)
{
	$insert = "INSERT INTO jb_matchs (id_champ, id_journee, id_equipe1, id_equipe2) VALUES (".$championnat.", ".$journee.", ".$equipe1.", ".$equipe2.")";
	$res = dbc::execSQL($insert);
}

$db = dbc::connect();

$players_name = getPlayersName($sess_context->getRealChampionnatId());

$ses = new SQLEquipesServices($sess_context->getRealChampionnatId());

// Récupération des matchs à insérer dans la journée en cours
while(list($cle, $match) = each($HTTP_POST_VARS))
{
	if (strstr($cle, "match_sel"))
	{
		$equipe = explode("/", $match);
		$j_eq1  = explode("-", $equipe[0]);
		$j_eq2  = explode("-", $equipe[1]);

		$ses->checkTeam($players_name, $j_eq1[0], $j_eq1[1]);
		$ses->checkTeam($players_name, $j_eq2[0], $j_eq2[1]);

		$id_eq1 = getIdTeam($sess_context->getRealChampionnatId(), $j_eq1[0], $j_eq1[1]);
		$id_eq2 = getIdTeam($sess_context->getRealChampionnatId(), $j_eq2[0], $j_eq2[1]);

		insertMatch($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $id_eq1, $id_eq2);
	}
}

mysql_close($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect("matchs.php");

?>
