<?

require_once "sess_context.php";
include "indicators.php";
include "googlesheet/sheet.php";

ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
session_start();

include "common.php";

if (!$sess_context->isSuperAdmin()) tools::do_redirect("index.php");

$pea = 0;

foreach(['action', 'symbol', 'pea', 'name', 'type', 'region', 'marketopen', 'marketclose', 'timezone', 'currency', 'f_gf_symbol', 'f_isin', 'f_provider', 'f_categorie', 'f_frais', 'f_actifs', 'f_distribution', 'f_link1', 'f_link2', 'f_rating', 'f_tags', 'f_dividende', 'f_date_dividende'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

if ($symbol == "") tools::do_redirect("index.php");

$db = dbc::connect();


function updateSymbolData($mysymbol) {

    if (tools::useGoogleFinanceService()) $values = updateGoogleSheet();

    $periods = array();

    $ret = cacheData::buildAllCachesSymbol($mysymbol, true);

    // Recalcul des indicateurs en fct maj cache
    foreach(['daily', 'weekly', 'monthly'] as $key) $periods[] = strtoupper($key);

    computeIndicatorsForSymbolWithOptions($mysymbol, array("aggregate" => false, "limited" => 0, "periods" => $periods));

    // Mise à jour de la cote de l'actif avec la donnée GSheet
    if (isset($values[$mysymbol])) {
        $ret['gsheet'] = updateQuotesWithGSData($values[$mysymbol]);

        // Mise a jour des indicateurs du jour (avec quotes)
        computeQuoteIndicatorsSymbol($mysymbol);
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

        logger::info("STOCK", $symbol, "[OK]");
    } else {
        updateSymbolData($symbol);
    }
}

if ($action == "indic") {

    logger::info("STOCK", "INDIC", "###########################################################");

    $req = "SELECT * FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {
        updateSymbolData($symbol);

        logger::info("STOCK", $symbol, "[OK]");
    }
}

if ($action == "reload") {

    logger::info("STOCK", "RELOAD", "###########################################################");

    $req = "SELECT * FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {

        $limited_computing = 0;

        foreach(['daily_time_series_adjusted', 'weekly_time_series_adjusted', 'monthly_time_series_adjusted'] as $key) {
            $req2 = "DELETE FROM ".$key." WHERE symbol='".$row['symbol']."'";
            $res2 = dbc::execSql($req2);    
        }
        $req2 = "DELETE FROM indicators WHERE symbol='".$row['symbol']."' AND period='DAILY'";
        $res2 = dbc::execSql($req2);    
        $req2 = "DELETE FROM indicators WHERE symbol='".$row['symbol']."' AND period='WEEKLY'";
        $res2 = dbc::execSql($req2);    
        $req2 = "DELETE FROM indicators WHERE symbol='".$row['symbol']."' AND period='MONTHLY'";
        $res2 = dbc::execSql($req2);
        
        aafinance::$cache_load = true;
    
        // Mise a jour des caches : full = false => compact, sinon full (on fait les 2 dans le sens ci dessous)
        $ret = cacheData::buildCachesSymbol($row['symbol'], false, array("daily" => 1, "weekly" => 1, "monthly" => 1));
        $ret = cacheData::buildCachesSymbol($row['symbol'], true,  array("daily" => 1, "weekly" => 1, "monthly" => 1));
        
        if ($ret['daily'])   computePeriodIndicatorsSymbol($row['symbol'], $limited_computing, "DAILY");
        if ($ret['weekly'])  computePeriodIndicatorsSymbol($row['symbol'], $limited_computing, "WEEKLY");
        if ($ret['monthly']) computePeriodIndicatorsSymbol($row['symbol'], $limited_computing, "MONTHLY");

        cacheData::deleteCacheSymbol($symbol);
    }
}

if ($action == "upt" || $action == "sync") {

    logger::info("STOCK", "UPDATE", "###########################################################");

    $req = "SELECT * FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {

        $links = json_encode(array("link1" => $f_link1, "link2" => $f_link2));

        $req = "UPDATE stocks SET links='".$links."', pea=".$pea.", ISIN='".$f_isin."', provider='".$f_provider."', categorie='".$f_categorie."', frais='".$f_frais."', actifs='".$f_actifs."', distribution='".$f_distribution."', gf_symbol='".$f_gf_symbol."', rating='".$f_rating."', tags='".$f_tags."', dividende_annualise='".$f_dividende."', date_dividende='".$f_date_dividende."' WHERE symbol='".$symbol."'";
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

                updateSymbolData($symbol, true);

                logger::info("SYNC", $symbol, "[OK]");

            } catch (RuntimeException $e) {
                if ($e->getCode() == 1) logger::error("UDT", $row['symbol'], $e->getMessage());
                if ($e->getCode() == 2) logger::info("UDT", $row['symbol'], $e->getMessage());
            }
        }

        logger::info("STOCK", $symbol, "[OK]");
    }
}

if ($action == "del") {

    logger::info("STOCK", "DEL", "###########################################################");

    $req = "SELECT * FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {

        foreach(['daily_time_series_adjusted', 'weekly_time_series_adjusted', 'monthly_time_series_adjusted', 'stocks', 'quotes', 'indicators'] as $key) {
            $req = "DELETE FROM ".$key." WHERE symbol='".$symbol."'";
            $res = dbc::execSql($req);    
        }

        cacheData::deleteCacheSymbol($symbol);

        logger::info("STOCK", $symbol, "[OK]");
    }
}

?>

</div>

<script>
var p = loadPrompt();
<? if ($action == "upt" || $action == "indic" || $action == "sync" || $action == "reload") { ?>
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