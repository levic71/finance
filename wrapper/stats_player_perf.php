<?

include("pChart/pData.class");
include("pChart/pChart.class");

//$datas1 ="60§70";
//$datas2 = "2012-01-01§2012-02-02";
//$moy = 65;

$datay1 = isset($datas1) && $datas1 != "" ? explode("§", $datas1) : 0;
$axes   = isset($datas2) && $datas2 != "" ? explode("§", $datas2) : 0;
$chart  = isset($chart) ? $chart : 1;
$moy    = isset($moy) ? $moy : -1;

if (count($axes) > 10)
{
	$mod = count($axes) > 20 ? 4 : 2;
	$i = 0;
	reset($axes);
	foreach($axes as $cle => $val)
	{
		if (($i++ % $mod) != 0) $axes[$cle] = "";
	}
}

// Dataset definition
$DataSet = new pData;
$DataSet->AddPoint($datay1,"Serie1");
$DataSet->AddPoint($axes,"Serie3");
$DataSet->AddSerie("Serie1");
$DataSet->SetAbsciseLabelSerie("Serie3");
$DataSet->SetSerieName("% Victoires","Serie1");

// Initialise the graph
$Test = new pChart(480,220);
$Test->drawGraphAreaGradient(90,90,90,90,TARGET_BACKGROUND);

// Prepare the graph area
$Test->setFontProperties("fonts/tahoma.ttf",8);

// Graph area !
$Test->setGraphArea(45,30,450,160);

// Initialise graph area
$Test->setFontProperties("fonts/tahoma.ttf",8);

// Draw the SourceForge Rank graph
$Test->setFixedScale(0,100,4);
if (is_array($datay1)) $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_START0,213,217,221,TRUE,45,0);

$Test->drawGraphAreaGradient(40,40,40,-50);
$Test->drawGrid(3,TRUE,180,180,180,5);
$Test->setShadowProperties(3,3,0,0,0,30,4);
if (is_array($datay1)) $Test->drawCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription());
$Test->clearShadow();
if (is_array($datay1)) $Test->drawFilledCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription(),.1,30);
if (is_array($datay1)) $Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);

// Clear the scale
$Test->clearScale();

// Ligne de la moyenne
if ($moy != -1) $Test->drawDottedLine(45, 30+((100-$moy) * 1.3), 450, 30+((100-$moy) * 1.3), 2, 255, 0, 0);

// Write the legend (box less)
$Test->setFontProperties("fonts/tahoma.ttf",8);
$Test->drawLegend(235,5,$DataSet->GetDataDescription(),0,0,0,0,0,0,255,255,255,FALSE);

// Write the title
$Test->setFontProperties("fonts/manksans.ttf",14);
$Test->setShadowProperties(1,1,0,0,0);
$Test->drawTitle(0,0,"Performance",255,255,255,190,25,TRUE);
$Test->clearShadow();

// Render the picture
$Test->Stroke();

?>
