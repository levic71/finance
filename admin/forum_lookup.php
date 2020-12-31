<?

include "../include/sess_context.php";

session_start();

include "../include/constantes.php";
include "../include/toolbox.php";
include "../include/Xclasses.php";
include "../include/HTMLTable.php";
include "../include/inc_db.php";

$db = dbc::connect();

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD 4.01 Transitional//EN">
<HTML>
<HEAD>
<META NAME="author"        CONTENT="Jorkers">
<META NAME="keywords"      CONTENT="Jorkers,gratuit,gestion,championnat,TOURNOI,Tournoi,Forum,jorkers,jorker,Jorkyball, jorkyball, JORKYBALL, JorkyBall,Jorky,online,en ligne,web,footris,Gratuit,Gestion,Championnat,Championship,classement,tournoi,statistique,joueur,équipe,journée,photo,forum,football,sport,divertissement,compétition,ami,pote,fun,futsal,Futsal Tournaments,management">
<META NAME="description"   CONTENT="Gestion de Championnats/tournois de JorkyBall - Tout est gratuit - Saisissez vos joueurs/équipes/matchs et automatiquement les classements et les statistiques sont calculés. Affichage et personnalisation de ces informations sur votre site grâce à la syndication des classements.">
<META NAME="robots"        CONTENT="index, follow">
<META NAME="rating"        CONTENT="General">
<META NAME="distribution"  CONTENT="Global">
<META NAME="author"        CONTENT="contact@jorkers.com">
<META NAME="reply-to"      CONTENT="contact@jorkers.com">
<META NAME="owner"         CONTENT="contact@jorkers.com">
<META NAME="copyright"     CONTENT="jorkers.com">
<META http-equiv="Content-Language" CONTENT="fr-FX">
<META http-equiv="Content-Type"     CONTENT="text/html; charset=<?= sess_context::charset ?>">
<LINK HREF="../images/H.ico" REL="shortcut icon">
<TITLE>Jorkers - Gestion de tournois/championnats de Jorkyball</TITLE>
</HEAD>
<BODY>

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=100%>
<TR><TD ALIGN=CENTER><TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=100% STYLE="border: 1px solid black;">

<STYLE type="text/css">
TD {
	border-top: 1px dashed #525252;
	font-family  : arial;
	font-size    : x-small;
	font-variant : normal;
	font-weight  : normal;
	font-size    : 12px;
}
TABLE {
	border-collapse:collapse
}
</STYLE>

<?

echo "<TR>";
HTMLTable::printCellWithColSpan("<FONT SIZE=5 COLOR=white>Liste des derniers messages</FONT>", "#4863A0", "100%", "CENTER", _CELLBORDER_ALL_, 7);

$lst_champ[0] = "Forum général";
$req = "SELECT * FROM jb_championnat";
$res = dbc::execSQL($req);
while($row = mysql_fetch_array($res))
{
	$lst_champ[$row['id']] = $row['nom'];
}

$i = 0;
$req = "SELECT * FROM jb_forum ORDER BY date DESC LIMIT 0,50";
$res = dbc::execSQL($req);
while($row = mysql_fetch_array($res))
{
	echo "<TR VALIGN=TOP onMouseOver=\"this.bgColor='#D5D9EA'\" onMouseOut =\"this.bgColor=''\">";
	HTMLTable::printCell("<IMG SRC=../images/".($row['del'] == 0 ? "ok.gif" : "del.gif").">", "#BCC5EA",  "3%", "CENTER", _CELLBORDER_BOTTOM_);
	HTMLTable::printCell("<IMG SRC=".$row['smiley'].">", "#BCC5EA",  "5%", "CENTER", _CELLBORDER_BOTTOM_);
	HTMLTable::printCell(ToolBox::mysqldate2smalldatetime($row['date']), "#BCC5EA", "8%", "CENTER", _CELLBORDER_BOTTOM_);
	HTMLTable::printCell($row['nom'], "#BCC5EA", "10%", "LEFT", _CELLBORDER_BOTTOM_);
	HTMLTable::printCell("<A HREF=../www/forum_redirect.php?champ=".$row['id_champ']."&id_msg=".$row['id'].">".$row['title']."</A>", "#BCC5EA",  "15%", "LEFT", _CELLBORDER_BOTTOM_);
	HTMLTable::printCell($row['message'].($row['image'] == "" ? "" : "<BR><IMG SRC=".$row['image']." BORDER=0>"), "#BCC5EA",  "", "LEFT", _CELLBORDER_BOTTOM_);
	HTMLTable::printCell($lst_champ[$row['id_champ']], "#BCC5EA",  "10%", "LEFT", _CELLBORDER_BOTTOM_);
	$i++;
}

?>

</TABLE></TD>

</TABLE>

</BODY>
</HTML>
