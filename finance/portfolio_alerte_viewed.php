<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
// //////////////////////////////////////////

// Alerte (id, date YYY-MM-JJ, user_id, actif, mail, lue, type, sens, couleur, icone, libelle, seuil)

include "include.php";

$db = dbc::connect();

// Pour l'instant que pour moi
$req = "SELECT * FROM users WHERE email='vmlf71@gmail.com'";
$res = dbc::execSql($req);
$row = mysqli_fetch_array($res);

$user_id = $row['id'];

$req = "UPDATE SET lue=1, sens='".$sens."', couleur='".$colr."', icone='".$icon."', seuil='".$seuil."' WHERE user_id=".$user_id." AND actif='' AND date='' AND type=''";
$res = dbc::execSql($req);

?>