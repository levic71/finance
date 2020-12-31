<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/journeebuilder.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$idp = Wrapper::getRequest('idp', '0');
$del = 1;

if (!is_numeric($idp)) ToolBox::do_redirect("grid.php");

if ($del == 1)
{
	// Suppression du lien avec joueur inscrit si besoin
	$delete = "DELETE FROM jb_user_player WHERE id_player=".$idp." AND id_champ=".$sess_context->getRealChampionnatId();
	$res = dbc::execSQL($delete);

	$err = false;

?><span class="hack_ie">_HACK_IE_</span><script>mm({action:'players'}); $<?= $err ? "dMsg" : "cMsg" ?>({ msg: 'Lien avec joueur inscrit <?= $err ? "non" : "" ?> supprimé' });</script><?
	exit(0);
}
