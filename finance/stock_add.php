<?

include_once "include.php";

foreach(['symbol', 'name', 'type', 'region', 'marketopen', 'marketclose', 'timezone', 'currency'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$timezone = urldecode($timezone);

if (isset($symbol) && $symbol != "") {

    $db = dbc::connect();

    // Recuperation des infos des assets
    $req = "SELECT count(*) total FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);
    $row = mysqli_fetch_array($res);

    if ($row['total'] == 0) {

        $name = urldecode($name);
        
        $req = "INSERT INTO stocks (symbol, name, type, region, marketopen, marketclose, timezone, currency) VALUES ('".$symbol."','".addslashes($name)."', '".$type."', '".$region."', '".$marketopen."', '".$marketclose."', '".$timezone."', '".$currency."')";

        echo $req;
        $res = dbc::execSql($req);

        cacheData::buildCacheSymbol($symbol, true);
    }
}
?>

<script>
	Swal.fire({ title: '', icon: 'info', html: "Quote <?= $symbol ?> added" });
    go({ action: 'home_content', id: 'main', url: 'home_content.php?admin=1' });
</script>