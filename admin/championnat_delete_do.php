<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";

$db = dbc::connect();

$req = "SELECT count(*) total FROM jb_forum WHERE id_champ=".$championnat.";";
$res = dbc::execSQL($req);
while($row = mysql_fetch_array($res)) echo "<br />nb messages = ".$row['total'];

$req = "SELECT count(*) total FROM jb_matchs m, jb_saisons s WHERE s.id_champ=".$championnat." AND m.id_champ = s.id;";
$res = dbc::execSQL($req);
while($row = mysql_fetch_array($res)) echo "<br />nb matchs = ".$row['total'];

$req = "SELECT count(*) total FROM jb_journees j, jb_saisons s WHERE s.id_champ=".$championnat." AND j.id_champ = s.id;";
$res = dbc::execSQL($req);
while($row = mysql_fetch_array($res)) echo "<br />nb journees = ".$row['total'];

$req = "SELECT count(*) total FROM jb_equipes WHERE id_champ=".$championnat.";";
$res = dbc::execSQL($req);
while($row = mysql_fetch_array($res)) echo "<br />nb equipes = ".$row['total'];

$req = "SELECT count(*) total FROM jb_joueurs WHERE id_champ=".$championnat.";";
$res = dbc::execSQL($req);
while($row = mysql_fetch_array($res)) echo "<br />nb joueurs = ".$row['total'];

$req = "SELECT count(*) total FROM jb_saisons WHERE id_champ=".$championnat.";";
$res = dbc::execSQL($req);
while($row = mysql_fetch_array($res)) echo "<br />nb saisons = ".$row['total'];

$req = "SELECT count(*) total FROM jb_championnat WHERE id=".$championnat.";";
$res = dbc::execSQL($req);
while($row = mysql_fetch_array($res)) echo "<br />nb championnats = ".$row['total'];


if (isset($valid) && $valid == "Y") {

$delete = "DELETE FROM jb_forum WHERE id_champ=".$championnat.";";
$res = dbc::execSQL($delete);

$delete = "DELETE FROM jb_matchs WHERE id_champ IN (SELECT id FROM jb_saisons WHERE id_champ=".$championnat.");";
$res = dbc::execSQL($delete);

$delete = "DELETE FROM jb_journees WHERE id_champ IN (SELECT id FROM jb_saisons WHERE id_champ=".$championnat.");";
$res = dbc::execSQL($delete);

$delete = "DELETE FROM jb_equipes WHERE id_champ=".$championnat.";";
$res = dbc::execSQL($delete);

$delete = "DELETE FROM jb_joueurs WHERE id_champ=".$championnat.";";
$res = dbc::execSQL($delete);

$delete = "DELETE FROM jb_saisons WHERE id_champ=".$championnat.";";
$res = dbc::execSQL($delete);

$delete = "DELETE FROM jb_championnat WHERE id=".$championnat.";";
$res = dbc::execSQL($delete);

echo "<br />Done";
}

echo "<br />=>valid=Y";

mysql_close ($db);

?>
