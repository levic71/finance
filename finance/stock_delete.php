<?

include_once "include.php";
$symbol = isset($_GET["symbol"]) ? $_GET["symbol"] : "";

if (isset($symbol) && $symbol != "") {

    $db = dbc::connect();

    // Recuperation des infos des assets
    $req = "DELETE FROM stock WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

    cacheData::deleteCacheSymbol($symbol);
}

?>