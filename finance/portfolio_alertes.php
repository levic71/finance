<?

require_once "sess_context.php";

session_start();

include "common.php";

$portfolio_id = 0;

foreach (['portfolio_id'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) {
	uimx::staticInfoMsg("VOUS DEVEZ ETRE CONNECTE POUR UTILISER CETTE FONCTIONNALITE", "comment outline", "blue");
	exit(0);
}

$alertes = [];
// Recuperation des infos du portefeuille
$req = "SELECT * FROM alertes WHERE (user_id=".$sess_context->getUserId()." OR user_id=0) AND lue=0";
$res = dbc::execSql($req);

// Bye bye si inexistant
while ($row = mysqli_fetch_assoc($res)) {
    $alertes[] = $row;
}

?>
<div class="ui container inverted segment">

    <h2 class="ui left floated"><i class="inverted bullhorn icon"></i>Alertes</h2>

    <table class="ui striped inverted single line unstackable very compact table sortable-theme-minimal" id="lst_alertes" data-sortable>
		<thead>
			<tr>
				<th class="center aligned">Date</th>
				<th>Actif</th>
				<th>Valeur</th>
			</tr>
		</thead>
		<tbody>
			<?
                foreach ($alertes as $key => $val) {
                    echo '<tr>
                        <td class="center aligned">'.$val['date'].'</td>
                        <td><button class="mini ui primary button">'.$val['actif'].'</button></td>
                        <td>'.$val['actif'].'</td>
                    </tr>';
				}
			?>				
		</tbody>
	</table>

</div>

<script>

</script>