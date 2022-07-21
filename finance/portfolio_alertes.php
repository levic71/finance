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
				<th>Alerte</th>
				<th class="center aligned">Action</th>
			</tr>
		</thead>
		<tbody>
			<?
                $i = 0;
                foreach ($alertes as $key => $val) {
                    $i++;
                    // if ($i == 0) var_dump($val);
                    echo '<tr id="alerte_'.$i.'">
                        <td class="center aligned">'.$val['date'].'</td>
                        <td><div id="portfolio_alertes_'.$val['actif'].'_bt" class="ui labeled button portfolio_alerte" tabindex="0">
                            <div class="ui '.$val['couleur'].' button">
                                <i class="'.$val['icone'].' inverted icon"></i>'.$val['actif'].'
                            </div>
                            <a class="ui basic '.$val['couleur'].' left pointing label">'.sprintf(is_numeric($val['seuil']) ? "%.2f " : "%s ", $val['seuil']).'</a>
                        </div></td>
                        <td class="center aligned"><i onclick="change_status_alerte('.$i.');" class="eye inverted icon"></i></td>
                    </tr>';
				}
			?>				
		</tbody>
	</table>

</div>

<script>

change_status_alerte = function(id) {

    go({ action: 'alert_viewed', id: 'main', url: 'portfolio_alerte_viewed.php?date=&symbol='+Dom.attribute(element, 'data-sym'), no_data: 1 });
    Dom.id('alerte_'+id).remove();

}

</script>