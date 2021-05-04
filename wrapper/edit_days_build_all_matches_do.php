<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$jours = Wrapper::getRequest('jours', '');

if ($jours == "") ToolBox::do_redirect("grid.php");

// Récupération des équipes
$ses = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());
$liste_equipes = $ses->getListeEquipes();

// Ajustement si nb equipes impair
if ((count($liste_equipes) % 2) == 1) $liste_equipes[-1] = array("id" => "-1", "nom" => "Equipe virtuelle");

// Calcul nb journées
$nb_journees = count($liste_equipes) - 1;

// Récupération des ids des équipes
$str_ids_equipes = "";
$str_ids_joueurs = "";
$ids_equipes = array();
$i = 0;
foreach($liste_equipes as $item)
{
	$ids_equipes[$i++] = $item['id'];
	if ($item['id'] != -1)
	{
		$str_ids_equipes .= ($str_ids_equipes == "" ? "" : ",").$item['id'];
		$str_ids_joueurs .= ($str_ids_joueurs == "" ? "" : ",").str_replace('|', ',', $item['joueurs']);
	}
}

// On mélange
srand((float)microtime()*1000000);
shuffle($ids_equipes);

$liste_matchs = array();
$liste_dates  = array();

// Récupération des dates sélectionnées
$i = 0;
$tmp = explode(",", $jours);
foreach($tmp as $d)
{
	if (strstr($d, "j_"))
	{
		$exp = explode("_", $d);
		$liste_dates[$i++] = sprintf("%d-%02d-%02d", $exp[3], $exp[2], $exp[1]);
	}
}

// On isole l'equipe pivot
$id_eq_pivot = $ids_equipes[0];
unset($ids_equipes[0]);

// On créé les matchs aller
$ind_journee = 0;
for($x = 0; $x < $nb_journees; $x++)
{
	$date_j = $liste_dates[$ind_journee++];

	$n1 = $ids_equipes;
	$n1[99999] = $id_eq_pivot;
	$n2 = array_reverse($n1);

	$liste_matchs[$date_j] = array();

	for($z = 0; $z < (count($liste_equipes) / 2); $z++)
	{
		$liste_matchs[$date_j][] = current($n2)."-".current($n1);
		next($n1);
		next($n2);
	}

	// On fait une rotation des ids des equipes
	$id = array_shift($ids_equipes);
	array_push($ids_equipes, $id);
}

// On créé les matchs retour si besoin ...
if ($allerretour == 0)
{
	$l2 = $liste_matchs;
	foreach($l2 as $cle => $val)
	{
		$date_j = $liste_dates[$ind_journee++];

		$liste_matchs[$date_j] = array();

		foreach($val as $match)
		{
			$tmp = explode('-', $match);
			$liste_matchs[$date_j][] = $tmp[1]."-".$tmp[0];
		}
	}
}

// Insertion ou affichage
$display = 0;
$nom = 1;
foreach($liste_matchs as $cle => $val)
{
	if ($display == 1)
		echo "<br />".$cle."<br />";
	else
	{
		$insert = "INSERT INTO jb_journees (id_champ, nom, date, heure, duree, joueurs, equipes, pref_saisie) VALUES (".$sess_context->getChampionnatId().", '".$nom."', '".$cle."', '21h00', 135, '".$str_ids_joueurs."', '".$str_ids_equipes."', 1);";
		$res = dbc::execSQL($insert);

		// On récupère les infos de la journée
		$sjs2 = new SQLJourneesServices($sess_context->getChampionnatId(), -1);
		$journee = $sjs2->getJourneeByDate($cle);
	}
	foreach($val as $match)
	{
		$tmp = explode('-', $match);
		if ($display == 1)
			echo $liste_equipes[$tmp[0]]['nom']."-".$liste_equipes[$tmp[1]]['nom']."<br />";
		else
		{
   			$insert = "INSERT INTO jb_matchs (id_champ, id_journee, id_equipe1, id_equipe2, nbset, resultat, niveau, score_points) VALUES (".$sess_context->getChampionnatId().", ".$journee['id'].", ".$tmp[0].", ".$tmp[1].", 1, '0/0', '', '0|0');";
   	   		$res = dbc::execSQL($insert);
		}
	}
	$nom++;
}

mysqli_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

?>

<script>journees=""; mm({action:'days'});</script>