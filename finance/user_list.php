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

	<h2>Comptes <button id="user_add_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white add icon"></i> Utilisateur</button></h2>

	<table class="ui selectable inverted single line unstackable very compact table sortable-theme-minimal" id="lst_users" data-sortable>
		<thead>
			<tr>
				<th>Email</th>
                <th>Statut</th>
                <th>Inscription</th>
                <th>Confirmation</th>
                <th>Abonnement</th>
                <th>Nb Strategies</th>
                <th>Nb Portfolios</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
<?
			$req = "SELECT *, (SELECT count(*) FROM strategies s WHERE s.user_id= users.id) total_strategies, (SELECT count(*) FROM portfolios p WHERE p.user_id= users.id) total_portfolios FROM users";
			$res = dbc::execSql($req);
        	while($row = mysqli_fetch_array($res)) {
?>
				<tr>
					<td class="five wide"><?= $row['email'] ?></td>
					<td><i class="ui inverted <?= $row['status'] == 1 ? "green check" : "red cancel" ?> icon"></i></td>
					<td><?= $row['date_inscription'] ?></td>
					<td><i class="ui inverted <?= $row['confirmation'] == 1 ? "green check" : "red cancel" ?> icon"></i></td>
					<td><?= $row['abonnement'] ?></td>
					<td><?= $row['total_strategies'] ?></td>
					<td><?= $row['total_portfolios'] ?></td>
					<td>
						<i class="ui inverted edit icon"  onclick="go({ action: 'user', id: 'main', url: 'user.php?action=upt&item_id=<?= $row['id'] ?>' });"></i>
						<i class="ui inverted trash icon" onclick="go({ action: 'user', id: 'main', url: 'user_action.php?action=del&item_id=<?= $row['id'] ?>', confirmdel: 1 });"></i>
					</td>
				<tr>
<?
			}
?>
		</tbody>
	</table>
</div>

<script>
	Dom.addListener(Dom.id('user_add_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'user', id: 'main', url: 'user.php?action=new', loading_area: 'user_add_bt' }); });
	change_wide_menu_state('wide_menu', 'm1_users_bt');
	Sortable.initTable(el("lst_users"));
</script>