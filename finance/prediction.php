<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isUserConnected()) uimx::redirectLoginPage('prediction');

foreach([''] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

// Récupération des devises
$devises = calc::getGSDevisesWithNoUpdate();

// Recuperation de tous les actifs
$quotes = calc::getIndicatorsLastQuote();

// $req = "SELECT * FROM prediction WHERE user_id=".$sess_context->getUserId()." AND status = 0";
$req = "SELECT * FROM prediction WHERE user_id=".$sess_context->getUserId();
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

	$date_objectif = date('Y-m-d');
	$date_stoploss = date('Y-m-d');
	$date_gain_max = date('Y-m-d');
	$objectif = 0;
	$stoploss = 0;
	$gain_max = 0;
	$stop_gain_max = 1;
	$previous_price = 0;

	$req2 = "SELECT * FROM daily_time_series_adjusted WHERE symbol='".$row['symbol']."' AND day >= '".$row['date_avis']."'";
	$res2 = dbc::execSql($req2);
	while($row2 = mysqli_fetch_array($res2)) {

		if ($previous_price == 0) $previous_price = $row2['open'];

		// Stoploss atteint
		if ($objectif == 0 && $row['stoploss'] >= $row2['low']) {
			$stoploss = 1;
			$date_stoploss = $row2['day'];
		}

		// Objectif atteint
		if ($stoploss == 0 && $row2['adjusted_close'] >= $row['objectif']) {
			$objectif = 1;
			$date_objectif = $row2['day'];
			$stop_gain_max = 0;
		}

		// Recherche gain max en cas d'objectif atteint avec stoploss à max(objectif, -5% cloture veille)
		if ($previous_price > 0 && $objectif == 1 && $stop_gain_max == 0) {

			$stoploss_ref = $row['objectif'] * 0.97;
			if ($row2['adjusted_close'] < $stoploss_ref) {
				$stop_gain_max = 1;
				$gain_max = $row['objectif'];
				$date_gain_max = $row2['day'];
			} else {
				if ($row2['adjusted_close'] > max($stoploss_ref, $gain_max)) {
					$date_gain_max = $row2['day'];
					$gain_max = $row2['adjusted_close'];
				}
			}

		}

		$previous_price = $row2['adjusted_close'];

	}

	// Enregistrement stoploss atteint
	if ($stoploss == 1) {
		$update = "UPDATE prediction SET status=-1, date_status='".$date_stoploss."' WHERE id=".$row['id'];
		$res3 = dbc::execSql($update);
	}

	// Enregistrement objectif atteint
	if ($objectif == 1) {
		$update = "UPDATE prediction SET status=1, date_status='".$date_objectif."' WHERE id=".$row['id'];
		$res3 = dbc::execSql($update);
	}

	// Enregistrement max gain (on enregistre meme si gaim_max=0)
	$update = "UPDATE prediction SET gain_max='".$gain_max."', gain_max_date='".$date_gain_max."' WHERE id=".$row['id'];
	$res3 = dbc::execSql($update);

	// Prediction cloturée après 6 mois
	$datetime1 = new DateTime($row['date_avis']);
	$datetime2 = new DateTime(date("Y-m-d"));
	$difference = $datetime1->diff($datetime2);
	if ($row['status'] == 0 && $difference->m >= 6) {
		$update = "UPDATE prediction SET status=-2, date_status='".date("Y-m-d")."' WHERE id=".$row['id'];
		$res3 = dbc::execSql($update);
	}

}

?>

<div class="ui container inverted segment">

	<h2><i class="inverted magic icon"></i>Prédictions <button id="prediction_add_bt" class="ui icon very small right floated labelled button"><i class="inverted black add icon"></i></button></h2>

	<table class="ui selectable inverted single line unstackable very compact table sortable-theme-minimal" id="lst_prediction" data-sortable>
		<thead>
			<tr>
				<th class="center aligned">Date</th>
                <th class="center aligned">Actif</th>
                <th class="center aligned">Cours Avis</th>
				<th class="center aligned">Cotation</th>
                <th class="center aligned">Objectif</th>
                <th class="center aligned">Max</th>
                <th class="center aligned">Stoploss</th>
                <th>Conseiller</th>
                <th class="center aligned">Délai</th>
                <th class="center aligned">Status</th>
				<th class="center aligned"></th>
			</tr>
		</thead>
		<tbody>
<?
			$tab_extend = [];
			$req = "SELECT * FROM prediction WHERE user_id=".$sess_context->getUserId()." ORDER BY date_avis DESC";
			$res = dbc::execSql($req);
        	while($row = mysqli_fetch_array($res)) {

				$cj   = $quotes["stocks"][$row['symbol']]['price'];
				$name = $quotes["stocks"][$row['symbol']]['name'];
				$perc = $quotes["stocks"][$row['symbol']]['percent'];
				$curr = uimx::getCurrencySign($quotes["stocks"][$row['symbol']]['currency']);
				$ref_cotation = $row['status'] == 0 ? $cj : $row['cours']; // Si status en cours alors perf par rapport au cours réel sinon par perf par rapport cours debut prediction
				$perf = $ref_cotation == 0 ? 0 : (($row['objectif'] / $ref_cotation) - 1) * 100;
				$perf_gain_max = $row['gain_max'] == 0 ? 0 : (($row['gain_max'] / $ref_cotation) - 1) * 100;

				$datetime1 = new DateTime($row['date_avis']);
				$datetime2 = new DateTime($row['status'] == 0 ? date("Y-m-d") : $row['date_status']);
				$difference = $datetime1->diff($datetime2);

				// Initialisation calcul rendement prediction
				if (!isset($tab_extend[$row['conseiller']]['rendement'])) {
					$tab_extend[$row['conseiller']]['rendement'] = 0;
					$tab_extend[$row['conseiller']]['nb_predictions'] = 0;
				} 

				// Calcul rendement si prédiction validée ou invalidée
				if ($row['status'] == 1 || $row['status'] == -1) {
					$ref_cours_rendement = $row['status'] == 1 ?$row['objectif'] : $row['stoploss'];
					$tab_extend[$row['conseiller']]['rendement'] += (($ref_cours_rendement - $row['cours']) / $row['cours'])*100;
					$tab_extend[$row['conseiller']]['nb_predictions']++;
				}

				if ($row['status'] == 1) {
					$tab_extend[$row['conseiller']]['days'] = isset($tab_extend[$row['conseiller']]['days']) ? $tab_extend[$row['conseiller']]['days'] + $difference->days : $difference->days; 
					$tab_extend[$row['conseiller']]['nb_p'] = isset($tab_extend[$row['conseiller']]['nb_p']) ? $tab_extend[$row['conseiller']]['nb_p'] + 1 : 1;
				}

				$lib_diff = ($difference->y > 0 ? $difference->y.' ans, ' : '').($difference->m > 0 ? $difference->m.' mois, ' : '').$difference->d.' jours';
?>
				<tr>
					<td class="center aligned"><?= $row['date_avis'] ?></td>
					<td class="center aligned"><button data-tootik="<?= mb_convert_encoding($name, 'ISO-8859-1', 'UTF-8') ?>" data-tootik-conf="right" class="tiny ui primary button"><?= $row['symbol'] ?></button></td>
					<td class="center aligned"><?= sprintf("%.2f", $row['cours']).$curr ?></td>
					<td class="center aligned price_perf"><div>
						<button class="tiny ui <?= $perc >= 0 ? "aaf-positive" : "aaf-negative" ?> button"><?= sprintf("%.2f", $cj).$curr ?></button>
						<label class="<?= $perc >= 0 ? "aaf-positive" : "aaf-negative" ?>"><?= sprintf("%.2f%%", $perc) ?></label>
					</div></td>
					<td class="center aligned price_perf"><div>
						<button <?= $row['status'] == 1 ? 'data-tootik="'.$row['date_status'].'"' : "" ?> class="tiny ui <?= $row['status'] < 0 ? "aaf-negative" : ($row['cours'] <= $cj || $row['status'] > 0  ? "aaf-positive" : "orange") ?> button"><?= sprintf("%.2f", $row['objectif']).$curr ?></button>
						<label class="<?= $row['status'] < 0 ? "aaf-negative" : ($row['cours'] <= $cj || $row['status'] > 0 ? "aaf-positive" : "orange") ?>"><?= sprintf("%.2f%%", $perf) ?></label>
					</div></td>
					<td class="center aligned price_perf"><div>
						<button <?= $row['gain_max'] == 0 ? '' : 'style="background: #c959ff;"' ?>  <?= $row['gain_max'] > 0 ? 'data-tootik="'.$row['gain_max_date'].'"' : "" ?> class="tiny ui button"><?= sprintf("%.2f", $row['gain_max']).$curr ?></button>
						<label  <?= $row['gain_max'] == 0 ? '' : 'style="color: #c959ff;"' ?>> <?= $row['gain_max'] == 0 ? "-" : sprintf("%.2f%%", $perf_gain_max) ?></label>
					</div></td>
					<td class="center aligned price_perf"><div>
						<button <?= $row['status'] == -1 ? 'data-tootik="'.$row['date_status'].'"' : "" ?> class="tiny ui aaf-negative button"><?= sprintf("%.2f", $row['stoploss']).$curr ?></button>
						<label class="aaf-negative"><?= sprintf("%.2f%%", $row['cours'] == 0 ? 0 : (($row['stoploss'] / $row['cours']) - 1) * 100) ?></label>
					</div></td>
					<td><?= uimx::$conseillers[$row['conseiller']] ?></td>
					<td class="center aligned"><?= $lib_diff ?></td>
					<td class="center aligned" data-value="<?= $row['status'] ?>" data-tootik="<?= $row['date_status'] ?>"><i class="inverted <?= $row['status'] == 1 ? "calendar check outline green" : ($row['status'] == -1 ? "calendar times outline red" : ($row['status'] == -2 ? "calendar minus outline red" : "clock outline")) ?> icon"></i></td>
					<td class="center aligned collapsing"><i data-value="<?= $row['id'] ?>" class="edit inverted icon"></i></td>
				<tr>
<?
			}
?>
		</tbody>
	</table>
	<div id="lst_prediction_box"></div>
</div>


<div class="ui container inverted segment">

	<h2><i class="inverted picture icon"></i>Synthèse</h2>

	<table class="ui selectable inverted single line unstackable very compact table sortable-theme-minimal" id="lst_conseiller" data-sortable>
		<thead>
			<tr>
				<th class="center aligned">Conseiller</th>
                <th class="center aligned">En cours</th>
                <th class="center aligned">Validées</th>
				<th class="center aligned">Invalidées</th>
				<th class="center aligned">Expirées</th>
				<th class="center aligned">Délai moyen validation</th>
				<th class="center aligned">Taux Réussite</th>
				<th class="center aligned">1Y</th>
				<th class="center aligned">Rendement</th>
			</tr>
		</thead>
		<tbody>
<?
			// Synthèse sur 12 mois
			$t = [];
			$req = "SELECT count(*) total, conseiller, status FROM prediction WHERE user_id=".$sess_context->getUserId()." AND date_avis >= CURDATE() - INTERVAL 12 MONTH GROUP BY conseiller, status";
			$res = dbc::execSql($req);
        	while($row = mysqli_fetch_array($res))
				$t[$row['conseiller']][$row['status']] = $row['total'];

			foreach($t as $key => $val) {
				$val[0]  = isset($val[0])  ? $val[0]  : 0;
				$val[1]  = isset($val[1])  ? $val[1]  : 0;
				$val[-1] = isset($val[-1]) ? $val[-1] : 0;
				$val[-2] = isset($val[-2]) ? $val[-2] : 0;
				$nb_predictions = $val[1] + $val[-1] + $val[-2];
				$perf_mois[$key] = $val[1] == 0 ? "-" : ($val[1] / $nb_predictions) * 100;
			}
	
			// Synthèse complète
			$t = [];
			$req = "SELECT count(*) total, conseiller, status FROM prediction WHERE user_id=".$sess_context->getUserId()." GROUP BY conseiller, status";
			$res = dbc::execSql($req);
        	while($row = mysqli_fetch_array($res))
				$t[$row['conseiller']][$row['status']] = $row['total'];

			foreach($t as $key => $val) {
				$val[0]  = isset($val[0])  ? $val[0]  : 0;
				$val[1]  = isset($val[1])  ? $val[1]  : 0;
				$val[-1] = isset($val[-1]) ? $val[-1] : 0;
				$val[-2] = isset($val[-2]) ? $val[-2] : 0;
				$nb_predictions = $val[1] + $val[-1] + $val[-2];
				$perf  = $val[1] == 0 ? 0 : ($val[1] / $nb_predictions) * 100;
				$perf2 = isset($perf_mois[$key]) ? $perf_mois[$key] : "-";

				$date_ref = date("Y-m-d", strtotime("-".(isset($tab_extend[$key]['days']) ? Round($tab_extend[$key]['days'] / $tab_extend[$key]['nb_p']) : 0)." days"));
				$datetime1 = new DateTime($date_ref);
				$datetime2 = new DateTime(date("Y-m-d"));
				$difference = $datetime1->diff($datetime2);
				$lib_diff = ($difference->y > 0 ? $difference->y.' ans, ' : '').($difference->m > 0 ? $difference->m.' mois, ' : '').$difference->d.' jours';

				echo '<tr>
					<td class="center aligned">'.uimx::$conseillers[$key].'</td>
					<td class="center aligned">'.$val[0].'</td>
					<td class="center aligned">'.$val[1].'</td>
					<td class="center aligned">'.$val[-1].'</td>
					<td class="center aligned">'.$val[-2].'</td>
					<td class="center aligned">'.(isset($tab_extend[$key]['days']) ? $lib_diff : "-").'</td>
					<td class="center aligned">'.($perf == "-" ? "0%" : sprintf("%.0f", $perf)."%").'</td>
					<td class="center aligned">'.($perf2 == "-" ? "0%" : sprintf("%.0f", $perf2)."%").'</td>
					<td class="center aligned">'.(sprintf("%.2f", $tab_extend[$key]['rendement']))."%".'</td>
				</tr>';
			}
?>

		</tbody>
	</table>
	<div id="lst_conseiller_box"></div>
</div>

<script>

// Listener sur edition bouton
Dom.find('#lst_prediction tbody tr td:last-child i').forEach(function(item) {
	let predict_id = Dom.attribute(item, 'data-value');
	Dom.addListener(item, Dom.Event.ON_CLICK, function(event) {
		go({ action: 'prediction', id: 'main', url: 'prediction_detail.php?action=upt&prediction_id='+predict_id, loading_area: 'main' });
	});
});

// Listener sur bouton actif
Dom.find('#lst_prediction tbody tr td:nth-child(2) button').forEach(function(element) {
	Dom.addListener(element, Dom.Event.ON_CLICK, function(event) {
		go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol=' + element.innerHTML, loading_area: 'main' });
	});
});

Dom.addListener(Dom.id('prediction_add_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'prediction', id: 'main', url: 'prediction_detail.php?action=new', loading_area: 'prediction_add_bt' }); });
Sortable.initTable(el("lst_prediction"));

paginator({
  table: document.getElementById("lst_prediction"),
  box: document.getElementById("lst_prediction_box")
});
</script>