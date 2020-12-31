<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

?>

<SCRIPT SRC="../js/flashobject.js" type="text/javascript"></SCRIPT>

<CENTER>

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

		<TR><TD><DIV style="text-align: center;" id="swfchart">

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
	
		</DIV></TD></TR>

</TABLE>

</CENTER>

<? $menu->end(); ?>
