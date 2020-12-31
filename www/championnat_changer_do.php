<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

$scs = new SQLChampionnatsServices(-1);
$c = $scs->getChampionnat($choix_amis);

ToolBox::do_redirect("../www/championnat_acces.php?ref_champ=".$c['championnat_id']);

?>
