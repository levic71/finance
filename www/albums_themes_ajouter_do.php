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

// Insertion du nouveau joueurs
$insert = "INSERT INTO jb_albums_themes (id_champ, id_saison, nom, date, nb_photos, last_modif) VALUES (".$sess_context->getRealChampionnatId().", ".$sess_context->getChampionnatId().", '".$nom."', '".$date_creation."', 0, SYSDATE());";
$res = dbc::execSQL($insert);

mysql_close($db);

ToolBox::do_redirect("albums_themes.php");

?>
