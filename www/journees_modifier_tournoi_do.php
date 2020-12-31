<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

$new_date = substr($zone_calendar, 6, 4) . "-" . substr($zone_calendar, 3, 2) . "-" . substr($zone_calendar, 0, 2);
$js = "";
$eq = $selection;
$type_participant = 1;

// Formatage des équipes = Somme des équipes des poules sans les '|'
$all_equipes = "";
$tmp = str_replace('|', ',', $eq);
$items = explode(',', $tmp);
foreach($items as $item) 
	if ($item != "") $all_equipes .= $all_equipes == "" ? $item : ",".$item;

// Récupération de la liste des joueurs à partir des équipes
$sjs = new SQLJoueursServices($sess_context->getChampionnatId());
$js   = $sjs->getListeJoueursByEquipes($all_equipes);

// Insertion de la journée
$update = "UPDATE jb_journees SET nom='".$nom.":".$nom_journee."', tournoi_consolante=".$tournoi_consolante.", tournoi_nb_poules=".$nb_poules.", tournoi_phase_finale=".$phase_finale.", date='".$new_date."', joueurs='".$js."', equipes='".$eq."' WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getJourneeId();
$res = dbc::execSQL($update);

// Création automatique des matchs de poule
if ($matchs_auto == 0)
{
	$sms = new SQLMatchsServices($sess_context->getChampionnatId(), $sess_context->getJourneeId(), -1);
	$sms->createMatchsPoulesTournoi($eq, $matchs_ar);
}

mysql_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

// On redirige sur match.php
ToolBox::do_redirect("matchs_tournoi.php?pkeys_where_jb_journees=+WHERE+id%3D".$sess_context->getJourneeId());

?>
