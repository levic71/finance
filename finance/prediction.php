<?

require_once "sess_context.php";

session_start();

include "common.php";

$action = "new";

foreach(['item_id', 'action'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$libelle_action_bt = tools::getLibelleBtAction($action);

$db = dbc::connect();

if ($action == "upt") {
	$req = "SELECT count(*) total FROM users WHERE id=".$item_id;
	$res = dbc::execSql($req);
	$row = mysqli_fetch_array($res);

	if ($row['total'] != 1) {
		echo '<div class="ui container inverted segment"><h2>Utilisateur introuvable !!!</h2></div>"';
		exit(0);
	}

	$req = "SELECT * FROM users WHERE id=".$item_id;
	$res = dbc::execSql($req);
	$row = mysqli_fetch_array($res);
}
else {
	$row = [ "email" => "", "status" => 0];
}

?>

<style type="text/css">
	.column { max-width: 90%; }
	.label { width: 150px; text-align: left; background: #333 !important; }
	.ui.selection.dropdown { height: 45px !important; border-bottom-left-radius: 0px !important; }
</style>

<div class="ui inverted middle aligned center aligned grid segment container">
    <div class="column">

		<div class="ui inverted clearing segment">
			<h2 class="ui inverted left floated header">Prédiction</h2>
		</div>

		<form class="ui inverted large form">

			<input type="hidden" id="item_id" value="<?= $item_id ?>" />

			<div class="ui inverted stackable two column grid container">

				<div class="wide column">
	                <div class="inverted field">
	                    <div class="ui corner inverted labeled input">
                        	<div class="ui inverted basic label">Email</div><input type="text" id="f_email" value="<?= $row['email'] ?>" placeholder="Email" />
							<div id="f_email_error" class="ui inverted corner label"><i class="asterisk inverted icon"></i></div>
                    	</div>
					</div>
                </div>

				<div class="wide column">
					<div class="inverted field">
						<div class="ui inverted labeled input">
							<div class="ui inverted basic label">Activé</div>
							<select id="f_status" class="ui selection dropdown">
								<option value="0" <?= $row['status'] == 0 ? "selected=\"selected\"" : "" ?>>Non</option>
								<option value="1" <?= $row['status'] == 1 ? "selected=\"selected\"" : "" ?>>Oui</option>
							</select>
                    	</div>
					</div>
				</div>
			</div>

			<div class="ui inverted stackable two column grid container">

				<div class="wide column"></div>

				<div class="wide right aligned column">
					<div id="table_cancel_bt" class="ui grey submit button">Cancel</div>
                    <div id="table_<?= $libelle_action_bt ?>_bt" class="ui floated right teal submit button"><?= $libelle_action_bt ?></div>
				</div>

			</div>

		</form>
    </div>
</div>

<script>
	Dom.addListener(Dom.id('table_cancel_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'home', id: 'main', url: 'user_list.php', loading_area: 'table_cancel_bt' }); });
	Dom.addListener(Dom.id('table_<?= $libelle_action_bt ?>_bt'), Dom.Event.ON_CLICK, function(event) {

		if (valof('f_email') == "") {
			Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Saisir un email' });
			addCN('f_email_error', 'red');
			return;
		}
		rmCN('f_email_error', 'red');

		if (!check_email(valof('f_email'))) {
			Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Email non conforme' });
			addCN('f_email_error', 'red');
			return false;
		}
		rmCN('f_email_error', 'red');

		params = '?action=<?= $action ?>&'+attrs(['item_id', 'f_email', 'f_status' ]);
		go({ action: 'home', id: 'main', url: 'user_action.php'+params, loading_area: 'table_<?= $libelle_action_bt ?>_bt' });
	});
</script>