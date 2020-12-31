<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "StatsBuilder.php";

$db = dbc::connect();

$select = "SELECT * FROM jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getJourneeId();
$res = dbc::execSQL($select);
$journee = mysql_fetch_array($res);

$select = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$equipe;
$res = dbc::execSQL($select);
$mon_equipe = mysql_fetch_array($res);

// On récupère les equipes déjà présentes
$items = explode(',', $journee['equipes']);
foreach($items as $i) $eq_presentes[$i] = $i;

// On ajoute l'équipe sélectionnée
$eq_presentes[$equipe] = $equipe;

// On reconstitue le champ 'equipes'
$res_eq = "";
foreach($eq_presentes as $eq) $res_eq .= ($res_eq == "" ? "" : ",").$eq;

// On reconstitue le champ 'joueurs'
$res_j = $journee['joueurs'];
$item  = explode('|', $mon_equipe['joueurs']);
if ($mon_equipe['nb_joueurs'] >= 2)
{
	$item = explode('|', $mon_equipe['joueurs']);
	foreach($item as $j) $res_j .= ",".$j;
}

// On met à jour la journée
$update = "UPDATE jb_journees SET equipes='".$res_eq."', joueurs='".$res_j."' WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$sess_context->getJourneeId();
$res = dbc::execSQL($update);

// Mise des statistiques de la journée
$stats = new StatsJourneeBuilder($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$stats->SQLUpdateClassementJournee();

mysql_close ($db);

ToolBox::do_redirect("matchs.php");

?>