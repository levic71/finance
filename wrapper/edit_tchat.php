<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$idp = isset($_REQUEST['idp']) && is_numeric($_REQUEST['idp']) && $_REQUEST['idp'] > 0 ? $_REQUEST['idp'] : 0;
$modifier = $idp > 0 ? true : false;

$dual = isset($dual) ? $dual : 0;
$real_championnat = $dual == 2 || $dual == 3 || $dual == 5 ? 0 : $sess_context->getRealChampionnatId();

if (($dual == 5 || $dual == 2 || $dual == 3) && isset($sess_context) && $sess_context->getRealChampionnatId() == 0)
{
	$real_championnat = 0;
	$champ['championnat_id']   = 0;
	$champ['championnat_nom']  = "Forum général";
	$champ['type'] = 0;
	$sess_context->setChampionnat($champ);
}

if ($modifier) {
	$sql = "SELECT *, SUBSTRING_INDEX(title, '}', -1) title FROM jb_forum WHERE id_champ=".$real_championnat." AND id=".$idp;
	$res = dbc::execSQL($sql);
	$msg = mysqli_fetch_array($res);

	// MAJ du compteur de lecture sur le msg initial
	$update = "UPDATE jb_forum SET nb_lectures=".($msg['nb_lectures']+1)." WHERE id=".$msg['id']." AND id_champ=".$real_championnat;
	$res = dbc::execSQL($update);
}

$nom         = $modifier && false ? $msg['nom']     : ($sess_context->isUserConnected() ? $sess_context->user['pseudo'] : (isset($pseudo_forum) ? $pseudo_forum : ""));
$photo       = $modifier && false ? $msg['image']   : "";
$message     = $modifier && false ? $msg['message'] : "";
$sujet       = $modifier && false ? $msg['title']   : "";
$email       = $modifier && false ? $msg['email']   : ($sess_context->isUserConnected() ? $sess_context->user['email'] : (isset($email_forum) ? $email_forum : ""));

?>

<div id="edit_tchat" class="edit">

<input type="hidden" id="max_file_size" value="100000" />

<? if ($modifier) { ?>
<h2 class="grid">Message(s)</h2>
<table cellspacing="0" cellpadding="0" class="jkgrid">
<tbody>
<? getHistoriqueMail($msg); ?>
</tbody></table>
<? } ?>

<h2 class="grid"><?= $modifier ? "Répondre" : "Ajout d'un message" ?></h2>

<table cellspacing="0" cellpadding="0" class="jkgrid">
<tbody>
<tr><td class="c1" colspan="2"><div><label for="nom">Nom</label><input type="text" name="nom" id="nom" value="<?= $nom ?>" /></div></td></tr>
<? if (!$modifier) { ?>
<tr><td class="c1" colspan="2"><div><label for="nom">Sujet</label><input type="text" name="sujet" id="sujet" value="<?= $sujet ?>" /></div></td></tr>
<? } else { ?>
<input type="hidden" name="sujet" id="sujet" value="<?= $sujet ?>" />
<? } ?>
<tr><td class="c1" colspan="2"><div><label for="email">Email</label><input type="text" name="email" id="email" value="<?= $email ?>" /><small>[Restez informé !]</small></div></td></tr>
<tr><td class="c1" colspan="2"><div><label for="message">Message</label><textarea name="message" id="message" cols="36" rows="6"><?= $message ?></textarea></div></td></tr>

<tr><td class="c1" colspan="2"><div>
	<label for="photo">Image</label>

	<ul class="upload_target"><li><img style="width: 32px; height: 32px;" id="img_target" src="<?= $photo != "" ? $photo : "img/usersxxl.png" ?>" /></li><li id="f1_upload_process"><img src="img/loader.gif" /></li><li id="f1_upload_ok"><img src="img/tick_32.png" /></li><li id="f1_upload_err"><img src="img/block_32.png" /></li></ul>
	<input type="hidden" name="photo" id="photo" value="<?= $photo ?>" />

	<form name="uploadform" action="upload.php?target_image=img_target&target_upload=photo&tchat=1" style="clear: both;" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="startUpload();" >
		<span id="f1_upload_form" align="center">
			<label for="myfile">&nbsp;</label><input name="myfile" id="myfile" type="file" size="30" /><button onclick="startUpload();" class="button blue">Upload</button><small>< 100ko</small>
		</span>
		<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
	</form>

</div></td></tr>
</tbody></table>

<? if (!$modifier) { ?>
<table cellspacing="0" cellpadding="0" class="jkgrid">
<tr>
	<td class="c2"><div><label for="diffusion_joueurs">Diffusion vers joueurs</label></div></td>
	<td class="c3"><div id="diffusion_joueurs" class="grouped"></div></td>
</tr>
</table>

<table cellspacing="0" cellpadding="0" class="jkgrid">
<tr>
	<td class="c2"><div><label for="diffusion_webmaster">Copie webmaster</label></div></td>
	<td class="c3"><div id="diffusion_webmaster" class="grouped"></div></td>
</tr>
</table>

<table cellspacing="0" cellpadding="0" class="jkgrid">
<tr><td class="c1" colspan="2"><div><label for="autres_email">Autres emails</label><input type="text" name="autres_email" id="autres_email" value="" /></div></td></tr>
</tbody></table>
<? } ?>


<div class="actions grouped_inv">
<button onclick="return validate_and_submit();" class="button green"><?= $modifier ? "Répondre" : "Envoyer" ?></button>
<button onclick="return annuler();" class="button gray">Annuler</button>
</div>

<script>

<? if (!$modifier) { ?>
choices.build({ name: 'diffusion_joueurs', c1: 'blue', c2: 'white', values: [ {v: 0, l: 'Oui'}, {v: 1, l: 'Non', s: true} ] });
choices.build({ name: 'diffusion_webmaster', c1: 'blue', c2: 'white', values: [ {v: 0, l: 'Oui'}, {v: 1, l: 'Non', s: true} ] });
<? } ?>

validate_and_submit = function()
{
    if (!check_alphanumext(valof('nom'), 'Nom', -1))
		return false;

<? if (!$modifier) { ?>
    if (!check_alphanumext(valof('sujet'), 'Sujet', -1))
		return false;
<? } ?>

	if (valof('email') != '')
	{
		if (!check_alphanumext(valof('email'), 'Email', -1))
			return false;
		if (!check_email(valof('email')))
			return false;
	}

    if (!check_alphanumext(valof('message'), 'Message', -1))
		return false;

	if (valof('photo') != '')
	{
		items = valof('photo').split('.');
		if (items[(items.length-1)] != 'gif' && items[(items.length-1)] != 'GIF' && items[(items.length-1)] != 'jpg' && items[(items.length-1)] != 'JPG' && items[(items.length-1)] != 'JPEG' && items[(items.length-1)] != 'jpeg')
		{
			alert('Le format de l\'image doit être \'gif\' ou \'jpg\'.');
			return false;
		}
	}

	var msg = '&message='+attrs(['message']).replace(/\n/g, '<br />\n');
<? if (!$modifier) { ?>
	params = '?diffusion_joueurs='+choices.getSelection('diffusion_joueurs')+'&diffusion_webmaster='+choices.getSelection('diffusion_webmaster')+attrs(['nom', 'sujet', 'email', 'photo', 'autres_email'])+msg;
<? } else { ?>
	params = '?'+attrs(['nom', 'sujet', 'email', 'photo'])+msg;
<? } ?>
	xx({action:'tchat', id:'main', url:'edit_tchat_do.php'+params+'&idp='+<?= $idp ?>+'&upd=<?= $modifier ? 1 : 0 ?>'});

	return true;
}
annuler = function()
{
	mm({action: 'tchat'});
	return true;
}
<? if (!$modifier) { ?>
mandatory(['nom', 'sujet', 'message']); fs('nom');
<? } else { ?>
mandatory(['nom', 'message']); fs('nom');
<? } ?>
</script>

</div>


<?

function formatMessage($msg_origine, $msg_init)
{
	global $sess_context, $indice_msg;

	$lib  = "";
	$lib .= "
<tr id=\"msg_".$msg_init['id']."\"><td class=\"c1\" colspan=\"2\"><div>
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
		<span class=\"number\">#".$indice_msg++."</span>
".($sess_context->isAdmin() ? ($msg_init['in_response'] != 0 ? "<button class=\"button small red\" onclick=\"go({action: 'tchat', id:'main', url:'edit_tchat_do.php?rep=1&idp=".$msg_init['id']."', confirmdel:'1'});\">Supprimer la réponse</button>" : "<button class=\"button small red\" onclick=\"go({action: 'tchat', id:'main', url:'edit_tchat_do.php?rep=0&idp=".$msg_init['id']."', confirmdel:'1'});\">Tout supprimer</button>") : "")."
	</div>
</div>
</div></td></tr>
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
		$msg_init = mysqli_fetch_array($res);
	}
	else
		$msg_init = $msg_origine;

	echo formatMessage($msg_origine, $msg_init);

	$select = "SELECT *, SUBSTRING_INDEX(title, '}', -1) title FROM jb_forum WHERE id_champ=".$real_championnat." AND in_response=".$msg_init['id']." AND del=0 ORDER BY date ASC";
	$res = dbc::execSQL($select);
	if (mysqli_num_rows($res) > 0)
	{
		while($msg = mysqli_fetch_array($res))
			echo formatMessage($msg_origine, $msg);
	}

	return $tab;
}

?>