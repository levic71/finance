<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$idl = isset($_REQUEST['idl']) && is_numeric($_REQUEST['idl']) && $_REQUEST['idl'] > 0 ? $_REQUEST['idl'] : 0;
$modifier = $idl > 0 ? true : false;

if ($modifier && !$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

$etape      = Wrapper::getRequest('etape', 0);
$choix_type = Wrapper::getRequest('choix_type', _TYPE_CHAMPIONNAT_);

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

if ($etape == 0 && !$sess_context->isUserConnected()) {

	$items = array();
	array_push($items, array("func" => "label_form", "id" => "warning", "icon" => "warning", "libelle" => "Pour créer un nouveau championnat vous devez vous authentifier ou vous inscrire.", "nb_col" => 11, "checked" => 1));
	$actions = array(0 => array("onclick" => "mm({action: 'leagues'});", "libelle" => "Annuler", "color" => "mdl-color-text--grey-600"), 1 => array("onclick" => "mm({action: 'login'});", "libelle" => "Continuer"));
	Wrapper::build_form(array("nb_col" => 8, "title" => "Nouvelle Compétition", "items" => $items, "actions" => $actions));
	exit(0);
}

$nb_fonts = 25;

if ($etape == 0) {
	
	$items = array();
	$tmp1 = '<div class="mdl-cell mdl-cell--4-col"> '.
				'<i class="material-icons mdl-icon-toggle">list</i><br /><span>Championnat classique</span>'.
				'<hr />'.
				'<p>Vous êtes organisés comme un véritable championnat avec un nombre d\'équipes fini qui se rencontrent les unes contre les autres tout au long d\'une saison sur plusieurs journées.</p>'.
				'<hr />'.
				'<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" onclick="xx({action:\'leagues\', id:\'main\', url:\'edit_leagues.php?etape=1&choix_type=1\'});">Créer</button>'.
			'</div>';
	$tmp2 = '<div class="mdl-cell mdl-cell--4-col"> '.
				'<i class="material-icons mdl-icon-toggle">change_history</i><br /><span>Tournoi</span>'.
				'<hr />'.
				'<p>Vous organisez un tournoi dans lequel les équipes vont se rencontrer d\'abord en poule, les meilleurs disputeront la phase finale en élimination directe pour déterminer le vainqueur.</p>'.
				'<hr />'.
				'<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" onclick="xx({action:\'leagues\', id:\'main\', url:\'edit_leagues.php?etape=1&choix_type=2\'});">Créer</button>'.
			'</div>';
	$tmp3 = '<div class="mdl-cell mdl-cell--4-col"> '.
			'<i class="material-icons mdl-icon-toggle">star_border</i><br /><span>Championnat libre</span>'.
			'<hr />'.
			'<p>Vous jouez entre amis sans vraiment de contraintes, toutes les équipes sont possibles et varient au fil des journées, chaque joueur est de mesurer ces statistiques de progression.</p>'.
			'<hr />'.
			'<button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" onclick="xx({action:\'leagues\', id:\'main\', url:\'edit_leagues.php?etape=1&choix_type=0\'});">Créer</button>'.
		'</div>';

	$tmp4 = '<div class="mdl-grid">'.$tmp1.$tmp2.$tmp3.'</div>';
	array_push($items, array("func" => "label_form", "id" => "new-league", "class" => "no-left-margin", "libelle" => $tmp4, "nb_col" => 12));
	$actions = array(0 => array("onclick" => "mm({action: 'leagues'});", "libelle" => "Annuler", "color" => "mdl-color-text--grey-600"), 1 => array("onclick" => "mm({action: 'login'});", "libelle" => "Continuer"));
	Wrapper::build_form(array("nb_col" => 12, "title" => "Choix du type de Compétition", "items" => $items));
?>
	<div class="mdl-layout-spacer"></div>
	<div class="mdl-card__supporting-text mdl-cell mdl-cell--12-col">
		<div style="text-align: center !important; font-style: italic; font: normal 11px/12px 'lucida sans', 'trebuchet MS', 'Tahoma';">Jorkers.com ne pourrait en aucun cas être tenu pour responsable des pannes ou des dysfonctionnements <br />qui pourraient survenir suite à l'utilisation de ce site. Jorkers.com se donne le droit de pouvoir<br /> supprimer n'importe quel championnat sans avoir à fournir d'explication</div>
	</div>

	<? exit(0);
}



$db = dbc::connect();
unset($_SESSION['antispam']);
$_SESSION['antispam'] = ToolBox::getRand(5);

if ($modifier) {
	$scs = new SQLChampionnatsServices($sess_context->getRealChampionnatId());
	$row = $scs->getChampionnat();

	$row['options'] = $row['options'] != "" ? $row['options'] : "1|1|1|1|1|1|1|0|0|0|0|0";
	$row['nom_saison'] = $row['saison_nom'];
	$demo = $row['demo'] == 1;
	$row['photo'] = $row['logo_photo'];
	$row['headcount'] = $row['home_list_headcount'];
	$row['gestion_twitter'] = $row['twitter'] != "" ? 1 : 0;
	$row['controle'] = $_SESSION['antispam'];
}
else
{
	$row = array();
	$row['championnat_nom'] = isset($ch_nom) ? str_replace('\\\'', '\'', $ch_nom) : "";
	$row['description']     = isset($ch_description) ? str_replace('\\\'', '\'', $ch_description) : "";
	$row['gestionnaire']    = isset($ch_gestionnaire) ? str_replace('\\\'', '\'', $ch_gestionnaire) : "";
	$row['email']           = isset($ch_email) ? $ch_email : "";
	$row['type']            = isset($type) ? $type : $choix_type;
	$row['options']         = isset($options) ? $options  : "1|1|1|1|1|1|1|0|0|0|0|0";
	$row['login']           = isset($ch_login) ? str_replace('\\\'', '\'', $ch_login) : "";
	$row['pwd']             = isset($ch_pwd) ? $ch_pwd : "";
	$row['pwd2']            = isset($ch_pwd2) ? $ch_pwd2 : "";
	$row['news']            = isset($ta) ? str_replace('\\\'', '\'', $ta) : "";
	$row['dt_creation']     = "";
	$row['type_lieu']       = isset($type_lieu) ? $type_lieu : _LIEU_VILLE_;
	$row['lieu']            = isset($lieu_pratique) ? str_replace('\\\'', '\'', $lieu_pratique) : "";
	$row['visu_journee']    = isset($visu_journee) ? $visu_journee : 0;
	$row['valeur_victoire'] = isset($valeur_victoire) ? $valeur_victoire : 3;
	$row['valeur_nul']      = isset($valeur_nul) ? $valeur_nul : 1;
	$row['valeur_defaite']  = isset($valeur_defaite) ? $valeur_defaite : 1;
	$row['type_gestionnaire'] = isset($type_gestionnaire) ? $type_gestionnaire : 0;
	$row['gestion_buteurs'] = isset($gestion_buteurs) ? $gestion_buteurs : 0;
	$row['gestion_nul']     = isset($gestion_nul) ? $gestion_nul : 1;
	$row['nom_saison']      = isset($ch_nom_saison) ? str_replace('\\\'', '\'', $ch_nom_saison) : "Saison ".date("Y")."-".(date("Y")+1);
	$row['friends']         = isset($selected_friends) ? $selected_friends : "";
	$demo                   = false;
	$row['twitter']         = isset($twitter) ? $twitter : "";
	$row['gestion_twitter'] = isset($gestion_twitter) ? $gestion_twitter : 0;
	$row['gestion_fanny']   = isset($gestion_fanny) ? $gestion_fanny : 0;
	$row['gestion_sets']    = isset($gestion_sets)  ? $gestion_sets  : 1;
	$row['tri_classement_general'] = isset($tri_classement_general) ? $tri_classement_general : 1;
	$row['type_sport']      = isset($type_sport) ? $type_sport : 1;
	$row['controle']        = isset($ch_controle) ? $ch_controle : "";
	$row['lat']             = isset($lat) ? $lat : '';
	$row['lng']             = isset($lng) ? $lng : '';
	$row['zoom']            = isset($zoom) ? $zoom : '10';
	$row['theme']           = isset($theme) ? $theme : _THEME_CLASSIQUE_;
	$row['logo_font']       = isset($logo_font) ? $logo_font : 8;
	$row['headcount']       = isset($headcount) ? $headcount : 7;
	$row['forfait_penalite_bonus'] = isset($forfaitpenalitebonus) ? $forfaitpenalitebonus : 5;
	$row['forfait_penalite_malus'] = isset($forfaitpenalitemalus) ? $forfaitpenalitemalus : 5;

	if ($sess_context->isSuperUser()) {
		$row['championnat_nom'] = "toto:".$_SESSION['antispam'];
		$row['gestionnaire']    = "toto";
		$row['email']           = "toto@toto.com";
		$row['login']           = "vicmju";
		$row['pwd']             = "vicmju";
		$row['pwd2']            = "vicmju";
		$row['lieu']            = "Houilles";
		$row['controle']        = $_SESSION['antispam'];
	}

}
if (!isset($row['photo']) || $row['photo'] == "") $row['photo'] = 'img/soccer-larger.jpg';

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

$mode_modification = $modifier && !$demo;

?>

<input type="hidden" id="options" name="options" value="<?= $row['options'] ?>" />
<input type="hidden" id="visu_journee" name="visu_journee" value="<?= $row['visu_journee'] ?>" />
<input type="hidden" id="tri_classement_general" name="tri_classement_general" value="<?= $row['tri_classement_general'] ?>" />
<input type="hidden" id="ch_friends" name="ch_friends" value="" />
<input type="hidden" id="selected_friends" name="selected_friends" value="<?= $row['friends'] ?>" />
<input type="hidden" id="lat" name="lat" value="<?= $row['lat'] ?>" />
<input type="hidden" id="lng" name="lng" value="<?= $row['lng'] ?>" />
<input type="hidden" id="zoom" name="zoom" value="<?= $row['zoom'] ?>" />

<?
	$title = $modifier ? "Paramétres du championnat" : "Créer un nouveau championnat";

	$items = array();
	array_push($items, array("func" => "choice_component_form", "id" => "type_sport", "icon" => "blur_on", "libelle" => "Sport", "nb_col" => 6));
	array_push($items, array("func" => "choice_component_form", "id" => "type", "icon" => "style", "libelle" => "Type compétition", "nb_col" => 6));
	array_push($items, array("func" => "textfield_form",        "id" => "ch_nom", "value" => $row['championnat_nom'], "icon" => "text_fields", "libelle" => "Nom championnat", "nb_col" => 12, "required" => 1));
	array_push($items, array("func" => "textarea_form",         "id" => "ch_description", "value" => $row['description'], "icon" => "subject", "libelle" => "Description", "nb_col" => 12));
	if (!$modifier) array_push($items, array("func" => "textfield_form", "id" => "ch_nom_saison", "value" => $row['nom_saison'], "icon" => "text_fields", "libelle" => "Saison", "nb_col" => 12, "required" => 1));
	array_push($items, array("func" => "choice_component_form", "id" => "type_lieu", "icon" => "settings_ethernet", "libelle" => "Envergure", "nb_col" => 6, "grouped" => 1));
	array_push($items, array("func" => "textfield_place_form",  "id" => "lieu_pratique", "value" => $row['lieu'], "icon" => "place", "libelle" => "Adresse, code postal, ville, état, pays", "nb_col" => 6));
	array_push($items, array("func" => "choice_component_form", "id" => "gestion_buteurs", "icon" => "perm_identity", "libelle" => "Gestion des buteurs", "nb_col" => 6, "grouped" => 1));
	array_push($items, array("func" => "choice_component_form", "id" => "type_gestionnaire", "icon" => "supervisor_account", "libelle" => "Type gestionnaire", "nb_col" => 6, "grouped" => 1));
	array_push($items, array("func" => "choice_component_form", "id" => "gestion_nul", "icon" => "account_balance", "libelle" => "Gestion des matchs nuls", "nb_col" => 4, "grouped" => 1));
	array_push($items, array("func" => "choice_component_form", "id" => "gestion_sets", "icon" => "filter_b_and_w", "libelle" => "Gestion des sets", "nb_col" => 4, "grouped" => 1));
	array_push($items, array("func" => "choice_component_form", "id" => "gestion_fanny", "icon" => "filter_tilt_shift", "libelle" => "Gestion des fannys", "nb_col" => 4, "grouped" => 1));
	array_push($items, array("func" => "number_component_form", "id" => "valeur-victoire-zip", "icon" => "add_circle_outline", "libelle" => "Points victoire", "nb_col" => 4, "value" => $row['valeur_victoire'], "start" => 0, "end" => 100));
	array_push($items, array("func" => "number_component_form", "id" => "valeur-nul-zip", "icon" => "highlight_off", "libelle" => "Points nul", "nb_col" => 4, "value" => $row['valeur_nul'], "start" => 0, "end" => 100));
	array_push($items, array("func" => "number_component_form", "id" => "valeur-defaite-zip", "icon" => "remove_circle_outline", "libelle" => "Points défaite", "nb_col" => 4, "value" => $row['valeur_defaite'], "start" => 0, "end" => 100));
	array_push($items, array("func" => "number_component_form", "id" => "headcount", "icon" => "view_list", "libelle" => "Nb items dashboard", "value" => $row['headcount'], "nb_col" => 4, "start" => 0, "end" => 100));
	array_push($items, array("func" => "number_component_form", "id" => "forfaitpenalitebonus", "icon" => "add_circle_outline", "libelle" => "Bonus goal average en cas de forfait", "nb_col" => 4, "value" => $row['forfait_penalite_bonus'], "start" => 0, "end" => 100));
	array_push($items, array("func" => "number_component_form", "id" => "forfaitpenalitemalus", "icon" => "remove_circle_outline", "libelle" => "Malus goal average en cas de forfait", "nb_col" => 4, "value" => $row['forfait_penalite_malus'], "start" => 0, "end" => 100));
	array_push($items, array("func" => "choice_component_form", "id" => "theme", "icon" => "landscape", "libelle" => "Thème", "nb_col" => 6));
	array_push($items, array("func" => "choice_component_form", "id" => "logo_font", "icon" => "font_download", "libelle" => "Font entête", "nb_col" => 6));
	array_push($items, array("func" => "upload_image_form",     "id" => "photo", "value" => $row['photo'], "icon" => "photo_camera", "libelle" => "Logo", "nb_col" => 6));
//	array_push($items, array("func" => "choice_component_form", "id" => "gestion_twitter", "icon" => "blur_on", "libelle" => "Twitter", "nb_col" => 6, "grouped" => 1));
//	array_push($items, array("func" => "textfield_form",        "id" => "twitter", "value" => $row['twitter'], "icon" => "text_fields", "libelle" => "Nom du compte twitter", "nb_col" => 6));
	if (!$modifier) array_push($items, array("func" => "captcha_form", "id" => "ch_controle", "icon" => "text_fields", "libelle" => "Je ne suis pas un robot", "nb_col" => 6));

	$actions = array(0 => array("onclick" => "return annuler();"), 1 => array("onclick" => "return validate_and_submit();"));

	Wrapper::build_form(array('title' => $title, 'items' => $items, 'actions' =>$actions));
?>

<div class="mdl-cell mdl-cell--12-col mdl-cell--middle" style="text-align: center;">
	<small><a href="http://www.cnil.fr/en-savoir-plus/deliberations/deliberation/delib/106/" target="_blank">CNIL - Dispense n° 8 - Délibération n° 2010-229</a></small>
</div>

<script>

<? $sports = ""; reset($libelle_genre); foreach($libelle_genre as $cle => $val) { $sports .= ($sports == "" ? "" : ",")."{ v: '".$cle."', l: '<img src=\"img/sports/".$icon_genre[$cle]."\" />".Wrapper::stringEncode4JS($val)."', s: ".($cle == $row['type_sport'] ? "true" : "false")." }"; } ?>
choices.build({ name: 'type_sport', c1: 'blue', singlepicking: true, removable: true, values: [<?= $sports ?>] });

<? $type = ""; reset($libelle_type); foreach($libelle_type as $cle => $val) { $type .= ($type == "" ? "" : ",")."{ v: '".$cle."', l: '".Wrapper::stringEncode4JS($val)."', s: ".($cle == $row['type'] ? "true" : "false")." }"; } ?>
choices.build({ name: 'type', c1: 'blue', singlepicking: true, removable: true, values: [<?= $type ?>] });

<? $type = ""; reset($libelle_typelieu); foreach($libelle_typelieu as $cle => $val) {  $type .= ($type == "" ? "" : ",")."{ v: '".$cle."', l: '".Wrapper::stringEncode4JS($val)."', s: ".($cle == $row['type_lieu'] ? "true" : "false")." }"; } ?>
choices.build({ name: 'type_lieu', c1: 'blue', c2: 'white', values: [<?= $type ?>] });

choices.build({ name: 'type_gestionnaire', c1: 'blue', c2: 'white', values: [ { v: 0, l: 'Particulier', s: <?= $row['type_gestionnaire'] == 0 ? "true" : "false" ?> }, { v: 1, l: 'Gérant club', s: <?= $row['type_gestionnaire'] == 1 ? "true" : "false" ?> }] });
choices.build({ name: 'gestion_buteurs', c1: 'blue', c2: 'white', values: [ { v: 0, l: 'Non', s: <?= $row['gestion_buteurs'] == 0 ? "true" : "false" ?> }, { v: 1, l: 'Oui', s: <?= $row['gestion_buteurs'] == 1 ? "true" : "false" ?> }] });
choices.build({ name: 'gestion_nul', c1: 'blue', c2: 'white', callback: 'cb_nul_input', values: [ { v: 0, l: 'Non', s: <?= $row['gestion_nul'] == 0 ? "true" : "false" ?> }, { v: 1, l: 'Oui', s: <?= $row['gestion_nul'] == 1 ? "true" : "false" ?> }] });
choices.build({ name: 'gestion_sets', c1: 'blue', c2: 'white', values: [{ v: 0, l: 'Non', s: <?= $row['gestion_sets'] == 0 ? "true" : "false" ?> }, { v: 1, l: 'Oui', s: <?= $row['gestion_sets'] == 1 ? "true" : "false" ?> }] });
choices.build({ name: 'gestion_fanny', c1: 'blue', c2: 'white', values: [{ v: 0, l: 'Non', s: <?= $row['gestion_fanny'] == 0 ? "true" : "false" ?> }, { v: 1, l: 'Oui', s: <?= $row['gestion_fanny'] == 1 ? "true" : "false" ?> }] });
choices.build({ name: 'gestion_twitter', c1: 'blue', c2: 'white', callback: 'cb_twitter_input', values: [{ v: 0, l: 'Non', s: <?= $row['gestion_twitter'] == 0 ? "true" : "false" ?> }, { v: 1, l: 'Oui', s: <?= $row['gestion_twitter'] == 1 ? "true" : "false" ?> }] });
choices.build({ name: 'logo_font', c1: 'blue', c2: 'white', singlepicking: true, removable: true, values: [
<? for($i = 1; $i <= $nb_fonts; $i++) { ?>
	<?= $i > 1 ? "," : "" ?> { v: <?= $i ?>, l: 'Sample', s: <?= $row['logo_font'] == $i ? "true" : "false" ?> }
<? } ?>
] });

for(i=1; i <= <?= $nb_fonts ?>; i++) {
	var fileref=document.createElement("link");
	fileref.setAttribute("rel", "stylesheet");
	fileref.setAttribute("type", "text/css");
	fileref.setAttribute("href", "css/fonts/font"+i+".css");
	document.getElementsByTagName("head")[0].appendChild(fileref);
}

logo_font_choice = document.getElementById('logo_font');
buttons = logo_font_choice.getElementsByTagName('button');
for(var i=0; i < buttons.length; i++) { buttons[i].style.fontFamily = "logo"+(i+1); buttons[i].style.fontSize = "18px"; buttons[i].style.lineHeight = "18px"; buttons[i].style.height = "30px"; }

<? $themes = ""; foreach($libelle_theme as $cle => $val) { $themes .= ($themes == "" ? "" : ",")."{ v: '".$cle."', l: '".$val."', s: ".($cle == $row['theme'] ? "true" : "false")." }";  } ?>
choices.build({ name: 'theme', c1: 'blue', c2: 'white', singlepicking: true, removable: true, values: [ <?= $themes ?> ] });

<? if ($row['gestion_nul'] == 0) { ?>hide('valeur-nul-zip-box');<? } else { ?>show('valeur-nul-zip-box');<? } ?>
<? if ($row['gestion_twitter'] == 0) { ?>hide('twitter-box');<? } else { ?>show('twitter-box');<? } ?>

cb_nul_input = function(name) {
	if (choices.getSelection(name) == 0)
		hide('valeur-nul-zip-box');
	else
		show('valeur-nul-zip-box')
}

cb_twitter_input = function(name) {
	if (choices.getSelection(name) == 0) {
		hide('twitter-box');
		el('twitter').value = '';
	}
	else
		show('twitter-box');
}

validate_and_submit = function()
{
    if (!check_alphanumext(valof('ch_nom'), 'Nom', -1))
		return false;
    if (false && !check_alphanumext(valof('ch_gestionnaire'), 'Gestionnaire', -1))
		return false;
    if (false && !check_alphanumext(valof('ch_email'), 'Email', -1))
		return false;
	if (false && !check_email(valof('ch_email')))
		return false;
    if (false && !check_alphanumext(valof('ch_login'), 'Login', 6))
		return false;
    if (false && !check_alphanumext(valof('ch_pwd'), 'Mot de passe', 6))
		return false;
    if (false && !check_alphanumext(valof('ch_pwd2'), 'Confirmation', 6))
		return false;
<? if (!$modifier) { ?>
	if (!check_slide_value(slider))
		return false;
<? } ?>
	if (false && valof('ch_pwd2') != valof('ch_pwd'))
	{
		alert('La confirmation du mot de passe est incorrecte');
		return false;
	}

	var valeur_victoire_zip = numbers.getValue('valeur-victoire-zip');
	var valeur_nul_zip = numbers.getValue('valeur-nul-zip');
	var valeur_defaite_zip = numbers.getValue('valeur-defaite-zip');
	var headcount = numbers.getValue('headcount');
	var forfait_penalite_bonus = numbers.getValue('forfaitpenalitebonus');
	var forfait_penalite_malus = numbers.getValue('forfaitpenalitemalus');

	params = '?ch_controle=<?= $row['controle'] ?>&forfait_penalite_malus='+forfait_penalite_malus+'&forfait_penalite_bonus='+forfait_penalite_bonus+'&headcount='+headcount+'&valeur_victoire_zip='+valeur_victoire_zip+'&valeur_nul_zip='+valeur_nul_zip+'&valeur_defaite_zip='+valeur_defaite_zip+'&theme='+choices.getSelection('theme')+'&logo_font='+choices.getSelection('logo_font')+'&type_gestionnaire='+choices.getSelection('type_gestionnaire')+'&gestion_buteurs='+choices.getSelection('gestion_buteurs')+'&type_lieu='+choices.getSelection('type_lieu')+'&type='+choices.getSelection('type')+'&gestion_nul='+choices.getSelection('gestion_nul')+'&type_sport='+choices.getSelection('type_sport')+'&gestion_fanny='+choices.getSelection('gestion_fanny')+'&gestion_sets='+choices.getSelection('gestion_sets')+attrs(['ch_nom', 'visu_journee', 'ch_nom_saison', 'lieu_pratique', 'ch_description', 'zoom', 'lat', 'lng', 'photo', 'twitter' ]);
	xx({action:'leagues', id:'main', url:'edit_leagues_do.php'+params+'&idl='+<?= $idl ?>+'&upd=<?= $modifier ? 1 : 0 ?>'});

	return true;
}
annuler = function()
{
	mm({action: '<?= $sess_context->isAdmin() && $modifier ? "dashboard" : "leagues" ?>'});
	return true;
}
var slider = unlocker_slider('ch_controle');
</script>
