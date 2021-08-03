<?

include_once "include.php";

$symbol = "";

foreach(['symbol'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

if ($symbol == "") exit;

$db = dbc::connect();

try {

    $data = aafinance::searchSymbol($symbol);

    if (isset($data["bestMatches"])) {
        foreach ($data["bestMatches"] as $key => $val) {
            $req = "UPDATE stocks SET name='".addslashes($val["2. name"])."', type='".$val["3. type"]."', region='".$val["4. region"]."', marketopen='".$val["5. marketOpen"]."', marketclose='".$val["6. marketClose"]."', timezone='".$val["7. timezone"]."', currency='".$val["8. currency"]."' WHERE symbol='".$val["1. symbol"]."'";
            $res = dbc::execSql($req);
        }
    }

    unlink('cache/QUOTE_'.$symbol.'.json');
    cacheData::buildCacheQuote($symbol);

} catch (RuntimeException $e) {
    if ($e->getCode() == 1) logger::error("UDT", $row['symbole'], $e->getMessage());
    if ($e->getCode() == 2) logger::info("UDT", $row['symbole'], $e->getMessage());
}

?>
<script>
	Swal.fire({ title: '', icon: 'info', html: "Mise à jour quote <?= $symbol ?> OK" });
</script>