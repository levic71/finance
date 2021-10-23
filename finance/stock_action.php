<?

require_once "sess_context.php";
include "indicators.php";
include "googlesheet/sheet.php";

ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
session_start();

include "common.php";

if (!$sess_context->isSuperAdmin()) tools::do_redirect("index.php");

$pea = 0;

foreach(['action', 'symbol', 'pea', 'name', 'type', 'region', 'marketopen', 'marketclose', 'timezone', 'currency', 'gf_symbol'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

if ($symbol == "") tools::do_redirect("index.php");

$db = dbc::connect();


function updateSymbolData($symbol, $force = 0) {

    if (tools::useGoogleFinanceService()) $values = updateGoogleSheet();

    $periods = array();

    $ret = cacheData::buildAllCachesSymbol($symbol, true);

    // Recalcul des indicateurs en fct maj cache
    if ($force == 0)
        foreach(['daily', 'weekly', 'monthly'] as $key) if ($ret[$key]) $periods[] = strtoupper($key);
    else
        foreach(['daily', 'weekly', 'monthly'] as $key) $periods[] = strtoupper($key);

    computeIndicatorsForSymbolWithOptions($symbol, array("aggregate" => false, "limited" => 1, "periods" => $periods));

    // Mise à jour de la cote de l'actif avec la donnée GSheet
    if (isset($values[$symbol])) {
        $ret['gsheet'] = updateQuotesWithGSData($values[$symbol]);

        // Mise a jour des indicateurs du jour (avec quotes)
        computeQuoteIndicatorsSymbol($symbol);
    }

    // On supprime les fichiers cache tmp
    cacheData::deleteTMPFiles();

}


?><div class="ui container inverted segment"><?

if ($action == "add") {

    logger::info("STOCK", "ADD", "###########################################################");

    // Recuperation des infos des assets
    $req = "SELECT count(*) total FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);
    $row = mysqli_fetch_array($res);

    if ($row['total'] == 0) {

        $name = urldecode($name);
        
        $req = "INSERT INTO stocks (symbol, name, type, region, marketopen, marketclose, timezone, currency) VALUES ('".$symbol."','".addslashes($name)."', '".$type."', '".$region."', '".$marketopen."', '".$marketclose."', '".$timezone."', '".$currency."')";
        $res = dbc::execSql($req);

        updateSymbolData($symbol);
    }
}

if ($action == "indic") {

    logger::info("STOCK", "UPDATE", "###########################################################");

    $req = "SELECT * FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {
        updateSymbolData($symbol);
    }
}

if ($action == "upt" || $action == "sync") {

    logger::info("STOCK", "UPDATE", "###########################################################");

    $req = "SELECT * FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {

        $req = "UPDATE stocks SET pea=".$pea.", gf_symbol='".$gf_symbol."' WHERE symbol='".$symbol."'";
        $res = dbc::execSql($req);

        if ($action == "sync") {
            try {

                $data = aafinance::searchSymbol($symbol);

                if (isset($data["bestMatches"])) {
                    foreach ($data["bestMatches"] as $key => $val) {
                        $req = "UPDATE stocks SET name='".addslashes($val["2. name"])."', type='".$val["3. type"]."', region='".$val["4. region"]."', marketopen='".$val["5. marketOpen"]."', marketclose='".$val["6. marketClose"]."', timezone='".$val["7. timezone"]."', currency='".$val["8. currency"]."' WHERE symbol='".$val["1. symbol"]."'";
                        $res = dbc::execSql($req);
                    }
                }

                updateSymbolData($symbol);

            } catch (RuntimeException $e) {
                if ($e->getCode() == 1) logger::error("UDT", $row['symbole'], $e->getMessage());
                if ($e->getCode() == 2) logger::info("UDT", $row['symbole'], $e->getMessage());
            }
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
<? if ($action == "upt" || $action == "indic" || $action == "sync") { ?>
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