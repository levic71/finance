<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom());

$modifier = 0;
// Récupération des infos du joueur à modifier
if (isset($pkeys_where) && $pkeys_where != "")
{
	$select = "SELECT * FROM jb_joueurs ".urldecode($pkeys_where);
	$res = dbc::execSQL($select);
	$joueur = mysql_fetch_array($res);
	$modifier = 1;
}

$j_id     = ($modifier == 1) ? $joueur['id']           : "";
$j_nom    = ($modifier == 1) ? $joueur['nom']          : "";
$j_prenom = ($modifier == 1) ? $joueur['prenom']       : "";
$j_nais   = ($modifier == 1) ? ToolBox::mysqldate2date($joueur['dt_naissance']) : date("d/m/Y");
$j_pseudo = ($modifier == 1) ? $joueur['pseudo']       : "";
$j_email  = ($modifier == 1) ? $joueur['email']        : "";
$j_tel1	  = ($modifier == 1) ? $joueur['tel1']         : "";
$j_tel2	  = ($modifier == 1) ? $joueur['tel2']         : "";
$j_photo  = ($modifier == 1) ? $joueur['photo']        : "";
$j_presen = ($modifier == 1) ? $joueur['presence']     : "1";
$j_etat   = ($modifier == 1) ? $joueur['etat']         : "0";

$j_nom    = ($j_nom    == "") ? "\" STYLE='background-color: #FFCCCC'" : $j_nom;
$j_pseudo = ($j_pseudo == "") ? "\" STYLE='background-color: #FFCCCC'" : $j_pseudo;

if ($j_photo == "") $j_photo="../uploads/linconnu.gif";

// Récupération des joueurs qui participent à la saion courante
$sss = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());
$lst_joueurs_saison = $sss->getListeJoueurs();

// Récupération de tous les joueurs du championnat (ttes saisons confondues)
$select = "SELECT pseudo FROM jb_joueurs WHERE id_champ=".$sess_context->getRealChampionnatId();
$res = dbc::execSQL($select);
$nb = mysql_num_rows($res) - 1;

?>

<SCRIPT SRC="../js/ts_picker.js"></SCRIPT>

<SCRIPT>
var all_pseudo = new Array(<?
$lib = "";
while($row = mysql_fetch_array($res))
{
	if ($modifier == 1 && $row['pseudo'] == $j_pseudo) continue;
	$lib .= "'".$row['pseudo']."',";
}
echo ereg_replace(",,", ",", ereg_replace("^,", "", ereg_replace(",$", "", $lib)));
?>);
var saison_pseudo = new Array(<?
$lib = "";
foreach($lst_joueurs_saison as $joueur)
{
	if ($modifier == 1 && $joueur['pseudo'] == $j_pseudo) continue;
	$lib .= "'".$joueur['pseudo']."',";
}
echo ereg_replace(",,", ",", ereg_replace("^,", "", ereg_replace(",$", "", $lib)));
?>);
</SCRIPT>

<FORM ACTION=<?= $modifier == 1 ? "joueurs_modifier_do.php" : "joueurs_ajouter_do.php" ?> METHOD=POST ENCTYPE="multipart/form-data">
<INPUT TYPE=HIDDEN NAME=MAX_FILE_SIZE VALUE=50000>
<INPUT TYPE=HIDDEN NAME=old_pseudo VALUE="<?= $j_pseudo ?>">
<INPUT TYPE=HIDDEN NAME=id_joueur  VALUE="<?= $j_id ?>">
<INPUT TYPE=HIDDEN NAME=source_tux VALUE="">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

$tab = array();

$tab[] = array("Nom:",      "<INPUT TYPE=TEXT NAME=nom SIZE=32 VALUE=\"".$j_nom."\" onKeyUp='javascript:changeColor(this);'>");
$tab[] = array("Prénom:",   "<INPUT TYPE=TEXT NAME=prenom SIZE=32 VALUE=\"".$j_prenom."\">");
$tab[] = array("Date de naissance:", "<TABLE BORDER=0 CELLPADDING=2 CELLSPACING=0><TR><TD><INPUT TYPE=TEXT  NAME=date_nais SIZE=32 VALUE=\"".$j_nais."\"></INPUT></TD><TD><a href=\"#\" onClick=\"javascript:show_calendar('document.forms[0].date_nais', document.forms[0].date_nais.value);\" title=\"Afficher le calendrier\"><img src=\"../images/images_cal/c_b.gif\" border=0/></a></TD><TD><B>(JJ/MM/AAAA)</B></TD></TABLE>");
$tab[] = array("Pseudo:",   "<INPUT TYPE=TEXT NAME=pseudo SIZE=32 VALUE=\"".$j_pseudo."\" onKeyUp='javascript:changeColor(this);'>");
$tab[] = array("Email:",    "<INPUT TYPE=TEXT NAME=email SIZE=32 VALUE=\"".$j_email."\">");
$tab[] = array("Téléphone 1:","<INPUT TYPE=TEXT NAME=tel1 SIZE=32 VALUE=\"".$j_tel1."\">");
$tab[] = array("Téléphone 2:","<INPUT TYPE=TEXT NAME=tel2 SIZE=32 VALUE=\"".$j_tel2."\">");
$photo_choice = "
<DIV STYLE=\"display: block;\">
	<DIV STYLE=\"display: inline;\">
		<DIV STYLE=\"float: left;\">
			<INPUT TYPE=FILE NAME=photo SIZE=32>
		</DIV>
		<DIV STYLE=\"float: left;\">
			<IMG ID=photochoice onmouseover=\"show_info_upleft('<IMG SRC=".$j_photo." border=1>', event);\" onmouseout=\"close_info();\" SRC=".$j_photo." BORDER=1 HEIGHT=20 WIDTH=20>
		</DIV>
		<DIV STYLE=\"padding: 5px 0px 0px 5px;\">
			&nbsp;50Ko max, jpg ou gif
		</DIV>
	</DIV>
	<DIV STYLE=\"margin: 20px 0px 10px 0px;\">
		<A HREF=# onClick=\"javascript:showtux();\" CLASS=cmd>Choisir un TUX pour la photo</A>
	</DIV>
	<DIV ID=tuxbox STYLE=\"display: none;\">
";

$tux = JKCache::getCache("../cache/flux_tux.txt", -1, "_FLUX_TUX_");
foreach($tux as $item)
	$photo_choice .= "<IMG onClick=\"javascript:selectTux(this);\" SRC=../tux/".$item." BORDER=1 HEIGHT=128 WIDTH=128>";
		
$photo_choice .= "
		<A CLASS=cmd HREF=http://tux.crystalxp.net TARGET=_blank>Merci à tux.crystalxp.net</A>
	</DIV>
</DIV>
";

$tab[] = array("Photo:", $photo_choice);
$tab[] = array("Régulier:", "<INPUT TYPE=RADIO NAME=presence  VALUE=0 ".($j_presen == 0 ? "CHECKED" : "")."> Non </INPUT><INPUT TYPE=RADIO NAME=presence VALUE=1 ".($j_presen == 1 ? "CHECKED" : "")."> Oui </INPUT></TD>");
$lib = "<SELECT NAME=etat>";
reset($libelle_etat_joueur);
while(list($cle, $val) = each($libelle_etat_joueur))
	$lib .= "<OPTION VALUE=".$cle." ".($j_etat == $cle ? "SELECTED" : "").">".$val;
$lib .= "</SELECT>";
$tab[] = array("Etat:", $lib);
if ($modifier != 1 && $sess_context->isFreeXDisplay())
{
	$lib  = "<SELECT NAME=auto_create_team>";
	$lib .= "<OPTION VALUE=0> Oui ";
	$lib .= "<OPTION VALUE=1> Oui pour les joueurs réguliers";
	$lib .= "<OPTION ".(!$sess_context->isFreeXDisplay() ? "SELECTED" : "")." VALUE=2> Non";
	$lib .= "</SELECT>";
	$tab[] = array("Création automatique<BR>de toutes les équipes possible:", $lib);
}
else
	echo "<INPUT TYPE=HIDDEN NAME=auto_create_team VALUE=2>";

echo "<TR><TD COLSPAN=2>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle(($modifier == 1 ? "Modification" : "Ajout")." d'un joueur", "CENTER");
$fxlist->FXSetColumnsAlign(array("RIGHT", "LEFT"));
$fxlist->FXSetColumnsColor(array("#BCC5EA", ""));
$fxlist->FXSetColumnsWidth(array("20%", ""));
$fxlist->FXDisplay();
echo "</TD>";

?>

<TR><TD ALIGN=RIGHT><TABLE BORDER=0>
<TR><TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="Annuler" onClick="return annuler();"></TD>
    <TD ALIGN=LEFT><INPUT TYPE=SUBMIT VALUE="<?= $modifier == 1 ? "Modifier" : "Ajouter"?>" onClick="return validate_and_submit();"></TD>
</TABLE></TD>

</TABLE>

<SCRIPT>
function showtux()
{
	if (document.getElementById("tuxbox").style.display == '')
		document.getElementById("tuxbox").style.display = 'none';
	else
		document.getElementById("tuxbox").style.display = '';
}
function selectTux(obj)
{
	document.forms[0].source_tux.value = obj.src;
	document.getElementById("photochoice").src = obj.src;
	document.getElementById("tuxbox").style.display = 'none';
}
function validate_and_submit()
{
    if (!verif_alphanumext(document.forms[0].nom.value, 'Nom', -1))
		return false;
	document.forms[0].nom.value = document.forms[0].nom.value.toUpperCase();
	document.forms[0].prenom.value = upperFirstLetter(document.forms[0].prenom.value);

    if (!verif_JJMMAAAA(document.forms[0].date_nais.value, 'Date de naissance'))
		return false;

	if (document.forms[0].photo.value != '')
	{
		items = document.forms[0].photo.value.split('.');
		if (items[1] != 'gif' && items[1] != 'GIF' && items[1] != 'jpg' && items[1] != 'JPG')
		{
			alert('Le format de l\'image doit être \'gif\' ou \'jpg\'.');
			return false;
		}
	}

    if (!verif_alphanumext(document.forms[0].pseudo.value, 'Pseudo', -1))
		return false;
	document.forms[0].pseudo.value = upperFirstLetter(document.forms[0].pseudo.value);

	for(i=0; i < saison_pseudo.length; i++)
	{
		if (document.forms[0].pseudo.value == saison_pseudo[i])
		{
			alert('Ce pseudo est déjà utilisé, veuillez en choisir un autre ...');
			return false;
		}
	}
	
	for(i=0; i < all_pseudo.length; i++)
	{
		if (document.forms[0].pseudo.value == all_pseudo[i])
		{
			alert('Ce pseudo est déjà utilisé dans une saison précédente, veuillez en choisir un autre ou insérer ce joueur dans cette saison via la procédure adéquate ...');
			return false;
		}
	}
	
	if (document.forms[0].email.value != '')
	{
		if (!verif_EMAIL(document.forms[0].email.value))
			return false;
	}
	
	return true;
}
function annuler()
{
	document.forms[0].action='joueurs.php';

	return true;
}
document.forms[0].nom.focus();
</SCRIPT>

</FORM>

<? $menu->end(); ?>
