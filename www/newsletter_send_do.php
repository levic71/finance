<?

include "../include/inc_db.php";

$db = dbc::connect();

$sets = array();

if (true)
{
	$sets["contact@jorkers.com"] = "contact@jorkers.com";
}
else
{
	$select = "SELECT distinct(email) FROM jb_email";
	$res = dbc::execSQL($select);
	while($row = mysql_fetch_array($res))
		$sets[$row['email']] = $row['email'];
}


// /////////////////////////////////////////////////////////////////////////////
// Gestion de l'image incrustée
// /////////////////////////////////////////////////////////////////////////////
// on génère une frontière
$boundary = '-----=' . md5( uniqid ( rand() ) );
// on génère un identifiant aléatoire pour le fichier
$file_id  = md5( uniqid ( rand() ) ) . $_SERVER['SERVER_NAME'];

// on va maintenant lire le fichier et l'encoder
$path = '../images/jorkers_signature.jpg';
$fp = fopen($path, 'rb');
$content = fread($fp, filesize($path));
fclose($fp);
$content_encode = chunk_split(base64_encode($content));
// /////////////////////////////////////////////////////////////////////////////


// Constitution du message
$mail_sujet  = "Foot 2x2 championnat";

$message  = "Ceci est un message au format MIME 1.0 multipart/mixed.\n\n";
$message .= "--" . $boundary . "\n";
$message .= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
$message .= "Content-Transfer-Encoding: 8bit\n\n";
$message .= "
<html>
<body>
Bonjour,<br /><br />
<p>
Venez découvrir le Jorkers.com pour gérer gratuitement vos championnats et tournois.<br>
Simples joueurs ou gérants, ce site vous permettra de saissir des matchs et réaliser des statistiques automatiquement, le classement est d'ailleurs affichable en temps réel sur vos propres sites !!!<br /><br />
N'hésitez pas, venez faire un tour.<br /><br />
@+
</p>
<div align=\"center\"><a href=\"http://www.jorkers.com\"><img src=\"cid:$file_id\" border=\"0\"></a></div>
</body>
</html>
";
$message .= "\n\n";
$message .= "--" . $boundary . "\n";
$message .= "Content-Type: image/jpg; name=\"../images/jorkers_signature.jpg\"\n";
$message .= "Content-Transfer-Encoding: base64\n";
$message .= "Content-ID: <$file_id>\n\n";
$message .= $content_encode . "\n";
$message .= "\n\n";
$message .= "--" . $boundary . "--\n";

$buffer = "From: \"Jorkers.com\"<contact@jorkers.com>\n";
$buffer.= "Reply-To: Jorkers.com <contact@jorkers.com>\n";
$buffer.= "MIME-Version: 1.0\n";
$buffer.= "X-Sender: <contact@jorkers.com>\n";
$buffer.= "X-Mailer: PHP/".phpversion()."\n";
$buffer.= "X-Priority: 1\n";
//$buffer.= "Content-Type: text/html; charset=iso-8859-1\n";
$buffer.= "Content-Type: multipart/related; boundary=\"$boundary\"";
//$buffer.= "Content-Type: text/html; boundary=\"$boundary\"";

?>

<html>
<body>

<?

// On envoit un mail aux personnes qui souhaitent être informées de ce nouveau msg
foreach($sets as $e)
{
	if ($e != "")
	{
		$res = mail($e, stripslashes($mail_sujet), $message, $buffer);
		echo "send2 : ".$e;
	}
}

mysql_close ($db);

?>

</body>
</html>
