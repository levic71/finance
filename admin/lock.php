<?

include "../include/inc_db.php";
include "../include/toolbox.php";
include "../include/lock.php";

if (isset($validation) && $validation == 1)
{

	$db = dbc::connect();

	$select = "SELECT * FROM jb_championnat WHERE id=8 AND login='".$login."' AND pwd='".$pwd."';";
	$res = dbc::execSQL($select);
	$row = mysql_fetch_array($res);
	if ($row)
	{
        $fichier = fopen("../include/lock.php", "w");
		fputs($fichier, "<? \$lock=".($lock == 0 ? "1" : "0")."; ?>");
		fclose($fichier);

		ToolBox::do_redirect("../admin/lock.php");
	}

	mysql_close ($db);

	ToolBox::do_redirect("../admin/lock.php?errno=1");
}

?>

<html>
<body>
<center>

<? if (isset($errno) && $errno == 1) ToolBox::alert('Identification NOK'); ?>

<form ACTION="../admin/lock.php" METHOD=POST>
<input type="hidden" name=validation value=1>

<table border="0">
<td colspan="2" align="center"><b><?= ($lock == 0 ? "Lock Access JC" : "Unlock Access JC") ?></b></td>
<tr>
<td>login:</td><td><input TYPE=TEXT NAME=login></td>
<tr>
<td>pwd:</td><td><input TYPE=PASSWORD NAME=pwd></td>
<tr>
<td colspan="2" align="right"><input type="submit" NAME=valider VALUE="Valider"></td>
</table>

</form>

</center>
</body></html>
