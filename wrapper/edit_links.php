<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$idl = isset($_REQUEST['idl']) && is_numeric($_REQUEST['idl']) && $_REQUEST['idl'] > 0 ? $_REQUEST['idl'] : 0;
$modifier = $idl > 0 ? true : false;

if ($modifier) {
	$select = "SELECT u.date_nais, u.photo uphoto, p.photo pphoto, up.status, CONCAT('<span>', u.nom, ' ', u.prenom, '</span><small>alias ', u.pseudo, '</small>') user, CONCAT('<span>', p.nom, ' ', p.prenom, '</span><small>alias ', p.pseudo, '</small>') player FROM jb_user_player up, jb_joueurs p, jb_users u WHERE up.id=".$idl." AND up.id_player=p.id AND up.id_user=u.id AND up.id_champ=".$sess_context->getRealChampionnatId();
	$res = dbc::execSQL($select);
	$row = mysqli_fetch_array($res);
}

$status = $modifier ? $row['status'] : 0;

?>

<div id="edit_roles" class="edit">

<h2 class="grid links"><?= $modifier ? "Modification" : "Ajout" ?> d'un rattachement</h2>

<table cellspacing="0" cellpadding="0" class="jkgrid">
<tbody>

<tr><td class="c1"><div><label for="status">Joueur</label><div class="col2"><?= $row['player'] ?></div><div class="col3"><img src="<?= Wrapper::formatPhotoJoueur($row['pphoto']) ?>" height="90" width="90" /></div></div></td></tr>
<tr><td class="c1"><div><label for="status">Utilisateur</label><div class="col2"><?= $row['user'] ?><span><?= Wrapper::formatNumber(Toolbox::date2age($row['date_nais'])) ?> ans</span></div><div class="col3"><img src="<?= Wrapper::formatPhotoJoueur($row['uphoto']) ?>" height="90" width="90" /></div></div></td></tr>
<tr><td class="c1"><div><label for="status">Rattachement</label><div id="status" class="grouped" style="float:left; width: 200px;"></div></div></td></tr>

</tbody></table>

<div class="actions grouped_inv">
<button onclick="return validate_and_submit();" class="button green"><?= $modifier ? "Valider" : "Ajouter" ?></button>
<button onclick="return annuler();" class="button gray">Annuler</button>
</div>

<script>
choices.build({ name: 'status', c1: 'blue', c2: 'white', values: [ {v: 0, l: 'Non', s: <?= $status == 0 ? "true" : "false" ?>}, {v: 1, l: 'Oui', s: <?= $status == 1 ? "true" : "false" ?>} ] });

validate_and_submit = function()
{
	params = '?status='+choices.getSelection('status');
	go({id:'main', url:'edit_links_do.php'+params+'&idl='+<?= $idl ?>+'&upd=<?= $modifier ? 1 : 0 ?>'});

	return true;
}
annuler = function()
{
	mm({action: 'links'});
	return true;
}
</script>

</div>