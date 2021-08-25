<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isSuperAdmin()) tools::do_redirect("index.php");

$pea = 0;

foreach(['action', 'symbol', 'pea', 'name', 'type', 'region', 'marketopen', 'marketclose', 'timezone', 'currency'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

if ($symbol == "") tools::do_redirect("index.php");

$db = dbc::connect();

?>

<div class="ui container inverted segment">

<?

if ($action == "add") {

    // Recuperation des infos des assets
    $req = "SELECT count(*) total FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);
    $row = mysqli_fetch_array($res);

    if ($row['total'] == 0) {

        $name = urldecode($name);
        
        $req = "INSERT INTO stocks (symbol, name, type, region, marketopen, marketclose, timezone, currency) VALUES ('".$symbol."','".addslashes($name)."', '".$type."', '".$region."', '".$marketopen."', '".$marketclose."', '".$timezone."', '".$currency."')";
        $res = dbc::execSql($req);

        cacheData::buildCacheSymbol($symbol, true);
    }
}

if ($action == "upt_cache") {

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
}

if ($action == "upt") {

    $req = "SELECT * FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {
        $req = "UPDATE stocks SET pea=".$pea." WHERE symbol='".$symbol."'";
        $res = dbc::execSql($req);
    }
}

if ($action == "del") {

    $req = "SELECT * FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {
        $req = "DELETE FROM stocks WHERE symbol='".$symbol."'";
        $res = dbc::execSql($req);

        cacheData::deleteCacheSymbol($symbol);
    }
}

?>

</div>

<script>
    var p = loadPrompt();
	<? if ($action == "upt" || $action == "upt_cache") { ?>
        p.success('Actif <?= $symbol ?> mis à jour');
	<? } ?>
	<? if ($action == "del") { ?>
        p.warm('Actif <?= $symbol ?> supprimé');
	<? } ?>
	<? if ($action == "add") { ?>
        p.success('Actif <?= $symbol ?> ajouté');
	<? } ?>
    go({ action: 'home_content', id: 'main', url: 'home_content.php' });
</script>