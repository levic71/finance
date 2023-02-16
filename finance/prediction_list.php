<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isSuperAdmin()) tools::do_redirect("index.php");

foreach([''] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

// Récupération des devises
$devises = calc::getGSDevisesWithNoUpdate();

// Recuperation de tous les actifs
$quotes = calc::getIndicatorsLastQuote();

$req = "SELECT * FROM prediction WHERE user_id=".$sess_context->getUserId()." AND status = 0";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

	$date_objectif = date('Y-m-d');
	$date_stoploss = date('Y-m-d');
	$objectif = 0;
	$stoploss = 0;

	$req2 = "SELECT * FROM daily_time_series_adjusted WHERE symbol='".$row['symbol']."' AND day >= '".$row['date_avis']."'";
	$res2 = dbc::execSql($req2);
	while($row2 = mysqli_fetch_array($res2)) {

		if ($objectif == 0 && $row['stoploss'] >= $row2['low'])  { $stoploss = 1; $date_stoploss = $row2['day']; }
		if ($stoploss == 0 && $row2['high'] >= $row['objectif']) { $objectif = 1; $date_objectif = $row2['day']; }

	}

	if ($stoploss == 1) {
		$update = "UPDATE prediction SET status=-1, date_status='".$date_stoploss."' WHERE id=".$row['id'];
		$res3 = dbc::execSql($update);
	}

	if ($objectif == 1) {
		$update = "UPDATE prediction SET status=1, date_status='".$date_objectif."' WHERE id=".$row['id'];
		$res3 = dbc::execSql($update);
	}

}

?>

<div class="ui container inverted segment">

	<h2><i class="inverted magic icon"></i>Prédictions <button id="prediction_add_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white add icon"></i></button></h2>

	<table class="ui selectable inverted single line unstackable very compact table sortable-theme-minimal" id="lst_prediction" data-sortable>
		<thead>
			<tr>
				<th class="center aligned">Date</th>
                <th class="center aligned">Actif</th>
                <th class="center aligned">Cours Avis</th>
				<th class="center aligned">Cours J</th>
                <th class="center aligned">Objectif</th>
                <th class="center aligned">Stoploss</th>
                <th>Conseiller</th>
                <th class="center aligned">Délai</th>
                <th class="center aligned">Status</th>
				<th class="center aligned"></th>
			</tr>
		</thead>
		<tbody>
<?
			$req = "SELECT * FROM prediction WHERE user_id=".$sess_context->getUserId()." ORDER BY date_avis DESC";
			$res = dbc::execSql($req);
        	while($row = mysqli_fetch_array($res)) {

				$cj   = $quotes["stocks"][$row['symbol']]['price'];
				$curr = uimx::getCurrencySign($quotes["stocks"][$row['symbol']]['currency']);
				$ref_cours = $row['status'] == 0 ? $cj : $row['cours']; // Si status en cours alors perf par rapport au cours réel sinon par perf par rapport cours debut prediction
				$perf = $ref_cours == 0 ? 0 : (($row['objectif'] / $ref_cours) - 1) * 100;

				$datetime1 = new DateTime($row['date_avis']);
				$datetime2 = new DateTime($row['status'] == 0 ? date("Y-m-d") : $row['date_status']);
				$difference = $datetime1->diff($datetime2);

				$lib_diff = ($difference->y > 0 ? $difference->y.' ans, ' : '').($difference->m > 0 ? $difference->m.' mois, ' : '').$difference->d.' jours';
?>
				<tr>
					<td class="center aligned"><?= $row['date_avis'] ?></td>
					<td class="center aligned"><button class="tiny ui primary button"><?= $row['symbol'] ?></button></td>
					<td class="center aligned"><?= sprintf("%.2f", $row['cours']).$curr ?></td>
					<td class="center aligned"><?= sprintf("%.2f", $cj).$curr ?></td>
					<td class="center aligned"><div>
						<button class="tiny ui aaf-positive button"><?= sprintf("%.2f", $row['objectif']).$curr ?></button>
						<label class="aaf-positive"><?= sprintf("%.2f", $perf) ?>%</label>
					</div></td>
					<td class="center aligned"><div>
						<button class="tiny ui aaf-negative button"><?= sprintf("%.2f", $row['stoploss']).$curr ?></button>
						<label class="aaf-negative"><?= sprintf("%.2f", $row['cours'] == 0 ? 0 : (($row['stoploss'] / $row['cours']) - 1) * 100) ?>%</label>
					</div></td>
					<td><?= $row['conseiller'] ?></td>
					<td class="center aligned"><?= $lib_diff ?></td>
					<td class="center aligned" data-tootik="<?= $row['date_status'] ?>"><i class="inverted <?= $row['status'] == 1 ? "calendar check outline green" : ($row['status'] == -1 ? "calendar times outline red" : ($row['status'] == -2 ? "calendar minus outline red" : "clock outline")) ?> icon"></i></td>
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
				<th class="center aligned">Perf (All)</th>
				<th class="center aligned">Perf (1Y)</th>
			</tr>
		</thead>
		<tbody>
<?
			// Synthèse sur 6 mois
			$t = [];
			$req = "SELECT count(*) total, conseiller, status FROM prediction WHERE date_avis >= CURDATE() - INTERVAL 12 MONTH GROUP BY conseiller, status";
			$res = dbc::execSql($req);
        	while($row = mysqli_fetch_array($res))
				$t[$row['conseiller']][$row['status']] = $row['total'];

			foreach($t as $key => $val) {
				$val[0]  = isset($val[0])  ? $val[0]  : 0;
				$val[1]  = isset($val[1])  ? $val[1]  : 0;
				$val[-1] = isset($val[-1]) ? $val[-1] : 0;
				$nb_predictions = $val[1] + $val[-1];
				$perf_mois[$key] = $val[1] == 0 ? "-" : ($val[1] / $nb_predictions) * 100;
			}
	
			// Synthèse complète
			$t = [];
			$req = "SELECT count(*) total, conseiller, status FROM prediction GROUP BY conseiller, status";
			$res = dbc::execSql($req);
        	while($row = mysqli_fetch_array($res))
				$t[$row['conseiller']][$row['status']] = $row['total'];

			foreach($t as $key => $val) {
				$val[0]  = isset($val[0])  ? $val[0]  : 0;
				$val[1]  = isset($val[1])  ? $val[1]  : 0;
				$val[-1] = isset($val[-1]) ? $val[-1] : 0;
				$nb_predictions = $val[1] + $val[-1];
				$perf  = $val[1] == 0 ? 0 : ($val[1] / $nb_predictions) * 100;
				$perf2 = isset($perf_mois[$key]) ? $perf_mois[$key] : "-";
				echo '<tr>
					<td class="center aligned">'.$key.'</td>
					<td class="center aligned">'.$val[0].'</td>
					<td class="center aligned">'.$val[1].'</td>
					<td class="center aligned">'.$val[-1].'</td>
					<td class="center aligned">'.sprintf("%.0f", $perf).'%</td>
					<td class="center aligned">'.($perf2 == "-" ? "-" : sprintf("%.0f", $perf2)."%").'</td>
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
		go({ action: 'prediction', id: 'main', url: 'prediction.php?action=upt&prediction_id='+predict_id, loading_area: 'main' });
	});
});

// Listener sur bouton actif
Dom.find('#lst_prediction tbody tr td:nth-child(2) button').forEach(function(element) {
	Dom.addListener(element, Dom.Event.ON_CLICK, function(event) {
		go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol=' + element.innerHTML, loading_area: 'main' });
	});
});

Dom.addListener(Dom.id('prediction_add_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'prediction', id: 'main', url: 'prediction.php?action=new', loading_area: 'prediction_add_bt' }); });
Sortable.initTable(el("lst_prediction"));

paginator({
  table: document.getElementById("lst_prediction"),
  box: document.getElementById("lst_prediction_box")
});
</script>