<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$idd = Wrapper::getRequest('idd', 0);

if (!is_numeric($idd)) ToolBox::do_redirect("grid.php");


$emails = array();

$select = "SELECT * from jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$idd;
$res = dbc::execSQL($select);
$day = mysqli_fetch_array($res);

$select = "SELECT * from jb_joueurs WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id IN (".SQLServices::cleanIN($day['joueurs']).")";
$res = dbc::execSQL($select);
while ($row = mysqli_fetch_array($res)) $emails[] = $row;

if (count($emails) > 0) {
	foreach($emails as $e) {
		$mail_header = "From: ".$sess_context->championnat['email']."\n";
		$mail_header.= "MIME-Version: 1.0\n";
		$mail_header.= "X-Sender: <".$sess_context->championnat['email'].">\n";
		$mail_header.= "X-Mailer: PHP/".phpversion()."\n";
		$mail_header.= "X-Priority: 1\n";
		$mail_header.= "Return-Path: <".$sess_context->championnat['email'].">\n";
		$mail_header.= "Content-Type: text/html; charset=".sess_context::mail_charset."\n";
		$mail_sujet  = utf8_encode("[Jorkers.com] Message - Invitation participation journée");
		$mail_corps  = htmlentities("Bonjour ".$e['pseudo']).",\n\n".
				htmlentities("Dans le cadre du championnat ".$sess_context->championnat['championnat_nom']).", ".
				htmlentities("tu es invité à la journée du ").$day['date']."\n".
				htmlentities("Merci de confirmer ta présence.")."\n\n".
				"» <a href=\"http://".Wrapper::string2DNS($sess_context->championnat['championnat_nom']).".jorkers.com/wrapper/invitation_confirm_do.php?idp=&idd=&idc=\">Confirmer</a>\n\n".
				"» <a href=\"http://".Wrapper::string2DNS($sess_context->championnat['championnat_nom']).".jorkers.com/wrapper/invitation_confirm_do.php?idp=&idd=&idc=\">Refuser</a>\n\n".
				"» Tous les invités : \n\n".
				"Cordialement";
		$mail_to     = "contact@jorkers.com";

		if (!$sess_context->isSuperUser()) $res = @mail($mail_to,  utf8_decode(stripslashes($mail_sujet)), utf8_decode(nl2br(stripslashes($mail_corps))), $mail_header);
	}
}
?><span class="hack_ie">_HACK_IE_</span><script>mm({action:'matches', idj: <?= $idd ?>, name: '<?= urlencode($day['nom']) ?>', date: '<?= ToolBox::mysqldate2date($day['date']) ?>' }); $cMsg({msg : 'Message(s) envoyé(s)' });</script>