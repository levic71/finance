<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$iframe = Wrapper::getRequest('iframe', "yes");

if ($iframe == "yes") {
?>
<h2 class="grid dashboard">Statistiques de fréquentation</h2>

<ul class="sidebar">
	<li><a href="#" onclick="mm({action: 'dashboard'});" id="sb_back" class="swap ToolText" onmouseover="showtip('sb_back');"><span>Retour</span></a></li>
</ul>

<iframe src="admin_stats_freq.php?iframe=no" height="430" width="700" frameborder="0" border="0" framespacing="0" scrolling="no"></iframe>

<?
	exit(0);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script src="../js/flashobject.js" type="text/javascript"></script>
</head>
<body>

<table border="0" cellpadding="0" cellspacing="0" width="700" summary="tab central">

		<tr><td><div style="text-align: center;" id="swfchart">

		<script type="text/javascript">
			// <![CDATA[
			var chart = new FlashObject("../swf/charts.swf", "swfchart", "600", "400", "0", "#666666");
			chart.addParam("quality", "best");
			chart.addParam("salign", "t");
			chart.addParam("scale", "noscale");
			chart.addVariable("library_path", "../swf/charts_library");
			chart.addVariable("xml_source", "../admin/xml_stats_freq.php?ref_champ=<?= $sess_context->getRealChampionnatId() ?>");
			chart.write("swfchart");
			// ]]>
		</script>

		</div></td></tr>

</table>

</body>
</html>