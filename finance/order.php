<?

require_once "sess_context.php";

session_start();

include "common.php";

$portfolio_id = 0;

foreach(['portfolio_id'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) {
	uimx::staticInfoMsg("VOUS DEVEZ ETRE CONNECTE POUR UTILISER CETTE FONCTIONNALITE", "comment outline", "blue");
	exit(0);
}

// Recuperation de tous les actifs
$quotes = calc::getIndicatorsLastQuote();

// Calcul synthese portefeuille
$portfolio_data = calc::aggregatePortfolio($portfolio_id, $quotes);

// On récupère les infos du portefeuille + les positions et les ordres
$my_portfolio  = $portfolio_data['infos'];
$lst_positions = $portfolio_data['positions'];
$lst_orders    = $portfolio_data['orders'];

?>

<div class="ui container inverted segment">

	<h2 class="ui left floated">
		<i class="inverted black briefcase icon"></i>&nbsp;&nbsp;<?= $my_portfolio['name'] ?><small id="subtitle"></small>
	</h2>
	<div class="ui stackable column grid container">
		<div class="row">
			<div class="ten wide column">
				<h3>Synthèse</h3>
				<div class="ui stackable two column grid container">
					<div class="row">

						<div class="ui inverted column readonly form">
							<div class="field">
								<label>Estimation Portefeuille</label>
								<div class="field">
									<input id="f_valo_ptf" type="text" value="0 &euro;" readonly="" />
								</div>
							</div>
							<div class="field">
								<label>Cash disponible</label>
								<div class="field">
									<input type="text" value="<?= sprintf("%.2f &euro;", $portfolio_data['cash']) ?>" readonly="" />
								</div>
							</div>
							<div class="field">
								<label>Gain/Perte</label>
								<div class="field">
									<input id="gain_perte" type="text" value="0 &euro;" readonly="" />
								</div>
							</div>
						</div>

						<div class="ui inverted column readonly form">
							<div class="field">
								<label>&sum; Dépots</label>
								<div class="field">
									<input id="depots" type="text" value="0 &euro;" readonly="" />
								</div>
							</div>
							<div class="field">
								<label>&sum; Retraits / &sum; Transferts</label>
								<div class="field">
									<input id="retraits_transferts" type="text" value="0x &euro; / 0y &euro;" readonly="" />
								</div>
							</div>
							<div class="field">
								<label>&sum; Dividendes</label>
								<div class="field">
									<input id="dividendes" type="text" value="0 &euro;" readonly="" />
								</div>
							</div>
						</div>

					</div>
				</div>
				<!-- div id="perf_ribbon1" style="right: 2rem; height: 5rem !important" class="ribbon">Perf<br /><small>0 %</small></div> -->
				<div id="perf_ribbon3" style="right: 2rem; height: 5rem !important" class="ribbon">Perf<br /><small>0 %</small></div>
			</div>
			<div class="six wide column" style="background: #222; border-bottom-right-radius: 50px; border-bottom: 1px solid grey;">
				<h3 class="ui left floated">Répartition</h3>
				<div id="perf_ribbon2" style="height: 5rem !important" class="ribbon">Perf<br /><small>0 %</small></div>
				<canvas id="pft_donut" height="100"></canvas>
			</div>
		</div>
	</div>

	<div class="ui stackable column grid container">
      	<div class="row">
			<div class="column">
				<h3 class="ui left floated">Positions</h3>
				<table class="ui selectable inverted single line unstackable very compact table sortable-theme-minimal" id="lst_position" data-sortable>
					<thead><tr>
						<th>Actif</th>
						<th class="center aligned">Qté</th>
						<th class="center aligned">PRU</th>
						<th class="center aligned">Cotation</th>
						<th class="center aligned">% jour</th>
						<th class="center aligned">Achat</th>
						<th class="center aligned">Valorisation</th>
						<th class="center aligned" colspan="2">Performance</th>
					</tr></thead>
					<tbody>
	<?
				$i = 1;
				foreach($lst_positions as $key => $val) {
					$achat = sprintf("%.2f", $val['nb'] * $val['pru']);
					// Si on n'a pas la cotation en base on prend le pru
					$quote = isset($quotes['stocks'][$key]['price'])   ? $quotes['stocks'][$key]['price']   : $val['pru'];
					$pct   = isset($quotes['stocks'][$key]['percent']) ? $quotes['stocks'][$key]['percent'] : 0;
					$valo  = sprintf("%.2f", $val['nb'] * $quote);
					$perf  = round($achat != 0 ? (($valo - $achat) * 100) / $achat : 0, 2);
					echo '<tr id="tr_item_'.$i.'">
						<td id="f_actif_'.$i.'">'.$key.'</td>
						<td class="right aligned" id="f_nb_'.$i.'">'.$val['nb'].'</td>
						<td class="right aligned" id="f_pru_'.$i.'" data-pru="'.sprintf("%.2f", $val['pru']).'">'.sprintf("%.2f", $val['pru']).' &euro;</td>
						<td class="right aligned"><div class="ui right labeled input">
							<input id="f_price_'.$i.'" type="text" class="align_right" size="4" value="'.sprintf("%.2f", $quote).'" />
							<div class="ui basic label">&euro;</div>
						<td id="f_pct_jour_'.$i.'" class="align_right '.($pct >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $pct).' %</td>
						<td id="f_achat_'.$i.'" class="right aligned"></td>
						<td id="f_valo_'.$i.'"  class="right aligned"></td>
						<td id="f_perf_pru_'.$i.'" class="right aligned"></td>
						<td id="f_gain_pru_'.$i.'" class="right aligned"></td>
					</tr>';
					$i++;
				}
	?>
					</tbody>
					<tfoot><tr>
						<td colspan="5"></th>
						<td id="sum_achat" class="right aligned"></td>
						<td id="sum_valo"  class="right aligned"></td>
						<td id="glob_perf" class="right aligned"></td>
						<td id="glob_gain" class="right aligned"></td>
					</tr></tfoot>
				</table>
			</div>
		</div>
	</div>

	<div class="ui stackable column grid container">
      	<div class="row">
			<div class="column">
				<h3 class="ui left floated">
					Historique ordres
					<button id="order_add_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white add icon"></i> Ordre</button>
				</h3>
				<table class="ui striped selectable inverted single line unstackable very compact table sortable-theme-minimal" id="lst_order" data-sortable>
					<thead><tr>
						<th></th>
						<th>Date</th>
						<th>Actif</th>
						<th>Action</th>
						<th>Qté</th>
						<th>Prix</th>
						<th>Total</th>
						<th>Comm</th>
						<th></th>
					</tr></thead>
					<tbody>
	<?
				foreach($lst_orders as $key => $val) {
					echo '<tr>
						<td><i class="inverted long arrow alternate '.($val['action'] >= 0 ? "right green" : "left orange").' icon"></i></td>
						<td>'.$val['date'].'</td>
						<td>'.$val['product_name'].'</td>
						<td class="center aligned">'.uimx::$order_actions[$val['action']].'</td>
						<td>'.$val['quantity'].'</td>
						<td>'.sprintf("%.2f", $val['price']).' &euro;</td>
						<td>'.sprintf("%.2f", $val['quantity'] * $val['price']).' &euro;</td>
						<td>'.sprintf("%.2f", $val['commission']).' &euro;</td>
						<td class="collapsing">
							<i id="order_edit_'.$val['id'].'_bt" class="edit inverted icon"></i>
						</td>
					</tr>';
				}
	?>
					</tbody>
					<tfoot><tr>
						<td colspan="7"></th>
						<td id="sum_comm" class="right aligned"></td>
						<td></th>
					</tr></tfoot>
				</table>
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

var myChart
var ctx = document.getElementById('pft_donut').getContext('2d');

el("pft_donut").height = document.body.offsetWidth > 700 ? 200 : 300;

var options = {
    responsive: false,
    maintainAspectRatio: true,
	plugins: {
            legend: {
                display: true,
				position: 'right'
            }
        },
	};

<?
	foreach($lst_orders as $key => $val) { ?>
		Dom.addListener(Dom.id('order_edit_<?= $val['id'] ?>_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'order', id: 'main', url: 'order_detail.php?action=upt&portfolio_id=<?= $portfolio_id ?>&order_id=<?= $val['id'] ?>', loading_area: 'main' }); });
<?	
	}
?>

setColTab = function(id, val, opt) {
	rmCN(id, "aaf-positive");
	rmCN(id, "aaf-negative");
	addCN(id, val >= 0 ? "aaf-positive" : "aaf-negative");
	Dom.id(id).innerHTML = val.toFixed(2) + opt;
}

computeLines = function(opt) {

	sum_achat = 0;
	sum_valo  = 0;
	glob_perf = 0;
	glob_gain = 0;
	const actifs_labels = [];
	const actifs_data = [];
	const actifs_bg = [];

	valo_ptf   = <?= sprintf("%.2f", $portfolio_data['valo_ptf']) ?>;
	cash       = <?= sprintf("%.2f", $portfolio_data['cash']) ?>;
	ampplt     = <?= sprintf("%.2f", $portfolio_data['ampplt']) ?>;
	gain_perte = <?= sprintf("%.2f", $portfolio_data['gain_perte']) ?>;
	retraits   = <?= sprintf("%.2f", $portfolio_data['retrait']) ?>;
	depots     = <?= sprintf("%.2f", $portfolio_data['depot']) ?>;
	dividendes = <?= sprintf("%.2f", $portfolio_data['dividende']) ?>;
	transferts_in  = <?= sprintf("%.2f", $portfolio_data['transfert_in']) ?>;
	transferts_out = <?= sprintf("%.2f", $portfolio_data['transfert_out']) ?>;
	transferts = transferts_in - transferts_out;
	commissions = <?= sprintf("%.2f", $portfolio_data['commission']) ?>;

	if (opt == 'init') {
		setInputValueAndKeepLastCar('f_valo_ptf', valo_ptf.toFixed(2));
		setInputValueAndKeepLastCar('gain_perte', gain_perte.toFixed(2));
		Dom.id('depots').value = Dom.id('depots').value.replace("0", depots.toFixed(2));
		Dom.id('dividendes').value = Dom.id('dividendes').value.replace("0", dividendes.toFixed(2));
		Dom.id('retraits_transferts').value = Dom.id('retraits_transferts').value.replace("0x", retraits.toFixed(2));
		Dom.id('retraits_transferts').value = Dom.id('retraits_transferts').value.replace("0y", transferts.toFixed(2));
		setColTab('sum_comm', commissions, ' &euro;');
		Dom.id('subtitle').innerHTML = ' (<?= $portfolio_data['interval_year'] > 0 ? $portfolio_data['interval_year'].($portfolio_data['interval_year'] > 1 ? " ans " : " an") : "" ?> <?= $portfolio_data['interval_month'] ?> mois)';
	}

	Dom.find('#lst_position tbody tr').forEach(function(item) {

		ind = Dom.attribute(item, 'id').split('_')[2];

		actif    = el('f_actif_' + ind).innerHTML;
		pru      = parseFloat(Dom.attribute(Dom.id('f_pru_' + ind), 'data-pru'));
		price    = parseFloat(Dom.attribute(Dom.id('f_price_' + ind), 'value'));
		nb       = parseFloat(el('f_nb_' + ind).innerHTML);
		achat    = parseFloat(nb * pru);
		valo     = parseFloat(nb * price);
		perf_pru = parseFloat(getPerf(pru, price));
		gain_pru = parseFloat(nb * (price - pru));

		actifs_labels.push(actif);
		sum_achat += achat;
		sum_valo  += valo;

		Dom.id('f_achat_' + ind).innerHTML = achat.toFixed(2) + ' &euro;';
		Dom.id('f_valo_'  + ind).innerHTML = valo.toFixed(2) + ' &euro;';

		setColTab('f_perf_pru_' + ind, perf_pru, ' %');
		setColTab('f_gain_pru_' + ind, gain_pru, ' &euro;');

		if (opt == 'change') {
			Dom.id('f_pct_jour_'  + ind).innerHTML = 'N/A';
			setCN('f_pct_jour_'  + ind, "align_right");
		}
	});

	Dom.find('#lst_position tbody tr').forEach(function(item) {

		ind = Dom.attribute(item, 'id').split('_')[2];

		price = parseFloat(Dom.attribute(Dom.id('f_price_' + ind), 'value'));
		nb    = parseFloat(el('f_nb_' + ind).innerHTML);
		valo  = parseFloat(nb * price);

		actifs_data.push(getRatio(sum_valo, valo).toFixed(2));
	});

	glob_perf = getPerf(sum_achat, sum_valo);
	glob_gain = sum_valo - sum_achat;
	estimation_valo = valo_ptf;

	if (actifs_data.length > 0) {

		setColTab('sum_achat', sum_achat, ' &euro;');
		setColTab('sum_valo',  sum_valo,  ' &euro;');
		setColTab('glob_perf', glob_perf, ' %');
		setColTab('glob_gain', glob_gain, ' &euro;');

		addCN('perf_ribbon2', glob_perf >= 0 ? "ribbon--green" : "ribbon--red");
		Dom.find('#perf_ribbon2 small')[0].innerHTML = glob_perf.toFixed(2) + ' %';

		estimation_valo = cash + sum_valo;
		gain_perte = estimation_valo - depots - transferts_in;

		setInputValueAndKeepLastCar('f_valo_ptf', estimation_valo.toFixed(2));
		setInputValueAndKeepLastCar('gain_perte', gain_perte.toFixed(2));
	}

	// RIBBON
	// perf_ptf = getPerf(depots, estimation_valo);
	// addCN('perf_ribbon1', perf_ptf >= 0 ? "ribbon--green" : "ribbon--red");
	// Dom.find('#perf_ribbon1 small')[0].innerHTML = perf_ptf.toFixed(2) + ' %';
	perf_ptf2 = (gain_perte / ampplt) * 100;
	addCN('perf_ribbon3', perf_ptf2 >= 0 ? "ribbon--green" : "ribbon--red");
	Dom.find('#perf_ribbon3 small')[0].innerHTML = perf_ptf2.toFixed(2) + ' %';


	if (actifs_data.length == 0) {
		actifs_data.push(100);
		actifs_labels.push('None');
		actifs_bg.push('rgb(200, 200, 200)');
	} else {
		['rgb(54,  162, 235)',
			'rgb(255, 205, 86)',
			'rgb(255, 99,  132)',
			'rgb(238, 130, 6)',
			'rgb(97,  194, 97)',
			'rgb(255, 153, 255)',
			'rgb(153, 51,  51)',
			'rgb(204, 230, 255)',
			'rgb(209, 179, 255)' ].forEach((item) => { actifs_bg.push(item); });
	}

	const data = {
		labels: actifs_labels,
		datasets: [{
			label: 'Répartition',
			data: actifs_data,
			borderWidth: 0.5,
			backgroundColor: actifs_bg,
			hoverOffset: 4
		}]
	};

	if (myChart) myChart.destroy();
	myChart = new Chart(ctx, { type: 'doughnut', data: data, options: options } );
	mychart.update();
}

Dom.find('#lst_position tbody td:nth-child(4) input').forEach(function(item) {
	Dom.addListener(item, Dom.Event.ON_CHANGE, function(event) {
		computeLines('change');
	});
});


Dom.addListener(Dom.id('order_add_bt'),  Dom.Event.ON_CLICK, function(event) { go({ action: 'order', id: 'main', url: 'order_detail.php?action=new&portfolio_id=<?= $portfolio_id ?>', loading_area: 'main' }); });
Dom.addListener(Dom.id('order_back_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'portfolio', id: 'main', url: 'portfolio.php', loading_area: 'main' }); });

Sortable.initTable(el("lst_position"));
Sortable.initTable(el("lst_order"));

computeLines('init');

</script>