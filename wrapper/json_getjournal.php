<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/toolbox.php";
include "../include/inc_db.php";
include "wrapper_fcts.php";

$db = dbc::connect();

$id_match = Wrapper::getRequest('id_match', 0);

$journees = array();
$req = "SELECT journal FROM jb_matchs WHERE id=".$id_match;
$res = dbc::execSql($req);
$journal = "";
if ($row = mysqli_fetch_array($res))

	$tmp = explode("|", $row['journal']);
	foreach($tmp as $item)
		if ($item != "") $journal .= ($journal == "" ? "" : ",")."{\"item\":\"".$item."\" }";

?>
{
  "journal": [ <?= $journal ?> ]
}