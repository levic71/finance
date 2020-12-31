<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "ManagerTableExtends.php";

$friends = isset($friends) && $friends == 1 ? 1 : 0;

if (isset($pkeys_where_jb_championnat) && $pkeys_where_jb_championnat != "")
{
	$db = dbc::connect();
	$scs = new SQLChampionnatsServices(-1);
	$row = $scs->getChampionnatByPKeysWhere($pkeys_where_jb_championnat);
?>
	<SCRIPT>
	window.opener.document.forms[0].<?= $friends == 1 ? "ch_friends" : "championnat" ?>.value='<?= $row['nom'] ?>';
	window.opener.document.forms[0].submit();
	window.close();
	</SCRIPT>
<?
}

TemplateBox::htmlBegin();

?>
<FORM ACTION="championnat_aide.php">
<INPUT TYPE=HIDDEN NAME=friends VALUE=<?= $friends ?>>

<TR><TD><TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 HEIGHT=100% WIDTH=100%>
<TR VALIGN=CENTER><TD ALIGN=CENTER>

<?	$mtc = new ManagerTableChampionnats(false);
	$mtc->display(); ?>

</TD>
<TR><TD ALIGN=CENTER>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=A&friends=<?= $friends ?>>A</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=B&friends=<?= $friends ?>>B</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=C&friends=<?= $friends ?>>C</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=D&friends=<?= $friends ?>>D</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=E&friends=<?= $friends ?>>E</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=F&friends=<?= $friends ?>>F</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=G&friends=<?= $friends ?>>G</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=H&friends=<?= $friends ?>>H</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=I&friends=<?= $friends ?>>I</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=J&friends=<?= $friends ?>>J</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=K&friends=<?= $friends ?>>K</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=L&friends=<?= $friends ?>>L</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=M&friends=<?= $friends ?>>M</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=N&friends=<?= $friends ?>>N</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=O&friends=<?= $friends ?>>O</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=P&friends=<?= $friends ?>>P</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=Q&friends=<?= $friends ?>>Q</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=R&friends=<?= $friends ?>>R</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=S&friends=<?= $friends ?>>S</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=T&friends=<?= $friends ?>>T</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=U&friends=<?= $friends ?>>U</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=V&friends=<?= $friends ?>>V</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=W&friends=<?= $friends ?>>W</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=X&friends=<?= $friends ?>>X</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=Y&friends=<?= $friends ?>>Y</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=Z&friends=<?= $friends ?>>Z</A>
<A CLASS=blue HREF=championnat_aide.php?check_nom=on&nom=&friends=<?= $friends ?>>#</A>
</TABLE></TD>

</FORM>
<? TemplateBox::htmlEnd(); ?>