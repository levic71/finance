<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

// Vérification nom équipe unique
$select = "SELECT count(*) total FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." AND nom='".$nom."' AND id <> ".$id_equipe;
$res = dbc::execSQL($select);
$row = mysql_fetch_array($res);
if ($row['total'] > 0) ToolBox::do_redirect("equipes_ajouter.php?error=2&pkeys_where=".$pkeys_where);

// Upload de l'image si c'est nécessaire
$upload 	= 0;
$source   	= ToolBox::get_global("photo");
$filename 	= ToolBox::purgeCaracteresWith("_", "../uploads/EQUIPE_".$sess_context->getChampionnatId()."_".$nom."_".ToolBox::get_global("photo_name"));
if ($source != "" && file_exists($source))
{
	// Récupération des infos de l'ancienne image pour la supprimer
	$ses = new SQLEquipesServices($sess_context->getRealChampionnatId());
	$equipe = $ses->getEquipe($id_equipe);
	if ($equipe['photo'] != "" && file_exists($equipe['photo'])) unlink($equipe['photo']);

	$filename = ImageBox::imageSquareResize($source, $filename, 80, 400, 400);
	$upload = 1;
}

if ($selection == "")
	$nb_joueurs = 0;
else
{
	$tab = explode('|', $selection);
	$nb_joueurs = count($tab);
}

// Modification du joueur
if ($upload == 1)
	$update = "UPDATE jb_equipes SET nom='".$nom."', joueurs='".$selection."', nb_joueurs=".$nb_joueurs.", photo='".$filename."', commentaire='".$commentaire."' WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$id_equipe;
else
	$update = "UPDATE jb_equipes SET nom='".$nom."', joueurs='".$selection."', nb_joueurs=".$nb_joueurs.", commentaire='".$commentaire."' WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$id_equipe;
$res = dbc::execSQL($update);

mysql_close($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect("equipes.php");

?>
