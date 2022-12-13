<?

require_once "sess_context.php";

session_start();

include "common.php";

$portfolio_id = 0;
$viewed = 0;

foreach (['portfolio_id', 'viewed'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) {
	uimx::staticInfoMsg("VOUS DEVEZ ETRE CONNECTE POUR UTILISER CETTE FONCTIONNALITE", "comment outline", "blue");
	exit(0);
}

// Recuperation des alertes non lues
$alertes = [];
$req = "SELECT * FROM alertes WHERE (user_id=".$sess_context->getUserId()." OR user_id=0) AND lue<=".$viewed." ORDER BY date DESC";
$res = dbc::execSql($req);
while ($row = mysqli_fetch_assoc($res)) {
    $alertes[] = $row;
}

?>
<div class="ui container inverted segment">

    <h2 class="ui left floated">
        <i class="inverted bullhorn icon"></i>Alertes
        <button id="home_alertes_option" class="circular ui right floated button icon_action"><i class="inverted black eye <?= $viewed == 0 ? "slash" : ""?> icon"></i></button>
    </h2>

    <table class="ui striped inverted single line unstackable very compact table sortable-theme-minimal" id="lst_alertes" data-sortable>
		<thead>
			<tr>
				<th class="center aligned">Date</th>
				<th class="center aligned">Type</th>
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
                    echo '<tr id="alerte_'.$i.'" data-alerte="'.$val['date'].'|'.$val['user_id'].'|'.$val['actif'].'|'.$val['type'].'">
                        <td class="center aligned">'.$val['date'].'</td>
                        <td class="center aligned">'.$val['type'].'</td>
                        <td data-value="'.$val['actif'].'"><div id="portfolio_alertes_'.$val['actif'].'_bt" class="ui labeled button portfolio_alerte" tabindex="0">
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
    <div id="table_box"></div>

</div>

<script>

Dom.addListener(Dom.id('home_alertes_option'),  Dom.Event.ON_CLICK, function(event) { overlay.hide(); overlay.load('portfolio_alertes.php', { 'viewed': <?= ($viewed + 1) % 2 ?> }); });


change_status_alerte = function(id) {

    let element = Dom.id('alerte_'+id);
    go({ action: 'alert_viewed', id: 'main', url: 'portfolio_alerte_viewed.php?alerte='+Dom.attribute(element, 'data-alerte'), no_data: 1 });
    element.remove();

}

Sortable.initTable(el("lst_alertes"));

paginator({
  table: document.getElementById("lst_alertes"),
  box: document.getElementById("table_box"),
  rows_per_page: 10,
  tail_call: 10
});

</script>