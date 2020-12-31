<?

require_once "../include/sess_context.php";

session_start();

require_once "../include/constantes.php";
require_once "../include/cache_manager.php";
require_once "../include/toolbox.php";

if (!isset($champ) || $champ == "") ToolBox::do_redirect("error_redirect.php");

require_once "../include/inc_db.php";
require_once "../www/SQLServices.php";
require_once "../www/ManagerFXList.php";
require_once "../www/StatsBuilder.php";
require_once "../wrapper/wrapper_fcts.php";

require_once('config/lang/eng.php');
require_once('tcpdf.php');

if (!defined("PHP_INT_MAX")) define("PHP_INT_MAX", 2147483647);

if(!function_exists('str_split')) {
  function str_split($string, $split_length = 1) {
    $array = explode("\r\n", chunk_split($string, $split_length));
    array_pop($array);
    return $array;
  }
}

function caractersConverter($str)
{
	$str = str_replace("à", "a", $str);
	$str = str_replace("á", "a", $str);
	$str = str_replace("â", "a", $str);
	$str = str_replace("ã", "a", $str);
	$str = str_replace("ä", "a", $str);
	$str = str_replace("å", "a", $str);
	$str = str_replace("æ", "ae", $str);
	$str = str_replace("ç", "c", $str);
	$str = str_replace("è", "e", $str);
	$str = str_replace("é", "e", $str);
	$str = str_replace("ê", "e", $str);
	$str = str_replace("ë", "e", $str);
	$str = str_replace("ì", "i", $str);
	$str = str_replace("í", "i", $str);
	$str = str_replace("î", "i", $str);
	$str = str_replace("ï", "i", $str);
	$str = str_replace("ð", "o", $str);
	$str = str_replace("ñ", "n", $str);
	$str = str_replace("ò", "o", $str);
	$str = str_replace("ó", "o", $str);
	$str = str_replace("ô", "o", $str);
	$str = str_replace("õ", "o", $str);
	$str = str_replace("ö", "o", $str);
	$str = str_replace("ù", "u", $str);
	$str = str_replace("ú", "u", $str);
	$str = str_replace("û", "u", $str);
	$str = str_replace("ü", "u", $str);
	$str = str_replace("ý", "y", $str);
	$str = str_replace("ÿ", "y", $str);

	return $str;
}

function getHtmlContent($nom, $tab, $format = "A4", $page, $delta)
{
	global $ratio, $real_champ_id, $saison_id, $is_tournoi, $is_championnat, $is_free, $gestion_nuls, $gestion_sets;

	$my_heightcell_table = 14 * $ratio;
	$table_width = 600 * $ratio;
	$line_width = $table_width+(40 * $ratio);

	$htmlcontent = "<table border=\"0\" width=\"".$table_width."\" cellpadding=\"".round(3 * $ratio)."\" bgcolor=\"#000000\" style=\"border: ".round(10 * $ratio)."px solid black;\">";

	$i = 1;
	foreach($tab as $item)
	{
		if (!($i >= (($page*$delta)+1) && $i <= (($page+1)*$delta))) {
			$i++;
			continue;
		}

		if ($is_tournoi)
		{

			$cols = array(
				array("",     25  * $ratio, "right",  "",        "#AAAAAA", "0"),
				array("",     225 * $ratio, "left",   "",        "#DDDDDD", "1"),
				array("Pts",  48  * $ratio, "center", "#222222", "#FFFFFF", "2"),

				array("J",    30  * $ratio, "center", "",        "#696969", "3"),
				array("G",    30  * $ratio, "center", "",        "#696969", "4"),
				array("N",    30  * $ratio, "center", "",        "#696969", $gestion_nuls ? "5" : "skip"),
				array("P",    30  * $ratio, "center", "",        "#696969", "5"),

				array("SJ",   36  * $ratio, "center", "",        "#696969", $gestion_sets ? "6" : "skip"),
				array("SG",   30  * $ratio, "center", "",        "#696969", $gestion_sets ? "7" : "skip"),
				array("SP",   30  * $ratio, "center", "",        "#696969", $gestion_sets ? "8" : "skip"),

				array("Buts", 60  * $ratio, "center", "",        "#696969", "10"),
				array("Diff", 36  * $ratio, "center", "",        "#64991e", "12"),

				array("CM",   30  * $ratio, "center", "",        "#696969", $gestion_sets ? "13" : "skip")
			);
		}
		if ($is_free)
		{
			$cols = array();
		}
		if ($is_championnat)
		{
			$cols = array(
				array("",     25  * $ratio, "right",  "",        "#AAAAAA", "indice"),
				array("",     225 * $ratio, "left",   "",        "#DDDDDD", "nom"),
				array("Pts",  48  * $ratio, "center", "#222222", "#FFFFFF", "points"),

				array("J",    30  * $ratio, "center", "",        "#696969", "matchs_joues"),
				array("G",    30  * $ratio, "center", "",        "#696969", "matchs_gagnes"),
				array("N",    30  * $ratio, "center", "",        "#696969", $gestion_nuls ? "matchs_nuls" : "skip"),
				array("P",    30  * $ratio, "center", "",        "#696969", "matchs_perdus"),

				array("SJ",   36  * $ratio, "center", "",        "#696969", $gestion_sets ? "sets_joues" : "skip"),
				array("SG",   30  * $ratio, "center", "",        "#696969", $gestion_sets ? "sets_gagnes" : "skip"),
				array("SN",   30  * $ratio, "center", "",        "#696969", $gestion_sets ? "sets_nuls" : "skip"),
				array("SP",   30  * $ratio, "center", "",        "#696969", $gestion_sets ? "sets_perdus" : "skip"),

				array("Buts", 60  * $ratio, "center", "",        "#696969", "buts_marques"),
				array("Diff", 36  * $ratio, "center", "",        "#64991e", "diff")
			);
		}

		// Somme des largeurs des colonnes = 640

		$nb_pull_col = 0;
		$over_width  = 0;
		$extra_width = 0;

		if (!$gestion_nuls) { $over_width += (30 * $ratio); $nb_pull_col += 1; }
		if (!$gestion_sets) { $over_width += (126 * $ratio); $nb_pull_col += 4; }
		if ($nb_pull_col > 0) $extra_width = ($over_width / (count($cols) - $nb_pull_col));

		if ($i == (($page * $delta) + 1)) {
			$htmlcontent .= "<tr><td colspan=\"100\"  width=\"".$line_width."\" height=\"".round(30 * $ratio)."\" style=\"font-size: ".round(50 * $ratio)."px;\">&nbsp;&nbsp;".strtoupper($nom)."</td></tr>";
			$htmlcontent .= "<tr>";
			reset($cols);
			foreach($cols as $c) {
				if ($c[5] == "skip") continue;
				$htmlcontent .= "<td width=\"".($c[1] + $extra_width)."\" align=\"center\" bgcolor=\"#000000\" color=\"#007ead\" height=\"".$my_heightcell_table."\">".$c[0]."</td>";
			}
			$htmlcontent .= "</tr>";
		}

		$htmlcontent .= "<tr bgcolor=\"#111\">";
		reset($cols);
		foreach($cols as $c) {
			if ($c[5] == "skip") continue;
			$val = $i;
			if (isset($item[$c[5]])) $val = $item[$c[5]];
			if (($is_tournoi && $c[5] == "1") || ($is_championnat && $c[5] == "nom")) $val = strtoupper(caractersConverter(ereg_replace("<.*>", "", str_replace("</A>", "", $item[$c[5]]))));

			if ($c[5] == "buts_marques") $val = $item[$c[5]]."/".$item['buts_encaisses'];
			if ($is_tournoi && $c[0] == "Buts") $val = $item[$c[5]]."/".$item[11];
			if ($c[0] == "Diff" && $item[$c[5]] < 0) $c[4] = "#d81b21";

			$border = "style=\"border-bottom: 1px solid #1c1c1c;\"";
			if ($i == 1 && $c[0] != "Diff") $c[4] = "#f78d1d";
			if (($i == 2 || $i == 3) && $c[0] != "Diff") $c[4] = "#FFCC00";
			if ($i > max(12, count($tab)-3) && $c[5] != "diff") $c[4] = "#666";
			if ($i == 1 || $i == 2 || $i == 3) $border = "style=\"border-bottom: 1px solid #333;\"";
			$htmlcontent .= "<td ".$border." width=\"".($c[1] + $extra_width)."\" align=\"".$c[2]."\" ".($c[3] != "" ? "bgcolor=\"".$c[3]."\"" : "")." color=\"".$c[4]."\" height=\"".$my_heightcell_table."\">".$val."</td>";
		}
		$htmlcontent .= "</tr>";

		$i++;

		if ($i > ($page+1)*$delta ) break;
	}

	$htmlcontent .= "<tr><td style=\"font-size: ".round(20 * $ratio)."px;\" align=\"right\" width=\"".$line_width."\" colspan=\"100\" height=\"".round(12 * $ratio)."\">Pts: Points, [J,G,N,P]: [Matchs joués, gagnés, nuls, perdus], [SJ,SG,SN,SP]: [Sets joués, gagnés, nuls, perdus], Buts: Buts marqués/encaissés".($is_tournoi ? ", CM: Classement moyen" : "")."<br />Publié le ".date("j-m-Y")."</td></tr>";

	$htmlcontent .= "</table>";

	return $htmlcontent;
}

if (!isset($format) || $format == "" || !($format == "A3" || $format == "A4" || $format == "A5") ) $format = "A4";

$db = dbc::connect();

$scs = new SQLChampionnatsServices($champ);
$row = $scs->getChampionnat();

if (!$row) {
	ToolBox::do_redirect("error_redirect.php");
	exit(0);
}

$real_champ_id   = $row['championnat_id'];
$saison_id       = $row['saison_id'];
$is_tournoi      = $row['type'] == _TYPE_TOURNOI_ ? true : false;
$is_championnat  = $row['type'] == _TYPE_CHAMPIONNAT_ ? true : false;
$is_free         = $row['type'] == _TYPE_LIBRE_ ? true : false;
$gestion_nuls    = $row['gestion_nul'] == 1 ? true : false;
$gestion_sets    = $row['gestion_sets'] == 1 ? true : false;

Toolbox::trackUser($real_champ_id, _TRACK_PDF_);

$sgb = JKCache::getCache("../cache/stats_champ_".$real_champ_id."_".$saison_id.".txt", 24*60*60, "_FLUX_STATS_CHAMP_");

if ($is_tournoi) {
	$best_teams_tournoi = $sgb->getBestTeamsByTournoiPoints();
	$fxlist = new FXListClassementGeneralTournoi($real_champ_id, $saison_id, $best_teams_tournoi);
}

if ($is_free) {
	$fxlist = new FXListStatsJoueurs($sgb);
	exit(0);
}

if ($is_championnat) {
	$best_teams_championnat = $sgb->getBestTeamsByPoints();
	$fxlist = new FXListStatsTeamsII($best_teams_championnat);
}

$mytab = array();
foreach($fxlist->body->tab as $item) {

	if ($is_tournoi && $item['1'] == "X") continue;
	if ($is_free && $item['pseudo'] == "F") continue;

	$mytab[] = $item;
}

//print_r($fxlist->body->tab);
//print_r($mytab));
//exit(0);

$ratio = $format == "A5" ? 0.707 : ($format == "A3" ? 1.41421 : 1);
$my_fontsize_header = 20 * $ratio;
$my_posx_table = 14 * $ratio;
$my_posy_table = 90 * $ratio;
$my_fontsize_table = 8 * $ratio;

//create new PDF document (document units are set by default to millimeters)
$pdf = new TCPDF('P', PDF_UNIT, $format, false);

$pdf->SetDisplayMode('fullpage');

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set document information
$pdf->SetCreator("Jorkers.com");
$pdf->SetAuthor("Jorkers.com");
$pdf->SetTitle("Classement ".strtoupper($row['championnat_nom']));
$pdf->SetSubject("Classement ".strtoupper($row['championnat_nom']));
$pdf->SetKeywords("Jorkers.com Classemment ".strtoupper($row['championnat_nom']));

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); //set image scale factor
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', $my_fontsize_header));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->setLanguageArray($l); //set language items

//initialize document
$pdf->AliasNbPages();

$delta = 32;
for($k = 0; $k < ceil(count($mytab) / $delta); $k++) {

	$pdf->AddPage();

	// get the current page break margin
	$bMargin = $pdf->getBreakMargin(); $auto_page_break = $pdf->getAutoPageBreak(); $pdf->SetAutoPageBreak(false, 0);
	$pdf->Image("images/fond.jpg", 0, 0, $pdf->getPageWidth(), $pdf->getPageHeight());
	// restore auto-page-break status
	$pdf->SetAutoPageBreak($auto_page_break, 0); $pdf->setPageMark();

	// output some HTML code
	$pdf->SetFontSize($my_fontsize_table);
	$pdf->SetTextColor(100, 100, 100);
	$pdf->SetDrawColor(150, 150, 150);
	$pdf->SetLeftMargin($my_posx_table);
	$pdf->SetY($my_posy_table);

	$content = getHtmlContent($row['championnat_nom'], $mytab, $format, $k, $delta);
	$pdf->writeHTML(str_replace("\n", "", $content), true, 0);

	// new style
	$style = array(
		'border' => false,
		'vpadding' => 'auto',
		'hpadding' => 'auto',
		'fgcolor' => array(0,0,0),
		'bgcolor' => array(255,255,255),
		'module_width' => 1, // width of a single module in points
		'module_height' => 1 // height of a single module in points
	);
	$pdf->write2DBarcode('http://'.Wrapper::string2DNS($row['championnat_nom']).'.jorkers.com', 'DATAMATRIX', ((210 - 30) * $ratio) - $my_posx_table, 10 * $ratio, 30 * $ratio, 30 * $ratio, $style, 'N');

}

//Close and output PDF document
$pdf->Output();

mysql_close($db);

?>