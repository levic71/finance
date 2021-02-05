<?

require_once "../include/sess_context.php";
session_start();
include "common.php";
include "../include/inc_db.php";
header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();
$token  = Wrapper::getRequest('chpwd', 1234567890);

if (!Wrapper::isChPwdValid($token)) {
?>
<script>
mm({action: 'login', mobile: 0});
$aMsg({msg : 'Demande invalide !' });</script>
<?
	exit(0);
}

$menu = '';
$items = array();
array_push($items, array("func" => "textfield_form", "id" => "pwd1", "icon" => "lock", "password" => 1, "libelle" => "Nouveau mot de passe",  "nb_col" => 12, "required" => 1, "autofocus" => 1));
array_push($items, array("func" => "textfield_form", "id" => "pwd2", "icon" => "",     "password" => 1, "libelle" => "Resaisissez votre mot de passe",  "nb_col" => 12, "required" => 1, "autofocus" => 1));
$actions = array(0 => array("onclick" => "mm({action: 'reload'});", "libelle" => "Annuler"), 1 => array("onclick" => "return valid_form();", "libelle" => "Valider"));
Wrapper::build_form(array("nb_col" => 6, "title" => "Mot de passe oublié", "menu" => $menu, "items" => $items, "actions" => $actions));

?>

<script>
mandatory(['pwd1', 'pwd2']); fs('pwd1');

valid_form = function() {
	if (!check_alphanumext(valof('pwd1'), 'Mot de passe', -1)) return false;
	if (!check_alphanumext(valof('pwd2'), 'Mot de passe', -1)) return false;
	if (valof('pwd2') != valof('pwd1'))
	{
		$dMsg({msg : 'La confirmation du mot de passe est incorrecte !'});
		return false;
	}

	go({url:'login_chpwd_do.php?token=<?= $token ?>&'+attrs(['pwd1'])});
	return true;
}

submit_login = function(elt, e)
{
	var keycode;
	if (window.event) keycode = window.event.keyCode;
	else if (e) keycode = e.which;
	else return true;

	if (keycode == 13) { fs('pwd1'); return false; }

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