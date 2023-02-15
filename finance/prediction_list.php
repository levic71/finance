<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isSuperAdmin()) tools::do_redirect("index.php");

foreach([''] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

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
                <th>Actif</th>
                <th class="center aligned">Cours</th>
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
			$req = "SELECT * FROM prediction WHERE user_id=".$sess_context->getUserId();
			$res = dbc::execSql($req);
        	while($row = mysqli_fetch_array($res)) {

				$datetime1 = new DateTime($row['date_avis']);
				$datetime2 = new DateTime($row['status'] == 0 ? date("Y-m-d") : $row['date_status']);
				$difference = $datetime1->diff($datetime2);

				$lib_diff = ($difference->y > 0 ? $difference->y.' ans, ' : '').($difference->m > 0 ? $difference->m.' mois, ' : '').$difference->d.' jours';
?>
				<tr>
					<td class="center aligned"><?= $row['date_avis'] ?></td>
					<td><?= $row['symbol'] ?></td>
					<td class="center aligned"><?= $row['cours'] ?></td>
					<td class="center aligned"><?= $row['objectif'] ?></td>
					<td class="center aligned"><?= $row['stoploss'] ?></td>
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

<script>

Dom.find('#lst_prediction tbody tr td:last-child i').forEach(function(item) {
	let predict_id = Dom.attribute(item, 'data-value');
	Dom.addListener(item, Dom.Event.ON_CLICK, function(event) {
		go({ action: 'prediction', id: 'main', url: 'prediction.php?action=upt&prediction_id='+predict_id, loading_area: 'main' });
	});
});

Dom.addListener(Dom.id('prediction_add_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'prediction', id: 'main', url: 'prediction.php?action=new', loading_area: 'prediction_add_bt' }); });
Sortable.initTable(el("lst_prediction"));

paginator({
  table: document.getElementById("lst_prediction"),
  box: document.getElementById("lst_prediction_box")
});
</script>