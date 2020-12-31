<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

function getPlayersName($championnat, &$last_id_insert)
{
    global $nom, $prenom, $pseudo, $presence;

	$req = "SELECT * FROM jb_joueurs WHERE id_champ=".$championnat;
	$res = dbc::execSQL($req);

	if ($res)
	{
		while($row = mysql_fetch_array($res))
		{
			$players_name[$row['id']] = strlen($row['pseudo']) > 0 ? $row['pseudo'] : $row['nom']." ".$row['prenom'];
            if ($row['nom'] == $nom && $row['prenom'] == $prenom && $row['pseudo'] == $pseudo && $row['presence'] == $presence)
               $last_id_insert = $row['id'];
		}
	}

	mysql_free_result($res);

	return $players_name;
}

$db = dbc::connect();

// Upload de l'image si c'est nécessaire
$upload = 0;
$source   = ToolBox::get_global("photo");
$filename = ToolBox::purgeCaracteresWith("_", "../uploads/JOUEUR_".$sess_context->getRealChampionnatId()."_".$pseudo."_".ToolBox::get_global("photo_name"));
if ($source != "" && file_exists($source))
{
	// Récupération des infos de l'ancienne image pour la supprimer
	$sjs = new SQLJoueursServices($sess_context->getRealChampionnatId());
	$joueur = $sjs->getJoueur($id_joueur);
	if ($joueur['photo'] != "" && file_exists($joueur['photo'])) unlink($joueur['photo']);

	$filename = ImageBox::imageSquareResize($source, $filename, 80, 400, 400);
	$upload = 1;
}
else if (isset($source_tux) && $source_tux != "")
{
	$upload = 1;
	$filename = $source_tux;
}

$date_nais = ToolBox::date2mysqldate($date_nais);

// Modification du joueur
if ($upload == 1)
	$update = "UPDATE jb_joueurs SET etat=".$etat.", nom='".$nom."', prenom='".$prenom."', dt_naissance='".$date_nais."', pseudo='".$pseudo."', photo='".$filename."', presence=".$presence.", email='".$email."', tel1='".$tel1."', tel2='".$tel2."' WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$id_joueur;
else
	$update = "UPDATE jb_joueurs SET etat=".$etat.", nom='".$nom."', prenom='".$prenom."', dt_naissance='".$date_nais."', pseudo='".$pseudo."', presence=".$presence.", email='".$email."', tel1='".$tel1."', tel2='".$tel2."' WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$id_joueur;
$res = dbc::execSQL($update);

// On récupère les noms+id des joueurs
$players_name = getPlayersName($sess_context->getRealChampionnatId(), $last_id_insert);

// Si le pseudo du joueur change, il faut changer le pseudo des équipes
if ($old_pseudo != $pseudo)
{
	$select = "SELECT * FROM jb_equipes WHERE (joueurs LIKE '".$id_joueur."|%' AND nom LIKE '".$old_pseudo."-%') OR (joueurs LIKE '%|".$id_joueur."|%' AND nom LIKE '%-".$old_pseudo."')";
	$res = dbc::execSQL($select);
	while($row = mysql_fetch_array($res))
	{
		$item = explode('|', $row['joueurs']);
		$defenseur = $item[0];
		$attaquant = $item[1];

		// On ne change que si le nom de l'équipe n'a pas été créé automatiquement. S'il y a eu personnalisation du
		// nom de l'équipe, alors on garde l'ancien nom
		if ($defenseur == $id_joueur && $row['nom'] == $old_pseudo."-".$players_name[$attaquant])
		{
			$update = "UPDATE jb_equipes set nom='".$pseudo."-".$players_name[$attaquant]."' WHERE id=".$row['id']." AND id_champ=".$sess_context->getRealChampionnatId();
			$res2 = dbc::execSQL($update);
		}
		if ($attaquant == $id_joueur && $row['nom'] == $players_name[$defenseur]."-".$old_pseudo)
		{
			$update = "UPDATE jb_equipes set nom='".$players_name[$defenseur]."-".$pseudo."' WHERE id=".$row['id']." AND id_champ=".$sess_context->getRealChampionnatId();
			$res3 = dbc::execSQL($update);
		}
	}
}

mysql_close($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect("joueurs.php");

?>
