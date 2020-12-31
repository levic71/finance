<?

include "../include/sess_context.php";

session_start();

$jorkyball_redirect_exception = 1;

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

if (isset($sess_context) && $sess_context->isChampionnatValide())
{
	$menu = new menu("full_access");
	$menu->debut($sess_context->getChampionnatNom());
}
else
{
	$menu = new menu("forum_access");
	$menu->debut("");
}

if (!isset($form_part)) $form_part = 0;

if ($form_part == 0)
	Toolbox::trackUser(isset($sess_context) && $sess_context->isChampionnatValide() ? $sess_context->getRealChampionnatId() : 0, _TRACK_PARTENAIRE_);

if ($form_part == 0) {
?>

<div id="pageint" style="margin-bottom: 0px">

<h2>Soutenir le Jorkers.com et devenir partenaire</h2>

<div>
<P><B STYLE="background:url('../images/fleche.gif') no-repeat 0px 2px; padding-left: 15px;"><U>En devenant partenaire, vous pourrez :</U></B></P>
<UL STYLE="text-align: left;">
<LI>Contribuer à ce que le site continu à vivre et à évoluer</LI>
<LI>Etre présent dans la liste des partenaires officiels</LI>
<LI>Présenter votre complexe sportif (coordonnées, configuration lieu, téléphone)</LI>
<li>Personnaliser le plan d'accès avec géocalisation de votre salle (exemple <b><a class="blue" href="http://www.jorkers.com/www/partenaires_map.php?id_part=1">ici</a></b>)</li>
<LI>Exporter directement les résultats de vos championnats sur vos propres sites</LI>
<LI>Bénéficier de tarifs préférentiels pour utiliser l'espace publicitaire</LI>
</UL>

<P>
>> Si vous êtes intéressé pour devenir partenaire ou pour avoir plus d'informations, <B><A CLASS=blue HREF="../www/devenir_partenaire.php?type_info=0&amp;form_part=1">cliquez ici</A></B>.
</P>

</DIV>

<DIV CLASS=box>
<B STYLE="background:url('../images/fleche.gif') no-repeat 0px 2px; padding-left: 15px;">Si vous souhaitez utiliser l'espace publicitaire du JORKERS.com sans être partenaire, <A CLASS=blue HREF="../www/devenir_partenaire.php?type_info=1&amp;form_part=1">cliquez ici.</A></B>
</DIV>

<DIV CLASS=box>
<B>ATTENTION: Il n'est pas nécessaire d'être partenaire pour utiliser le JORKERS.com !!!</B>
</DIV>

<DIV CLASS=box STYLE="margin: 20px 0px 50px 0px;">
<B STYLE="background:url('../images/fleche.gif') no-repeat 0px 2px; padding-left: 15px;"><A CLASS=blue HREF="../www/partenaires.php?type_info=1&amp;form_part=1">Liste des partenaires</A></B>
</DIV>

</div>

<? } else if ($form_part == 1) { ?>

<div id="pageint" style="margin-bottom: 0px">

<h2>DEMANDE D'INFORMATION</h2>

<FORM ACTION=../www/devenir_partenaire.php>
<INPUT TYPE=HIDDEN NAME=form_part VALUE=2 />
<TABLE BORDER=0 SUMMARY="">
<TR><TD>Nom (*) :</TD><TD><INPUT TYPE=TEXT NAME=part_nom SIZE=64 /></TD>
<TR><TD>Prénom :</TD><TD><INPUT TYPE=TEXT NAME=part_prenom SIZE=64 /></TD>
<TR><TD>Email (*) :</TD><TD><INPUT TYPE=TEXT NAME=part_email SIZE=64 /></TD>
<TR><TD>Téléphone :</TD><TD><INPUT TYPE=TEXT NAME=part_tel SIZE=64 /></TD>
<TR><TD>Sujet de votre message :</TD><TD><SELECT NAME=type_info><OPTION VALUE=0 <?= $type_info == 0 ? "SELECTED" : ""?>>Partenariat<OPTION VALUE=1 <?= $type_info == 1 ? "SELECTED" : ""?>>Publicité<OPTION VALUE=2>Divers</SELECT></TD>
<TR><TD>Commentaire :</TD><TD><TEXTAREA NAME=part_comment COLS=50 ROWS=10></TEXTAREA></TD>
<TR><TD COLSPAN=2 ALIGN=right><TABLE BORDER=0 SUMMARY=""><TR><TD><INPUT TYPE=SUBMIT VALUE=Annuler onclick="document.forms[0].action='../www/partenaires.php';" /></TD><TD><INPUT TYPE=SUBMIT VALUE=Valider /></TD></TABLE></TD>
<TR><TD COLSPAN=2 ALIGN=center>(*) Champ obligatoire</TD>
</TABLE>
</FORM>

</div>

<?
}
else
{
	$mail_to     = "contact@jorkers.com";
	$mail_sujet  = "[Msg][Marketing]Demande d'information partenariat";
	$mail_corps  = "";
	$mail_corps  .= "Nom : ".$part_nom."\n";
	$mail_corps  .= "Prénom : ".$part_prenom."\n";
	$mail_corps  .= "Email : ".$part_email."\n";
	$mail_corps  .= "Téléphone : ".$part_tel."\n";
	$mail_corps  .= "Commentaire : ".$part_comment."\n";
	$mail_corps  .= "Type information : ".$type_info."\n";
	$mail_header = "From: ".$part_email;
	$res = mail($mail_to,  $mail_sujet, $mail_corps, $mail_header);
?>
<DIV CLASS=box>
<UL><B>Votre demande d'information a été prise en compte, merci de l'intérêt que vous portez aux JORKERS.com</B></UL>
<DIV  CLASS=titre><A CLASS=menu HREF="../www/partenaires.php">Liste des partenaires</A></DIV>
</DIV>
<?
}

?>

<? $menu->end(); ?>

