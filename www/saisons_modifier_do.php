<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

// Changement status ancienne saison active
if ($active == 1)
{
	$update = "UPDATE jb_saisons SET active=0 WHERE id_champ=".$sess_context->getRealChampionnatId()." AND active=1;";
	$res = dbc::execSQL($update);
	$sess_context->setSaisonId($saison_id);
	$sess_context->setSaisonNom($nom);
}
else
{
	$sss = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $saison_id);
	$saison = $sss->getSaison();
	if ($saison['active'] == 1)
	{
		ToolBox::do_redirect("saisons.php?errno=2");
		exit(0);
	}
}

// Modification de la saison
if ($sess_context->getChampionnatType() == _TYPE_LIBRE_)
{
	$update = "UPDATE jb_saisons SET nom='".$nom."', active=".$active.", joueurs='".$selection."', equipes='' WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$saison_id;
	$res = dbc::execSQL($update);
}
else
{
	$joueurs = "";
	if ($selection != "")
	{
		$req = "SELECT * FROM jb_equipes WHERE ID IN (".$selection.")";
		$res = dbc::execSQL($req);
		while($row = mysql_fetch_array($res))
			$joueurs .= ($joueurs == "" ? "" : ",").$row['joueurs'];
	}

	$update = "UPDATE jb_saisons SET nom='".$nom."', active=".$active.", joueurs='".str_replace('|', ',', $joueurs)."', equipes='".$selection."' WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$saison_id;
	$res = dbc::execSQL($update);
}

$sess_context->setSaisons();


mysql_close($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect("saisons.php");

?>
