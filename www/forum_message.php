<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

if (!isset($dual)) $dual = 0;

$real_championnat = $dual == 2 || $dual == 3 || $dual == 5 ? 0 : $sess_context->getRealChampionnatId();

if (($dual == 5 || $dual == 2 || $dual == 3) && isset($sess_context) && $sess_context->getRealChampionnatId() == 0)
{
	$real_championnat = 0;
	$champ['championnat_id']   = 0;
	$champ['championnat_nom']  = "Forum général";
	$champ['type'] = 0;
	$sess_context->setChampionnat($champ);
	$menu = new menu("forum_access");
}
else
	$menu = new menu("full_access");

$menu->debut($sess_context->getChampionnatNom(), "", "initEditor()");

if (isset($id_msg))
{
	$select = "SELECT *, SUBSTRING_INDEX(title, '}', -1) title FROM jb_forum WHERE id_champ=".$real_championnat." AND id=".$id_msg;
	$res = dbc::execSQL($select);
	$row = mysql_fetch_array($res);

	// MAJ du compteur de lecture sur le msg initial
	$update = "UPDATE jb_forum SET nb_lectures=".($row['nb_lectures']+1)." WHERE id=".$row['id']." AND id_champ=".$real_championnat;
	$res = dbc::execSQL($update);
}

?>

<script src="../js/smileysv10.js" type="text/javascript"></script>

<style type="text/css">
#popmenu {
	z-index: 100;
}
.FXList_CAPTION {
	height: 18px;
	font-size: 12px;
	padding: 2px 0px 0px 0px;
}
#formmsg td {
	border: 0px solid white;
}
#formmsg {
	border: 1px solid #AAAAAA;
}
</style>

<form action="forum_message_do.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="50000" />
<input type="hidden" name="smiley" value="" />
<input type="hidden" name="dual" value="<?= $dual ?>" />

<table id="forum_page" border="0" cellpadding="0" cellspacing="0" width="700" summary="tab central">

<?

$indice_msg = 1;

function formatMessage($msg_origine, $msg_init)
{
	global $sess_context, $indice_msg;

	$lib  = "";
	$lib .= "
<div class=\"bloc_forum\">
	<div class=\"smiley\"><img src=\"".$msg_init['smiley']."\"></div>
	<div class=\"reponse\">
		<div class=\"auteur\">
			".($msg_init['in_response'] == 0 ? "<span class=\"titre\">\"".$msg_init['title']."\"</span>, " : "")." ".($msg_init['id'] == $msg_origine['id'] ? "par " : "Réponse de ")." <a href=\"#\" title=\"[".$msg_init['ip']."][".$msg_init['agent']."]\"class=\"blue_none\">".$msg_init['nom']."</a><span class=\"date\">, le ".ToolBox::mysqltime2time($msg_init['date'])."</span>
		</div>
		<div class=\"message\">
			".str_replace("<br>", "<br />", nl2br($msg_init['message']))."
		</div>
".($msg_init['image'] != "" ? "<div class=\"image\"><img src=\"".$msg_init['image']."\" alt=\"\" /></div>" : "")."
".($sess_context->isAdmin() ? "<div class=\"admin\">".($msg_init['id'] != $msg_origine['id'] ? "<input type=\"submit\" value=\"Supprimer la réponse\" onclick=\"javascript:return del_submessage(".$msg_init['id'].");\" />" : "<input type=\"submit\" value=\"Supprimer tous les messages\" onclick=\"javascript:del_submessage(".$msg_init['id'].");\" />")."</div>" : "")."
		<span style=\"float:right; font-weight:normal; font-size: 10px; color: #AAAAAA;\">#".$indice_msg++."</span>
	</div>
</div>	
	";

	return $lib;
}

function getHistoriqueMail($msg_origine)
{
	global $sess_context, $real_championnat;
	
	$tab = array();

	// On regarde si le msg à afficher est le message initial
	if ($msg_origine['in_response'] != 0)
	{
		$select = "SELECT *, SUBSTRING_INDEX(title, '}', -1) title FROM jb_forum WHERE id_champ=".$real_championnat." AND id=".$msg_origine['in_response'];
		$res = dbc::execSQL($select);
		$msg_init = mysql_fetch_array($res);
	}
	else
		$msg_init = $msg_origine;

	$tab[] = array(formatMessage($msg_origine, $msg_init));

	$select = "SELECT *, SUBSTRING_INDEX(title, '}', -1) title FROM jb_forum WHERE id_champ=".$real_championnat." AND in_response=".$msg_init['id']." AND del=0 ORDER BY date ASC";
	$res = dbc::execSQL($select);
	if (mysql_num_rows($res) > 0)
	{
		while($msg = mysql_fetch_array($res))
			$tab[] = array(formatMessage($msg_origine, $msg));
	}
	
	return $tab;
}


if (isset($id_msg)) { ?>
	<INPUT TYPE=HIDDEN NAME=id_msg_rep VALUE=<?= $row['in_response'] == 0 ? $id_msg : $row['in_response'] ?> />
<?
	echo "<tr><td align=\"right\"><a  class=\"link_bas\" href=\"#bottom\">Vers le bas</a><a name=\"top\"></a></td></tr>";
	echo "<TR><TD>";
	$fxlist = new FXListPresentation(getHistoriqueMail($row));
	$fxlist->FXSetTitle("Message", "CENTER");
	$fxlist->FXSetColumnsAlign(array("left"));
	$fxlist->FXSetMouseOverEffect(false);
	$fxlist->FXDisplay();
	echo "</td></tr>";
	echo "<tr><td align=\"right\"><a class=\"link_haut\" href=\"#top\">Vers le haut</a><a name=\"bottom\"></a></td></tr>";
}
	
$pseudo_forum = isset($pseudo_forum) ? str_replace('\\\'', '\'', $pseudo_forum) : "";
$item = "
<div>
	<div id=\"smileys_choice\">
		<div style=\"float:left; margin-right: 10px;\"><a href=\"#\" onclick=\"show_smileys(event, linkset);\" onmouseout=\"delayhidemenu()\"><img name=\"smiley\" src=\"../forum/smileys/smile.gif\" border=\"0\" alt=\"\" /></a></div>
		<div><= Cliquer sur le smiley pour le modifier</div>
	</div>
	<div id=\"smileys_area\" style=\"display: none;\">
	</div>
	<div id=\"smileys_radio\" style=\"display: none;\" class=\"forum_c\">
			<input TYPE=\"radio\" name=\"smileys_r\" onclick=\"javascript:change_smileys(event, linkset_classique);\" checked />Classique
			<input TYPE=\"radio\" name=\"smileys_r\" onclick=\"javascript:change_smileys(event, linkset_buddys1);\" />Buddys1
			<input TYPE=\"radio\" name=\"smileys_r\" onclick=\"javascript:change_smileys(event, linkset_buddys2);\" />Buddys2
			<input type=\"radio\" name=\"smileys_r\" onclick=\"javascript:change_smileys(event, linkset_buddys3);\" />Buddys3
	</div>
</div>
";

$tab = array();
$tab[] = array("Smiley:", $item);
$tab[] = array("Nom:", "<input type=\"text\" name=\"msg_nom\" size=\"64\" maxlength=\"32\" value=\"".$pseudo_forum."\" onkeyup='javascript:changeColor(this);' ".($pseudo_forum != "" ? "" : "STYLE='background-color: #FFCCCC'")." />");
if (isset($id_msg)) // Réponse à un message
{
    $def = stristr($row['title'], "[re]") ? $row['title'] : "[re]".$row['title'];
    $lib = "<INPUT TYPE=TEXT NAME=msg_titre SIZE=64 MAXLENGTH=64 VALUE=\"".$def."\" onKeyUp='javascript:changeColor(this);' ".($def == "" ? "STYLE='background-color: #FFCCCC'>" : "");
}
else
    $lib = (!isset($id_msg)) ? "<INPUT TYPE=TEXT NAME=msg_titre SIZE=64 MAXLENGTH=64 onKeyUp='javascript:changeColor(this);' STYLE='background-color: #FFCCCC'>" : $row['title'];
$tab[] = array("Titre:", $lib);
$tab[] = array("Email:", "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 SUMMARY=\"\"><TR><TD><INPUT TYPE=TEXT NAME=msg_email SIZE=64 MAXLENGTH=64 VALUE=\"".(isset($email_forum) ? $email_forum : "")."\"></TD><TR><TD><FONT CLASS=classic>[Pour être informé(e) des évolutions de ce message, saisissez votre email]</FONT></TD></TABLE>");
//    $tab[] = array("Message:", "<TEXTAREA COLS=60 ROWS=6 id=msg_description  name=msg_description onKeyUp='javascript:changeColor(this);' STYLE='background-color: #FFCCCC'></TEXTAREA>");
$tab[] = array("Message:", "<TEXTAREA COLS=70 ROWS=10 id=ta name=ta onKeyUp='javascript:changeColor(this);'></TEXTAREA>");
$tab[] = array("Image:", "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 SUMMARY=\"\"><TR><TD><INPUT TYPE=FILE NAME=msg_image SIZE=32 MAXLENGTH=64 VALUE=\"\" /></TD><TD><FONT CLASS=classic>&nbsp;[50 Ko max]</FONT></TD></TABLE>");
if ($real_championnat != 0)
    $tab[] = array("Diffusion:", "<TABLE BORDER=0 SUMMARY=\"\"><TR><TD><INPUT TYPE=CHECKBOX NAME=msg_diffusion VALUE=1 /> Tous les joueurs </TD><TD><INPUT TYPE=CHECKBOX NAME=msg_webmaster VALUE=1 /> Webmaster</TD><TD COLSPAN=2>Copie à : <INPUT TYPE=TEXT SIZE=32 MAXLENGTH=64 NAME=msg_copie /></TABLE>");

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle($dual == 2 || $dual == 3 ? "Commentaire" : (isset($id_msg) ? "Réponse" : "Message"), "center");
$fxlist->FXSetColumnsAlign(array("right", "left"));
$fxlist->FXSetColumnsWidth(array("25%", ""));
$fxlist->FXSetColumnsColor(array("#CCCCCC", ""));
$fxlist->FXSetNbCols(2);
$fxlist->FXSetMouseOverEffect(false);
$fxlist->FXSetTableId("formmsg");
$fxlist->FXDisplay();
echo "</TD>";

?>
<TR><TD ALIGN=right><TABLE BORDER=0 SUMMARY="">
<TR><TD ALIGN=left><INPUT TYPE=SUBMIT VALUE="Envoyer" onclick="return checkForm();" /></TD>
	<TD ALIGN=left><INPUT TYPE=SUBMIT VALUE="Annuler" onclick="return back();" /></TD>
<? if (isset($id_msg)) { ?>
	<TD ALIGN=left><INPUT TYPE=SUBMIT VALUE="Nouveau message" onclick="return nouveaumsg();" /></TD>
<? } ?>
</TABLE></TD>


</TABLE>
</FORM>

<SCRIPT type="text/javascript">

linkset=linkset_classique;

function onclic(image, event)
{
	document.images['smiley'].src=image.src;
	show_smileys(event);
}
function onover(elt)
{
	elt.style.background='#D5D9EA';
	elt.style.border='dashed black 1px';
}
function onout(elt)
{
	elt.style.background='';
	elt.style.border='dashed white 1px';
}
function change_smileys(event, smileys)
{
    document.getElementById("smileys_area").innerHTML=smileys;
    linkset = smileys;
}
function show_smileys(event, smileys)
{
	if (document.getElementById("smileys_area").style.display == "none")
	{
		document.getElementById("smileys_area").style.display = '';
		document.getElementById("smileys_radio").style.display = '';
	    document.getElementById("smileys_area").innerHTML=smileys;
	    linkset = smileys;
	}
	else
	{
		document.getElementById("smileys_area").style.display = 'none';
		document.getElementById("smileys_radio").style.display = 'none';
	}
}
function back()
{
	document.forms[0].action='forum.php';
	document.forms[0].submit();
}
function nouveaumsg()
{
	document.forms[0].action='forum_message.php';
	document.forms[0].submit();
}
function del_submessage(id)
{
	document.forms[0].action='forum_delmsg_do.php?id_msg2del='+id;
	document.forms[0].submit();
}
function checkForm()
{
<? if ($lock != 0) { ?>
	alert('Le site est en cours de maintenance, cette fonctionnalité est indisponible pour l\'instant ...');
	return false;
<? } ?>

	if (document.forms[0].msg_nom.value.length == 0)
	{
		alert('Le champ <nom> doit être rempli ...');
		return false;
	}
	document.forms[0].msg_nom.value = upperFirstLetter(document.forms[0].msg_nom.value);

	if (document.forms[0].msg_titre.value.length == 0)
	{
		alert('Le champ <titre> doit être rempli ...');
		return false;
	}
	document.forms[0].msg_titre.value = upperFirstLetter(document.forms[0].msg_titre.value);

	document.forms[0].smiley.value = document.images['smiley'].src;
	document.forms[0].msg_titre.disabled=false;

	return true;
}
<? if (isset($id_msg)) { ?>
obj = document.forms[0].msg_titre;
obj.disabled=true;
obj.style.color='black';
obj.style.background='#EFEFEF';
<? } ?>
</SCRIPT>

<? $menu->end(); ?>
