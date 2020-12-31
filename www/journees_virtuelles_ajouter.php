<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";
include "StatsBuilder.php";

// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
// >>>>>> Ecran pour la consultation/insertion/modification d'une journée virtuelle
// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

$db = dbc::connect();

// Par défaut, on vient consulter
$modifier    = 0;
$nb_journees = 0;
$refdate     = date("Y-m-d");
$journee['classement_equipes']  = "";

// Si $pkeys_where_jb_journees est positionné, alors on récupère les infos de la journée virtuelle (visu+modification)
if (isset($pkeys_where_jb_journees) || isset($journee_next) || isset($journee_prev))
{
	// Si on vient de la liste des journées
	if (isset($pkeys_where_jb_journees))
		$sess_context->setJourneeId(str_replace(" WHERE id=", "", $pkeys_where_jb_journees));
	
	// Si on vient de la même page avec les fleches suivante/precedente, alors on cherche la journée à afficher
	if (isset($journee_next) || isset($journee_prev))
	{
		$journee_selected = isset($journee_next) ? $journee_next : $journee_prev;
	    $select = "select id, virtuelle from jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." ORDER BY date ASC";
	    $res = dbc::execSQL($select);
	    while($row = mysql_fetch_array($res)) $id_journee[] = $row;
	
	    $index_selected = -1;
	    if (count($id_journee) > 0)
	    {
	        while(list($cle, $valeur) = each($id_journee))
	        {
	            if ($valeur['id'] == $journee_selected)
	            {
	                $index_selected = $cle;
	                break;
	            }
	        }
	    }

		$new_index = -1;
	    if ($index_selected != -1 && isset($journee_prev))
	        if ($index_selected != 0) $new_index = --$index_selected;
			
	    if ($index_selected != -1 && isset($journee_next))
	        if ($index_selected != (count($id_journee) - 1)) $new_index = ++$index_selected;
			
		if ($new_index != -1) 
		{
			$sess_context->setJourneeId($id_journee[$new_index]['id']);
			
			// Si la journée à afficher n'est pas virtuelle, il faut rediriger sur matchs_tournoi.php
			if ($id_journee[$new_index]['virtuelle'] == 0)
			{
				ToolBox::do_redirect("matchs_tournoi.php?pkeys_where_jb_journees=+WHERE+id%3D".$id_journee[$new_index]['id']);
				exit();
			}
		}
	}

	$sjs = new SQLJourneesServices($sess_context->getChampionnatId(), $sess_context->getJourneeId());
	$journee = $sjs->getJournee();

	$nb_journees = $journee['nom'];
	$refdate     = $journee['date'];
	if ($sess_context->isAdmin()) $modifier = 1;
}
else // Récupération du nombre de journées pour une nouvelle insertion
{
	$scs = new SQLChampionnatsServices($sess_context->getChampionnatId());
	$nb_journees = $scs->getNbJournees() + 1;
}

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

?>

<SCRIPT SRC="../js/ts_picker.js"></SCRIPT>

<LINK REL="stylesheet" HREF="../css/XList.css" TYPE="text/css">
<FORM ACTION=journees_virtuelles_ajouter_do.php METHOD=POST>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

echo "<INPUT TYPE=HIDDEN NAME=nom_journee  VALUE=\"".$nb_journees."\">";
echo "<INPUT TYPE=HIDDEN NAME=modification VALUE=\"".$modifier."\">";

if ($sess_context->isAdmin())
{
	$calendar = "<TABLE BORDER=0><TR><TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=zone_calendar id=zone_calendar SIZE=10 VALUE=\"".ToolBox::mysqldate2date($refdate)."\"></INPUT></TD><TD ALIGN=LEFT><a href=\"#\" onClick=\"javascript:show_calendar('document.forms[0].zone_calendar', document.forms[0].zone_calendar.value);\" title=\"Afficher le calendrier\"><img src=\"../images/images_cal/c_b.gif\" border=0/></a></TD></TABLE>";
	$title    = "<TABLE BORDER=0 HEIGHT=30 WIDTH=100%><TR><TD WIDTH=100></TD><TD ALIGN=CENTER><FONT CLASS=big COLOR=white> ".($sess_context->isAdmin() ? ($modifier == 0 ? "Ajout" : "Modification") : "Visualisation")." d'une journée virtuelle </FONT></TD><TD>".$calendar."</TD><TD ALIGN=RIGHT WIDTH=100></TD></TABLE>";
}
else
{
	$calendar = ToolBox::mysqldate2date($refdate);
	$title    = "<TABLE BORDER=0 HEIGHT=30 WIDTH=100%><TR><TD ALIGN=LEFT><A HREF=journees_virtuelles_ajouter.php?journee_prev=".$sess_context->getJourneeId()."><IMG SRC=../images/journee_prv.gif BORDER=0 ALT=\"Journée précédente\"></A></TD><TD ALIGN=CENTER><FONT CLASS=big COLOR=white> Visualisation de la journée virtuelle du ".$calendar." </FONT></TD><TD ALIGN=RIGHT><A HREF=journees_virtuelles_ajouter.php?journee_next=".$sess_context->getJourneeId()."><IMG SRC=../images/journee_nxt.gif BORDER=0 ALT=\"Journée suivante\"></A></TD></TABLE>";
}

echo "<TR><TD>";
$fxlist = new FXListJourneeVirtuelle($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId(), $journee['classement_equipes'], $sess_context->isAdmin());
$fxlist->FXSetTitle($title);
$fxlist->FXDisplay();
echo "</TD>";

if ($sess_context->isAdmin() && !(isset($FXOption) && $FXOption == _FXLIST_EXPORT_))
{
	echo "<TR><TD ALIGN=RIGHT><TABLE BORDER=0>";
	echo "	<TD ALIGN=RIGHT><INPUT TYPE=SUBMIT NAME=bouton VALUE=\"Annuler\" onClick=\"javascript:annuler_journee_virtuelle();\"></TD>";
	echo "	<TD ALIGN=RIGHT><INPUT TYPE=SUBMIT NAME=bouton VALUE=\"Valider\" onClick=\"javascript:return valider_journee_virtuelle();\"></TD>";
	echo "</TABLE></TD>";
}

?>

<SCRIPT>
function annuler_journee_virtuelle()
{
    document.forms[0].action = '<?= $sess_context->championnat['visu_journee'] == _VISU_JOURNEE_CALENDRIER_ ? "calendar.php" : "journees.php" ?>';
}
function ajouter_journee_virtuelle()
{
    if (!verif_JJMMAAAA(document.forms[0].zone_calendar.value, 'Date'))
		return false;
}
</SCRIPT>

</TABLE>
</FORM>

<? $menu->end(); ?>