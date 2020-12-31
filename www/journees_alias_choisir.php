<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

// Récupération des équipes
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), "-1");
$liste_journees = $sjs->getAllNoneAliasJournee();

?>

<FORM ACTION="journees_alias_ajouter.php" METHOD=POST>

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

echo "<tr><td>Choix de la journée de référence : <select name=\"choix_journee\">";
foreach($liste_journees as $item)
{
	echo "<option value=\"".$item['id']."\">".$sjs->getNomJournee($item['nom'])." (".$item['date'].")</option>";
}
echo "</select></td></tr>";

?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
	<TR><TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Annuler" onclick="return annuler();"></INPUT></TD>
	    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Ajouter"></INPUT></TD>
	</TABLE></TD>


</TABLE>

<script>
function annuler()
{
	document.forms[0].action='../www/calendar.php';

	return true;
}
</script>

</FORM>

<? $menu->end(); ?>
