<?

include "../include/sess_context.php";

session_start();

include "common.php";

$sess_context->changeSaison($choix_saisons);

$redirect_page = isset($HTTP_REFERER) && $HTTP_REFERER != "" ? $HTTP_REFERER : (isset($appelant) && $appelant != "") ? $appelant : "championnat_home.php";

if (strstr($redirect_page, "matchs") || strstr($redirect_page, "journees_virtuelles")) $redirect_page = $sess_context->getChampionnatType() == _TYPE_LIBRE_ ? "../www/calendar.php" : "../www/journees.php";
if (strstr($redirect_page, "/admin/")) $redirect_page = "../admin/superuser_fcts.php";
if (strstr($redirect_page, "stats_detail_equipe")) $redirect_page = "stats_equipes.php";
if (strstr($redirect_page, "stats_detail_joueur")) $redirect_page = "stats_joueurs.php";
if (strstr($redirect_page, "joueurs_")) $redirect_page = "joueurs.php";
if (strstr($redirect_page, "equipes_")) $redirect_page = "joueurs.php";

ToolBox::do_redirect($redirect_page);

?>