<?

require_once "sess_context.php";

session_start();

include "common.php";

$symbol = "";
$rsi_choice = 0;
$volume_choice = 1;

foreach (['symbol', 'ptf_id'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

// Affichage par defaut des MMX
$mmx = 8;

// Couleurs boutons
$bt_interval_colr = "green"; // D/W/M
$bt_period_colr   = "blue";  // ALL/3Y/1Y/1T
$bt_mmx_colr      = "purple";
$bt_volume_colr   = "yellow";
$bt_filter_colr   = "teal";
$bt_grey_colr     = "grey";

$readonly = $sess_context->isSuperAdmin() ? false : true;

$db = dbc::connect();

// Recuperation des infos de l'actif selectionne
$req = "SELECT *, s.symbol symbol FROM stocks s LEFT JOIN quotes q ON s.symbol = q.symbol WHERE s.symbol='" . $symbol . "'";
$res = dbc::execSql($req);

// Bye bye si inexistant
if (!$row = mysqli_fetch_assoc($res)) exit(0);

// Traitement des donnees
$links = json_decode($row['links'], true);

$row['link1'] = isset($links['link1']) ? $links['link1'] : "";
$row['link2'] = isset($links['link2']) ? $links['link2'] : "";

$tags = array_flip(explode("|", utf8_decode($row['tags'])));

// Recuperation des min/max des cotations
$minmax = calc::getMinMaxQuotations();

// Calcul synthese de tous les porteuilles de l'utilisateur (on recupere les PRU globaux)
$aggregate_ptf = calc::getAggregatePortfoliosByUser($sess_context->getUserId());

$pru = isset($aggregate_ptf['positions'][$symbol]['pru']) ? $aggregate_ptf['positions'][$symbol]['pru'] : 0;

// Nb positions en portefeuille
$ptf_nb_positions = isset($aggregate_ptf['positions']) ? count($aggregate_ptf['positions']) : 0;

// Recuperation des indicateurs de l'actif de la derniere cotation
$data = calc::getSymbolIndicatorsLastQuote($row['symbol']);
$curr = $row['currency'] == "EUR" ? "&euro;" : "$";

?>

<div class="ui container inverted segment">

    <h2 class="ui left">
        <span>
            <?= utf8_decode($row['name']) ?>
            <button style="position: relative; left: 10px; top:-4px;" id="symbol_refresh_bt" class="mini ui floated right button"><?= $row['symbol'] ?></button>
        </span>
        <? if ($sess_context->isSuperAdmin()) { ?>
            <i style="float: right; margin-top: 5px;" id="stock_delete_bt" class="ui inverted right float small trash icon"></i>
        <? } ?>
        <?
            if ($ptf_nb_positions > 0) {
                echo '<select id="ptf_select_bt" style="float: right; top: -4px; right: 10px;" class="ui dropdown"><option />';
                foreach($aggregate_ptf['positions'] as $key => $val)
                    if (!$val['other_name']) echo "<option>$key</option>";
                echo "</select>";
            }
        ?>
    </h2>

    <table id="detail_stock" class="ui selectable inverted single line table">
        <thead>
            <tr><?
                foreach (['Devise', 'Type', 'Région', 'Marché', 'TZ', 'Cotation', 'Prix', '%', 'DM', 'MM200', 'MM7'] as $key)
                    echo "<th>" . $key . "</th>";
                ?></tr>
        </thead>
        <tbody>
            <tr>
                <td data-label="Devise"><?= $row['currency'] ?></td>
                <td data-label="Région"><?= $row['type'] ?></td>
                <td data-label="Région"><?= $row['region'] ?></td>
                <td data-label="Marché"><?= $row['marketopen'] . "-" . $row['marketclose'] ?></td>
                <td data-label="TZ"><?= $row['timezone'] ?></td>
                <td data-label="Cotation"><?= ($row['day'] == "" ? "N/A" : $row['day']) ?></td>
                <td data-label="Prix"><?= $row['price'] == "" ? "N/A" : sprintf("%.2f", $row['price']) . $curr ?></td>
                <td data-label="%" class="<?= ($row['percent'] >= 0 ? "aaf-positive" : "aaf-negative") ?>"><?= sprintf("%.2f", $row['percent']) ?>%</td>
                <td data-label="DM" class="<?= ($data['DM'] >= 0 ? "aaf-positive" : "aaf-negative") ?>"><?= $data['DM'] ?>%</td>
                <td data-label="M200"><?= sprintf("%.2f", $data['MM200']) . $curr ?></td>
                <td data-label="MM7"><?= sprintf("%.2f", $data['MM7']) . $curr ?></td>
            </tr>
        </tbody>
    </table>

</div>


<?
// /////////////////////
// GRAPHES COURS
// //////////////////////

function getTimeSeriesData($table_name, $period, $sym)
{

    $ret = array('rows' => array(), 'colrs' => array());

    $file_cache = 'cache/TMP_TIMESERIES_' . $sym . '_' . $period . '.json';

    if (cacheData::refreshCache($file_cache, 600)) { // Cache de 5 min

        $req = "SELECT * FROM " . $table_name . " dtsa, indicators indic WHERE dtsa.symbol=indic.symbol AND dtsa.day=indic.day AND indic.period='" . $period . "' AND dtsa.symbol='" . $sym . "' ORDER BY dtsa.day ASC";
        $res = dbc::execSql($req);
        while ($row = mysqli_fetch_assoc($res)) {
            $row['adjusted_close'] = sprintf("%.2f", $row['adjusted_close']);
            $row['MM7']   = sprintf("%.2f", $row['MM7']);
            $row['MM20']  = sprintf("%.2f", $row['MM20']);
            $row['MM50']  = sprintf("%.2f", $row['MM50']);
            $row['MM200'] = sprintf("%.2f", $row['MM200']);
            $row['RSI14'] = sprintf("%.1f", $row['RSI14']);
            $row['DM']    = sprintf("%.2f", $row['DM']);
            $ret['rows'][] = $row;
            // Pour le choix de la couleur on ne prend pas le adjusted_close car le adjusted_open n'existe pas
            $ret['colrs'][] = $row['close'] >= $row['open'] ? 1 : 0;
        }

        cacheData::writeCacheData($file_cache, $ret);
    } else {
        $ret = cacheData::readCacheData($file_cache);
    }

    return $ret;
}

// Format data
function format_data($data, $period) {
?>
        new_data_<?= $period ?> = [
<?
        $i = 1;
        $count = count($data["rows"]);

        reset($data["colrs"]);
        foreach($data["rows"] as $key => $val) {
            echo sprintf("{ x: '%s', y: %s, v: %s, m1: %s, m2: %s, m3: %s, m4: %s, r: %s, d: %s, c: '%s' }%s",
                $val["day"],
                $val["adjusted_close"],
                $val["volume"] == "" ? 0 : round($val["volume"]/1000),
                $val["MM200"],
                $val["MM50"],
                $val["MM20"],
                $val["MM7"],
                $val["RSI14"],
                $val["DM"],
                current($data["colrs"]),
                $i++ == $count ? '' : ','
            );
            next($data["colrs"]);
        }
?>
    ];
<?
}

// Recuperation de tous les indicateurs DAILY de l'actif
$data_daily = getTimeSeriesData("daily_time_series_adjusted", "DAILY", $symbol);

// On ajoute la cotation du jour
$data_daily_today = array("symbol" => $row["symbol"], "day" => $row["day"], "open" => $row["open"], "high" => $row["high"], "low" => $row["low"], "close" => $row["price"], "adjusted_close" => $row["price"], "volume" => $row["volume"], "period" => "DAILY", "DM" => $data['DM'], "MM7" => $data['MM7'], "MM20" => $data['MM20'], "MM50" => $data["MM50"], "MM200" => $data['MM200'], "RSI14" => $data["RSI14"]);
$data_daily["rows"][]  = $data_daily_today;
$data_daily["colrs"][] = 1;

// Recuperation de tous les indicateurs WEEKLY/MONTHLY de l'actif
$data_weekly  = getTimeSeriesData("weekly_time_series_adjusted",  "WEEKLY",  $symbol);
$data_monthly = getTimeSeriesData("monthly_time_series_adjusted", "MONTHLY", $symbol);

asort(uimx::$invest_secteur); // Permet de rajouter des items n'importe ou dans la liste
asort(uimx::$invest_zone_geo);
asort(uimx::$invest_classe);
asort(uimx::$invest_factorielle);

?>

<style>
    table td {
        padding: 5px 20px !important;
    }

    table div.checkbox {
        padding: 8px 0px !important;
    }
</style>


<div id="canvas_area" class="ui container inverted segment">
    <span>
        <button id="graphe_D_bt" class="mini ui <?= $rsi_choice == 0  ? $bt_interval_colr : $bt_grey_colr ?> button">Daily</button>
        <button id="graphe_W_bt" class="mini ui <?= $rsi_choice == 1  ? $bt_interval_colr : $bt_grey_colr ?> button">Weekly</button>
        <button id="graphe_M_bt" class="mini ui <?= $rsi_choice == 2  ? $bt_interval_colr : $bt_grey_colr ?> button" style="margin-right: 20px;">Monthly</button>
        <button id="graphe_all_bt" class="mini ui <?= $bt_period_colr ?> button">All</button>
        <button id="graphe_3Y_bt" class="mini ui <?= $bt_grey_colr ?> button">3Y</button>
        <button id="graphe_1Y_bt" class="mini ui <?= $bt_grey_colr ?> button">1Y</button>
        <button id="graphe_1T_bt" class="mini ui <?= $bt_grey_colr ?> button" style="margin-right: 20px;">1T</button>
        <button id="graphe_mm7_bt" class="mini ui <?= ($mmx & 1) == 1 ? $bt_mmx_colr : $bt_grey_colr ?> button">MM7</button>
        <button id="graphe_mm20_bt" class="mini ui <?= ($mmx & 2) == 2 ? $bt_mmx_colr : $bt_grey_colr ?> button">MM20</button>
        <button id="graphe_mm50_bt" class="mini ui <?= ($mmx & 4) == 4 ? $bt_mmx_colr : $bt_grey_colr ?> button">MM50</button>
        <button id="graphe_mm200_bt" class="mini ui <?= ($mmx & 8) == 8 ? $bt_mmx_colr : $bt_grey_colr ?> button" style="margin-right: 20px;">MM200</button>
        <button id="graphe_volume_bt" class="mini ui <?= $volume_choice == 1  ? $bt_volume_colr : $bt_grey_colr ?> button"><i style="margin-left: 5px;" class="icon inverted signal"></i></button>
    </span>
    <canvas id="stock_canvas1" height="100"></canvas>
    <canvas id="stock_canvas2" height="20"></canvas>
    <h4>Evolution DM</h4>
    <canvas id="stock_canvas3" height="50"></canvas>
</div>

<div class="ui container inverted segment">
    <form class="ui inverted form <?= $readonly ? "readonly" : "" ?>">
        <h4 class="ui inverted dividing header">Asset Informations</h4>
        <div class="field">
            <div class="three fields">
                <div class="field">
                    <label>Provider</label>
                    <input type="text" id="f_provider" value="<?= $row['provider'] ?>" placeholder="Provider">
                </div>
                <div class="field">
                    <label>ISIN</label>
                    <input type="text" id="f_isin" value="<?= $row['ISIN'] ?>" placeholder="ISIN">
                </div>
                <div class="field">
                    <label>Risque</label>
                    <? if (!$readonly) { ?>
                        <select class="ui fluid search dropdown" id="f_rating">
                            <?
                            foreach ([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7] as $key => $val)
                                echo '<option value="' . $key . '" ' . ($row['rating'] == $key ? 'selected="selected"' : '') . '>' . $val . '</option>';
                            ?>
                        </select>
                    <? } else { ?>
                        <input type="text" id="f_rating" value="<?= $row['rating'] ?>" placeholder="Rating">
                    <? } ?>
                </div>
            </div>
            <div class="three fields">
                <div class="field">
                    <label>Frais de gestion (%)</label>
                    <input type="text" id="f_frais" value="<?= $row['frais'] ?>" placeholder="Frais de gestion">
                </div>
                <div class="field">
                    <label>Actifs (Million)</label>
                    <input type="text" id="f_actifs" value="<?= $row['actifs'] ?>" placeholder="Actifs">
                </div>
                <div class="field">
                    <label>Politique de distribution</label>
                    <? if (!$readonly) { ?>
                        <select class="ui fluid search dropdown" id="f_distribution">
                            <option value="">Choisir</option>
                            <?
                            foreach (uimx::$invest_distribution as $key => $val)
                                echo '<option value="' . $key . '" ' . ($row['distribution'] == $key ? 'selected="selected"' : '') . '>' . $val . '</option>';
                            ?>
                        </select>
                    <? } else { ?>
                        <input type="text" id="f_distribution" value="<?= $row['distribution'] == "" ? "" : uimx::$invest_distribution[$row['distribution']] ?>" placeholder="Distribution">
                    <? } ?>
                </div>
            </div>
            <div class="two fields">
                <div class="field">
                    <? if (!$readonly) { ?>
                        <label>Morning Star</label>
                        <input type="text" id="f_link1" value="<?= $row['link1'] ?>" placeholder="Lien http">
                    <? } ?>
                </div>
                <div class="field">
                    <? if (!$readonly) { ?>
                        <label>JustETF</label>
                        <input type="text" id="f_link2" value="<?= $row['link2'] ?>" placeholder="Lien http">
                    <? } ?>
                </div>
            </div>
            <div class="two fields">
                <div class="field">
                    <label>GF Symbole</label>
                    <input type="text" id="f_gf_symbol" value="<?= $row['gf_symbol'] ?>" placeholder="Google finance symbole">
                </div>
                <div class="field">
                    <? if (!$readonly) { ?>
                        <label>&nbsp;</label>
                        <div class="ui toggle inverted checkbox" onclick="toogleCheckBox('f_pea');">
                            <input type="checkbox" id="f_pea" <?= $row['pea'] == 1 ? 'checked="checked' : '' ?> tabindex="0" class="hidden">
                            <label>Eligible PEA</label>
                        </div>
                    <? } else { ?>
                        <label>Eligible PEA</label>
                        <input type="text" id="f_pea" value="<?= $row['pea'] == 0 ? "Non" : "Oui" ?>" placeholder="">
                    <? } ?>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="canvas_area2" class="ui container inverted segment">
<? if ($readonly) { ?>
    <h4 class="ui inverted dividing header">Tags</h4>
<? } ?>
<? foreach( [
                "Classe d'actif"      => uimx::$invest_classe,
                "Secteur"             => uimx::$invest_secteur,
                "Marché"              => uimx::$invest_market,
                "Zone géographique"   => uimx::$invest_zone_geo,
                "Critère factoriel"   => uimx::$invest_factorielle,
                "Taille"              => uimx::$invest_taille,
                "Thème"               => uimx::$invest_theme
            ] as $lib => $tab) { ?>

<? if (!$readonly) { ?>
    <h4 class="ui inverted dividing header"><?= $lib ?></h4>
<? } ?>

    <div class="ui horizontal list">
        <?
            foreach ($tab as $key => $val) {
                if ($readonly) {
                    
                    if (isset($tags[$val['tag']])) { ?>

                        <div class="item"><button class="item very small ui bt_tags <?= $bt_interval_colr ?> button"><?= $val['tag'] ?></button></div>

                <? } } else { ?>
    
                    <div class="item"><button <?= isset($val['desc']) && $val['desc'] != "" ? "data-tootik-conf=\"multiline\" data-tootik=\"".$val['desc']."\"" : "" ?> id="bt_<?= strtoupper(substr($lib, 0, 3))."_".$key ?>" class="item very small ui bt_tags <?= isset($tags[$val['tag']]) ? $bt_interval_colr : $bt_grey_colr ?> button"><?= $val['tag'] ?></button></div>

                <? } ?>

            <? } ?>
    </div>
<? } ?>
</div>

<? if ($readonly) { ?>
<div id="canvas_area3" class="ui container inverted segment form">
    <h4 class="ui inverted dividing header">Links</h4>
    <div class="field">
        <div class="two fields">
            <div class="field">
                <i class="ui icon inverted external"></i><a href="<?= $row['link1'] ?>">Morning Star</a>
            </div>
            <div class="field">
                <i class="ui icon inverted external"></i><a href="<?= $row['link2'] ?>">JustETF</a>
            </div>
        </div>
    </div>
</div>
<? } ?>

<?

if (!$readonly) {
    $infos = calc::getDirectDM($data);
?>

    <div class="ui container inverted grid segment">
        <form class="ui inverted form">
            <h4 class="ui inverted dividing header">Cache Informations</h4>
        </form>
    </div>

    <div class="ui container inverted grid segment">
        <div class="column">

            <div class="ui inverted stackable two column grid container">
                <div class="wide column">
                    <table id="detail2_stock" class="ui selectable inverted single line table">
                        <tbody>
                            <tr>
                                <td>Price</td>
                                <td><?= $data["day"] ?> [<?= sprintf("%.2f", $infos['price']) ?>] [<?= sprintf("%2.2f", $infos['dm']) ?>%]</td>
                            </tr>
                            <tr>
                                <td>DMD1</td>
                                <td><?= isset($data["DMD1"]) ? $data["DMD1"] : "N/A" ?> [<?= sprintf("%.2f", $infos['close']['DMD1']) ?>] [<?= sprintf("%2.2f", $infos['perf']['DMD1']) ?>%]</td>
                            </tr>
                            <tr>
                                <td>DMD2</td>
                                <td><?= isset($data["DMD2"]) ? $data["DMD2"] : "N/A" ?> [<?= sprintf("%.2f", $infos['close']['DMD2']) ?>] [<?= sprintf("%2.2f", $infos['perf']['DMD2']) ?>%]</td>
                            </tr>
                            <tr>
                                <td>DMD3</td>
                                <td><?= isset($data["DMD3"]) ? $data["DMD3"] : "N/A" ?> [<?= sprintf("%.2f", $infos['close']['DMD3']) ?>] [<?= sprintf("%2.2f", $infos['perf']['DMD3']) ?>%]</td>
                            </tr>
                            <tr>
                                <td colspan="2"><div style="height: 140px; width: 450px; overflow: scroll;"><?= var_dump($data) ?></div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="wide column">
                    <table id="detail3_stock" class="ui selectable inverted single line table">
                        <tbody>
                            <?
                            foreach (cacheData::$lst_cache as $key) {
                                $fn = "cache/" . $key . "_" . $symbol . ".json";
                                echo "<tr data-tootik=\"".$key."_".$symbol.".json\"><td style=\"padding: 0px 0px 0px 10px !important;\">".(file_exists($fn) ? "<i class=\"ui icon inverted green check\"></i>" : "<i class=\"ui icon inverted red x\"></i>")."</td><td>[".(file_exists($fn) ? date("Y-m-d H:i", filemtime($fn)) : "")."] ".str_replace("_TIME_SERIES_ADJUSTED_", "::", $key)."</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

<? } ?>

<div class="ui container inverted segment">
    <h2 class="ui inverted right aligned header foot_buttons">
        <? if (!$readonly) { ?>
            <button id="nav_menu_bt" class="dropbtn circular ui right floated grey button icon_action"><i class="inverted white ellipsis vertical icon"></i></button>
            <div class="ui vertical menu nav nav_menu_top" id="nav_menu">
                <a class="item" id="stock_sync_bt"><span>Modifier & Sync</span></a>
                <a class="item" id="stock_indic_bt"><span>Rebuild Indicators</span></a>
                <a class="item" id="stock_reload_bt"><span>Reload cache data</span></a>
            </div>
            <button id="stock_edit_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white edit icon"></i> Modifier</button>
        <? } ?>
        <button id="stock_back_bt" class="circular ui icon very small right floated grey labelled button"><i class="inverted white reply icon"></i></button>

    </h2>
</div>



<script>
    var myChart1 = null;
    var myChart2 = null;
    var myChart3 = null;

    // 1y=280d, 55w, 12m
    var interval_period_days = {
        'D': { 'ALL': 0, '3Y': 840, '1Y': 280, '1T': 70 },
        'W': { 'ALL': 0, '3Y': 165, '1Y': 55,  '1T': 14 },
        'M': { 'ALL': 0, '3Y': 36,  '1Y': 12,  '1T': 3 }
    };

    var mm_bts = {
        'graphe_mm7_bt'   : 'MM7',
        'graphe_mm20_bt'  : 'MM20',
        'graphe_mm50_bt'  : 'MM50',
        'graphe_mm200_bt' : 'MM200',
        'graphe_volume_bt': 'VOLUME'
    };

    // Couleurs des MMX
    var mmx_colors = { 'MM7': '<?= $sess_context->getSpectreColor(4) ?>', 'MM20': '<?= $sess_context->getSpectreColor(2) ?>', 'MM50': '<?= $sess_context->getSpectreColor(1) ?>', 'MM200': '<?= $sess_context->getSpectreColor(6) ?>' };

    // Fonction de gestoion des tableaux de valeurs
    min_slice = function(tab, size) {
        var ret = (tab.length - size - 1) > 0 ? (tab.length - size - 1) : 0;
        return ret;
    }
    max_slice = function(tab) {
        var ret = tab.length > 0 ? tab.length : 0;
        return ret;
    }
    getSlicedData = function(tab, size) {
        var ret = size == 0 ? tab : tab.slice(min_slice(tab, size), max_slice(tab));
        return ret;
    }
    getSlicedData2 = function(interval, t_d, t_w, t_m, size) {
        var tab = interval == 'D' ? t_d : (interval == 'W' ? t_w : t_m);
        var ret = size == 0 ? tab : tab.slice(min_slice(tab, size), max_slice(tab));
        return ret;
    }

    // Creation de Dataset generique
    newDataset2 = function(mydata, mytype, yaxeid, yaxekey, mylabel, mycolor, bg, myfill, myborderwith = 0.5, mytension = 0.4, myradius = 0) {

        var ret = {
            type: mytype,
            data: mydata,
            label: mylabel,
            borderColor: mycolor,
            backgroundColor: bg,
            borderWidth: myborderwith,
            yAxisID: yaxeid,
            parsing: {
                yAxisKey: yaxekey
            },
            cubicInterpolationMode: 'monotone',
            tension: mytension,
            fill: myfill,
            pointRadius: myradius,
            normalized: true
        };

        return ret;
    }

    // Creation de Dataset generique
    newDatasetVols = function(mydata, mytype, yaxeid, yaxekey, mylabel) {

        var ret = {
            type: mytype,
            data: mydata,
            label: mylabel,
            borderColor: 0,
            backgroundColor: mydata.map(function(item) { return item.c == 1 ? 'green' : 'red'; }),
            borderWidth: 0,
            yAxisID: yaxeid,
            parsing: {
                yAxisKey: yaxekey
            },
            fill: true,
            normalized: true
        };

        return ret;
    }

    // Creation de Datasets specifiques
    getDatasetVals = function(vals) {
        return newDataset2(vals, 'line', 'y1', 'y', 'Cours', '<?= $sess_context->getSpectreColor(0) ?>', '<?= $sess_context->getSpectreColor(0, 0.2) ?>', true);
    }
    getDatasetVols = function(vals, l) {
        return newDatasetVols(vals, 'bar', 'y2', 'v', l);
    }
    getDatasetMMX = function(vals, k, l) {
        return newDataset2(vals, 'line', 'y1', k, l, mmx_colors[l], '', false);
    }
    getDatasetRSI14 = function(vals) {
        return newDataset2(vals, 'line', 'y', 'r', "RSI14", 'violet', 'violet', false);
    }
    getDatasetDM = function(vals) {
        return newDataset2(vals, 'line', 'y', 'd', "DM", 'rgba(255, 255, 0, 0.5)', 'rgba(255, 255, 0, 0.75)', false, 2, 0.4, 0);
    }

    var graphe_size_days = 0;
    var new_data_daily   = [];
    var new_data_weekly  = [];
    var new_data_monthly = [];

<?
    format_data($data_daily,   "daily");
    format_data($data_weekly,  "weekly");
    format_data($data_monthly, "monthly");
?>

    try {

        // Ref Day Data
        var ref_d_days  = [<?= '"' . implode('","', array_column($data_daily["rows"],   "day")) . '"' ?>];
        var ref_w_days  = [<?= '"' . implode('","', array_column($data_weekly["rows"],  "day")) . '"' ?>];
        var ref_m_days  = [<?= '"' . implode('","', array_column($data_monthly["rows"], "day")) . '"' ?>];

        // Current data
        var g_new_data = null;
        var g_days     = null;

        var ctx1 = document.getElementById('stock_canvas1').getContext('2d');
        el("stock_canvas1").height = document.body.offsetWidth > 700 ? 100 : 300;

        var ctx2 = document.getElementById('stock_canvas2').getContext('2d');
        el("stock_canvas2").height = document.body.offsetWidth > 700 ? 30 : 90;

        var ctx3 = document.getElementById('stock_canvas3').getContext('2d');
        el("stock_canvas3").height = document.body.offsetWidth > 700 ? 50 : 150;

    } catch(e) {
        alert('stock_detail.php: Graphe data error' + e);
    }

    getMMXKey = function(label) {
        return label == "MM7" ? 'm4' : (label == "MM20" ? 'm3' : (label == "MM50" ? 'm2' : 'm1'));
    }

    toogleMMX = function(chart, label) {
        ref_colr = label.toLowerCase() == "volume" ? "<?= $bt_volume_colr ?>" : "<?= $bt_mmx_colr ?>";
        bt = 'graphe_' + label.toLowerCase() + '_bt'
        addCN(bt, 'loading');
        if (isCN(bt, ref_colr)) {
            chart.data.datasets.forEach((dataset) => {
                if (dataset.label == label) dataset.data = null;
            });
        } else {
            if (label.toLowerCase() == "volume")
                chart.data.datasets.push(getDatasetVols(g_new_data, 'VOLUME'));
            else
                chart.data.datasets.push(getDatasetMMX(g_new_data, getMMXKey(label), label));
        }
        chart.update();
        rmCN(bt, 'loading');
        switchCN(bt, '<?= $bt_grey_colr ?>', ref_colr);
    }

    getIntervalStatus = function() {
        return isCN('graphe_D_bt', '<?= $bt_interval_colr ?>') ? 'D' : (isCN('graphe_W_bt', '<?= $bt_interval_colr ?>') ? 'W' : 'M');
    }
    getPeriodStatus = function() {
        return isCN('graphe_all_bt', '<?= $bt_period_colr ?>') ? "ALL" : (isCN('graphe_3Y_bt', '<?= $bt_period_colr ?>') ? "3Y" : (isCN('graphe_1Y_bt', '<?= $bt_period_colr ?>') ? "1Y" : "1T"));
    }

    update_data = function(size) {

        interval = getIntervalStatus();

        g_new_data = getSlicedData2(interval, new_data_daily, new_data_weekly, new_data_monthly, size);
        g_days     = getSlicedData2(interval, ref_d_days, ref_w_days, ref_m_days, size);

        return g_days.length;
    }

    update_graphe_buttons = function(bt) {
        c2 = '<?= $bt_grey_colr ?>';
        addCN(bt, 'loading');
        if (bt == 'graphe_1T_bt' || bt == 'graphe_1Y_bt' || bt == 'graphe_3Y_bt' || bt == 'graphe_all_bt') {
            c1 = '<?= $bt_period_colr ?>';
            ['graphe_1T_bt', 'graphe_1Y_bt', 'graphe_3Y_bt', 'graphe_all_bt'].forEach((bt) => {
                replaceCN(bt, c1, c2);
            });
        }
        if (bt == 'graphe_D_bt' || bt == 'graphe_W_bt' || bt == 'graphe_M_bt') {
            c1 = '<?= $bt_interval_colr ?>';
            ['graphe_D_bt', 'graphe_W_bt', 'graphe_M_bt'].forEach((bt) => {
                replaceCN(bt, c1, c2);
            });
        }
        switchCN(bt, c1, c2);
    }

    update_graph_chart = function(c, ctx, opts, lbls, dtsts, plg) {

        try {
            if (c) c.destroy();
            c = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: lbls,
                    datasets: dtsts
                },
                options: opts,
                plugins: plg
            });
            c.update();

        } catch(e) {
            alert('update_graphe_chart error !' + e);
        }

        return c;
    }

    update_all_charts = function(bt) {

        // Ajustement des buttons
        update_graphe_buttons(bt);

        // Ajustement des données
        var nb_items = update_data(interval_period_days[getIntervalStatus()][getPeriodStatus()]);

        // Changement dynamique option graphe
        if (g_new_data.length > 2000) {
            options_Stock_Graphe.animation = false;
            options_RSI_Graphe.animation   = false;
            options_DM_Graphe.animation    = false;
        }

        // En attendant d'avoir un plugin ou de corriger le pb de volumetrie on limite le daily a max 3000 inputs
        max_data = 3000;
        if (g_new_data.length > max_data) {
            g_days     =  g_days.slice(min_slice(g_days, max_data), max_slice(g_days));
            g_new_data =  g_new_data.slice(min_slice(g_new_data, max_data), max_slice(g_new_data));
        }

        // Update Chart Stock
        var datasets1 = [];
        datasets1.push(getDatasetVals(g_new_data));
        if (isCN('graphe_mm7_bt',    '<?= $bt_mmx_colr ?>')) datasets1.push(getDatasetMMX(g_new_data, 'm4', 'MM7'));
        if (isCN('graphe_mm20_bt',   '<?= $bt_mmx_colr ?>')) datasets1.push(getDatasetMMX(g_new_data, 'm3', 'MM20'));
        if (isCN('graphe_mm50_bt',   '<?= $bt_mmx_colr ?>')) datasets1.push(getDatasetMMX(g_new_data, 'm2', 'MM50'));
        if (isCN('graphe_mm200_bt',  '<?= $bt_mmx_colr ?>')) datasets1.push(getDatasetMMX(g_new_data, 'm1', 'MM200'));
        if (isCN('graphe_volume_bt', '<?= $bt_volume_colr ?>')) datasets1.push(getDatasetVols(g_new_data, 'VOLUME'));
        options_Stock_Graphe.plugins.horizontal = [ ];
        options_Stock_Graphe.plugins.vertical   = [ <?= $pru == 0 ? "" : "{ lineColor: 'red', yPosition: ".$pru." }" ?> ];
        myChart1 = update_graph_chart(myChart1, ctx1, options_Stock_Graphe, g_days, datasets1, [ horizontal, vertical ]);

        // Update Chart RSI
        var datasets2 = [];
        datasets2.push(getDatasetRSI14(g_new_data));
        myChart2 = update_graph_chart(myChart2, ctx2, options_RSI_Graphe, g_days, datasets2, [horizontalLines_RSI_Graphe]);

        // Update Chart DM
        var datasets3 = [];
        datasets3.push(getDatasetDM(g_new_data));
        options_DM_Graphe.scales.y.position = 'right';
        myChart3 = update_graph_chart(myChart3, ctx3, options_DM_Graphe, g_days, datasets3, [horizontalLines_DM_Graphe]);

        rmCN(bt, 'loading');
    }

    // Initialisation des graphes
    update_all_charts('graphe_all_bt');

    var p = loadPrompt();

    <? if (!$readonly) { ?>

    getFormValues = function() {
        params = attrs(['f_isin', 'f_provider', 'f_frais', 'f_actifs', 'f_gf_symbol', 'f_rating', 'f_distribution', 'f_link1', 'f_link2']) + '&pea=' + (valof('f_pea') == 0 ? 0 : 1);

        var tags = '';
        Dom.find('button.bt_tags').forEach(function(item) {
            if (isCN(item.id, '<?= $bt_interval_colr ?>')) tags += item.innerHTML+'|';
        });
        params += '&f_tags=' + tags;

        return params;
    }

    // Listenet sur bt edit
    Dom.addListener(Dom.id('stock_edit_bt'), Dom.Event.ON_CLICK, function(event) {
        p = getFormValues();
        go({ action: 'update', id: 'main', url: 'stock_action.php?action=upt&symbol=<?= $symbol ?>' + p, loading_area: 'main' });
        toogleCN('nav_menu', 'on'); scroll(0,0);
    });
    
    // Listenet sur bt synchronisation
    Dom.addListener(Dom.id('stock_sync_bt'), Dom.Event.ON_CLICK, function(event) {
        p = getFormValues();
        go({ action: 'update', id: 'main', url: 'stock_action.php?action=sync&symbol=<?= $symbol ?>' + p, loading_area: 'main' });
        toogleCN('nav_menu', 'on'); scroll(0,0);
    });

    // Listenet sur indicators
    Dom.addListener(Dom.id('stock_indic_bt'), Dom.Event.ON_CLICK, function(event) {
        go({ action: 'update', id: 'main', url: 'stock_action.php?action=indic&symbol=<?= $symbol ?>', loading_area: 'main' });
        toogleCN('nav_menu', 'on'); scroll(0,0);
    });

    // Listenet sur bt reload
    Dom.addListener(Dom.id('stock_reload_bt'), Dom.Event.ON_CLICK, function(event) {
        go({ action: 'update', id: 'main', url: 'stock_action.php?action=reload&symbol=<?= $symbol ?>', loading_area: 'main' });
        toogleCN('nav_menu', 'on'); scroll(0,0);
    });

    // Gestion menu secondaire
	Dom.addListener(Dom.id('nav_menu_bt'), Dom.Event.ON_CLICK, function(event) {
        el('nav_menu').style.top = "-150px"; toogleCN('nav_menu', 'on');
    });

    <? } ?>

    // Listener sur bt back
    Dom.addListener(Dom.id('stock_back_bt'), Dom.Event.ON_CLICK, function(event) {
        go({ action: 'home', id: 'main', url: '<?= $ptf_id == "" ?  "home_content.php" : "portfolio_dashboard.php?portfolio_id=".$ptf_id ?>', loading_area: 'main' });
    });

    // Listener sur bt MMX et volume
    Object.entries(mm_bts).forEach(([key, val]) => {
        Dom.addListener(Dom.id(key), Dom.Event.ON_CLICK, function(event) {
            toogleMMX(myChart1, val);
        });
    });

    // Listenet sur bt 1T, 1Y, 3Y, ALL, D, W, M
    ['graphe_1T_bt', 'graphe_1Y_bt', 'graphe_3Y_bt', 'graphe_all_bt', 'graphe_D_bt', 'graphe_W_bt', 'graphe_M_bt'].forEach((item) => {
        Dom.addListener(Dom.id(item), Dom.Event.ON_CLICK, function(event) {
            update_all_charts(item);
        });
    });

    /* Dom.addListener(Dom.id('graphe_L_bt'),  Dom.Event.ON_CLICK, function(event) {
        if (isCN('graphe_L_bt', 'grey'))
            p.error('Les graphes sont liés (pas encore implémenté)');
        else
            p.inform('Les graphes ne sont plus liés');

        switchCN('graphe_L_bt', 'grey', 'blue');
        switchCN('graphe_L_bt_icon', 'unlink', 'linkify');
    }); */

    // Refresh button
    Dom.addListener(Dom.id('symbol_refresh_bt'), Dom.Event.ON_CLICK, function(event) {
        go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol=<?= $symbol ?>', loading_area: 'main' });
    });

    // Choix actif dans select actif ptf
    <? if ($ptf_nb_positions > 0) { ?>
        Dom.addListener(Dom.id('ptf_select_bt'), Dom.Event.ON_CHANGE, function(event) {
            element = Dom.id('ptf_select_bt');
            var selection = "";
            for (i=0; i < element.length; i++) if (element[i].selected) selection = element[i].value;
            if (selection != "") go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol=' + selection, loading_area: 'main' });
        });
    <? } ?>


    // Changement etat bouttons tags
    changeState = function(item) {
        switchColorElement(item.id, '<?= $bt_interval_colr ?>', '<?= $bt_grey_colr ?>');
    }

    // Listener sur boutons tags
    Dom.find('button.bt_tags').forEach(function(item) {
        Dom.addListener(item, Dom.Event.ON_CLICK, function(event) {
            changeState(item);
        });
    });

    <? if ($sess_context->isSuperAdmin()) { ?>
    // Listener sur bt delete
    Dom.addListener(Dom.id('stock_delete_bt'), Dom.Event.ON_CLICK, function(event) {
        go({ action: 'delete', id: 'main', url: 'stock_action.php?action=del&symbol=<?= $symbol ?>', loading_area: 'main', confirmdel: 1 });
    });
    <? } ?>
    
    // Top de page
    scroll(0,0);

</script>