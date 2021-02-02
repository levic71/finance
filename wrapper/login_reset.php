<?

require_once "../include/sess_context.php";
session_start();
include "common.php";
header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$mobile  = Wrapper::getRequest('mobile', 0);
$email = $sess_context->isSuperUser() ? "victor.ferreira@laposte.net" : "";

$menu = '';
$items = array();
array_push($items, array("func" => "textfield_form", "id" => "email", "value" => $email, "icon" => "mail", "libelle" => "Saisissez votre email",  "nb_col" => 12, "required" => 1, "autofocus" => 1));
$actions = array(0 => array("onclick" => "mm({action: 'login'});", "libelle" => "Annuler"), 1 => array("onclick" => "return valid_form();", "libelle" => "Valider"));
Wrapper::build_form(array("nb_col" => 6, "title" => "Mot de passe oublié", "menu" => $menu, "items" => $items, "actions" => $actions));

?>

<script>
mandatory(['email']); fs('email');

valid_form = function() {

	if (!check_alphanumext(valof('email'), 'Email', -1)) return false;

	go({url:'login_reset_do.php?'+attrs(['email'])});
	return true;
}

submit_login = function(elt, e)
{
	var keycode;
	if (window.event) keycode = window.event.keyCode;
	else if (e) keycode = e.which;
	else return true;

	if (keycode == 13) { fs('email'); return false; }

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