<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

$joueurs = array();

// Récupération données équipe
$req = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$id_equipe;
$res = dbc::execSQL($req);
$equipe = mysql_fetch_array($res);
$mes_joueurs = explode('|', $equipe['joueurs']);
if (count($mes_joueurs) >= 2)
{
	$defaut_defenseur = $mes_joueurs[0];
	$defaut_attaquant = $mes_joueurs[1];
}

// Récupération données match
$req = "SELECT * FROM jb_matchs WHERE id_champ=".$sess_context->getChampionnatId()." AND id=".$id_match;
$res = dbc::execSQL($req);
$match = mysql_fetch_array($res);
// Réaffectation joueurs réellement sur le terrain
if ($choix_equipe == 1)
{
	if ($match['surleterrain1'] != "")
	{
		$items = explode('|', $match['surleterrain1']);
		$defaut_defenseur = $items[0];
		$defaut_attaquant = $items[1];
	}
}
// Réaffectation joueurs réellement sur le terrain
if ($choix_equipe == 2)
{
	if ($match['surleterrain2'] != "")
	{
		$items = explode('|', $match['surleterrain2']);
		$defaut_defenseur = $items[0];
		$defaut_attaquant = $items[1];
	}
}

// Récupération des joueurs
$req = "SELECT * FROM jb_joueurs WHERE id_champ=".$sess_context->getChampionnatId()." ORDER BY pseudo ASC";
$res = dbc::execSQL($req);
$select1 = "<SELECT NAME=selected_defenseur>";
$select2 = "<SELECT NAME=selected_attaquant>";
while($row = mysql_fetch_array($res))
{
	reset($mes_joueurs);
	foreach($mes_joueurs as $j)
	{
		if ($row['id'] == $j)
		{
			$select1 .= "<OPTION VALUE=".$row['id']." ".($j == $defaut_defenseur ? "SELECTED" : "").">".$row['pseudo'];
			$select2 .= "<OPTION VALUE=".$row['id']." ".($j == $defaut_attaquant ? "SELECTED" : "").">".$row['pseudo'];
		}
	}
}
mysql_free_result($res);
$select1 .= "</SELECT>";
$select2 .= "</SELECT>";

?>

<FORM ACTION=matchs_choixjoueurs_do.php METHOD=POST>
<INPUT TYPE=HIDDEN NAME=choix_equipe VALUE=<?= $choix_equipe ?>>
<INPUT TYPE=HIDDEN NAME=id_match     VALUE=<?= $id_match ?>>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

$tab = array();

$lib  = "<INPUT TYPE=HIDDEN NAME=selection VALUE=\"\"></INPUT>";
$lib .= "<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=1>";
$lib .= "<TR><TD ALIGN=CENTER><B><SMALL> Défenseur : </SMALL></B></TD>";
$lib .= "    <TD>".$select1."</TD>";
$lib .= "<TR><TD ALIGN=CENTER><B><SMALL> Attaquant : </SMALL></B></TD>";
$lib .= "    <TD>".$select2."</TD>";
$lib .= "</TABLE>";
$tab[] = array($lib);

echo "<TR><TD>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle("Choix des positions de joueurs", "CENTER");
$fxlist->FXDisplay();
echo "</TD>";

?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
	<TR><TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Annuler"  onclick="return annuler();"></INPUT></TD>
	    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Modifier" onclick="return validate_and_submit();"></INPUT></TD>
	</TABLE></TD>

<SCRIPT>
function validate_and_submit()
{
	if (document.forms[0].selected_defenseur.value == document.forms[0].selected_attaquant.value)
	{
		alert('Vous devez sélectionner 2 joueurs différents ...');
		return false;
	}

	return true;
}
function annuler()
{
	document.forms[0].action='matchs.php?pkeys_where_jb_journees=+WHERE+id%3D<?= $sess_context->id_journee_encours ?>';

	return true;
}
</SCRIPT>

</TABLE>
</FORM>

<? $menu->end(); ?>
