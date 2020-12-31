<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/journeebuilder.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$status = Wrapper::getRequest('status', 0);
$role   = Wrapper::getRequest('role',   _ROLE_ANONYMOUS_);
$del    = Wrapper::getRequest('del',    0);
$idr    = Wrapper::getRequest('idr',    0);
$upd    = Wrapper::getRequest('upd',    0);

if (($upd == 1 || $del == 1) && !is_numeric($idr)) ToolBox::do_redirect("grid.php");

if ($upd == 1 || $del == 1) {
	$sql = "SELECT role FROM jb_roles WHERE id=".$idr." AND id_champ=".$sess_context->getRealChampionnatId();
	$res = dbc::execSQL($sql);
	$droit = mysqli_fetch_array($res);
}

if ($del == 1)
{
	$err = true;
	$sql = "SELECT count(*) total FROM jb_roles WHERE id=".$idr." AND id_champ=".$sess_context->getRealChampionnatId();
	$res = dbc::execSQL($sql);
	$row = mysqli_fetch_array($res);
	if ($row['total'] == 1) {

		if ($droit['role'] == _ROLE_ADMIN_ && $sess_context->isOnlyDeputy()) {
			// On ne fait rien, pas assez de droit
		}
		else
		{
			$delete = "DELETE FROM jb_roles WHERE id=".$idr." AND id_champ=".$sess_context->getRealChampionnatId();
			$res = dbc::execSQL($delete);
			$err = false;
		}
	}

?><span class="hack_ie">_HACK_IE_</span><script>mm({action:'roles'}); $<?= $err ? "dMsg" : "cMsg" ?>({ msg: 'Droit <?= $err ? "non" : "" ?> supprimé' });</script><?
	exit(0);
}


$modifier = $upd == 1 ? true : false;


if ($modifier)
{
	if ($droit['role'] == _ROLE_ADMIN_ && $sess_context->isOnlyDeputy()) {
		// On ne fait rien, pas assez de droit
	}
	else
	{
		$update = "UPDATE jb_roles SET status=".$status.", role=".$role." WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$idr;
		$res = dbc::execSQL($update);
	}
}
else
{
}

?><span class="hack_ie"><span class="hack_ie">_HACK_IE_</span></span><script>mm({action:'roles'}); $cMsg({ msg: 'Droit <?= $modifier ? "modifié" : "ajouté" ?>' });</script>