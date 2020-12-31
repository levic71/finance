<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";

$db = dbc::connect();

// Gestion de la piece jointe (image)
$source    = ToolBox::get_global("image");
$xsource   = ToolBox::get_global("ximage");
$filename  = ToolBox::purgeCaracteresWith("_", "../uploads/FORUM_0_".ToolBox::get_global("image_name"));
$xfilename = ToolBox::purgeCaracteresWith("_", "../uploads/xFORUM_0_".ToolBox::get_global("image_name"));

if ($source != "" && file_exists($source))
{
	$filename = ImageBox::imageWidthResize($source, $filename, 80, 400);
	if ($xsource != "" && file_exists($xsource))
		$xfilename = ImageBox::imageWidthResize($xsource, $xfilename, 80, 120);
	else
		$xfilename = ImageBox::imageWidthResize($source, $xfilename, 80, 120);
}
else
	$filename = "";

// Insertion
$insert = "INSERT INTO jb_forum (date, nom, title, message, image, last_reponse, last_user, smiley) VALUES ('".ToolBox::date2mysqldate($date)."', '".$nom."', '".$title."', '".$ta."', '".$filename."', '".ToolBox::date2mysqldate($date)."', '".$nom."', '../forum/smileys/smile.gif');";
$res = dbc::execSQL($insert);

mysql_close($db);

ToolBox::do_redirect("gestion_photos.php");

?>
