<?

include "../include/cache_manager.php";

session_start();

include "common.php";
include "../include/constantes.php";
include "../include/toolbox.php";
include "../include/inc_db.php";
include "../www/SQLServices.php";
include "wrapper_fcts.php";

$db = dbc::connect();

$req = "SELECT count(*) total FROM jb_championnat WHERE actif = 1 AND demo = 0 AND entity = '_NATIF_' AND lat != '' ORDER BY nom";
$res = dbc::execSql($req);
$row = mysqli_fetch_array($res);

$flux = "var data = { 'count': ".$row['total'].", 'items': [\n";

$fichier = fopen("data/json_championnats.json", "w");
$pos = array();

if (flock($fichier, LOCK_EX))
{
	$i = 0;
	$req = "SELECT * FROM jb_championnat WHERE actif = 1 AND demo = 0 AND entity = '_NATIF_' AND lat != '' ORDER BY nom";
	$res = dbc::execSql($req);
	while($row = mysqli_fetch_array($res))
	{

		$delta = 0.00015;
		$d = $delta;

		while( true ) {

			if (!isset($pos[strval($row['lat']).'-'.strval($row['lng'])])) { break; }

			if (!isset($pos[floatval(strval($row['lat'])+$d).'-'.strval(floatval($row['lng'])+$d)])) { $row['lat']=floatval($row['lat'])+$d; $row['lng']=floatval($row['lng'])+$d; break; }
			if (!isset($pos[floatval(strval($row['lat'])+$d).'-'.strval(floatval($row['lng'])-$d)])) { $row['lat']=floatval($row['lat'])+$d; $row['lng']=floatval($row['lng'])-$d; break; }
			if (!isset($pos[floatval(strval($row['lat'])-$d).'-'.strval(floatval($row['lng'])-$d)])) { $row['lat']=floatval($row['lat'])-$d; $row['lng']=floatval($row['lng'])-$d; break; }
			if (!isset($pos[floatval(strval($row['lat'])-$d).'-'.strval(floatval($row['lng'])+$d)])) { $row['lat']=floatval($row['lat'])-$d; $row['lng']=floatval($row['lng'])+$d; break; }

			if (!isset($pos[floatval(strval($row['lat'])).'-'.strval(floatval($row['lng']-$d-$delta))])) { $row['lat']=floatval($row['lat']); $row['lng']=floatval($row['lng']-$d-$delta); break; }
			if (!isset($pos[floatval(strval($row['lat'])).'-'.strval(floatval($row['lng']+$d+$delta))])) { $row['lat']=floatval($row['lat']); $row['lng']=floatval($row['lng']+$d+$delta); break; }

			$d += $delta;
		}

		$pos[strval($row['lat']).'-'.strval($row['lng'])] = 1;

		if ($i > 0) $flux .= ",\n";
		$flux .= "\t{\n";
		$flux .= "\t\t'id': ".$row['id'].",\n";
		$flux .= "\t\t'dns': '".Wrapper::string2DNS($row['nom'])."',\n";
		$flux .= "\t\t'name': '".addslashes($row['nom'])."',\n";
		$flux .= "\t\t'type': '".$row['type']."',\n";
		$flux .= "\t\t'genre': '".$row['genre']."',\n";
		$flux .= "\t\t'sport': '".$row['type_sport']."',\n";
		$flux .= "\t\t'manager': '".addslashes($row['gestionnaire'])."',\n";
		$flux .= "\t\t'type_gestionnaire': '".$row['type_gestionnaire']."',\n";
		$flux .= "\t\t'address': '".addslashes($row['lieu'])."',\n";
		$flux .= "\t\t'lat': '".$row['lat']."',\n";
		$flux .= "\t\t'lng': '".$row['lng']."',\n";
		$flux .= "\t\t'zoom': '".$row['zoom']."'\n";
		$flux .= "\t}";

		$i++;
	}

	$flux .= "\n]}";

	fputs($fichier, $flux);

	flock($fichier, LOCK_UN);
}

fclose($fichier);


mysqli_close($db);

?>
