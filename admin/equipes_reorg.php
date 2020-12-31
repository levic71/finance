<?

include "../include/sess_context.php";

session_start();

include "../www/common.php";
include "../include/inc_db.php";
include "../www/StatsBuilder.php";

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatId());

$db = dbc::connect();

?>

<FORM ACTION="superuser_fcts.php">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">
<TR><TD ALIGN=CENTER><TABLE CELLPADDING=0 CELLSPACING=0 CLASS=masterfree>

<?

echo "<TR>";
HTMLTable::printCellWithColSpan("<FONT SIZE=5 COLOR=white>Résultat réorganisation équipes</FONT>", "#4863A0", "100%", "CENTER", _CELLBORDER_ALL_, 3);

// On parcours toutes les équipes
$i = 0;
$req = "SELECT * FROM jb_equipes";
$res = dbc::execSQL($req);
while($equipe = mysql_fetch_array($res))
{
	if (isset($equipe['id_joueur1']) && isset($equipe['id_joueur2']))
	{
	    $js = $equipe['id_joueur1']."|".$equipe['id_joueur2'];
	    $update = "UPDATE jb_equipes SET joueurs='".$js."', nb_joueurs=2 WHERE id=".$equipe['id'];
	    $res_update = dbc::execSQL($update);

		echo "<TR onMouseOver=\"this.bgColor='#D5D9EA'\" onMouseOut =\"this.bgColor=''\">";
		HTMLTable::printCell($i, "",  "5%", "CENTER", _CELLBORDER_BOTTOM_);
		HTMLTable::printCell("update équipe id=".$equipe['id'], "#BCC5EA", "80%", "CENTER", _CELLBORDER_BOTTOM_);
		HTMLTable::printCell("Ok", "",  "", "CENTER", _CELLBORDER_BOTTOM_);

		$i++;
	}
}

?>

</TABLE></TD>

</TABLE>
</FORM>

<? $menu->end(); ?>
