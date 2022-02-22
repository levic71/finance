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
$isPortfolioSynthese = $portfolio_data['infos']['synthese'];

echo $isPortfolioSynthese;

// On recupere les eventuelles saisies de cotation manuelles
$save_quotes = array();
$t = explode(',', $portfolio_data['infos']['quotes']);
if ($t[0] != '') {
	foreach($t as $key => $val) {
		$x = explode('|', $val);
		$save_quotes[$x[0]] = $x[1];
	}
}

// On récupère les infos du portefeuille + les positions et les ordres
$my_portfolio  = $portfolio_data['infos'];
$lst_positions = $portfolio_data['positions'];
$lst_orders    = $portfolio_data['orders'];
$lst_trend_following = $portfolio_data['trend_following'];

?>

<div class="ui container inverted segment">

	<h2 class="ui left floated">
		<i class="inverted briefcase icon"></i><?= utf8_decode($my_portfolio['name']) ?><small id="subtitle"></small>
		<button id="portfolio_graph_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white chart bar outline icon"></i></button>
	</h2>
	<div class="ui stackable column grid">
		<div class="row">
			<div class="ten wide column">
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
								<label>+/- Value</label>
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
		<? if ($isPortfolioSynthese == 0) { ?>
		<button id="order_save_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white save icon"></i></button>
		<? } ?>
	</h2>
	<div class="ui stackable column grid">
      	<div class="row">
			<div class="column">
				<table class="ui selectable inverted single line unstackable very compact sortable-theme-minimal table" id="lst_position" data-sortable>
					<thead><tr>
						<th class="center aligned">Actif</th>
						<th class="center aligned">Qté</th>
						<th class="center aligned">PRU</th>
						<th class="center aligned">Cotation</th>
						<th class="right aligned">% jour</th>
						<th class="center aligned">Stop/Alerte</th>
						<th class="center aligned">DM</th>
						<th class="center aligned">Tendance</th>
						<th class="center aligned">Poids</th>
						<th class="right aligned">Valorisation</th>
						<th class="center aligned">Performance</th>
					</tr></thead>
					<tbody>
	<?
				$i = 1;
				foreach($lst_positions as $key => $val) {

					// Infos sur actif courant
					$qs = $quotes['stocks'][$key];

					$achat = sprintf("%.2f", $val['nb'] * $val['pru']);
					// Si on n'a pas la cotation en base on prend le pru
					$quote_from_pru = isset($qs['price']) && !isset($save_quotes[$key]) ? false : true;
					$quote = $quote_from_pru ? (isset($save_quotes[$key]) ? $save_quotes[$key] : $val['pru']) : $qs['price'];
					$pct   = isset($qs['percent']) ? $qs['percent'] : 0;
					$valo  = sprintf("%.2f", $val['nb'] * $quote);
					$perf  = round($achat != 0 ? (($valo - $achat) * 100) / $achat : 0, 2);
					$pname = $val['other_name'] ? $key : '<button class="tiny ui primary button">'.$key.'</button>';

					$stop_loss   = isset($lst_trend_following[$key]['stop_loss'])   ? $lst_trend_following[$key]['stop_loss']   : 0;
					$stop_profit = isset($lst_trend_following[$key]['stop_profit']) ? $lst_trend_following[$key]['stop_profit'] : 0;

					$perf_indicator = calc::getPerfIndicator($qs);
					$perf_bullet    = "<span data-tootik-conf=\"left multiline\" data-tootik=\"".uimx::$perf_indicator_libs[$perf_indicator]."\"><a class=\"ui empty ".uimx::$perf_indicator_colrs[$perf_indicator]." circular label\"></a></span>";

					echo '<tr id="tr_item_'.$i.'">
						<td class="center aligned" id="f_actif_'.$i.'" data-pname="'.$key.'">'.$pname.'</td>
						<td class="right  aligned" id="f_nb_'.$i.'">'.$val['nb'].'</td>
						<td class="right  aligned" id="f_pru_'.$i.'" data-pru="'.sprintf("%.2f", $val['pru']).'">'.sprintf("%.2f", $val['pru']).' &euro;</td>
						<td class="center aligned" data-value="'.$quote.'"><div class="small ui right labeled input">
							<input id="f_price_'.$i.'" type="text" class="align_right" size="4" value="'.sprintf("%.2f", $quote).'" data-name="'.$key.'" data-pru="'.($quote_from_pru ? 1 : 0).'" />
							<div class="ui basic label">&euro;</div>
						</div></td>
						<td id="f_pct_jour_'.$i.'" class="align_right '.($pct >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $pct).' %</td>
						<td class="center aligned" data-value="'.$quote.'"><div class="small ui right group input" data-pname="'.$key.'">
							<div class="'.(intval($stop_loss) == 0 ? "grey" : "").' floating ui label">'.sprintf("%.2f", $stop_loss).'</div>
							<div class="'.(intval($stop_profit) == 0 ? "grey" : "").' floating ui label">'.sprintf("%.2f", $stop_profit).'</div>
						</div></td>
						<td id="f_dm_'.$i.'"       class="center aligned '.($qs['DM'] >= 0 ? "aaf-positive" : "aaf-negative").'">'.$qs['DM'].' %</td>
						<td id="f_tendance_'.$i.'" class="center aligned">'.$perf_bullet.'</td>
						<td id="f_poids_'.$i.'"    class="center aligned"></td>
						<td id="f_valo_'.$i.'"     class="right aligned"></td>
						<td id="f_perf_pru_'.$i.'" class="center aligned"></td>
					</tr>';
					$i++;
				}
	?>
					</tbody>
					<tfoot><tr>
						<td colspan="7"></th>
						<td id="sum_achat" class="right aligned"></td>
						<td class="center aligned"></td>
						<td id="sum_valo"  class="right aligned"></td>
						<td id="glob_perf" class="center aligned"></td>
					</tr></tfoot>
				</table>
			</div>
		</div>
	</div>

	<div class="ui hidden divider"></div>
	
	<h2 class="ui left floated"><i class="inverted history icon"></i>Historique ordres
<? if (!$isPortfolioSynthese) { ?>
	<button id="order_add_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white add icon"></i></button>
<? } ?>
	</h2>
	<div class="ui stackable column grid">
      	<div class="row">
			<div class="column">
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
				$hide_save_bt = true;
				foreach(array_reverse($lst_orders) as $key => $val) {
					if ($val['other_name']) $hide_save_bt = false;
					echo '<tr>
						<td><i class="inverted long arrow alternate '.($val['action'] >= 0 ? "right green" : "left orange").' icon"></i></td>
						<td>'.$val['date'].'</td>
						<td>'.$val['product_name'].'</td>
						<td class="center aligned">'.uimx::$order_actions[$val['action']].'</td>
						<td data-value="'.$val['quantity'].'">'.$val['quantity'].'</td>
						<td data-value="'.$val['price'].'">'.sprintf("%.2f", $val['price']).' &euro;</td>
						<td data-value="'.sprintf("%.2f", $val['quantity'] * $val['price']).'">'.sprintf("%.2f", $val['quantity'] * $val['price']).' &euro;</td>
						<td data-value="'.$val['commission'].'">'.sprintf("%.2f", $val['commission']).' &euro;</td>
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
		setColNumericTab('sum_comm', commissions, commissions.toFixed(2) + ' &euro;');
		Dom.id('subtitle').innerHTML = ' (<?= $portfolio_data['interval_year'] > 0 ? $portfolio_data['interval_year'].($portfolio_data['interval_year'] > 1 ? " ans " : " an") : "" ?> <?= $portfolio_data['interval_month'] ?> mois)';
	}

	// On parcours les lignes du tableau positions pour calculer valo, perf, gain, ...
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

		//setColNumericTab('f_achat_' + ind, achat, achat.toFixed(2) + ' &euro;');
		setColNumericTab('f_valo_'  + ind, valo,  valo.toFixed(2)  + ' &euro;');
		setColNumericTab('f_perf_pru_' + ind, perf_pru, '<button class="tiny ui ' + (perf_pru >= 0 ? 'aaf-positive' : 'aaf-negative') + ' button">' + perf_pru.toFixed(2) + ' %</button><label>' + (gain_pru >= 0 ? '+' : '') + gain_pru.toFixed(2) + ' &euro;</label>');
		//setColNumericTab('f_gain_pru_' + ind, gain_pru, gain_pru.toFixed(2) + ' &euro;');

		if (opt == 'change') {
			Dom.id('f_pct_jour_'  + ind).innerHTML = 'N/A';
			setCN('f_pct_jour_'  + ind, "align_right");
		}
	});

	// On reparcours les lignes du tableau positions pour le % de chaque actif dans le portefeuille
	Dom.find('#lst_position tbody tr').forEach(function(item) {

		ind = Dom.attribute(item, 'id').split('_')[2];

		price = parseFloat(Dom.attribute(Dom.id('f_price_' + ind), 'value'));
		nb    = parseFloat(el('f_nb_' + ind).innerHTML);
		valo  = parseFloat(nb * price);
		ratio = getRatio(sum_valo, valo).toFixed(2);

		setColNumericTab('f_poids_' + ind, Math.round(ratio), Math.round(ratio) + ' %', false);

		actifs_data.push(ratio);
	});

	glob_perf = getPerf(sum_achat, sum_valo);
	glob_gain = sum_valo - sum_achat;
	estimation_valo = valo_ptf;

	if (actifs_data.length > 0) {

		// setColNumericTab('sum_achat', sum_achat, sum_achat.toFixed(2) + ' &euro;');
		setColNumericTab('sum_valo',  sum_valo,  sum_valo.toFixed(2)  + ' &euro;');
		setColNumericTab('glob_perf', glob_perf, '<button class="tiny ui ' + (glob_perf >= 0 ? 'aaf-positive' : 'aaf-negative') + ' button">' + glob_perf.toFixed(2) + ' %</button><label>' + (glob_gain >= 0 ? '+' : '') + glob_gain.toFixed(2) + ' &euro;</label>');
		// setColNumericTab('glob_gain', glob_gain, glob_gain.toFixed(2) + ' &euro;');

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

	var nb_actifs = actifs_data.length;
	if (nb_actifs == 0) {

		actifs_data.push(100);
		actifs_labels.push('None');
		actifs_bg.push('rgb(200, 200, 200)');

	} else {

		// var colrs = ['#e41a1c','#377eb8','#4daf4a','#984ea3','#ff7f00','#ffff33','#a65628','#f781bf','#999999'];
		// var colrs = [ '#9b59b6', '#2980b9', '#1abc9c', '#27ae60', '#f1c40f', '#e67e22', '#7d3c98', '#e74c3c' ];
		var colrs = [];
		var h = 225;
		for (var n = 0; n < nb_actifs; n++) {
			var c = new KolorWheel([h, 63, 62]);
			colrs.push(c.getHex());
			h += Math.round(360 / nb_actifs);
		}

		colrs.forEach((item) => { actifs_bg.push(item); });
	}

	const data = {
		labels: actifs_labels,
		datasets: [
			{
				label: 'Répartition',
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
	go({ action: 'order', id: 'main', url: 'order_action.php?action=save&portfolio_id=<?= $portfolio_id ?>&quotes=' + quotes, no_data: 1, msg: 'Portfolio sauvegardé' });
});

<? if ($hide_save_bt) { ?>
hide('order_save_bt');
<? } ?>

<? } ?>

// Init du calcul
computeLines('init');

// Listener sur button detail ligne tableau
Dom.find("#lst_position tbody tr td:nth-child(1) button").forEach(function(element) {
	Dom.addListener(element, Dom.Event.ON_CLICK, function(event) {
		go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol='+element.innerHTML, loading_area: 'main' });
	});
});

// Listener sur button detail ligne tableau
Dom.find("#lst_position tbody tr td:nth-child(6) > div").forEach(function(element) {
	Dom.addListener(element, Dom.Event.ON_CLICK, function(event) {

		// Pas tres beau !!!
		var divs = element.getElementsByTagName("div");
		var stoploss   = divs[0].innerHTML;
		var stopprofit = divs[1].innerHTML;

		Swal.fire({
				title: '',
				html: '<div class="ui form"><div class="field"><label>Stop loss</label><input type="text"<input id="f_stoploss" class="swal2-input" type="text" placeholder="0.00" value="' + stoploss + '" /><label>Stop Profit</label><input type="text"<input id="f_stopprofit" class="swal2-input" type="text" placeholder="0.00" value="' + stopprofit + '" /></div></div>',
				showCancelButton: true,
				confirmButtonText: 'Valider',
				cancelButtonText: 'Annuler',
				showLoaderOnConfirm: true,
				allowOutsideClick: () => !Swal.isLoading()
			}).then((result) => {
				if (result.isConfirmed) {
					if (!check_num(valof('f_stoploss'), 'Stop loss', 0, 999999)) return false;
					if (!check_num(valof('f_stopprofit'), 'Stop profit', 0, 999999)) return false;
					var symbol = Dom.attribute(element, 'data-pname');
					var params = attrs([ 'f_stoploss', 'f_stopprofit' ]) + '&symbol=' + symbol;
					go({ action: 'main', id: 'main', url: 'trend_following_action.php?action=stops&' + params, no_data: 1 });
					divs[0].innerHTML = valof('f_stoploss');
					divs[1].innerHTML = valof('f_stopprofit');
					Swal.fire('Données modifiées');
				}
			});
	});
});

// Tri sur tableau
Sortable.initTable(el("lst_position"));
Sortable.initTable(el("lst_order"));

paginator({
  table: document.getElementById("lst_order"),
  box: document.getElementById("lst_order_box")
});
</script>