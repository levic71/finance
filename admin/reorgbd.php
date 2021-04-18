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

function string2DNS($str) {

	$name = trim($str, "-");
	$name = trim($name, " ");
	$name = strtr($name, 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
	$name = strtr($name, '+.,|!"ï¿½$%&/()=?^*ç°§;:_>][@);', '                             ');
	$name = str_replace('\\', '', $name);
	$name = str_replace('\'', '', $name);
	$name = str_replace('-', ' ', $name);  /* ' */
	while(substr_count($name,"  ") != 0) $name = str_replace("  ", " ", $name);
	$name = str_replace(' ', '-', strtolower($name));

	return $name;
}

// Avant de lancer ce script se connecter sur wrappeer/jk.php


// Nettoyage images absentes
$i = 0;
$req = "SELECT * FROM jb_users";
$res = dbc::execSQL($req);
while($row = mysqli_fetch_array($res))
{
	if ($row['photo'] != "" && !file_exists($row['photo']))
	{
		$i++;
		$sql = "UPDATE jb_users SET photo='' WHERE id=".$row['id'];
		$ttt = dbc::execSQL($sql);
	}
}
echo "Joueurs: ".$i." images reset<br />";
$i = 0;
$req = "SELECT * FROM jb_joueurs";
$res = dbc::execSQL($req);
while($row = mysqli_fetch_array($res))
{
	if ($row['photo'] != "" && !file_exists($row['photo']))
	{
		$i++;
		$sql = "UPDATE jb_joueurs SET photo='' WHERE id=".$row['id'];
		$ttt = dbc::execSQL($sql);
	}
}
echo "Joueurs: ".$i." images reset<br />";
$i = 0;
$req = "SELECT * FROM jb_equipes";
$res = dbc::execSQL($req);
while($row = mysqli_fetch_array($res))
{
	if ($row['photo'] != "" && !file_exists($row['photo']))
	{
		$i++;
		$sql = "UPDATE jb_equipes SET photo='' WHERE id=".$row['id'];
		$ttt = dbc::execSQL($sql);
	}
}
echo "Equipes: ".$i." images reset<br />";
$i = 0;
$req = "SELECT * FROM jb_forum";
$res = dbc::execSQL($req);
while($row = mysqli_fetch_array($res))
{
	if ($row['image'] != "" && !file_exists($row['image']))
	{
		$i++;
		$sql = "UPDATE jb_forum SET image='' WHERE id=".$row['id'];
		$ttt = dbc::execSQL($sql);
	}
}
echo "Forum: ".$i." images reset<br />";
$i = 0;
$req = "SELECT * FROM jb_albums";
$res = dbc::execSQL($req);
while($row = mysqli_fetch_array($res))
{
	if ($row['photo'] != "" && !file_exists($row['photo']))
	{
		$i++;
		$sql = "UPDATE jb_albums SET photo='' WHERE id=".$row['id'];
		$ttt = dbc::execSQL($sql);
	}
}
echo "Albums: ".$i." images reset <br />";


exit(0);

$i = 0;
$req = "SELECT * FROM jb_championnat";
$res = dbc::execSQL($req);
while($row = mysqli_fetch_array($res))
{
	$sql = "UPDATE jb_championnat SET dns='".substr(strtolower(string2DNS($row['nom'])), 0, 32)."' WHERE id=".$row['id'];
	echo $sql;
	echo "<br />\n";
	$ttt = dbc::execSQL($sql);
}


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
