<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "StatsBuilder.php";

$db = dbc::connect();

if ($selection == "") ToolBox::do_redirect("equipes.php");

$sss = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());
$saison = $sss->getSaison();

$equipes = str_replace('|', ',', $saison['equipes'] == ""  ? $selection : $saison['equipes'].",".$selection);

$update  = "UPDATE jb_saisons SET equipes='".$equipes."' WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$sess_context->getChampionnatId();
echo $update;
$res = dbc::execSQL($update);

mysql_close ($db);

ToolBox::do_redirect("equipes.php");

?>