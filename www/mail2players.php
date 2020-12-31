<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

$scs = new SQLChampionnatsServices($sess_context->getRealChampionnatId());
$row = $scs->getChampionnat();

$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$journee = $sjs->getJournee();

$sps = new SQLJoueursServices($sess_context->getRealChampionnatId());
$lst = $sps->getListeJoueursFromIds(ereg_replace(",$", "", $journee['joueurs']));
$emails = "";
foreach($lst as $joueur)
	if ($joueur['email'] != "") $emails .= ($emails == "" ? "" : ";").$joueur['email'];

TemplateBox::htmlBegin();

?>

<!-- TABLEAU DU CENTRE --------------------------- -->
<TR><TD><TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 HEIGHT=100%>
<TR><TD ALIGN=CENTER>

<?

$mail_to     = $emails;
$mail_sujet  = $reco_sujet;
$mail_corps  = $reco_corps."\n\n------------------------------------------------------\nhttp://www.jorkers.com\n";
$mail_header = "From: ".$row['nom'];

$res = mail($mail_to,  $mail_sujet, $mail_corps, $mail_header);

?>

<SCRIPT>
window.close();
</SCRIPT>

</TABLE></TD>

<? TemplateBox::htmlEnd(); ?>