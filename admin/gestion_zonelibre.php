<?

include "../include/sess_context.php";

session_start();

$jorkyball_redirect_exception = 1;

include "../www/common.php";
include "../include/inc_db.php";
include "../www/ManagerFXList.php";

$db = dbc::connect();

$filename = "../cache/home_zone_libre.txt";
if (isset($valider))
{
    if (file_exists($filename))
		unlink($filename);
	$fichier = fopen($filename, "w");
	if (flock($fichier, LOCK_EX))
	{
		$flux = str_replace("\\\"", "\"", $ta);
		fputs($fichier, $flux);
		flock($fichier, LOCK_UN);
	}
	fclose($fichier);
}

$flux = JKCache::getCache($filename, -1, "_FLUX_ZONE_LIBRE_");

?>
<html>
</head>
<script type="text/javascript" src="../include/fckeditor/fckeditor.js"></script>
<script type="text/javascript">
window.onload = function()
{
	var oFCKeditor = new FCKeditor( 'ta' );
	oFCKeditor.BasePath = "../include/fckeditor/";
	oFCKeditor.Height = 400;
	oFCKeditor.width = 500;
	oFCKeditor.ReplaceTextarea();
}
</script>
</head>
<body>

<form action="gestion_zonelibre.php" method="post">


<textarea id=ta name=ta>
<?
	foreach($flux as $line)
		echo $line;
?>
</textarea>
<br />
<input type="submit" name="valider" value="Valider">

</form>
</body>
</html>
