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

// Upload de l'image si c'est nécessaire
$upload = 0;
$source   = ToolBox::get_global("photo");
$filename = ToolBox::purgeCaracteresWith("_", "../uploads/ALBUM_".$sess_context->getRealChampionnatId()."_".$id_theme."_".ToolBox::get_global("photo_name"));
$thumb = ToolBox::purgeCaracteresWith("_", "../thumbs/ALBUM_".$sess_context->getRealChampionnatId()."_".$id_theme."_".ToolBox::get_global("photo_name"));
if ($source != "" && file_exists($source))
{
	// Récupération des infos de l'ancienne image pour la supprimer
	$sas = new SQLAlbumsServices($sess_context->getRealChampionnatId());
	$photo = $sas->getPhoto($id_photo);
	if ($photo['photo'] != "" && file_exists($photo['photo'])) unlink($photo['photo']);
	if ($photo['photo'] != "" && file_exists(str_replace('uploads', 'thumbs', $photo['photo']))) unlink(str_replace('uploads', 'thumbs', $photo['photo']));

	$filename = ImageBox::imageSquareResize($source, $filename, 80, 400, 400);
	$thumb = ImageBox::thumbImageSquareResize($source, $thumb, 100, 45, 45);
	$upload = 1;
}

// Mise de la photo
if ($upload == 1)
	$update = "UPDATE jb_albums SET photo='".$filename."', id_champ=".$sess_context->getRealChampionnatId().", id_saison=".$sess_context->getChampionnatId().", commentaire='".$commentaire."', date='".$date_creation."' WHERE id=".$id_photo.";";
else
	$update = "UPDATE jb_albums SET id_champ=".$sess_context->getRealChampionnatId().", id_saison=".$sess_context->getChampionnatId().", commentaire='".$commentaire."', date='".$date_creation."' WHERE id=".$id_photo.";";
$res = dbc::execSQL($update);

// On force la recréation du XML
$sats = new SQLAlbumsThemesServices($sess_context->getRealChampionnatId(), $id_theme);
$xml_file = $sats->getXMLFilename();
JKCache::delCache($xml_file, "_FLUX_XML_ALBUM_");

$update = "UPDATE jb_albums_themes SET last_modif=SYSDATE() WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$id_theme;
$res = dbc::execSql($update);

mysql_close($db);

ToolBox::do_redirect("albums.php?id_theme=".$id_theme);

?>
