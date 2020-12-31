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

if ($sess_context->isUserConnected()) {

	$sess_context->resetUserConnection();
	$sess_context->resetAdmin();
	echo ($sess_context->getRealChampionnatId() != sess_context::INVALID_CHAMP_ID_HOME ? "2" : "3")."||Vous êtes déconnecté";

} else {

$login = $sess_context->isSuperUser() ? "levic" : (isset($login_user) ? $login_user : "");
$pwd   = $sess_context->isSuperUser() ? "vicmju" : "";

?>

<div class="mdl-layout-spacer"></div>
<div class="mdl-card mdl-shadow--6dp mdl-cell mdl-cell--6-col mdl-cell--middle">
	<div class="mdl-grid mdl-card__title mdl-color--primary mdl-color-text--white">
		<h2 class="mdl-cell mdl-cell--12-col mdl-card__title-text mdl-color--primary">Authentifiez-vous avec<button id="btforget" class="mdl-button mdl-js-button mdl-button--icon" style="position:absolute; right: 10px;" onclick="mm({action: 'inscription'});"><i class="material-icons">settings</i></button><div class="mdl-tooltip mdl-tooltip--left" for="btforget">Mot de passe oublié ?</div></h2>
		<p class="mdl-cell mdl-cell--12-col mdl-typography--text-center text-divider">
			<button id="btfb" class="mdl-button mdl-js-button mdl-button--icon socialglyphs" onclick="alert('Comming soon ...'); return false;">f</button>
			<button id="btgg" class="mdl-button mdl-js-button mdl-button--icon socialglyphs" onclick="alert('Comming soon ...'); return false;">h</button>
			<button id="btin" class="mdl-button mdl-js-button mdl-button--icon socialglyphs" onclick="alert('Comming soon ...'); return false;">i</button>
			<button id="bttt" class="mdl-button mdl-js-button mdl-button--icon socialglyphs" onclick="alert('Comming soon ...'); return false;">t</button>
			<div class="mdl-tooltip" for="btfb">Facebook</div>
			<div class="mdl-tooltip" for="btgg">Google+</div>
			<div class="mdl-tooltip" for="btin">LinkedIn</div>
			<div class="mdl-tooltip" for="bttt">Twitter</div>
		</p>
		<p class="mdl-cell mdl-cell--12-col mdl-color--primary">ou plus classiquement</p>
	</div>
	<div class="mdl-card__supporting-text form-group mdl-grid">
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label mdl-cell mdl-cell--12-col">
				<i class="mdl-textfield__icon material-icons">person</i><input class="mdl-textfield__input" type="text" id="login" onKeyPress="return submit_login(this, event);" value="<?= $login ?>"  />
				<label class="mdl-textfield__label" for="login">Identifiant</label>
			</div>
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label mdl-cell mdl-cell--12-col">
				<i class="mdl-textfield__icon material-icons">lock</i><input class="mdl-textfield__input" type="password" id="pwd" onKeyPress="return submit_enter(this, event);" value="<?= $pwd ?>" />
				<label class="mdl-textfield__label" for="pwd">Mot de passe</label>
			</div>
			<div class="mdl-cell mdl-cell--12-col" style="padding-left: 50px;">
				<label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="remind">
					<input type="checkbox" id="remind" class="mdl-checkbox__input" checked>
					<span class="mdl-checkbox__label">Se souvenir de moi ?</span>
				</label>
			</div>
	</div>

	<? Wrapper::two_action_buttons(array(0 => array("libelle" => "Se conntecter", "onclick" => "return valid_form();"), 1 => array("libelle" => "S'inscrire", "onclick" => "mm({action: 'inscription'});"))); ?>

</div>
<div class="mdl-layout-spacer"></div>

<script>
mandatory(['login', 'pwd']); fs('login');

valid_form = function() {
	mm({action: 'valid', params: '<?= $sess_context->getRealChampionnatId() ?>|'+el('login').value+'|'+el('pwd').value, mobile: '<?= $mobile ?>'});
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

<? } ?>