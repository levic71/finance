<?

header('Content-Type: text/html; charset=ISO-8859-15');

if (isset($refSaisonForm) && $refSaisonForm != "")
{
	include "../include/inc_db.php";
	include "../include/toolbox.php";
	$db = dbc::connect();

	echo "<table border=\"0\"><tr><td>Forcer la journées : </td><td><select name=\"idJourneeForm\" id=\"idJourneeForm\"><option value=\"0\"> Selectionner une journee";

	$request = "SELECT * FROM jb_journees WHERE id_champ=".$refSaisonForm." ORDER BY date ASC";
	$res = dbc::execSQL($request);
	while ($row = mysql_fetch_array($res))
	{
		echo "<option value=\"".$row['id']."\">".ToolBox::mysqldate2date($row['date'])." : ".ToolBox::conv_lib_journee($row['nom']);;
	}

	echo "</select></td></tr></table>";
}
else
	echo "Pb parametres ...";

?>
