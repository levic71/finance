<html>
<body>

<?

include "../include/inc_db.php";
$db = dbc::connect();

$i = 0;
$mail_corps = "";
$select = "SELECT c.id, c.nom championnat, s.nom saison, j.nom journee, j.date FROM `jb_saisons` s, `jb_championnat` c, `jb_journees` j WHERE pronostic=1 and c.id=s.id_champ and j.id_champ = s.id and (TO_DAYS(j.date)-TO_DAYS(now())) = 0";
$res = dbc::execSQL($select);
while ($row = mysql_fetch_array($res))
{
	$mail_corps .= "\nMettre à jour => ".$row['championnat'];
	$i++;
}
$mail_corps .= "\n\n\n<a href=\"http://www.jorkers.com/www/parseur.php\">Accès au module import</a>";

if ($i > 0)
{
	$mail_sujet  = "Jorkers alerte";
	$msg_email = "contact@jorkers.com";

	$Buffer = "From: [Jorkers.com] Jorkers.com <".$msg_email.">\n";
	$Buffer.= "MIME-Version: 1.0\n";
	$Buffer.= "X-Sender: <".$msg_email.">\n";
	$Buffer.= "X-Mailer: PHP/".phpversion()."\n";
	$Buffer.= "X-Priority: 1\n";
	$Buffer.= "Return-Path: <".$msg_email.">\n";
	$Buffer.= "Content-Type: text/html; charset=iso-8859-1\n";

	$res = @mail("victor.ferreira@laposte.net",  stripslashes($mail_sujet), nl2br(stripslashes($mail_corps)), $Buffer);

	echo "un message envoyé ...".$res;
}
else
{
	echo "pas de message envoyé ...";
}

?>