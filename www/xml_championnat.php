<?

include "../include/sess_context.php";

if (!isset($id_championnat) || $id_championnat == "")
{
	header("Content-type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"".sess_context::charset."\"?>\n";
	echo "<CHAMPIONNAT ID=\"-1\" NOM=\"Championnat invalide\" URL=\"http://www.jorkers.com\">\n";
	echo "</CHAMPIONNAT>\n";
	exit();
}

session_start();

$_SESSION['sess_context'] = new sess_context();
if (!isset($jb_langue)) setcookie("jb_langue", "fr", time()+(3600*24*30*6));

include "../include/constantes.php";
include "../include/toolbox.php";
include "../include/inc_db.php";
include "ManagerFXList.php";
include "StatsBuilder.php";
include "SQLServices.php";

$db = dbc::connect();

$scs = new SQLChampionnatsServices($id_championnat);
$row = $scs->getChampionnat();
header("Content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"".sess_context::charset."\"?>\n";
if ($row)
{
	echo "<CHAMPIONNAT ID=\"".$row['championnat_id']."\" NOM=\"".$row['championnat_nom']."\" URL=\"http://www.jorkers.com\" ";

	// Recherche de la saison active
	$saison = $scs->getSaisonActive();
	echo "SAISON_ID=\"".$saison['id']."\" SAISON_NOM=\"".$saison['nom']."\" ";
	
	// Recherche de la dernière journée
	$sjs = new SQLJourneesServices($saison['id'], -1);
	$journees = $sjs->getListeLast4Journees(date("Y-m-d"));
	if (count($journees) > 0)
	{
		while(list($cle, $val) = each($journees))
		{
			echo "LAST_JOURNEE=\"".$val['date']."\" ";
			break;
		}
	}
	else
		echo "LAST_JOURNEE=\"-\" ";
	
	// Recherche de la prochaine journée
	$journees = $sjs->getListeNext4Journees(date("Y-m-d"));
	if (count($journees) > 0)
	{
		while(list($cle, $val) = each($journees))
		{
			echo "NEXT_JOURNEE=\"".$val['date']."\" ";
			break;
		}
	}
	else
		echo "NEXT_JOURNEE=\"-\" ";

	echo "></CHAMPIONNAT>\n";
}
else
	echo "<CHAMPIONNAT>no championnat</CHAMPIONNAT>\n";

mysql_close($db);

?>
