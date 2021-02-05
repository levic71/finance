<?

require_once "../include/sess_context.php";

session_start();

$jb_langue = "fr";

include "../wrapper/common.php";
include "../include/inc_db.php";

$db = dbc::connect();

?>

<HTML>
<BODY>

<?

// Avant de lancer ce script se connecter sur wrappeer/jk.php

exit(0);

$req = "ALTER TABLE `jb_users` ADD `crypt` INT NOT NULL DEFAULT '0' AFTER `removed`;";
$res = dbc::execSQL($req);

$req = "ALTER TABLE `jb_users` CHANGE `pwd` `pwd` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';";
$res = dbc::execSQL($req);

$i = 0;
$req = "SELECT * FROM jb_users WHERE crypt=0 LIMIT 60";
$res = dbc::execSQL($req);
while($row = mysqli_fetch_array($res))
{
	$h = password_hash($row['pwd'], PASSWORD_DEFAULT);
	$update = "UPDATE jb_users SET crypt=1, pwd='".$h."' WHERE id=".$row['id'];
	$res2   = dbc::execSQL($update);	
	echo "id=".$row['id'].", email=".$row['email'].", pwd=".$h."<br />";
	$i++;
}

if ($i == 0) {
	$req = "ALTER TABLE `jb_users` DROP `crypt`;";
	$res = dbc::execSQL($req);	
}

?>

</BODY>
</HTML>
