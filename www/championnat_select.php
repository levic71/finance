<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

$friends = isset($friends) && $friends == 1 ? 1 : 0;

TemplateBox :: htmlBegin();

// On ajoute les championnats sélectionnés à la fenêtre appelante et on ferme la fenêtre courante
if (isset($ajouter) && $ajouter != "")
{
	$scs = new SQLChampionnatsServices();
?>
<script>
	<? if ($friends == 0)
	{ 
		$row = $scs->getChampionnatByPKeysWhere($pkeys_where_jb_championnat);
	?>
	window.opener.document.forms[0].championnat.value='<?= $row['nom'] ?>';
	<? } else { 
?>
   	SBox_Vider(window.opener.document.forms[0].ch_friends);
	SBox_Ajout_Item(window.opener.document.forms[0].ch_friends,  '______________________________________', 0, false);
<?
		while(list($cle, $val) = each($HTTP_POST_VARS))
		{
			if (strstr($cle, "cb_championnat_"))
			{
				$exp = explode("_", $cle);
				$row = $scs->getChampionnat($exp[2]);
	?>
       	SBox_Ajout_Item(window.opener.document.forms[0].ch_friends,  '<?= $row['championnat_nom'] ?>', <?= $exp[2] ?>, false);
	<?
			}
		} 
	} ?>
	window.close();

</script>
<?
	exit(0);
}
?>

<FORM ACTION="championnat_select.php" METHOD=POST>
<INPUT TYPE=HIDDEN NAME=friends VALUE=<?= $friends ?> />

<CENTER>

<TR><TD HEIGHT=40 COLSPAN=2><TABLE  BACKGROUND=../images/panneau.jpg BORDER=0 HEIGHT=100% WIDTH=100% STYLE="border-bottom: 1px solid black;"><TR><TD ALIGN=CENTER> <FONT CLASS=big SIZE=3 COLOR=white> CHOIX D'UN CHAMPIONNAT </FONT> </TD></TABLE></TD>

<TR VALIGN=TOP>

<TD WIDTH=50 STYLE="border-right: 1px dashed black;"><TABLE BORDER=0 STYLE="border: 1px dashed black; background: #EEEEEE;margin: 5 5 0 5;">
<!-- TR onmouseover="this.style.background='#DDDDDD';" onmouseout="this.style.background='';"><TD ALIGN=LEFT STYLE="border-bottom: 1px dashed black;"><IMG SRC=arrow.gif BORDER=0><A HREF=decouvrir.php?decouvrir_item=1 CLASS=blue> Le Jorkyball Championship : C'est Quoi ? </A></TD -->
</TABLE></TD>

<TD ALIGN=CENTER><TABLE style="background: white;" WIDTH=500 BORDER=0 CELLPADDING=0 CELLSPACING=0 STYLE="margin: 5 5 0 5;">

<?

$tab = "<TABLE BORDER=0 WIDTH=350><TR BGCOLOR=#eeeeee><TD></TD><TD>Nom</TD><TD>Gestionnaire</TD>";

$tab_c = array();
$champ_amis = explode(',', $sess_context->championnat['friends']);
foreach($champ_amis as $c) $tab_c[$c] = $c;

$select = "SELECT * FROM jb_championnat WHERE demo=0 AND actif=1 ".(isset($filtre) && $filtre != "" ? " AND nom LIKE '".$filtre."%'" : "")." ORDER BY nom";
$res = dbc::execSQL($select);
while($row = mysql_fetch_array($res))
{
	$tab .= "<TR onmouseover=\"javascript:this.bgcolor='red';\" onmouseout=\"javascript:this.bgcolor='none';\"><TD><INPUT TYPE=CHECKBOX NAME=cb_championnat_".$row['id']." /></TD><TD>".$row['nom']."</TD><TD>".$row['gestionnaire']."</TD>";
}
	
$tab .= "</TABLE>";

?>

<TR><TD><TABLE BORDER=0><TR><TD WIDTH=30><TABLE BORDER=0 BGCOLOR=#DDDDDD HEIGHT=30 WIDTH=30 STYLE="border: 1px solid #AAAAAA;"><TR><TD> </TD></TABLE></TD><TD><FONT SIZE=2><U> Choix des championnats </U></FONT></TD></TABLE></TD>
<TR><TD ALIGN=CENTER COLSPAN=5><HR></TD>
<TR><TD ALIGN=CENTER COLSPAN=5>
<A CLASS=blue HREF=championnat_select.php?filtre=A&friends=<?= $friends ?>>A</A>
<A CLASS=blue HREF=championnat_select.php?filtre=B&friends=<?= $friends ?>>B</A>
<A CLASS=blue HREF=championnat_select.php?filtre=C&friends=<?= $friends ?>>C</A>
<A CLASS=blue HREF=championnat_select.php?filtre=D&friends=<?= $friends ?>>D</A>
<A CLASS=blue HREF=championnat_select.php?filtre=E&friends=<?= $friends ?>>E</A>
<A CLASS=blue HREF=championnat_select.php?filtre=F&friends=<?= $friends ?>>F</A>
<A CLASS=blue HREF=championnat_select.php?filtre=G&friends=<?= $friends ?>>G</A>
<A CLASS=blue HREF=championnat_select.php?filtre=H&friends=<?= $friends ?>>H</A>
<A CLASS=blue HREF=championnat_select.php?filtre=I&friends=<?= $friends ?>>I</A>
<A CLASS=blue HREF=championnat_select.php?filtre=J&friends=<?= $friends ?>>J</A>
<A CLASS=blue HREF=championnat_select.php?filtre=K&friends=<?= $friends ?>>K</A>
<A CLASS=blue HREF=championnat_select.php?filtre=L&friends=<?= $friends ?>>L</A>
<A CLASS=blue HREF=championnat_select.php?filtre=M&friends=<?= $friends ?>>M</A>
<A CLASS=blue HREF=championnat_select.php?filtre=N&friends=<?= $friends ?>>N</A>
<A CLASS=blue HREF=championnat_select.php?filtre=O&friends=<?= $friends ?>>O</A>
<A CLASS=blue HREF=championnat_select.php?filtre=P&friends=<?= $friends ?>>P</A>
<A CLASS=blue HREF=championnat_select.php?filtre=Q&friends=<?= $friends ?>>Q</A>
<A CLASS=blue HREF=championnat_select.php?filtre=R&friends=<?= $friends ?>>R</A>
<A CLASS=blue HREF=championnat_select.php?filtre=S&friends=<?= $friends ?>>S</A>
<A CLASS=blue HREF=championnat_select.php?filtre=T&friends=<?= $friends ?>>T</A>
<A CLASS=blue HREF=championnat_select.php?filtre=U&friends=<?= $friends ?>>U</A>
<A CLASS=blue HREF=championnat_select.php?filtre=V&friends=<?= $friends ?>>V</A>
<A CLASS=blue HREF=championnat_select.php?filtre=W&friends=<?= $friends ?>>W</A>
<A CLASS=blue HREF=championnat_select.php?filtre=X&friends=<?= $friends ?>>X</A>
<A CLASS=blue HREF=championnat_select.php?filtre=Y&friends=<?= $friends ?>>Y</A>
<A CLASS=blue HREF=championnat_select.php?filtre=Z&friends=<?= $friends ?>>Z</A>
<A CLASS=blue HREF=championnat_select.php?filtre=&friends=<?= $friends ?>>#</A>
</TABLE></TD>
<TR><TD>
	<TABLE BORDER=0>
		<TR><TD WIDTH=30> </TD><TD><?= $tab ?></TD>
<TR><TD ALIGN=RIGHT COLSPAN=5><INPUT TYPE=SUBMIT NAME=ajouter VALUE=Ajouter></TD>

</TABLE></TD>

</CENTER>

</FORM>

<script>
nb_sel=window.opener.document.forms[0].ch_friends.length;
for(i=1; i < nb_sel; i++)
{
	txt=window.opener.document.forms[0].ch_friends.options[i].text;
	val=window.opener.document.forms[0].ch_friends.options[i].value;
	document.forms[0].elements['cb_championnat_'+val].checked = true;
}
</script>

<? TemplateBox :: htmlEnd(); ?>
