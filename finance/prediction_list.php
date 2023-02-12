<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isSuperAdmin()) tools::do_redirect("index.php");

foreach([''] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();
	
?>

<div class="ui container inverted segment">

	<h2>Prédictions <button id="prediction_add_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white add icon"></i></button></h2>

	<table class="ui selectable inverted single line unstackable very compact table sortable-theme-minimal" id="lst_prediction" data-sortable>
		<thead>
			<tr>
				<th>Date</th>
                <th>Actif</th>
                <th>Cours</th>
                <th>Objectif</th>
                <th>Stoploss</th>
                <th>Conseiller</th>
                <th>Status</th>
			</tr>
		</thead>
		<tbody>
<?
			$req = "SELECT * FROM prediction WHERE user_id=".$sess_context->getUserId();
			$res = dbc::execSql($req);
        	while($row = mysqli_fetch_array($res)) {
?>
				<tr>
					<td><?= $row['date'] ?></td>
					<td><?= $row['symbol'] ?></td>
					<td><?= $row['cours'] ?></td>
					<td><?= $row['objectif'] ?></td>
					<td><?= $row['stoploss'] ?></td>
					<td><?= $row['conseiller'] ?></td>
					<td><?= $row['status'] ?></td>
				<tr>
<?
			}
?>
		</tbody>
	</table>
	<div id="lst_prediction_box"></div>
</div>

<script>
Dom.addListener(Dom.id('prediction_add_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'prediction', id: 'main', url: 'prediction.php?action=new', loading_area: 'prediction_add_bt' }); });
Sortable.initTable(el("lst_prediction"));

paginator({
  table: document.getElementById("lst_prediction"),
  box: document.getElementById("lst_prediction_box")
});
</script>