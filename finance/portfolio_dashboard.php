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

$devises = cacheData::readCacheData("cache/CACHE_GS_DEVISES.json");

// Recuperation des infos du portefeuille
$req = "SELECT * FROM portfolios WHERE id=".$portfolio_id." AND user_id=".$sess_context->getUserId();
$res = dbc::execSql($req);

// Bye bye si inexistant
if (!$row = mysqli_fetch_assoc($res)) exit(0);

// Recuperation de tous les actifs
$quotes = calc::getIndicatorsLastQuote();

// Calcul synthese portefeuille
$portfolio_data = calc::aggregatePortfolioById($portfolio_id);

// Portfolio synthese ?
$isPortfolioSynthese = $portfolio_data['infos']['synthese'] == 1 ? true : false;

// On recupere les eventuelles saisies de cotation manuelles recupereres de Trend Following
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
		<button id="portfolio_graph_bt" class="circular ui icon very small right floated labelled button"><i class="inverted black chart bar outline icon"></i></button>
		<? if (!$isPortfolioSynthese) { ?>
		<button id="ptf_balance_bt" class="circular ui icon very small right floated darkgray labelled button"><i class="inverted black balance icon"></i></button>
		<? } ?>
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
				<div id="perf_ribbon3" style="right: 2rem; height: 5rem !important" class="ribbon">Perf<br /><small>0.00 %</small></div>
			</div>
			<div class="six wide column" style="background: #222; border-bottom-right-radius: 50px; border-bottom: 1px solid grey;">
				<div id="perf_ribbon2" style="height: 5rem !important" class="ribbon">Perf<br /><small>0.00 %</small></div>
				<div class="ui buttons">
					<button id="0" class="mini ui primary button">Répartition</button>
					<button id="1" class="mini ui grey button">Secteurs</button>
					<button id="2" class="mini ui grey button">Géographie</button>
				</div>
				<canvas id="pft_donut" height="130" style="margin-top: 10px"></canvas>
			</div>
		</div>
	</div>

	<div class="ui hidden divider"></div>

	<h2 class="ui left floated">
		<i class="inverted location arrow icon"></i>Positions
		<button id="ptf_pos_sync_bt" class="circular ui icon very small right floated darkgray labelled button"><i class="inverted black sync icon"></i></button>
	</h2>
	<div class="ui stackable column grid">
      	<div class="row">
			<div class="column">
				<table class="ui selectable inverted single line unstackable very compact sortable-theme-minimal table" id="lst_position" data-sortable>
					<thead><tr>
						<th class="center aligned"></th>
						<th class="center aligned">Actif</th>
						<th class="center aligned" data-sortable="false">PRU<br />Qté</th>
						<th class="center aligned">Cotation<br />%</th>
						<th class="center aligned">MM200<br />%</th>
						<th class="center aligned" data-sortable="false">Alertes</th>
						<th class="center aligned">DM</th>
						<th class="center aligned">Tendance</th>
						<th class="center aligned">Poids</th>
						<th class="center aligned">Valorisation (&euro;)</th>
						<th class="center aligned">Performance</th>
						<th class="center aligned">Rendement<br /><small>PRU/Cours</small></th>
					</tr></thead>
					<tbody>
	<?
				$i = 1;
				ksort($lst_positions);
				$div_per_year = 0;
				foreach($lst_positions as $key => $val) {

					// Infos sur actif courant
					$qs = isset($quotes['stocks'][$key]) ? $quotes['stocks'][$key] : [ 'DM' => 0, 'MM7' => 0, "MM20" => 0, "MM50" => 0, "MM100" => 0, "MM200" => 0];

					// Choix de la devise
					$currency = $val['other_name'] ? $val['devise'] : $qs['currency'];

					// Taux conversion devise
					$taux = $currency == "EUR" ? 1 : calc::getCurrencyRate($currency."EUR", $devises);

					// Dividende annualise s'il existe
					$dividende = isset($qs['dividende_annualise']) ? $qs['dividende_annualise'] : 0;

					// Estimation dividende annuel
					$div_per_year += $dividende * $val['nb'] * $taux;

					$achat = sprintf("%.2f", $val['nb'] * $val['pru']);
					// Si on n'a pas la cotation en base on prend le pru
					$quote_from_pru = isset($qs['price']) && !isset($save_quotes[$key]) ? false : true;
					$quote = $quote_from_pru ? (isset($save_quotes[$key]) ? $save_quotes[$key] : $val['pru']) : $qs['price'];
					$qs['price'] = $quote;
					$pct   = isset($qs['percent']) ? $qs['percent'] : 0;
					$valo  = sprintf("%.2f", $val['nb'] * $quote);
					$perf  = round($achat != 0 ? (($valo - $achat) * 100) / $achat : 0, 2);
					$pname = '<button class="tiny ui primary button">'.$key.($val['other_name'] ? '(*)' : '').'</button>';

					$isAlerteActive = isset($lst_trend_following[$key]['active']) && $lst_trend_following[$key]['active'] == 1 ? true : false;
					$stop_loss   = isset($lst_trend_following[$key]['stop_loss'])   ? $lst_trend_following[$key]['stop_loss']   : 0;
					$stop_profit = isset($lst_trend_following[$key]['stop_profit']) ? $lst_trend_following[$key]['stop_profit'] : 0;
					$objectif    = isset($lst_trend_following[$key]['objectif'])    ? $lst_trend_following[$key]['objectif']    : 0;
					$seuils      = isset($lst_trend_following[$key]['seuils'])      ? $lst_trend_following[$key]['seuils']      : "";
					$options     = isset($lst_trend_following[$key]['options'])     ? $lst_trend_following[$key]['options']     : 0;

					$perf_indicator = calc::getPerfIndicator($qs);
					$perf_bullet    = "<span data-tootik-conf=\"left multiline\" data-tootik=\"".uimx::$perf_indicator_libs[$perf_indicator]."\"><a class=\"ui empty ".uimx::$perf_indicator_colrs[$perf_indicator]." circular label\"></a></span>";

					$tooltip = "Entreprise";

					$icon = "copyright outline";
					$icon_tag = "bt_filter_SEC_99999";
					$pct_mm = $qs['MM200'] == 0 ? 0 : (($qs['MM200'] - $quote) * 100) / $quote;

					$tags_infos = uimx::getIconTooltipTag($qs['tags']);
				
					echo '<tr id="tr_item_'.$i.'" data-pname="'.$key.'" data-other="'.($val['other_name'] ? 1 : 0).'" data-taux="'.$taux.'">
						<td data-geo="'.$tags_infos['geo'].'" data-value="'.$tags_infos['icon_tag'].'" data-tootik-conf="right" data-tootik="'.$tags_infos['tooltip'].'" class="center align collapsing">
							<i data-secteur="'.$tags_infos['icon_tag'].'" class="inverted grey '.$tags_infos['icon'].' icon"></i>
						</td>

						<td class="center aligned" id="f_actif_'.$i.'" data-pname="'.$key.'">'.$pname.'</td>

						<td class="center aligned" id="f_pru_'.$i.'" data-nb="'.$val['nb'].'" data-pru="'.sprintf("%.2f", $val['pru']).'"><div>
							<button class="tiny ui button">'.sprintf("%.2f %s", $val['pru'], uimx::getCurrencySign($currency)).'</button>
							<label>'.$val['nb'].'</label>
						</div></td>

						<td class="center aligned" data-value="'.$pct.'"><div>
							<button id="f_price_'.$i.'" data-value="'.sprintf("%.2f", $quote).'" data-name="'.$key.'" data-pru="'.($quote_from_pru ? 1 : 0).'" class="tiny ui button">'.sprintf("%.2f %s", $quote, uimx::getCurrencySign($currency)).'</button>
							<label id="f_pct_jour_'.$i.'" class="'.($pct >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $pct).' %</label>
						</div></td>
					
						<td class="center aligned" data-value="'.$pct_mm.'"><div>
							<button class="tiny ui button" style="background: '.uimx::getRedGreenColr($qs['MM200'], $quote).'">'.sprintf("%.2f %s", $qs['MM200'], uimx::getCurrencySign($currency)).'</button>
							<label style="color: '.uimx::getRedGreenColr($qs['MM200'], $quote).'">'.sprintf("%s%.2f", ($pct_mm >= 0 ? '+' : ''), $pct_mm).' %</label>
						</div></td>

						<td class="center aligned" data-active="'.($isAlerteActive ? 1 : 0).'" data-value="'.$quote.'" data-seuils="'.sprintf("%s", $seuils).'" data-options="'.$options.'"><div class="small ui right group input" data-pname="'.$key.'">
							<div class="'.(!$isAlerteActive || intval($stop_loss)   == 0 ? "grey" : "").' floating ui label">'.sprintf("%.2f", $stop_loss).'</div>
							<div class="'.(!$isAlerteActive || intval($objectif)    == 0 ? "grey" : "").' floating ui label">'.sprintf("%.2f", $objectif).'</div>
							<div class="'.(!$isAlerteActive || intval($stop_profit) == 0 ? "grey" : "").' floating ui label">'.sprintf("%.2f", $stop_profit).'</div>
						</div></td>

						<td id="f_dm_'.$i.'"       class="center aligned '.($qs['DM'] >= 0 ? "aaf-positive" : "aaf-negative").'" data-value="'.$qs['DM'].'">'.$qs['DM'].' %</td>
						<td id="f_tendance_'.$i.'" class="center aligned">'.$perf_bullet.'</td>
						<td id="f_poids_'.$i.'"    class="center aligned"></td>
						<td id="f_valo_'.$i.'"     class="right  aligned"></td>
						<td id="f_perf_pru_'.$i.'" class="center aligned"></td>
						<td id="f_rand_'.$i.'"     class="center aligned">
							<div>
								<label>'.($dividende == 0 ? "-" : sprintf("%.2f%%", ($dividende * 100) / $val['pru'])).'</label>
								<label>'.($dividende == 0 ? "-" : sprintf("%.2f%%", ($dividende * 100) / $quote)).'</label>
							</div>
						</td>
					</tr>';
					$i++;
				}
	?>
					</tbody>
					<tfoot><tr>
						<td colspan="7"></th>
						<td></td>
						<td></td>
						<td id="sum_valo"  class="right aligned"></td>
						<td id="glob_perf" class="center aligned"></td>
						<td></td>
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
				<option value="cash">Cash</option>
                <? foreach ($quotes["stocks"] as $key => $val) { ?>
                    <option value="<?= $val['symbol'] ?>"><?= $val['symbol'] ?></option>
                <? } ?>
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
					<thead><tr>
						<th></th>
						<th>Date</th>
						<th>Actif</th>
						<th>Action</th>
						<th>Qté</th>
						<th>Prix</th>
						<th>Total</th>
						<th>Comm/TTF</th>
						<th></th>
						<th></th>
					</tr></thead>
					<tbody>
<?
				foreach(array_reverse($lst_orders) as $key => $val) {

					$val = calc::formatDataOrder($val);

					echo '<tr>
						<td><i class="inverted long arrow alternate '.$val['icon'].' icon"></i></td>
						<td>'.$val['date'].'</td>
						<td>'.$val['product_name'].'</td>
						<td class="center aligned">'.$val['action_lib'].'</td>
						<td data-value="'.$val['quantity'].'">'.$val['quantity'].'</td>
						<td data-value="'.$val['price'].'">'.$val['price_signed'].'</td>
						<td data-value="'.sprintf("%.2f", $val['valo']).'" class="'.$val['action_colr'].'">'.$val['valo_signed'].'</td>
						<td data-value="'.$val['commission'].'">'.sprintf("%.2f", $val['commission']).' &euro;/'.sprintf("%.2f", $val['ttf']).' &euro;</td>
						<td data-value="'.$val['confirme'].'"><i class="ui '.($val['confirme'] == 1 ? "check green" : "clock outline orange").' icon"></i></td>
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

var options = {
    responsive: false,
    maintainAspectRatio: true,
	plugins: {
		legend: {
			display: true,
			position: 'right'
		},
		title: {
			display: false,
			color: 'white',
			text: 'Répartition'
		}
	}
};

<?
	if (!$isPortfolioSynthese) {
		foreach($lst_orders as $key => $val) { ?>
			Dom.addListener(Dom.id('order_edit_<?= $val['id'] ?>_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'order', id: 'main', url: 'order_detail.php?action=upt&portfolio_id=<?= $portfolio_id ?>&order_id=<?= $val['id'] ?>', loading_area: 'main' }); });
<?	
		}
	}
?>

// Pour donut repartition par actif, secteur, geo 
const labels_repartition = [[], [], []];
const data_repartition   = [[], [], []];
const bg_repartition     = [[], [], []];
var tab_secteur = {};
var tab_geo = {};
var infos_area = { n: 1, l:[], v: [] };
var infos_area_bis = { n: 2, l:[], v: [] };
var current_infos_area = {};

update_infos_areas = function(o) {

	current_infos_area = o;

	var x = 0;

	[ 'infos_area1', 'infos_area2' ].forEach(function(elt) {

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

computeLines = function(opt) {

	sum_achat = 0;
	sum_valo  = 0;
	glob_perf = 0;
	glob_gain = 0;

	sum_valo_stoploss1  = 0;
	sum_valo_stoploss2  = 0;
	sum_valo_stopprofit = 0;
	sum_valo_objectif   = 0;

	valo_ptf   = <?= sprintf("%.2f", $portfolio_data['valo_ptf']) ?>;
	perf_ptf   = <?= sprintf("%.2f", $portfolio_data['perf_ptf']) ?>;
	cash       = <?= sprintf("%.2f", $portfolio_data['cash']) ?>;
	ampplt     = <?= sprintf("%.2f", $portfolio_data['ampplt']) ?>;
	gain_perte = <?= sprintf("%.2f", $portfolio_data['gain_perte']) ?>;
	retraits   = <?= sprintf("%.2f", $portfolio_data['retrait']) ?>;
	depots     = <?= sprintf("%.2f", $portfolio_data['depot']) ?>;
	dividendes = <?= sprintf("%.2f", $portfolio_data['dividende']) ?>;
	commissions = <?= sprintf("%.2f", $portfolio_data['commission']) ?>;
	div2depot  = <?= sprintf("%.2f", ($portfolio_data['dividende'] * 100 ) / $portfolio_data['depot']) ?>;
	ttf = <?= sprintf("%.2f", $portfolio_data['ttf']) ?>;

	if (opt == 'init') {
		infos_area.l[0] = "Estimation Portefeuille";
		infos_area.v[0] = valo_ptf.toFixed(2) + ' \u20AC';
		infos_area.l[1] = "Cash disponible";
		infos_area.v[1] = cash.toFixed(2) + ' \u20AC';
		infos_area.l[2] = "+/- Value";
		infos_area.v[2] = gain_perte.toFixed(2) + ' \u20AC';
		infos_area.l[3] = "&sum; Dépots";
		infos_area.v[3] = depots.toFixed(2) + ' \u20AC';
		infos_area.l[4] = "&sum; Retraits";
		infos_area.v[4] = retraits.toFixed(2) + ' \u20AC';
		infos_area.l[5] = "&sum; Dividendes (Div to Depot)";
		infos_area.v[5] = dividendes.toFixed(2) + ' \u20AC' + ' (' + div2depot.toFixed(2) + ' %)';
		setColNumericTab('sum_comm', commissions, commissions.toFixed(2) + ' &euro;');
		setColNumericTab('sum_ttf', ttf, ttf.toFixed(2) + ' &euro;');
		Dom.id('subtitle').innerHTML = ' (<?= $portfolio_data['interval_year'] > 0 ? $portfolio_data['interval_year'].($portfolio_data['interval_year'] > 1 ? " ans " : " an") : "" ?> <?= $portfolio_data['interval_month'] ?> mois)';
	}

	// On parcours les lignes du tableau positions pour calculer valo, perf, gain, ...
	Dom.find('#lst_position tbody tr').forEach(function(item) {

		ind      = Dom.attribute(item, 'id').split('_')[2];
		other    = Dom.attribute(item, 'data-other');
		taux     = Dom.attribute(item, 'data-taux');
		actif    = Dom.attribute(Dom.id('f_actif_' + ind), 'data-pname');
		pru      = parseFloat(Dom.attribute(Dom.id('f_pru_'   + ind), 'data-pru'));
		price    = parseFloat(Dom.attribute(Dom.id('f_price_' + ind), 'data-value'));
		nb       = parseFloat(Dom.attribute(Dom.id('f_pru_'   + ind), 'data-nb'));
		perf_pru = parseFloat(getPerf(pru, price));

		// Recuperation des stops
		var stoploss   = 0;
		var stopprofit = 0;
		var objectif   = 0;

		var divs = item.getElementsByTagName("td")[5].getElementsByTagName("div")[0].getElementsByTagName("div");

		if (divs) {
			stoploss   = divs[0].innerHTML;
			objectif   = divs[1].innerHTML;
			stopprofit = divs[2].innerHTML;
		}

		// si devise != EUR, appliquer taux de change
		achat    = parseFloat(nb * pru * taux);
		valo     = parseFloat(nb * price * taux);
		valo     = parseFloat(nb * price * taux);
		valo     = parseFloat(nb * price * taux);
		gain_pru = parseFloat(nb * (price - pru) * taux);

		valo_stoploss1  = parseFloat(nb * (stoploss == 0 ? price : stoploss) * taux);
		valo_stoploss2  = parseFloat(nb * stoploss * taux);
		valo_objectif   = parseFloat(nb * (objectif == 0 ? price : objectif) * taux);
		valo_stopprofit = parseFloat(nb * (stopprofit == 0 ? price : stopprofit) * taux);

		// Data donuts
		labels_repartition[0].push(actif);

		sum_achat += achat;
		sum_valo  += valo;

		sum_valo_stoploss1  += valo_stoploss1;
		sum_valo_stoploss2  += valo_stoploss2;
		sum_valo_objectif   += valo_objectif;
		sum_valo_stopprofit += valo_stopprofit;

		setColNumericTab('f_valo_'  + ind, valo,  valo.toFixed(2)  + ' &euro;');
		setColNumericTab('f_perf_pru_' + ind, perf_pru, '<div><button class="tiny ui ' + (perf_pru >= 0 ? 'aaf-positive' : 'aaf-negative') + ' button">' + perf_pru.toFixed(2) + ' %</button><label>' + (gain_pru >= 0 ? '+' : '') + gain_pru.toFixed(2) + ' &euro;</label></div>');

		if (other == 1 && opt == 'change') {
			Dom.id('f_pct_jour_'  + ind).innerHTML = 'N/A';
		}
	});

	// On reparcours les lignes du tableau positions pour le % de chaque actif dans le portefeuille
	Dom.find('#lst_position tbody tr').forEach(function(item) {

		ind = Dom.attribute(item, 'id').split('_')[2];

		secteur  = Dom.attribute(item.getElementsByTagName("td")[0], 'data-tootik');
		geo      = Dom.attribute(item.getElementsByTagName("td")[0], 'data-geo');

		price = parseFloat(Dom.attribute(Dom.id('f_price_' + ind), 'data-value'));
		nb    = parseFloat(Dom.attribute(Dom.id('f_pru_' + ind), 'data-nb'));
		valo  = parseFloat(nb * price);
		ratio = getRatio(sum_valo, valo).toFixed(2);

		tab_secteur[secteur] = (tab_secteur[secteur] ? parseFloat(tab_secteur[secteur]) : 0) + parseFloat(ratio);
		tab_geo[geo] = (tab_geo[geo] ? parseFloat(tab_geo[geo]) : 0) + parseFloat(ratio);

		setColNumericTab('f_poids_' + ind, Math.round(ratio), Math.round(ratio) + ' %', false);

		data_repartition[0].push(ratio);
	});

	// On garnit les data du donut secteur
	for (var key in tab_secteur) {
		labels_repartition[1].push(key);
		data_repartition[1].push(tab_secteur[key]);
	}

	// On garnit les data du donut geo
	for (var key in tab_geo) {
		labels_repartition[2].push(key);
		data_repartition[2].push(tab_geo[key]);
	}

	glob_perf = getPerf(sum_achat, sum_valo);
	glob_gain = sum_valo - sum_achat;

	if (data_repartition[0].length > 0) {

		setColNumericTab('sum_valo',  sum_valo,  sum_valo.toFixed(2)  + ' &euro;');
		setColNumericTab('glob_perf', glob_perf, '<div><button class="tiny ui ' + (glob_perf >= 0 ? 'aaf-positive' : 'aaf-negative') + ' button">' + glob_perf.toFixed(2) + ' %</button><label>' + (glob_gain >= 0 ? '+' : '') + glob_gain.toFixed(2) + ' &euro;</label></div>');

		addCN('perf_ribbon2', glob_perf >= 0 ? "ribbon--green" : "ribbon--red");
		Dom.find('#perf_ribbon2 small')[0].innerHTML = glob_perf.toFixed(2) + ' %';
	}

	addCN('perf_ribbon3', perf_ptf >= 0 ? "ribbon--green" : "ribbon--red");
	Dom.find('#perf_ribbon3 small')[0].innerHTML = perf_ptf.toFixed(2) + ' %';

	// Garanissage des tablaux de couleurs
	[ 0, 1, 2].forEach(function(ind) {

		let nb_actifs = data_repartition[ind].length;
		if (nb_actifs == 0) {

			data_repartition[ind].push(100);
			labels_repartition[ind].push('None');
			bg_repartition[ind].push('rgb(200, 200, 200)');

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

			colrs.forEach((item) => { bg_repartition[ind].push(item); });
		}
	});


	const data_donut = {
		labels: labels_repartition[0],
		datasets: [
			{
				label: 'Répartition',
				data: data_repartition[0],
				borderWidth: 0.5,
				backgroundColor: bg_repartition[0],
				borderWidth: 4,
				borderColor: "#222",
				hoverOffset: 4
			}
		]
	};

	perf_stoploss1  = getPerf(valo_ptf, sum_valo_stoploss1).toFixed(2);
	perf_objectif   = getPerf(valo_ptf, sum_valo_objectif).toFixed(2);
	perf_stopprofit = getPerf(valo_ptf, sum_valo_stopprofit).toFixed(2);

	infos_area_bis.l[0] = 'Estimation Stop Loss <a class="ui mini '   + (perf_stoploss1 >= 0  ? 'green' : 'red') + ' tag label">' + perf_stoploss1 + '%</a>';
	infos_area_bis.v[0] = sum_valo_stoploss1.toFixed(2) + ' \u20AC';
	infos_area_bis.l[1] = 'Estimation Objectif <a class="ui mini '    + (perf_objectif >= 0   ? 'green' : 'red') + ' tag label">' + perf_objectif + '%</a>';
	infos_area_bis.v[1] = sum_valo_objectif.toFixed(2) + ' \u20AC';
	infos_area_bis.l[2] = 'Estimation Stop Profit <a class="ui mini ' + (perf_stopprofit >= 0 ? 'green' : 'red') + ' tag label">' + perf_stopprofit + '%</a>';
	infos_area_bis.v[2] = sum_valo_stopprofit.toFixed(2) + ' \u20AC';
	infos_area_bis.l[3] = "&sum; Dépots";
	infos_area_bis.v[3] = depots.toFixed(2) + ' \u20AC';
	infos_area_bis.l[4] = 'Couverture Stop Loss';
	infos_area_bis.v[4] = sum_valo_stoploss2.toFixed(2) + ' \u20AC';
	infos_area_bis.l[5] = 'Estimation dividende annuel (Div to Depot)';
	infos_area_bis.v[5] = '<?= sprintf("%.2f", $div_per_year) ?> \u20AC' + ' ( <?= sprintf("%.2f", ($div_per_year * 100) / $portfolio_data['depot']) ?> %)';


	if (myChart) myChart.destroy();
	myChart = new Chart(ctx, { type: 'doughnut', data: data_donut, options: options } );
	myChart.update();
}

// Filtre de la table des ordres
filter = function() {

	paginator({
		table: document.getElementById("lst_order"),
		box: document.getElementById("lst_order_box"),
		get_rows: get_orders_list
	});

	hide('filters');
}

// Listener sur les boutons ADD et BACK
<? if (!$isPortfolioSynthese) { ?>
Dom.addListener(Dom.id('order_add_bt'),  Dom.Event.ON_CLICK, function(event) { go({ action: 'order', id: 'main', url: 'order_detail.php?action=new&portfolio_id=<?= $portfolio_id ?>', loading_area: 'main' }); });
Dom.addListener(Dom.id('ptf_balance_bt'), Dom.Event.ON_CLICK, function(event) { overlay.load('portfolio_balance.php', { 'portfolio_id' : <?= $portfolio_id ?> }); });
<? } ?>
Dom.addListener(Dom.id('order_back_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'portfolio', id: 'main', url: 'portfolio.php', loading_area: 'main' }); });
Dom.addListener(Dom.id('portfolio_graph_bt'), Dom.Event.ON_CLICK, function(event) {
//	go({ action: 'portfolio', id: 'main', url: 'portfolio_graph.php?portfolio_id=<?= $portfolio_id ?>', loading_area: 'main' });
	overlay.load('portfolio_graph.php', { 'portfolio_id' : <?= $portfolio_id ?> });
});
Dom.addListener(Dom.id('portfolio_switch_bt'), Dom.Event.ON_CLICK, function(event) { update_infos_areas(current_infos_area.n == 1 ? infos_area_bis : infos_area); });
Dom.addListener(Dom.id('order_filter_bt'), Dom.Event.ON_CLICK, function(event) { toogle('filters') });
Dom.addListener(Dom.id('filter_go_bt'),    Dom.Event.ON_CLICK, function(event) { filter() });
Dom.addListener(Dom.id('ptf_pos_sync_bt'), Dom.Event.ON_CLICK, function(event) { toogleCN('lst_position', 'alternate'); toogleCN('ptf_pos_sync_bt', 'on'); });

// Init du calcul
computeLines('init');

// Listener sur button detail sur actif
Dom.find("#lst_position tbody tr td:nth-child(2) button").forEach(function(element) {
	// pru = Dom.attribute(Dom.id('f_pru_' + element.parentNode.id.split('_')[2]), 'data-pru');
	Dom.addListener(element, Dom.Event.ON_CLICK, function(event) {
		other = Dom.attribute(element.parentNode.parentNode, 'data-other');
		if (other == 1)
			Swal.fire('Actif non suivi');
		else
			go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?ptf_id=<?= $portfolio_id ?>&symbol=' + element.innerHTML, loading_area: 'main' });
	});
});

// Listener sur buttons manuel price ligne tableau si actif other
Dom.find("#lst_position tbody tr td:nth-child(4) button").forEach(function(element) {

	let other = Dom.attribute(element.parentNode.parentNode.parentNode, 'data-other');
	let pname = Dom.attribute(element.parentNode.parentNode.parentNode, 'data-pname');

	if (other == 1) {
		Dom.addListener(element, Dom.Event.ON_CLICK, function(event) {

			// On récupère la valeur dans le button
			let quote = Dom.attribute(element, 'data-value');

			Swal.fire({
				title: '',
				html: '<div class="ui form"><div class="field">' +
							'<label>Saisie manuelle de la cotation</label><input type="text"<input id="f_quote" class="swal2-input" type="text" placeholder="0.00" value="' + quote + '" />' +
						'</div></div>',
				showCancelButton: true,
				confirmButtonText: 'Valider',
				cancelButtonText: 'Annuler',
				showLoaderOnConfirm: true,
				allowOutsideClick: () => !Swal.isLoading()
			}).then((result) => {
				if (result.isConfirmed) {
					if (!check_num(valof('f_quote'), 'Cotation', 0, 999999)) return false;
					let params = attrs([ 'f_quote' ]) + '&symbol=' + pname;
					go({ action: 'main', id: 'main', url: 'trend_following_action.php?action=manual_price&' + params, no_data: 1 });
					element.innerHTML = valof('f_quote') + '&euro;';
					Dom.attribute(element, { 'data-value': valof('f_quote') });
					computeLines('change');
					Swal.fire('Données modifiées');
				}
			});

		});
	}
});

// Listener sur buttons stoploss/stoplimit ligne tableau
Dom.find("#lst_position tbody tr td:nth-child(6) > div").forEach(function(element) {
	Dom.addListener(element, Dom.Event.ON_CLICK, function(event) {

		// On récupère les valeurs dans la cellule du tavleau - Pas tres beau !!!
		var divs   = element.getElementsByTagName("div");
		var pname      = Dom.attribute(element, 'data-pname');
		var price      = Dom.attribute(element.parentNode, 'data-value');
		var active     = Dom.attribute(element.parentNode, 'data-active');
		var stoploss   = divs[0].innerHTML;
		var objectif   = divs[1].innerHTML;
		var stopprofit = divs[2].innerHTML;
		var seuils     = Dom.attribute(element.parentNode, 'data-seuils') ? Dom.attribute(element.parentNode, 'data-seuils') : "";
		var options    = Dom.attribute(element.parentNode, 'data-options');

		tf_ui_html = trendfollowing_ui.getHtml(pname, price, active, stoploss, objectif, stopprofit, seuils, options);

		Swal.fire({
				title: '',
				html: tf_ui_html,
				showCancelButton: true,
				confirmButtonText: 'Valider',
				cancelButtonText: 'Annuler',
				showLoaderOnConfirm: true,
				allowOutsideClick: () => !Swal.isLoading()
			}).then((result) => {
				if (result.isConfirmed) {

					if (!trendfollowing_ui.checkForm()) return false;


					go({ action: 'main', id: 'main', url: trendfollowing_ui.getUrlRedirect(pname), no_data: 1 });

					divs[0].innerHTML = valof('f_stoploss');
					divs[1].innerHTML = valof('f_objectif');
					divs[2].innerHTML = valof('f_stopprofit');
					divs[0].className = divs[0].className.replaceAll('grey', '');
					divs[1].className = divs[1].className.replaceAll('grey', '');
					divs[2].className = divs[2].className.replaceAll('grey', '');
					if (valof('f_active') == 0 || parseInt(valof('f_stoploss'))   == 0) divs[0].className = divs[0].className + ' grey';
					if (valof('f_active') == 0 || parseInt(valof('f_objectif'))   == 0) divs[1].className = divs[1].className + ' grey';
					if (valof('f_active') == 0 || parseInt(valof('f_stopprofit')) == 0) divs[2].className = divs[2].className + ' grey';
					if (valof('f_active') == 0 || parseInt(valof('f_seuils'))     == 0) divs[3].className = divs[3].className + ' grey';
					Dom.attribute(element.parentNode, { 'data-seuils'  : valof('f_seuils') });
					Dom.attribute(element.parentNode, { 'data-options' : trendfollowing_ui.getOptionsValue() });
					Dom.attribute(element.parentNode, { 'data-active'  : valof('f_active') == 0 ? 0 : 1 });

					Swal.fire('Données modifiées');
				}
			});
	});
});

// Gestion des boutons du graphe donut
changeButtonState = function(bt) {
	['0', '1', '2'].forEach(function(item) { replaceCN(item, 'primary', 'grey'); });
	replaceCN(bt, 'grey', 'primary');
}
updateDonut = function(bt) {
    let i = parseInt(bt);
	changeButtonState(bt);
    myChart.config.data.datasets[0].data = data_repartition[i];
    myChart.config.data.labels           = labels_repartition[i];
	myChart.config.data.backgroundColor  = bg_repartition[i];
    myChart.update();
}
Dom.addListener(Dom.id('0'), Dom.Event.ON_CLICK, function(event) { updateDonut('0'); });
Dom.addListener(Dom.id('1'), Dom.Event.ON_CLICK, function(event) { updateDonut('1'); });
Dom.addListener(Dom.id('2'), Dom.Event.ON_CLICK, function(event) { updateDonut('2'); });

// Tri sur tableau
Sortable.initTable(el("lst_position"));
Sortable.initTable(el("lst_order"));

get_orders_list = function() {

	let filter_date = valof('f_date');
	let filter_product_name = valof('f_product_name');
	let filter_action = valof('f_action');

	var table = document.getElementById("lst_order");
	var tbody = table.getElementsByTagName("tbody")[0]||table;

	children = tbody.children;
	var trs = [];
	for (var i=0; i < children.length; i++) {
		if (children[i].nodeType = "tr") {
			if (children[i].getElementsByTagName("td").length > 0) {

				let hide_line = false;

				if (filter_date && children[i].getElementsByTagName("td")[1].innerHTML.toLowerCase() != filter_date.toLowerCase())
					hide_line = true;

				if (filter_product_name && children[i].getElementsByTagName("td")[2].innerHTML.toLowerCase() != filter_product_name.toLowerCase())
					hide_line = true;
				
				if (filter_action && filter_action.toLowerCase() != "achatvente" && children[i].getElementsByTagName("td")[3].innerHTML.toLowerCase() != filter_action.toLowerCase())
					hide_line = true;

				if (filter_action && filter_action.toLowerCase() == "achatvente" && (children[i].getElementsByTagName("td")[3].innerHTML.toLowerCase() != "achat" && children[i].getElementsByTagName("td")[3].innerHTML.toLowerCase() != "vente"))
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

// On cache les fitres de selection de la liste des ordres passes
hide("filters");

</script>