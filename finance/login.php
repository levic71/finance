<?

require_once "sess_context.php";

session_start();

include "common.php";

foreach(['f_email', 'redirect', 'goto'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

?>

<style type="text/css">
	.column { max-width: 450px; }
</style>

<div class="ui inverted middle aligned center aligned grid segment container">
	<div class="column">

		<h1 class="ui image header">
    		<i class="ui inverted universal access icon"></i>
			<div class="content"></div>
		</h1>

		<form class="ui inverted large form">

			<div class="ui inverted stacked segment">
				<div class="inverted field">
					<div class="ui inverted corner left icon input">
            			<i class="user inverted icon"></i>
            			<input type="text" id="f_email" name="email" value="<?= $f_email ?>" placeholder="E-mail address">
						<div id="f_email_error" class="ui inverted corner label"><i class="asterisk inverted icon"></i></div>
        			</div>
				</div>

				<div class="inverted field">
        			<div class="ui inverted left icon input">
						<i class="lock inverted icon"></i>
						<div id="f_pwd_error" class="ui inverted corner label"><i class="asterisk inverted icon"></i></div>
						<input type="password" id="f_pwd" name="password" placeholder="Password">
          			</div>
        		</div>

				<div id="f_pwd2_box" class="inverted field">
        			<div class="ui inverted left icon input">
						<i class="lock inverted icon"></i>
						<div id="f_pwd2_error" class="ui inverted corner label"><i class="asterisk inverted icon"></i></div>
						<input type="password" id="f_pwd2" name="password" placeholder="Confirmation">
          			</div>
        		</div>

				<div class="ui fluid buttons">
					<div id="login_signup_bt" class="ui large button">Sign Up</div>
					<div id="login_login_bt" class="ui large teal button">Login</div>
				</div>

			</div>

		</form>

	</div>
</div>

<? if ($redirect == 1) {
	uimx::staticInfoMsg("VOUS DEVEZ ETRE CONNECTE POUR UTILISER CETTE FONCTIONNALITE", "comment outline", "blue");
} ?>

<script>
	
	hide('f_pwd2_box');

	check_form = function(signup) {

		if (valof('f_email') == "") {
			Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Saisir un email' });
			addCN('f_email_error', 'red');
			return false;
		}
		rmCN('f_email_error', 'red');

		if (!check_email(valof('f_email'))) {
			Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Email non conforme' });
			addCN('f_email_error', 'red');
			return false;
		}
		rmCN('f_email_error', 'red');

		if (valof('f_pwd') == "") {
			Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Saisir un mot de passe' });
			addCN('f_pwd_error', 'red');
			return false;
		}
		rmCN('f_pwd_error', 'red');

		if (String(valof('f_pwd')).length < 8) {
			Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Minimum 8 caractères pour le mot de passe' });
			addCN('f_pwd_error', 'red');
			return false;
		}
		rmCN('f_pwd_error', 'red');

		if (signup) {

			if (isHidden('f_pwd2_box')) {
				showelt('f_pwd2_box');
				return false;
			}

			if (String(valof('f_pwd2')).length < 8) {
				Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Minimum 8 caractères pour le mot de passe' });
				addCN('f_pwd2_error', 'red');
				return false;
			}

			if (valof('f_pwd') != valof('f_pwd2')) {
				Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Confirmation mot de passe incorrecte' });
				addCN('f_pwd2_error', 'red');
				return false;
			}

		}		

		return true;
	}

	// Listener bt Sign Up
	Dom.addListener(Dom.id('login_signup_bt'), Dom.Event.ON_CLICK, function(event) {
		if (check_form(true)) {
			params = '?action=signup&'+attrs(['f_email', 'f_pwd']);
			go({ action: 'home', id: 'main', url: 'login_action.php'+params, loading_area: 'login_signup_bt' });
		}
	});

	// Listener bt Login
	Dom.addListener(Dom.id('login_login_bt'), Dom.Event.ON_CLICK, function(event) {
		if (check_form(false)) {
			params = '?action=login&'+attrs(['f_email', 'f_pwd'])+'&goto=<?= $goto ?>';
			go({ action: 'home', id: 'main', url: 'login_action.php'+params, loading_area: 'login_login_bt' });
		}
	});

</script>