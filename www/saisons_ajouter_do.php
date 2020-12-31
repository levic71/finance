<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "StatsBuilder.php";

$menu = new menu("full_access");

$db = dbc::connect();

// Changement status ancienne saison active
if ($active == 1)
{
	$update = "UPDATE jb_saisons SET active=0 WHERE id_champ=".$sess_context->getRealChampionnatId()." AND active=1;";
	$res = dbc::execSQL($update);
}

if ($sess_context->getChampionnatType() == _TYPE_LIBRE_)
{
	$insert = "INSERT INTO jb_saisons (id_champ, nom, date_creation, active, joueurs, equipes) VALUES (".$sess_context->getRealChampionnatId().", '".$nom."', '".date("Y")."-".date("m")."-".date("d")."', '".$active."', '".$selection."', '');";
	$res = dbc::execSQL($insert);
}
else
{
	$joueurs = "";
	if ($selection != "")
	{
		$req = "SELECT * FROM jb_equipes WHERE ID IN (".$selection.")";
		$res = dbc::execSQL($req);
		while($row = mysql_fetch_array($res))
		{
			if ($row['nb_joueurs'] > 0)
				$joueurs .= ($joueurs == "" ? "" : ",").$row['joueurs'];
		}
	}

	$insert = "INSERT INTO jb_saisons (id_champ, nom, date_creation, active, joueurs, equipes) VALUES (".$sess_context->getRealChampionnatId().", '".$nom."', '".date("Y")."-".date("m")."-".date("d")."', '".$active."', '".str_replace('|', ',', $joueurs)."', '".$selection."');";
	$res = dbc::execSQL($insert);
}

if ($active == 1)
{
	$scs = new SQLChampionnatsServices($sess_context->getRealChampionnatId());
	$saison = $scs->getSaisonActive();
	$sess_context->setSaisonId($saison['id']);
	$sess_context->setSaisonNom($saison['nom']);
	$sess_context->setSaisons();
}

mysql_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect("saisons.php");

?>
