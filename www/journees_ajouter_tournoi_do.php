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
$sjs1 = new SQLJoueursServices($sess_context->getChampionnatId());
$js   = $sjs1->getListeJoueursByEquipes($all_equipes);

// Insertion de la journée
$insert = "INSERT INTO jb_journees (id_champ, tournoi_consolante, tournoi_nb_poules, tournoi_phase_finale, nom, date, heure, duree, joueurs, equipes, pref_saisie) VALUES (".$sess_context->getChampionnatId().", ".$tournoi_consolante.", ".$nb_poules.", ".$phase_finale.", '".$nom.":".$nom_journee."', '".$new_date."', '".$heure."', ".$duree.", '".$js."', '".$eq."', ".$type_participant.");";
$res = dbc::execSQL($insert);

// On récupère les infos de la journée
$sjs2 = new SQLJourneesServices($sess_context->getChampionnatId(), -1);
$journee = $sjs2->getJourneeByDate($new_date);

// On affecte l'id de la journée en cours
$sess_context->setJourneeId($journee['id']);

// Création automatique des matchs de poule
if ($matchs_auto == 0)
{
	$sms = new SQLMatchsServices($sess_context->getChampionnatId(), $sess_context->getJourneeId(), -1);
	$sms->createMatchsPoulesTournoi($eq, $matchs_ar);
}

mysql_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

// On redirige sur match.php
ToolBox::do_redirect("matchs_tournoi.php");

?>
