<?

require_once "sess_context.php";

session_start();

include "common.php";

$action = "new";

foreach(['strategie_id', 'action'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$libelle_action_bt = tools::getLibelleBtAction($action);

$db = dbc::connect();

$lst_symbol_strategie = array();
$lst_symbol_strategie_pct = array();

if ($action == "upt") {
	$req = "SELECT count(*) total FROM strategies WHERE id=".$strategie_id;
	$res = dbc::execSql($req);
	$row = mysqli_fetch_array($res);

	if ($row['total'] != 1) {
		echo '<div class="ui container inverted segment"><h2>Strategies not found !!!</h2></div>"';
		exit(0);
	}

	$req = "SELECT * FROM strategies WHERE id=".$strategie_id;
	$res = dbc::execSql($req);
	$row = mysqli_fetch_array($res);

	$t = json_decode($row['data'], true);
	$i = 1;
	foreach($t['quotes'] as $key => $val) {
		$lst_symbol_strategie[$i] = $key;
		$lst_symbol_strategie_pct[$i++] = $val;
	}

	$nb_symbol = count($lst_symbol_strategie);
}
else {
	$row = [ "title" => "", "methode" => 1];
	$nb_symbol = 1;
}

$lst_all_symbol = array();
$req3 = "SELECT * FROM stocks ORDER BY symbol";
$res3 = dbc::execSql($req3);
while($row3 = mysqli_fetch_array($res3)) $lst_all_symbol[] = $row3;

?>

<style type="text/css">
	.column { max-width: 90%; }
	.label { width: 150px; text-align: left; background: #333 !important; }
	.ui.selection.dropdown { height: 45px !important; border-bottom-left-radius: 0px !important; }
	#symbol_area .hide { display: none; }
	#symbol_area .label { width: 50px; text-align: center; }
	.bestof .input_pct { display: none; }
</style>

<div class="ui inverted middle aligned center aligned grid segment container">
    <div class="column">

		<div class="ui inverted clearing segment">
			<h2 class="ui inverted left floated header">Stratégie</h2>
<? if ($action == "upt") { ?>
			<h3 class="ui right floated header"><i id="strategie_delete_bt" class="ui inverted right floated black small trash icon"></i></h3>
<? } ?>
		</div>

		<form class="ui inverted large form">

			<input type="hidden" id="strategie_id" value="<?= $strategie_id ?>" />

			<div class="ui inverted stackable two column grid container">

				<div class="wide column">
	                <div class="inverted field">
	                    <div class="ui corner inverted labeled input">
                        	<div class="ui inverted basic label">Nom</div><input type="text" id="f_name" value="<?= $row['title'] ?>" placeholder="Nom stratégie" />
							<div id="f_name_error" class="ui inverted corner label"><i class="asterisk inverted icon"></i></div>
                    	</div>
					</div>
	                <div class="inverted field">
						<div class="ui inverted labeled input">
							<div class="ui inverted basic label">Méthode</div>
							<select id="f_methode" class="ui selection dropdown">
								<option value="1" <?= $row['methode'] == 1 ? "selected=\"selected\"" : "" ?>>Meilleur DM</option>
								<option value="2" <?= $row['methode'] == 2 ? "selected=\"selected\"" : "" ?>>Par Répartition</option>
							</select>
                    	</div>
					</div>
	                <div class="inverted field">
						<div class="ui inverted labeled input">
							<div class="ui inverted basic label">Nb actifs</div>
							<select id="f_nb_symbol_max" class="ui selection dropdown">
								<? foreach (range(1, 6) as $number) echo "<option value=\"".$number."\" ".($number == $nb_symbol ? "selected=\"selected\"" : "").">".$number."</option>"; ?>
							</select>
                    	</div>
					</div>
                </div>

				<div id="symbol_area" class="wide column <?= $row['methode'] == 1 ? "bestof" : "" ?>">
<? foreach (range(1, 6) as $number) { ?>
					<div id="symbol_choice_<?= $number ?>" class="inverted field <?= $number > $nb_symbol ? "hide" : "" ?>">
						<div class="ui inverted labeled input">
							<div id="f_symbol_choice_<?= $number ?>_error" class="ui inverted basic label"><?= $number ?></div>
							<select id="f_symbol_choice_<?= $number ?>" class="ui mini selection dropdown">
								<option value=""></option>
<? foreach($lst_all_symbol as $key => $val) { echo "<option id=\"symbol_choice_".$number."\" value=\"".$val['symbol']."\" ".($lst_symbol_strategie[$number] == $val['symbol'] ? "selected=\"selected\"" : "")." >".$val['symbol']."</option>"; } ?>
							</select>
							<input type="text" id="f_symbol_choice_pct_<?= $number ?>" class="input_pct" value="<?= $row['methode'] == 2 && isset($lst_symbol_strategie_pct[$number]) ? $lst_symbol_strategie_pct[$number] : "" ?>" size="3" placeholder="0" />
                    	</div>
					</div>
<? } ?>
				</div>

			</div>

			<div class="ui inverted stackable two column grid container">

				<div class="wide column"></div>

				<div class="wide right aligned column">
					<div id="strategie_cancel_bt" class="ui grey submit button">Cancel</div>
                    <div id="strategie_<?= $libelle_action_bt ?>_bt" class="ui floated right teal submit button"><?= $libelle_action_bt ?></div>
				</div>

			</div>

		</form>
    </div>
</div>

<script>
	Dom.addListener(Dom.id('f_methode'), Dom.Event.ON_CHANGE, function(event) { rmCN('symbol_area', 'bestof'); if (this.value == 1) addCN('symbol_area', 'bestof'); });
	Dom.addListener(Dom.id('f_nb_symbol_max'), Dom.Event.ON_CHANGE, function(event) {
		for(i = 1; i <= 6; i++) { rmCN('symbol_choice_'+i, 'hide'); }
		for(i = parseInt(this.value)+1; i <= 6; i++) { addCN('symbol_choice_'+i, 'hide'); }
	});
	Dom.addListener(Dom.id('strategie_cancel_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'home', id: 'main', url: 'home_content.php', loading_area: 'strategie_cancel_bt' }); });
	Dom.addListener(Dom.id('strategie_<?= $libelle_action_bt ?>_bt'), Dom.Event.ON_CLICK, function(event) {

		if (valof('f_name') == "") {
			Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Saisir un nom stratégie' });
			addCN('f_name_error', 'red');
			return;
		}
		rmCN('f_name_error', 'red');

		sum_pct = 0;
		for(i=1; i <= parseInt(valof('f_nb_symbol_max')); i++) {

			if (valof('f_symbol_choice_'+i) == "") {
				Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Choisir un actif' });
				addCN('f_symbol_choice_'+i+'_error', 'red');
				return;
			}
			rmCN('f_symbol_choice_'+i+'_error', 'red');

			if (valof('f_methode') == 2 && valof('f_symbol_choice_pct_'+i) == "") {
				Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Saisir un nombre' });
				addCN('f_symbol_choice_'+i+'_error', 'red');
				return;
			}
			rmCN('f_symbol_choice_'+i+'_error', 'red');

			sum_pct += parseInt(valof('f_symbol_choice_pct_'+i));
		}

		if (sum_pct > 100) {
			Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'La somme des répartitiond doit être inférieur à 100%' });
			addCN('f_name_error', 'red');
			return;
		}

		params = '?action=<?= $action ?>&'+attrs(['strategie_id', 'f_name', 'f_methode', 'f_nb_symbol_max', 'f_symbol_choice_1', 'f_symbol_choice_pct_1', 'f_symbol_choice_2', 'f_symbol_choice_pct_2', 'f_symbol_choice_3', 'f_symbol_choice_pct_3', 'f_symbol_choice_4', 'f_symbol_choice_pct_4', 'f_symbol_choice_5', 'f_symbol_choice_pct_5', 'f_symbol_choice_6', 'f_symbol_choice_pct_6']);
		go({ action: 'home', id: 'main', url: 'strategie_action.php'+params, loading_area: 'strategie_<?= $libelle_action_bt ?>_bt' });
	});
	Dom.addListener(Dom.id('strategie_delete_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'home', id: 'main', url: 'strategie_action.php?action=del&strategie_id=<?= $strategie_id ?>', loading_area: 'strategie_delete_bt', confirmdel: 1 }); });
</script>