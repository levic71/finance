<?

include("../pChart/pChart/pData.class");
include("../pChart/pChart/pChart.class");

$datay1  = explode("|", $datas1);
$axes    = explode("|", $datas2);
$legends = explode("|", urldecode($legendes));

if (count($axes) > 10)
{
	$mod = count($axes) > 20 ? 3 : 2;
	$i = 0;
	reset($axes);
	while(list($cle, $val) = each($axes))
	{
		if (($i++ % 2) != 0) $axes[$cle] = "";
	}
}

// Calcul de la moyenne
$nb  = 0; $moy = 0;
foreach($datay1 as $d)
{
        if ($d != "") { $moy += $d;        $nb++; }
}
$moy = $moy/$nb;


// Dataset definition
$DataSet = new pData;
$DataSet->AddPoint($datay1,"Serie1");
$DataSet->AddPoint($axes,"Serie3");
$DataSet->AddSerie("Serie1");
$DataSet->SetAbsciseLabelSerie("Serie3");
$DataSet->SetSerieName("% Victoires","Serie1");

// Initialise the graph
$Test = new pChart(340,220);
$Test->drawGraphAreaGradient(90,90,90,90,TARGET_BACKGROUND);

// Prepare the graph area
$Test->setFontProperties("Fonts/tahoma.ttf",8);
$Test->setGraphArea(45,30,310,160);

// Initialise graph area
$Test->setFontProperties("Fonts/tahoma.ttf",8);

// Draw the SourceForge Rank graph
$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_START0,213,217,221,TRUE,45,0);
$Test->drawGraphAreaGradient(40,40,40,-50);
$Test->drawGrid(3,TRUE,180,180,180,5);
$Test->setShadowProperties(3,3,0,0,0,30,4);
$Test->drawCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription());
$Test->clearShadow();
$Test->drawFilledCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription(),.1,30);
$Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);

// Clear the scale
$Test->clearScale();

$Test->drawDottedLine(45,40+40,310,40+40,2,255,0,0);

// Write the legend (box less)
$Test->setFontProperties("Fonts/tahoma.ttf",8);
$Test->drawLegend(235,5,$DataSet->GetDataDescription(),0,0,0,0,0,0,255,255,255,FALSE);

// Write the title
$Test->setFontProperties("Fonts/MankSans.ttf",14);
$Test->setShadowProperties(1,1,0,0,0);
$Test->drawTitle(0,0,"Performance",255,255,255,190,25,TRUE);
$Test->clearShadow();

// Render the picture
$Test->Stroke();

?>
