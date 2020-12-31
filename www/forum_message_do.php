<?

include "../include/sess_context.php";

session_start();

setcookie("pseudo_forum", str_replace('\\\'', '\'', $msg_nom),   time()+(3600*24*30*6));
setcookie("email_forum",  $msg_email, time()+(3600*24*30*6));

include "common.php";
include "../include/inc_db.php";

if (!isset($msg_nom) || $msg_nom == "") ToolBox::do_redirect("forum.php?errno=1");

$menu = new menu("full_access");

$db = dbc::connect();

if (!isset($dual)) $dual = 0;

$msg_diffusion = isset($msg_diffusion) ? $msg_diffusion : 0;
$msg_webmaster = isset($msg_webmaster) ? $msg_webmaster : 0;

$real_championnat = $dual == 2 ||$dual == 3 ||$dual == 5 ? 0 : $sess_context->getRealChampionnatId();

// Gestion de la piece jointe (image)
$source   = ToolBox::get_global("msg_image");
$filename = ToolBox::purgeCaracteresWith("_", "../uploads/FORUM_".$real_championnat."_".ToolBox::get_global("msg_image_name"));

if ($source != "" && file_exists($source))
	$filename = ImageBox::imageWidthResize($source, $filename, 80, 400);
else
	$filename = "";

// Smiley par défaut
$smiley = ($smiley == "") ? "smile.gif" : $smiley;
$smiley_dir = ($smiley == "smile.gif") ? "smileys" : basename(dirname($smiley));

// On récupère les emails des personnes qui souhaitent être informées de ce nouveau msg
$sets = array();
if (isset($id_msg_rep))
{
	$sets = array();

	$select = "SELECT * FROM jb_forum WHERE id_champ=".$real_championnat." AND (id=".$id_msg_rep." OR in_response=".$id_msg_rep.")";
	$res = dbc::execSQL($select);
	while($row = mysql_fetch_array($res)) $sets[$row['email']] = $row['email'];
}

// Message à diffuser à tous les joueurs du championnat
if ($msg_diffusion == 1 && ($dual != 2 || $dual != 3))
{
	$select = "SELECT * FROM jb_saisons WHERE id=".$sess_context->getChampionnatId();
	$res = dbc::execSQL($select);
	$saison = mysql_fetch_array($res);

	$select = "SELECT * FROM jb_joueurs WHERE id_champ=".$real_championnat.(isset($saison['joueurs']) && $saison['joueurs'] != "" ? " AND id IN (".$saison['joueurs'].")" : "");
	$res = dbc::execSQL($select);
	while($row = mysql_fetch_array($res)) $sets[$row['email']] = $row['email'];
}

// Message à diffuser au webmaster
if ($msg_webmaster == 1) $sets['contact@jorkers.com'] = "contact@jorkers.com";

// On réupère le msg initial
$prefix = "";
if (isset($id_msg_rep))
{
	$req = "SELECT * FROM jb_forum WHERE id=".$id_msg_rep." AND id_champ=".$real_championnat;
	$res = dbc::execSQL($req);
	$msg_init = mysql_fetch_array($res);
	
	// On remet le prefix {xxx} s'il y en a un dans le message initial
	if (strstr($msg_init['title'], '}'))
	{	
		$tab = explode('}', $msg_init['title']);
		$prefix = $tab[0]."}";
	}
}

$mon_id_champ = isset($id_msg_rep) ? $msg_init['id_champ'] : $real_championnat;

// On insère le message dans la BD
if (isset($id_msg_rep))
	$insert = "INSERT INTO jb_forum (ip, agent, image, id_champ, nom, title, email, message, date, in_response, smiley, last_reponse, last_user) VALUES ('".getenv('REMOTE_ADDR')."', '".getenv("HTTP_USER_AGENT")."', '".$filename."', ".$mon_id_champ.", '".$msg_nom."', '".$prefix.$msg_titre."', '".$msg_email."', '".$ta."', NOW(), ".$id_msg_rep.", '../forum/".$smiley_dir."/".basename($smiley)."', NOW(), '".$msg_nom."');";
else
	$insert = "INSERT INTO jb_forum (ip, agent, image, id_champ, nom, title, email, message, date, smiley, last_reponse, last_user) VALUES ('".getenv('REMOTE_ADDR')."', '".getenv("HTTP_USER_AGENT")."', '".$filename."', ".$mon_id_champ.", '".$msg_nom."', '".$prefix.$msg_titre."', '".$msg_email."', '".$ta."', NOW(), '../forum/".$smiley_dir."/".basename($smiley)."', NOW(), '".$msg_nom."');";
$res = dbc::execSQL($insert);

// On récupère l'ID du message inséré
$select = "SELECT * FROM jb_forum WHERE id_champ=".$real_championnat." AND nom='".$msg_nom."' AND message='".$ta."';";
$res = dbc::execSQL($select);
$mon_msg = mysql_fetch_array($res);
$id_msg  = $mon_msg['id'];

// On met à jour le compteur de réponses au msg initial
if (isset($id_msg_rep))
{
	$update = "UPDATE jb_forum SET nb_reponses=".($msg_init['nb_reponses']+1).", last_reponse=NOW(), last_user='".str_replace('\'', '\\\'', $mon_msg['nom'])."' WHERE id=".$msg_init['id']." AND id_champ=".$real_championnat;	$res = dbc::execSQL($update);
}

// Constitution du message
$mail_sujet  = $msg_titre;
$mail_corps  = $ta;
$mail_corps .= "\n\n------------------------------------------------------------------------------------\n";
$mail_corps .= "<A HREF=http://www.jorkers.com/www/forum_redirect.php?champ=".$real_championnat."&id_msg=".$id_msg."#ITEM_".$id_msg."><IMG SRC=http://www.jorkers.com/images/fleche.gif BORDER=0>Accès au message</A>\n";
$mail_corps .= "------------------------------------------------------------------------------------\n";

$Buffer = "From: [".$msg_nom."] Jorkers.com <".$msg_email.">\n";
$Buffer.= "MIME-Version: 1.0\n";
$Buffer.= "X-Sender: <".$msg_email.">\n";
$Buffer.= "X-Mailer: PHP/".phpversion()."\n";
$Buffer.= "X-Priority: 1\n";
$Buffer.= "Return-Path: <".$msg_email.">\n";
$Buffer.= "Content-Type: text/html; charset=".sess_context::mail_charset."\n";

// On envoit un mail aux personnes qui souhaitent être informées de ce nouveau msg
foreach($sets as $e)
{
	if ($e != "")
	{
		ToolBox::appendLog("[email]:To[".$e."]-From[".$msg_email."]-Subject[".stripslashes($mail_sujet)."]");
		$res = @mail($e, stripslashes($mail_sujet), nl2br(stripslashes($mail_corps)), $Buffer);
	}
}

// Envoi du mail aux personnes en copie
if (isset($msg_copie) && $msg_copie != "")
{
	ToolBox::appendLog("[email]:To[".$msg_copie."]-From[".$msg_email."]-Subject[".stripslashes($mail_sujet)."]");
	$res = @mail($msg_copie, stripslashes($mail_sujet), nl2br(stripslashes($mail_corps)), $Buffer);
}

if ($real_championnat == 0)
	$res = @mail("contact@jorkers.com",  stripslashes($mail_sujet), nl2br(stripslashes($mail_corps)), $Buffer);

mysql_close ($db);

if ($real_championnat == 0)
	JKCache::delCache("../cache/forum_home.txt", "_FLUX_FORUM_HOME_");
else
	JKCache::delCache("../cache/forum_champ_".$real_championnat."_.txt", "_FLUX_FORUM_");

ToolBox::do_redirect("forum.php?dual=".$dual);

?>
