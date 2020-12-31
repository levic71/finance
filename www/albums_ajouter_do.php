<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

// Gestion de la date
if ($date_creation != "")
{
	$item = explode('/', $date_creation);
	$date_creation = $item[2]."-".$item[1]."-".$item[0];
}

// gestion de la piece jointe (photo)
$source   = ToolBox::get_global("photo");
$filename = ToolBox::purgeCaracteresWith("_", "../uploads/ALBUM_".$sess_context->getRealChampionnatId()."_".$id_theme."_".ToolBox::get_global("photo_name"));
$thumb = ToolBox::purgeCaracteresWith("_", "../thumbs/ALBUM_".$sess_context->getRealChampionnatId()."_".$id_theme."_".ToolBox::get_global("photo_name"));

if ($source != "" && file_exists($source))
{
	$filename = ImageBox::imageSquareResize($source, $filename, 80, 400, 400);
	$thumb = ImageBox::thumbImageSquareResize($source, $thumb, 100, 45, 45);
}
//	copy($source, $filename);
else
	$filename = "";

// Insertion du nouveau joueurs
$insert = "INSERT INTO jb_albums (id_champ, id_saison, id_theme, commentaire, date, photo) VALUES (".$sess_context->getRealChampionnatId().", ".$sess_context->getChampionnatId().", ".$id_theme.", '".$commentaire."', '".$date_creation."', '".$filename."');";
$res = dbc::execSQL($insert);

// Incrémentation du compteur de photos dans le theme parent
$sats = new SQLAlbumsThemesServices($sess_context->getRealChampionnatId(), $id_theme);
$theme = $sats->getAlbumTheme();

// On force la recréation du XML
$xml_file = $sats->getXMLFilename();
JKCache::delCache($xml_file, "_FLUX_XML_ALBUM_");

$update = "UPDATE jb_albums_themes SET last_modif=SYSDATE(), nb_photos=".($theme['nb_photos']+1)." WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$id_theme;
$res = dbc::execSql($update);

mysql_close($db);

ToolBox::do_redirect("albums.php?id_theme=".$id_theme);

?>
