<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

// type_mail = 0 : Envoi mail au webmaster du Jorkers
// type_mail = 1 : Envoi mail au gérant d un championnat
// type_mail = 2 : Envoi mail aux joueurs ayant participés à une journée donnée d un championnat
// type_mail = 3 : Envoi mail à tous les joueurs d un championnat
// type_mail = 4 : Envoi mail pour rejoindre le staff
// type_mail = 5 : Envoi mail pour se rattacher à un joueur

$type_mail = Wrapper::getRequest('type_mail', 0);
$nom       = Wrapper::getRequest('nom',       '');
$controle  = Wrapper::getRequest('controle',  '');
$email     = Wrapper::getRequest('email',     'contact@jorkers.com');
$sujet     = Wrapper::getRequest('sujet',     '');
$message   = Wrapper::getRequest('message',   '');
$idd       = Wrapper::getRequest('idd',       0);
$idp       = Wrapper::getRequest('idp',       0);
$name      = Wrapper::getRequest('name',      0);
$date      = Wrapper::getRequest('date',      0);
$mail_to   = array();
$action    = "dashboard";

if (false && $controle != $_SESSION['antispam'])
{
	echo "-1||Zone de controle non correcte ".$_SESSION['antispam']." ".$controle; exit(0);
}
else
{
	unset($_SESSION['antispam']);

	$mail_header = "From: ".$email."\n";
	$mail_header.= "MIME-Version: 1.0\n";
	$mail_header.= "X-Sender: <".$email.">\n";
	$mail_header.= "X-Mailer: PHP/".phpversion()."\n";
	$mail_header.= "X-Priority: 1\n";
	$mail_header.= "Return-Path: <".$email.">\n";
	$mail_header.= "Content-Type: text/html; charset=".sess_context::mail_charset."\n";
	$mail_sujet  = "[Jorkers.com] - ".$sujet;
	$mail_corps  = "Nom : ".$nom."\nEmail : ".$email."\nSujet : ".$sujet."\nMessage :\n".$message;

	if ($type_mail == 0)
	{
		$mail_to[]   = "contact@jorkers.com";
	}

	if ($type_mail == 1)
	{
		$mail_to[] = $sess_context->championnat['email'];
	}

	if ($sess_context->isAdmin() && $type_mail == 2)
	{
		$action   = "matches";
		$mail_sujet = "[Jorkers.com] - ".$sujet;
		$mail_corps = $message;

		$scs = new SQLChampionnatsServices($sess_context->getRealChampionnatId());
		$row = $scs->getChampionnat();

		$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $idd);
		$journee = $sjs->getJournee();

		$sps = new SQLJoueursServices($sess_context->getRealChampionnatId());
		$lst = $sps->getListeJoueursFromIds(ereg_replace(",$", "", $journee['joueurs']));

		foreach($lst as $joueur)
			if ($joueur['email'] != "") $mail_to[] = $joueur['email'];
	}

	if ($sess_context->isAdmin() && $type_mail == 3)
	{
		$action   = "players";
		$mail_sujet = "[Jorkers.com] - ".$sujet;
		$mail_corps = $message;

		$sps = new SQLJoueursServices($sess_context->getRealChampionnatId());
		$lst = $sps->getListeJoueurs();

		foreach($lst as $joueur)
			if ($joueur['email'] != "") $mail_to[] = $joueur['email'];
	}

	if ($sess_context->isUserConnected() && $type_mail == 4)
	{
		$mail_sujet = "[Jorkers.com] - ".$sujet;
		$mail_corps = $message."\n\n---------------------------------------------\nPour valider cette demande, connectez vous sur le Jorkers, et au niveau du dashboard cliquez sur \"Droits d'administration\"";
		$mail_to[] = $sess_context->championnat['email'];

		$sql = "INSERT INTO jb_roles (id_champ, id_user, role, status) VALUES (".$sess_context->getRealChampionnatId().", ".$sess_context->user['id'].", "._ROLE_DEPUTY_.", 0);";
		$res = dbc::execSQL($sql);
	}

	if ($sess_context->isUserConnected() && $type_mail == 5)
	{
		$mail_sujet = "[Jorkers.com] - ".$sujet;
		$mail_corps = $message."\n\n---------------------------------------------\nPour valider cette demande, connectez vous sur le Jorkers, et au niveau du dashboard cliquez sur \"Droits d'administration\"";
		$mail_to[] = $sess_context->championnat['email'];

		// On vérifie que c pas déjà affecté
		$sql = "SELECT count(*) total FROM jb_user_player WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id_player=".$idp." AND status=1;";
		$res = dbc::execSQL($sql);
		$data = mysqli_fetch_assoc($res);
		if ($data['total'] > 0) {
?><span class="hack_ie">_HACK_IE_</span><script>mm({action:'<?= $action ?>'}); $dMsg({msg : 'Action impossible, joueur déjà rattaché !'});</script>
<?
			exit(0);
		}

		$sql = "INSERT INTO jb_user_player (id_champ, id_user, id_player, status, date_request) VALUES (".$sess_context->getRealChampionnatId().", ".$sess_context->user['id'].", ".$idp.", 0, NOW());";
		$res = dbc::execSQL($sql);
	}

	// Envoi du/des mail(s) uniquement en prod !!!
	foreach($mail_to as $m) {
		if (!$sess_context->isSuperUser())
			$res = @mail($m, stripslashes($mail_sujet), nl2br(stripslashes($mail_corps)), $mail_header);
		else
			echo "Envoi mail à ".$m." : ".stripslashes($mail_sujet);
	}
}

?><span class="hack_ie">_HACK_IE_</span><script><?= $type_mail == 2 ? "mm({action:'".$action."', idj:'".$idd."', name:'".$name."', date:'".$date."'});" : "mm({action:'".$action."'});" ?> $cMsg({msg : 'Message envoyé.' });</script>