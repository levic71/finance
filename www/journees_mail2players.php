<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

TemplateBox::htmlBegin();

?>

<!-- TABLEAU DU CENTRE --------------------------- -->
<TR><TD><TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 HEIGHT=100% WIDTH=100%>
<TR><TD ALIGN=CENTER>

<TABLE BORDER=0>

<?
$fiche = new enveloppe();
$fiche->setStyle("info");
$fiche->debut("");
?>

<FORM ACTION="mail2players.php">
<TABLE BORDER=0>
<TR><TD ALIGN=LEFT NOWRAP><SMALL>Sujet du message   : </SMALL></TD><TD ALIGN=RIGHT><INPUT TYPE=TEXT SIZE=28 NAME=reco_sujet></INPUT></TD>
<TR><TD ALIGN=LEFT COLSPAN=2><SMALL>Contenu du message : </SMALL></TD>
<TR><TD ALIGN=LEFT COLSPAN=2><TEXTAREA NAME=reco_corps COLS=38 ROWS=7>
</TEXTAREA></TD>
<TR><TD ALIGN=CENTER COLSPAN=2>&nbsp;</TD>
<TR><TD ALIGN=CENTER COLSPAN=2><INPUT TYPE=SUBMIT VALUE="Envoyer"></INPUT></TD>
</TABLE>
</FORM>

<? $fiche->end(); ?>

</TABLE></TD>

</TABLE></TD>

<? TemplateBox::htmlEnd(); ?>