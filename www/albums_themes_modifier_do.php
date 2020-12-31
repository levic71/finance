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
$update = "UPDATE jb_albums_themes SET last_modif=SYSDATE(), id_champ=".$sess_context->getRealChampionnatId().", id_saison=".$sess_context->getChampionnatId().", nom='".$nom."', date='".$date_creation."' WHERE id=".$id_theme.";";
$res = dbc::execSQL($update);

mysql_close($db);

ToolBox::do_redirect("albums_themes.php");

?>
