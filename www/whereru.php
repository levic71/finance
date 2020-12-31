<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<title>Where R ?</title>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAxSR6tl3WAafWf0ejSwIWqBQC-WnudriH7EPj_GGA9JFl0uvjGBT3OxrkgPvHGJPY1QSvqIL-jWvWAA" type="text/javascript"></script>
<link rel="stylesheet" href="../css/stylesv300.css" type="text/css" />
<link rel="stylesheet" href="../css/templatev300.css" type="text/css" />
<script src="../js/gicons.js" type="text/javascript"></script>
<script src="../js/gfcts.js" type="text/javascript"></script>
</head>
<body onload="loadmap()" onunload="GUnload()">
<center>
<br />
<h1>Where R U ?</h1>
<div style="width: 700px;">
<div class="titre" style="float: left; margin: 0px 20px 0px 0px;"><a class="cmd" href="javascript:zoom('paris');">Zoom Paris</a></div>
<div class="titre" style="float: left; margin: 0px 20px 0px 0px;"><a class="cmd" href="javascript:zoom('france');">Zoom France</a></div>
<div class="titre" style="float: left; margin: 0px 20px 0px 0px;"><a class="cmd" href="javascript:zoom('portugal');">Zoom Portugal</a></div>
<div class="titre" style="float: left; margin: 0px 20px 0px 0px;"><a class="cmd" href="javascript:zoom('europe');">Zoom Europe</a></div>
<div class="titre" style="float: left; margin: 0px 20px 0px 0px;"><a class="cmd" href="javascript:zoom('monde');">Zoom Monde</a></div>
</div>
<br />
<div id="map" style="width: 700px; height: 500px; border: 4px solid #AAAAAA;"></div>
<div style="width: 700px;">
<div class="titre" style="float: left; margin: 0px 20px 0px 0px;"><a class="cmd" href="#">Cliquer sur les ballons pour zoomer</a></div>
<div class="titre" style="float: left; margin: 0px 20px 0px 0px;"><a class="cmd" href="#">Double cliquer sur les ballons pour revenir au zoom initial</a></div>
</div>
<br />
<div style="width: 700px;">
	Acces direct :
	<select id="speedaccess" onchange="javascript:zoompoint(document.getElementById('speedaccess'));">
		<option />
	</select>
</div>
</center>
</body>
</html>
