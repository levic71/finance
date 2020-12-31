<?

include "../include/sess_context.php";

session_start();

$jorkyball_redirect_exception = 1;

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$_SESSION['antispam'] = ToolBox::getRand(5);

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

?>

<form action="sms_submit.php" method="post">

<div id="pageint">

<h2> Envoyer un SMS - [EXPERIMENTAL]</h2>

Envie d'envoyer un message message sur le ticker du Jorkers.com (anniversaire, annonce événement, joke, etc), c'est simple,
il suffit de remplir le formulaire ci-dessous avec un pseudo, un message et de choisir une date et heure de publication.
Si l'horaire choisit n'est pas plein (10 messages par 1/4 d'heure), alors le message est accepté. Un message par personne par plage horaire.
<br />
<br />
Si vous souhaitez avertir quelqu'un de la diffusion de votre message, il faut saisir leurs emails dans le champ "Invitation" séparer par des ";"
<br />
<br />

<table id="inscription" border="0" cellpadding="0" cellspacing="0" width="670" summary="tab central">

<?

$row = array();
$row['sms_invitation'] = isset($sms_invitation) ? $sms_invitation : (isset($sms_cookie_invitation) ? $sms_cookie_invitation : "");
$row['sms_pseudo']     = isset($sms_pseudo)     ? $sms_pseudo     : (isset($sms_cookie_pseudo) ? $sms_cookie_pseudo : "");
$row['sms_msg']        = isset($sms_msg)        ? $sms_msg        : "";
$row['sms_jour']       = isset($sms_jour)       ? $sms_jour       : date("d/m/Y");
$row['sms_heure']      = isset($sms_heure)      ? $sms_heure      : date("G");
$row['sms_minute']     = isset($sms_minute)     ? $sms_minute     : date("i");
if ($row['sms_minute'] < 10) $row['sms_minute'] = 15;
else if ($row['sms_minute'] >= 10 && $row['sms_minute'] < 25) $row['sms_minute'] = 30;
else if ($row['sms_minute'] >= 25 && $row['sms_minute'] < 40) $row['sms_minute'] = 45;
else if ($row['sms_minute'] >= 40 && $row['sms_minute'] < 55) { $row['sms_minute'] = "0"; $row['sms_heure']++; }
else if ($row['sms_minute'] >= 55) { $row['sms_minute'] = "15"; $row['sms_heure']++; }
if ($row['sms_heure'] == 24) $row['sms_heure'] = 0;

$tab = array();

$tab[] = array("Pseudo:",  "<input type=\"text\" name=\"sms_pseudo\" size=\"32\" maxlength=\"16\" value=\"".$row['sms_pseudo']."\" ".($row['sms_pseudo'] == "" ? "style='background-color: #FFCCCC'" : "")." onkeyup='javascript:changeColor(this);' />");
$tab[] = array("Message:", "<input type=\"text\" name=\"sms_msg\"    size=\"64\" maxlength=\"48\" value=\"".$row['sms_msg']."\" ".($row['sms_msg'] == "" ? "style='background-color: #FFCCCC'" : "")." onkeyup='javascript:changeColor(this);' />");
$tab[] = array("Date:",    "<input type=\"text\" name=\"sms_jour\"   size=\"16\" maxlength=\"16\" value=\"".$row['sms_jour']."\" ".($row['sms_jour'] == "" ? "style='background-color: #FFCCCC'" : "")." onkeyup='javascript:changeColor(this);' />");

$input  = "<select name=\"sms_heure\">";
for($i=0; $i < 24; $i++)
	$input .= "<option value=\"".$i."\" ".($row['sms_heure'] == $i ? "selected=\"selected\"" : "")."> ".($i < 10 ? "0" : "").$i." </option>";
$input .= "</select>";
$tab[] = array("Heure:", $input);

$input  = "<select name=\"sms_minute\">";
for($i=0; $i < 4; $i++)
	$input .= "<option value=\"".($i * 15)."\" ".($row['sms_minute'] == ($i * 15) ? "selected=\"selected\"" : "")."> ".($i < 1 ? "0" : "").($i * 15)." </option>";
$input .= "</select>";
$tab[] = array("Minute:", $input);

$tab[] = array("Invitation:", "<input type=\"text\" name=\"sms_invitation\" size=\"64\" maxlength=\"255\" value=\"".$row['sms_invitation']."\" />");

$tab[] = array("Zone de contrôle:<br /><span style=\"font-weight: normal;\">[Reportez le code de l'image dans le champ de saisie]</span>", "<table border=0><tr valign=\"center\"><td><input type=\"text\" name=\"sms_controle\" size=\"32\" maxlength=\"16\" value=\"\" style=\"background-color: #FFCCCC;\" onkeyup='javascript:changeColor(this);' /></td><td><img src=\"../include/codeimage.php\" /></td></tr></table>");

echo "<tr><td>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Formulaire", "center");
$fxlist->FXSetColumnsAlign(array("right", "left"));
$fxlist->FXSetColumnsColor(array("#BCC5EA", ""));
$fxlist->FXSetColumnsWidth(array("25%", ""));
$fxlist->FXDisplay();
echo "</td></tr>";

if (isset($errno) && $errno == 0) Toolbox::alert('SMS envoyé');
if (isset($errno) && $errno == 1) Toolbox::alert('La saisie de la zone de controle est erronée, veuillez recommencer');
if (isset($errno) && $errno == 2) Toolbox::alert('La plage horaire sélectionnée est saturée, veuillez saisir un autre horaire');
if (isset($errno) && $errno == 3) Toolbox::alert('Vous avez déjà envoyé un sms sur cette plage horaire, veuillez saisir un autre horaire');
if (isset($errno) && $errno == 4) Toolbox::alert('Erreur dans la saisie des champs');

?>

<tr><td align="right">
<input type="submit" value="Valider" onclick="return checkForm(1, 0);" /></td>
</tr>

</table>
<br />

<span style="border: 1px solid #777777;background: #777777; color: white; font-weight: bold;">&nbsp;Note:</span><span style="border: 1px solid #777777; color: #777777; font-weight: bold;">&nbsp;Ce service se veut convivial, merci de ne pas envoyer de message à caractère injurieux, raciste, xénophobe, ...&nbsp;</span>
<br />
<br />

&#187; <a href="sms_historique.php">Historique des sms</a>
<br />
<br />

</div>

</form>

<script type="text/javascript">
// <![CDATA[
function checkForm()
{
	if (verif_alphanumext(document.forms[0].sms_pseudo.value, 'Pseudo', -1) == false)
		return false;
	if (verif_alphanumext(document.forms[0].sms_msg.value, 'Message', -1) == false)
		return false;
	if (verif_alphanumext(document.forms[0].sms_date.value, 'Jour', -1) == false)
		return false;
	if (verif_alphanumext(document.forms[0].sms_controle.value, 'Controle', -1) == false)
		return false;

    return true;
}
// ]]>
</script>


<? $menu->end(); ?>
