<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

function getPlayersName($championnat)
{
	global $nom_des_joueurs, $pseudo_des_joueurs;
	
	$players_name = array();

	$req = "SELECT id, nom, prenom, pseudo FROM jb_joueurs WHERE id_champ=".$championnat;
	$res = dbc::execSQL($req);

	if ($res)
	{
		while($row = mysql_fetch_array($res))
			$players_name[$row['id']] = strlen($row['pseudo']) > 0 ? $row['pseudo'] : $row['nom']." ".$row['prenom'];
	}

	mysql_free_result($res);

	return $players_name;
}

function createTeams($championat, $noms, $j1, $j2)
{
	global $ses;
	
	reset($j1);
	while(list($cle1, $id1) = each($j1))
	{
	    reset($j2);
	    while(list($cle2, $id2) = each($j2))
	    	if ($id1 != $id2) $ses->checkTeam($noms, $id1, $id2);
	}
}

$db = dbc::connect();

$players_name = getPlayersName($sess_context->getRealChampionnatId());

$ses = new SQLEquipesServices($sess_context->getRealChampionnatId());

// Si on récupère 'joueur_selected', alors on ne créé que les équipes concernant ce joueur
if (isset($joueur_selected))
{
	$j1 = array("0" => $joueur_selected);
	$j2 = array_keys($players_name);
	$j2[] = $joueur_selected;
	createTeams($sess_context->getRealChampionnatId(), $players_name, $j1, $j2);
	createTeams($sess_context->getRealChampionnatId(), $players_name, $j2, $j1);
}
else
{
	$j1 = array_keys($players_name);
	$j2 = array_keys($players_name);
	createTeams($sess_context->getRealChampionnatId(), $players_name, $j1, $j2);
}

mysql_close($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect("equipes.php");

?>
