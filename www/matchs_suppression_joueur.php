<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

TemplateBox::htmlBegin();

$select = "SELECT * FROM jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getJourneeId();
$res = dbc::execSQL($select);
$j = mysql_fetch_array($res);

$select = "SELECT * FROM jb_joueurs WHERE id_champ=".$sess_context->getChampionnatId()." AND id IN (".ereg_replace(",$", "", $j['joueurs']).")";
$res = dbc::execSQL($select);

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

<FORM ACTION="matchs_supprimer_joueur_do.php">
<TABLE BORDER=0>
<?
	$i = 0;
	while($joueur = mysql_fetch_array($res))
		echo "<TR><TD><INPUT TYPE=CHECKBOX NAME=joueur".$i++." VALUE=".$joueur['id']."></TD><TD>".$joueur['nom']." ".$joueur['nom']." as ".$joueur['pseudo']."</TD>";
?>
<TR><TD ALIGN=CENTER COLSPAN=2>&nbsp;</TD>
<TR><TD ALIGN=CENTER COLSPAN=2><INPUT TYPE=SUBMIT VALUE="Supprimer"></INPUT></TD>
</TABLE>
</FORM>

<? $fiche->end(); ?>

</TABLE></TD>

</TABLE></TD>

<? TemplateBox::htmlEnd(); ?>
