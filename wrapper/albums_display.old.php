<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/ManagerFXList.php";
include "../www/StatsBuilder.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

if (!isset($_REQUEST['id_theme']) || $_REQUEST['id_theme'] == 0)
{
	$tmp = new SQLAlbumsThemesServices($sess_context->getRealChampionnatId(), -1);
	$id_theme = $tmp->getFirstTheme();
}
else $id_theme = $_REQUEST['id_theme'];

$sas = new SQLAlbumsThemesServices($sess_context->getRealChampionnatId(), $id_theme);
$themes = $sas->getAllThemes();
$photos = $sas->getPhotos();

$i = 0;
foreach($themes as $item)
	$i++;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript" src="js/flashobject.js"></script>
</head>
<body>

<div>

<?

if ($i > 0) {
	$xml_file = $sas->getXMLFilename();
	$xml_cache = JKCache::getCache($xml_file, -1, "_FLUX_XML_ALBUM_");

	if (count($photos) == 0) echo "Aucune photo";
	else {
?>
	<div style="text-align: center;" id="swfviewer">

	<script type="text/javascript">
		// <![CDATA[
		var airness_fo = new FlashObject("../swf/viewer.swf", "swfviewer", "700", "500", "0", "#DDDDDD");
		airness_fo.addParam("quality", "best");
		airness_fo.addParam("wmode", "transparent");
		airness_fo.addParam("salign", "t");
		airness_fo.addParam("scale", "noscale");
		airness_fo.addVariable("xmlDataPath", "<?= $xml_file ?>");
		airness_fo.write("swfviewer");
		// ]]>
	</script>

	</div>
<?
	}
}
?>

</div>

</body>
</html>
