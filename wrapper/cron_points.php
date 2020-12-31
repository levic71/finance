<?

include "../include/sess_context.php";

session_start();

require_once "../include/constantes.php";
include "../include/toolbox.php";
include "../include/inc_db.php";
require_once "../include/cache_manager.php";
require_once "../www/SQLServices.php";
include "../www/StatsBuilder.php";

if (!isset($sess_context))
{
//	session_register("sess_context");
	$sess_context = new sess_context();
	$_SESSION["sess_context"] = $sess_context;
}

$db = dbc::connect();

$most_active = JKCache::getCache("../cache/most_active_home.txt", 900, "_FLUX_MOST_ACTIVE_");

foreach($most_active as $c)	{
	$tab_most_active[$c['id']] = $c['points'];
	$sql = "UPDATE jb_championnat SET points=".$c['points']." WHERE id=".$c['id'].";";
	$res = dbc::execSQL($sql);
}

?>
