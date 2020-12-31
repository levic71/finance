<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

if (isset($option) && $option == 0)
{
	// Gestionnaire championnat
	$db = dbc::connect();
	$scs = new SQLChampionnatsServices($sess_context->getRealChampionnatId());
	$row = $scs->getChampionnat();
	mysql_close ($db);

	$mail_to     = $row['email'];
	$mail_sujet  = "[Msg][Gestionnaire]".$reco_sujet;
	$mail_corps  = "Nom : ".$reco_nom."\nFrom : ".$reco_email."\n".$reco_corps;
	$mail_header = "From: ".$reco_email;
}
else
{
	// Administrateur Site
	$mail_to     = "contact@jorkers.com";
	$mail_sujet  = "[Msg][Administrateur]".$reco_sujet;
	$mail_corps  = "Nom : ".$reco_nom."\n".$reco_corps;
	$mail_header = "From: ".$reco_email;
}

$res = mail($mail_to,  $mail_sujet, $mail_corps, $mail_header);

ToolBox::do_redirect($sess_context->isChampionnatNonValide() ? "home.php" : "championnat_home.php");

?>
