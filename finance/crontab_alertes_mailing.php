<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
// //////////////////////////////////////////

include "include.php";
include "indicators.php";
include "googlesheet/sheet.php";

ini_set('max_execution_time', '300'); //300 seconds = 5 minutes

// Overwrite include value
$dbg = false;

if (!is_dir("cache/")) mkdir("cache/");

$db = dbc::connect();

// On récupère les portefeuilles de l'utilisateur
$req = "SELECT * FROM users";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

	$req = "SELECT * FROM alertes WHERE user_id=".$row['id']." AND mail=0 AND date=CURDATE()";
	$res = dbc::execSql($req);
	while ($row = mysqli_fetch_assoc($res)) $notifs[] = $row;

	if (count($notifs) == 0) continue;

	$mail_corps  = "";
	foreach($notifs as $key => $val)
		$mail_corps .= $val['actif'].':'.$val['type'].':'.sprintf(is_numeric($val['seuil']) ? "%.2f " : "%s ", $val['seuil']).'\n';

	$mail_to     = $row['email'];
	$mail_sujet  = "Alertes finances";
	$mail_header = "From: contact@jorkers.com";
	$res = mail($mail_to, $mail_sujet, $mail_corps, $mail_header);

	$req = "UPDATE alertes SET mail=1 WHERE user_id=".$row['id']." AND mail=0 AND date=CURDATE()";
	$res = dbc::execSql($req);

}

?>