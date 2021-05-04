<?

include("pChart/pData.class");
include("pChart/pChart.class");

//$reverse=1;
//$scale1=-14;
//$scale2=0;
//$chart=1;
//$datas1="-2§-1§-3§-5§-2§-3§-3§-2§-1§-1§-4§-2§-4§-3§-1§-5§-3§-2§-2§-6§-2§-2§-3§-3§-2§-1";
//$datas2="03-10-11§10-10-11§17-10-11§24-10-11§07-11-11§14-11-11§21-11-11§25-11-11§05-12-11§12-12-11§09-01-12§16-01-12§27-01-12§06-02-12§13-02-12§20-02-12§27-02-12§12-03-12§19-03-12§26-03-12§30-03-12§13-04-12§27-04-12§07-05-12§14-05-12§25-05-12";
//$legendes="Evolution+classement§Moyenne";

$datay1  = isset($datas1) && $datas1 != "" ? explode("§", $datas1) : array();
$axes    = isset($datas2) && $datas2 != "" ? explode("§", $datas2) : array();
$legends = isset($legendes) && $legendes != "" ? explode("§", urldecode($legendes)) : array();
$chart   = isset($chart) ? $chart : 1;
$scale1  = isset($scale1) ? $scale1 : 0;
$scale2  = isset($scale2) ? $scale2 : 0;
$reverse = isset($reverse) ? $reverse : 0;

if ($chart == 1) {
	if (count($axes) > 10)
	{
		$mod = count($axes) > 20 ? 3 : 2;
		$i = 0;
		reset($axes);
		foreach($axes as $cle => $val)
		{
			if (($i++ % 2) != 0) $axes[$cle] = "";
		}
	}

	// Calcul de la moyenne
	$nb  = 0; $moy = 0;
	foreach($datay1 as $d)
	{
		if ($d != "") { $moy += $d; $nb++; }
	}
	$moy = $nb > 0 ? $moy/$nb : 0;

	// Dataset definition
	$DataSet = new pData;
	$DataSet->AddPoint($datay1,"Serie1");
	$DataSet->AddPoint($axes,"Serie3");
	$DataSet->AddSerie("Serie1");
	$DataSet->SetAbsciseLabelSerie("Serie3");
	$DataSet->SetSerieName($legends[0],"Serie1");

	// Initialise the graph
	$Test = new pChart(480,220);
	$Test->setFixedScale($scale1, $scale2);
/*	if ($reverse == 1)
		$Test->SetMargin(30, 20, 70, 40);
	else
		$Test->SetMargin(30, 20, 40, 70);
		*/
	$Test->drawGraphAreaGradient(90,90,90,90,TARGET_BACKGROUND);

/*
	if ($reverse == 1)
	{
		$Test->yaxis->SetLabelFormatCallback("Negate");
		$Test->xaxis->SetLabelSide(SIDE_UP);
		$Test->xaxis->SetTickSide(SIDE_DOWN);
		$Test->yaxis->HideZeroLabel();
	}
*/
	// Prepare the graph area
	$Test->setFontProperties("fonts/tahoma.ttf",8);
	$Test->setGraphArea(45,30,450,160);

	// Initialise graph area
	$Test->setFontProperties("fonts/tahoma.ttf",8);

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

	$Test->drawDottedLine(45,40+40,450,40+40,2,255,0,0);

	// Write the legend (box less)
	$Test->setFontProperties("fonts/tahoma.ttf",8);
	$Test->drawLegend(235,5,$DataSet->GetDataDescription(),0,0,0,0,0,0,255,255,255,FALSE);

	// Write the title
	$Test->setFontProperties("fonts/manksans.ttf",14);
	$Test->setShadowProperties(1,1,0,0,0);
	$Test->drawTitle(0,0,"Performance",255,255,255,190,25,TRUE);
	$Test->clearShadow();

/*
	if ($reverse == 1)
		$Test->legend->SetPos(0.01, 0.85, 'right', 'top');
	else
		$Test->legend->SetPos(0.01, 0.02, 'right', 'top');
*/
	// Render the picture
	$Test->Stroke();
}



if ($chart == 2) {
	// Dataset definition
	$DataSet = new pData;
	$DataSet->AddPoint(array("Memory","Disk","Network","Slots","CPU","CPU"),"Label");
	$DataSet->AddPoint(array(1,2,3,4,3,2),"Serie1");
	$DataSet->AddSerie("Serie1");
	$DataSet->SetAbsciseLabelSerie("Label");

	$DataSet->SetSerieName("Performance","Serie1");

	// Initialise the graph
	$Test = new pChart(400,400);
	$Test->setFontProperties("Fonts/tahoma.ttf",8);
	$Test->drawFilledRoundedRectangle(7,7,393,393,5,200,200,200);
	$Test->drawRoundedRectangle(5,5,395,395,5,230,230,230);
	$Test->setGraphArea(30,30,370,370);
	$Test->drawFilledRoundedRectangle(30,30,370,370,5,255,255,255);
	$Test->drawRoundedRectangle(30,30,370,370,5,220,220,220);

	// Draw the radar graph
	$Test->drawRadarAxis($DataSet->GetData(),$DataSet->GetDataDescription(),TRUE,20,120,120,120,230,230,230);
	$Test->drawFilledRadar($DataSet->GetData(),$DataSet->GetDataDescription(),50,20);

	// Finish the graph
	$Test->setFontProperties("Fonts/tahoma.ttf",10);

	// Render the picture
	$Test->Stroke();
}


?>
