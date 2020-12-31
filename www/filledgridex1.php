<?

include ("../jpgraph/src/jpgraph.php");
include ("../jpgraph/src/jpgraph_line.php");

// Some datas
$datay1  = explode("|", $datas1);
$axes    = explode("|", $datas2);
$legends = explode("|", urldecode($legendes));
$reverse = (!isset($reverse) ? 0 : $reverse);

// Calcul de la moyenne
$nb  = 0;
$moy = 0;
foreach($datay1 as $d)
{
	if ($d != "")
	{
		$moy += $d;
		$nb++;
	}
}
$moy = $moy/$nb;

function Negate($aVal) {
  return -$aVal;
}

// Setup the graph
$graph = new Graph(680, 350);
$graph->SetMarginColor('white');
$graph->SetScale("textlin", $scale1, $scale2);
$graph->SetFrame(false);
if ($reverse == 1)
	$graph->SetMargin(30, 20, 70, 40);
else
	$graph->SetMargin(30, 20, 40, 70);

$graph->ygrid->SetFill(true, '#EFEFEF@0.5', '#BBCCFF@0.5');
$graph->xgrid->Show();

$graph->xaxis->SetTickLabels($axes);
$graph->xaxis->SetLabelAngle(90);

if ($reverse == 1)
{
	$graph->yaxis->SetLabelFormatCallback("Negate");
	$graph->xaxis->SetLabelSide(SIDE_UP);
	$graph->xaxis->SetTickSide(SIDE_DOWN);
	$graph->yaxis->HideZeroLabel();
}

// Create the first line
$p1 = new LinePlot($datay1);
$p1->SetColor("navy");
$p1->mark->SetType(MARK_FILLEDCIRCLE);
$p1->mark->SetFillColor("red");
$p1->mark->SetWidth(3);
$p1->SetLegend($legends[0]);
$graph->Add($p1);

// Add a vertical line at the end scale position '7'
$l1 = new PlotLine(HORIZONTAL, $moy);
$l1->SetColor("red");
$graph->Add($l1);

$graph->legend->SetShadow('gray@0.4', 2);
if ($reverse == 1)
	$graph->legend->SetPos(0.01, 0.85, 'right', 'top');
else
	$graph->legend->SetPos(0.01, 0.02, 'right', 'top');

// Output line
$graph->Stroke();

?>


