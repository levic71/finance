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

// Recuperation des infos du portefeuille
$req = "SELECT * FROM portfolios WHERE id=".$portfolio_id." AND user_id=".$sess_context->getUserId();
$res = dbc::execSql($req);

// Bye bye si inexistant
if (!$row = mysqli_fetch_assoc($res)) exit(0);

// Recuperation de tous les actifs
$quotes = calc::getIndicatorsLastQuote();

// Calcul synthese portefeuille
$portfolio_data = calc::aggregatePortfolio($portfolio_id, $quotes);

// Portfolio synthese ?
$isPortfolioSynthese = $portfolio_data['infos']['synthese'] == 1 ? true : false;

// On recupere les eventuelles saisies de cotation manuelles
$save_quotes = array();
$t = explode(',', $portfolio_data['infos']['quotes']);
if ($t[0] != '') {
	foreach($t as $key => $val) {
		$x = explode('|', $val);
		$save_quotes[$x[0]] = $x[1];
	}
}

// On r�cup�re les infos du portefeuille + les positions et les ordres
$my_portfolio  = $portfolio_data['infos'];
$lst_positions = $portfolio_data['positions'];
$lst_orders    = $portfolio_data['orders'];

?>

<div class="ui container inverted segment">

	<h2 class="ui left floated">
		<i class="inverted briefcase icon"></i><?= utf8_decode($my_portfolio['name']) ?><small id="subtitle"></small>
		<button id="portfolio_graph_bt" class=" ui right floated black button"><i class="ui inverted chart bar outline icon"></i></button>
	</h2>
	<div class="ui stackable column grid">
		<div class="row">
			<div class="ten wide column">
				<div class="ui stackable two column grid container">
					<div class="row">

						<div class="ui inverted column readonly form" style="padding-left: 0px;">
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
								<label>+/- Value</label>
								<div class="field">
									<input id="gain_perte" type="text" value="0 &euro;" readonly="" />
								</div>
							</div>
						</div>

						<div class="ui inverted column readonly form">
							<div class="field">
								<label>&sum; D�pots</label>
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
				<!-- div id="perf_ribbon1" style="right: 2rem; height: 5rem !important" class="ribbon">Perf<br /><small>0.00 %</small></div> -->
				<div id="perf_ribbon3" style="right: 2rem; height: 5rem !important" class="ribbon">Perf<br /><small>0.00 %</small></div>
			</div>
			<div class="six wide column" style="background: #222; border-bottom-right-radius: 50px; border-bottom: 1px solid grey;">
				<div id="perf_ribbon2" style="height: 5rem !important" class="ribbon">Perf<br /><small>0.00 %</small></div>
				<canvas id="pft_donut" height="100"></canvas>
			</div>
		</div>
	</div>

	<div class="ui hidden divider"></div>

	<h2 class="ui left floated"><i class="inverted location arrow icon"></i>Positions
		<? if (!$isPortfolioSynthese) { ?>
		<button id="order_save_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white save icon"></i> Save</button>
		<? } ?>
	</h2>
	<div class="ui stackable column grid">
      	<div class="row">
			<div class="column">
				<table class="ui selectable inverted single line unstackable very compact table sortable-theme-minimal" id="lst_position" data-sortable>
					<thead><tr>
						<th class="center aligned">Actif</th>
						<th class="right aligned">Qt�</th>
						<th class="center aligned">PRU</th>
						<th class="center aligned">Cotation</th>
						<th class="right aligned">% jour</th>
						<th class="right aligned">Achat</th>
						<th class="right aligned">Valorisation</th>
						<th class="center aligned">Poids</th>
						<th class="center aligned">Performance</th>
						<th class="center aligned">+/-</th>
					</tr></thead>
					<tbody>
	<?
				$i = 1;
				foreach($lst_positions as $key => $val) {
					$achat = sprintf("%.2f", $val['nb'] * $val['pru']);
					// Si on n'a pas la cotation en base on prend le pru
					$quote_from_pru = isset($quotes['stocks'][$key]['price']) && !isset($save_quotes[$key]) ? false : true;
					$quote = $quote_from_pru ? (isset($save_quotes[$key]) ? $save_quotes[$key] : $val['pru']) : $quotes['stocks'][$key]['price'];
					$pct   = isset($quotes['stocks'][$key]['percent']) ? $quotes['stocks'][$key]['percent'] : 0;
					$valo  = sprintf("%.2f", $val['nb'] * $quote);
					$perf  = round($achat != 0 ? (($valo - $achat) * 100) / $achat : 0, 2);
					$pname = $val['other_name'] ? $key : '<button class="tiny ui primary button">'.$key.'</button>';
					echo '<tr id="tr_item_'.$i.'">
						<td class="center aligned" id="f_actif_'.$i.'" data-pname="'.$key.'">'.$pname.'</td>
						<td class="right aligned" id="f_nb_'.$i.'">'.$val['nb'].'</td>
						<td class="right aligned" id="f_pru_'.$i.'" data-pru="'.sprintf("%.2f", $val['pru']).'">'.sprintf("%.2f", $val['pru']).' &euro;</td>
						<td class="center aligned"><div class="small ui right labeled input">
							<input id="f_price_'.$i.'" type="text" class="align_right" size="4" value="'.sprintf("%.2f", $quote).'" data-name="'.$key.'" data-pru="'.($quote_from_pru ? 1 : 0).'" />
							<div class="ui basic label">&euro;</div>
						<td id="f_pct_jour_'.$i.'" class="align_right '.($pct >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $pct).' %</td>
						<td id="f_achat_'.$i.'" class="right aligned"></td>
						<td id="f_valo_'.$i.'"  class="right aligned"></td>
						<td id="f_poids_'.$i.'"  class="center aligned"></td>
						<td id="f_perf_pru_'.$i.'" class="center aligned"></td>
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
						<td class="center aligned">100 %</td>
						<td id="glob_perf" class="center aligned"></td>
						<td id="glob_gain" class="right aligned"></td>
					</tr></tfoot>
				</table>
			</div>
		</div>
	</div>

	<div class="ui hidden divider"></div>
	
	<h2 class="ui left floated"><i class="inverted history icon"></i>Historique ordres
<? if (!$isPortfolioSynthese) { ?>
	<button id="order_add_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white add icon"></i> Ordre</button>
<? } ?>
	</h2>
	<div class="ui stackable column grid">
      	<div class="row">
			<div class="column">
				<table class="ui striped selectable inverted single line unstackable very compact table sortable-theme-minimal tototo" id="lst_order" data-sortable>
					<thead><tr>
						<th></th>
						<th>Date</th>
						<th>Actif</th>
						<th>Action</th>
						<th>Qt�</th>
						<th>Prix</th>
						<th>Total</th>
						<th>Comm</th>
						<th></th>
					</tr></thead>
					<tbody>
<?
				$hide_save_bt = true;
				foreach($lst_orders as $key => $val) {
					if ($val['other_name']) $hide_save_bt = false;
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
							'.($isPortfolioSynthese ? '' : '<i id="order_edit_'.$val['id'].'_bt" class="edit inverted icon"></i>').'
						</td>
					</tr>';
				}
?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="7"></td>
							<td class="right aligned"><span id="sum_comm"></span></td>
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
	if (!$isPortfolioSynthese) {
		foreach($lst_orders as $key => $val) { ?>
			Dom.addListener(Dom.id('order_edit_<?= $val['id'] ?>_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'order', id: 'main', url: 'order_detail.php?action=upt&portfolio_id=<?= $portfolio_id ?>&order_id=<?= $val['id'] ?>', loading_area: 'main' }); });
<?	
		}
	}
?>

setColTab = function(id, val, opt) {
	rmCN(id, "aaf-positive");
	rmCN(id, "aaf-negative");
	addCN(id, val >= 0 ? "aaf-positive" : "aaf-negative");
	Dom.id(id).innerHTML = val.toFixed(2) + opt;
}
setColTab2 = function(id, val, opt) {
	rmCN(id, "aaf-positive");
	rmCN(id, "aaf-negative");
	addCN(id, val >= 0 ? "aaf-positive" : "aaf-negative");
	Dom.id(id).innerHTML = val + opt;
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

		actif    = Dom.attribute(Dom.id('f_actif_' + ind), 'data-pname');
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

		setColTab2('f_perf_pru_' + ind, '<button class="tiny ui ' + (perf_pru > 0 ? 'aaf-positive' : 'aaf-negative') + ' button">' + perf_pru.toFixed(2) + ' %</button>', '');
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
		ratio = getRatio(sum_valo, valo).toFixed(2);

		Dom.id('f_poids_' + ind).innerHTML = Math.round(ratio) + ' %';

		actifs_data.push(ratio);
	});

	glob_perf = getPerf(sum_achat, sum_valo);
	glob_gain = sum_valo - sum_achat;
	estimation_valo = valo_ptf;

	if (actifs_data.length > 0) {

		setColTab('sum_achat', sum_achat, ' &euro;');
		setColTab('sum_valo',  sum_valo,  ' &euro;');
		setColTab2('glob_perf', '<button class="tiny ui ' + (glob_perf > 0 ? 'aaf-positive' : 'aaf-negative') + ' button">' + glob_perf.toFixed(2) + ' %</button>', '');
		setColTab('glob_gain', glob_gain, ' &euro;');

		addCN('perf_ribbon2', glob_perf >= 0 ? "ribbon--green" : "ribbon--red");
		Dom.find('#perf_ribbon2 small')[0].innerHTML = glob_perf.toFixed(2) + ' %';

		estimation_valo = cash + sum_valo;
		gain_perte = estimation_valo + transferts_out - depots - transferts_in;

		setInputValueAndKeepLastCar('f_valo_ptf', estimation_valo.toFixed(2));
		setInputValueAndKeepLastCar('gain_perte', gain_perte.toFixed(2));
	}

	// RIBBON
	// perf_ptf = getPerf(depots, estimation_valo);
	// addCN('perf_ribbon1', perf_ptf >= 0 ? "ribbon--green" : "ribbon--red");
	// Dom.find('#perf_ribbon1 small')[0].innerHTML = perf_ptf.toFixed(2) + ' %';
	perf_ptf2 = ampplt == 0 ? 0 : (gain_perte / ampplt) * 100;
	addCN('perf_ribbon3', perf_ptf2 >= 0 ? "ribbon--green" : "ribbon--red");
	Dom.find('#perf_ribbon3 small')[0].innerHTML = perf_ptf2.toFixed(2) + ' %';


	if (actifs_data.length == 0) {
		actifs_data.push(100);
		actifs_labels.push('None');
		actifs_bg.push('rgb(200, 200, 200)');
	} else {
		[
			'#118ab2', '#ef476f', '#ffd166', '#06d6a0', '#073b4c'
		].forEach((item) => { actifs_bg.push(item); });
	}

	const data = {
		labels: actifs_labels,
		datasets: [
			{
				label: 'R�partition',
				data: actifs_data,
				borderWidth: 0.5,
				backgroundColor: actifs_bg,
				borderWidth: 4,
				borderColor: "#222",
				hoverOffset: 4
			}
		]
	};

	if (myChart) myChart.destroy();
	myChart = new Chart(ctx, { type: 'doughnut', data: data, options: options } );
	myChart.update();
}

// Listener sur changement de valeur dans tableau des positions
Dom.find('#lst_position tbody td:nth-child(4) input').forEach(function(item) {
	Dom.addListener(item, Dom.Event.ON_CHANGE, function(event) {
		computeLines('change');
	});
});

// Listener sur les boutons ADD et BACK
<? if (!$isPortfolioSynthese) { ?>
Dom.addListener(Dom.id('order_add_bt'),  Dom.Event.ON_CLICK, function(event) { go({ action: 'order', id: 'main', url: 'order_detail.php?action=new&portfolio_id=<?= $portfolio_id ?>', loading_area: 'main' }); });
<? } ?>
Dom.addListener(Dom.id('order_back_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'portfolio', id: 'main', url: 'portfolio.php', loading_area: 'main' }); });
Dom.addListener(Dom.id('portfolio_graph_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'portfolio', id: 'main', url: 'portfolio_graph.php?portfolio_id=<?= $portfolio_id ?>', loading_area: 'main' }); });

// Listener possible sur SAVE et SELECT si pas portfolio synthese 
<? if (!$isPortfolioSynthese) { ?>
Dom.addListener(Dom.id('order_save_bt'), Dom.Event.ON_CLICK, function(event) {
	var quotes = '';
	Dom.find('#lst_position tbody td:nth-child(4) input').forEach(function(item) {
		if (Dom.attribute(item, 'data-pru') == 1) quotes += (quotes == "" ? "" : ",") + Dom.attribute(item, 'data-name') + '|' + valof(item.id);
	});
	go({ action: 'order', id: 'main', url: 'order_action.php?action=save&portfolio_id=<?= $portfolio_id ?>&quotes=' + quotes, no_data: 1, msg: 'Portfolio sauvegard�' });
});

<? if ($hide_save_bt) { ?>
hide('order_save_bt');
<? } ?>

<? } ?>

// Init du calcul
computeLines('init');

// Tri sur tableau
Sortable.initTable(el("lst_position"));
Sortable.initTable(el("lst_order"));

paginator({
  table: document.getElementById("lst_order"),
  box: document.getElementById("lst_order_box")
});
</script>