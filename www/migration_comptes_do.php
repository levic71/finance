<?

include "../include/sess_context.php";

session_start();

header('Content-Type: text/html; charset='.sess_context::charset);

$jorkyball_redirect_exception = 1;

include "../www/common.php";
include "../include/inc_db.php";

$db = dbc::connect();

$nb_championnats = 0;
$nb_comptes = 0;
$nb_roles = 0;

$req = "SELECT * FROM jb_championnat ORDER BY dt_creation DESC;";
$res = dbc::execSQL($req);
while($row = mysqli_fetch_array($res)) {

	$sexe      = '1';
	$nom       = '';
	$prenom    = '';
	$pseudo    = $row['gestionnaire'];
	$taille    = '170';
	$poids     = '70';
	$email     = $row['email'];
	$date_nais = date('d/m/Y');
	$photo     = '';
	$login     = $row['login'];
	$pwd       = $row['pwd'];

	echo "<br />championnats: ".$row['dt_creation']."-".$row['nom']."[".$row['gestionnaire'].",".$row['login'].",".$row['pwd']."]";

	$select2 = "SELECT count(*) total FROM jb_users WHERE login='".$row['login']."'";
	$res2 = dbc::execSQL($select2);
	$row2 = mysqli_fetch_array($res2);

	if (true) {

		if ($row2['total'] == 0) {
			echo "<br />insert user";
			$insert = "INSERT INTO jb_users (sexe, taille, poids, photo, pseudo, nom, prenom, email, date_nais, login, pwd, status, date_inscription) VALUES (" . $sexe . ", " . $taille . ", " . $poids . ", '" . $photo . "', '" . $pseudo . "', '" . $nom . "', '" . $prenom . "', '" . $email . "', '" . $date_nais . "', '" . $login . "', '" . $pwd . "', 1, '" . date("Y") . "-" . date("m") . "-" . date("d") . "');";
			$res9 = dbc::execSQL($insert);
			$nb_comptes++;
		}

		$select3 = "SELECT * FROM jb_users WHERE login='" . $row['login'] . "'";
		$res3 = dbc::execSQL($select3);
		$row3 = mysqli_fetch_array($res3);

		$select4 = "SELECT count(*) total FROM jb_roles WHERE id_champ=" . $row['id'] . " AND id_user=" . $row3['id'];
		$res4 = dbc::execSQL($select4);
		$row4 = mysqli_fetch_array($res4);
		if ($row4['total'] == 0) {
			echo "<br />insert role";
			$insert = "INSERT INTO jb_roles (id_champ, id_user, role) VALUES (" . $row['id'] . ", " . $row3['id'] . ", '" . _ROLE_ADMIN_ . "');";
			$res5 = dbc::execSQL($insert);
			$nb_roles++;
		}

	}

	$nb_championnats++;
}

echo "<br /><br />".$nb_championnats." championnats, ".$nb_comptes." comptes, ".$nb_roles." roles";

mysql_close ($db);

?>
