<?

include "../include/sess_context.php";

session_start();

$jorkyball_redirect_exception = 1;

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

if (isset($sess_context) && $sess_context->isChampionnatValide())
{
	$menu = new menu("full_access");
	$menu->debut($sess_context->getChampionnatNom());
}
else
{
	$menu = new menu("forum_access");
	$menu->debut("");
}

$id_zone = isset($id_zone) ? $id_zone : 0;

?>

<form action="sms_submit.php" method="post">

<div id="pageint">

<h2> Envoyer un SMS </h2>

<ul>
<?
	$request = "SELECT * FROM jb_sms WHERE id_zone=".$id_zone." ORDER BY id DESC LIMIT 0,40;";
	$res = dbc::execSQL($request);
	while($row = mysql_fetch_array($res))
	{
		echo "<li><span style=\"color:#777;\">".$row['date']." ".$row['heure']."</span> ".$row['pseudo']."::".$row['message']."</li>";
	}
?>
</ul>

&#187; <a href="sms.php">Envoyer un sms</a>
<br />
<br />

</div>

</form>

<? $menu->end(); ?>
