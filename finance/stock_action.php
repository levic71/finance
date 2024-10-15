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
$f_levier = 1;
$f_sousjacent = "";
$f_ticker = "";
$f_callput = "";
$f_emetteur = "";
$f_val_init = 0;
$f_val_prev = 0;
$f_expire = 0;

foreach(['action', 'engine', 'symbol', 'f_search_type', 'ptf_id', 'pea', 'name', 'region', 'marketopen', 'marketclose', 'timezone', 'currency', 'f_type', 'f_gf_symbol', 'f_isin', 'f_provider', 'f_categorie', 'f_frais', 'f_actifs', 'f_distribution', 'f_link1', 'f_link2', 'f_rating', 'f_tags', 'f_dividende', 'f_date_dividende', 'f_ticker', 'f_callput', 'f_emetteur', 'f_levier', 'f_sousjacent', 'f_val_init', 'f_val_prev', 'f_expire'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

// Suppression des espaces
$symbol = str_replace(' ', '', $symbol);

// Remplacement
$f_val_init = str_replace(',', '.', $f_val_init);
$f_val_prev = str_replace(',', '.', $f_val_prev);

if ($symbol == "" && $f_callput == "") tools::do_redirect("index.php");

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

    if ($engine == "manual" && $action == "add") { // pas de reload pour les turbos

        // Recuperation de tous les actifs
        $quotes = calc::getIndicatorsLastQuote();
        $sousjacent = $quotes["stocks"][$f_sousjacent];

        $symbol = QuoteComputing::getComplexQuoteName($f_emetteur, $f_sousjacent, $f_callput, $f_levier, $f_ticker);
        $ret_add = cacheData::insertComplexProduct($f_emetteur, $f_ticker, $f_callput, $f_levier, $sousjacent, $f_val_init, $f_val_prev) ? 1 : 0;

    }

}

if ($action == "indic") {

    logger::info("STOCK", "INDIC", "###########################################################");

    $req = "SELECT * FROM stocks WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {
        if ($row['type'] != "CALL" || $row['type'] != "PUT")
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
        $req = "UPDATE stocks SET type='".$f_type."', links='".$links."', pea=".$pea.", ISIN='".$f_isin."', provider='".$f_provider."', categorie='".$f_categorie."', frais='".$f_frais."', actifs=".($f_actifs ? $f_actifs : 0).", distribution='".$f_distribution."', gf_symbol='".$f_gf_symbol."', rating=".$f_rating.", tags='".$f_tags."', dividende_annualise=".($f_dividende ? $f_dividende : 0).", date_dividende=".($f_date_dividende ? "'".$f_date_dividende."'" : "NULL").", pc_emetteur='".$f_emetteur."', pc_ticker='".$f_ticker."', pc_levier='".$f_levier."', pc_sousjacent='".$f_sousjacent."', pc_expire='".$f_expire."' WHERE symbol='".$symbol."'";
        $res = dbc::execSql($req);

        // Mise à jour de la valeur previous dans la table quote pour les turbos
        if ($row['type'] != "CALL" || $row['type'] != "PUT") {
            $req = "UPDATE quotes SET open='".$f_val_init."', high='".$f_val_init."', low='".$f_val_init."', price='".$f_val_init."', day='".date("Y-m-d")."', previous='".$f_val_prev."' WHERE symbol='".$symbol."'";
            $res = dbc::execSql($req);    
        }

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

cacheData::deleteTMPFiles();

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