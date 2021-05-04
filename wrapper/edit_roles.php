<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$idr = isset($_REQUEST['idr']) && is_numeric($_REQUEST['idr']) && $_REQUEST['idr'] > 0 ? $_REQUEST['idr'] : 0;
$modifier = $idr > 0 ? true : false;

if ($modifier) {
	$select = "SELECT * FROM jb_roles WHERE id=".$idr;
	$res = dbc::execSQL($select);
	$row = mysqli_fetch_array($res);
}

$role   = $modifier ? $row['role'] : _ROLE_ANONYMOUS_;
$status = $modifier ? $row['status'] : 0;

?>

<div id="edit_roles" class="edit">

<h2 class="grid roles"><?= $modifier ? "Modification" : "Ajout" ?> d'un role</h2>

<table cellspacing="0" cellpadding="0" class="jkgrid">
<tbody>

<tr><td class="c1"><div><label for="role">Role</label><div id="role" class="grouped" style="float:left; width: 200px;"></div></td></tr>
<tr><td class="c1"><div><label for="status">Actif</label><div id="status" class="grouped" style="float:left; width: 200px;"></div></td></tr>

</tbody></table>

<div class="actions grouped_inv">
<? if ($role == _ROLE_ADMIN_ && $sess_context->isOnlyDeputy()) { ?>
<button onclick="return annuler();" class="button green">Retour</button>
<? } else { ?>
<button onclick="return validate_and_submit();" class="button green"><?= $modifier ? "Valider" : "Ajouter" ?></button>
<button onclick="return annuler();" class="button gray">Annuler</button>
<? } ?>
</div>

<script>
<? if ($role == _ROLE_ADMIN_ && $sess_context->isOnlyDeputy()) { ?>
choices.build({ name: 'status', c1: 'blue', c2: 'white', values: [ {v: <?= $status  ?>, l: '<?= $status == 0 ? "Non" : "Oui" ?>', s: true } ] });
<? } else { ?>
choices.build({ name: 'status', c1: 'blue', c2: 'white', values: [ {v: 0, l: 'Non', s: <?= $status == 0 ? "true" : "false" ?>}, {v: 1, l: 'Oui', s: <?= $status == 1 ? "true" : "false" ?>} ] });
<? } ?>

<? if ($role == _ROLE_ADMIN_ && $sess_context->isOnlyDeputy()) { ?>
<? $values = ""; reset($libelle_role); foreach($libelle_role as $cle => $val) if ($cle == _ROLE_ADMIN_) $values .= ($values == "" ? "" : ",")."{ v: '".$cle."', l: '".$val."', s: ".($role == $cle ? "true" : "false")." }"; ?>
<? } else if ($sess_context->isOnlyDeputy()) { ?>
<? $values = ""; reset($libelle_role); foreach($libelle_role as $cle => $val) if ($cle != _ROLE_ADMIN_) $values .= ($values == "" ? "" : ",")."{ v: '".$cle."', l: '".$val."', s: ".($role == $cle ? "true" : "false")." }"; ?>
<? } else { ?>
<? $values = ""; reset($libelle_role); foreach($libelle_role as $cle => $val) $values .= ($values == "" ? "" : ",")."{ v: '".$cle."', l: '".Wrapper::stringEncode4JS($val)."', s: ".($role == $cle ? "true" : "false")." }"; ?>
<? } ?>

choices.build({ name: 'role', c1: 'blue', c2: 'white', values: [<?= $values ?>] });

validate_and_submit = function()
{
	params = '?role='+choices.getSelection('role')+'&status='+choices.getSelection('status');
	go({id:'main', url:'edit_roles_do.php'+params+'&idr='+<?= $idr ?>+'&upd=<?= $modifier ? 1 : 0 ?>'});

	return true;
}
annuler = function()
{
	mm({action: 'roles'});
	return true;
}
</script>

</div>