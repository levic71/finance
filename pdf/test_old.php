<?

include "../include/constantes.php";
include "../include/toolbox.php";
include "../www/SQLServices.php";
include "../include/inc_db.php";

$doc_title = "test title";
$doc_subject = "test description";
$doc_keywords = "test keywords";
$htmlcontent = "<h1>heading 1</h1><h2>heading 2</h2><h3>heading 3</h3><h4>heading 4</h4><h5>heading 5</h5><h6>heading 6</h6>ordered list:<br /><ol><li><b>bold text</b></li><li><i>italic text</i></li><li><u>underlined text</u></li><li><a href=\"http://www.tecnick.com\">link to http://www.tecnick.com</a></li><li>test break<br />second line<br />third line</li><li><font size=\"+3\">font + 3</font></li><li><small>small text</small></li><li>normal <sub>subscript</sub> <sup>superscript</sup></li></ul><hr />table:<br /><table border=\"1\" cellspacing=\"1\" cellpadding=\"1\"><tr><th>#</th><th>A</th><th>B</th></tr><tr><th>1</th><td>A1</td><td>B1</td></tr><tr><th>2</th><td>A2</td><td>B2</td></tr><tr><th>3</th><td>A3</td><td>B3</td></tr></table><hr />image:<br /><img src=\"images/logo_example.png\" alt=\"\" width=\"100\" height=\"100\" border=\"0\" />";

$htmlcontent = "
<table border=\"1\"cellpadding=\"1\" cellspacing=\"1\"><tr><td width=\"50\" align=\"right\" bgcolor=\"#CCCCCC\">N°</td><td width=\"200\" align=\"center\"> Nom </td><td width=\"100\"> test </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0.00 </td><td width=\"50\"> 0.00 </td></tr>
</table><table border=\"1\"cellpadding=\"1\" cellspacing=\"1\"><tr><td width=\"50\" align=\"right\" bgcolor=\"#CCCCCC\"> 2 </td><td width=\"200\"> Ajaccio </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0.00 </td><td width=\"50\"> 0.00 </td></tr>
<tr><td width=\"50\" align=\"right\" bgcolor=\"#CCCCCC\"> 2 </td><td width=\"200\"> Marseille </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0 </td><td width=\"50\"> 0.00 </td><td width=\"50\"> 0.00 </td></tr>
</table>
";

require_once('config/lang/eng.php');
require_once('tcpdf.php');


/*
if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
	$fxlist = new FXListClassementGeneralTournoi($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId(), $best_teams_tournoi);
else
	$fxlist = new FXListStatsTeamsII($best_teams_championnat);
*/


//create new PDF document (document units are set by default to millimeters)
$pdf = new TCPDF('L', PDF_UNIT, "A3", false); 
$pdf->SetDisplayMode('fullpage');

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(PDF_AUTHOR);
$pdf->SetTitle($doc_title);
$pdf->SetSubject($doc_subject);
$pdf->SetKeywords($doc_keywords);

$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); //set image scale factor

$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

$pdf->setLanguageArray($l); //set language items

//initialize document
$pdf->AliasNbPages();

$pdf->AddPage();

// set barcode
$pdf->SetBarcode(date("Y-m-d H:i:s", time()));

// output some HTML code
$pdf->writeHTML(str_replace("\n", "", $htmlcontent), true);

//Close and output PDF document
$pdf->Output();

//============================================================+
// END OF FILE                                                 
//============================================================+
?>