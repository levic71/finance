<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/StatsBuilder.php";
include "../www/ManagerFXList.php";
include "../www/journees_synchronisation.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$confirm = Wrapper::getRequest('confirm', "no");

?>

<h2 class="grid dashboard">Synchronisation journées</h2>
<table>
<?

$tab = synchronize_journees($sess_context->getRealChampionnatId(), $sess_context->getChampionnatType(), $sess_context->getChampionnatId(), $confirm);

foreach($tab as $item) {
	echo "<tr><td>".$item[1]."</td><td>".$item[2]."</td></tr>";
}

if (count($tab) == 0) echo "<tr><td>RAS</td></tr>";

?>
</table>

<? if ($confirm != "yes") { ?>
<div class="actions grouped_inv">
<button onclick="go({action: 'seasons', id:'main', url:'admin_full_sync_do.php?confirm=yes'});" class="button green">Synchroniser</button>
<button onclick="mm({action: 'dashboard'});" class="button gray">Annuler</button>
</div>
<? } else { ?>
<div class="actions grouped_inv">
<button onclick="mm({action: 'dashboard'});" class="button green">Done</button>
</div>
<? } ?>
