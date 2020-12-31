<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../include/selection.php";
include "ManagerFXList.php";

$db = dbc::connect();

$inscription = isset($inscription) ? $inscription : 0;

if ($inscription == 1 && isset($inscription_valid) && $inscription_valid == "no")
	ToolBox::alert("Désolé, le championnat ".$ch_nom." existe déjà, veuillez en saisir un autre ...");

if ($inscription == 1 && isset($inscription_antispam) && $inscription_antispam == "no")
	ToolBox::alert("Désolé, le code de contrôle n'est pas correct, saisissez le de nouveau.");

$menu = new menu($inscription == 1 ? "forum_access" : "full_access");
$menu->debut($sess_context->getChampionnatNom(), $inscription == 1 ? "10" : "11", "initEditor()");

if ($inscription == 0)
{
	$scs = new SQLChampionnatsServices($sess_context->getRealChampionnatId());
	$row = $scs->getChampionnat();
	$row['options'] = $row['options'] != "" ? $row['options'] : "1|1|1|1|1|1|1|0|0|0|0|0";
	$demo = $row['demo'] == 1;
}
else
{
	$row = array();
	$row['championnat_nom'] = isset($ch_nom) ? str_replace('\\\'', '\'', $ch_nom) : "";
	$row['description']     = isset($ch_description) ? str_replace('\\\'', '\'', $ch_description) : "";
	$row['gestionnaire']    = isset($ch_gestionnaire) ? str_replace('\\\'', '\'', $ch_gestionnaire) : "";
	$row['email']           = isset($ch_email) ? $ch_email : "";
	$row['type']            = isset($type) ? $type : _TYPE_CHAMPIONNAT_;
	$row['options']         = isset($options) ? $options  : "1|1|1|1|1|1|1|0|0|0|0|0";
	$row['login']           = isset($ch_login) ? str_replace('\\\'', '\'', $ch_login) : "";
	$row['pwd']             = isset($ch_pwd) ? $ch_pwd : "";
	$row['pwd2']             = isset($ch_pwd2) ? $ch_pwd2 : "";
	$row['news']            = isset($ta) ? str_replace('\\\'', '\'', $ta) : "";
	$row['dt_creation']     = "";
	$row['type_lieu']       = isset($type_lieu) ? $type_lieu : _LIEU_VILLE_;
	$row['lieu']            = isset($lieu_pratique) ? str_replace('\\\'', '\'', $lieu_pratique) : "";
	$row['visu_journee']    = isset($visu_journee) ? $visu_journee : 0;
	$row['valeur_victoire'] = isset($valeur_victoire) ? $valeur_victoire : 3;
	$row['valeur_nul']      = isset($valeur_nul) ? $valeur_nul : 1;
	$row['valeur_defaite']  = isset($valeur_defaite) ? $valeur_defaite : 1;
	$row['gestion_nul']     = isset($gestion_nul) ? $gestion_nul : 0;
	$row['nom_saison']      = isset($ch_nom_saison) ? str_replace('\\\'', '\'', $ch_nom_saison) : "Saison ".date("Y")."-".(date("Y")+1);
	$row['friends']         = isset($selected_friends) ? $selected_friends : "";
	$demo                   = false;
	$row['gestion_fanny']   = isset($gestion_fanny) ? $gestion_fanny : 1;
	$row['gestion_sets']    = isset($gestion_sets) ? $gestion_sets : 1;
	$row['tri_classement_general'] = isset($tri_classement_general) ? $tri_classement_general : 1;
	$row['type_sport']      = isset($type_sport) ? $type_sport : 1;
	if (!isset($_SESSION['antispam'])) $_SESSION['antispam'] = ToolBox::getRand(5);
	$row['controle']        = isset($ch_controle) ? $ch_controle : "";
}

$mes_options = explode('|', $row['options']);
$display_news         = isset($mes_options[0])  ? $mes_options[0]  : 1;
$display_forum        = isset($mes_options[1])  ? $mes_options[1]  : 1;
$display_fannys       = isset($mes_options[2])  ? $mes_options[2]  : 1;
$display_last_journee = isset($mes_options[3])  ? $mes_options[3]  : 1;
$display_next_journee = isset($mes_options[4])  ? $mes_options[4]  : 1;
$display_focus_player = isset($mes_options[5])  ? $mes_options[5]  : 1;
$display_clt_joueurs  = isset($mes_options[6])  ? $mes_options[6]  : 1;
$display_poule_lettre = isset($mes_options[7])  ? $mes_options[7]  : 0;
$display_all_matchs   = isset($mes_options[8])  ? $mes_options[8]  : 0;
$display_last_matchs  = isset($mes_options[9])  ? $mes_options[9]  : 0;
$display_focus_team   = isset($mes_options[10]) ? $mes_options[10] : 0;
$display_gavgp        = isset($mes_options[11]) ? $mes_options[11] : 0;

// C'est l'un ou l'autre
if ($display_focus_player == 1 && $display_focus_team == 1) $display_focus_team = 0;
if ($display_fannys == 1 && $display_last_matchs == 1) $display_last_matchs = 0;

$mode_modification = ($inscription == 1 || $sess_context->isAdmin()) && !$demo;

// Choix de l'URL de redirection
if ($inscription == 1)
	$redirect_url = "inscription_valid.php";
else
	$redirect_url = "championnat_modifier_do.php";
?>

<form action="<?= $redirect_url ?>" method="post">
<div>
<input type="hidden" name="inscription" value="<?= $inscription ?>" />
<input type="hidden" name="eq1" value="" />
<input type="hidden" name="eq2" value="" />
<input type="hidden" name="lieu_pratique" value="" />
<input type="hidden" name="selected_friends" value="<?= $row['friends'] ?>" />
<input type="hidden" name="valeur_nul" value="<?= $row['valeur_nul'] ?>" />
</div>

<div id="pageint">

<h2> <?= $mode_modification ? "Inscription championnat" : "Description championnat" ?> </h2>

<table id="inscription" border="0" cellpadding="0" cellspacing="0" width="670" summary="tab central">

<style>
#inscription .FXList_BODY td {
	padding: 5px;
}
#inscription .soustab td {
	padding: 0px;
}
</style>

<?

$tab = array();

if ($inscription == 0 && false)
{
	if ($row['type_sport'] == _TS_FOOTBALL_)
	{
		$display_fannys = 0;
		$row['gestion_fanny'] = 0;
		$display_focus_player = 0;
		$display_clt_joueurs = 0;
		$row['gestion_sets'] = 0;
		$row['gestion_nul'] = 1;
		$row['valeur_victoire'] = 3;
		$row['valeur_nul'] = 1;
		$row['valeur_defaite'] = 0;
	}
	if ($row['type_sport'] == _TS_JORKYBALL_)
	{
		$row['gestion_nul'] = 0;
	}
	if ($row['type'] != _TYPE_TOURNOI_)
	{
		$display_poule_lettre = 0;
	}
}


$input  = "<select name=\"type_sport\">";
$input .= "<option value=\"1\" ".($row['type_sport'] == _TS_JORKYBALL_ ? "selected=\"selected\"" : "")."> ".$libelle_genre[_TS_JORKYBALL_]." </option>";
$input .= "<option value=\"2\" ".($row['type_sport'] == _TS_FUTSAL_    ? "selected=\"selected\"" : "")."> ".$libelle_genre[_TS_FUTSAL_]."    </option>";
$input .= "<option value=\"3\" ".($row['type_sport'] == _TS_FOOTBALL_  ? "selected=\"selected\"" : "")."> ".$libelle_genre[_TS_FOOTBALL_]."  </option>";
$input .= "<option value=\"0\" ".($row['type_sport'] == _TS_AUTRE_     ? "selected=\"selected\"" : "")."> ".$libelle_genre[_TS_AUTRE_]."     </option>";
$input .= "</select>";
$tab[] = array("Choix du type sport:", $input);

if ($inscription == 1)
	$tab[] = array("", "Le choix du type de sport est important, il permet de proposer des options de gestions adaptées au sport sélectionné.");

$tab[] = array("Nom du championnat:", "<input type=\"text\" name=\"ch_nom\" size=\"32\" maxlength=\"30\" value=\"".$row['championnat_nom']."\" ".($row['championnat_nom'] == "" ? "style='background-color: #FFCCCC'" : "")." onkeyup='javascript:changeColor(this);' />");
$tab[] = array("Description courte:",        "<input type=\"text\" name=\"ch_description\" size=\"32\" maxlength=\"64\" value=\"".$row['description']."\" />");

$tab[] = array("Type Gestion:", "<select name=\"type\" onchange=\"checkVisuStatJoueur(this.value);\"><option value=\""._TYPE_LIBRE_."\" ".($row['type'] == _TYPE_LIBRE_ ? "selected=\"selected\"" : "")."> ".$libelle_type[_TYPE_LIBRE_]." </option><option value=\""._TYPE_CHAMPIONNAT_."\" ".($row['type'] == _TYPE_CHAMPIONNAT_ ? "selected=\"selected\"" : "")."> ".$libelle_type[_TYPE_CHAMPIONNAT_]." </option><option value=\""._TYPE_TOURNOI_."\" ".($row['type'] == _TYPE_TOURNOI_ ? "selected=\"selected\"" : "")."> ".$libelle_type[_TYPE_TOURNOI_]." </option></select>");
$tab[] = array("", "<table border=\"0\" summary=\"\"><tr><td>Championnat:</td><td><span style=\"font-weight:normal;\">Si vous êtes organisés comme un véritable championnat, alors choisissez le mode Championnat</span></td></tr><tr><td>Tournoi:</td><td><span style=\"font-weight:normal;\">Si vous souhaitez organiser un tournoi avec des poules et une phase finale, alors choisissez le mode Tournoi</span></td></tr><tr><td>Libre:</td><td><span style=\"font-weight:normal;\">Si vous êtes entre amis, que les toutes équipes sont possibles et varient d'une journée à l'autre, alors choisissez le mode libre.</span></td></tr></table>");

$tab[] = array("Nom du gestionnaire:", "<table border=\"0\" width=\"100%\"><tr><td><input type=\"text\" name=\"ch_gestionnaire\" size=\"24\" maxlength=\"64\" value=\"".$row['gestionnaire']."\" ".($row['gestionnaire'] == "" ? "style='background-color: #FFCCCC'" : "")." onkeyup='javascript:changeColor(this);' /></td><td>Email : <input type=\"text\" name=\"ch_email\" size=\"24\" maxlength=\"64\" value=\"".$row['email']."\" ".($row['email'] == "" ? "style='background-color: #FFCCCC'" : "")." onkeyup='javascript:changeColor(this);' /></td></tr></table>");



$continent = "<select name=continent>";
reset($libelle_continent);
while(list($cle, $val) = each($libelle_continent))
	$continent .= "<option value=\"".$val."\" ".($row['type_lieu'] == _LIEU_CONTINENT_ && $row['lieu'] == $val ? "selected=\"selected\"" : "").">".$val."</option>";
$continent .= "</select>";

$pays = new pays();

$lib  = "<table summary=\"\" border=\"0\" width=\"100%\" class=\"soustab\"><tr>";
$lib .= "<td><input type=\"radio\" name=\"type_lieu\" onclick=\"javascript:changeVisiblility(0);\" value=\""._LIEU_VILLE_."\"     ".($row['type_lieu'] == _LIEU_VILLE_     ? "checked=\"checked\"" : "")." /> Ville </td>";
$lib .= "<td><input type=\"radio\" name=\"type_lieu\" onclick=\"javascript:changeVisiblility(1);\" value=\""._LIEU_PAYS_."\"      ".($row['type_lieu'] == _LIEU_PAYS_      ? "checked=\"checked\"" : "")." /> Pays  </td>";
$lib .= "<td><input type=\"radio\" name=\"type_lieu\" onclick=\"javascript:changeVisiblility(2);\" value=\""._LIEU_CONTINENT_."\" ".($row['type_lieu'] == _LIEU_CONTINENT_ ? "checked=\"checked\"" : "")." /> Continent </td>";
$lib .= "</tr><tr>";
$lib .= "<td id=\"id_ville\"><input type=\"text\" name=\"ville\" value=\"".($row['type_lieu'] == _LIEU_VILLE_ ? $row['lieu'] : "")."\" /></td>";
$lib .= "<td id=\"id_pays\">".$pays->getCombo2($row['type_lieu'] == _LIEU_PAYS_ ? $row['lieu'] : "")."</td>";
$lib .= "<td id=\"id_continent\">".$continent."</td>";
$lib .= "</tr></table>";
$tab[] = array("Lieu de pratique:",   $lib);

$lib  = "<table summary=\"\" border=\"0\" class=\"soustab\">";
$lib .= "<tr><td><input type=\"checkbox\" name=\"chk_news\"   ".($display_news == 1 ?         "checked=\"checked\"" : "")." /> News </td>                 <td><input type=\"checkbox\" name=\"chk_forum\" ".($display_forum == 1 ?        "checked=\"checked\"" : "")." /> Forum </td>              <td><input type=\"checkbox\" name=\"chk_fannys\" ".($display_fannys == 1 ?       "checked=\"checked\"" : "")." onclick=\"checkintegrite(this, document.forms[0].chk_matchs, 'L\'option Focus joueur et focus équipe sont en exclusion, on ne peut en choisir qu\'une.');\"/> Fannys </td></tr>";
$lib .= "<tr><td><input type=\"checkbox\" name=\"chk_prev\"   ".($display_last_journee == 1 ? "checked=\"checked\"" : "")." /> Dernières journées </td>   <td><input type=\"checkbox\" name=\"chk_next\"  ".($display_next_journee == 1 ? "checked=\"checked\"" : "")." /> Prochaines journées </td><td><input type=\"checkbox\" name=\"chk_focus\"  ".($display_focus_player == 1 ? "checked=\"checked\"" : "")." onclick=\"checkintegrite(this, document.forms[0].chk_team, 'L\'option Focus joueur et focus équipe sont en exclusion, on ne peut en choisir qu\'une.');\" /> Focus joueur </td></tr>";
$lib .= "<tr><td><input type=\"checkbox\" name=\"chk_matchs\" ".($display_last_matchs == 1 ?  "checked=\"checked\"" : "")." onclick=\"checkintegrite(this, document.forms[0].chk_fannys, 'L\'option fannys et derniers matchs joués sont en exclusion, on ne peut en choisir qu\'une.');\" /> Derniers matchs joués </td><td><input type=\"checkbox\" name=\"chk_team\"  ".($display_focus_team == 1 ?   "checked=\"checked\"" : "")." onclick=\"checkintegrite(this, document.forms[0].chk_focus, 'L\'option Focus joueur et focus équipe sont en exclusion, on ne peut en choisir qu\'une.');\" /> Focus équipe </td>       <td></td></tr>";
$lib .= "</table>";
$tab[] = array("Affichage blocs en page d'accueil:", $lib);



if ($inscription == 0)
	$tab[] = array("Date de création:",   $row['dt_creation']);

$tab[] = array("Mode de visualisation des journées:", "<select name=\"visu_journee\"><option value=\""._VISU_JOURNEE_CALENDRIER_."\" ".($row['visu_journee'] == _VISU_JOURNEE_CALENDRIER_ ? "selected=\"selected\"" : "")."> ".$libelle_visu_journee[_VISU_JOURNEE_CALENDRIER_]." </option><option value=\""._VISU_JOURNEE_LISTE_."\" ".($row['visu_journee'] == _VISU_JOURNEE_LISTE_ ? "selected=\"selected\"" : "")."> ".$libelle_visu_journee[_VISU_JOURNEE_LISTE_]." </option></select>");

$input0 = "<div style=\"padding: 0px 5px 0px 0px; float: left;\">Gestion des matchs nuls: <select name=\"gestion_nul\" onchange=\"javascript:changeNulDisplay(this[this.selectedIndex].value);\"><option value=\"0\" ".($row['gestion_nul'] == 0 ? "selected=\"selected\"" : "")."> Non </option><option value=\"1\" ".($row['gestion_nul'] == 1 ? "selected=\"selected\"" : "")."> Oui </option></select></div>";
$input1 = ""; $input2 = ""; $input3 = "";
for($i=0; $i < 10; $i++)
{
	$input1 .= "<option value=\"$i\" ".($i == $row['valeur_victoire'] ? "selected=\"selected\"" : "")."> $i </option>";
	$input2 .= "<option value=\"$i\" ".($i == $row['valeur_nul']      ? "selected=\"selected\"" : "")."> $i </option>";
	$input3 .= "<option value=\"$i\" ".($i == $row['valeur_defaite']  ? "selected=\"selected\"" : "")."> $i </option>";
}
$tab[] = array("Affection des points:", $input0."<div id=\"victoire\" style=\"padding: 0px 5px 0px 0px;\">Victoire = <select name=\"valeur_victoire\">".$input1."</select></div><div id=\"defaite\" style=\"padding: 0px 5px 0px 0px;\">Défaite = <select name=\"valeur_defaite\">".$input3."</select></div><div id=\"nul\" style=\"padding: 0px 5px 0px 0px; ".($row['gestion_nul'] == 0 ? "visibility: hidden;" : "")."\">Nul = <select name=\"valeur_nul\">".$input2."</select></div>");

$input1 = "<div style=\"padding: 5px; float: left;\">Gestion des fannys: <select name=\"gestion_fanny\"><option value=\"0\" ".($row['gestion_fanny'] == 0 ? "selected=\"selected\"" : "")."> Non </option><option value=\"1\" ".($row['gestion_fanny'] == 1 ? "selected=\"selected\"" : "")."> Oui </option></select></div>";
$input2 = "<div style=\"padding: 5px; float: left;\">Gestion des sets: <select name=\"gestion_sets\"><option value=\"0\" ".($row['gestion_sets'] == 0 ? "selected=\"selected\"" : "")."> Non </option><option value=\"1\" ".($row['gestion_sets'] == 1 ? "selected=\"selected\"" : "")."> Oui </option></select></div>";
$tab[] = array("Options matchs: ", $input1.$input2);

$input = "<div style=\"padding: 5px; float: left;\"><select name=\"tri_classement_general\"><option value=\"0\" ".($row['tri_classement_general'] == 0 ? "selected=\"selected\"" : "")."> Non opérationel </option></select></div>";
$tab[] = array("Tri classement général: ", $input);

$lib  = "<table summary=\"\" border=\"0\" class=\"soustab\">";
$lib .= "<tr><td><input type=\"checkbox\" name=\"chk_clt_joueurs\"  ".($display_clt_joueurs == 1 ?  "checked=\"checked\"" : "")." /></td><td>Accès classement joueurs (pas utile pour les championnats)</td></tr>";
$lib .= "<tr><td><input type=\"checkbox\" name=\"chk_poule_lettre\" ".($display_poule_lettre == 1 ? "checked=\"checked\"" : "")." /></td><td>Lettre pour nommage des poules (poule A au lieu de poule 1, ...)</td></tr>";
$lib .= "<tr><td><input type=\"checkbox\" name=\"chk_all_matchs\"   ".($display_all_matchs == 1 ?   "checked=\"checked\"" : "")." /></td><td>Affichage de tous les matchs de poules par défaut (tournois)</td></tr>";
$lib .= "<tr><td><input type=\"checkbox\" name=\"chk_gavgp\"        ".($display_gavgp == 1 ?        "checked=\"checked\"" : "")." /></td><td>Goal average particulier (pour les tournois, en cas d'égalité de points on privilégie d'abord le goal avg particulier)</td></tr>";
$lib .= "</table>";
$tab[] = array("Divers:", $lib);

if ($inscription == 1)
	$tab[] = array("Nom première saison:", "<input type=\"text\" name=\"ch_nom_saison\" size=\"32\" maxlength=\"32\" value=\"".$row['nom_saison']."\" ".($row['nom_saison'] == "" ? "style='background-color: #FFCCCC'" : "")." onkeyup='javascript:changeColor(this);' />");



echo "<input type=\"hidden\" name=\"options\"         value=\"".$row['options']."\" />";



$news = $row['news'] == "" ? "&nbsp;" : $row['news'];
$tab[] = array("Edito de page d'accueil:", "<textarea id=\"ta\" name=\"ta\" cols=\"60\" rows=\"10\">".$news."</textarea>");

$lib  = "<table summary=\"\" border=\"0\">";
$lib .= "<tr><td><select name=\"ch_friends\" size=\"4\" multiple=\"multiple\" onchange=\"javascript:SBox_TestSelection(this);\">";
$lib .= "</select></td>";
$lib .= "<td><table summary=\"\" border=\"0\">";
$lib .= "<tr><td><input type=\"submit\" value=\"Ajouter\" name=bt_ajouter onclick=\"javascript:window.open('championnat_select.php?friends=1', 'championnat_aide', 'width=550, height=450, screenX=100, screenY=100, pageXOffset=100, pageYOffset=100, alwaysRaised=yes, toolbar=no, location=no, personnalBar=no, status=no, menuBar=no, scrollbars=yes, resizable=yes'); return false;\" /></td></tr>";
$lib .= "<tr><td><input type=\"submit\" value=\"Retirer\" name=bt_retirer onclick=\"javascript:return delete_selected_items();\" /></td></tr>";
$lib .= "</table></td></tr>";
$lib .= "</table>";
$tab[] = array("Championnats amis:", $lib);



if ($mode_modification)
{
	$tab[] = array("login:",        "<input type=\"text\"     name=\"ch_login\" size=\"32\" maxlength=\"16\" value=\"".$row['login']."\" ".($row['login'] == "" ? "style='background-color: #FFCCCC'" : "")." onkeyup='javascript:changeColor(this);' />");
	$tab[] = array("Mot de passe:", "<table border=\"0\" width=\"100%\"><tr><td><input type=\"password\" name=\"ch_pwd\"   size=\"16\" maxlength=\"16\" value=\"".$row['pwd']."\"   ".($row['pwd']   == "" ? "style='background-color: #FFCCCC'" : "")." onkeyup='javascript:changeColor(this);' /></td><td>Confirmation: <input type=\"password\" name=\"ch_pwd2\"  size=\"16\" maxlength=\"16\" value=\"".$row['pwd']."\"   ".($row['pwd']   == "" ? "style='background-color: #FFCCCC'" : "")." onkeyup='javascript:changeColor(this);' /></td></tr></table>");
}

if ($inscription ==1)
{
	$tab[] = array("Zone de contrôle:<br /><span style=\"font-weight: normal;\">[Reportez le code de l'image dans le champ de saisie]</span>", "<table border=0><tr valign=\"center\"><td><input type=\"text\" name=\"ch_controle\" size=\"32\" maxlength=\"16\" value=\"".$row['controle']."\" style=\"background-color: #FFCCCC;\" onkeyup='javascript:changeColor(this);' /></td><td><img src=\"../include/codeimage.php\" /></td></tr></table>");
}

if ($demo) $tab[] = array("ATTENTION", "<span style=\"font-weight:bold;color: red;\">En mode démonstration ces données ne sont pas modifiables.</span>");
$tab[] = array("Avertissements", "<span style=\"font-weight:normal;\">Jorkers.com ne pourrait en aucun cas être tenu pour responsable des pannes ou des dysfonctionnements qui pourraient survenir suite à l'utilisation de ce site. Jorkers.com se donne le droit de pouvoir supprimer n'importe quel championnat sans avoir à fournir d'explication.</span>");




echo "<tr><td>";
$fxlist = new FXListPresentation($tab);
$fxlist->FXSetTitle($inscription == 1 ? "Formulaire inscription" : "Informations Championnat", "center");
$fxlist->FXSetColumnsAlign(array("right", "left"));
$fxlist->FXSetColumnsColor(array("#BCC5EA", ""));
$fxlist->FXSetColumnsWidth(array("25%", ""));
$fxlist->FXDisplay();
echo "</td></tr>";

if ($mode_modification) { ?>
<tr><td align="right">
<input type="submit" value="Valider" onclick="return checkForm(1);" />
</td></tr>
<? } ?>

</table>
</div>

</form>


<script type="text/javascript">
// <![CDATA[
function changeNulDisplay(val)
{
	if (val == 0)
		document.getElementById('nul').style.visibility='hidden';
	else
		document.getElementById('nul').style.visibility='visible';
}

function delete_selected_items()
{
	SBox_Del_SelectedItems(document.forms[0].ch_friends);
	return false;
}

<? if ($inscription == 0) { ?>
SBox_Ajout_Item(document.forms[0].ch_friends,  '______________________________________', 0, false);
<? } ?>

<?
if ($row['friends'] != "")
{
	$select = "SELECT * FROM jb_championnat WHERE id IN (".$row['friends'].") ORDER BY nom";
	$res = dbc::execSQL($select);
	while($champ = mysql_fetch_array($res))
	{
?>
		SBox_Ajout_Item(document.forms[0].ch_friends,  '<?= $champ['nom'] ?>', <?= $champ['id'] ?>, false);
<?
	}
}
?>

<? if ($inscription == 0) { ?>
<? if ($row['type_lieu'] == _LIEU_VILLE_) { ?>
changeVisiblility(0);
<? } ?>
<? if ($row['type_lieu'] == _LIEU_PAYS_) { ?>
changeVisiblility(1);
<? } ?>
<? if ($row['type_lieu'] == _LIEU_CONTINENT_) { ?>
changeVisiblility(2);
<? } ?>
<? } ?>

function checkintegrite(obj1, obj2, message)
{
	if (obj1.checked == true && obj2.checked == true)
	{
		obj2.checked = false;
		alert(message);
	}
}

function checkVisuStatJoueur(val)
{
	if (val == 0)
		document.forms[0].chk_clt_joueurs.checked=true;
	else
		document.forms[0].chk_clt_joueurs.checked=false;
}
function changeVisiblility(val)
{
	if (val == 0)
	{
		document.getElementById('id_ville').style.visibility='visible';
		document.getElementById('id_pays').style.visibility='hidden';
		document.getElementById('id_continent').style.visibility='hidden';
	}
	if (val == 1)
	{
		document.getElementById('id_ville').style.visibility='hidden';
		document.getElementById('id_pays').style.visibility='visible';
		document.getElementById('id_continent').style.visibility='hidden';
	}
	if (val == 2)
	{
		document.getElementById('id_pays').style.visibility='hidden';
		document.getElementById('id_ville').style.visibility='hidden';
		document.getElementById('id_continent').style.visibility='visible';
	}
}
function checkForm(sens)
{
		if (verif_alphanumext(document.forms[0].ch_nom.value, 'Nom du championnat', 6) == false)
			return false;
		if (verif_alphanumext(document.forms[0].ch_gestionnaire.value, 'Gestionnaire', -1) == false)
			return false;
		if (verif_alphanumext(document.forms[0].ch_email.value, 'Email', -1) == false)
			return false;
		if (document.forms[0].ch_email.value != '')
		{
			if (!verif_EMAIL(document.forms[0].ch_email.value))
				return false;
		}

		if (document.forms[0].type_lieu[0].checked)
			document.forms[0].lieu_pratique.value = document.forms[0].ville.value;

		if (document.forms[0].type_lieu[1].checked)
			document.forms[0].lieu_pratique.value = document.forms[0].mon_pays.value;

		if (document.forms[0].type_lieu[2].checked)
			document.forms[0].lieu_pratique.value = document.forms[0].continent.value;

		options  = "";
		options += document.forms[0].chk_news.checked ? "1|" : "0|";
		options += document.forms[0].chk_forum.checked ? "1|" : "0|";
		options += document.forms[0].chk_fannys.checked ? "1|" : "0|";
		options += document.forms[0].chk_prev.checked ? "1|" : "0|";
		options += document.forms[0].chk_next.checked ? "1|" : "0|";
		options += document.forms[0].chk_focus.checked ? "1|" : "0|";
		options += document.forms[0].chk_clt_joueurs.checked ? "1|" : "0|";
		options += document.forms[0].chk_poule_lettre.checked ? "1|" : "0|";
		options += document.forms[0].chk_all_matchs.checked ? "1|" : "0|";
		options += document.forms[0].chk_matchs.checked ? "1|" : "0|";
		options += document.forms[0].chk_team.checked ? "1|" : "0|";
		options += document.forms[0].chk_gavgp.checked ? "1" : "0";
		document.forms[0].options.value = options;

<? if ($inscription == 1) { ?>
		if (verif_alphanumext(document.forms[0].ch_nom_saison.value, 'Nom première saison', 6) == false)
			return false;
<? } ?>



		document.forms[0].selected_friends.value = '';
		sboxS=document.forms[0].ch_friends;
		nb_sel=sboxS.length;
	    for(i=1; i < nb_sel; i++)
	    {
	         txt=sboxS.options[i].text;
	         val=sboxS.options[i].value;

	         document.forms[0].selected_friends.value += (document.forms[0].selected_friends.value == '' ? '' : ',')+val;
	    }



		if (verif_alphanum2(document.forms[0].ch_login.value, 'Login', 6) == false)
			return false;
		if (verif_alphanum2(document.forms[0].ch_pwd.value, 'Mot de passe', 6) == false)
			return false;
		if (verif_alphanum2(document.forms[0].ch_pwd2.value, 'Confirmation', 6) == false)
			return false;
		if (document.forms[0].ch_pwd2.value != document.forms[0].ch_pwd.value)
		{
			alert('La confirmation du mot de passe incorrecte');
			return false;
		}


	// Si précédent action, alors il faut rectifier la valeur de 'etape'
	if (sens == 0)
	{
		document.forms[0].action='championnat_details.php';
	}

    return true;
}

function disabledObj(obj)
{
	obj.disabled=true;
	obj.style.color='black';
	obj.style.background='#EFEFEF';
}

<? if (!$mode_modification) { ?>
	disabledObj(document.forms[0].ch_nom);
	disabledObj(document.forms[0].ch_description);
	disabledObj(document.forms[0].type);
	disabledObj(document.forms[0].ch_gestionnaire);
	disabledObj(document.forms[0].ch_email);
	disabledObj(document.forms[0].ta);
	disabledObj(document.forms[0].chk_news);
	disabledObj(document.forms[0].chk_prev);
	disabledObj(document.forms[0].chk_matchs);
	disabledObj(document.forms[0].visu_journee);
	disabledObj(document.forms[0].gestion_nul);
	disabledObj(document.forms[0].valeur_victoire);
	<? if ($row['gestion_nul'] == 1) { ?> disabledObj(document.forms[0].valeur_nul); <? } ?>
	disabledObj(document.forms[0].valeur_defaite);
	disabledObj(document.forms[0].gestion_fanny);
	disabledObj(document.forms[0].gestion_sets);
	disabledObj(document.forms[0].tri_classement_general);
	disabledObj(document.forms[0].type_sport);
	disabledObj(document.forms[0].chk_clt_joueurs);
	disabledObj(document.forms[0].chk_poule_lettre);
	disabledObj(document.forms[0].chk_all_matchs);
	disabledObj(document.forms[0].ch_friends);
	disabledObj(document.forms[0].bt_ajouter);
	disabledObj(document.forms[0].bt_retirer);
	disabledObj(document.forms[0].chk_fannys);
	disabledObj(document.forms[0].chk_focus);
	disabledObj(document.forms[0].chk_team);
	disabledObj(document.forms[0].chk_gavgp);
	disabledObj(document.forms[0].chk_prev);
	disabledObj(document.forms[0].chk_next);
	disabledObj(document.forms[0].chk_forum);
<? } ?>

// ]]>
</script>


<? $menu->end(); ?>
