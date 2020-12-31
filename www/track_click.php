<?

include "../include/toolbox.php";
include "../include/inc_db.php";

$db = dbc::connect();

if (isset($champ, $admin))
{
	Toolbox::trackUser($champ, $admin);
	echo "Tracked";
}
else
	echo "Not tracked ...";

mysql_close($db);
		
?>
