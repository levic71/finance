<?

if (isset($_REQUEST['iframe']) &&  $_REQUEST['iframe'] == 1) {
	echo "<iframe src=\"getprofile.php\" style=\"width: 900px; height: 1400px;\" frameborder=\"0\" scrolling=\"no\" />";
	exit(0);
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>amCharts</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?= sess_context::charset ?>">
<link href="style.css" rel="stylesheet" type="text/css">
<script src="amstock.js" type="text/javascript"></script>
<script src="amfallback.js" type="text/javascript"></script>
<script src="stats_fcts.js" type="text/javascript"></script>
<script src="data.js?ts=<?= date("YmdH") ?>00" type="text/javascript"></script>
<script src="stats_globales.js?ts=<?= date("YmdH") ?>00" type="text/javascript"></script>
</head>
<body>
<div id="chartdiv6" style="display: block; background: #fff; width:860px; height:500px; border: 1px solid #ddd;"></div>
<div id="chartdiv1" style="display: none; background: #fff; width: 860px; height:400px; border: 1px solid #ddd;"></div>
<div id="chartdiv3" style="display: none; background: #fff; width:860px; height:400px; border: 1px solid #ddd;"></div>
<script>
AmCharts.ready(function () {
	stocks_graph(sg_data, sg_events, 'chartdiv6');
	bar3d_graph(chartData1, 'chartdiv1');
	donut_graph(chartData3, 'chartdiv3', 'Visitors countries');
});
</script>
</body>
</html>