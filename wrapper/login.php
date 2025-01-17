<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/ManagerFXList.php";
include "../www/StatsBuilder.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();
$mobile  = Wrapper::getRequest('mobile',    0);
$email = $sess_context->isSuperUser() ? "victor.ferreira@laposte.net" : (isset($cookie_email_user) ? $cookie_email_user : "");
$pwd   = $sess_context->isSuperUser() ? "vicmju" : "";

$menu = '<button id="btforget" class="mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect mdl-color-text--white" onclick="go({url: \'login_reset.php\'});"><i class="mdl-textfield__icon material-icons">lock_outline</i></button><div class="mdl-tooltip mdl-tooltip--left" for="btforget">Mot de passe oubli� ?</div>';
$items = array();
array_push($items, array("func" => "textfield_form", "id" => "email", "value" => $email, "icon" => "person", "libelle" => "Email",  "nb_col" => 12, "required" => 1, "autofocus" => 1));
array_push($items, array("func" => "textfield_form", "id" => "pwd",   "value" => $pwd,   "icon" => "lock",   "libelle" => "Mot de passe", "nb_col" => 12, "required" => 1, "password" => 1));
array_push($items, array("func" => "checkbox_form",  "id" => "remind", "icon" => "", "libelle" => "Se souvenir de moi ?", "nb_col" => 11, "checked" => 1));
$actions = array(0 => array("onclick" => "mm({action: 'inscription'});", "libelle" => "S'inscrire", "color" => ""), 1 => array("onclick" => "return valid_form();", "libelle" => "Se connecter"));
Wrapper::build_form(array("nb_col" => 6, "title" => "Authentification", "menu" => $menu, "items" => $items, "actions" => $actions));

?>

<script>
mandatory(['email', 'pwd']); fs('email');

valid_form = function() {

	if (!check_alphanumext(valof('email'), 'Email', -1)) return false;
    if (!check_alphanumext(valof('pwd'), 'Mot de passe', -1)) return false;

	mm({action: 'valid', params: '<?= $sess_context->getRealChampionnatId() ?>|'+el('email').value+'|'+el('pwd').value, mobile: '<?= $mobile ?>'});
	return false;
}

submit_login = function(elt, e)
{
	var keycode;
	if (window.event) keycode = window.event.keyCode;
	else if (e) keycode = e.which;
	else return true;

	if (keycode == 13) { fs('pwd'); return false; }

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
</script>