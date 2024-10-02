<?

require_once "sess_context.php";

session_start();

include "common.php";

$portfolio_id = 0;
$strat_ptf    = isset($_COOKIE["strat_ptf"]) ? $_COOKIE["strat_ptf"] :  1;

foreach (['portfolio_id'] as $key)
	$$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) uimx::redirectLoginPage('portfolio');

$devises = cacheData::readCacheData("cache/CACHE_GS_DEVISES.json");

// Recuperation des infos du portefeuille
$req = "SELECT * FROM portfolios WHERE user_id=" . $sess_context->getUserId();
$res = dbc::execSql($req);

$lst_ptf = array();
while($row = mysqli_fetch_assoc($res)) $lst_ptf[$row['id']] = $row;

// Bye bye si ptf inexistant
if (!isset($lst_ptf[$portfolio_id])) exit(0);

// Recuperation de tous les actifs
$quotes = calc::getIndicatorsLastQuote();

// Calcul synthese portefeuille
$portfolio_data = calc::aggregatePortfolioById($portfolio_id);

$sc = new StockComputing($quotes, $portfolio_data, $devises);
$sc->setStratPtf($strat_ptf);

// On récupère les infos du portefeuille + les positions et les ordres
$lst_positions = $sc->getPositions();
$lst_orders    = $sc->getOrders();
$lst_orders_futur = $sc->getOrdersFutur();

?>

<div class="ui container inverted segment">

	<h2 class="ui left floated">
		<i class="inverted briefcase icon"></i>
		<?= tools::UTF8_encoding($sc->getPtfName()) ?><small id="subtitle"></small>

		<? if (count($lst_ptf) > 0) { ?>
		<div class="compact circular black outline inverted ui icon top left pointing dropdown button" id="ptf_select">
			<i class="inverted caret down icon"></i>
			<div class="menu" id="ptf_menu">
				<? foreach($lst_ptf as $key => $val) if ($val['id'] != $portfolio_id) { ?>
				<div class="item ptf_item" data-value="<?= $val['id'] ?>"><?= tools::UTF8_encoding($val['name']) ?></div>
				<? } ?>
			</div>
		</div>
		<? } ?>

		<button id="portfolio_graph_bt" class="circular ui icon very small right floated labelled button"><i class="inverted black chart bar outline icon"></i></button>
		<button id="ptf_balance_bt" class="circular ui icon very small right floated darkgray labelled button"><i class="inverted black balance icon"></i></button>
	</h2>
	<div class="ui stackable column grid">
		<div class="row">
			<div class="ten wide column ptf_infos">
				<div class="ui stackable two column grid container">
					<div class="row">

						<div id="infos_area1" class="ui inverted column readonly form">
							<div class="field">
								<label>Label1</label>
								<input type="text" value="0" readonly="" />
								<a id="portfolio_switch_bt" class="ui primary right corner label"><i class="ui inverted retweet icon"></i></a>
							</div>
							<div class="field">
								<label>Label2</label>
								<input type="text" value="0" readonly="" />
							</div>
							<div class="field">
								<label>Label3</label>
								<input type="text" value="0" readonly="" />
							</div>
						</div>

						<div id="infos_area2" class="ui inverted column readonly form">
							<div class="field">
								<label>Label4</label>
								<input type="text" value="0" readonly="" />
							</div>
							<div class="field">
								<label>Label5</label>
								<input type="text" value="0" readonly="" />
							</div>
							<div class="field">
								<label>Label6</label>
								<input type="text" value="0" readonly="" />
							</div>
						</div>

					</div>
				</div>
				<div id="perf_ribbon3" class="ribbon">Perf<br /><small>0.00%</small></div>
			</div>
			<div class="six wide column" id="ptf_secteur" style="background: #222; border-bottom-right-radius: 50px; border-bottom: 1px solid grey;">
				<div id="perf_ribbon2" class="ribbon">Perf<br /><small>0.00%</small></div>
				<div class="ui buttons">
					<button id="donut_0" class="mini ui primary button">Répartition</button>
					<button id="donut_1" class="mini ui grey button">Secteurs</button>
					<button id="donut_2" class="mini ui grey button">Géographie</button>
				</div>
				<canvas id="pft_donut" height="130" style="margin-top: 10px"></canvas>
			</div>
		</div>
	</div>

	<? echo "MVVR: ".sprintf("%.2f%%", $portfolio_data['mvvr']); ?>

	<div class="ui hidden divider"></div>

	<h2 class="ui left floated">
		<i class="inverted location arrow icon"></i>Positions
		<button id="ptf_pos_sync_bt" class="circular ui icon very small right floated darkgray labelled button"><i class="inverted black sync icon"></i></button>
	</h2>
	<div class="ui stackable column grid">
		<div class="row">
			<div class="column">
				<table class="ui selectable inverted single line unstackable very compact sortable-theme-minimal table" id="lst_position" data-sortable>
					<thead><? echo QuoteComputing::getHtmlTableHeader(); ?></thead>
					<tbody>
						<?
						$i = 1;
						ksort($lst_positions);
						$div_per_year = 0;
						foreach ($lst_positions as $key => $val) {

							$qc = new QuoteComputing($sc, $key);
							echo $qc->getHtmlTableLine($i++);
							$div_per_year += $qc->getEstimationDividende();    // Estimation dividende annuel

						}
						?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="6">
								</th>
							<td></td>
							<td></td>
							<td id="sum_valo" class="right aligned"></td>
							<td id="glob_perf" class="center aligned"></td>
							<td></td>
							<td></td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>

	<div class="ui hidden divider"></div>

	<h2 class="ui left floated"><i class="inverted history icon"></i>Historique ordres
		<button id="order_add_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white add icon"></i></button>
		<button id="order_filter_bt" class="circular ui icon very small right floated darkgray labelled button"><i class="inverted black filter icon"></i></button>
	</h2>
	<div id="filters" class="ui inverted form six fields filters" style="display: inline-flex;">
		<div class="field">
			<label>Date</label>
			<div class="ui right icon inverted left labeled fluid input">
				<input type="text" size="10" id="f_date" value="" placeholder="0000-00-00">
				<i class="inverted black calendar alternate outline icon"></i>
			</div>
		</div>
		<div class="field">
			<label>Actif</label>
			<select id="f_product_name" class="ui dropdown">
				<option value="">All</option>
				<option value="Cash">Cash</option>
				<? foreach ($sc->getListActifsAchetes() as $key => $val) echo "<option value=\"".$key."\">".$val."</option>"; ?>
				<option value="other">Autre</option>
			</select>
		</div>
		<div class="field">
			<label>Action</label>
			<select id="f_action" class="ui dropdown">
				<option value="">All</option>
				<option value="achatvente">Achat/Vente</option>
				<? foreach (uimx::$order_actions as $key => $val) { ?>
					<option value="<?= $val ?>"><?= $val ?></option>
				<? } ?>
			</select>
		</div>
		<div class="field">
			<label>&nbsp;</label>
			<div id="filter_go_bt" class="ui floated right blue submit button">Filter</div>
		</div>
	</div>

	<div class="ui stackable column grid">
		<div class="row">
			<div class="column">
				<table class="ui striped selectable inverted single line unstackable very compact table sortable-theme-minimal" id="lst_order" data-sortable>
					<thead>
						<tr>
							<th>Date</th>
							<th>Ptf</th>
							<th>Actif</th>
							<th>Action</th>
							<th>Qté</th>
							<th>Prix</th>
							<th>Total</th>
							<th>Comm/TTF</th>
							<th>+/-</th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?
						foreach (array_merge(array_reverse($lst_orders_futur), array_reverse($lst_orders)) as $key => $val) {

							$val = calc::formatDataOrder($val);
							$td_gain = '<td>-</td>';
							if ($val['action'] == -1 && $val['pru']) {
								$order_gain = ($val['price'] - $val['pru']) * $val['quantity'];
								$td_gain = '<td data-value="' . $order_gain . '" class="' . ($val['price'] > $val['pru'] ? "aaf-positive" : "aaf-negative") . '">' . ($val['price'] > $val['pru'] ? "+" : "") . sprintf("%.2f", $order_gain) . uimx::getCurrencySign($val['devise']) . '</td>';
							}

							echo '<tr data-value="'.$val['product_name'].'">
						<td data-value="' . $val['date'] . '"><i class="inverted ' . str_replace(["left", "right"], ["sign out", "sign in"], $val['icon']) . ' icon"></i> ' . $val['date'] . '</td>
						<td>' . $val['shortname'] . '</td>
						<td>' . QuoteComputing::getQuoteNameWithoutExtension($val['product_name']) . '</td>
						<td class="center aligned">' . $val['action_lib'] . '</td>
						<td data-value="' . $val['quantity'] . '">' . $val['quantity'] . '</td>
						<td data-value="' . $val['price'] . '">' . $val['price_signed'] . '</td>
						<td data-value="' . sprintf("%.2f", $val['valo']) . '" class="' . $val['action_colr'] . '">' . $val['valo_signed'] . '</td>
						<td data-value="' . $val['commission'] . '">' . sprintf("%.2f", $val['commission']) . '&euro;/' . sprintf("%.2f", $val['ttf']) . '</td>
						' . $td_gain . '
						<td data-value="' . $val['confirme'] . '"><i class="ui ' . ($val['confirme'] == 1 ? "check green" : "clock outline orange") . ' icon"></i></td>
						<td class="collapsing"><i id="order_edit_' . $val['id'] . '_' . $val['portfolio_id'] . '_bt" class="edit inverted icon"></i></td>
					</tr>';
						}
						?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="6"></td>
							<td class="right aligned"><span id="sum_comm"></span>/<span id="sum_ttf"></span></td>
							<td></td>
					</tfoot>
				</table>
				<div id="lst_order_box"></div>

			</div>
		</div>
	</div>

	<div class="ui grid">
		<div class="wide right aligned column">
			<div id="order_back_bt" class="ui grey submit button">Back</div>
		</div>
	</div>

</div>


<script>
	var myChart = null;
	var ctx = document.getElementById('pft_donut').getContext('2d');

	el("pft_donut").height = document.body.offsetWidth > 700 ? 130 : 300;

	// Listener sur boutons edit orders
	Dom.find('#lst_order i.edit').forEach(function(item) {
		Dom.addListener(item, Dom.Event.ON_CLICK, function(event) {
			let order_attributes = Dom.attribute(item, 'id').split("_");
			if (order_attributes.length > 4)
				go({
					action: 'order',
					id: 'main',
					url: 'order_detail.php?action=upt&portfolio_id=' + order_attributes[3] + '&order_id=' + order_attributes[2],
					loading_area: 'main'
				});
		});
	});

	// Pour donut repartition par actif, secteur, geo 
	var infos_area = {
		n: 1,
		l: [],
		v: []
	};
	var infos_area_bis = {
		n: 2,
		l: [],
		v: []
	};
	var current_infos_area = {};

	update_infos_areas = function(o) {

		current_infos_area = o;

		var x = 0;

		['infos_area1', 'infos_area2'].forEach(function(elt) {

			var area = Dom.id(elt);
			var children = area.children; // fields

			for (var i = 0; i < children.length; i++) {

				if (children[i].nodeType = "div") {
					if (children[i].getElementsByTagName("label").length > 0) {
						if (o.l[x]) children[i].getElementsByTagName("label")[0].innerHTML = o.l[x];
					}
					if (children[i].getElementsByTagName("input").length > 0) {
						if (o.v[x]) children[i].getElementsByTagName("input")[0].value = o.v[x];
					}
					x++;
				}

			}

		});
	}

	updateDataPage = function(opt) {

		glob_perf = 0;
		glob_gain = 0;

		valo_ptf = <?= sprintf("%.2f", $portfolio_data['valo_ptf']) ?>;
		perf_ptf = <?= sprintf("%.2f", $portfolio_data['perf_ptf']) ?>;
		cash = <?= sprintf("%.2f", $portfolio_data['cash']) ?>;
		ampplt = <?= sprintf("%.2f", $portfolio_data['ampplt']) ?>;
		gain_perte = <?= sprintf("%.2f", $portfolio_data['gain_perte']) ?>;
		retraits = <?= sprintf("%.2f", $portfolio_data['retrait']) ?>;
		depots = <?= sprintf("%.2f", $portfolio_data['depot']) ?>;
		dividendes = <?= sprintf("%.2f", $portfolio_data['dividende']) ?>;
		commissions = <?= sprintf("%.2f", $portfolio_data['commission']) ?>;
		div2depot = <?= sprintf("%.2f", $portfolio_data['depot'] == 0 ? 0 : ($portfolio_data['dividende'] * 100) / $portfolio_data['depot']) ?>;
		ttf = <?= sprintf("%.2f", $portfolio_data['ttf']) ?>;

		if (opt == 'init') {
			infos_area.l[0] = "Estimation Portefeuille";
			infos_area.v[0] = valo_ptf.toFixed(2) + '\u20AC';
			infos_area.l[1] = "Cash disponible";
			infos_area.v[1] = cash.toFixed(2) + '\u20AC';
			infos_area.l[2] = "+/- Value";
			infos_area.v[2] = gain_perte.toFixed(2) + '\u20AC';
			infos_area.l[3] = "&sum; Dépots";
			infos_area.v[3] = depots.toFixed(2) + '\u20AC';
			infos_area.l[4] = "&sum; Retraits";
			infos_area.v[4] = retraits.toFixed(2) + '\u20AC';
			infos_area.l[5] = "&sum; Dividendes (Div to Depot)";
			infos_area.v[5] = dividendes.toFixed(2) + '\u20AC' + ' (' + div2depot.toFixed(2) + '%)';
			setColNumericTab('sum_comm', commissions, commissions.toFixed(2) + '&euro;');
			setColNumericTab('sum_ttf', ttf, ttf.toFixed(2) + '&euro;');
			Dom.id('subtitle').innerHTML = ' (<?= $portfolio_data['interval_year'] > 0 ? $portfolio_data['interval_year'] . ($portfolio_data['interval_year'] > 1 ? " ans " : " an") : "" ?> <?= $portfolio_data['interval_month'] ?> mois)';
		}

		// On parcours les lignes du tableau positions pour calculer valo, perf, gain, atio et des tooltip du tableau des positions
		trendfollowing_ui.computePositionsTable('lst_position', <?= $portfolio_id ?>);

		glob_perf = getPerf(trendfollowing_ui.ptf.achats, trendfollowing_ui.ptf.valo);
		glob_gain = trendfollowing_ui.ptf.valo - trendfollowing_ui.ptf.achats;

		setColNumericTab('sum_valo', trendfollowing_ui.ptf.valo, trendfollowing_ui.ptf.valo.toFixed(2) + '&euro;');
		setColNumericTab('glob_perf', glob_perf, '<div><button class="tiny ui ' + (glob_perf >= 0 ? 'aaf-positive' : 'aaf-negative') + ' button">' + glob_perf.toFixed(2) + '%</button><label>' + (glob_gain >= 0 ? '+' : '') + glob_gain.toFixed(2) + '&euro;</label></div>');

		addCN('perf_ribbon2', glob_perf >= 0 ? "ribbon--green" : "ribbon--red");
		Dom.find('#perf_ribbon2 small')[0].innerHTML = glob_perf.toFixed(2) + '%';

		addCN('perf_ribbon3', perf_ptf >= 0 ? "ribbon--green" : "ribbon--red");
		Dom.find('#perf_ribbon3 small')[0].innerHTML = perf_ptf.toFixed(2) + '%';

		perf_stoploss1 = getPerf(valo_ptf, trendfollowing_ui.ptf.stoploss1).toFixed(2);
		perf_objectif = getPerf(valo_ptf, trendfollowing_ui.ptf.objectif).toFixed(2);
		perf_stopprofit = getPerf(valo_ptf, trendfollowing_ui.ptf.stopprofit).toFixed(2);

		infos_area_bis.l[0] = 'Estimation Stop Loss <a class="ui mini ' + (perf_stoploss1 >= 0 ? 'green' : 'red') + ' tag label">' + perf_stoploss1 + '%</a>';
		infos_area_bis.v[0] = trendfollowing_ui.ptf.stoploss1.toFixed(2) + '\u20AC';
		infos_area_bis.l[1] = 'Estimation Objectif <a class="ui mini ' + (perf_objectif >= 0 ? 'green' : 'red') + ' tag label">' + perf_objectif + '%</a>';
		infos_area_bis.v[1] = trendfollowing_ui.ptf.objectif.toFixed(2) + '\u20AC';
		infos_area_bis.l[2] = 'Estimation Stop Profit <a class="ui mini ' + (perf_stopprofit >= 0 ? 'green' : 'red') + ' tag label">' + perf_stopprofit + '%</a>';
		infos_area_bis.v[2] = trendfollowing_ui.ptf.stopprofit.toFixed(2) + '\u20AC';
		infos_area_bis.l[3] = "&sum; Dépots";
		infos_area_bis.v[3] = depots.toFixed(2) + '\u20AC';
		infos_area_bis.l[4] = 'Couverture Stop Loss';
		infos_area_bis.v[4] = trendfollowing_ui.ptf.stoploss2.toFixed(2) + '\u20AC';
		infos_area_bis.l[5] = 'Estimation dividende annuel (Div to Depot)';
		infos_area_bis.v[5] = '<?= sprintf("%.2f", $div_per_year) ?>\u20AC' + ' ( <?= sprintf("%.2f", $portfolio_data['depot'] == 0 ? 0 : ($div_per_year * 100) / $portfolio_data['depot']) ?>%)';

		const data_donut = {
			labels: trendfollowing_ui.labels_repartition[0],
			datasets: [{
				label: 'Répartition',
				data: trendfollowing_ui.data_repartition[0],
				borderWidth: 0.5,
				backgroundColor: trendfollowing_ui.bg_repartition[0],
				borderWidth: 4,
				borderColor: "#222",
				hoverOffset: 4
			}]
		};

		if (myChart) myChart.destroy();
		options_donut_graphe.plugins.legend.display = window.innerWidth < 600 ? false : true;
		myChart = new Chart(ctx, {
			type: 'doughnut',
			data: data_donut,
			options: options_donut_graphe
		});
		myChart.update();

	}('init');

	// Filtre de la table des ordres
	filter = function() {

		paginator({
			table: document.getElementById("lst_order"),
			box: document.getElementById("lst_order_box"),
			get_rows: get_orders_list
		});

		hide('filters');
	}

	// Listener sur les boutons ADD, BALANCE, BACK, etc ...
	Dom.addListener(Dom.id('order_add_bt'), Dom.Event.ON_CLICK, function(event) {
		go({
			action: 'order',
			id: 'main',
			url: 'order_detail.php?action=new&portfolio_id=<?= $portfolio_id ?>',
			loading_area: 'main'
		});
	});
	Dom.addListener(Dom.id('ptf_balance_bt'), Dom.Event.ON_CLICK, function(event) {
		overlay.load('portfolio_balance.php', {
			'portfolio_id': <?= $portfolio_id ?>
		});
	});
	Dom.addListener(Dom.id('order_back_bt'), Dom.Event.ON_CLICK, function(event) {
		go({
			action: 'portfolio',
			id: 'main',
			url: 'portfolio.php',
			loading_area: 'main'
		});
	});
	Dom.addListener(Dom.id('portfolio_graph_bt'), Dom.Event.ON_CLICK, function(event) {
		//	go({ action: 'portfolio', id: 'main', url: 'portfolio_graph.php?portfolio_id=<?= $portfolio_id ?>', loading_area: 'main' });
		overlay.load('portfolio_graph.php', {
			'portfolio_id': <?= $portfolio_id ?>
		});
	});
	Dom.addListener(Dom.id('portfolio_switch_bt'), Dom.Event.ON_CLICK, function(event) {
		update_infos_areas(current_infos_area.n == 1 ? infos_area_bis : infos_area);
	});
	Dom.addListener(Dom.id('order_filter_bt'), Dom.Event.ON_CLICK, function(event) {
		toogle('filters')
	});
	Dom.addListener(Dom.id('filter_go_bt'), Dom.Event.ON_CLICK, function(event) {
		filter()
	});
	Dom.addListener(Dom.id('ptf_pos_sync_bt'), Dom.Event.ON_CLICK, function(event) {
		toogleCN('lst_position', 'alternate');
		toogleCN('ptf_pos_sync_bt', 'on');
	});

	// Gestion des boutons du graphe donut
	changeButtonState = function(bt) {
		['0', '1', '2'].forEach(function(item) {
			replaceCN('donut_' + item, 'primary', 'grey');
		});
		replaceCN('donut_' + bt, 'grey', 'primary');
	}
	// Maj data donut
	updateDonut = function(bt) {
		let i = parseInt(bt);
		changeButtonState(bt);
		myChart.config.data.datasets[0].data = trendfollowing_ui.data_repartition[i];
		myChart.config.data.labels = trendfollowing_ui.labels_repartition[i];
		myChart.config.data.backgroundColor = trendfollowing_ui.bg_repartition[i];
		myChart.update();
	}

	// N'aime pas le ['0', '1', '2'].forEach() !!! 
	const list = ['0', '1', '2'];
	list.forEach(function(element) {
		Dom.addListener(Dom.id('donut_' + element), Dom.Event.ON_CLICK, function(event) {
			updateDonut(element);
		});
	});

	get_orders_list = function() {

		let filter_date = valof('f_date');
		let filter_product_name = valof('f_product_name');
		let filter_action = valof('f_action');

		var table = document.getElementById("lst_order");
		var tbody = table.getElementsByTagName("tbody")[0] || table;

		children = tbody.children;
		var trs = [];
		for (var i = 0; i < children.length; i++) {
			if (children[i].nodeType = "tr") {

				var mytds = children[i].getElementsByTagName("td");

				if (mytds.length > 0) {

					let hide_line = false;

					if (filter_date && mytds[0].childNodes[1].nodeValue.trim().toLowerCase() != filter_date.toLowerCase())
						hide_line = true;

					if (filter_product_name && children[i].getAttributeNode("data-value").value != filter_product_name)
						hide_line = true;

					if (filter_action && filter_action.toLowerCase() != "achatvente" && mytds[3].innerHTML.toLowerCase() != filter_action.toLowerCase())
						hide_line = true;

					if (filter_action && filter_action.toLowerCase() == "achatvente" && (mytds[3].innerHTML.toLowerCase() != "achat" && mytds[3].innerHTML.toLowerCase() != "vente"))
						hide_line = true;

					if (hide_line)
						children[i].style.display = "none";
					else
						trs.push(children[i]);
				}
			}
		}

		return trs;
	}

	// Show/hide portfolio select menu
	hide('ptf_menu');
	Dom.addListener(Dom.id('ptf_select'), Dom.Event.ON_CLICK, function(event) {
		toogle('ptf_menu');
	});

	// Listener sur click lien menu ptf
	Dom.find('#ptf_menu .ptf_item').forEach(function(item) {
		Dom.addListener(item, Dom.Event.ON_CLICK, function(event) {
			go({ action: 'portfolio', id: 'main', url: 'portfolio_dashboard.php?portfolio_id=' + Dom.attribute(item, 'data-value'), loading_area: 'main' });
		});
	});

	// Mise a jour de la zone infos ptf
	update_infos_areas(infos_area);

	// Pagination
	paginator({
		table: document.getElementById("lst_order"),
		box: document.getElementById("lst_order_box"),
		get_rows: get_orders_list
	});

	// Aide a la sasie date
	const datepicker1 = new TheDatepicker.Datepicker(el('f_date'));
	datepicker1.options.setInputFormat("Y-m-d")
	datepicker1.render();

	// Tri sur tableau
	Sortable.initTable(el("lst_position"));
	Sortable.initTable(el("lst_order"));

	// On cache les fitres de selection de la liste des ordres passes
	hide("filters");

</script>