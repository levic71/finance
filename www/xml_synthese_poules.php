<?

include "../include/sess_context.php";

session_start();

$_SESSION['sess_context'] = new sess_context();
if (!isset($jb_langue))
{
	$jb_langue = "fr";
	setcookie("jb_langue", $jb_langue, time()+(3600*24*30*6));
}

if (!isset($id_championnat) || $id_championnat == "" || !isset($id_journee) || $id_journee == "")
{
	header("Content-type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"".sess_context::charset."\"?>\n";
	echo "<SYNTHESE CHAMPIONNAT_ID=\"-1\" CHAMPIONNAT_NOM=\"Championnat invalide\" SAISON_ID=\"-1\" SAISON_NOM=\"Championnat invalide\" URL=\"http://www.jorkers.com\">\n";
	echo "</SYNTHESE>\n";
	exit();
}

include "../include/constantes.php";
include "../include/toolbox.php";
include "../include/inc_db.php";
include "../include/cache_manager.php";
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
$sess_context->setJourneeId($id_journee);

if ($row)
{
//	$sgb = new StatsGlobalBuilder($row['saison_id'], $row['type']);
//	$sgb = JKCache::getCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", 24*60*60, "_FLUX_STATS_CHAMP_");

	// On récupère les infos de la journée
	$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
	$journee = $sjs->getJournee();
	
	$is_journee_alias = ($journee['id_journee_mere'] == "" || $journee['id_journee_mere'] == "0" ? false : true);

	if ($is_journee_alias)
	{
		$journee_mere = $sjs->getJournee($journee['id_journee_mere']);
		$id_journee_mere = $journee['id_journee_mere'];
		$nb_poules    = $journee_mere['tournoi_nb_poules'];
		$equipes_journee = $journee_mere['equipes'];
		$liste_poules = explode('|', $journee_mere['equipes']);
	}
	else
	{
		$nb_poules    = $journee['tournoi_nb_poules'];
		$equipes_journee = $journee['equipes'];
		$liste_poules = explode('|', $journee['equipes']);
	}
	
	// Création d'un filtre pour la répartition des equipes dans les poules
	$i = 1;
	foreach($liste_poules as $lst)
	{
		$tmp = explode(',', $lst);
		foreach($tmp as $team) $equipes_poules[$i."_".$team] = "";
		$i++;
	}

	// Récupération des classements des poules 
	$req = "SELECT * FROM jb_classement_poules WHERE id_champ=".$sess_context->getChampionnatId()." AND id_journee=".($is_journee_alias ? $id_journee_mere : $sess_context->getJourneeId());
	$res = dbc::execSql($req);
	while($stat_poule = mysql_fetch_array($res))
	{
		$niveau_poule = explode('|', $stat_poule['poule']);
		$classement_equipes[$niveau_poule[1]] = $stat_poule['classement_equipes'];
	}

	$req = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." ORDER BY nom ASC";
	$res = dbc::execSql($req);
	while($eq = mysql_fetch_array($res))
		$equipes_infos[$eq['id']] = $eq['nom'];

	header("Content-type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"".sess_context::charset."\"?>\n";
	echo "<SYNTHESE CHAMPIONNAT_ID=\"".$id_championnat."\" CHAMPIONNAT_NOM=\"".$row['championnat_nom']."\" SAISON_ID=\"".$row['saison_id']."\" SAISON_NOM=\"".$row['saison_nom']."\" URL=\"http://www.jorkers.com\">\n";

	while(list($cle, $classement) = each($classement_equipes))
	{
		echo "<POULE ID=\"".$cle."\">";
		$equipes = explode('|', $classement);
		foreach($equipes as $eq)
		{
			$st = new StatJourneeTeam();
			$st->init($eq);

			if (isset($equipes_poules[$cle."_".$st->id]))
			{
				echo "<EQUIPE NOM=\"".$equipes_infos[$st->id]."\"  POINTS=\"".$st->points."\" JOUES=\"".$st->matchs_joues."\" GAGNES=\"".$st->matchs_gagnes."\" NULS=\"".$st->matchs_nuls."\" PERDUS=\"".$st->matchs_perdus."\" DIFF=\"".$st->diff."\">";
				echo "</EQUIPE>";
			}
		}
//		echo "<![CDATA[".$classement."]]>";
		echo "</POULE>";
	}

	echo "</SYNTHESE>\n";
}

mysql_close($db);

?>
