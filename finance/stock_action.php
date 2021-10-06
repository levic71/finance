<?

require_once "sess_context.php";
include "indicators.php";

session_start();

include "common.php";

if (!$sess_context->isSuperAdmin()) tools::do_redirect("index.php");

$pea = 0;

foreach(['action', 'symbol', 'pea', 'name', 'type', 'region', 'marketopen', 'marketclose', 'timezone', 'currency', 'gf_symbol'] as $key)
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

        cacheData::buildAllsCachesSymbol($symbol, true);

        computeIndicators($symbol, 0);
    }
}

if ($action == "upt") {

    $req = "SELECT * FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {

        $req = "UPDATE stocks SET pea=".$pea.", gf_symbol='".$gf_symbol."' WHERE symbol='".$symbol."'";
        $res = dbc::execSql($req);

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

            computeIndicators($symbol, 0);

        } catch (RuntimeException $e) {
            if ($e->getCode() == 1) logger::error("UDT", $row['symbole'], $e->getMessage());
            if ($e->getCode() == 2) logger::info("UDT", $row['symbole'], $e->getMessage());
        }
    }
}

if ($action == "del") {

    $req = "SELECT * FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {

        foreach(['daily_time_series_adjusted', 'weekly_time_series_adjusted', 'monthly_time_series_adjusted', 'stocks', 'quotes', 'indicators'] as $key) {
            $req = "DELETE FROM ".$key." WHERE symbol='".$symbol."'";
            $res = dbc::execSql($req);    
        }

        cacheData::deleteCacheSymbol($symbol);
    }
}

?>

</div>

<script>
var p = loadPrompt();
<? if ($action == "upt") { ?>
    go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol=<?= $symbol ?>', loading_area: 'main' });
    p.success('Actif <?= $symbol ?> mis à jour');
<? } ?>
<? if ($action == "del") { ?>
go({ action: 'home_content', id: 'main', url: 'home_content.php' });
p.success('Actif <?= $symbol ?> supprimé');
<? } ?>
<? if ($action == "add") { ?>
    go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol=<?= $symbol ?>', loading_area: 'main' });
    p.success('Actif <?= $symbol ?> ajouté');
<? } ?>
</script>