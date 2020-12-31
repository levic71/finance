<?

include "../include/sess_context.php";
include "../include/constantes.php";
include "../include/cache_manager.php";
include "../include/toolbox.php";

if (!isset($champ) || $champ == "") ToolBox::do_redirect("error_redirect.php");
// session_register("sess_context");

$sess_context = new sess_context();
$_SESSION["sess_context"] = $sess_context;

include "../include/inc_db.php";
include "../www/SQLServices.php";
include "../www/ManagerFXList.php";
include "../www/StatsBuilder.php";

function caractersConverter($str)
{
	$str = str_replace("�", "a", $str);
	$str = str_replace("�", "a", $str);
	$str = str_replace("�", "a", $str);
	$str = str_replace("�", "a", $str);
	$str = str_replace("�", "a", $str);
	$str = str_replace("�", "a", $str);
	$str = str_replace("�", "ae", $str);
	$str = str_replace("�", "c", $str);
	$str = str_replace("�", "e", $str);
	$str = str_replace("�", "e", $str);
	$str = str_replace("�", "e", $str);
	$str = str_replace("�", "e", $str);
	$str = str_replace("�", "i", $str);
	$str = str_replace("�", "i", $str);
	$str = str_replace("�", "i", $str);
	$str = str_replace("�", "i", $str);
	$str = str_replace("�", "o", $str);
	$str = str_replace("�", "n", $str);
	$str = str_replace("�", "o", $str);
	$str = str_replace("�", "o", $str);
	$str = str_replace("�", "o", $str);
	$str = str_replace("�", "o", $str);
	$str = str_replace("�", "o", $str);
	$str = str_replace("�", "u", $str);
	$str = str_replace("�", "u", $str);
	$str = str_replace("�", "u", $str);
	$str = str_replace("�", "u", $str);
	$str = str_replace("�", "y", $str);
	$str = str_replace("�", "y", $str);
	
	return $str;
}

if (!isset($format) || $format == "") $format = "A4";

$db = dbc::connect();

$scs = new SQLChampionnatsServices($champ);
$row = $scs->getChampionnat();

if ($row)
{
	$row['login'] = "";
	$row['pwd']   = "";
	$sess_context->setChampionnat($row);

	Toolbox::trackUser($sess_context->getRealChampionnatId(), _TRACK_PDF_);

	$sgb = JKCache::getCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", 24*60*60, "_FLUX_STATS_CHAMP_");

	$best_teams_tournoi = $sgb->getBestTeamsByTournoiPoints();
	$best_teams_championnat = $sgb->getBestTeamsByPoints();

	if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
		$fxlist = new FXListClassementGeneralTournoi($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId(), $best_teams_tournoi);

	if ($sess_context->getChampionnatType() == _TYPE_LIBRE_)
		$fxlist = new FXListStatsJoueurs($sgb);

	if ($sess_context->getChampionnatType() == _TYPE_CHAMPIONNAT_)
		$fxlist = new FXListStatsTeamsII($best_teams_championnat);

//	print_r($fxlist->body->tab);
//	exit(0);

	$doc_title = "test title";
	$doc_subject = "test description";
	$doc_keywords = "test keywords";

	require_once('config/lang/eng.php');
	require_once('tcpdf.php');

	// A3
	if ($format == "A3")
	{
		$my_pdf_size = "A3";
		$my_fontsize_hearder = 40;
		$my_posy_hearder = 40;
		$my_posx_colsname = array("26", "60", "105", "118", "130", "142", "154", "166", "180", "191", "204", "216", "228", "243", "256", "268");
		$my_posy_colsname = 97;
		$my_angle_colsname = 80;
		$my_fontsize_colsname = 12;
		$my_posx_table = 19.2;
		$my_posy_table = 99.3;
		$my_fontsize_table = 12;
		$my_heightcell_table = 20;
		$my_widthcell_table = array("50", "265", "50", "50", "50", "50", "50", "50", "50", "50", "50", "50", "50", "50", "50", "50");
	}

	// A4
	if ($format == "A4")
	{
		$my_pdf_size = "A4";
		$my_fontsize_hearder = 20;
		$my_posy_hearder = 22;
		$my_posx_colsname = array("19", "38", "73", "82", "91", "100", "109", "118", "127", "136", "145", "153", "162", "171", "180", "189");
		$my_posy_colsname = 68;
		$my_angle_colsname = 90;
		$my_fontsize_colsname = 10;
		$my_posx_table = 13.6;
		$my_posy_table = 70.2;
		$my_fontsize_table = 8;
		$my_heightcell_table = 14;
		$my_widthcell_table = array("36", "178", "36", "36", "36", "36", "36", "36", "36", "36", "36", "36", "36", "36", "36", "36");
	}

	//create new PDF document (document units are set by default to millimeters)
	$pdf = new TCPDF('P', PDF_UNIT, $my_pdf_size, false);
	$pdf->SetDisplayMode('fullpage');

	// set document information
	$pdf->SetCreator("Jorkers.com");
	$pdf->SetAuthor("Jorkers.com");
	$pdf->SetTitle("Classement");
	$pdf->SetSubject("Classement");
	$pdf->SetKeywords("Jorkers.com");

//	$pdf->SetHeaderData("jorkers_logo.jpg", 45, $row['championnat_nom'], "");

	//set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); //set image scale factor

	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', $my_fontsize_hearder));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	$pdf->setLanguageArray($l); //set language items

	$htmlcontent = "<table border=\"1\" width=\"600\">";

	$tot = count($fxlist->body->tab);

	$height = $tot < 16 ? 30 : $my_heightcell_table;

	$i = 0;
	foreach($fxlist->body->tab as $item)
	{
		if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_ && $item['1'] == "X") continue;
		if ($sess_context->getChampionnatType() == _TYPE_LIBRE_ && $item['pseudo'] == "F") continue;

		$htmlcontent .= "<tr>";
		if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
		{
			$htmlcontent .= "<td width=\"".$my_widthcell_table[0]."\" align=\"right\" bgcolor=\"#52595a\" color=\"#AAAAAA\" height=\"".$height."\">".$item['0']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[1]."\" align=\"left\"  bgcolor=\"#42494a\" color=\"#DDDDDD\">".strtoupper(caractersConverter(ereg_replace("<.*>", "", str_replace("</A>", "", $item['1']))))."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[2]."\"  align=\"right\" bgcolor=\"#941408\" color=\"#FFFFFF\">".$item['2']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[3]."\"  align=\"right\" bgcolor=\"#e6ee13\" color=\"#696969\">".$item['3']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[4]."\"  align=\"right\" bgcolor=\"#9cdb00\">".$item['4']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[5]."\"  align=\"right\" bgcolor=\"#89b7cf\">".$item['5']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[6]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['6']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[7]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['7']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[8]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['8']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[9]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['9']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[10]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['10']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[11]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['11']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[12]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['12']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[13]."\"  align=\"right\" bgcolor=\"#52595a\" color=\"#AAAAAA\">".$item['13']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[14]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['14']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[15]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['15']."</td>";
		}
		if ($sess_context->getChampionnatType() == _TYPE_LIBRE_)
		{
			$htmlcontent .= "<td width=\"".$my_widthcell_table[0]."\" align=\"right\" bgcolor=\"#52595a\" color=\"#AAAAAA\" height=\"".$height."\">".$i."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[1]."\" align=\"left\"  bgcolor=\"#42494a\" color=\"#DDDDDD\">".strtoupper(caractersConverter(ereg_replace("<.*>", "", str_replace("</A>", "", $item['pseudo']))))."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[2]."\"  align=\"right\" bgcolor=\"#941408\" color=\"#FFFFFF\">".$item['presence']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[3]."\"  align=\"right\" bgcolor=\"#e6ee13\" color=\"#696969\">".$item['pourc_joues']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[4]."\"  align=\"right\" bgcolor=\"#9cdb00\">".$item['pourc_gagnes']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[5]."\"  align=\"right\" bgcolor=\"#89b7cf\">".$item['pourc_gagnes']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[6]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['pourc_gagnes']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[7]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['pourc_gagnes']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[8]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['pourc_gagnes']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[9]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['pourc_gagnes']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[10]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['pourc_gagnes']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[11]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['pourc_gagnes']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[12]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['pourc_gagnes']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[13]."\"  align=\"right\" bgcolor=\"#52595a\" color=\"#AAAAAA\">".$item['pourc_gagnes']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[14]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['pourc_gagnes']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[15]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['pourc_gagnes']."</td>";
		}
		if ($sess_context->getChampionnatType() == _TYPE_CHAMPIONNAT_)
		{
			$htmlcontent .= "<td width=\"".$my_widthcell_table[0]."\" align=\"right\" bgcolor=\"#52595a\" color=\"#AAAAAA\" height=\"".$height."\">".$i."</td>";
			$htmlcontent .= "<td width=\"".($my_widthcell_table[1]+$my_widthcell_table[14]+$my_widthcell_table[15])."\" align=\"left\"  bgcolor=\"#42494a\" color=\"#DDDDDD\">".strtoupper(caractersConverter(ereg_replace("<.*>", "", str_replace("</A>", "", $item['nom']))))."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[2]."\"  align=\"right\" bgcolor=\"#941408\" color=\"#FFFFFF\">".$item['points']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[3]."\"  align=\"right\" bgcolor=\"#e6ee13\" color=\"#696969\">".$item['matchs_joues']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[4]."\"  align=\"right\" bgcolor=\"#9cdb00\">".$item['matchs_gagnes']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[5]."\"  align=\"right\" bgcolor=\"#89b7cf\">".$item['matchs_nuls']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[6]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['matchs_perdus']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[7]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['sets_joues']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[8]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['sets_gagnes']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[9]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['sets_nuls']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[10]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['sets_perdus']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[11]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['buts_marques']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[12]."\"  align=\"right\" bgcolor=\"#DDDDDD\">".$item['buts_encaisses']."</td>";
			$htmlcontent .= "<td width=\"".$my_widthcell_table[13]."\"  align=\"right\" bgcolor=\"#52595a\" color=\"#AAAAAA\">".$item['diff']."</td>";

	/*
	Array ( [0] => Array ( [id] => 27 [nom] => VERNIZEAU [prenom] => Charles [pseudo] => Charly [dt_naissance] => 2003-04-17 [photo] => ../uploads/8_Charly_charly_____.jpg [presence] => 1 [email] => charles.vernizeau@unilog.fr [etat] => 0 [joues] => 58 [jouesA] => 28 [jouesD] => 30 [gagnes] => 26 [nuls] => 0 [perdus] => 32 [marquesA] => 142 [encaissesD] => 152 [forme_participation] => 4 [forme_joues] => 34 [forme_gagnes] => 41 [moy_marquesA] => 5.07 [moy_encaissesD] => 5.07 [pourc_joues] =>
	85 %
	[pourc_gagnes] =>
	44.83 %
	[pourc_nuls] => 0 [pourc_perdus] => 55.172413793103 [forme_indice] => Etat de forme sur les 4 derni�res journ�es [forme_last_gagnes] => 60 [forme_last_indice] => Derni�re journ�e jou�e : 2006-12-21 [60 % matchs gagn�s] [forme_last_date] => 2006-12-21 [podium] => 0 [polidor] => 2 [evol_pourc_gagne] => Array ( [2006-08-31] => 42 [2006-09-07] => 55 [2006-09-14] => 50 [2006-09-21] => 22 [2006-11-23] => 57 [2006-12-07] => 25 [2006-12-21] => 60 ) [fanny_in] => 2 [fanny_out] => 2 [justesse_gagnes] => 2 [justesse_perdus] => 5 [sets_joues] => 58 [sets_gagnes] => 26 [sets_nuls] => 0 [sets_perdus] => 32 [sets_diff] => -6 [lib_presence] => R�gulier [joueur] =>
	Charly
)
*/
		}
		$htmlcontent .= "</tr>";

		$i++;
	}

	$htmlcontent .= "<tr><td width=\"100\" height=\"12\">".date("j-m-Y")."</td></tr>";

	$htmlcontent .= "</table>";

	//initialize document
	$pdf->AliasNbPages();

	$pdf->AddPage();


	$pdf->Image("images/fond.jpg", 0, 0, $pdf->fw, $pdf->fh);


	$coul = $pdf->convertColorHexToDec("#FFFFFF");
	$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
	$pdf->SetXY(20, $my_posy_hearder);
	$pdf->Cell($pdf->fw-20-20, 20, strtoupper($row['championnat_nom']), 0, 1, 'C');


	// output some HTML code
	$pdf->SetFontSize($my_fontsize_colsname);
	$angle = $my_angle_colsname;
	$coul = $pdf->convertColorHexToDec("#FFFFFF");
	$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);

	if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
	{
		$pdf->TextWithRotation($my_posx_colsname[0],  $my_posy_colsname, "Podium", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[1],  $my_posy_colsname, "Equipe", 0, 0);

		$coul = $pdf->convertColorHexToDec("#660000");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[2], $my_posy_colsname, "Points", $angle, 0);

		$coul = $pdf->convertColorHexToDec("#FFFFFF");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[3], $my_posy_colsname, "Matchs jou�s", $angle, 0);

		$coul = $pdf->convertColorHexToDec("#006600");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[4], $my_posy_colsname, "Matchs gagn�s", $angle, 0);

		$coul = $pdf->convertColorHexToDec("#0070aa");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[5], $my_posy_colsname, "Matchs perdus", $angle, 0);

		$coul = $pdf->convertColorHexToDec("#FFFFFF");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[6], $my_posy_colsname, "Sets jou�s", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[7], $my_posy_colsname, "Sets gagn�s", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[8], $my_posy_colsname, "Sets perdus", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[9], $my_posy_colsname, "Diff�rence", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[10], $my_posy_colsname, "Buts marqu�s", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[11], $my_posy_colsname, "Buts encaiss�s", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[12], $my_posy_colsname, "Diff�rence", $angle, 0);

		$coul = $pdf->convertColorHexToDec("#52595a");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[13], $my_posy_colsname, "Moy classement", 90, 0);

		$coul = $pdf->convertColorHexToDec("#696969");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[14], $my_posy_colsname, "Moy buts marqu�s", 90, 0);

		$coul = $pdf->convertColorHexToDec("#696969");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[15], $my_posy_colsname, "Moy buts encaiss�s", 90, 0);
	}
	if ($sess_context->getChampionnatType() == _TYPE_LIBRE_)
	{
		$pdf->TextWithRotation($my_posx_colsname[0],  $my_posy_colsname, "Podium", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[1],  $my_posy_colsname, "Equipe", 0, 0);

		$coul = $pdf->convertColorHexToDec("#660000");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[2], $my_posy_colsname, "Points", $angle, 0);

		$coul = $pdf->convertColorHexToDec("#FFFFFF");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[3], $my_posy_colsname, "Matchs jou�s", $angle, 0);

		$coul = $pdf->convertColorHexToDec("#006600");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[4], $my_posy_colsname, "Matchs gagn�s", $angle, 0);

		$coul = $pdf->convertColorHexToDec("#0070aa");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[5], $my_posy_colsname, "Matchs perdus", $angle, 0);

		$coul = $pdf->convertColorHexToDec("#FFFFFF");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[6], $my_posy_colsname, "Sets jou�s", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[7], $my_posy_colsname, "Sets gagn�s", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[8], $my_posy_colsname, "Sets perdus", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[9], $my_posy_colsname, "Diff�rence", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[10], $my_posy_colsname, "Buts marqu�s", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[11], $my_posy_colsname, "Buts encaiss�s", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[12], $my_posy_colsname, "Diff�rence", $angle, 0);

		$coul = $pdf->convertColorHexToDec("#52595a");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[13], $my_posy_colsname, "Moy classement", 90, 0);

		$coul = $pdf->convertColorHexToDec("#696969");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[14], $my_posy_colsname, "Moy buts marqu�s", 90, 0);

		$coul = $pdf->convertColorHexToDec("#696969");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[15], $my_posy_colsname, "Moy buts encaiss�s", 90, 0);
	}
	if ($sess_context->getChampionnatType() == _TYPE_CHAMPIONNAT_)
	{
		$pdf->TextWithRotation($my_posx_colsname[0],  $my_posy_colsname, "Podium", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[1],  $my_posy_colsname, "Equipe", 0, 0);

		$coul = $pdf->convertColorHexToDec("#660000");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[4], $my_posy_colsname, "Points", $angle, 0);

		$coul = $pdf->convertColorHexToDec("#FFFFFF");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[5], $my_posy_colsname, "Matchs jou�s", $angle, 0);

		$coul = $pdf->convertColorHexToDec("#006600");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[6], $my_posy_colsname, "Matchs gagn�s", $angle, 0);

		$coul = $pdf->convertColorHexToDec("#0070aa");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[7], $my_posy_colsname, "Matchs nuls", $angle, 0);

		$coul = $pdf->convertColorHexToDec("#FFFFFF");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[8], $my_posy_colsname, "Matchs perdus", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[9], $my_posy_colsname, "Sets jou�s", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[10], $my_posy_colsname, "Sets gagn�s", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[11], $my_posy_colsname, "Sets nuls", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[12], $my_posy_colsname, "Sets perdus", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[13], $my_posy_colsname, "Buts marqu�s", $angle, 0);
		$pdf->TextWithRotation($my_posx_colsname[14], $my_posy_colsname, "Buts encaiss�s", $angle, 0);

		$coul = $pdf->convertColorHexToDec("#52595a");
		$pdf->SetTextColor($coul['R'], $coul['G'], $coul['B']);
		$pdf->TextWithRotation($my_posx_colsname[15], $my_posy_colsname, "Diff�rence", 90, 0);
	}

	$pdf->SetFontSize($my_fontsize_table);
	$pdf->SetTextColor(100, 100, 100);
	$pdf->SetDrawColor(150, 150, 150);
	$pdf->SetLeftMargin($my_posx_table);
	$pdf->SetY($my_posy_table);
	$pdf->writeHTML(str_replace("\n", "", $htmlcontent), true, 0);


	//Close and output PDF document
	$pdf->Output();
//	echo $htmlcontent;

	//============================================================+
	// END OF FILE
	//============================================================+

	exit(0);
}

ToolBox::do_redirect("error_redirect.php");

mysql_close($db);

?>