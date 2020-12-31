donut_graph = function (data, target, title) {
	var chart;

	// PIE CHART
	chart = new AmCharts.AmPieChart();

	// title of the chart
	chart.addTitle(title, 16);

	chart.dataProvider = data;
	chart.titleField = "title";
	chart.valueField = "value";
	chart.sequencedAnimation = true;
	chart.startEffect = "elastic";
	chart.innerRadius = "30%";
	chart.startDuration = 2;
	chart.labelRadius = 15;

	// the following two lines makes the chart 3D
	chart.depth3D = 10;
	chart.angle = 15;

	// WRITE
	chart.write(target);
}

bar3d_graph = function (data, target) {
	var chart;

	// SERIAL CHART
	chart = new AmCharts.AmSerialChart();
	chart.dataProvider = data;
	chart.categoryField = "championnat";
	chart.depth3D = 20;
	chart.angle = 30;

	// AXES
	// category
	var categoryAxis = chart.categoryAxis;
	categoryAxis.labelRotation = 45;
	categoryAxis.dashLength = 5;
	categoryAxis.gridPosition = "start";

	// value
	var valueAxis = new AmCharts.ValueAxis();
	valueAxis.title = "Visites";
	valueAxis.dashLength = 5;
	chart.addValueAxis(valueAxis);

	// GRAPH
	var graph = new AmCharts.AmGraph();
	graph.valueField = "visits";
	graph.colorField = "color";
	graph.balloonText = "[[category]]: [[value]]";
	graph.type = "column";
	graph.lineAlpha = 0;
	graph.fillAlphas = 1;
	chart.addGraph(graph);

	chart.write(target);
}


stocks_graph = function (data, events, target) {
	var chart;
	chart = new AmCharts.AmStockChart();
	chart.pathToImages = "./";
	chart.panEventsEnabled = true;

	// DATASETS //////////////////////////////////////////
	var dataSet = new AmCharts.DataSet();
	dataSet.color = "#b0de09";
	dataSet.fieldMappings = [ { fromField: "visites", toField: "visites" }, { fromField: "uniques", toField: "uniques" }, { fromField: "pages", toField: "pages" } ];
	dataSet.dataProvider = data;
	dataSet.categoryField = "date";

	// set data sets to the chart
	chart.dataSets = [dataSet];

	// PANELS ///////////////////////////////////////////
	// first stock panel
	var stockPanel1 = new AmCharts.StockPanel();
	stockPanel1.showCategoryAxis = false;
	stockPanel1.title = "Visites";
	stockPanel1.percentHeight = 70;
	stockPanel1.panEventsEnabled = true;

	// graph of first stock panel
	var graph1 = new AmCharts.StockGraph();
	graph1.valueField = "visites";
	stockPanel1.addStockGraph(graph1);

	// graph of second stock panel
	var graph2 = new AmCharts.StockGraph();
	graph2.valueField = "uniques";
	graph2.useDataSetColors=false;
	graph2.lineColor="#FCD202";
	stockPanel1.addStockGraph(graph2);

	// create stock legend
	var stockLegend1 = new AmCharts.StockLegend();
	stockLegend1.valueTextRegular = " ";
	stockLegend1.markerType = "none";
	stockPanel1.stockLegend = stockLegend1;


	// second stock panel
	var stockPanel2 = new AmCharts.StockPanel();
	stockPanel2.title = "Pages";
	stockPanel2.percentHeight = 30;
	var graph2 = new AmCharts.StockGraph();
	graph2.valueField = "pages";
	graph2.type = "column";
	graph2.fillAlphas = 1;
	stockPanel2.addStockGraph(graph2);

	// create stock legend
	var stockLegend2 = new AmCharts.StockLegend();
	stockLegend2.valueTextRegular = " ";
	stockLegend2.markerType = "none";
	stockPanel2.stockLegend = stockLegend2;
	stockPanel2.panEventsEnabled = true;

	// set panels to the chart
	chart.panels = [stockPanel1, stockPanel2];


	// OTHER SETTINGS ////////////////////////////////////
	var scrollbarSettings = new AmCharts.ChartScrollbarSettings();
	scrollbarSettings.graph = graph1;
	scrollbarSettings.updateOnReleaseOnly = true;
	chart.chartScrollbarSettings = scrollbarSettings;

	var cursorSettings = new AmCharts.ChartCursorSettings();
	cursorSettings.valueBalloonsEnabled = true;
	chart.chartCursorSettings = cursorSettings;


	// PERIOD SELECTOR ///////////////////////////////////
	var periodSelector = new AmCharts.PeriodSelector();
	periodSelector.periods = [
		{ period: "DD", count: 10, label: "10 days" },
		{ period: "MM", count: 1, label: "1 month" },
		{ period: "YYYY", count: 1, label: "1 year" },
		{ period: "YTD", label: "YTD" },
		{ period: "MAX", label: "MAX" }
	];
	chart.periodSelector = periodSelector;

	var panelsSettings = new AmCharts.PanelsSettings();
	panelsSettings.usePrefixes = true;
	chart.panelsSettings = panelsSettings;
	
	for(i=0; i < events.length; i++) { events[i].graph=graph1; }
	dataSet.stockEvents = events;

	chart.write(target);
}