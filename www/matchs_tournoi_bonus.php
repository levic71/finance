<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "StatsBuilder.php";
include "ManagerFXList.php";

$modifier = isset($pkeys_where) && $pkeys_where != "" ? true : false;

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

// Récupération des informations de la journée
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$row = $sjs->getJournee();

// Récupération des équipes
$type_matchs = "";
if ($sess_context->getChampionnatType() == _TYPE_TOURNOI_)
{
	// Pour résoudre pb avec la page matchs_tournoi.php
	if (isset($niveau)) $options_type_matchs = $niveau;
	
	$items = explode('|', $options_type_matchs);
	$type_matchs = $items[0];
	$niveau_type = $items[1];
	$ordre       = isset($items[2]) ? $items[2] : 0;

	// Formatage du champs équipes pour ne prendre que les equipes de poules pour les poules et toutes équipes pour la phase finale
	$equipes = "";

	$tmp = str_replace('|', ',', $row['equipes']);
	$items = explode(',', $tmp);
	foreach($items as $item) 
		if ($item != "") $equipes .= $equipes == "" ? $item : ",".$item;
}
else
	$equipes = $row['equipes'];
	
// Récupération des bonus
$tab_bonus = array();
if ($row['bonus'] != "") $tab_bonus = explode(',', $row['bonus']);

$bonus = array();
foreach($tab_bonus as $item)
{
	$x = explode('=', $item);
	$bonus[$x[0]] = $x[1];
}

// Récupération des infos des equipes	
$ses = new SQLEquipesServices($sess_context->getRealChampionnatId());
$equipes_infos = $ses->getListeEquipes($equipes);
	
$lst_equipes = array();
if ($equipes != "") $lst_equipes = explode(',', $equipes);

?>
<SCRIPT>
function validate_and_submit()
{
	return true;
}
function annuler()
{
	document.forms[0].action='<?= $sess_context->getChampionnatType() == _TYPE_TOURNOI_ ? "matchs_tournoi.php?options_type_matchs=".$options_type_matchs : "matchs.php" ?>';

	return true;
}
</SCRIPT>

<FORM ACTION=matchs_tournoi_bonus_modifier_do.php METHOD=POST>
<INPUT TYPE=HIDDEN NAME=options_type_matchs VALUE="<?= $options_type_matchs ?>">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

$tab = array();

foreach($lst_equipes as $item)
{
	$default = isset($bonus[$item]) ? $bonus[$item] : 0;
	$options = "";
	for($i = -9; $i < 10; $i++)
	{
		$options .= "<OPTION VALUE=".$i." ".($default == $i ? "SELECTED" : "").">".$i;
	}
	$tab[] = array($equipes_infos[$item]['nom'], "<SELECT NAME=bonus_".$item.">".$options."</SELECT>");
}

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Gestion des bonus", "CENTER");
$fxlist->FXSetColumnsName(array("Equipe", "Bonus"));
$fxlist->FXSetColumnsAlign(array("LEFT", "CENTER"));
$fxlist->FXSetColumnsColor(array("", "", "#BCC5EA"));
$fxlist->FXSetColumnsWidth(array("", "25%"));
$fxlist->FXDisplay();
echo "</TD>";

?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
<TR><TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Annuler" onclick="return annuler();"></INPUT></TD>
    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE=Modifier onclick="return validate_and_submit();"></INPUT></TD>
</TABLE></TD>


</TD>
</TABLE>
</FORM>

<? $menu->end(); ?>
