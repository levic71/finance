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

?><div class="ui container inverted segment"><?

if ($action == "add" || $action == "reload") {

    $ret_add = 0;

    logger::info("STOCK", $action == "add" ? "ADD" : "RELOAD", "###########################################################");

    if ($action == "reload") {

        $req = "SELECT * FROM stocks WHERE symbol='".$symbol."'";
        $res = dbc::execSql($req);

        if ($row = mysqli_fetch_array($res)) {
            $name   = $row['name'];
            $f_type = $row['type'];
            $f_search_type = $row['type'];
            $region = $row['region'];
            $marketopen  = $row['marketopen'];
            $marketclose = $row['marketclose'];
            $timezone = $row['timezone'];
            $currency = $row['currency'];
        }

    }        

    if ($engine == "alpha") {

        // Data provenant de search.php ou stocks si reload
        $ret_add = cacheData::getAndInsertAllDataQuoteFromAlphaPlusIndicators($symbol, $name, $f_type, $region, $marketopen, $marketclose, $timezone, $currency, $engine) ? 1 : 0;

    }
    
    if ($engine == "google") {

        $gf_symbol = $symbol;
        $ret_add = cacheData::getAndInsertAllDataQuoteFromGSPlusIndicators($symbol, $f_search_type) ? 1 : 0;

    }

}

if ($action == "indic") {

    logger::info("STOCK", "INDIC", "###########################################################");

    $req = "SELECT * FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {
        computeDWMIndicators($row['symbol'], $row['engine']);
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

    $req = "SELECT count(*) total FROM orders WHERE product_name='".$symbol."' AND portfolio_id in (SELECT portfolio_id FROM portfolios WHERE user_id=".$sess_context->getUserId().")";
    $res = dbc::execSql($req);
    $row = mysqli_fetch_array($res);
    
    $del_ret = $row['total'] == 0 ? true : false;

    if ($del_ret) {

        logger::info("STOCK", "DEL", "###########################################################");

        $req = "SELECT count(*) total FROM stocks WHERE symbol='".$symbol."'";
        $res = dbc::execSql($req);
        $row = mysqli_fetch_array($res);

        if ($row['total'] == 1) {
            calc::removeSymbol($symbol);
            logger::info("STOCK", $symbol, "[OK]");
        }
    }
}

?>

</div>

<script>

var p = loadPrompt();

<? if ($action == "upt" || $action == "indic" || $action == "reload") { ?>
    go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol=<?= $symbol ?>&ptf_id=<?= $ptf_id ?>', loading_area: 'main' });
    <? if ($action != "reload" || ($action == "reload" && $ret_add)) { ?>
    p.success('Actif <?= $symbol ?> mis à jour');
    <? } else { ?>
    p.error('Actif <?= $symbol ?> non mis à jour');
    <? } ?>
<? } ?>

<? if ($action == "del") { ?>
    <? if ($del_ret) { ?>
        go({ action: 'home_content', id: 'main', url: 'home_content.php' });
        p.success('Actif <?= $symbol ?> supprimé');
    <? } else { ?>
        go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol=<?= $symbol ?>&ptf_id=<?= $ptf_id ?>', loading_area: 'main' });
        p.error('Actif <?= $symbol ?> non supprimé. Ordres existants');
    <? } ?>
<? } ?>

<? if ($action == "add") { ?>
    <? if ($ret_add) { ?>
        go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?edit=1&symbol=<?= $symbol ?>&ptf_id=<?= $ptf_id ?>', loading_area: 'main' });
        p.success('Actif <?= $symbol ?> <?= $ret_add == 1 ? 'ajouté' : 'modifié' ?>');
    <? } else { ?>
        go({ action: 'home_content', id: 'main', url: 'home_content.php' });
        p.error('Actif <?= $symbol ?> non ajouté');
    <? } ?>
<? } ?>

</script>