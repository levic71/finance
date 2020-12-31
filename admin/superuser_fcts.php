<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";
include "../www/ManagerFXList.php";

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

?>

<FORM ACTION="superuser_fcts.php">

<TABLE BORDER=0 CELLPADDING=1 CELLSPACING=1 WIDTH=600>

<TR VALIGN=TOP><TD><TABLE BORDER=0 STYLE="border: 1px solid #AAAAAA; margin-top: 5px;">
<?

$lib  = "<p class=p>Bienvenue sur l'écran des fonctions d'administration de votre championnat.</p>";
$lib .= "<p class=p>Utiliser les différentes fonctions ci-dessous en cliquant sur la ligne de votre choix.</p>";
$lib .= "<p class=p>Si vous accèder aux menus classiques (joueurs/équipes/journées/photos/albums), vous pourrez mettre à jour ces différentes sections.</p>";
echo "<TR VALIGN=TOP><TD WIDTH=200 ALIGN=LEFT>".$lib."</TD>";
echo "<TR><TD HEIGHT=10></TD>";

?>

</TABLE></TD>

<TD ALIGN=CENTER><TABLE BORDER=0>
	<TR>
		<TD ALIGN=CENTER><a href="#" onClick="javascript:launch('../www/saisons.php');"><img src=../images/admin_panel_saisons.gif border=0 onmouseover="javascript:this.src='../images/admin_panel_saisons_o.gif';" onmouseout="javascript:this.src='../images/admin_panel_saisons.gif';"></a><TD>
		<TD ALIGN=CENTER><a href="#" onClick="javascript:launch('journees_full_sync.php');"><img src=../images/admin_panel_sync.gif border=0 onmouseover="javascript:this.src='../images/admin_panel_sync_o.gif';" onmouseout="javascript:this.src='../images/admin_panel_sync.gif';"></a><TD>
	</TR>
	<TR>
		<TD ALIGN=CENTER><a href="#" onClick="javascript:launch('../admin/stats_freq.php');"><img src=../images/admin_panel_stats.gif border=0 onmouseover="javascript:this.src='../images/admin_panel_stats_o.gif';" onmouseout="javascript:this.src='../images/admin_panel_stats.gif';"></a><TD>
		<TD ALIGN=CENTER><a href="#" onClick="javascript:launch('../admin/backup.php');"><img src=../images/admin_backup.gif border=0 onmouseover="javascript:this.src='../images/admin_backup_o.gif';" onmouseout="javascript:this.src='../images/admin_backup.gif';"></a><TD>
	</TR>
	<TR>
		<TD ALIGN=CENTER><a href="#" onClick="javascript:launch('superuser_exit.php');"><img src=../images/admin_panel_exit.gif border=0 onmouseover="javascript:this.src='../images/admin_panel_exit_o.gif';" onmouseout="javascript:this.src='../images/admin_panel_exit.gif';"></a><TD>
	</TR>

</TABLE></TD>

</TABLE>

</FORM>

<? $menu->end(); ?>
