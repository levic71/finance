<?

include_once "include.php";

$symbol = "";

foreach(['symbol'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

if ($symbol == "") exit;

$db = dbc::connect();

?>

<div class="ui container inverted segment">
	<table class="ui selectable inverted single line fixed table">
        <thead>
                        <tr>
                            <th>Symbol</th>
                            <th>Name</th>
                            <th>Currency</th>
                            <th>Type</th>
                            <th>Region</th>
                            <th>Market Hours</th>
                            <th>Time Zone</th>
                            <th>Last Day Quote</th>
                            <th>Price</th>
                            <th>DM flottant</th>
                            <th>DM TKL</th>
                            <th>MM200</th>
                            <th>MM20</th>
                            <th>MM7</th>
                        </tr>
                    </thead>
                    <tbody>
<?

$req = "SELECT * FROM stocks s, quotes q WHERE s.symbol = q.symbol AND s.symbol='".$symbol."'";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

	$c = calc::processData($row['symbol'], date("Y-m-d"));

	echo "<tr>
		<td>".$row['symbol']."</td><td></td><td>".$row['currency']."</td>
		<td>".$row['type']."</td><td>".$row['region']."</td>
        <td>".$row['marketopen']."-".$row['marketclose']."</td><td>".$row['timezone']."</td>
		<td>".$row['day']."</td><td>".$row['price']."</td>
		<td>".$c['MMFDM']."%</td>
		<td>".$c['MMZDM']."%</td>
		<td>".$c['MM200']."</td>
		<td>".$c['MM20']."</td>
		<td>".$c['MM7']."</td>
		</tr>
	";

}

?>
                    </tbody>
                </table>
</div>

<div class="ui stripe inverted segment">
    <div class="ui stackable grid container">
      	<div class="row">
        	<div class="eight wide column">

                <ul> 
<?
    echo "<li>Ref date MMZ1M => ".$c['MMZ1MDate']."</li>";
    echo "<li>Ref date MMZ3M => ".$c['MMZ3MDate']."</li>";
    echo "<li>Ref date MMZ6M => ".$c['MMZ6MDate']."</li>";

    foreach(cacheData::$lst_cache as $key)
        echo "<li>Cache ".$key."_".$symbol.".json ".(file_exists("cache/".$key."_".$symbol.".json") ? "Ok" : "NOK")."</li>";

?>
                </ul>
            </div>
        </div>
    </div>
</div>
