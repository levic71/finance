<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";
include "../www/StatsBuilder.php";
include "../www/journees_synchronisation.php";

$db = dbc::connect();

$req = "SELECT * FROM jb_championnat";
$res = dbc::execSQL($req);
while($c = mysql_fetch_array($res))
{
	$req2 = "SELECT * FROM jb_saisons WHERE id_champ=".$c['id'].";";
	$res2 = dbc::execSQL($req2);
	while($saison = mysql_fetch_array($res2))
	{
		$tab = synchronize_journees($c['id'], $c['type'], $saison['id'], "yes");
		echo "synchronize_journees(".$c['id'].", ".$saison['id'].", ".$c['type'].", \"yes\")<BR>";
	}
}

?>
