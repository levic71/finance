<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
//
// Lancer par cron-job
//
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

	$req2 = "SELECT * FROM alertes WHERE user_id=".$row['id']." AND mail=0 AND date=CURDATE()";
	$res2 = dbc::execSql($req2);
	while ($row2 = mysqli_fetch_assoc($res2)) $notifs[] = $row2;

	if (count($notifs) == 0) continue;

	$mime_boundary = "----finance.jorkers.com----".md5(time());

	$mail_to     = $row['email'];
	$mail_header = "From: contact@jorkers.com\n";
	$mail_header .= "Mime-Version: 1.0\n";
	$mail_header .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
	$mail_header .= "X-Sender: <finance.jorkers.com>\n";
	$mail_header .= "X-Mailer: PHP/" . phpversion() . " \n" ;
	$mail_header .= "X-Priority: 3 (normal) \n";
	$mail_header .= "X-auth-smtp-user: contact@jorkers.com\n";
	$mail_header .= "X-abuse-contact: contact@jorkers.com\n";
	$mail_header .= "Importance: Normal\n";
	$mail_header .= "Reply-to: contact@jorkers.com\n";

	$mail_sujet  = "Alertes finances";
	
	$mail_corps  = "";
	$mail_corps .= "--$mime_boundary\n";
	$mail_corps .= "Content-Type: text/html; charset=ISO-8859-1\n";
	$mail_corps .= "Content-Transfer-Encoding: 8bit\n\n";

	$mail_corps .= "<html><body><table>";

	foreach($notifs as $key => $val) {
		$mail_corps .= "<tr>";
		$mail_corps .= "<td>".$val['actif']."</td>";
		$mail_corps .= "<td>".$val['type']."</td>";
		$mail_corps .= "<td><span data-tootik-conf=\"left multiline\"><a class=\"ui empty ".$val['couleur']." circular label\"></a></span></td>";
		$mail_corps .= "<td>".$val['sens']."</td>";
		$mail_corps .= "<td>".$val['couleur']."</td>";
		$mail_corps .= "<td>".$val['icone']."</td>";
		$mail_corps .= "<td>".sprintf(is_numeric($val['seuil']) ? "%.2f " : "%s ", $val['seuil'])."</td>";
		$mail_corps .= "</tr>";
	}

	$mail_corps .= "</table></body></html>";

		$res = mail($mail_to, $mail_sujet, $mail_corps, $mail_header);

	$req3 = "UPDATE alertes SET mail=1 WHERE user_id=".$row['id']." AND mail=0 AND date=CURDATE()";
	$res3 = dbc::execSql($req3);

}

?>