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

// Calcul synthese portefeuille
$portfolio_data = calc::aggregatePortfolio($portfolio_id);

// Recuperation de tous les actifs
$quotes = calc::getIndicatorsLastQuote();

// On récupère les infos du portefeuille + les positions et les ordres
$my_portfolio  = $portfolio_data['infos'];
$lst_positions = $portfolio_data['positions'];
$lst_orders    = $portfolio_data['orders'];

?>

<div class="ui container inverted segment">

	<h2 class="ui left floated">
		<i class="inverted black briefcase icon"></i>&nbsp;&nbsp;<?= $my_portfolio['name'] ?>
	</h2>

	<div class="ui stackable two column grid container">
		<div class="row">
			<div class="column">
				<h3 class="ui left floated">Répartition</h3>
				<canvas id="pft_donut" height="100"></canvas>
			</div>
			<div class="column">
				<h3 class="ui left floated">Informations</h3>
				<div class="ui stackable two column grid container">
					<div class="row">
						<div class="ui inverted column readonly form">
							<div class="field">
								<label>&sum; Achats</label>
								<div class="field">
								<input type="text" value="<?= sprintf("%.2f &euro;", $portfolio_data['depot']) ?>" readonly="" />
								</div>
							</div>
							<div class="field">
								<label>&sum; Dépots</label>
								<div class="field">
								<input type="text" value="<?= sprintf("%.2f &euro;", $portfolio_data['depot']) ?>" readonly="" />
								</div>
							</div>
							<div class="field">
								<label>&sum; Retraits</label>
								<div class="field">
								<input type="text" value="<?= sprintf("%.2f &euro;", $portfolio_data['retrait']) ?>" readonly="" />
								</div>
							</div>
						</div>
						<div class="ui inverted column readonly form">
							<div class="field">
								<label>Valorisation</label>
								<div class="field">
									<input type="text" value="<?= sprintf("%.2f &euro;", $portfolio_data['dividende']) ?>" readonly="" />
								</div>
							</div>
							<div class="field">
								<label>&sum; Dividendes</label>
								<div class="field">
									<input type="text" value="<?= sprintf("%.2f &euro;", $portfolio_data['dividende']) ?>" readonly="" />
								</div>
							</div>
							<div class="field">
								<label>&sum; Commissions</label>
								<div class="field">
									<input type="text" value="<?= sprintf("%.2f &euro;", $portfolio_data['commission']) ?>" readonly="" />
								</div>
							</div>
						</div>
					</div>
				</div>
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
						<th class="center aligned" colspan="2">Perf PRU</th>
					</tr></thead>
					<tbody>
	<?
				$i = 1;
				foreach($lst_positions as $key => $val) {
					$achat = sprintf("%.2f", $val['nb'] * $val['pru']);
					$valo  = sprintf("%.2f", $val['nb'] * $quotes['stocks'][$key]['price']);
					$perf  = round($achat != 0 ? (($valo - $achat) * 100) / $achat : 0, 2);
					echo '<tr id="tr_item_'.$i.'">
						<td id="f_actif_'.$i.'">'.$key.'</td>
						<td class="right aligned" id="f_nb_'.$i.'">'.$val['nb'].'</td>
						<td class="right aligned" id="f_pru_'.$i.'" data-pru="'.sprintf("%.2f", $val['pru']).'">'.sprintf("%.2f", $val['pru']).' &euro;</td>
						<td class="right aligned"><div class="ui right labeled input">
							<input id="f_price_'.$i.'" type="text" class="align_right" size="4" value="'.$quotes['stocks'][$key]['price'].'" />
							<div class="ui basic label">&euro;</div>
						<td id="f_pct_jour_'.$i.'" class="align_right '.($quotes['stocks'][$key]['percent'] >= 0 ? "aaf-positive" : "aaf-negative").'">'.$quotes['stocks'][$key]['percent'].' %</td>
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
						<td>'.uimx::$order_actions[$val['action']].'</td>
						<td>'.$val['quantity'].'</td>
						<td>'.sprintf("%.2f", $val['price']).' &euro;</td>
						<td>'.sprintf("%.2f", $val['quantity'] * $val['price']).' &euro;</td>
						<td>'.$val['commission'].' &euro;</td>
						<td class="collapsing">
							<i id="order_edit_'.$val['id'].'_bt" class="edit inverted icon"></i>
						</td>
					</tr>';
				}
	?>
					</tbody>
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

		price    = parseFloat(Dom.attribute(Dom.id('f_price_' + ind), 'value'));
		nb       = parseFloat(el('f_nb_' + ind).innerHTML);
		valo     = parseFloat(nb * price);

		actifs_data.push(getRatio(sum_valo, valo).toFixed(2));
	});

	glob_perf = getPerf(sum_achat, sum_valo);
	glob_gain = sum_valo - sum_achat;

	if (actifs_data.length > 0) {

		setColTab('sum_achat', sum_achat, ' &euro;');
		setColTab('sum_valo',  sum_valo,  ' &euro;');
		setColTab('glob_perf', glob_perf,  ' %');
		setColTab('glob_gain', glob_gain,  ' &euro;');

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

	if (actifs_data.length == 0) {
		actifs_data.push(100);
		actifs_labels.push('None');
		actifs_bg.push('rgb(200, 200, 200)');
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