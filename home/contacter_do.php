<?

session_start();

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

include "../wrapper/wrapper_fcts.php";

$nom       = Wrapper::getRequest('nom',       '');
$controle  = Wrapper::getRequest('controle',  '');
$email     = Wrapper::getRequest('email',     'contact@jorkers.com');
$sujet     = Wrapper::getRequest('sujet',     '');
$message   = Wrapper::getRequest('message',   '');

if ($email == '' || $sujet == '' || $message == '')
{
	echo "-1||Champs manquants"; exit(0);
}

if ($controle != $_SESSION['antispam'])
{
	echo "-1||Zone de controle non correcte"; exit(0);
}

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
$mail_to[]   = "contact@jorkers.com";

// Envoi du/des mail(s) uniquement en prod !!!
foreach($mail_to as $m) {
	if (true)
		$res = @mail($m, stripslashes($mail_sujet), nl2br(stripslashes($mail_corps)), $mail_header);
}

?>1||<div class="alert alert-info">
    <a class="close" data-dismiss="alert" href="#">×</a>
    Demande traitée
    </div>