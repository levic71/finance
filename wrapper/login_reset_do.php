<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$email = Wrapper::getRequest('email', 'victor.ferreira@laposte.net');

$db = dbc::connect();

$select = "SELECT * FROM jb_users WHERE email='".$email."';";
$res = dbc::execSQL($select);
if ($row = mysqli_fetch_array($res))
{
	$token = bin2hex(random_bytes(50));
	$update = "UPDATE jb_users SET reset_time=".time().", reset_token='".$token."', reset_count=0 WHERE email='".$row['email']."'";
	$res = dbc::execSQL($update);

	unset($_SESSION['antispam']);
	$mail_sender = "contact@jorkers.com";
	$mail_header = "From: ".$mail_sender."\n";
	$mail_header.= "MIME-Version: 1.0\n";
	$mail_header.= "X-Sender: <".$mail_sender.">\n";
	$mail_header.= "X-Mailer: PHP/".phpversion()."\n";
	$mail_header.= "X-Priority: 1\n";
	$mail_header.= "Return-Path: <no-reply@jorkers.com>\n";
	$mail_header.= "Content-Type: text/html; charset=".sess_context::mail_charset."\n";
	$mail_sujet  = "[Jorkers.com] - Mot de passe oubli�";
	$mail_corps  = "Bonjour";

//	if (!$sess_context->isSuperUser())
		$res = @mail($email, stripslashes($mail_sujet), nl2br(stripslashes($mail_corps)), $mail_header);
}

mysqli_close ($db);

?><span class="hack_ie">_HACK_IE_</span><script>mm({action: 'login', mobile: 0}); $cMsg({msg : 'La demande a �t� prise en compte' });</script>