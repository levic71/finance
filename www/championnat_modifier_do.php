<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$menu = new menu("full_access");

$db = dbc::connect();

$options  = "";
$options .= isset($chk_news)   && $chk_news   == "on" ? "1|" : "0|";
$options .= isset($chk_forum)  && $chk_forum  == "on" ? "1|" : "0|";
$options .= isset($chk_fannys) && $chk_fannys == "on" ? "1|" : "0|";
$options .= isset($chk_prev)   && $chk_prev   == "on" ? "1|" : "0|";
$options .= isset($chk_next)   && $chk_next   == "on" ? "1|" : "0|";
$options .= isset($chk_focus)  && $chk_focus  == "on" ? "1|" : "0|";
$options .= isset($chk_clt_joueurs)  && $chk_clt_joueurs  == "on" ? "1|" : "0|";
$options .= isset($chk_poule_lettre) && $chk_poule_lettre == "on" ? "1|" : "0|";
$options .= isset($chk_all_matchs)   && $chk_all_matchs   == "on" ? "1|" : "0|";
$options .= isset($chk_matchs)       && $chk_matchs       == "on" ? "1|" : "0|";
$options .= isset($chk_team)         && $chk_team         == "on" ? "1|" : "0|";
$options .= isset($chk_gavgp)        && $chk_gavgp        == "on" ? "1|" : "0|";

$vars_update = "nom='".$ch_nom."'";
if (isset($gestion_fanny)) $vars_update .= ", gestion_fanny=".$gestion_fanny;
if (isset($gestion_sets)) $vars_update .= ", gestion_sets=".$gestion_sets;
if (isset($tri_classement_general)) $vars_update .= ", tri_classement_general=".$tri_classement_general;
if (isset($type_sport)) $vars_update .= ", type_sport=".$type_sport;
if (isset($gestion_nul)) $vars_update .= ", gestion_nul=".$gestion_nul;
if (isset($selected_friends)) $vars_update .= ", friends='".$selected_friends."'";
if (isset($visu_journee)) $vars_update .= ", visu_journee=".$visu_journee;
if (isset($valeur_victoire)) $vars_update .= ", valeur_victoire=".$valeur_victoire;
if (isset($valeur_nul)) $vars_update .= ", valeur_nul=".$valeur_nul;
if (isset($valeur_defaite)) $vars_update .= ", valeur_defaite=".$valeur_defaite;
if (isset($type_lieu)) $vars_update .= ", type_lieu=".$type_lieu;
if (isset($lieu_pratique)) $vars_update .= ", lieu='".$lieu_pratique."'";
if (isset($options)) $vars_update .= ", options='".$options."'";
if (isset($ch_description)) $vars_update .= ", description='".$ch_description."'";
if (isset($ch_email)) $vars_update .= ", email='".$ch_email."'";
if (isset($ch_gestionnaire)) $vars_update .= ", gestionnaire='".$ch_gestionnaire."'";
if (isset($ta)) $vars_update .= ", news='".$ta."'";
if (isset($ch_login)) $vars_update .= ", login='".$ch_login."'";
if (isset($ch_pwd)) $vars_update .= ", pwd='".$ch_pwd."'";
$update = "UPDATE jb_championnat SET ".$vars_update." WHERE id=".$sess_context->getRealChampionnatId();
$res = dbc::execSQL($update);

$scs = new SQLChampionnatsServices($sess_context->getRealChampionnatId());
$row = $scs->getChampionnat();
$sess_context->setChampionnat($row);

mysql_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");
JKCache::delCache("../cache/info_champ_".$sess_context->getRealChampionnatId()."_.txt", "_FLUX_INFO_CHAMP_");

ToolBox::do_redirect("championnat_home.php");

?>
