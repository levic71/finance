<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>amCharts</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="style.css" rel="stylesheet" type="text/css">
<script src="amstock.js" type="text/javascript"></script>
<script src="amfallback.js" type="text/javascript"></script>
<script src="data.js" type="text/javascript"></script>
<script src="stats_fcts.js" type="text/javascript"></script>
<script src="stats_globales.js?ts=<?= date("YmdH") ?>00" type="text/javascript"></script>
</head>
<body>

<?

include("stats_globales.php");

$db = mysqli_connect("localhost", "root", "") or die("Impossible de se connecter : " . mysqli_error());
mysqli_select_db('jk');

//for($i = 0; $i < 366; $i++) echo rand(0, 1000).',';
$val = "207,909,596,950,645,145,709,240,111,575,897,834,576,792,880,72,508,891,877,803,875,463,607,366,146,604,287,608,290,907,638,872,777,466,877,1000,574,875,723,337,149,226,396,727,474,115,614,513,14,635,595,540,741,380,58,150,121,443,223,189,125,418,809,1000,221,398,31,424,377,979,613,809,62,252,770,996,246,814,222,329,396,756,187,652,482,672,386,310,973,657,35,145,836,303,855,502,540,706,62,224,55,458,377,655,851,654,18,639,894,887,705,519,653,250,656,139,98,339,588,844,698,245,722,476,420,564,775,380,735,389,968,399,610,313,17,877,514,430,143,658,416,335,63,84,785,120,999,1,590,381,665,754,298,209,283,182,881,200,571,633,804,446,748,949,38,542,532,473,51,582,142,51,814,158,297,25,792,366,216,239,956,798,617,37,774,548,720,263,215,210,242,261,749,879,403,874,342,147,922,445,465,108,15,820,86,356,405,340,674,986,308,852,197,590,444,697,124,262,31,720,425,596,788,264,568,687,934,173,521,126,186,792,753,9,765,964,233,930,389,30,431,981,276,839,313,757,146,731,350,351,217,551,510,357,208,642,262,870,338,848,575,481,325,514,917,286,389,482,256,878,247,96,62,351,119,201,304,299,724,140,451,815,279,757,470,493,505,395,832,427,619,898,503,210,340,610,955,938,888,375,153,201,981,537,569,96,837,939,170,215,175,925,426,654,215,337,305,3,676,523,276,359,774,323,307,315,230,81,868,964,262,644,926,900,589,352,952,216,430,50,912,508,503,83,277,868,27,293,15,890,722,25,595,667,805,122,986,795,995,929,656,365,511,287,573,973";

$sg = new Stats_globales();
$sg->update_year("2012", $val, $val, $val);

mysqli_close($db);

?>

<script type="text/javascript">
var chart2;
AmCharts.ready(function () {
	// SERIAL CHART
	chart2 = new AmCharts.AmSerialChart();
	chart2.dataProvider = chartData2;
	chart2.categoryField = "country";
	chart2.color = "#FFFFFF";
	chart2.startDuration = 1;
	chart2.plotAreaFillAlphas = 0.2;
	// the following two lines makes chart 3D
	chart2.angle = 30;
	chart2.depth3D = 60;

	// AXES
	// category
	var categoryAxis = chart2.categoryAxis;
	categoryAxis.gridAlpha = 0.2;
	categoryAxis.gridPosition = "start";
	categoryAxis.gridColor = "#FFFFFF";
	categoryAxis.axisColor = "#FFFFFF";
	categoryAxis.axisAlpha = 0.5;
	categoryAxis.dashLength = 5;

	// value
	var valueAxis = new AmCharts.ValueAxis();
	valueAxis.stackType = "3d"; // This line makes chart 3D stacked (columns are placed one behind another)
	valueAxis.gridAlpha = 0.2;
	valueAxis.gridColor = "#FFFFFF";
	valueAxis.axisColor = "#FFFFFF";
	valueAxis.axisAlpha = 0.5;
	valueAxis.dashLength = 5;
	valueAxis.title = "GDP growth rate"
	valueAxis.titleBold = false;
	valueAxis.unit = "%";
	chart2.addValueAxis(valueAxis);

	// GRAPHS
	// first graph
	var graph1 = new AmCharts.AmGraph();
	graph1.title = "2004";
	graph1.valueField = "year2004";
	graph1.type = "column";
	graph1.lineAlpha = 0;
	graph1.lineColor = "#D2CB00";
	graph1.fillAlphas = 1;
	graph1.balloonText = "GDP grow in [[category]] (2004): [[value]]";
	chart2.addGraph(graph1);

	// second graph
	var graph2 = new AmCharts.AmGraph();
	graph2.title = "2005";
	graph2.valueField = "year2005";
	graph2.type = "column";
	graph2.lineAlpha = 0;
	graph2.lineColor = "#BEDF66";
	graph2.fillAlphas = 1;
	graph2.balloonText = "GDP grow in [[category]] (2005): [[value]]";
	chart2.addGraph(graph2);

	chart2.write("chartdiv2");
});


var chart4;
AmCharts.ready(function () {
	// SERIAL CHART
	chart4 = new AmCharts.AmSerialChart();
	chart4.dataProvider = chartData4;
	chart4.categoryField = "date";
	chart4.marginTop = 0;

	// AXES
	// category axis
	var categoryAxis = chart4.categoryAxis;
	categoryAxis.parseDates = true; // as our data is date-based, we set parseDates to true
	categoryAxis.minPeriod = "DD"; // our data is daily, so we set minPeriod to DD
	categoryAxis.autoGridCount = false;
	categoryAxis.gridCount = 50;
	categoryAxis.gridAlpha = 0;
	categoryAxis.gridColor = "#000000";
	categoryAxis.axisColor = "#555555";
	// we want custom date formatting, so we change it in next line
	categoryAxis.dateFormats = [
		{ period: "DD", format: "DD" },
		{ period: "WW", format: "MMM DD" },
		{ period: "MM", format: "MMM" },
		{ period: "YYYY", format: "YYYY" }
	];

	// as we have data of different units, we create two different value axes
	// Duration value axis
	var durationAxis = new AmCharts.ValueAxis();
	durationAxis.title = "duration";
	durationAxis.gridAlpha = 0.05;
	durationAxis.axisAlpha = 0;
	durationAxis.inside = true;
	// the following line makes this value axis to convert values to duration
	// it tells the axis what duration unit it should use. mm - minute, hh - hour...
	durationAxis.duration = "mm";
	durationAxis.durationUnits = {
		DD: "d. ",
		hh: "h ",
		mm: "min",
		ss: ""
	};
	chart4.addValueAxis(durationAxis);

	// Distance value axis
	var distanceAxis = new AmCharts.ValueAxis();
	distanceAxis.title = "distance";
	distanceAxis.gridAlpha = 0;
	distanceAxis.position = "right";
	distanceAxis.inside = true;
	distanceAxis.unit = "mi";
	distanceAxis.axisAlpha = 0;
	chart4.addValueAxis(distanceAxis);

	// GRAPHS
	// duration graph
	var durationGraph = new AmCharts.AmGraph();
	durationGraph.title = "duration";
	durationGraph.valueField = "duration";
	durationGraph.type = "line";
	durationGraph.valueAxis = durationAxis; // indicate which axis should be used
	durationGraph.lineColor = "#CC0000";
	durationGraph.balloonText = "[[value]]";
	durationGraph.lineThickness = 1;
	durationGraph.legendValueText = "[[value]]";
	durationGraph.bullet = "square";
	chart4.addGraph(durationGraph);

	// distance graph
	var distanceGraph = new AmCharts.AmGraph();
	distanceGraph.valueField = "distance";
	distanceGraph.title = "distance";
	distanceGraph.type = "column";
	distanceGraph.fillAlphas = 0.1;
	distanceGraph.valueAxis = distanceAxis; // indicate which axis should be used
	distanceGraph.balloonText = "[[value]] miles";
	distanceGraph.legendValueText = "[[value]] mi";
	distanceGraph.lineColor = "#000000";
	distanceGraph.lineAlpha = 0;
	chart4.addGraph(distanceGraph);

	// CURSOR
	var chartCursor = new AmCharts.ChartCursor();
	chartCursor.zoomable = false;
	chartCursor.categoryBalloonDateFormat = "DD";
	chartCursor.cursorAlpha = 0;
	chart4.addChartCursor(chartCursor);

	// LEGEND
	var legend = new AmCharts.AmLegend();
	legend.bulletType = "round";
	legend.equalWidths = false;
	legend.valueWidth = 120;
	legend.color = "#000000";
	chart4.addLegend(legend);

	// WRITE
	chart4.write("chartdiv4")
});

var chart5;
var graph;

// months in JS are zero-based, 0 means January

AmCharts.ready(function () {
	// SERIAL CHART
	chart5 = new AmCharts.AmSerialChart();
	chart5.pathToImages = "./";
	chart5.dataProvider = chartData5;
	chart5.autoMargins = false;
	chart5.marginLeft = 0;
	chart5.marginTop = 0;
	chart5.marginBottom = 30;
	chart5.marginRight = 0;
	chart5.categoryField = "year";
	chart5.zoomOutButton = {
		backgroundColor: "#000000",
		backgroundAlpha: 0.15
	};

	// listen for "dataUpdated" event (fired when chart is inited) and call zoomChart method when it happens
	chart5.addListener("dataUpdated", zoomChart);

	// AXES
	// category
	var categoryAxis = chart5.categoryAxis;
	categoryAxis.parseDates = true; // as our data is date-based, we set parseDates to true
	categoryAxis.minPeriod = "YYYY"; // our data is yearly, so we set minPeriod to YYYY
	categoryAxis.gridAlpha = 0;

	// value
	var valueAxis = new AmCharts.ValueAxis();
	valueAxis.axisAlpha = 0;
	valueAxis.inside = true;
	valueAxis.gridAlpha = 0.1;
	chart5.addValueAxis(valueAxis);

	// GRAPH
	graph = new AmCharts.AmGraph();
	graph.type = "smoothedLine"; // this line makes the graph smoothed line.
	graph.lineColor = "#d1655d";
	graph.negativeLineColor = "#637bb6"; // this line makes the graph to change color when it drops below 0
	graph.bullet = "round";
	graph.bulletSize = 5;
	graph.lineThickness = 2;
	graph.valueField = "value";
	chart5.addGraph(graph);

	// CURSOR
	var chartCursor = new AmCharts.ChartCursor();
	chartCursor.cursorAlpha = 0;
	chartCursor.cursorPosition = "mouse";
	chartCursor.categoryBalloonDateFormat = "YYYY";
	chart5.addChartCursor(chartCursor);

	// SCROLLBAR
	var chartScrollbar = new AmCharts.ChartScrollbar();
	chartScrollbar.graph = graph;
	chartScrollbar.backgroundColor = "#DDDDDD";
	chartScrollbar.scrollbarHeight = 30;
	chartScrollbar.selectedBackgroundColor = "#FFFFFF";
	chart5.addChartScrollbar(chartScrollbar);

	// WRITE
	chart5.write("chartdiv5");
});

// this method is called when chart is first inited as we listen for "dataUpdated" event
function zoomChart() {
	// different zoom methods can be used - zoomToIndexes, zoomToDates, zoomToCategoryValues
	chart5.zoomToDates(new Date(1972, 0), new Date(1984, 0));
}


AmCharts.ready(function () {
	bar3d_graph(chartData1, 'chartdiv1');
	stocks_graph(sg_data, sg_events, 'chartdiv6');
	donut_graph(chartData3, 'chartdiv3', 'Visitors countries');
});


</script>
<div id="chartdiv6" style="display: none; background: #fff; width:860px; height:500px;"></div>
<div id="chartdiv1" style="display: none; background: #fff; width: 860px; height:400px;"></div>
<div id="chartdiv3" style="display: none; background: #fff; width:860px; height:400px;"></div>
<div id="chartdiv2" style="display: none; background: #000; width:860px; height:400px;"></div>
<div id="chartdiv4" style="display: none; background: #fff; width:860px; height:400px;"></div>
<div id="chartdiv5" style="display: none; background: #fff; width:860px; height:400px;"></div>
</body>
</html>

