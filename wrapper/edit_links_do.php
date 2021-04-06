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
$del    = Wrapper::getRequest('del',    0);
$idl    = Wrapper::getRequest('idl',    0);
$upd    = Wrapper::getRequest('upd',    0);

if (($upd == 1 || $del == 1) && !is_numeric($idl)) ToolBox::do_redirect("grid.php");

if ($del == 1)
{
	$err = true;
	$sql = "SELECT count(*) total FROM jb_user_player WHERE id=".$idl." AND id_champ=".$sess_context->getRealChampionnatId();
	$res = dbc::execSQL($sql);
	$row = mysqli_fetch_array($res);
	if ($row['total'] == 1) {
		$delete = "DELETE FROM jb_user_player WHERE id=".$idl." AND id_champ=".$sess_context->getRealChampionnatId();
		$res = dbc::execSQL($delete);
		$err = false;
	}

?><span class="hack_ie">_HACK_IE_</span><script>mm({action:'links'}); $<?= $err ? "dMsg" : "cMsg" ?>({ msg: 'Rattachement <?= $err ? "non" : "" ?> supprimé' });</script><?
	exit(0);
}


$modifier = $upd == 1 ? true : false;


if ($modifier)
{
	$sql = "SELECT * FROM jb_user_player WHERE id=".$idl." AND id_champ=".$sess_context->getRealChampionnatId();
	$res = dbc::execSQL($sql);

	if ($row = mysqli_fetch_array($res)) {

		// On vérifie que c pas déjà affecté
		$sql = "SELECT count(*) total FROM jb_user_player WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id_player=".$row['id_player']." AND status=1;";
		$res = dbc::execSQL($sql);
		if ($data = mysqli_fetch_assoc($res) && $data['total'] > 0 && $status == 1) { ?>
<span class="hack_ie"><span class="hack_ie">_HACK_IE_</span></span><script>mm({action:'links'}); $dMsg({ msg: 'Rattachement impossible, joueur déjà affecté !' });</script>
<?
			exit(0);
		}

		$update = "UPDATE jb_user_player SET status=".$status." WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$idl;
		$res = dbc::execSQL($update);
	}
}

?><span class="hack_ie">_HACK_IE_</span><script>mm({action:'links'}); $cMsg({ msg: 'Rattachement <?= $modifier ? "modifié" : "ajouté" ?>' });</script>