<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

function getPlayersName($championnat, $regulier)
{
	$req = "SELECT * FROM jb_joueurs WHERE id_champ=".$championnat.($regulier == 1 ? " AND presence=1;" : "");
	$res = dbc::execSQL($req);

	if ($res)
	{
		while($row = mysql_fetch_array($res))
			$players_name[$row['id']] = strlen($row['pseudo']) > 0 ? $row['pseudo'] : $row['nom']." ".$row['prenom'];
	}

	mysql_free_result($res);

	return $players_name;
}

$db = dbc::connect();

// gestion de la piece jointe (photo)
$source   = ToolBox::get_global("photo");
$filename = ToolBox::purgeCaracteresWith("_", "../uploads/JOUEUR_".$sess_context->getRealChampionnatId()."_".$pseudo."_".ToolBox::get_global("photo_name"));

if ($source != "" && file_exists($source))
	$filename = ImageBox::imageSquareResize($source, $filename, 80, 400, 400);
else if (isset($source_tux) && $source_tux != "")
	$filename = $source_tux;
else
	$filename = "";

// Gestion de la date de naissance
$date_nais = ToolBox::date2mysqldate($date_nais);

$ses = new SQLEquipesServices($sess_context->getRealChampionnatId());

// Insertion du nouveau joueurs
$insert = "INSERT INTO jb_joueurs (id_champ, nom, prenom, dt_naissance, pseudo, photo, presence, email, tel1, tel2, etat) VALUES (".$sess_context->getRealChampionnatId().", '".$nom."', '".$prenom."', '".$date_nais."', '".$pseudo."', '".$filename."', ".$presence.", '".$email."', '".$tel1."', '".$tel2."', ".$etat.");";
$res = dbc::execSQL($insert);

// Récuparation de l'id du joueur
$sjs = new SQLJoueursServices($sess_context->getRealChampionnatId());
$joueur = $sjs->getJoueurByPseudo($pseudo);
$last_id_insert = $joueur['id'];

// Mise à jour de la saison
$sss = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());
$saison = $sss->getSaison();
$saison['joueurs'] .= ($saison['joueurs'] == "" ? "" : ",").$joueur['id'];
$update = "UPDATE jb_saisons SET joueurs='".$saison['joueurs']."' WHERE id=".$saison['id'];
$res = dbc::execSQL($update);

// Création des équipes si souhaité
if ($auto_create_team != 2)
{
	// On récupère les noms+id des joueurs
	$players_name = getPlayersName($sess_context->getRealChampionnatId(), $auto_create_team);

	// On créé automatiquement toutes les équipes avec ce nouveau joueurs
	while(list($cle, $match) = each($players_name))
	{
	    if ($cle != $last_id_insert)
	    {
	        $ses->checkTeam($players_name, $cle, $last_id_insert);
	        $ses->checkTeam($players_name, $last_id_insert, $cle);
	    }
	}
}

mysql_close($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect("joueurs.php");

?>
