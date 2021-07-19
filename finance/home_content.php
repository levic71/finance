<?

include_once "include.php";

$ver = "1.2.6";
$pea = -1;
$admin = 0;

foreach(['pea', 'admin'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$admin = $admin == 1 ? true : false;
	
?>
	
<div class="ui container inverted segment">
	<div class="ui inverted dimmer">
    	<div class="ui text loader">Processing</div>
  	</div>
	<table class="ui selectable inverted single line fixed table">
		<thead>
			<tr>
<? if ($admin) { ?>
    		    <th></th>
<? } ?>
				<th>Symbol</th>
                <th>Name</th>
                <th>Currency</th>
                <th class="center aligned">Type</th>
				<th>Region</th>
				<th class="center aligned">Market Hours</th>
				<th class="center aligned">Time Zone</th>
				<th class="center aligned">Cache Quote</th>
				<th class="center aligned">Last Day Quote</th>
				<th class="center aligned">Max Archive</th>
				<th class="right aligned">Price</th>
				<th class="right aligned">DM Float</th>
				<th class="right aligned">DM TKL</th>
				<th class="right aligned">MM200</th>
				<th class="right aligned">MM20</th>
				<th class="right aligned">MM7</th>
			</tr>
		</thead>
        <tbody>
<?

$db = dbc::connect();

$data = calc::getDualMomentum("ALL", date("Y-m-d"));

// Tri décroissant des perf DM des stocks
arsort($data["perfs"]);

foreach($data["stocks"] as $key => $val) {

	$symbol = $key;

	$max_histo = calc::getMaxHistoryDate($symbol);

	$cache_filename = "cache/QUOTE_".$symbol.".json";
	$cache_timestamp = file_exists($cache_filename) ? date("Y-m-d", filemtime($cache_filename)) : "xxxx-xx-xx";

	echo "<tr>";
	if ($admin) {
		echo "<td class=\"collapsing\">
        		<div class=\"ui inverted checkbox\">
          			<input type=\"radio\" name=\"row_symbol\" id=\"row_symbol\" value=\"".$val['symbol']."\" /> <label></label>
        		</div>
    	</td>";
	}
	echo "
		<td><a onclick=\"go({ action: 'update', id: 'main', url: 'detail.php?symbol=".$val['symbol']."' });\">".$val['symbol']."</a></td>
		<td>".$val['name']."</td>
		<td>".$val['currency']."</td>
		<td>".$val['type']."</td>
		<td>".$val['region']."</td>
		<td>".$val['marketopen']."-".$val['marketclose']."</td>
		<td>".$val['timezone']."</td>
		<td>".$cache_timestamp."</td>
		<td>".$val['day']."</td>
		<td>".$max_histo."</td>
		<td>".sprintf("%.2f", $val['price'])."</td>
		<td>".sprintf("%.2f", $val['MMFDM'])."%</td>
		<td>".sprintf("%.2f", $val['MMZDM'])."%</td>
		<td>".sprintf("%.2f", $val['MM200'])."</td>
		<td>".sprintf("%.2f", $val['MM20'])."</td>
		<td>".sprintf("%.2f", $val['MM7'])."</td>
	";
	
	echo "</tr>";
}

?>
		</tbody>
<? 	if ($admin) { ?>
		<tfoot class="full-width">
			<tr>
				<th></th>
				<th colspan="16">
					<div class="ui primary small button"  id="update_bt">Update</div>
					<div class="ui negative small button" id="delete_bt">Delete</div>
				</th>
			</tr>
		</tfoot>
<? } ?>
	</table>
</div>

<div class="ui stripe inverted segment">
    <div class="ui stackable grid container">
      	<div class="row">
        	<div class="four wide column">
				<?= "DM RP PEA [".$data["day"]."]" ?>
<?
echo "<ul>";
foreach($data["perfs"] as $key => $val) {
	if ($key == "BRE.PAR" || $key == "ESE.PAR" || $key == "PUST.PAR" || $key == "OBLI.PAR") echo "<li>".$key." : ".$val."</li>";
}
echo "</ul>";
?>

			</div>
        	<div class="four wide column">
				<?= "DM+ RP PEA [".$data["day"]."]" ?>
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

<script>
	Dom.addListener(Dom.id('update_bt'),  Dom.Event.ON_CLICK, function(event) { if (valof('row_symbol') != '') go({ action: 'update', id: 'main', url: 'stock_update.php?symbol='+valof('row_symbol'), loading_area: 'update_bt' }); });
	Dom.addListener(Dom.id('delete_bt'),  Dom.Event.ON_CLICK, function(event) { if (valof('row_symbol') != '') go({ action: 'delete', id: 'main', url: 'stock_delete.php?symbol='+valof('row_symbol'), loading_area: 'delete_bt', confirmdel: 1 }); });
</script>