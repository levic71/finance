<?

include ("../jpgraph/src/jpgraph.php");
include ("../jpgraph/src/jpgraph_pie.php");

// INPUTS => $titre, $datas, $legendes

// Some datas
$data = explode("|", $datas);

// Create the Pie Graph.
$graph = new PieGraph(320, 140, "auto");
$graph->SetFrame(false);
//$graph->SetShadow();

// Set A title for the plot
$graph->legend->Pos(0.02, 0.45);

// Create 3D pie plot
$p1 = new PiePlot($data);
$p1->SetSliceColors(array('#1281FC','#F66719', '#CCCCCC')); 
$p1->SetCenter(0.3);
$p1->SetSize(45);
$p1->SetColor('#FFFFFF');

// Adjust projection angle
//$p1->SetAngle(0);

// As a shortcut you can easily explode one numbered slice with
/*
if ($data[0] > $data[1])
	$p1->ExplodeSlice(1);
else
	$p1->ExplodeSlice(0);
*/

// Setup the slice values
//$p1->value->SetFont(FF_ARIAL, FS_BOLD, 11);
$p1->value->SetColor("navy");

$p1->SetLegends(explode("|", urldecode($legendes)));

$graph->Add($p1);
$graph->Stroke();

?>


