<?

header('Content-Type: text/html; charset=ISO-8859-15');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Import de matchs du type : equipe1;2-0;equipe2 ou equipe1;2/0;equipe2 ou equipe1;2:0;equipe2
// A la place des ";" on peut mettre des ","
// ////////////////////////////////////////////////////////////////////////////////////////////////////

class Match
{
	var $date;
	var $time;
	var $local;
	var $score;
	var $visitor;

	function Match()
	{
		$this->date = "";
		$this->time = "";
		$this->local = "";
		$this->score = "";
		$this->visitor = "";
	}

	function isDateEmpty() { return empty($this->date); }
	function isTimeEmpty() { return empty($this->time); }
	function isLocalEmpty() { return empty($this->local); }
	function isScoreEmpty() { return empty($this->score); }
	function isVisitorEmpty() { return empty($this->visitor); }

	function setDate($date) { $this->date = $date; }
	function setTime($time) { $this->time = $time; }
	function setLocal($local) { $this->local = $local; }
	function setScore($score) { $this->score = $score; }
	function setVisitor($visitor) { $this->visitor = $visitor; }

	function toString() { return ("[".$this->date.", ".$this->time.", ".$this->local.", ".$this->score.", ".$this->visitor."]"); }
}

$matchArray = array();
$myMatch = null;
$debug = $debugForm == 1 ? true : false;
$detected_year = "";

$dataForm = stripslashes($dataForm);
$str=explode("\n",$dataForm);
foreach($str as $item)
{
	if ($item != "")
	{
		$field_delimiter = ";";
		if (strstr($item, ",")) $field_delimiter = ",";

		$mysplit = explode($field_delimiter, $item);

		if (count($mysplit) == 3)
		{
			$myMatch = new Match();
			$myMatch->setDate(date("d/m"));
			$myMatch->setTime("21:00");
			$myMatch->setLocal(trim($mysplit[0]));
			$score_delimiter = "-";
			if (strstr($item, ":")) $score_delimiter = ":";
			if (strstr($item, "/")) $score_delimiter = "/";
			$score = explode("-", trim($mysplit[1]));
			$myMatch->setScore(trim($score[0])."-".$score[1]);
			$myMatch->setVisitor(trim($mysplit[2]));

			$matchArray[count($matchArray)] = $myMatch;
		}
	}
}

?>

<html>
<body>

<?

include "../include/inc_db.php";
include "../include/constantes.php";
include "../www/SQLServices.php";
include "../www/StatsBuilder.php";
include "../include/sess_context.php";
include "../include/cache_manager.php";

$db = dbc::connect();

$sess_context = new sess_context();

$id_championnat = $championnatForm;
$type_championnat = 0;
$id_saison = 0;
$id_journee = 0;
$matchs_joues = $playedForm;
$myYear = date("Y");

$scs = new SQLChampionnatsServices($id_championnat);
$row = $scs->getChampionnat();
$sess_context->setChampionnat($row);
$type_championnat = $row['type'];
$id_saison = $row['saison_id'];


// Reset datas
/*
$delete = "DELETE FROM jb_equipes WHERE id_champ=".$id_championnat;
$res = dbc::execSQL($delete);
$update = "UPDATE jb_saisons SET equipes='' WHERE id_champ=".$id_championnat." AND id=".$id_saison;
$res = dbc::execSQL($update);
$delete = "DELETE FROM jb_journees WHERE id_champ=".$id_saison;
$res = dbc::execSQL($delete);
$delete = "DELETE FROM jb_matchs WHERE id_champ=".$id_saison;
$res = dbc::execSQL($delete);
*/

// ////////////////////////////////////////////////
// Initialisations de données
// ////////////////////////////////////////////////
$teamList = array();
$dateList = array();
if ($action == "preview") echo "Annee : ".$myYear."<br /><table border=\"0\">";
reset($matchArray);
while(list($cle, $item) = each($matchArray))
{
	$teamList[] = $item->local;
	$teamList[] = $item->visitor;
	if (isset($dateList[$item->date])) $dateList[$item->date]++; else $dateList[$item->date] = 1;
	if ($action == "preview") echo "<tr><td>".$cle."</td><td>".$item->date."</td><td>".$item->time."</td><td>".$item->local."</td><td>".$item->score."</td><td>".$item->visitor."</td></tr>";
}
if ($action == "preview") echo "</table><br />";


if ($action == "import")
{

	// ////////////////////////////////////////////////
	// Vérification si les équipes existent dans le championnat + Récupération des ID
	// ////////////////////////////////////////////////
	$teamListString = "";
	$idTeamArray = array();
	reset($teamList);
	while(list($cle, $item) = each($teamList))
	{
		// Vérification nom équipe unique
		$select = "SELECT * FROM jb_equipes WHERE id_champ=".$id_championnat." AND nom = '".$item."'";
		$res = dbc::execSQL($select);
		if ($row = mysql_fetch_array($res))
		{
			echo "Equipe $item deja existante<br />";
			$idTeamArray[$item] = $row['id'];
		}
		else
		{
			$insert = "INSERT INTO jb_equipes (id_champ, nom) VALUES (".$id_championnat.", '".$item."');";
			$res = dbc::execSQL($insert);
			echo $insert."<br />";

			$select = "SELECT * FROM jb_equipes WHERE nom='".$item."' AND id_champ=".$id_championnat;
			$res = dbc::execSQL($select);
			$row = mysql_fetch_array($res);
			$idTeamArray[$item] = $row['id'];

			// Pour les tournois et les championnats, ajouter l'équipe à la saison en cours
			if ($type_championnat != _TYPE_LIBRE_)
			{
				$select = "SELECT * FROM jb_saisons WHERE id_champ=".$id_championnat." AND id=".$id_saison;
				$res = dbc::execSQL($select);
				if ($row = mysql_fetch_array($res))
				{
					$equipes = $row['equipes'].($row['equipes'] == "" ? "" : ",").$idTeamArray[$item];
					$update  = "UPDATE jb_saisons SET equipes='".$equipes."' WHERE id_champ=".$id_championnat." AND id=".$id_saison;
					$res = dbc::execSQL($update);
				}
			}
		}
		$teamListString .= ($teamListString == "" ? "" : ",").$idTeamArray[$item];
	}

}



// ////////////////////////////////////////////////
// Choix de la date de la journée
// ////////////////////////////////////////////////
$max = 0;
$date_journee = date('d/m');
reset($dateList);
while(list($cle, $item) = each($dateList))
{
	if ($item >= $max)
	{
		$max = $item;
		$date_journee = $cle;
	}
}

$tmp = explode('/', $date_journee);
$myDate = $myYear."-".$tmp[1]."-".$tmp[0];
echo "<br />Choix de la date de la journee : ".$myDate."<br />";



// Vérification si la journée existe et récupération de son ID
$select = "SELECT * FROM jb_journees WHERE id_champ=".$id_saison." AND date = '".$myDate."'";
$res = dbc::execSQL($select);
if ($row = mysql_fetch_array($res))
{
	$id_journee = $row['id'];
	echo "Update journee id=".$id_journee."<br />";
}
else
{

	if ($action == "import")
	{

		$select = "SELECT count(*) total FROM jb_journees WHERE id_champ=".$id_saison;
		$res = dbc::execSQL($select);
		$row = mysql_fetch_array($res);
		$indice_journee = $row['total']+1;

		$insert = "INSERT INTO jb_journees (id_champ, nom, date, joueurs, equipes, pref_saisie) VALUES (".$id_saison.", '".$indice_journee.":', '".$myDate."', '', '".$teamListString."', 1);";
		$res = dbc::execSQL($insert);
		echo $insert."<br /><br />";
		$select = "SELECT * from jb_journees WHERE id_champ=".$id_saison." AND date='".$myDate."' ORDER BY id DESC;";
		$res = dbc::execSQL($select);
		$row = mysql_fetch_array($res);
		$id_journee = $row['id'];
	}
	else
		echo "<span style=\"background: red; color: white\">Insertion d'une nouvelle journée</span>";

}



if ($action == "import")
{

	// ////////////////////////////////////////////////
	// Insertion ou update des matchs
	// ////////////////////////////////////////////////
	reset($matchArray);
	while(list($cle, $item) = each($matchArray))
	{
	//	$item->date    $item->time

		$select = "SELECT * FROM jb_matchs WHERE id_champ=".$id_saison." AND id_journee=".$id_journee." AND id_equipe1=".$idTeamArray[$item->local]." AND id_equipe2=".$idTeamArray[$item->visitor].";";
		$res = dbc::execSQL($select);
		$matchs_joues = $item->score == "-" ? 0 : $matchs_joues;
		if ($row = mysql_fetch_array($res))
		{
			$update = "UPDATE jb_matchs SET match_joue=".$matchs_joues.", resultat='".str_replace('-', '/', $item->score)."' WHERE id=".$row['id'].";";
			$res = dbc::execSQL($update);
			echo $update."<br />";
		}
		else
		{
			$insert = "INSERT INTO jb_matchs (play_date, play_time, match_joue, id_champ, id_journee, id_equipe1, id_equipe2, resultat, fanny, nbset) VALUES ('".$item->date."', '".$item->time."', ".$matchs_joues.", ".$id_saison.", ".$id_journee.", ".$idTeamArray[$item->local].", ".$idTeamArray[$item->visitor].", '".str_replace('-', '/', $item->score)."', 0, 1);";
			$res = dbc::execSQL($insert);
			echo $insert."<br />";
		}
	}

	// Mise des statistiques globales de la journée
	$stats = new StatsJourneeBuilder($id_saison, $id_journee, $type_championnat);
	$stats->SQLUpdateClassementJournee();

	mysql_close ($db);

	JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");
	JKCache::delCache("../cache/info_champ_".$sess_context->getRealChampionnatId()."_.txt", "_FLUX_INFO_CHAMP_");

	// Sauvegarde des données
	$handle = fopen("../xml/parseur_csv_".$sess_context->getChampionnatId()."_".str_replace('-', '', $myDate).".csv", 'w');
	fwrite($handle, $dataForm);

}

?>

</body>
</html>
