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

<div class="ui stripe inverted segment">

	<h2>Scoring</h2>

	<div class="ui stackable grid container">
      	<div class="row">
        	<div class="four wide column">

				<div class="ui inverted card">
					<div class="content">
						<div class="header">DM RP PEA</div>
					    <div class="meta"><?= $data["day"] ?></div>
						<div class="description">
<?
echo "<ul>";
foreach($data["perfs"] as $key => $val) {
	if ($key == "BRE.PAR" || $key == "ESE.PAR" || $key == "PUST.PAR" || $key == "OBLI.PAR") echo "<li>".$key." : ".$val."</li>";
}
echo "</ul>";
?>
						</div>
					</div>
				</div>

			</div>

			<div class="four wide column">

			<div class="ui inverted card">
					<div class="content">
						<div class="header">DM+ PEA</div>
					    <div class="meta"><?= $data["day"] ?></div>
						<div class="description">
						<?
echo "<ul>";
foreach($data["perfs"] as $key => $val) {
	if ($key == "GWT.PAR" || $key == "PMEH.PAR" || $key == "BRE.PAR" || $key == "ESE.PAR" || $key == "PUST.PAR" || $key == "OBLI.PAR") echo "<li>".$key." : ".$val."</li>";
}
echo "</ul>";
?>
						</div>
					</div>
				</div>

			</div>

		</div>
    </div>
</div>

	
<div class="ui container inverted segment">

	<h2>Assets List</h2>

	<table class="ui selectable inverted table" id="lst_stock">
		<thead>
			<tr>
				<th></th>
				<th>Symbol</th>
                <th class="four wide">Name</th>
                <th class="center aligned">Type</th>
				<th class="center aligned">Last Day Quote</th>
				<th class="right aligned">Price</th>
				<th class="right aligned">DM Float</th>
				<th class="right aligned">DM TKL</th>
				<th class="right aligned">MM200</th>
				<th class="right aligned">MM20</th>
				<th class="right aligned">MM7</th>
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

	echo "<tr>";
	echo "
		<td><i class=\"inverted blue play icon\" onclick=\"toogle_table('lst_stock_body', '".($x*2+1)."');\"></i></td>
		<td><a onclick=\"go({ action: 'update', id: 'main', url: 'detail.php?symbol=".$val['symbol']."' });\">".$val['symbol']."</a></td>
		<td>".$val['name']."</td>
		<td>".$val['type']."</td>
		<td>".$val['day']."</td>
		<td>".sprintf("%.2f", $val['price']).$curr."</td>
		<td>".sprintf("%.2f", $val['MMFDM'])."%</td>
		<td>".sprintf("%.2f", $val['MMZDM'])."%</td>
		<td>".sprintf("%.2f", $val['MM200']).$curr."</td>
		<td>".sprintf("%.2f", $val['MM20']).$curr."</td>
		<td>".sprintf("%.2f", $val['MM7']).$curr."</td>
	";
if ($admin) {
	echo "<td class=\"collapsing\">
			<div class=\"ui inverted checkbox\">
				<input type=\"radio\" name=\"row_symbol\" id=\"row_symbol\" value=\"".$val['symbol']."\" /> <label></label>
			</div>
	</td>";
}
		echo "</tr>";

	echo "<tr class=\"row-detail\">
		<td></td>
		<td colspan=\"10\">
		currency=".$val['currency']." ::
		region=".$val['region']." ::
		market=".$val['marketopen']."-".$val['marketclose']." :: 
		timezone=".$val['timezone']." :: 
		max archive=".$max_histo." :: 
		cache=".$cache_timestamp."
	</td></tr>";

	$x++;
}

?>
		</tbody>
<? 	if ($admin) { ?>
		<tfoot class="full-width">
			<tr>
				<th></th>
				<th colspan="16">
					<div class="ui negative small right floated button" id="delete_bt">Delete</div>
					<div class="ui primary small right floated button"  id="update_bt">Update</div>
				</th>
			</tr>
		</tfoot>
<? } ?>
	</table>
</div>

<script>
	Dom.addListener(Dom.id('update_bt'),  Dom.Event.ON_CLICK, function(event) { if (valof('row_symbol') != '') go({ action: 'update', id: 'main', url: 'stock_update.php?symbol='+valof('row_symbol'), loading_area: 'update_bt' }); });
	Dom.addListener(Dom.id('delete_bt'),  Dom.Event.ON_CLICK, function(event) { if (valof('row_symbol') != '') go({ action: 'delete', id: 'main', url: 'stock_delete.php?symbol='+valof('row_symbol'), loading_area: 'delete_bt', confirmdel: 1 }); });
	change_wide_menu_state('wide_menu', 'm1_home_bt');
</script>