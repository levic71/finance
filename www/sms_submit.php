<?

include "../include/sess_context.php";

session_start();

$jorkyball_redirect_exception = 1;

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

if ($sms_pseudo == "" || $sms_msg == "") ToolBox::do_redirect("sms.php?sms_pseudo=".$sms_pseudo."&sms_msg=".$sms_msg."&sms_jour=".$sms_jour."&sms_heure=".$sms_heure."&sms_minute=".$sms_minute."&sms_invitation=".$sms_invitation."&errno=4");

if (!$sess_context->isSuperUser())
{
	if ($_SESSION['antispam'] != $sms_controle) ToolBox::do_redirect("sms.php?sms_pseudo=".$sms_pseudo."&sms_msg=".$sms_msg."&sms_jour=".$sms_jour."&sms_heure=".$sms_heure."&sms_minute=".$sms_minute."&sms_invitation=".$sms_invitation."&errno=1");
}

// Gestion de la date
if ($sms_jour != "")
{
	$item = explode('/', $sms_jour);
	$my_sms_jour = $item[2]."-".$item[1]."-".$item[0];
}

$id_zone = isset($id_zone) ? $id_zone : 0;
$ip = $_SERVER["REMOTE_ADDR"];
$my_sms_heure  = ($sms_heure  < 9 ? "0" : "").$sms_heure;
$my_sms_minute = ($sms_minute < 9 ? "0" : "").$sms_minute;
$my_sms_horaire = $my_sms_heure.":".$my_sms_minute;

if (!$sess_context->isSuperUser())
{
	// Recherche si déjà un sms envoyé pour l'heure choisie
	if (isset($sms_cookie))
	{
		$request = "SELECT count(*) total FROM jb_sms WHERE date = '".$my_sms_jour."' AND heure = '".$my_sms_horaire."' AND cookie='".$sms_cookie."';";
		$res = dbc::execSQL($request);
		$total = ($row = mysql_fetch_array($res)) ? $row['total'] : 0;
		if ($total > 0) ToolBox::do_redirect("sms.php?sms_pseudo=".$sms_pseudo."&sms_msg=".$sms_msg."&sms_jour=".$sms_jour."&sms_heure=".$sms_heure."&sms_minute=".$sms_minute."&sms_invitation=".$sms_invitation."&errno=3");
	}

	// Recherche combien d'entrée pour l'heure choisie
	$request = "SELECT count(*) total FROM jb_sms WHERE date = '".$my_sms_jour."' AND heure = '".$my_sms_horaire."';";
	$res = dbc::execSQL($request);
	$total = ($row = mysql_fetch_array($res)) ? $row['total'] : 0;
	if ($total > 10) ToolBox::do_redirect("sms.php?sms_pseudo=".$sms_pseudo."&sms_msg=".$sms_msg."&sms_jour=".$sms_jour."&sms_heure=".$sms_heure."&sms_minute=".$sms_minute."&sms_invitation=".$sms_invitation."&errno=2");
}

if (!isset($sms_cookie))
{
	$sms_cookie = Toolbox::sessionId();
	setcookie("sms_cookie", $sms_cookie, time()+(3600*24*30*12*10));
}

setcookie("sms_cookie_pseudo",     $sms_pseudo,     time()+(3600*24*30*12*10));
setcookie("sms_cookie_invitation", $sms_invitation, time()+(3600*24*30*12*10));

// Insertion du nouveau joueurs
$insert = "INSERT INTO jb_sms (id_zone, date, heure, pseudo, message, reserve, ip, cookie, invitation) VALUES (".$id_zone.", '".$my_sms_jour."', '".$my_sms_horaire."', '".$sms_pseudo."', '".$sms_msg."', 0, '".$ip."', '".$sms_cookie."', '".$sms_invitation."');";
$res = dbc::execSQL($insert);

if (!$sess_context->isSuperUser())
{
	if ($sms_invitation != "")
	{
		$mail_to     = $sms_invitation;
		$mail_sujet  = "[Jorkers.com] Invitation à consulter un SMS";
		$mail_corps  = "<a href=\"http://www.jorkers.com\">Vous êtes invité à consulter un SMS le ".$sms_jour." à ".$my_sms_horaire." sur le Jorkers.com</a>";
		$mail_header = "Content-Type: text/html; charset=iso-8859-1\n";
		$mail_header .= "From: contact@jorkers.com";
		$res = mail($mail_to,  $mail_sujet, $mail_corps, $mail_header);
	}
}

// On force la recréation du XML
//$xml_file = $sats->getXMLFilename();
//JKCache::delCache($xml_file, "_FLUX_XML_ALBUM_");

mysql_close($db);

ToolBox::do_redirect("sms.php?errno=0");

?>
