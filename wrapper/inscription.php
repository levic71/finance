<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$sess_context->checkChampionnatValidity();

$db = dbc::connect();
unset($_SESSION['antispam']);
$_SESSION['antispam'] = ToolBox::getRand(5);

$modifier = 0 > 0 ? true : false;

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
$login     = Wrapper::getRequest('login',     '');
$pwd       = Wrapper::getRequest('pwd',       '');
$pwd2      = '';
$controle  = Wrapper::getRequest('controle', '');
$upd       = Wrapper::getRequest('upd', 0);

$modifier = $upd == 1 && $sess_context->isUserConnected() ? true : false;

if ($modifier) {

	$select = "SELECT * FROM jb_users WHERE id=".$sess_context->user['id'];
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
		$login     = $row['login'];
		$pwd       = $row['pwd'];
		$pwd2      = $row['pwd'];
	}
} else {

	if ($sess_context->isSuperUser()) {
		$pseudo   = "Victor_".$_SESSION['antispam'];
		$nom      = "FERREIRA";
		$prenom   = "Victor";
		$email    = "toto@toto.com";
		$login    = $pseudo;
		$pwd      = "vicmju";
		$pwd2     = "vicmju";
		$controle = $_SESSION['antispam'];
	}
}

$title = $modifier ? "Modifier mon profil" : "Inscription";

$items = array();
array_push($items, array("func" => "textfield_form",        "id" => "pseudo", "value" => $pseudo, "icon" => "account_circle", "libelle" => "Pseudo", "nb_col" => 6, "required" => 1, "autofocus" => 1));
array_push($items, array("func" => "textfield_form",        "id" => "email", "value" => $email, "icon" => "email", "libelle" => "Email", "nb_col" => 6, "required" => 1));
array_push($items, array("func" => "textfield_form",        "id" => "nom", "value" => $nom, "icon" => "person", "libelle" => "Nom", "nb_col" => 6, "required" => 1));
array_push($items, array("func" => "textfield_form",        "id" => "prenom", "value" => $prenom, "icon" => "", "libelle" => "Prénom", "nb_col" => 6, "required" => 1));
array_push($items, array("func" => "upload_image_form",     "id" => "photo", "value" => $photo, "icon" => "photo_camera", "libelle" => "Photo", "nb_col" => 12));
array_push($items, array("func" => "textfield_form",        "id" => "ville", "value" => $ville, "icon" => "home",  "libelle" => "Ville", "nb_col" => 4));
array_push($items, array("func" => "textfield_form",        "id" => "tel", "value" => $tel, "icon" => "phone",  "libelle" => "Téléphone", "nb_col" => 4));
array_push($items, array("func" => "textfield_form",        "id" => "mobile", "value" => $mobile, "icon" => "smartphone",  "libelle" => "Mobile", "nb_col" => 4));
array_push($items, array("func" => "choice_component_form", "id" => "sexe", "icon" => "wc", "libelle" => "Sexe", "nb_col" => 6, "grouped" => 1));
array_push($items, array("func" => "calendar_component_form", "id" => "date_nais", "value" => $date_nais, "icon" => "today", "libelle" => "Date de naissance", "nb_col" => 4, "grouped" => 1));
array_push($items, array("func" => "number_component_form", "id" => "taille-zip", "value" => $taille, "icon" => "publish", "libelle" => "Taille - cm", "nb_col" => 4, "start" => 0, "end" => 250));
array_push($items, array("func" => "number_component_form", "id" => "poids-zip", "value" => $poids, "icon" => "fitness_center", "libelle" => "Poids - kg", "nb_col" => 4, "start" => 0, "end" => 250));
array_push($items, array("func" => "number_component_form", "id" => "poignet-zip", "value" => $poignet, "icon" => "replay", "libelle" => "Circonférence poignet - cm", "nb_col" => 4, "start" => 0, "end" => 250));
array_push($items, array("func" => "choice_component_form", "id" => "morpho", "icon" => "format_size", "libelle" => "Morphologie", "nb_col" => 6, "grouped" => 1));
array_push($items, array("func" => "choice_component_form", "id" => "activite", "icon" => "poll", "libelle" => "Niveau d'activité physique", "nb_col" => 6));
array_push($items, array("func" => "textfield_form",        "id" => "login", "value" => $login, "icon" => "fingerprint", "libelle" => "Identifiant", "nb_col" => 6, "required" => 1));
array_push($items, array("func" => "choice_component_form", "id" => "confidentialite", "icon" => "security", "libelle" => "Confidentialité profil", "nb_col" => 6, "grouped" => 1));
array_push($items, array("func" => "textfield_form",        "id" => "pwd", "value" => $pwd, "icon" => "lock", "libelle" => "Mot de passe", "nb_col" => 6, "required" => 1, "password" => 1));
array_push($items, array("func" => "textfield_form",        "id" => "pwd2", "value" => $pwd2, "icon" => "", "libelle" => "Confirmation", "nb_col" => 6, "required" => 1, "password" => 1));
if (!$modifier) {
	array_push($items, array("func" => "captcha_form", "id" => "controle", "icon" => "text_fields", "libelle" => "Je ne suis pas un robot", "nb_col" => 6));
	array_push($items, array("func" => "choice_component_form", "id" => "conditions", "icon" => "info_outline", "libelle" => "J'accepte les conditions d'utilisation du Jorkers", "nb_col" => 6, "grouped" => 1));
}
$actions = array(0 => array("onclick" => "return annuler();"), 1 => array("onclick" => "return valid_form();"));

Wrapper::build_form(array('title' => $title, 'items' => $items, 'actions' => $actions));

?>

<div class="mdl-cell mdl-cell--12-col mdl-cell--middle" style="text-align: center;">
	<small><a href="http://www.cnil.fr/en-savoir-plus/deliberations/deliberation/delib/106/" target="_blank">CNIL - Dispense n° 8 - Délibération n° 2010-229</a></small>
</div>

<script>
choices.build({ name: 'sexe', c1: 'blue', c2: 'white', values: [{ v: 1, l: 'Homme', s: <?= $sexe == 1 ? 'true' : 'false' ?> }, { v: 2, l: 'Femme', s: <?= $sexe == 2 ? 'true' : 'false' ?> }] });
choices.build({ name: 'confidentialite', c1: 'blue', c2: 'white', values: [{ v: 0, l: 'Publique', s: <?= $confidentialite == 0 ? 'true' : 'false' ?> }, { v: 1, l: 'Limitée', s: <?= $confidentialite == 1 ? 'true' : 'false' ?> }, { v: 2, l: 'Privée', s: <?= $confidentialite == 2 ? 'true' : 'false' ?> }] });
choices.build({ name: 'morpho', c1: 'blue', c2: 'white', values: [{ v: 1, l: 'Normale', s: <?= $morpho == 1 ? 'true' : 'false' ?> }, { v: 2, l: 'Large', s: <?= $morpho == 2 ? 'true' : 'false' ?> }, { v: 3, l: 'Mince', s: <?= $morpho == 3 ? 'true' : 'false' ?> }] });
choices.build({ name: 'activite', c1: 'blue', c2: 'white', singlepicking: true, removable: true, values: [ { v: 1, l: 'Sédentaire', s: <?= $activite == 1 ? 'true' : 'false' ?> }, { v: 2, l: 'Léger', s: <?= $activite == 2 ? 'true' : 'false' ?> }, { v: 3, l: 'Moyen', s: <?= $activite == 3 ? 'true' : 'false' ?> }, { v: 4, l: 'Intense', s: <?= $activite == 4 ? 'true' : 'false' ?> }, { v: 5, l: 'Très intense', s: <?= $activite == 5 ? 'true' : 'false' ?> }] });
choices.build({ name: 'conditions', c1: 'blue', c2: 'white', values: [{ v: 0, l: 'Non', s: true }, { v: 1, l: 'Oui', s: false }] });
valid_form = function() {

    if (!check_alphanumext(valof('pseudo'), 'Pseudo', -1))
		return false;
    if (!check_alphanumext(valof('email'), 'Email', -1))
		return false;
	if (!check_email(valof('email')))
		return false;
    if (!check_alphanumext(valof('login'), 'Identifiant', <?= $sess_context->isSuperAdmin() ? "5" : "6" ?>))
		return false;
    if (!check_alphanumext(valof('pwd'), 'Mot de passe', 6))
		return false;
    if (!check_alphanumext(valof('pwd2'), 'Confirmation', 6))
		return false;
<? if (!$modifier) { ?>
	if (!check_slide_value(slider))
		return false;
<? } ?>

	if (valof('pwd2') != valof('pwd'))
	{
		alert('La confirmation du mot de passe est incorrecte');
		return false;
	}

	if (valof('photo') != '')
	{
		items = valof('photo').split('.');
		if (items[(items.length-1)] != 'gif' && items[(items.length-1)] != 'GIF' && items[(items.length-1)] != 'jpg' && items[(items.length-1)] != 'JPG' && items[(items.length-1)] != 'JPEG' && items[(items.length-1)] != 'jpeg')
		{
			alert('Le format de l\'image doit être \'gif\' ou \'jpg\'.');
			return false;
		}
	}

<? if (!$modifier) { ?>
	var conditions = choices.getSelection('conditions');
	if (conditions == 0)
	{
		alert('Vous devez acceptez les conditions d\'utilisations');
		return false;
	}
<? } ?>

	var taille    = numbers.getValue('taille-zip');
	var poids     = numbers.getValue('poids-zip');
	var poignet   = numbers.getValue('poignet-zip');
	var date_nais = calendar.getValue('date_nais');

	var params = '?confidentialite='+choices.getSelection('confidentialite')+'&activite='+choices.getSelection('activite')+'&morpho='+choices.getSelection('morpho')+'&sexe='+choices.getSelection('sexe')+'&date_nais='+date_nais+'&taille='+taille+'&poignet='+poignet+'&poids='+poids+attrs(['pseudo', 'nom', 'prenom', 'photo', 'email', 'tel', 'mobile', 'ville', 'login', 'pwd', 'controle']);

	xx({id:'main', url:'inscription_do.php'+params+'&upd=<?= $modifier ? 1 : 0 ?>'});
	return false;
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

annuler = function()
{
	mm({ action:'<?= $sess_context->isChampionnatNonDefini() ? "home" : ($modifier ? "myprofile" : "tables") ?>' });
	return true;
}

var slider = unlocker_slider('controle');

</script>