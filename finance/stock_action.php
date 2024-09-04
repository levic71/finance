<?

require_once "sess_context.php";
include "indicators.php";
include "googlesheet/sheet.php";

ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
session_start();

include "common.php";

if (!$sess_context->isSuperAdmin()) tools::do_redirect("index.php");

$pea = 0;
$engine = "alpha";

foreach(['action', 'engine', 'symbol', 'f_search_type', 'ptf_id', 'pea', 'name', 'region', 'marketopen', 'marketclose', 'timezone', 'currency', 'f_type', 'f_gf_symbol', 'f_isin', 'f_provider', 'f_categorie', 'f_frais', 'f_actifs', 'f_distribution', 'f_link1', 'f_link2', 'f_rating', 'f_tags', 'f_dividende', 'f_date_dividende'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

if ($symbol == "") tools::do_redirect("index.php");

$db = dbc::connect();


function updateSymbolData($symbol, $engine = "alpha") {

    $ret = [];

    // Mise a jour des valeurs du jour de tous les actifs suivis en utilisant GoogleSheet
    if (tools::useGoogleFinanceService()) $values = updateGoogleSheet();

    if ($engine == "alpha")
        $ret = cacheData::buildDailyCachesSymbol($symbol, true);
    //    $ret = cacheData::buildAllCachesSymbol($symbol, true);

        // Recalcul des indicateurs en fct maj cache
    if ($engine == "google" || ($engine == "alpha" && $ret["daily"]))
        computeIndicatorsForSymbolWithOptions($symbol, array("aggregate" => $engine == "alpha" ? true : true, "limited" => 0, "periods" => ['DAILY', 'WEEKLY', 'MONTHLY']));

    // Mise � jour de la cote de l'actif avec les donnees GoogleSheet si la valeur existe dans values
    if ($engine != "google" && isset($values[$symbol])) {
        $ret['gsheet'] = updateQuotesWithGSData($values[$symbol]);

        // Mise a jour des indicateurs du jour
        if ($engine == "google" || ($engine == "alpha" && $ret["daily"]))
            computeDailyIndicatorsSymbol($symbol);
    }

    // On supprime les fichiers cache tmp
    cacheData::deleteTMPFiles();

    return $ret;

}


?><div class="ui container inverted segment"><?

if ($action == "add") {

    $ret_add = 0;

    logger::info("STOCK", "ADD", "###########################################################");

    // Recuperation des infos des assets
    $req = "SELECT count(*) total FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);
    $row = mysqli_fetch_array($res);

    if ($row['total'] == 0) {

        if ($engine == "alpha") {

            $name = urldecode($name);
            $req = "INSERT INTO stocks (symbol, name, type, region, marketopen, marketclose, timezone, currency, engine) VALUES ('".$symbol."','".addslashes($name)."', '".$f_type."', '".$region."', '".$marketopen."', '".$marketclose."', '".$timezone."', '".$currency."', '".$engine."')";
            $res = dbc::execSql($req);

            $ret_add = 1;

        } else if ($engine == "google") {

            // Attention le symbol dans ce cas la est le gf_symbol et le ":" pose pb en tant que cl� symbol donc on remplace pas "."
            $gf_symbol = $symbol;
            $symbol = str_replace(':', '.', $symbol);
            $ret = cacheData::insertAllDataQuoteFromGS($symbol, $gf_symbol, $f_search_type);

            $ret_add = $ret ? 1 : 0;

        }

        // Bizarre mais il faudrait tout recoder proprement et dissocier add form AlphaVantage et GS
        if ($ret_add == 1) $ret = updateSymbolData($symbol, $engine);

        if ($engine == "alpha" && !$ret['daily']) {

            $req = "DELETE FROM stocks WHERE symbol='".$symbol."'";
            $res = dbc::execSql($req);

            $ret_add = 0;
        }

        logger::info("STOCK", $symbol, $ret_add == 1 ? "[OK]" : "[NOK]");

    } else {

        updateSymbolData($symbol, $engine);
        logger::info("STOCK", $symbol, "[UPT]");

        $ret_add = 2;

    }
}

if ($action == "indic") {

    logger::info("STOCK", "INDIC", "###########################################################");

    $req = "SELECT * FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {
        updateSymbolData($symbol, $row['engine']);

        logger::info("STOCK", $symbol, "[OK]");
    }
}

if ($action == "reload") {

    logger::info("STOCK", "RELOAD", "###########################################################");

    $req = "SELECT * FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {


        if ($row['engine'] == "alpha") {

            try {
                $data = aafinance::searchSymbol($row['symbol']);
                if (isset($data["bestMatches"])) {
                    foreach ($data["bestMatches"] as $key => $val) {
                        $req = "UPDATE stocks SET name='".addslashes($val["2. name"])."', type='".$val["3. type"]."', region='".$val["4. region"]."', marketopen='".$val["5. marketOpen"]."', marketclose='".$val["6. marketClose"]."', timezone='".$val["7. timezone"]."', currency='".$val["8. currency"]."' WHERE symbol='".$val["1. symbol"]."'";
                        $res = dbc::execSql($req);
                    }
                }
            } catch (RuntimeException $e) {
                if ($e->getCode() == 1) logger::error("RELOAD", $row['symbol'], $e->getMessage());
                if ($e->getCode() == 2) logger::info("RELOAD",  $row['symbol'], $e->getMessage());
            }
    
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
            
            aafinance::$cache_load = false;
        
            $ret = cacheData::buildCachesSymbol($row['symbol'], true,  array("daily" => 1, "weekly" => 1, "monthly" => 1));
            
            if ($ret['daily'])   computePeriodIndicatorsSymbol($row['symbol'], $limited_computing, "DAILY");
            if ($ret['weekly'])  computePeriodIndicatorsSymbol($row['symbol'], $limited_computing, "WEEKLY");
            if ($ret['monthly']) computePeriodIndicatorsSymbol($row['symbol'], $limited_computing, "MONTHLY");

            cacheData::deleteCacheSymbol($symbol);
        } else {
            $ret = cacheData::insertAllDataQuoteFromGS($row['symbol'], $row['gf_symbol'], $row['type'], $row['currency']);
        }

        updateSymbolData($row['symbol'], $row['engine']);

    }
}

if ($action == "upt") {

    logger::info("STOCK", "UPDATE", "###########################################################");

    $req = "SELECT * FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {

        $links = json_encode(array("link1" => $f_link1, "link2" => $f_link2));

        // Mise a jour des data informatives de l'actif
        $req = "UPDATE stocks SET type='".$f_type."', links='".$links."', pea=".$pea.", ISIN='".$f_isin."', provider='".$f_provider."', categorie='".$f_categorie."', frais='".$f_frais."', actifs=".($f_actifs ? $f_actifs : 0).", distribution='".$f_distribution."', gf_symbol='".$f_gf_symbol."', rating=".$f_rating.", tags='".$f_tags."', dividende_annualise=".($f_dividende ? $f_dividende : 0).", date_dividende=".($f_date_dividende ? "'".$f_date_dividende."'" : "NULL")." WHERE symbol='".$symbol."'";
        $res = dbc::execSql($req);

        logger::info("STOCK", $symbol, "[OK]");
    }
}

if ($action == "del") {

    logger::info("STOCK", "DEL", "###########################################################");

    $req = "SELECT * FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {
        calc::removeSymbol($symbol);
        logger::info("STOCK", $symbol, "[OK]");
    }
}

?>

</div>

<script>

var p = loadPrompt();

<? if ($action == "upt" || $action == "indic" || $action == "reload") { ?>
    go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol=<?= $symbol ?>&ptf_id=<?= $ptf_id ?>', loading_area: 'main' });
    p.success('Actif <?= $symbol ?> mis � jour');
<? } ?>

<? if ($action == "del") { ?>
    go({ action: 'home_content', id: 'main', url: 'home_content.php' });
    p.success('Actif <?= $symbol ?> supprim�');
<? } ?>

<? if ($action == "add") { ?>
    <? if ($ret_add > 0) { ?>
        go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?edit=1&symbol=<?= $symbol ?>&ptf_id=<?= $ptf_id ?>', loading_area: 'main' });
        p.success('Actif <?= $symbol ?> <?= $ret_add == 1 ? 'ajout�' : 'modifi�' ?>');
    <? } else { ?>
        go({ action: 'home_content', id: 'main', url: 'home_content.php' });
        p.error('Actif <?= $symbol ?> non ajout�');
    <? } ?>
<? } ?>

</script>