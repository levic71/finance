<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
if (strlen($search) < 4) { echo "<p class=\"red\">Merci de saisir au moins 4 caractères</p>"; exit(0); }

$nb_rows = 0;
$sql = "SELECT count(*) total FROM jb_users u WHERE u.nom LIKE '%".$search."%' OR u.pseudo LIKE '%".$search."%' OR u.ville LIKE '%".$search."%'";
$res = dbc::execSql($sql);
if ($row = mysqli_fetch_array($res)) $nb_rows = $row['total'];

?>

<?

if ($nb_rows == 0) { echo "<p class=\"red\">Aucun joueur inscrit trouvé ...</p>"; exit(0); }

if ($nb_rows > 15) { echo "<p class=\"red\">trop de joueurs inscrits trouvés, affiner votre recherche ...</p>"; exit(0); }

echo "<ul style=\"list-style: none; background: #efefef;\">";
$sql = "SELECT * FROM jb_users u WHERE u.nom LIKE '%".$search."%' OR u.pseudo LIKE '%".$search."%' OR u.ville LIKE '%".$search."%'";
$res = dbc::execSql($sql);
while($row = mysqli_fetch_array($res)) {
	echo "<li><input type=\"radio\" id=\"selected_player\" name=\"selected_player\" value=\"".$row['id']."\" />".$row['nom']." ".$row['prenom']." - ".$row['pseudo']." - ".$row['ville']."</li>";
}
echo "</ul>";

?>

