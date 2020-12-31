<?

include "../include/sess_context.php";
include "../include/constantes.php";
include "../include/cache_manager.php";

session_start();

if (!isset($champ) || $champ == "") { exit(0); }
if (!isset($id_journee) || $id_journee == "") { exit(0); }
if (!isset($options_type_matchs) || $options_type_matchs == "") { exit(0); }

session_register("sess_context");

$sess_context = new sess_context();

include "../include/toolbox.php";
include "../include/inc_db.php";
include "SQLServices.php";
include "ManagerFXList.php";
include "StatsBuilder.php";

$db = dbc::connect();

$scs = new SQLChampionnatsServices($champ);
$row = $scs->getChampionnat();

if ($row)
{
	$row['login'] = "";
	$row['pwd']   = "";
	$sess_context->setChampionnat($row);
}
else
	exit(0);

$sess_context->setJourneeId($id_journee);



//$options_type_matchs = "X|0";
$items = explode('|', $options_type_matchs);
$type_matchs = $items[0];
$niveau_type = $items[1];

// On récupère les infos de la journée
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$row = $sjs->getJournee();

$is_journee_alias = $sjs->isJourneeAlias($row);

// Si ce n'est pas une journee alias, on regarde si cette journee possède des alias
$all_alias = $sjs->getAllAliasJournee($is_journee_alias ? $row['id_journee_mere'] : "");

$date_journee = $row['date'];
$exclude_matchs_journee = "";

if ($is_journee_alias)
{
	$tmp = explode('|', $row['id_matchs']);
	$matchs_journee = isset($tmp[1]) ? $tmp[1] : "";
	$journee_mere = $sjs->getJournee($row['id_journee_mere']);
	$id_journee_mere = $row['id_journee_mere'];
	$nb_poules    = $journee_mere['tournoi_nb_poules'];
	$phase_finale = $journee_mere['tournoi_phase_finale'];
	$consolante   = $journee_mere['tournoi_consolante'];
	$equipes_journee = $journee_mere['equipes'];
	$liste_poules = explode('|', $journee_mere['equipes']);
}
else
{
	$tmp = explode('|', $row['id_matchs']);
	$exclude_matchs_journee = isset($tmp[1]) ? $tmp[1] : "";
	$nb_poules    = $row['tournoi_nb_poules'];
	$phase_finale = $row['tournoi_phase_finale'];
	$consolante   = $row['tournoi_consolante'];
	$equipes_journee = $row['equipes'];
	$liste_poules = explode('|', $row['equipes']);
}

// Formatage du champs équipes pour prendre en compte les poules et les phases finales
$all_equipes = "";
$nb_equipes  = 0;

if ($type_matchs == "P")
{
	// Recherche de la poule à afficher
	$all_equipes = isset($liste_poules[$niveau_type-1]) ? $liste_poules[$niveau_type-1] : "";
}
else
{
	// Mise à plat du champ 'equipes' pour récupérer toutes les équipes sans distinction de poules
	$tmp = str_replace('|', ',', $equipes_journee);
	$items = explode(',', $tmp);
	foreach($items as $item)
		if ($item != "") $all_equipes .= $all_equipes == "" ? $item : ",".$item;
}

$classement_equipes = array();
$equipes = array();

// Recherche des équipes et création de stats vierge
if ($type_matchs == "SP")
{
	reset($liste_poules);
	while(list($cle, $equipes_poules) = each($liste_poules))
	{
		// On récupères les infos des equipes (avec init classement vierge si besoin)
		if ($equipes_poules != "")
		{
			$num_poule = $cle + 1;
			$classement_equipes[$num_poule] = "";
			$req = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id IN (".$equipes_poules.") ORDER BY nom ASC";
			$res = dbc::execSql($req);
			while($eq = mysql_fetch_array($res))
			{
				if ($classement_equipes[$num_poule] != "") $classement_equipes[$num_poule] .= "|";
				$equipes[$num_poule][$eq['id']] = $eq['nom'];
				$classement_equipes[$num_poule] .= $eq['id']."@".StatJourneeTeam::vierge();
				$nb_equipes++;
			}
		}
	}
} else if ($all_equipes != "") {
  	// On récupères les infos des equipes (avec init classement vierge si besoin)
	$classement_equipes[$niveau_type] = "";
	$req = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id IN (".$all_equipes.") ORDER BY nom ASC";
	$res = dbc::execSql($req);
	while($eq = mysql_fetch_array($res))
	{
		if ($classement_equipes[$niveau_type] != "") $classement_equipes[$niveau_type] .= "|";
		$equipes[$niveau_type][$eq['id']] = $eq['nom'];
		$classement_equipes[$niveau_type] .= $eq['id']."@".StatJourneeTeam::vierge();
		$nb_equipes++;
	}
}

// On essaie de trouver le classement des poules
$req = "SELECT * FROM jb_classement_poules WHERE id_champ=".$sess_context->getChampionnatId()." AND id_journee=".($is_journee_alias ? $id_journee_mere : $sess_context->getJourneeId());
$res = dbc::execSql($req);
while($stat_poule = mysql_fetch_array($res))
{
	$niveau_poule = explode('|', $stat_poule['poule']);
	$classement_equipes[$niveau_poule[1]] = $stat_poule['classement_equipes'];
}

if ($type_matchs == "X")
{
	$fxlist = new FXListClassementJourneeTournoi($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId(), $is_journee_alias ? $id_journee_mere : $sess_context->getJourneeId());
	echo base64_encode(serialize($fxlist->datas));
}

if ($type_matchs == "SP")
{
	$tab = array();

	reset($classement_equipes);
	while(list($cle, $classement) = each($classement_equipes))
	{
		$fxlist = new FXListMatchsStatsEquipesLight($classement, $equipes[$cle]);
		$item = array();
		$item = array(
			"lib"   => "Poule ".($sess_context->championnat['option_poule_lettre'] == 1 ? chr(ord('A')+$cle-1) : $cle),
			"datas" => $fxlist->body->tab
		);

		$tab[] = $item;
	}

	echo base64_encode(serialize($tab));
}

if ($type_matchs == "F")
{
	$fxlist = new FXListMatchsPlayOff($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $phase_finale, 0, $type_matchs);
	echo base64_encode(serialize($fxlist->body->tab));
}


mysql_close($db);

?>
