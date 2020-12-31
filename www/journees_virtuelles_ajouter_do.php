<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "journeebuilder.php";
include "ManagerFXList.php";

$db = dbc::connect();

$new_date = substr($zone_calendar, 6, 4) . "-" . substr($zone_calendar, 3, 2) . "-" . substr($zone_calendar, 0, 2);

$js = "";
$eq = $equipes_stats;
$type_participant = 1;

$classement_equipes = "";
$liste_equipes = explode(',', $equipes_stats);
foreach($liste_equipes as $equipe)
{
	$podium = ToolBox::get_global("podium_".$equipe);
	$points = ToolBox::get_global("points_".$equipe);
	$matchs_g = ToolBox::get_global("gagnes_".$equipe);
	$matchs_p = ToolBox::get_global("perdus_".$equipe);
	$matchs_j = $matchs_g + $matchs_p;
	$sets_g = ToolBox::get_global("setsg_".$equipe);
	$sets_p = ToolBox::get_global("setsp_".$equipe);
	$sets_j = $sets_g + $sets_p;
	$sets_d = $sets_g - $sets_p;
	$marques   = ToolBox::get_global("marques_".$equipe);
	$encaisses = ToolBox::get_global("encaisses_".$equipe);
	$diff = $marques - $encaisses;

 	$classement_equipes .= ($classement_equipes == "" ? "" : "|").$equipe."@".$points.",".$matchs_j.",".$matchs_g.",0,".$matchs_p.",".$sets_j.",".$sets_g.",".$sets_p.",".$sets_d.",".$marques.",".$encaisses.",".$diff.",".$podium.",".$points.",0,0,0,0";
}

if ($modification == 1)
	$req = "UPDATE jb_journees SET date='".$new_date."', equipes='".$eq."', classement_equipes='".$classement_equipes."' WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getJourneeId();
else
	$req = "INSERT INTO jb_journees (id_champ, nom, date, heure, duree, joueurs, equipes, classement_equipes, pref_saisie, virtuelle) VALUES (".$sess_context->getChampionnatId().", '".$nom_journee."', '".$new_date."', '0', 0, '".$js."', '".$eq."', '".$classement_equipes."', ".$type_participant.", 1);";
$res = dbc::execSQL($req);

mysql_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

ToolBox::do_redirect($sess_context->championnat['visu_journee'] == _VISU_JOURNEE_CALENDRIER_ ? "calendar.php" : "journees.php");

?>
