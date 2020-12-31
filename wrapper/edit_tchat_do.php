<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/journeebuilder.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$dual = isset($dual) ? $dual : 0;
$real_championnat = $dual == 2 || $dual == 3 || $dual == 5 ? 0 : $sess_context->getRealChampionnatId();

$smiley  = Wrapper::getRequest('smiley', 'smile.gif');
$smiley_dir = ($smiley == "smile.gif") ? "smileys" : basename(dirname($smiley));
$nom     = Wrapper::getRequest('nom',     '');
$photo   = Wrapper::getRequest('photo',   '');
$message = Wrapper::getRequest('message', '');
$sujet   = Wrapper::getRequest('sujet',   '');
$email   = Wrapper::getRequest('email',   'contact@jorkers.com');
$diffusion_joueurs   = Wrapper::getRequest('diffusion_joueurs',   1);
$diffusion_webmaster = Wrapper::getRequest('diffusion_webmaster', 1);
$autres_email        = Wrapper::getRequest('autres_email',        '');
$del     = Wrapper::getRequest('del', 0);
$rep     = Wrapper::getRequest('rep', 1);
$idp     = Wrapper::getRequest('idp', 0);
$upd     = Wrapper::getRequest('upd', 0);

if (($upd == 1 || $del == 1) && !is_numeric($idp)) ToolBox::do_redirect("grid.php");

setcookie("pseudo_forum", str_replace('\\\'', '\'', $nom), time()+(3600*24*30*6));
setcookie("email_forum", $email, time()+(3600*24*30*6));

if ($del == 1 && $idp > 0)
{
	$err = true;

	$sfs = new SQLForumServices($sess_context->getRealChampionnatId());

	function delmsg($id)
	{
		global $sfs, $sess_context;

		//	$msg = $sfs->getMessage($id);
		//	if ($msg['image'] != "" && file_exists($msg['image'])) unlink($msg['image']);
		//	$delete = "DELETE FROM jb_forum WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$id;
		//	$res = dbc::execSQL($delete);

		// On ne supprime plus, on le rend invisible
		$update = "UPDATE jb_forum SET del=1 WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$id;
		$res = dbc::execSQL($update);
	}

	$sql = "SELECT * FROM jb_forum WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$idp;
	$res = dbc::execSQL($sql);
	$msg = mysqli_fetch_array($res);

	// On met à jour le compteur de réponses au msg initial
	if ($msg['in_response'] != 0)
	{
		$update = "UPDATE jb_forum SET nb_reponses=(nb_reponses-1) WHERE id=".$msg['in_response']." AND id_champ=".$real_championnat;
		$res = dbc::execSQL($update);
	}

	// Suppression des réponses au message initial
	$lst_reponses = $sfs->getReponses($idp);
	foreach($lst_reponses as $myrep)
		delmsg($myrep['id']);

	// Suppression du messqge initial
	delmsg($idp);

	$err = false;

?><span class="hack_ie">_HACK_IE_</span><script><?= $rep == 0 ? "mm({action:'tchat'});" : "go({action: 'tchat', id:'main', url:'edit_tchat.php?idp=".$idp."' });" ?> $<?= $err ? "dMsg" : "cMsg" ?>({ msg: 'Message <?= $err ? "non" : "" ?> supprimé' });</script><?
	exit(0);
}


// Pour Tchat, modifier equivaut a repondre a un message
$modifier = $upd == 1 ? true : false;



$sets = array();

// Message à diffuser à tous les joueurs du championnat
if ($diffusion_joueurs == 0 && ($dual != 2 || $dual != 3))
{
	$select = "SELECT * FROM jb_saisons WHERE id=".$sess_context->getChampionnatId();
	$res = dbc::execSQL($select);
	$saison = mysqli_fetch_array($res);

	$select = "SELECT * FROM jb_joueurs WHERE id_champ=".$real_championnat.(isset($saison['joueurs']) && $saison['joueurs'] != "" ? " AND id IN (".SQLServices::cleanIN($saison['joueurs']).")" : "");
	$res = dbc::execSQL($select);
	while($row = mysqli_fetch_array($res)) $sets[$row['email']] = $row['email'];
}

// Message à diffuser au webmaster
if ($diffusion_webmaster == 0) $sets['contact@jorkers.com'] = "contact@jorkers.com";


$prefix = "";
if ($modifier)
{
	// Diffusion aux joueurs qui ont commente le message
	$select = "SELECT * FROM jb_forum WHERE id_champ=".$real_championnat." AND (id=".$idp." OR in_response=".$idp.")";
	$res = dbc::execSQL($select);
	while($row = mysqli_fetch_array($res)) $sets[$row['email']] = $row['email'];

	// On réupère le msg initial
	$req = "SELECT * FROM jb_forum WHERE id=".$idp." AND id_champ=".$real_championnat;
	$res = dbc::execSQL($req);
	$msg_init = mysqli_fetch_array($res);

	// On remet le prefix {xxx} s'il y en a un dans le message initial
	if (strstr($msg_init['title'], '}'))
	{
		$tab = explode('}', $msg_init['title']);
		$prefix = $tab[0]."}";
	}

	$insert = "INSERT INTO jb_forum (ip, agent, image, id_champ, nom, title, email, message, date, in_response, smiley, last_reponse, last_user) VALUES ('".getenv('REMOTE_ADDR')."', '".getenv("HTTP_USER_AGENT")."', '".$photo."', ".$msg_init['id_champ'].", '".$nom."', '".$prefix.$sujet."', '".$email."', '".$message."', NOW(), ".$idp.", '../forum/".$smiley_dir."/".basename($smiley)."', NOW(), '".$nom."');";
}
else
{
	$insert = "INSERT INTO jb_forum (ip, agent, image, id_champ, nom, title, email, message, date, smiley, last_reponse, last_user) VALUES ('".getenv('REMOTE_ADDR')."', '".getenv("HTTP_USER_AGENT")."', '".$photo."', ".$real_championnat.", '".$nom."', '".$prefix.$sujet."', '".$email."', '".$message."', NOW(), '../forum/".$smiley_dir."/".basename($smiley)."', NOW(), '".$nom."');";
}

$res = dbc::execSQL($insert);

// On récupère l'ID du message inséré
$select = "SELECT * FROM jb_forum WHERE id_champ=".$real_championnat." AND nom='".$nom."' AND message='".$message."';";
$res = dbc::execSQL($select);
$mon_msg = mysqli_fetch_array($res);
$id_msg  = $mon_msg['id'];

if ($modifier)
{
	// On met à jour le compteur de réponses au msg initial
	$update = "UPDATE jb_forum SET nb_reponses=".($msg_init['nb_reponses']+1).", last_reponse=NOW(), last_user='".Wrapper::stringEncode4JS($mon_msg['nom'])."' WHERE id=".$msg_init['id']." AND id_champ=".$real_championnat;
	$res = dbc::execSQL($update);
}

// Constitution du message
$mail_sujet  = $sujet;
$mail_corps  = "Championnat : ".$sess_context->championnat['championnat_nom']."\n\n";
$mail_corps .= "Message de  : ".$nom."\n\n";
$mail_corps .= $message;
$mail_corps .= "\n\n------------------------------------------------------------------------------------\n";
$mail_corps .= "<A HREF=http://".Wrapper::string2DNS($sess_context->championnat['championnat_nom']).".jorkers.com/wrapper/jk.php?idc=".$real_championnat."&id_msg=".$id_msg."><img src=\"http://www.jorkers.com/images/fleche.gif\" border=\"0\">Accès au message</a>\n";
$mail_corps .= "------------------------------------------------------------------------------------\n";

$Buffer = "From: ".$email."\n";
$Buffer.= "MIME-Version: 1.0\n";
$Buffer.= "X-Sender: <".$email.">\n";
$Buffer.= "X-Mailer: PHP/".phpversion()."\n";
$Buffer.= "X-Priority: 1\n";
$Buffer.= "Return-Path: <".$email.">\n";
$Buffer.= "Content-Type: text/html; charset=".sess_context::mail_charset."\n";

// On envoit un mail aux personnes qui souhaitent être informées de ce nouveau msg
foreach($sets as $e)
{
	if ($e != "" && !$sess_context->isSuperUser())
	{
		ToolBox::appendLog("[email]:To[".$e."]-From[".$email."]-Subject[".stripslashes($mail_sujet)."]");
		$res = @mail($e,  utf8_decode(stripslashes($mail_sujet)), utf8_decode(nl2br(stripslashes($mail_corps))), $Buffer);
	}
}

// Envoi du mail aux personnes en copie
if (isset($autres_email) && $autres_email != "" && !$sess_context->isSuperUser())
{
	ToolBox::appendLog("[email]:To[".$autres_email."]-From[".$email."]-Subject[".stripslashes($mail_sujet)."]");
	$res = @mail($autres_email,  utf8_decode(stripslashes($mail_sujet)), utf8_decode(nl2br(stripslashes($mail_corps))), $Buffer);
}

if ($real_championnat == 0 && !$sess_context->isSuperUser())
	$res = @mail("contact@jorkers.com",  utf8_decode(stripslashes($mail_sujet)), utf8_decode(nl2br(stripslashes($mail_corps))), $Buffer);


if ($real_championnat == 0)
	JKCache::delCache("../cache/forum_home.txt", "_FLUX_FORUM_HOME_");
else
	JKCache::delCache("../cache/forum_champ_".$real_championnat."_.txt", "_FLUX_FORUM_");

?><span class="hack_ie">_HACK_IE_</span><script>go({action: 'tchat', id:'main', url:'edit_tchat.php?idp=<?= $modifier ? $mon_msg['in_response'] : $id_msg ?>' }); $cMsg({ msg: 'Message ajouté' });</script>