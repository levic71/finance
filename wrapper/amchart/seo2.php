<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>amCharts</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="style.css" rel="stylesheet" type="text/css">
<link href="jk.css" rel="stylesheet" type="text/css">
<link href="components.css" rel="stylesheet" type="text/css">
<script src="jxs_compressed.js" type="text/javascript"></script>
<script src="jk.js" type="text/javascript"></script>
<script src="amstock.js" type="text/javascript"></script>
<script src="amfallback.js" type="text/javascript"></script>
<script src="stats_fcts.js" type="text/javascript"></script>
<script src="data.js?ts=<?= date("YmdH") ?>00" type="text/javascript"></script>
<script src="stats_globales.js?ts=<?= date("YmdH") ?>00" type="text/javascript"></script></head>
<body>

<script type="text/javascript">
window.onload = function () {
	go2({ id: "chartdiv6", url: "getprofile2.php"});
}
</script>

<div id="chartdiv6" style="display: block; background: #fff; width:860px; height:500px; border: 1px solid #ddd;"></div>

</body>
</html>