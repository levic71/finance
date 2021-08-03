<?

include_once "include.php";

$ver = "1.2.6";
$pea = -1;
$admin = 0;

foreach(['pea', 'admin'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$admin = $admin == 1 ? true : false;

$db = dbc::connect();

$data = calc::getDualMomentum("ALL", date("Y-m-d"));

// Tri décroissant des perf DM des stocks
arsort($data["perfs"]);
	
?>

<div class="ui container inverted segment">

	<h2>Strategies <button id="home_strategie_add" class="circular ui icon very small right floated pink labelled button"><i class="inverted white add icon"></i> Ajouter</button></h2>

	<div class="ui stackable grid container" id="strategie_box">
      	<div class="row">

<?
			$tab_strat = array();
        	$req = "SELECT * FROM strategies WHERE defaut=1" ;
        	$req = "SELECT * FROM strategies" ;
        	$res = dbc::execSql($req);
        	while($row = mysqli_fetch_array($res)) {
				$tab_strat[] = $row['id'];
?>
        	<div class="four wide column">
				<?= uimx::perfCard("home_card", $row['id'], $row['title'], $data["day"], $data["perfs"], $row['data']) ?>
			</div>
<? } ?>

		</div>
    </div>
</div>

	
<div class="ui container inverted segment">

	<h2>Assets Followed <? if ($admin) { ?><button id="home_symbol_search" class="circular ui icon very small right floated pink button"><i class="inverted white add icon"></i> Ajouter</button><? } ?></h2>

	<table class="ui selectable inverted single line unstackable very compact table sortable-theme-minimal" id="lst_stock" data-sortable>
		<thead>
			<tr>
				<th></th>
				<th>Symbole</th>
                <th class="four wide">Nom</th>
                <th>Type</th>
				<th>Derniere Quotation</th>
				<th data-sortable-type="numeric">Prix</th>
				<th data-sortable-type="numeric">DM TKL</th>
				<th data-sortable-type="numeric">MM200</th>
				<th data-sortable-type="numeric">MM20</th>
				<th data-sortable-type="numeric">MM7</th>
<? if ($admin) { ?>
    		    <th></th>
<? } ?>
			</tr>
		</thead>
        <tbody id="lst_stock_body">
<?

$x = 0;

foreach($data["stocks"] as $key => $val) {

	$symbol = $key;

	$max_histo = calc::getMaxHistoryDate($symbol);

	$cache_filename = "cache/QUOTE_".$symbol.".json";
	$cache_timestamp = file_exists($cache_filename) ? date("Y-m-d", filemtime($cache_filename)) : "xxxx-xx-xx";

	$curr = $val['currency'] == "EUR" ? "&euro;" : "$";

	echo "<tr onclick=\"toogle_table('lst_stock_body', '".($x*2+1)."');\">";
	echo "
		<td><i class=\"inverted blue caret square right outline icon\"></i></td>
		<td><a onclick=\"go({ action: 'update', id: 'main', url: 'detail.php?symbol=".$val['symbol']."' });\">".$val['symbol']."</a></td>
		<td>".$val['name']."</td>
		<td>".$val['type']."</td>
		<td>".$val['day']."</td>
		<td data-value=\"".$val['price']."\">".sprintf("%.2f", $val['price']).$curr."</td>
		<td data-value=\"".$val['MMZDM']."\">".sprintf("%.2f", $val['MMZDM'])."%</td>
		<td data-value=\"".$val['MM200']."\">".sprintf("%.2f", $val['MM200']).$curr."</td>
		<td data-value=\"".$val['MM20']."\">".sprintf("%.2f", $val['MM20']).$curr."</td>
		<td data-value=\"".$val['MM7']."\">".sprintf("%.2f", $val['MM7']).$curr."</td>
	";
if ($admin) {
	echo "<td class=\"collapsing\">
			<div class=\"ui inverted checkbox\">
				<input type=\"radio\" name=\"row_symbol\" id=\"row_symbol\" value=\"".$val['symbol']."\" /> <label></label>
			</div>
	</td>";
}
	echo "</tr>";

	$tabi = [ "Devise" => $val['currency'], "Région" => $val['region'], "Marché" => $val['marketopen'].'-'.$val['marketclose'], "Timezone" => $val['timezone'], "Max Histo" => $max_histo, "Cache" => $cache_timestamp ];

	echo '<tr class="row-detail"><td></td><td colspan="10" class="ui fluid">';
	foreach($tabi as $keyi => $vali)
		echo '<div class="ui labeled button" tabindex="0">
				<div class="ui teal button">'.$keyi.'</div>
				<a class="ui basic teal left pointing label">'.$vali.'</a>
			</div>';
	echo '</td></tr>';

	$x++;
}

?>
		</tbody>
<? 	if ($admin) { ?>
		<tfoot class="full-width">
			<tr>
				<th></th>
				<th colspan="16">
					<div class="ui right floated buttons">
						<div class="ui small black button" id="delete_bt">Supprimer</div>
						<div class="ui small black button" id="update_bt">Modifier</div>
					</div>
				</th>
			</tr>
		</tfoot>
<? } ?>
	</table>
</div>

<script>
	Dom.addListener(Dom.id('home_strategie_add'), Dom.Event.ON_CLICK, function(event) { go({ action: 'strat_new', id: 'main', url: 'strategie.php?action=new', loading_area: 'home_strategie_add' }); });
<? foreach($tab_strat as $key => $val) { ?>
	Dom.addListener(Dom.id('home_sim_bt_<?= $val ?>'), Dom.Event.ON_CLICK, function(event) { go({ action: 'sim', id: 'main', url: 'simulator.php?strategie_id=<?= $val ?>', loading_area: 'home_sim_bt_<?= $val ?>' }); });
	Dom.addListener(Dom.id('home_strategie_<?= $val ?>_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'strat_upt', id: 'main', url: 'strategie.php?strategie_id=<?= $val ?>', loading_area: 'home_strategie_<?= $val ?>_bt' }); });
<? } ?>
<? if ($admin) { ?>
	Dom.addListener(Dom.id('update_bt'),  Dom.Event.ON_CLICK, function(event) { if (valof('row_symbol') != '') go({ action: 'update', id: 'main', url: 'stock_update.php?symbol='+valof('row_symbol'), loading_area: 'update_bt' }); });
	Dom.addListener(Dom.id('delete_bt'),  Dom.Event.ON_CLICK, function(event) { if (valof('row_symbol') != '') go({ action: 'delete', id: 'main', url: 'stock_delete.php?symbol='+valof('row_symbol'), loading_area: 'delete_bt', confirmdel: 1 }); });
	Dom.addListener(Dom.id('home_symbol_search'), Dom.Event.ON_CLICK, function(event) { go({ action: 'search', id: 'main', menu: 'm1_search_bt', url: 'search.php' }); });
<? } ?>
	change_wide_menu_state('wide_menu', 'm1_home_bt');
	Sortable.initTable(el("lst_stock"));
</script>