<?

include_once "include.php";
$symbol = isset($_GET["symbol"]) ? $_GET["symbol"] : "";

foreach(['symbol', 'name', 'type', 'region', 'marketopen', 'marketclose', 'timezone', 'currency'] as $key)
    $$key = isset($_GET[$key]) ? $_GET[$key] : "";

if (isset($symbol) && $symbol != "") {

    $db = dbc::connect();

    // Recuperation des infos des assets
    $req = "SELECT count(*) total FROM stock WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);
    $row = mysqli_fetch_array($res);

    if ($row['total'] == 0) {
        $req = "INSERT INTO stock (symbol, name, type, region, marketopen, marketclose, timezone, currency) VALUES ('".$symbol."','".addslashes($name)."', '".$type."', '".$region."', '".$marketopen."', '".$marketclose."', '".$timezone."', '".$currency."')";
        $res = dbc::execSql($req);

        cacheData::buildCacheSymbol($symbol);
    }

}

?>