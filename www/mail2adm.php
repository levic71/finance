<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();
$scs = new SQLChampionnatsServices($sess_context->getRealChampionnatId());
$row = $scs->getChampionnat();

TemplateBox::htmlBegin();

?>

<!-- TABLEAU DU CENTRE --------------------------- -->
<TR><TD><TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 HEIGHT=100%>
<TR><TD ALIGN=CENTER>

<?

$mail_to     = $row['email'];
$mail_sujet  = "[ADM Jorkyball] Mot de passe Jorkyball";
$mail_corps  = "Login : ".$row['login']."\nMot de passe : ".$row['pwd']."\nhttp://www.jorkers.com";
$mail_header = "From: ";

$res = mail($mail_to,  $mail_sujet, $mail_corps, $mail_header);

?>

<SCRIPT>
window.close();
</SCRIPT>

</TABLE></TD>

<? TemplateBox::htmlEnd(); ?>