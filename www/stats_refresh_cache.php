<?

include "../include/sess_context.php";
include "../include/constantes.php";
include "../include/cache_manager.php";

session_start();

if (!isset($id_real_champ) || $id_real_champ == "") { exit(0); }
if (!isset($id_saison) || $id_saison == "") { exit(0); }

session_register("sess_context");

$sess_context = new sess_context();

include "../include/toolbox.php";
include "../include/inc_db.php";
include "SQLServices.php";
include "ManagerFXList.php";
include "StatsBuilder.php";

$db = dbc::connect();

$scs = new SQLChampionnatsServices($id_real_champ);
$row = $scs->getChampionnat();

if ($row)
{
	$row['login'] = "";
	$row['pwd']   = "";
	$sess_context->setChampionnat($row);
}
else
	exit(0);

$sgb = JKCache::getCache("../cache/stats_champ_".$id_real_champ."_".$id_saison.".txt", 24*60*60, "_FLUX_STATS_CHAMP_");

?>
