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

if ($action == "upt" || $action == "copy") {

	$req = "SELECT count(*) total FROM strategies WHERE id=".$strategie_id." ".($sess_context->isSuperAdmin() || $action == "copy" ? "" : "AND user_id=".$sess_context->getUserId());
	$res = dbc::execSql($req);

	$row = mysqli_fetch_array($res);
	if ($row['total'] != 1) {
		echo '<div class="ui container inverted segment"><h2>Strategie not found or not autorized !!!</h2></div>';
		exit(0);
	}

	$req = "SELECT * FROM strategies WHERE id=".$strategie_id;
	$res = dbc::execSql($req);
	
	if ($row = mysqli_fetch_array($res)) {

		$criteres = array();
		$t = json_decode($row['data'], true);
		if ($row['methode'] != 3) {

			$i = 1;
			foreach($t['quotes'] as $key => $val) {
				$lst_symbol_strategie[$i] = $key;
				$lst_symbol_strategie_pct[$i++] = $val;
			}

			$nb_symbol = count($lst_symbol_strategie);

		} else {

			$t_criteres = explode('|', isset($t['criteres']) ? $t['criteres'] : "");
			$criteres = count($t_criteres) == 0 ? array() : array_flip($t_criteres);
			$nb_symbol = 1;

		}
		
	} else {
		echo '<div class="ui container inverted segment"><h3>Pb lecture stratégie !!!</h3></div>';
		exit(0);	
	}
}
else {
	$row = [ "title" => "", "methode" => 0, "defaut" => 0, "cycle" => 1 ];
	$nb_symbol = 1;
}

if ($action == "copy") {
	$row['title'] .= " - Copie";
}

if ($action == "new") {
	if (!$sess_context->isSuperAdmin()) {
        $req2 = "SELECT count(*) total FROM strategies WHERE user_id=".$sess_context->getUserId();
        $res2 = dbc::execSql($req2);
        $row2 = mysqli_fetch_array($res2);

        if ($row2['total'] >= 3) {
            echo '<div class="ui container inverted segment"><h3>Max stratégie atteint !!!</h3></div>';
            exit(0);
        }
    }
}

$lst_all_symbol = array();
$req3 = "SELECT * FROM stocks ORDER BY symbol";
$res3 = dbc::execSql($req3);
while($row3 = mysqli_fetch_array($res3)) $lst_all_symbol[] = $row3;

?>

<style type="text/css">
.label { width: 150px; text-align: left; background: #333 !important; }
.ui.selection.dropdown { height: 45px !important; border-bottom-left-radius: 0px !important; }
#symbol_area .hide { display: none; }
#symbol_area .label { width: 50px; text-align: center; }
.bestof .input_pct { display: none; }
#simulation_area .card { width: 100%; height: 100%; }
#simulation_area .card table { width: 100%; }
#simulation_area .tabnav select { width: auto !important; }
#simulation_area { padding: 0px !important; }
</style>

<form class="ui inverted large form">
	<input type="hidden" id="strategie_id"  value="<?= $strategie_id ?>" />
	<input type="hidden" id="backtest_call" value="0" />
	<div class="ui grid">

		<div class="ui sixteen wide column inverted clearing">
			<h2 class="ui inverted left floated header"><i class="inverted chess icon"></i> Stratégie</h2>
			<? if ($action == "upt") { ?>
				<h3 class="ui right floated header"><i id="strategie_delete_bt" class="ui inverted right floated black small trash icon"></i></h3>
			<? } ?>
		</div>

		<div class="eight wide column">
			<div class="inverted field">
				<div class="ui corner inverted labeled input">
					<div class="ui inverted basic label">Nom</div><input type="text" id="f_name" value="<?= utf8_decode($row['title']) ?>" placeholder="Nom stratégie" />
					<div id="f_name_error" class="ui inverted corner label"><i class="asterisk inverted icon"></i></div>
				</div>
			</div>
		
			<div class="inverted field">
				<div class="ui inverted labeled input">
					<div class="ui inverted basic label">Méthode</div>
					<select id="f_methode" class="ui selection dropdown">
						<option value="2" <?= $row['methode'] == 2 ? "selected=\"selected\"" : "" ?>>DCA</option>
						<option value="1" <?= $row['methode'] == 1 ? "selected=\"selected\"" : "" ?>>Meilleur DM</option>
						<? if ($sess_context->isSuperAdmin()) { ?>
							<option value="3" <?= $row['methode'] == 3 ? "selected=\"selected\"" : "" ?>>Super DM</option>
						<? } ?>
					</select>
				</div>
			</div>

			<div class="inverted field">
				<div class="ui inverted labeled input">
					<div class="ui inverted basic label">Rebalancing</div>
					<select id="f_cycle" class="ui selection dropdown">
						<option value="1"  <?= $row['cycle'] == 1  ? "selected=\"selected\"" : "" ?>>Mensuel</option>
						<option value="3"  <?= $row['cycle'] == 3  ? "selected=\"selected\"" : "" ?>>Trimestriel</option>
						<option value="6"  <?= $row['cycle'] == 6  ? "selected=\"selected\"" : "" ?>>Semestriel</option>
						<option value="12" <?= $row['cycle'] == 12 ? "selected=\"selected\"" : "" ?>>Annuel</option>
					</select>
				</div>
			</div>

			<div class="inverted field" id="form_nb_actifs">
				<div class="ui inverted labeled input">
					<div class="ui inverted basic label">Nb actifs</div>
					<select id="f_nb_symbol_max" class="ui selection dropdown">
						<? foreach (range(1, 7) as $number) echo "<option value=\"".$number."\" ".($number == $nb_symbol ? "selected=\"selected\"" : "").">".$number."</option>"; ?>
					</select>
				</div>
			</div>

			<? if ($sess_context->isSuperAdmin()) { ?>
			<div class="inverted field">
				<table><tr>
				<td><div class="ui inverted labeled input">
					<div class="ui inverted basic label">Par défaut</div>
				</div></td>
				<td><div class="ui inverted labeled checkbox">
					<div class="ui fitted toggle checkbox">
						<input id="f_common" type="checkbox" <?= $row["defaut"] == 1 ? 'checked="checked"' : '' ?>>
						<label></label>
					</div>
				</div></td>
				</tr></table>
			</div>
			<? } else { ?>
				<input id="f_common" type="hidden" value="0" />
			<? } ?>

		</div>

		<div id="symbol_area" class="eight wide column<?= $row['methode'] == 1 ? " bestof" : "" ?>">
			<? foreach (range(1, 7) as $number) { ?>
			<div id="symbol_choice_<?= $number ?>" class="inverted field <?= $number > $nb_symbol ? "hide" : "" ?>">
				<div class="ui inverted labeled input">
					<div id="f_symbol_choice_<?= $number ?>_error" class="ui inverted basic label"><?= $number ?></div>
					<select id="f_symbol_choice_<?= $number ?>" class="ui mini selection dropdown">
						<option value=""></option>	
						<? foreach($lst_all_symbol as $key => $val) { echo "<option id=\"symbol_choice_".$number."\" value=\"".$val['symbol']."\" ".(isset($lst_symbol_strategie[$number]) && $lst_symbol_strategie[$number] == $val['symbol'] ? "selected=\"selected\"" : "")." >".$val['symbol']."</option>"; } ?>
					</select>
					<input type="text" id="f_symbol_choice_pct_<?= $number ?>" class="input_pct" value="<?= $row['methode'] == 2 && isset($lst_symbol_strategie_pct[$number]) ? $lst_symbol_strategie_pct[$number] : "" ?>" size="3" placeholder="0" />
				</div>
			</div>
			<? } ?>
		</div>

		<div id="super_dm_area" class="eight wide column">
			<div id="super_dm_bt1" class="ui <?= isset($criteres['super_dm_bt1']) ? "blue" : "grey" ?> submit button">ETF</div>
			<div id="super_dm_bt2" class="ui <?= isset($criteres['super_dm_bt2']) ? "blue" : "grey" ?> submit button">EQUITY</div>
			<div id="super_dm_bt3" class="ui <?= isset($criteres['super_dm_bt3']) ? "blue" : "grey" ?> submit button">PEA</div>
			<div id="super_dm_bt4" class="ui <?= isset($criteres['super_dm_bt4']) ? "blue" : "grey" ?> submit button">EUR</div>
			<div id="super_dm_bt5" class="ui <?= isset($criteres['super_dm_bt5']) ? "blue" : "grey" ?> submit button">USD</div>
			<div id="super_dm_bt6" class="ui <?= isset($criteres['super_dm_bt6']) ? "blue" : "grey" ?> submit button">> 150M</div>
		</div>

		<div class="sixteen wide column">
			<div class="ui inverted stackable two column grid container">

				<div class="wide column"></div>

				<div class="wide right aligned column">
					<div id="strategie_cancel_bt" class="ui grey submit button">Cancel</div>
					<div id="strategie_backtest_bt" class="ui grey submit button">Backtesting</div>
					<div id="strategie_<?= $libelle_action_bt ?>_bt" class="ui floated right teal submit button"><?= $libelle_action_bt ?></div>
				</div>

			</div>
		</div>

		<div class="sixteen wide column" id="simulation_area"></div>

	</div>
</form>


<script>

filter_form = function(opt) {

	rmCN('symbol_area', 'bestof');
	showelt('symbol_area');
	showelt('form_nb_actifs');
	hide('super_dm_area');

	if (opt == 1) {
		addCN('symbol_area', 'bestof');
	}

	if (opt == 3) {
		show('super_dm_area');
		hide('symbol_area');
		hide('form_nb_actifs');
	}
}

check_form = function() {

	if (valof('f_name') == "") {
		Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Saisir un nom stratégie' });
		addCN('f_name_error', 'red');
		return false;
	}
	rmCN('f_name_error', 'red');

	if (valof('f_methode') != 3) {
		t_assets = [];
		sum_pct = 0;
		for(i=1; i <= parseInt(valof('f_nb_symbol_max')); i++) {

			var l_val = valof('f_symbol_choice_'+i);

			if (l_val == "") {
				Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Choisir un actif' });
				addCN('f_symbol_choice_'+i+'_error', 'red');
				return false;
			}
			rmCN('f_symbol_choice_'+i+'_error', 'red');

			if (valof('f_methode') == 2 && valof('f_symbol_choice_pct_'+i) == "") {
				Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Saisir un nombre' });
				addCN('f_symbol_choice_'+i+'_error', 'red');
				return false;
			}
			rmCN('f_symbol_choice_'+i+'_error', 'red');

			t_assets[l_val] = l_val in t_assets ? t_assets[l_val]+1 : 1;

			if (t_assets[l_val] > 1) {
				Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Actif en doublon' });
				addCN('f_symbol_choice_'+i+'_error', 'red');
				return false;
			}

			sum_pct += parseInt(valof('f_symbol_choice_pct_'+i));
		}

		if (valof('f_methode') == 2 && sum_pct != 100) {
			Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'La somme des répartitiond n\'est pas égale à 100% [' + (sum_pct) + ']' });
			addCN('f_name_error', 'red');
			return false;
		}
	}

	if (valof('f_methode') == 3) {

		var criteres = Array();
		Dom.find('#super_dm_area div').forEach(function(item) {
			if (isCN(item.id, 'blue')) criteres.push(item.id); 
		});

		if (criteres.length == 0) {
			Swal.fire({ title: 'Formulaire non valide !', icon: 'error', text: 'Sélectionner au moins un critère !' });
			return false;
		}
	}


	return true;
}

get_params_form = function(option) {

	// On recupere les valeurs du formulaire de la strategie
	params = '?action=<?= $action ?>&option_sim=' + option + attrs(['strategie_id', 'f_name', 'f_methode', 'f_cycle', 'f_nb_symbol_max', 'f_symbol_choice_1', 'f_symbol_choice_pct_1', 'f_symbol_choice_2', 'f_symbol_choice_pct_2', 'f_symbol_choice_3', 'f_symbol_choice_pct_3', 'f_symbol_choice_4', 'f_symbol_choice_pct_4', 'f_symbol_choice_5', 'f_symbol_choice_pct_5', 'f_symbol_choice_6', 'f_symbol_choice_pct_6', 'f_symbol_choice_7', 'f_symbol_choice_pct_7']) + '&f_common='+(valof('f_common') == 0 ? 0 : 1);

	// on recupere les criteres super DM
	var criteres = '';
	Dom.find('#super_dm_area div').forEach(function(item) {
		if (isCN(item.id, 'blue')) criteres += item.id + '|'; 
	});

	// On recupere les valeurs du formulaire de la simulation
	if (valof('backtest_call') == 1)
		params += '&criteres=' + criteres + attrs(['f_delai_retrait', 'f_montant_retrait', 'strategie_id', 'f_capital_init', 'f_invest', 'f_cycle_invest', 'f_date_start', 'f_date_end', 'f_compare_to' ]);
	else
		el('backtest_call').value = 1;

	return params;
}

// Listener sur changement de type de strategie
Dom.addListener(Dom.id('f_methode'), Dom.Event.ON_CHANGE, function(event) { filter_form(this.value); });

// Listener sur changement nb d'actif a selectionner
Dom.addListener(Dom.id('f_nb_symbol_max'), Dom.Event.ON_CHANGE, function(event) {
	for(i = 1; i <= 7; i++) { rmCN('symbol_choice_'+i, 'hide'); }
	for(i = parseInt(this.value)+1; i <= 7; i++) { addCN('symbol_choice_'+i, 'hide'); }
});

// Listener sur bt zone area super SM
Dom.find('#super_dm_area div').forEach(function(item) {
	Dom.addListener(item, Dom.Event.ON_CLICK, function(event) {
		switchColorElement(item.id, 'blue', 'grey');
		if (item.id == "super_dm_bt1" && isCN('super_dm_bt1', 'blue')) { replaceCN('super_dm_bt2', 'blue', 'grey'); }
		if (item.id == "super_dm_bt2" && isCN('super_dm_bt2', 'blue')) { replaceCN('super_dm_bt1', 'blue', 'grey'); }
		if (item.id == "super_dm_bt4" && isCN('super_dm_bt4', 'blue')) { replaceCN('super_dm_bt5', 'blue', 'grey'); }
		if (item.id == "super_dm_bt5" && isCN('super_dm_bt5', 'blue')) { replaceCN('super_dm_bt4', 'blue', 'grey'); }
	});
});

// Listener sur bt cancel
Dom.addListener(Dom.id('strategie_cancel_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'home', id: 'main', url: 'home_content.php', loading_area: 'strategie_cancel_bt' }); });

// Listener sur bt ajouter/modifier
Dom.addListener(Dom.id('strategie_<?= $libelle_action_bt ?>_bt'), Dom.Event.ON_CLICK, function(event) {
	if (check_form()) {
		params = get_params_form('strategie');
		go({ action: 'home', id: 'main', url: 'strategie_action.php'+params, loading_area: 'strategie_<?= $libelle_action_bt ?>_bt' });
	}
});

// Listener sur bt backtest
Dom.addListener(Dom.id('strategie_backtest_bt'), Dom.Event.ON_CLICK, function(event) {
	if (check_form()) {
		params = get_params_form('backtest');
		go({ action: 'home', id: 'simulation_area', url: 'simulator.php'+params, no_chg_cn: 1 });
	}
});

// Listener sur bt supprimer
<? if ($action == "upt") { ?>
Dom.addListener(Dom.id('strategie_delete_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'home', id: 'main', url: 'strategie_action.php?action=del&strategie_id=<?= $strategie_id ?>', loading_area: 'strategie_delete_bt', confirmdel: 1 }); });
<? } ?>

filter_form(<?= $row['methode'] ?>);

</script>