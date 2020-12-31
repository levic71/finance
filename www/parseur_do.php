<?

header('Content-Type: text/html; charset=ISO-8859-15');

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

$inMatchList = false;
$inMatchItem = false;
$matchArray = array();
$myMatch = null;
$debug = $debugForm == 1 ? true : false;
$algo = $algoForm;
$detected_year = "";

$filename = "flux.xml";

function startElement($parser, $name, $attrs)
{
	global $debug, $curPathTag, $curTag, $inMatchList, $inMatchItem, $matchArray, $myMatch;

	$curTag = "^$name";
	$curPathTag .= "^$name";

	if ($curTag == "^DIV" && isset($attrs['ID']) && $attrs['ID'] == "div_matchlist")
	{
		$curTag .= "@ID=".$attrs['ID'];
		$curPathTag .= "@ID=".$attrs['ID'];
	}

	if ($curTag == "^DIV" && isset($attrs['CLASS']) && $inMatchList)
	{
		$tmp = split(" ", $attrs['CLASS']); // On prend le premier attribut de CLASS
		$curTag .= "@CLASS=".$tmp[0];
		$curPathTag .= "@CLASS=".$tmp[0];
	}

	if ($curTag == "^DIV@ID=div_matchlist") $inMatchList = true;

	if ($curTag == "^DIV@CLASS=div_matchlist_match")
	{
		$inMatchItem = true;
		if ($inMatchList) $myMatch = new Match();
	}

	if ($debug) echo ">>> ".$curTag."<br />";
}

function endElement($parser, $name)
{
	global $debug, $curPathTag, $curTag, $inMatchList, $inMatchItem, $matchArray, $myMatch;

	$caret_pos = strrpos($curPathTag,'^');

	$curTag = substr($curPathTag, $caret_pos);
	$curPathTag = substr($curPathTag,0,$caret_pos);

	if ($curTag == "^DIV@ID=div_matchlist") $inMatchList = false;

	if ($curTag == "^DIV@CLASS=div_matchlist_match")
	{
		if ($debug) echo $myMatch->toString()."<br /><br />";
		if ($inMatchList) $matchArray[count($matchArray)] = $myMatch;
		$inMatchItem = false;
	}

	if ($debug) echo $curTag." <<< <br />";
}

function characterData($parser, $data)
{
	global $debug, $curPathTag, $curTag;
	global $inMatchList, $inMatchItem, $matchArray, $myMatch;

	$matchDateKey = "^DIV@CLASS=div_matchlist_date";
	$matchTimeKey = "^DIV@CLASS=div_matchlist_time";
	$matchLocalKey = "^DIV@CLASS=div_matchlist_localteam";
	$matchScoreKey = "^DIV@CLASS=div_matchlist_score";
	$matchVisitorKey = "^DIV@CLASS=div_matchlist_visitorteam";

	if ($inMatchList && $inMatchItem)
	{
		if ($curTag == $matchDateKey && $myMatch->isDateEmpty()) $myMatch->setDate(trim($data));
		if ($curTag == $matchTimeKey && $myMatch->isTimeEmpty()) $myMatch->setTime(trim($data));
		if ($curTag == $matchLocalKey && $myMatch->isLocalEmpty()) $myMatch->setLocal(trim($data));
		if ($curTag == $matchScoreKey && $myMatch->isScoreEmpty()) $myMatch->setScore(trim($data));
		if ($curTag == $matchVisitorKey && $myMatch->isVisitorEmpty()) $myMatch->setVisitor(trim($data));
	}
}

function startElement2($parser, $name, $attrs)
{
	global $debug, $curPathTag, $curTag, $inMatchList, $inMatchItem, $matchArray, $myMatch, $detected_year;

	$curTag = "^$name";
	$curPathTag .= "^$name";

	if ($curTag == "^MATCHLIST") $inMatchList = true;

	if ($curTag == "^ROW")
	{
		$inMatchItem = true;
		if ($inMatchList)
		{
			$myMatch = new Match();
			$tmp = split(" ", $attrs['DATE_MATCH']);
			$mydate = split('-', $tmp[0]);
			$detected_year = $mydate[0];
			$myMatch->setDate(trim($mydate[2]."/".$mydate[1]));
			$mytime = split(':', $tmp[1]);
			$myMatch->setTime(trim($mytime[0].":".$mytime[1]));
			$myMatch->setLocal(trim($attrs['TXT_LOCALNAME']));
			$myMatch->setScore(trim($attrs['NUM_LOCALSCORE']."-".$attrs['NUM_VISITORSCORE']));
			$myMatch->setVisitor(trim($attrs['TXT_VISITORNAME']));
		}
	}

	if ($debug) echo ">>> ".$curTag."<br />";
}

function endElement2($parser, $name)
{
	global $debug, $curPathTag, $curTag, $inMatchList, $inMatchItem, $matchArray, $myMatch;

	$caret_pos = strrpos($curPathTag,'^');

	$curTag = substr($curPathTag, $caret_pos);
	$curPathTag = substr($curPathTag,0,$caret_pos);

	if ($curTag == "^MATCHLIST") $inMatchList = false;

	if ($inMatchList && $curTag == "^ROW")
	{
		if ($debug) echo $myMatch->toString()."<br /><br />";
		if ($inMatchList) $matchArray[count($matchArray)] = $myMatch;
		$inMatchItem = false;
	}

	if ($debug) echo $curTag." <<< <br />";
}

function characterData2($parser, $data)
{
	global $debug, $curPathTag, $curTag;
	global $inMatchList, $inMatchItem, $matchArray, $myMatch;

	if ($inMatchList && $inMatchItem)
	{
	}
}

$dataForm = stripslashes($dataForm);
$dataForm = preg_replace("/^\s+/m","",$dataForm);
$dataForm = preg_replace("/\s+$/m","",$dataForm);
$dataForm = preg_replace("/\r/","",$dataForm);
$dataForm = preg_replace("/\n/","",$dataForm);

$xml_parser = xml_parser_create();
xml_parser_set_option($xml_parser, XML_OPTION_SKIP_TAGSTART, 0);
xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1);

if ($algo == 1)
{
	xml_set_element_handler($xml_parser, "startElement2", "endElement2");
	xml_set_character_data_handler($xml_parser, "characterData2");
}
else
{
	xml_set_element_handler($xml_parser, "startElement", "endElement");
	xml_set_character_data_handler($xml_parser, "characterData");
}

if (!xml_parse($xml_parser, $dataForm, true))
	die(sprintf("XML ERROR: %s at line %d", xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser)));
xml_parser_free($xml_parser);

?>

<html>
<body>

<style>
* {
	margin: 5px;
	font-family: arial;
	font-size: 11px;
}
</style>

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
$myYear = (isset($detected_year) && $detected_year != "") ? $detected_year : $yearForm;

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
$handle = fopen("../xml/parseur_".($algo == 1 ? "xml" : "html")."_".$sess_context->getChampionnatId()."_".str_replace('-', '', $myDate).".xml", 'w');
fwrite($handle, $dataForm);

}

?>

</body>
</html>
