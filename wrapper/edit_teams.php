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

// On récupère tous les joueurs du championnat
$select = "SELECT * FROM jb_joueurs WHERE id_champ=".$sess_context->getRealChampionnatId()." ".($saison['joueurs'] == "" ? "" : "AND id IN (".SQLServices::cleanIN($saison['joueurs']).")")." ORDER BY nom ASC";
$res_joueurs = dbc::execSQL($select);

// On rejette si aucun joueur existant sauf pour les championnats de type 'Championnats/Tournois'
if (mysqli_num_rows($res_joueurs) < 2 && $sess_context->getChampionnatType() == _TYPE_LIBRE_) { ?> <script>mm({action: 'teams'}); $dMsg({ msg: 'Vous devez saisir des joueurs avant de créer des équipes ...' });</script> <? }
$no_joueurs = (mysqli_num_rows($res_joueurs) == 0) ? true : false;

while($row = mysqli_fetch_array($res_joueurs))
	$joueurs[$row['id']] = $row;

$idt = isset($_REQUEST['idt']) && is_numeric($_REQUEST['idt']) && $_REQUEST['idt'] > 0 ? $_REQUEST['idt'] : 0;
$modifier = $idt > 0 ? true : false;

if ($modifier) {
	$select = "SELECT * FROM jb_equipes WHERE id=".$idt;
	$res = dbc::execSQL($select);
	$equipe = mysqli_fetch_array($res);
}

$e_nom         = $modifier ? $equipe['nom']          : "";
$e_photo       = $modifier ? $equipe['photo']        : "";
$e_commentaire = $modifier ? $equipe['commentaire']  : "";
$e_nb_joueurs  = $modifier ? $equipe['nb_joueurs']   : 0;
$e_joueurs     = $modifier ? $equipe['joueurs']      : "";
$e_capitaine   = $modifier ? $equipe['capitaine']    : 0;
$e_adjoint     = $modifier ? $equipe['adjoint']      : 0;

?>

<input type="hidden" id="max_file_size" value="100000" />
<input type="hidden" id="joueurs" value="<?= $e_joueurs ?>" />
<input type="hidden" id="selection"    value="" />
<input type="hidden" id="nb_selection" value="0" />


<div id="edit_teams" class="edit">

<h2 class="grid"><?= $modifier ? "Modification" : "Ajout" ?> d'une équipe</h2>

<table cellspacing="0" cellpadding="0" class="jkgrid">
<tbody>
<tr><td class="c1" colspan="2"><div><label for="nom">Nom</label><input type="text" name="nom" id="nom" value="<?= $e_nom ?>" /></div></td></tr>

<tr><td class="c1" colspan="2"><div>
	<label for="photo">Photo</label>

	<ul class="upload_target"><li><img style="width: 32px; height: 32px;" id="img_target" src="<?= $e_photo != "" ? $e_photo : "img/usersxxl.png" ?>" /></li><li id="f1_upload_process"><img src="img/loader.gif" /></li><li id="f1_upload_ok"><img src="img/tick_32.png" /></li><li id="f1_upload_err"><img src="img/block_32.png" /></li></ul>
	<input type="hidden" name="photo" id="photo" value="<?= $e_photo ?>" />

	<form name="uploadform" action="upload.php?target_image=img_target&target_upload=photo&teams=1" style="clear: both;" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="startUpload();" >
		<span id="f1_upload_form" align="center">
			<label for="myfile">&nbsp;</label><input name="myfile" id="myfile" type="file" size="30" /><button onclick="startUpload();" class="button blue">Upload</button><small>< 100ko</small>
		</span>
		<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
	</form>

</div></td></tr>

<tr valign="top">
	<td class="c2"><div>
		<label for="selectedplayers">Joueurs <img class="bt" style="margin: 0px 0px -10px 10px;" onclick="<? if ($no_joueurs) { ?>alert('Aucun joueurs dans le championnat !');<? } else { ?>choices.picker('selectedplayers');<? } ?>" src="img/icons/dark/appbar.add.png" /></label>
	</div></td>
	<td class="c3">
		<div id="selectedplayers"></div>
	</td>
</tr>

<tr valign="top"><td class="c2"><div><label for="capitaine">Capitaine</label></div></td><td class="c3"><div id="capitaine"></div></td></tr>
<tr valign="top"><td class="c2"><div><label for="adjoint">Adjoint</label></div></td><td class="c3"><div id="adjoint"></div></td></tr>

<tr><td class="c1" colspan="2"><div><label for="commentaire">Commentaire</label><textarea name="commentaire" id="commentaire" cols="36" rows="3"><?= $e_commentaire ?></textarea></div></td></tr>

<? if ($sess_context->isFreeXDisplay()) { ?><tr><td class="c2"><div></div></td><td class="c3"><div><small>Par défaut, le premier joueur de la liste sera le défenseur, le second l'attaquant.</small></div></td></tr><? } ?>

</tbody></table>

<div class="actions grouped_inv">
<button onclick="return validate_and_submit();" class="button green"><?= $modifier ? "Valider" : "Ajouter" ?></button>
<button onclick="return annuler();" class="button gray">Annuler</button>
</div>

<script>

<?
$selected = array();
if ($e_nb_joueurs > 0) {
	$tmp = explode('|', $e_joueurs);
	foreach($tmp as $j) $selected[$j] = $j;
}
$values = "";
$values2 = "";
$values3 = "";
$existeCapitaine = false;
$existeAdjoint = false;
if (!$no_joueurs)
{
	foreach($joueurs as $j)
	{
		$values .= ($values == "" ? "" : ",")."{ v: ".$j['id'].", l: '".Wrapper::stringEncode4JS($j['nom']." ".$j['prenom'])."', s: ".(isset($selected[$j['id']]) ? "true" : "false")." }";
		$values2 .= ($values2 == "" ? "" : ",")."{ v: ".$j['id'].", l: '".Wrapper::stringEncode4JS($j['nom']." ".$j['prenom'])."', s: ".($j['id'] == $e_capitaine ? "true" : "false")." }";
		$values3 .= ($values3 == "" ? "" : ",")."{ v: ".$j['id'].", l: '".Wrapper::stringEncode4JS($j['nom']." ".$j['prenom'])."', s: ".($j['id'] == $e_adjoint ? "true" : "false")." }";
		if ($j['id'] == $e_capitaine) $existeCapitaine = true;
		if ($j['id'] == $e_adjoint) $existeAdjoint = true;
	}
}
$values2 = "{ v: 0, l: 'Aucun', s: ".($existeCapitaine ? "false" : "true")." }".($values2 != "" ? "," : "").$values2;
$values3 = "{ v: 0, l: 'Aucun', s: ".($existeAdjoint ? "false" : "true")." }".($values3 != "" ? "," : "").$values3;
?>

choices.build({ name: 'selectedplayers', c1: 'orange', callback: 'sync_players', removable: true, multiple: true, values: [<?= $values ?>] });
choices.build({ name: 'capitaine', c1: 'orange', singlepicking: true, removable: true, values: [<?= $values2 ?>] });
choices.build({ name: 'adjoint', c1: 'orange', singlepicking: true, removable: true, values: [<?= $values3 ?>] });

sync_players = function(name) {

	var list = choices.getSelection(name);

//	alert(el('adjoint_picker'));

return false;

	var	capitaine_elts = el('capitaine_picker').getElementsByTagName('button');
	var	adjoint_elts = el('adjoint_picker').getElementsByTagName('button');
	var ids = list.split(',');

	for(k=0; k < capitaine_elts.length; k++) {
		if (capitaine_elts[k].name == "capitaine_name") {
			capitaine_elts[k].innerHTML = 'tyutut';
			for(i=0; i < ids.length; i++) {
			}
		}
	}
}

validate_and_submit = function()
{
	el('selection').value  = '';
	el('nb_selection').value = 0;

    if (!check_alphanumext(valof('nom'), 'Nom', -1))
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

<? if (!$no_joueurs) { ?>
	tmp = choices.getSelection('selectedplayers').split(',');
<? } else { ?>
	tmp= [];
<? } ?>
	nb_sel = tmp.length;

<? if ($sess_context->getChampionnatType() == _TYPE_LIBRE_) { ?>
	if (!(nb_sel >= 2))
	{
		alert('Vous devez sélectionner au minimum 2 joueurs ...');
		return false;
	}
<? } ?>

	el('selection').value = tmp.join('|');
	el('nb_selection').value = nb_sel;

	params = '?adjoint='+choices.getSelection('adjoint')+'&capitaine='+choices.getSelection('capitaine')+attrs(['nom', 'photo', 'commentaire', 'etat', 'selection', 'nb_selection']);
	xx({action:'teams', id:'main', url:'edit_teams_do.php'+params+'&idt='+<?= $idt ?>+'&upd=<?= $modifier ? 1 : 0 ?>'});

	return true;
}
annuler = function()
{
	mm({action: 'teams'});
	return true;
}
mandatory(['nom']); fs('nom');
</script>

</div>