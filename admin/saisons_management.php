<?

include "../include/constantes.php";
include "../include/toolbox.php";
include "../include/inc_db.php";

$db = dbc::connect();

// On parcours tous les championnats pour ajouter une saison par défaut
$i = 0;
$req = "SELECT * FROM jb_championnat";
$res = dbc::execSQL($req);
while($row = mysql_fetch_array($res))
{
	if ($row['type'] == _TYPE_LIBRE_) {
		$joueurs = "";
		$select = "SELECT * FROM jb_joueurs WHERE id_champ=".$row['id'];
		$res2 = dbc::execSQL($select);
		while($j = mysql_fetch_array($res2))
			$joueurs .= ($joueurs == "" ? "" : ",").$j['id'];
			
		$insert = "INSERT INTO jb_saisons (id, id_champ, nom, date_creation, active, joueurs, equipes) VALUES (".$row['id'].", ".$row['id'].", 'Saison 2003-2004', '".$row['dt_creation']."', 1, '".$joueurs."', '');";
		$res2 = dbc::execSQL($insert);
	}
	else if ($row['type'] == _TYPE_CHAMPIONNAT_) {
		$equipes = "";
		$select = "SELECT * FROM jb_equipes WHERE id_champ=".$row['id'];
		$res2 = dbc::execSQL($select);
		while($e = mysql_fetch_array($res2))
			$equipes .= ($equipes == "" ? "" : ",").$e['id'];
			
		$insert = "INSERT INTO jb_saisons (id, id_champ, nom, date_creation, active, joueurs, equipes) VALUES (".$row['id'].", ".$row['id'].", 'Saison 2003-2004', '".$row['dt_creation']."', 1, '', '".$equipes."');";
		$res2 = dbc::execSQL($insert);
	}
	else if ($row['type'] == _TYPE_TOURNOI_) {
		$equipes = "";
		$select = "SELECT * FROM jb_equipes WHERE id_champ=".$row['id'];
		$res2 = dbc::execSQL($select);
		while($e = mysql_fetch_array($res2))
			$equipes .= ($equipes == "" ? "" : ",").$e['id'];
			
		$insert = "INSERT INTO jb_saisons (id, id_champ, nom, date_creation, active, joueurs, equipes) VALUES (".$row['id'].", ".$row['id'].", 'Saison 2003-2004', '".$row['dt_creation']."', 1, '', '".$equipes."');";
		$res2 = dbc::execSQL($insert);
	}
	$i++;
}

mysql_close($db);

?>

OK