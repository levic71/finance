<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";
include "StatsBuilder.php";

$db = dbc::connect();

// On récupère les infos de la journée
$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
$row = $sjs->getJournee();

$is_journee_alias = ($row['id_journee_mere'] == "" || $row['id_journee_mere'] == "0" ? false : true);

$date_journee = $row['date'];
$exclude_matchs_journee = "";

if ($is_journee_alias)
{
	$tmp = explode('|', $row['id_matchs']);
	$matchs_journee = isset($tmp[1]) ? $tmp[1] : "";
	$journee_mere = $sjs->getJournee($row['id_journee_mere']);
	$id_journee_mere = $row['id_journee_mere'];
	$nb_poules    = $journee_mere['tournoi_nb_poules'];
	$phase_finale = $journee_mere['tournoi_phase_finale'];
	$consolante   = $journee_mere['tournoi_consolante'];
	$equipes_journee = $journee_mere['equipes'];
	$liste_poules = explode('|', $journee_mere['equipes']);
}
else
{
	$tmp = explode('|', $row['id_matchs']);
	$exclude_matchs_journee = isset($tmp[1]) ? $tmp[1] : "";
	$nb_poules    = $row['tournoi_nb_poules'];
	$phase_finale = $row['tournoi_phase_finale'];
	$consolante   = $row['tournoi_consolante'];
	$equipes_journee = $row['equipes'];
	$liste_poules = explode('|', $row['equipes']);
}

TemplateBox :: htmlBegin(false);

// Formatage du champs équipes pour prendre en compte les poules et les phases finales
$all_equipes = "";
$nb_equipes  = 0;

// Mise à plat du champ 'equipes' pour récupérer toutes les équipes sans distinction de poules
$tmp = str_replace('|', ',', $equipes_journee);
$items = explode(',', $tmp);
foreach($items as $item) 
{
	if ($item != "")
    {
        $all_equipes .= $all_equipes == "" ? $item : ",".$item;
        $nb_equipes++;
    }
}

$classement_equipes = array();
$equipes = array();

?>
<script type="text/javascript">
/* increasing and decreasing text size.
how this works:
1- loop through all of the stylesheets
2 - check if they have a title
3 - check if that title contains "article" (indicates our article stylesheet)
4 - disable all article stylesheets
5 - enable the next size up or down.
6 - exit, enjoy the day.
*/
var sz=13 //default stylesheet
function selectStyleSheet(dir)
{
	var li; // link items - that is, stylesheets
	//make sure we're under limit
	if (10<sz+dir&&sz+dir<20)
	{
		for(var i=0; li=document.getElementsByTagName("link")[i]; i++)
		{
			// get stylesheets
			if(li.getAttribute("rel").indexOf("style") != -1 && li.getAttribute("title"))
			{
				if(li.getAttribute("title").indexOf("planning") !=-1) li.disabled = true;
				// check if 1 - it's a stylesheet with a title, 2- if it is an article stylesheet, disable it
				if (li.getAttribute("title").indexOf(sz+dir)>-1)li.disabled = false;
				// if it's the next in line, enable it
			}
		}
		//don't forget to increment the size, so we know what's next....
		sz=sz+dir;
	}
}
</script>

<LINK REL="stylesheet" HREF="../css/planning_13.css" TYPE="text/css" title="planning_13">
<LINK REL="alternate stylesheet" HREF="../css/planning_11.css" TYPE="text/css" title="planning_11">
<LINK REL="alternate stylesheet" HREF="../css/planning_15.css" TYPE="text/css" title="planning_15">
<LINK REL="alternate stylesheet" HREF="../css/planning_17.css" TYPE="text/css" title="planning_17">
<LINK REL="alternate stylesheet" HREF="../css/planning_19.css" TYPE="text/css" title="planning_19">

<? if (!isset($nb_terrains)) $nb_terrains = 6; ?>
<? if (!isset($nb_colonnes)) $nb_colonnes = 3; ?>

<FORM ACTION=matchs_tournoi_planning.php METHOD=POST ENCTYPE="multipart/form-data">
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=100%>

<?

$matchs_poules = array();
for($x = 1; $x <= $nb_poules; $x++)
{
	$filtre_niveau = " AND niveau='P|".$x."'";
	$fxlist = new FXListMatchsPoules($sess_context->getChampionnatId(), $is_journee_alias ? $id_journee_mere : $sess_context->getJourneeId(), false, $filtre_niveau);
	$matchs_poules[$x] = $fxlist->body->tab;
}

$matchs_terrain = array();
$k = 0;
$nb_matchs = 0;
while(true)
{
	for($i = 1; $i <= $nb_poules; $i++)
	{
		if (count($matchs_poules[$i]) > 0)
		{
			$m = array_pop($matchs_poules[$i]);
			$matchs_terrain[$k][] = $m;
			$k++;
            $nb_matchs++;
			if ($k >= $nb_terrains) $k = 0;
		}
	}
	
	$again = 0;
	for($i = 1; $i <= $nb_poules; $i++)
		if (count($matchs_poules[$i]) > 0) $again = 1;
		
	if ($again == 0) break;
}

?>
<STYLE type="text/css">
UL {
    list-style-type: none;
}
LI {
    float: left;
    margin-right: 50px;
    background: #525252;
    color: white;
    padding: 0px 0px 0px 5px;
}
</STYLE>
<TR><TD ALIGN=CENTER HEIGHT=30>
<ul>
<li>Nombre d'équipes: <SELECT><OPTION><?= $nb_equipes ?></SELECT></li>
<li>Nombre de matchs: <SELECT><OPTION><?= $nb_matchs ?></SELECT></li>
<li>Nombre de terrains: 
<SELECT NAME=nb_terrains onChange="javascript:submit();">
<?
for($i = 1; $i < 16; $i++)
    echo "<OPTION VALUE=".$i." ".($nb_terrains == $i ? "SELECTED" : "").">".$i;
?>
</SELECT></li><li>
Nombre de colonnes:
<SELECT NAME=nb_colonnes onChange="javascript:submit();">
<?
for($i = 1; $i < 5; $i++)
    echo "<OPTION VALUE=".$i." ".($nb_colonnes == $i ? "SELECTED" : "").">".$i;
?>
</SELECT></li>
<li>
<A HREF="#" onClick="javascript:selectStyleSheet(-2);"><IMG SRC=../images/txt-pet.gif BORDER=0></A>
&nbsp;
<A HREF="#" onClick="javascript:selectStyleSheet(2);"><IMG SRC=../images/txt-grd.gif BORDER=0></A>
&nbsp;
<A HREF="#" onClick="javascript:window.print();"><IMG SRC=../images/extlist.gif BORDER=0></A>
</li>
</ul>
</TD>

<?

echo "<TR><TD><TABLE BORDER=0 WIDTH=100% CELLSPACING=5 CELLPADDING=0>";
$i = 0;
for($k = 0; $k < $nb_terrains; $k++)
{
	if (!isset($matchs_terrain[$k])) break;
	
	$fxlist->body->tab = $matchs_terrain[$k];
	if (($i % $nb_colonnes) == 0) echo "<TR VALIGN=TOP>";
	echo "<TD WIDTH=33%>";
	echo "<P CLASS=titre_planning>TERRAIN ".($k+1)."</P>";
    $fxlist->FXSetColumnsAlign(array("RIGHT", "", "LEFT"));
    $fxlist->FXSetColumnsColor(array(($k % 2) == 0 ? $fxlist->c1_column : $fxlist->c2_column, $fxlist->color_numero_column, ($k % 2) == 0 ? $fxlist->c1_column : $fxlist->c2_column));
	$fxlist->FXSetTitle("");
	$fxlist->FXDisplay();
	echo "</TD>";
	$i++;
}
echo "</TABLE></TD>";

?>

</TABLE>
</FORM>

<? TemplateBox :: htmlEnd(); ?>
