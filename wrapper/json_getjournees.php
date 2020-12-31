<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

//if (!$sess_context->isValideChampionnat()) exit(0);

$journees = array();
$req = "SELECT * FROM jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." ORDER BY date DESC;";
//$req = "SELECT * FROM jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." AND date BETWEEN '".$year."-".$month."-01' AND  '".$year."-".$month."-31';";
//$req = "SELECT * FROM jb_journees WHERE id_champ IN (SELECT id FROM jb_saisons WHERE id_champ=".$sess_context->championnat['id'].") AND date BETWEEN '".$year."-".$month."-01' AND  '".$year."-".$month."-31';";
$res = dbc::execSql($req);

$str = "";
while($row = mysqli_fetch_array($res))
{
	$str .= ($str == "" ? "" : ",")."{\"id\":\"".$row['id']."\", \"nom\":\"".htmlentities($row['nom'])."\", \"day\":\"".intval(substr($row['date'], 8))."\", \"date\":\"".ToolBox::mysqldate2date($row['date'])."\" }";
}

?>

{
  "journees": [ <?= $str ?> ]
}