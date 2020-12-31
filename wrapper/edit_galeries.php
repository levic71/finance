<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

// On récupère les infos de la saison
$sss = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());
$saison = $sss->getSaison();

$idg = isset($_REQUEST['idg']) && is_numeric($_REQUEST['idg']) && $_REQUEST['idg'] > 0 ? $_REQUEST['idg'] : 0;
$modifier = $idg > 0 ? true : false;

$thumbnail = "";
if ($modifier) {
	$select = "SELECT * FROM jb_albums_themes WHERE id=".$idg;
	$res = dbc::execSQL($select);
	$theme = mysqli_fetch_array($res);

	$sas = new SQLAlbumsThemesServices($sess_context->getRealChampionnatId(), $idg);
	$photos = $sas->getPhotos();
	$select_photos = "";
	foreach($photos as $p) {
		$select_photos .= ($select_photos == "" ? "" : ",").$p['photo'];
		$thumbnail .= "<img class=\"button gray\" onclick=\"removeImg(this, '".$p['photo']."', 'photo');\" src=\"".$p['photo']."\" />";
	}
}

$e_nom   = $modifier ? $theme['nom'] : "";
$e_date  = $modifier ? ToolBox::mysqldate2date($theme['date']) : date('d/m/Y');
$e_photo = $modifier ? $select_photos : "";

?>

<input type="hidden" id="max_file_size" value="100000" />


<div id="edit_teams" class="edit">

<h2 class="grid"><?= $modifier ? "Modification" : "Ajout" ?> d'une galerie photos</h2>

<table cellspacing="0" cellpadding="0" class="jkgrid">
<tbody>
<tr><td class="c1" colspan="2"><div><label for="nom">Nom</label><input type="text" name="nom" id="nom" value="<?= $e_nom ?>" /></div></td></tr>

<tr>
	<td class="c2"><div><label for="zone_calendar">Date</label></div></td>
	<td class="c3"><div><input type="text" name="zone_calendar" id="zone_calendar" value="<?= $e_date ?>" /><small>JJ/MM/AAAA</small></div></td>
</tr>

<tr><td class="c1" colspan="2"><div>
	<label for="photo">Images</label>

	<ul class="upload_target"><li id="img_target" class="thumbnail"><?= $thumbnail ?></li><li id="f1_upload_process"><img src="img/loader.gif" /></li><li id="f1_upload_ok"><img src="img/tick_32.png" /></li><li id="f1_upload_err"><img src="img/block_32.png" /></li></ul>
	<input type="hidden" name="photo" id="photo" value="<?= $e_photo ?>" />

	<form name="uploadform" action="upload.php?target_image=img_target&target_upload=photo&album=1&multi=1" style="clear: both;" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="startUpload();" >
		<span id="f1_upload_form" align="center">
			<label for="myfile">&nbsp;</label><input name="myfile" id="myfile" type="file" size="30" /><button onclick="startUpload();" class="button blue">Upload</button><small>< 100ko</small>
		</span>
		<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
	</form>

</div></td></tr>

</table>

<div class="actions grouped_inv">
<button onclick="return validate_and_submit();" class="button green"><?= $modifier ? "Valider" : "Ajouter" ?></button>
<button onclick="return annuler();" class="button gray">Annuler</button>
</div>

<script>
validate_and_submit = function()
{
    if (!check_alphanumext(valof('nom'), 'Nom', -1))
		return false;

    if (!check_JJMMAAAA(el('zone_calendar').value, 'Date'))
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

	params = '?'+attrs(['nom', 'zone_calendar', 'photo']);
	xx({action:'photos', id:'main', url:'edit_galeries_do.php'+params+'&idg='+<?= $idg ?>+'&upd=<?= $modifier ? 1 : 0 ?>'});

	return true;
}
annuler = function()
{
	mm({action: 'photos', idg: <?= $idg ?> });
	return true;
}
mandatory(['nom', 'zone_calendar']); fs('nom');
</script>

</div>