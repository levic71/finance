<?

include_once "include.php";

$symbol = "";

foreach(['symbol'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

if ($symbol == "") exit;

$db = dbc::connect();

// Recuperation des infos des assets
$req = "DELETE FROM stock WHERE symbol='".$symbol."'";
$res = dbc::execSql($req);

cacheData::deleteCacheSymbol($symbol);

?>
<script>
	Swal.fire({ title: '', icon: 'info', html: "Stock <?= $symbol ?> deleted" });
    go({ action: 'home_content', id: 'main', url: 'home_content.php?admin=1' });
</script>