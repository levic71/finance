<?

include "../include/sess_context.php";
include "../include/constantes.php";
include "../include/toolbox.php";
include "../include/inc_db.php";
include "SQLServices.php";

$db = dbc::connect();

$scs = new SQLChampionnatsServices();
$row = $scs->getAllChampionnats();

header("Content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"".sess_context::charset."\"?>\n";
echo "<JORKYBALL>\n";
foreach($row as $ch)
{
	echo "	<CHAMPIONNAT ID=\"".$ch['id']."\" NOM=\"".$ch['nom']."\"></CHAMPIONNAT>\n";
}
echo "</JORKYBALL>\n";

mysql_close($db);

?>