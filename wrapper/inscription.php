<?

require_once "../include/sess_context.php";

session_start();

// test

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$sess_context->checkChampionnatValidity();

$db = dbc::connect();
unset($_SESSION['antispam']);
$_SESSION['antispam'] = ToolBox::getRand(5);

$modifier = $sess_context->isUserConnected() ? true : false;

$confidentialite = Wrapper::getRequest('confidentialite', 0);
$sexe      = Wrapper::getRequest('sexe',      1);
$morpho    = Wrapper::getRequest('morpho',    1);
$activite  = Wrapper::getRequest('activite',  3);
$nom       = Wrapper::getRequest('nom',       '');
$prenom    = Wrapper::getRequest('prenom',    '');
$pseudo    = Wrapper::getRequest('pseudo',    '');
$taille    = Wrapper::getRequest('taille',    170);
$poids     = Wrapper::getRequest('poids',     70);
$poignet   = Wrapper::getRequest('poignet',   16);
$photo     = Wrapper::getRequest('photo',     '');
$email     = Wrapper::getRequest('email',     '');
$tel       = Wrapper::getRequest('tel',       '');
$mobile    = Wrapper::getRequest('mobile',    '');
$ville     = Wrapper::getRequest('ville',     '');
$date_nais = Wrapper::getRequest('date_nais', date("d/m/Y"));
$pwd       = Wrapper::getRequest('pwd',       '');
$pwd2      = '';
$controle  = Wrapper::getRequest('controle', '');
$upd       = Wrapper::getRequest('upd', 0);

$modifier = $upd == 1 && $sess_context->isUserConnected() ? true : false;

if ($modifier) {

	$select = "SELECT * FROM jb_users WHERE removed=0 AND id=".$sess_context->user['id'];
	$res = dbc::execSQL($select);
	if ($row = mysqli_fetch_array($res))
	{
		$confidentialite = $row['confidentialite'];
		$date_nais = ToolBox::mysqldate2date($row['date_nais']);
		$sexe      = $row['sexe'];
		$morpho    = $row['morpho'];
		$activite  = $row['activite'];
		$poignet   = $row['poignet'];
		$nom       = $row['nom'];
		$prenom    = $row['prenom'];
		$pseudo    = $row['pseudo'];
		$taille    = $row['taille'];
		$poids     = $row['poids'];
		$poignet   = $row['poignet'];
		$photo     = $row['photo'];
		$email     = $row['email'];
		$tel       = $row['tel'];
		$mobile    = $row['mobile'];
		$ville     = $row['ville'];
		$pwd       = $row['pwd'];
		$pwd2      = $row['pwd'];
	}
} else {

	if ($sess_context->isSuperUser()) {
		$pseudo   = "Victor_".$_SESSION['antispam'];
		$email    = $pseudo."@laposte.net";
		$pwd      = "vicmju";
		$pwd2     = "vicmju";
		$controle = $_SESSION['antispam'];
	}
}

if (!file_exists($photo)) $photo = sess_context::default_photo;

$title = $modifier ? "Modifier mon profil" : "Inscription";
$items = array();

if ($modifier) {
	array_push($items, array("func" => "textfield_form",          "id" => "email",       "value" => $email,     "icon" => "email",          "libelle" => "Email/Identifiant", "nb_col" => 6, "required" => 1, "autofocus" => 1));
	array_push($items, array("func" => "textfield_form",          "id" => "pseudo",      "value" => $pseudo,    "icon" => "account_circle", "libelle" => "Pseudo",            "nb_col" => 6, "required" => 1));
	array_push($items, array("func" => "textfield_form",          "id" => "nom",         "value" => $nom,       "icon" => "person",         "libelle" => "Nom", "nb_col" => 6));
	array_push($items, array("func" => "textfield_form",          "id" => "prenom",      "value" => $prenom,    "icon" => "",               "libelle" => "Pr�nom", "nb_col" => 6));
	array_push($items, array("func" => "divider_form",            "id" => "div1",        "nb_col" => 12));
	array_push($items, array("func" => "upload_image_form",       "id" => "photo",       "value" => $photo,     "icon" => "photo_camera",   "libelle" => "Photo", "extra" => "users", "nb_col" => 12));
	array_push($items, array("func" => "divider_form",            "id" => "div2",        "nb_col" => 12));
	array_push($items, array("func" => "textfield_form",          "id" => "ville",       "value" => $ville,     "icon" => "home",           "libelle" => "Ville", "nb_col" => 6));
	array_push($items, array("func" => "textfield_form",          "id" => "mobile",      "value" => $mobile,    "icon" => "smartphone",     "libelle" => "Mobile", "nb_col" => 6));
	array_push($items, array("func" => "choice_component_form",   "id" => "sexe",                               "icon" => "wc",             "libelle" => "Sexe", "nb_col" => 6, "grouped" => 1));
	array_push($items, array("func" => "calendar_component_form", "id" => "date_nais",   "value" => $date_nais, "icon" => "today",          "libelle" => "Date de naissance", "nb_col" => 4, "grouped" => 1));
	array_push($items, array("func" => "choice_component_form",   "id" => "confidentialite", "icon" => "security", "libelle" => "Confidentialit� profil", "nb_col" => 12, "grouped" => 1));
	array_push($items, array("func" => "choice_component_form",   "id" => "morpho",                             "icon" => "format_size",    "libelle" => "Morphologie", "nb_col" => 6, "grouped" => 1));
	array_push($items, array("func" => "choice_component_form",   "id" => "activite",                           "icon" => "poll",           "libelle" => "Niveau d'activit� physique", "nb_col" => 6));
	array_push($items, array("func" => "number_component_form",   "id" => "taille-zip",  "value" => $taille,    "icon" => "publish",        "libelle" => "Taille - cm", "nb_col" => 4, "start" => 0, "end" => 250));
	array_push($items, array("func" => "number_component_form",   "id" => "poids-zip",   "value" => $poids,     "icon" => "fitness_center", "libelle" => "Poids - kg", "nb_col" => 4, "start" => 0, "end" => 250));
	array_push($items, array("func" => "number_component_form",   "id" => "poignet-zip", "value" => $poignet,   "icon" => "replay",         "libelle" => "Circonf�rence poignet - cm", "nb_col" => 4, "start" => 0, "end" => 250));
	array_push($items, array("func" => "divider_form", "id" => "div3", "nb_col" => 12));

	// Mot de passe vide et si on remplit les 2, cela signifie qu'on veut le changer (de toute fa�on ils sont crypt�s !)
	$pwd = ""; $pwd2 = "";
	array_push($items, array("func" => "textfield_form", "id" => "pwd",  "value" => $pwd,  "icon" => "lock", "libelle" => "Mot de passe", "subtext" => "Laisser vide si inchang�", "nb_col" => 6, "password" => 1));
	array_push($items, array("func" => "textfield_form", "id" => "pwd2", "value" => $pwd2, "icon" => "",     "libelle" => "Confirmation", "nb_col" => 6, "password" => 1));
}
else
{
	array_push($items, array("func" => "textfield_form", "id" => "email_new",  "value" => $email,  "icon" => "email",          "libelle" => "Email/Identifiant", "nb_col" => 6, "required" => 1, "autofocus" => 1));
	array_push($items, array("func" => "textfield_form", "id" => "pseudo_new", "value" => $pseudo, "icon" => "account_circle", "libelle" => "Pseudo",            "nb_col" => 6, "required" => 1));
	array_push($items, array("func" => "textfield_form", "id" => "pwd_new",  "value" => $pwd,  "icon" => "lock", "libelle" => "Mot de passe", "subtext" => "Laisser vide si inchang�", "nb_col" => 6, "password" => 1));
	array_push($items, array("func" => "textfield_form", "id" => "pwd2_new", "value" => $pwd2, "icon" => "",     "libelle" => "Confirmation", "nb_col" => 6, "password" => 1));

}

$menu = "";
if (!$modifier) {
	array_push($items, array("func" => "captcha_form",          "id" => "controle",   "icon" => "text_fields",  "libelle" => "Je ne suis pas un robot", "nb_col" => 6));
	array_push($items, array("func" => "choice_component_form", "id" => "conditions", "icon" => "info_outline", "libelle" => "J'accepte les conditions d'utilisation du Jorkers", "nb_col" => 6, "grouped" => 1));
}
else
	$menu .= '<button id="btdel" class="mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect mdl-color-text--white mdl-button" onclick="return confirmDel();"><i class="mdl-textfield__icon material-icons">delete_forever</i></button><div class="mdl-tooltip mdl-tooltip--left" for="btdel">Supprimer le compte</div>';

$actions = array(0 => array("onclick" => "return annuler();"), 1 => array("onclick" => "return valid_form();"));

Wrapper::build_form(array('title' => $title, 'menu' => $menu, 'items' => $items, 'nb_col'=> 10, 'actions' => $actions));

?>

<div class="mdl-cell mdl-cell--12-col mdl-cell--middle" style="text-align: center;">
	<small><a href="http://www.cnil.fr/en-savoir-plus/deliberations/deliberation/delib/106/" class="mdl-color-text--white" target="_blank">CNIL - Dispense n� 8 - D�lib�ration n� 2010-229</a></small>
</div>

<script>

<? if ($modifier) { ?>
choices.build({ name: 'sexe', c1: 'blue', c2: 'white', values: [{ v: 1, l: 'Homme', s: <?= $sexe == 1 ? 'true' : 'false' ?> }, { v: 2, l: 'Femme', s: <?= $sexe == 2 ? 'true' : 'false' ?> }] });
choices.build({ name: 'confidentialite', c1: 'blue', c2: 'white', values: [{ v: 0, l: 'Publique', s: <?= $confidentialite == 0 ? 'true' : 'false' ?> }, { v: 1, l: 'Limit�e', s: <?= $confidentialite == 1 ? 'true' : 'false' ?> }, { v: 2, l: 'Priv�e', s: <?= $confidentialite == 2 ? 'true' : 'false' ?> }] });
choices.build({ name: 'morpho', c1: 'blue', c2: 'white', values: [{ v: 1, l: 'Normale', s: <?= $morpho == 1 ? 'true' : 'false' ?> }, { v: 2, l: 'Large', s: <?= $morpho == 2 ? 'true' : 'false' ?> }, { v: 3, l: 'Mince', s: <?= $morpho == 3 ? 'true' : 'false' ?> }] });
choices.build({ name: 'activite', c1: 'blue', c2: 'white', singlepicking: true, removable: true, values: [ { v: 1, l: 'S�dentaire', s: <?= $activite == 1 ? 'true' : 'false' ?> }, { v: 2, l: 'L�ger', s: <?= $activite == 2 ? 'true' : 'false' ?> }, { v: 3, l: 'Moyen', s: <?= $activite == 3 ? 'true' : 'false' ?> }, { v: 4, l: 'Intense', s: <?= $activite == 4 ? 'true' : 'false' ?> }, { v: 5, l: 'Tr�s intense', s: <?= $activite == 5 ? 'true' : 'false' ?> }] });
<? } ?>
choices.build({ name: 'conditions', c1: 'blue', c2: 'white', values: [{ v: 0, l: 'Non', s: true }, { v: 1, l: 'Oui', s: false }] });

valid_form = function() {

<? if ($modifier) { ?>
    if (!check_alphanumext(valof('pseudo'), 'Pseudo', -1))      return false;
    if (!check_alphanumext(valof('email'),  'Email', -1))       return false;
	if (!check_email(valof('email')))                           return false;
	if (valof('pwd').length > 0 && !check_alphanumext(valof('pwd'),  'Mot de passe', 6)) return false;
    if (valof('pwd').length > 0 && !check_alphanumext(valof('pwd2'), 'Confirmation', 6)) return false;
	if (valof('pwd2') != valof('pwd'))
	{
		$dMsg({msg : 'La confirmation du mot de passe est incorrecte' });
		return false;
	}
<? } else { ?>
    if (!check_alphanumext(valof('pseudo_new'), 'Pseudo', -1))      return false;
    if (!check_alphanumext(valof('email_new'),  'Email', -1))       return false;
	if (!check_email(valof('email_new')))                               return false;
	if (!check_alphanumext(valof('pwd_new'),  'Mot de passe', 6))   return false;
    if (!check_alphanumext(valof('pwd2_new'), 'Confirmation', 6))   return false;
	if (valof('pwd2_new') != valof('pwd_new'))
	{
		$dMsg({msg : 'La confirmation du mot de passe est incorrecte' });
		return false;
	}
<? } ?>

<? if (!$modifier) { ?>
	if (!check_slide_value(slider)) return false;
<? } ?>

	if (valof('photo') != '')
	{
		items = valof('photo').split('.');
		if (items[(items.length-1)].toUpperCase() != 'GIF' && items[(items.length-1)].toUpperCase() != 'PNG' && items[(items.length-1)].toUpperCase() != 'JPG' && items[(items.length-1)].toUpperCase() != 'JPEG')
		{
			$dMsg({msg : 'Le format de l\'image doit �tre \'gif\', \'png\' ou \'jpg\'' });
			return false;
		}
	}

<? if (!$modifier) { ?>
	var conditions = choices.getSelection('conditions');
	if (conditions == 0)
	{
		$dMsg({msg : 'Vous devez acceptez les conditions d\'utilisations'});
		return false;
	}
<? } ?>

<? if ($modifier) { ?>
	var taille    = numbers.getValue('taille-zip');
	var poids     = numbers.getValue('poids-zip');
	var poignet   = numbers.getValue('poignet-zip');
	var date_nais = calendar.getValue('date_nais');
	var params = '?confidentialite='+choices.getSelection('confidentialite')+'&activite='+choices.getSelection('activite')+'&morpho='+choices.getSelection('morpho')+'&sexe='+choices.getSelection('sexe')+'&date_nais='+date_nais+'&taille='+taille+'&poignet='+poignet+'&poids='+poids+attrs(['pseudo', 'nom', 'prenom', 'photo', 'email', 'mobile', 'ville', 'pwd', 'controle']);
<? } else { ?>
	var params = '?'+attrs(['pseudo_new', 'email_new', 'pwd_new', 'controle']);
<? } ?>

	xx({id:'main', url:'inscription_do.php'+params+'&upd=<?= $modifier ? 1 : 0 ?>'});
	return true;
}

submit_enter = function(elt, e)
{
	var keycode;
	if (window.event) keycode = window.event.keyCode;
	else if (e) keycode = e.which;
	else return true;

	if (keycode == 13) { valid_form(); return false; }

	return true;
}

confirmDel = function(elt, e)
{
	if (confirm('Etes vous sur de vouloir supprimer votre compte ?')) {
		go({action: 'login_remove', id:'main', url:'login_remove_do.php'});
	} 

	return true;
}

annuler = function()
{
	mm({ action: '<?= $modifier ? "myprofile" : "leagues" ?>' });
	return true;
}

var slider = unlocker_slider('controle');

</script>