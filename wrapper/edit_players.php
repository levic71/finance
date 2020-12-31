<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$idp = isset($_REQUEST['idp']) && is_numeric($_REQUEST['idp']) && $_REQUEST['idp'] > 0 ? $_REQUEST['idp'] : 0;
$modifier = $idp > 0 ? true : false;

if ($modifier) {
	$select = "SELECT * FROM jb_joueurs WHERE id=".$idp;
	$res = dbc::execSQL($select);
	$joueur = mysqli_fetch_array($res);
}

$j_id     = $modifier ? $joueur['id']           : "";
$j_nom    = $modifier ? $joueur['nom']          : "";
$j_prenom = $modifier ? $joueur['prenom']       : "";
$j_sexe   = $modifier ? $joueur['sexe']         : "1";
$j_nais   = $modifier ? ToolBox::mysqldate2date($joueur['dt_naissance']) : date("d/m/Y");
$j_pseudo = $modifier ? $joueur['pseudo']       : "";
$j_email  = $modifier ? $joueur['email']        : "";
$j_tel1	  = $modifier ? $joueur['tel1']         : "";
$j_tel2	  = $modifier ? $joueur['tel2']         : "";
$j_photo  = $modifier ? $joueur['photo']        : "";
$j_presen = $modifier ? $joueur['presence']     : "1";
$j_etat   = $modifier ? $joueur['etat']         : "0";

// Récupération des joueurs qui participent à la saion courante
$sss = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());
$lst_joueurs_saison = $sss->getListeJoueurs();

// Récupération de tous les joueurs du championnat (ttes saisons confondues)
$select = "SELECT pseudo FROM jb_joueurs WHERE id_champ=".$sess_context->getRealChampionnatId();
$res = dbc::execSQL($select);

$user_link = false;
if ($modifier) {
	$req2 = "SELECT u.* FROM jb_users u, jb_user_player up, jb_joueurs j WHERE j.id =".$j_id." AND j.id = up.id_player AND up.id_user = u.id";
	$res2 = dbc::execSql($req2);
	if ($user = mysqli_fetch_array($res2)) {
		$user_link = true;
		$patronyme = $user['prenom']." ".$user['nom']." as ".$user['pseudo'];
		$photo = $user['photo'] == "" ? $j_photo : $user['photo'];
	}
}

?>

<input type="hidden" id="max_file_size" value="100000" />
<input type="hidden" id="old_pseudo" value="<?= $j_pseudo ?>" />
<? if ($modifier || !$sess_context->isFreeXDisplay()) { ?><input type="hidden" id="auto_create_team" value="2" /><? } ?>

<? if ($modifier && !$user_link) { ?>
<ul class="sidebar">
	<li><a href="#" onclick="go({action: 'players', id:'main', url:'link_to_player.php?idp=<?= $j_id ?>'});" id="sb_link_player" class="ToolText" onmouseover="showtip('sb_link_player');"><span>Lier à un joueur inscrit</span></a></li>
</ul>
<? } ?>

<div id="edit_players" class="edit">

<h2 class="grid"><?= $modifier ? "Modification" : "Ajout" ?> d'un joueur</h2>

<table cellspacing="0" cellpadding="0" class="jkgrid">
<tbody>

<? if ($user_link) { ?>
<tr style="background: #9E3232; color:#fff; "><td class="c1"><div><label for="nom">Attention ce joueur est lié à un utilisateur inscrit : <?= $patronyme ?></label><button onclick="xx({action: 'stats', id:'main', url:'link_to_player_del.php?idp=<?= $j_id ?>'});" style="margin-bottom: 0px;" class="button red right">Suppression du lien</button></div></td></tr>
<? } ?>

<tr><td class="c1"><div><label for="nom">Nom</label><input type="text" name="nom" id="nom" value="<?= $j_nom ?>" /></div></td></tr>
<tr><td class="c1"><div><label for="prenom">Prénom</label><input type="text" name="prenom" id="prenom" value="<?= $j_prenom ?>" /></div></td></tr>
<tr><td class="c1"><div><label for="sexe">Sexe</label><div id="sexe" class="grouped" style="float:left; width: 200px;"></div></div></td></tr>
<tr><td class="c1"><div><label for="date_nais">Date de naissance</label><div class="singlepicking" style="float:left; width: 260px;"><button class="button blue" id="date_nais" onclick="calendar.picker({ name: 'date_nais' });"><span><?= $j_nais ?></span></button><small>JJ/MM/AAAA</small></div></div></td></tr>
<tr><td class="c1"><div><label for="pseudo">Pseudo</label><input type="text" name="pseudo" id="pseudo" value="<?= $j_pseudo ?>" /></div></td></tr>
<tr><td class="c1"><div><label for="email">Email</label><input type="text" name="email" id="email" value="<?= $j_email ?>" /></div></td></tr>
<tr><td class="c1"><div><label for="tel1">Téléphone</label><input type="text" name="tel1" id="tel1" value="<?= $j_tel1 ?>" /></div></td></tr>
<tr><td class="c1"><div><label for="tel2">Mobile</label><input type="text" name="tel2" id="tel2" value="<?= $j_tel2 ?>" /></div></td></tr>

<tr><td class="c1"><div>
	<label for="photo">Photo</label>

	<ul class="upload_target"><li><img style="width: 32px; height: 32px;" id="img_target" src="<?= $j_photo != "" ? $j_photo : "img/userxxl.png" ?>" /></li><li id="f1_upload_process"><img src="img/loader.gif" /></li><li id="f1_upload_ok"><img src="img/tick_32.png" /></li><li id="f1_upload_err"><img src="img/block_32.png" /></li></ul>
	<input type="hidden" name="photo" id="photo" value="<?= $j_photo ?>" />

	<form name="uploadform" action="upload.php?target_image=img_target&target_upload=photo&players=1" style="clear: both;" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="startUpload();" >
		<span id="f1_upload_form" align="center">
			<label for="myfile">&nbsp;</label><input name="myfile" id="myfile" type="file" size="30" /><button onclick="startUpload();" class="button blue">Upload</button><small>< 100ko</small>
		</span>
		<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
	</form>

</div></td></tr>

<tr><td class="c1"><div><label for="presence">Régulier</label><div id="presence" class="grouped" style="float:left; width: 200px;"></div></td></tr>
<tr><td class="c1"><div><label for="etat">Etat</label><div id="etat" class="grouped" style="float:left; width: 400px;"></div></td></tr>

<? if (!$modifier && $sess_context->isFreeXDisplay()) { ?>
<tr><td class="c1"><div><label for="auto_create_team" class="dbline">Création automatique<br />des équipes</label><div id="auto_create_team" class="grouped" style="float:left; width: 460px;"></div></td></tr>
<? } ?>

</tbody></table>

<div class="actions grouped_inv">
<button onclick="return validate_and_submit();" class="button green"><?= $modifier ? "Valider" : "Ajouter" ?></button>
<button onclick="return annuler();" class="button gray">Annuler</button>
</div>


<small><a href="http://www.cnil.fr/en-savoir-plus/deliberations/deliberation/delib/106/" target="_blank">CNIL - Dispense n° 8 - Délibération n° 2010-229</a></small>


<script>
choices.build({ name: 'sexe', c1: 'blue', c2: 'white', values: [{ v: 1, l: 'Homme', s: <?= $j_sexe == 1 ? 'true' : 'false' ?> }, { v: 2, l: 'Femme', s: <?= $j_sexe == 2 ? 'true' : 'false' ?> }] });
choices.build({ name: 'presence', c1: 'blue', c2: 'white', values: [ {v: 0, l: 'Non', s: <?= $j_presen == 0 ? "true" : "false" ?>}, {v: 1, l: 'Oui', s: <?= $j_presen == 1 ? "true" : "false" ?>} ] });
<? $values = ""; reset($libelle_etat_joueur);  while(list($cle, $val) = each($libelle_etat_joueur)) $values .= ($values == "" ? "" : ",")."{ v: '".$cle."', l: '".Wrapper::stringEncode4JS($val)."', s: ".($j_etat == $cle ? "true" : "false")." }"; ?>
choices.build({ name: 'etat', c1: 'blue', c2: 'white', values: [<?= $values ?>] });
<? if (!$modifier && $sess_context->isFreeXDisplay()) { ?>
choices.build({ name: 'auto_create_team', c1: 'blue', c2: 'white', values: [{ v: 0, l: 'Oui', s: true}, { v: 1, l: 'Oui pour les joueurs réguliers'}, { v: 2, l: 'Non'}] });
<? } ?>

var all_pseudo = new Array(<?
$lib = "";
while($row = mysqli_fetch_array($res))
{
	if ($modifier && $row['pseudo'] == $j_pseudo) continue;
	$lib .= "'".addslashes($row['pseudo'])."',";
}
echo preg_replace("/,,/", ",", preg_replace("/^,/", "", preg_replace("/,$/", "", $lib)));
?>);
var saison_pseudo = new Array(<?
$lib = "";
foreach($lst_joueurs_saison as $joueur)
{
	if ($modifier && $joueur['pseudo'] == $j_pseudo) continue;
	$lib .= "'".addslashes($joueur['pseudo'])."',";
}
echo preg_replace("/,,/", ",", preg_replace("/^,/", "", preg_replace("/,$/", "", $lib)));
?>);
validate_and_submit = function()
{
    if (!check_alphanumext(valof('nom'), 'Nom', -1))
		return false;
	el('nom').value = valof('nom').toUpperCase();
	el('prenom').value = upperFirstLetter(valof('prenom'));

    if (!check_alphanumext(valof('pseudo'), 'Pseudo', -1))
		return false;
	el('pseudo').value = upperFirstLetter(valof('pseudo'));

	for(i=0; i < saison_pseudo.length; i++)
	{
		if (valof('pseudo') == saison_pseudo[i])
		{
			alert('Ce pseudo est déjà utilisé, veuillez en choisir un autre ...');
			return false;
		}
	}

	for(i=0; i < all_pseudo.length; i++)
	{
		if (valof('pseudo') == all_pseudo[i])
		{
			alert('Ce pseudo est déjà utilisé dans une saison précédente, veuillez en choisir un autre ou insérer ce joueur dans cette saison via la procédure adéquate ...');
			return false;
		}
	}

	if (valof('email') != '')
	{
		if (!check_email(valof('email')))
			return false;
	}

	if (valof('photo') != '')
	{
		items = valof('photo').split('.');
		if (items[(items.length-1)] != 'gif' && items[(items.length-1)] != 'GIF' && items[(items.length-1)] != 'jpg' && items[(items.length-1)] != 'JPG' && items[(items.length-1)] != 'JPEG' && items[(items.length-1)] != 'jpeg')
		{
			alert('Le format de l\'image doit être \'gif\' ou \'jpg\'.');
			return false;
		}
	}

	var date_nais = calendar.getValue('date_nais');
	var auto_create_team = <? if (!$modifier && $sess_context->isFreeXDisplay()) { ?>choices.getSelection('auto_create_team');<? } else { ?>valof('auto_create_team');<? } ?>
	params = '?sexe='+choices.getSelection('sexe')+'&date_nais='+date_nais+'&presence='+choices.getSelection('presence')+'&etat='+choices.getSelection('etat')+'&auto_create_team='+auto_create_team+attrs(['nom', 'prenom', 'pseudo', 'email', 'tel1', 'tel2', 'photo', 'old_pseudo']);
	go({id:'main', url:'edit_players_do.php'+params+'&idp='+<?= $idp ?>+'&upd=<?= $modifier ? 1 : 0 ?>'});

	return true;
}
annuler = function()
{
	mm({action: 'players'});
	return true;
}
mandatory(['nom', 'pseudo']); fs('nom');
</script>

</div>