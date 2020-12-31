<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "StatsBuilder.php";

$db = dbc::connect();

if ($selection == "") ToolBox::do_redirect("joueurs.php");

$sss = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());
$saison = $sss->getSaison();

$joueurs = str_replace('|', ',', $saison['joueurs'] == ""  ? $selection : $saison['joueurs'].",".$selection);

$update  = "UPDATE jb_saisons SET joueurs='".$joueurs."' WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$sess_context->getChampionnatId();
$res = dbc::execSQL($update);

mysql_close ($db);

ToolBox::do_redirect("joueurs.php");

?>