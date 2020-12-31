<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "StatsBuilder.php";

$menu = new menu("full_access");

$db = dbc::connect();

// Vérification équipe pas déjà créée
if ($selection != "")
{
	$select = "SELECT count(*) total FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." AND joueurs='".$selection."'";
	$res = dbc::execSQL($select);
	$row = mysql_fetch_array($res);
	if ($row['total'] > 0) ToolBox::do_redirect("equipes_ajouter.php?error=1");
}

// Vérification nom équipe unique
$select = "SELECT count(*) total FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." AND nom LIKE '%".$nom."%'";
$res = dbc::execSQL($select);
$row = mysql_fetch_array($res);
if ($row['total'] > 0) ToolBox::do_redirect("equipes_ajouter.php?error=2");

// gestion de la piece jointe (photo)
$source   = ToolBox::get_global("photo");
$filename = ToolBox::purgeCaracteresWith("_", "../uploads/EQUIPE_".$sess_context->getChampionnatId()."_".$nom."_".ToolBox::get_global("photo_name"));
if ($source != "" && file_exists($source))
	$filename = ImageBox::imageSquareResize($source, $filename, 80, 400, 400);
else
	$filename = "";

$insert = "INSERT INTO jb_equipes (id_champ, nom, photo, commentaire, joueurs, nb_joueurs) VALUES (".$sess_context->getRealChampionnatId().", '".$nom."', '".$filename."', '".$commentaire."', '".$selection."', ".$nb_selection.");";
$res = dbc::execSQL($insert);

// Pour les tournois et les championnats, ajouter l'équipe à la saison en cours
if ($sess_context->getChampionnatType() != _TYPE_LIBRE_)
{
	$select = "SELECT * FROM jb_equipes WHERE nom='".$nom."' AND id_champ=".$sess_context->getRealChampionnatId();
	$res = dbc::execSQL($select);
	if ($eq = mysql_fetch_array($res))
	{
    	$select = "SELECT * FROM jb_saisons WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$sess_context->getChampionnatId();
		$res = dbc::execSQL($select);
		if ($row = mysql_fetch_array($res))
    	{
			$equipes = $row['equipes'].($row['equipes'] == "" ? "" : ",").$eq['id'];
			$update  = "UPDATE jb_saisons SET equipes='".$equipes."' WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$sess_context->getChampionnatId();
			$res = dbc::execSQL($update);
		}
	}
}

mysql_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect("equipes.php");

?>
