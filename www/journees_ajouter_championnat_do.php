<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

$annee = substr($zone_calendar, 6, 4);
$mois  = substr($zone_calendar, 3, 2);
$jour  = substr($zone_calendar, 0, 2);
$new_date = $annee."-".$mois."-".$jour;

// R�cup�ration des �quipes
$ses = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());
$liste_equipes = $ses->getListeEquipes();

// print_r($liste_equipes);

// Ajustement si nb equipes impair
if ((count($liste_equipes) % 2) == 1) $liste_equipes[-1] = array("id" => "-1", "nom" => "Equipe virtuelle");

// Calcul nb journ�es
$nb_journees = count($liste_equipes) - 1;

// R�cup�ration des ids des �quipes
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

// On m�lange
srand((float)microtime()*1000000);
shuffle($ids_equipes);

$liste_matchs = array();
$liste_dates  = array();

// R�cup�ration des dates s�lectionn�es
$i = 0;
while(list($cle, $val) = each($HTTP_POST_VARS))
{
	if (strstr($cle, "j_"))
	{
		$exp = explode("_", $cle);
		$liste_dates[$i++] = $exp[3]."-".($exp[2] > 9 ? "" : "0").$exp[2]."-".($exp[1] > 9 ? "" : "0").$exp[1];
	}
}

// On isole l'equipe pivot
$id_eq_pivot = $ids_equipes[0];
unset($ids_equipes[0]);

// On cr�� les matchs aller
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

// On cr�� les matchs retour si besoin ...
if ($allerretour == 0)
{
	$l2 = $liste_matchs;
	while(list($cle, $val) = each($l2))
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
while(list($cle, $val) = each($liste_matchs))
{
	if ($display == 1) 
		echo "<br />".$cle."<br />";
	else
	{
		$insert = "INSERT INTO jb_journees (id_champ, nom, date, heure, duree, joueurs, equipes, pref_saisie) VALUES (".$sess_context->getChampionnatId().", '".$nom."', '".$cle."', '21h00', 135, '".$str_ids_joueurs."', '".$str_ids_equipes."', 1);";
		$res = dbc::execSQL($insert);

		// On r�cup�re les infos de la journ�e
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

mysql_close ($db);

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

// On redirige sur calendar.php
ToolBox::do_redirect("calendar.php");

?>
