<?

include "../include/sess_context.php";

session_start();

$_SESSION['sess_context'] = new sess_context();
if (!isset($jb_langue))
{
	$jb_langue = "fr";
	setcookie("jb_langue", $jb_langue, time()+(3600*24*30*6));
}

if (!isset($id_championnat) || $id_championnat == "")
{
	header("Content-type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"".sess_context::charset."\"?>\n";
	echo "<CLASSEMENT CHAMPIONNAT_ID=\"-1\" CHAMPIONNAT_NOM=\"Championnat invalide\" SAISON_ID=\"-1\" SAISON_NOM=\"Championnat invalide\" URL=\"http://www.jorkers.com\">\n";
	echo "</CLASSEMENT>\n";
	exit();
}

include "../include/constantes.php";
include "../include/toolbox.php";
include "../include/inc_db.php";
include "../include/cache_manager.php";
include "ManagerFXList.php";
include "StatsBuilder.php";
include "SQLServices.php";

$sess_context = new sess_context();
$sess_context->setLangue($jb_langue);

include "../lang/nls_".$sess_context->getLangue().".php";

$db = dbc::connect();

$scs = new SQLChampionnatsServices($id_championnat);
$row = $scs->getChampionnat();

$row['login'] = "";
$row['pwd']   = "";
$sess_context->setChampionnat($row);

if ($row)
{
//	$sgb = new StatsGlobalBuilder($row['saison_id'], $row['type']);
	$sgb = JKCache::getCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", 24*60*60, "_FLUX_STATS_CHAMP_");

	$best_teams_tournoi = $sgb->getBestTeamsByTournoiPoints();
	$best_teams_championnat = $sgb->getBestTeamsByPoints();

	// /////////////////////////////////////////////////////////////////////////////////////////////
	// TABLEAU SYNTHESE EQUIPES
	// /////////////////////////////////////////////////////////////////////////////////////////////
	if ($row['type'] == _TYPE_TOURNOI_)
		$fxlist = new FXListClassementGeneralTournoi($row['championnat_id'], $row['saison_id'], $best_teams_tournoi);
	if ($row['type'] == _TYPE_CHAMPIONNAT_)
		$fxlist = new FXListStatsTeamsII($best_teams_championnat, true);
	if ($row['type'] == _TYPE_LIBRE_)
		$fxlist = new FXListStatsJoueurs($sgb);

	header("Content-type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"".sess_context::charset."\"?>\n";
	echo "<CLASSEMENT CHAMPIONNAT_ID=\"".$id_championnat."\" CHAMPIONNAT_NOM=\"".$row['championnat_nom']."\" SAISON_ID=\"".$row['saison_id']."\" SAISON_NOM=\"".$row['saison_nom']."\" URL=\"http://www.jorkers.com\">\n";
	$fxlist->getXmlClassement();
	// On récupère les vainqueurs des dernières journées
	if ($row['type'] == _TYPE_TOURNOI_)
	{
		$sss = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());
		echo $sess_context->getChampionnatId();
		$vainqueurs = $sss->getListeVainqueurs();
		if (count($vainqueurs) > 0)
		{
			echo "<VAINQUEURS>";
			foreach($vainqueurs as $item)
			{
				echo "<JOURNEE DATE=\"".$item['date']."\" EQUIPE=\"".$item['vainqueur']."\">";
				echo "</JOURNEE>";
			}
			echo "</VAINQUEURS>";
		}
	}
	echo "</CLASSEMENT>\n";
}

mysql_close($db);

?>
