<?

require_once "sess_context.php";

session_start();

include "common.php";

$symbol = "";
$rsi_choice = 0;
$volume_choice = 1;

foreach(['symbol'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

// Affichage par defaut des MMX
$mmx = 8;
// Couleurs boutons
$bt_interval_colr = "green"; // D/W/M
$bt_period_colr   = "blue";  // ALL/3Y/1Y/1T
$bt_mmx_colr      = "purple";
$bt_volume_colr   = "yellow";
$bt_grey_colr     = "grey";

$readonly = $sess_context->isSuperAdmin() ? false : true;

$db = dbc::connect();

$req = "SELECT *, s.symbol symbol FROM stocks s LEFT JOIN quotes q ON s.symbol = q.symbol WHERE s.symbol='".$symbol."'";
$res = dbc::execSql($req);

if (!$row = mysqli_fetch_assoc($res)) exit(0);

$links = json_decode($row['links'], true);

$row['link1'] = isset($links['link1']) ? $links['link1'] : "";
$row['link2'] = isset($links['link2']) ? $links['link2'] : "";

$data = calc::getSymbolIndicatorsLastQuote($row['symbol']);
$curr = $row['currency'] == "EUR" ? "&euro;" : "$";

?>

<div class="ui container inverted segment">
    
    <h2 class="ui inverted left floated header"><?= utf8_decode($row['name']) ?>
        <div id="symbol_refresh_bt" class="ui floated right label button"><?= $row['symbol'] ?></div>
        <? if ($sess_context->isSuperAdmin()) { ?>
            <h3 class="ui right floated header"><i id="stock_delete_bt" class="ui inverted right floated black small trash icon"></i></h3>
        <? } ?>
    </h2>

    <table id="detail_stock" class="ui selectable inverted single line table">
        <thead>
            <tr><?
                foreach(['Devise', 'Type', 'Région', 'Marché', 'TZ', 'Cotation', 'Prix' , '%', 'DM', 'MM200', 'MM7'] as $key)
                    echo "<th>".$key."</th>";
            ?></tr>
        </thead>
        <tbody>
            <tr>
                <td data-label="Devise"><?= $row['currency'] ?></td>
                <td data-label="Région"><?= $row['type'] ?></td>
                <td data-label="Région"><?= $row['region'] ?></td>
                <td data-label="Marché"><?= $row['marketopen']."-".$row['marketclose'] ?></td>
                <td data-label="TZ"><?= $row['timezone'] ?></td>
                <td data-label="Cotation"><?= ($row['day'] == "" ? "N/A" : $row['day']) ?></td>
                <td data-label="Prix"><?= $row['price'] == "" ? "N/A" : sprintf("%.2f", $row['price']).$curr ?></td>
                <td data-label="%" class="<?= ($row['percent'] >= 0 ? "aaf-positive" : "aaf-negative") ?>"><?= sprintf("%.2f", $row['percent'])?>%</td>
                <td data-label="DM" class="<?= ($data['DM'] >= 0 ? "aaf-positive" : "aaf-negative") ?>"><?= $data['DM'] ?>%</td>
                <td data-label="M200"><?= sprintf("%.2f", $data['MM200']).$curr ?></td>
                <td data-label="MM7"><?= sprintf("%.2f", $data['MM7']).$curr ?></td>
            </tr>
        </tbody>
    </table>

</div>


<?
// /////////////////////
// GRAPHES COURS
// //////////////////////

function getTimeSeriesData($table_name, $period, $sym) {

    $ret = array( 'rows' => array(), 'colrs' => array() );

    $file_cache = 'cache/TMP_TIMESERIES_'.$sym.'_'.$period.'.json';

    if (cacheData::refreshCache($file_cache, 600)) { // Cache de 5 min

        $req = "SELECT * FROM ".$table_name." dtsa, indicators indic WHERE dtsa.symbol=indic.symbol AND dtsa.day=indic.day AND indic.period='".$period."' AND dtsa.symbol='".$sym."' ORDER BY dtsa.day ASC";
        $res = dbc::execSql($req);    
        while ($row = mysqli_fetch_assoc($res)) {
            $row['adjusted_close'] = sprintf("%.2f", $row['adjusted_close']);
            $row['MM7']   = sprintf("%.2f", $row['MM7']);
            $row['MM20']  = sprintf("%.2f", $row['MM20']);
            $row['MM50']  = sprintf("%.2f", $row['MM50']);
            $row['MM200'] = sprintf("%.2f", $row['MM200']);
            $row['RSI14'] = sprintf("%.1f", $row['RSI14']);
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

$data_daily   = getTimeSeriesData("daily_time_series_adjusted",   "DAILY",   $symbol);

// On ajoute la cotation du jour
$data_daily_today = array("symbol" => $row["symbol"], "day" => $row["day"], "open" => $row["open"], "high" => $row["high"], "low" => $row["low"], "close" => $row["price"], "adjusted_close" => $row["price"], "volume" => $row["volume"], "period" => "DAILY", "DM" => $data['DM'], "MM7" => $data['MM7'], "MM20" => $data['MM20'], "MM50" => $data["MM50"], "MM200" => $data['MM200'], "RSI14" => $data["RSI14"] );
$data_daily["rows"][]  = $data_daily_today;
$data_daily["colrs"][] = 1;

$data_weekly  = getTimeSeriesData("weekly_time_series_adjusted",  "WEEKLY",  $symbol);
$data_monthly = getTimeSeriesData("monthly_time_series_adjusted", "MONTHLY", $symbol);

?>

<style>
table td { padding: 5px 20px !important; }
table div.checkbox { padding: 8px 0px !important; }
</style>


<div id="canvas_area" class="ui container inverted segment">
    <span>
        <button id="graphe_D_bt"      class="mini ui <?= $rsi_choice == 0  ? $bt_interval_colr : $bt_grey_colr ?> button">Daily</button>
        <button id="graphe_W_bt"      class="mini ui <?= $rsi_choice == 1  ? $bt_interval_colr : $bt_grey_colr ?> button">Weekly</button>
        <button id="graphe_M_bt"      class="mini ui <?= $rsi_choice == 2  ? $bt_interval_colr : $bt_grey_colr ?> button" style="margin-right: 20px;">Monthly</button>
        <button id="graphe_all_bt"    class="mini ui <?= $bt_period_colr ?> button">All</button>
        <button id="graphe_3Y_bt"     class="mini ui <?= $bt_grey_colr ?> button">3Y</button>
        <button id="graphe_1Y_bt"     class="mini ui <?= $bt_grey_colr ?> button">1Y</button>
        <button id="graphe_1T_bt"     class="mini ui <?= $bt_grey_colr ?> button" style="margin-right: 20px;">1T</button>
        <button id="graphe_mm7_bt"    class="mini ui <?= ($mmx & 1) == 1 ? $bt_mmx_colr : $bt_grey_colr ?> button">MM7</button>
        <button id="graphe_mm20_bt"   class="mini ui <?= ($mmx & 2) == 2 ? $bt_mmx_colr : $bt_grey_colr ?> button">MM20</button>
        <button id="graphe_mm50_bt"   class="mini ui <?= ($mmx & 4) == 4 ? $bt_mmx_colr : $bt_grey_colr ?> button">MM50</button>
        <button id="graphe_mm200_bt"  class="mini ui <?= ($mmx & 8) == 8 ? $bt_mmx_colr : $bt_grey_colr ?> button" style="margin-right: 20px;">MM200</button>
        <button id="graphe_volume_bt" class="mini ui <?= $volume_choice == 1  ? $bt_volume_colr : $bt_grey_colr ?> button"><i style="margin-left: 5px;" class="icon inverted signal"></i></button>
    </span>
    <canvas id="stock_canvas1" height="100"></canvas>
    <canvas id="stock_canvas2" height="20"></canvas>
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
                    <label>Catégorie</label>
                    <? if (!$readonly) { ?>
                        <select class="ui fluid search dropdown" id="f_categorie">
                            <?
                                asort(uimx::$invest_categories); // Permet de rajouter des items n'importe ou dans la liste
                                foreach(uimx::$invest_categories as $key => $val)
                                    echo '<option value="'.$key.'" '.($row['distribution'] == $key ? 'selected="selected"' : '').'>'.$val.'</option>';
                            ?>
                        </select>
                    <? } else { ?>
                        <input type="text" id="f_categorie" value="<?= $row['categorie'] == "" ? "" : uimx::$invest_categories[$row['categorie']] ?>" placeholder="Catégorie">
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
                            foreach(uimx::$invest_distribution as $key => $val)
                                echo '<option value="'.$key.'" '.($row['distribution'] == $key ? 'selected="selected"' : '').'>'.$val.'</option>';
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
                    <? } else { ?>
                        &nbsp;&nbsp;<i class="ui icon inverted external"></i><a href="<?= $row['link1'] ?>">Morning Star</a>
                    <? } ?>
                </div>
                <div class="field">
                    <? if (!$readonly) { ?>
                        <label>JustETF</label>
                        <input type="text" id="f_link2" value="<?= $row['link2'] ?>" placeholder="Lien http">
                    <? } else { ?>
                        &nbsp;&nbsp;<i class="ui icon inverted external"></i><a href="<?= $row['link2'] ?>">JustETF</a>
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
                        <div class="ui toggle inverted checkbox"  onclick="toogleCheckBox('f_pea');">
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

<div id="canvas_area" class="ui container inverted segment">
    <h4 class="ui inverted dividing header">Tags</h4>
    <span>
        <? foreach(['toto', 'tutu', 'titi'] as $key => $val) { ?>
            <button id="bt_<?= $val ?>" class="mini ui bt_tags <?= true ? $bt_interval_colr : $bt_grey_colr ?> button"><?= $val ?></button>
        <? } ?>
    </span>
</div>


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
                        <tr><td>Price</td><td><?= $data["day"] ?> [<?= sprintf("%.2f", $infos['price']) ?>] [<?= sprintf("%2.2f", $infos['dm']) ?>%]</td></tr>
                        <tr><td>DMD1</td><td><?= isset($data["DMD1"]) ? $data["DMD1"] : "N/A" ?> [<?= sprintf("%.2f", $infos['close']['DMD1']) ?>] [<?= sprintf("%2.2f", $infos['perf']['DMD1']) ?>%]</td></tr>
                        <tr><td>DMD2</td><td><?= isset($data["DMD2"]) ? $data["DMD2"] : "N/A" ?> [<?= sprintf("%.2f", $infos['close']['DMD2']) ?>] [<?= sprintf("%2.2f", $infos['perf']['DMD2']) ?>%]</td></tr>
                        <tr><td>DMD3</td><td><?= isset($data["DMD3"]) ? $data["DMD3"] : "N/A" ?> [<?= sprintf("%.2f", $infos['close']['DMD3']) ?>] [<?= sprintf("%2.2f", $infos['perf']['DMD3']) ?>%]</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="wide column">
                <table id="detail3_stock" class="ui selectable inverted single line table">
                    <tbody>
                    <?
                        foreach(cacheData::$lst_cache as $key)
                        echo "<tr><td style=\"padding: 0px 0px 0px 10px !important;\">".(file_exists("cache/".$key."_".$symbol.".json") ? "<i class=\"ui icon inverted green check\"></i>" : "<i class=\"ui icon inverted red x\"></i>")."</td><td>".$key."_".$symbol.".json</td></tr>";
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<? } ?>

<div class="ui container inverted segment">
    <h2 class="ui inverted right aligned header">
<? if (!$readonly) { ?>
        <button id="stock_edit_bt"  class="circular ui icon very small right floated pink labelled button"><i class="inverted white edit icon"></i> Modifier</button>
        <button id="stock_sync_bt"  class="circular ui icon very small right floated pink labelled button"><i class="inverted white retweet icon"></i> &nbsp;&nbsp;Modifier & Sync</button>
        <button id="stock_indic_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white settings icon"></i> &nbsp;&nbsp;Rebuild Indicators</button>
<? } ?>
        <button id="stock_back_bt"  class="circular ui icon very small right floated pink labelled button"><i class="inverted white reply icon"></i> Back</button>
    </h2>
</div>



<script>

var myChart1 = null;
var myChart2 = null;

// 1y=280d, 55w, 12m
var interval_period_days = {
    'D' : { 'ALL' : 0, '3Y' : 840, '1Y' : 280, '1T' : 10 },
    'W' : { 'ALL' : 0, '3Y' : 165, '1Y' : 55,  '1T' : 14 },
    'M' : { 'ALL' : 0, '3Y' : 36,  '1Y' : 12,  '1T' : 3 }
};

var mmx_colors = {
    'MM7'   : '<?= $sess_context->getSpectreColor(4) ?>',
    'MM20'  : '<?= $sess_context->getSpectreColor(2) ?>',
    'MM50'  : '<?= $sess_context->getSpectreColor(1) ?>',
    'MM200' : '<?= $sess_context->getSpectreColor(6) ?>'
};


min_slice = function(tab, size) { return (tab.length-size-1) > 0 ? (tab.length-size-1) : 0; }
max_slice = function(tab) { return tab.length > 0 ? tab.length : 0; }
getSlicedData = function(tab, size) { return size == 0 ? tab : tab.slice(min_slice(tab, size), max_slice(tab));}
getSlicedData2 = function(interval, t_d, t_w, t_m, size) {
    tab = interval == 'D' ? t_d : (interval == 'W' ? t_w : t_m);
    return size == 0 ? tab : tab.slice(min_slice(tab, size), max_slice(tab));
}

newDataset = function(mydata, mytype, yaxeid, mylabel, mycolor, bg, myfill) {
    
    var ret = {
        type: mytype,
        data: mydata,
	    label: mylabel,
        borderColor: mycolor,
        borderWidth: 0.5,
        yAxisID: yaxeid,
        cubicInterpolationMode: 'monotone',
        tension: 0.4,
        backgroundColor: bg,
        fill: myfill
    };

    return ret;
}

getDatasetVals  = function(vals) { return newDataset(vals, 'line', 'y1', 'Cours',  '<?= $sess_context->getSpectreColor(0) ?>', '<?= $sess_context->getSpectreColor(0, 0.2) ?>', true); }
getDatasetVols  = function(vals, l, c) { return newDataset(vals, 'bar',  'y2', l, c, c, true); }
getDatasetMMX   = function(vals, l) { return newDataset(vals, 'line', 'y1', l, mmx_colors[l], '', false); }
getDatasetRSI14 = function(vals) { return newDataset(vals, 'line', 'y', "RSI14", 'violet', '', false); }

var graphe_size_days = 0;

// Ref Daily data
var ref_d_days = [<?= '"'.implode('","', array_column($data_daily["rows"], "day")).'"' ?>];
var ref_d_vals   = [<?= implode(',', array_column($data_daily["rows"], "adjusted_close"))  ?>];
var ref_d_vols   = [<?= implode(',', array_column($data_daily["rows"], "volume")) ?>];
var ref_d_mm7    = [<?= implode(',', array_column($data_daily["rows"], "MM7"))    ?>];
var ref_d_mm20   = [<?= implode(',', array_column($data_daily["rows"], "MM20"))   ?>];
var ref_d_mm50   = [<?= implode(',', array_column($data_daily["rows"], "MM50"))   ?>];
var ref_d_mm200  = [<?= implode(',', array_column($data_daily["rows"], "MM200"))  ?>];
var ref_d_rsi14  = [<?= implode(',', array_column($data_daily["rows"], "RSI14"))  ?>];
var ref_d_colors = [<?= implode(',', $data_daily["colrs"]) ?>];

// Ref Weekly Data
var ref_w_days   = [<?= '"'.implode('","', array_column($data_weekly["rows"], "day")).'"' ?>];
var ref_w_vals   = [<?= implode(',', array_column($data_weekly["rows"], "adjusted_close"))  ?>]; // On prend close et pas adjusted_close car le cumul est tjs mis dans close quelque soit le champ choisit
var ref_w_vols   = [<?= implode(',', array_column($data_weekly["rows"], "volume")) ?>];
var ref_w_mm7    = [<?= implode(',', array_column($data_weekly["rows"], "MM7"))    ?>];
var ref_w_mm20   = [<?= implode(',', array_column($data_weekly["rows"], "MM20"))   ?>];
var ref_w_mm50   = [<?= implode(',', array_column($data_weekly["rows"], "MM50"))   ?>];
var ref_w_mm200  = [<?= implode(',', array_column($data_weekly["rows"], "MM200"))  ?>];
var ref_w_rsi14  = [<?= implode(',', array_column($data_weekly["rows"], "RSI14"))  ?>];

// Ref Monthly Data
var ref_m_days   = [<?= '"'.implode('","', array_column($data_monthly["rows"], "day")).'"' ?>];
var ref_m_vals   = [<?= implode(',', array_column($data_monthly["rows"], "adjusted_close"))  ?>]; // On prend close et pas adjusted_close car le cumul est tjs mis dans close quelque soit le champ choisit
var ref_m_vols   = [<?= implode(',', array_column($data_monthly["rows"], "volume")) ?>];
var ref_m_mm7    = [<?= implode(',', array_column($data_monthly["rows"], "MM7"))    ?>];
var ref_m_mm20   = [<?= implode(',', array_column($data_monthly["rows"], "MM20"))   ?>];
var ref_m_mm50   = [<?= implode(',', array_column($data_monthly["rows"], "MM50"))   ?>];
var ref_m_mm200  = [<?= implode(',', array_column($data_monthly["rows"], "MM200"))  ?>];
var ref_m_rsi14  = [<?= implode(',', array_column($data_monthly["rows"], "RSI14"))  ?>];

var g1_days   = null;
var g1_vals   = null;
var g1_vols   = null;
var g1_mm7    = null;
var g1_mm20   = null;
var g1_mm50   = null;
var g1_mm200  = null;
var g1_colors = null;
var g2_days   = null;
var g2_rsi14  = null;

// Transformation des 0/1 en rouge et vert
for (var i = 0; i < ref_d_colors.length; i++) {
    ref_d_colors[i] = (ref_d_colors[i] == 1) ? "green" : "red";
}

var ctx1 = document.getElementById('stock_canvas1').getContext('2d');
el("stock_canvas1").height = document.body.offsetWidth > 700 ? 100 : 300;

var options1 = {
    interaction: {
            intersect: false
        },
        radius: 0,
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    minRotation: 90,
                    maxRotation: 90
                }
            },
            y1: {
                grid: {
                    color: 'rgba(255, 255, 255, 0.05)'
                },
                ticks: {
                    align: 'end',
                    callback: function(value, index, values) {
                        var c = value+" \u20ac       ";
                        return c.substring(0, 6);
                    }
                },
                type: 'linear',
                position: 'right'
            },
            y2: {
                type: 'linear',
                position: 'left',
                display: false,
                ticks : {
                    max: 100000000,
                    min: 0,
                    stepSize: 20000000
                }
            }
        }
    };

var ctx2 = document.getElementById('stock_canvas2').getContext('2d');
el("stock_canvas2").height = document.body.offsetWidth > 700 ? 30 : 60;

var options2 = {
        interaction: {
            intersect: false
        },
        radius: 0,
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    minRotation: 90,
                    maxRotation: 90,
                    display: false
                }
            },
            y: {
                position: 'right',
                suggestedMin: 0,
                suggestedMax: 100,
                grid: {
                    color: 'rgba(255, 255, 255, 0.05)'
                },
                beginAtZero: true,
                ticks: {
                    stepSize: 25,
                    display: true,
                    align: 'end',
                    callback: function(value, index, values) {
                        var c = value+" %       ";
                        return c.substring(0, 6);
                    }
                },
                afterSetDimensions: (scale) => {
                    scale.maxWidth = 100;
                }
            }
        }
    };

const horizontalLines = {
    id: 'horizontalLines',
    beforeDraw(chart, args, options) {
        const { ctx, chartArea: { top, right, bottom, left, width, height }, scales: { x, y } } = chart;
        ctx.save();
        ctx.strokeStyle = 'rgba(255, 215, 0, 0.5)';
        // Attention, l'origine du graphe est en haut a gauche et donc le top en bas et le bottom en haut
        ctx.beginPath();
        ctx.setLineDash([3, 3]);
        h = (height/2) + top;
        // console.log('h:'+height+'y:'+y+'b:'+bottom+'t:'+top);
        ctx.moveTo(left, h);
        ctx.lineTo(right, h);
        ctx.stroke();
        ctx.fillStyle = 'rgba(1, 207, 243, 0.1)';
        pct = 30/100;
        x1 = (height*pct) + top;
        h2 = (height*(1-(pct*2)));
        ctx.fillRect(left, x1, right, h2);
        ctx.restore();
    }
};

getMMXData = function(label) {
    return label == "MM7" ? g1_mm7 : (label == "MM20" ? g1_mm20 : (label == "MM50" ? g1_mm50 : g1_mm200));
}

toogleMMX = function(chart, label) {
    ref_colr = label.toLowerCase() == "volume" ? "<?= $bt_volume_colr ?>" : "<?= $bt_mmx_colr ?>";
    bt = 'graphe_'+label.toLowerCase()+'_bt'
    addCN(bt, 'loading');
    if (isCN(bt, ref_colr)) {
        chart.data.datasets.forEach((dataset) => {
            if (dataset.label == label) dataset.data=null;
        });
    } else {
        if (label.toLowerCase() == "volume")
            chart.data.datasets.push(getDatasetVols(g1_vols, 'VOLUME', g1_colors));
        else
            chart.data.datasets.push(getDatasetMMX(getMMXData(label), label));
    }
    chart.update();
    rmCN(bt, 'loading');
    switchCN(bt, '<?= $bt_grey_colr ?>', ref_colr);
}

getIntervalStatus = function() { return isCN('graphe_D_bt', '<?= $bt_interval_colr ?>') ? 'D' : (isCN('graphe_W_bt', '<?= $bt_interval_colr ?>') ? 'W' : 'M'); }
getPeriodStatus   = function() { return isCN('graphe_all_bt', '<?= $bt_period_colr ?>') ? "ALL" : (isCN('graphe_3Y_bt', '<?= $bt_period_colr ?>') ? "3Y" : (isCN('graphe_1Y_bt', '<?= $bt_period_colr ?>') ? "1Y" : "1T")); }

update_data = function(size) {

    interval = getIntervalStatus();

    // Graphe 1
    g1_days   = getSlicedData2(interval, ref_d_days, ref_w_days, ref_m_days, size);
    g1_vals   = getSlicedData2(interval, ref_d_vals, ref_w_vals, ref_m_vals, size);
    g1_vols   = getSlicedData2(interval, ref_d_vols, ref_w_vols, ref_m_vols, size);

    g1_mm7    = getSlicedData2(interval, ref_d_mm7,   ref_w_mm7,   ref_m_mm7,   size);
    g1_mm20   = getSlicedData2(interval, ref_d_mm20,  ref_w_mm20,  ref_m_mm20,  size);
    g1_mm50   = getSlicedData2(interval, ref_d_mm50,  ref_w_mm50,  ref_m_mm50,  size);
    g1_mm200  = getSlicedData2(interval, ref_d_mm200, ref_w_mm200, ref_m_mm200, size);
    
    g1_colors = getSlicedData2(interval, ref_d_colors, ref_d_colors, ref_d_colors, size); // Couleurs peut etre a revoir !!!!

    // Graphe 2
    g2_days  = getSlicedData2(interval, ref_d_days, ref_w_days, ref_m_days,  size);
    g2_rsi14 = getSlicedData2(interval, ref_d_rsi14, ref_w_rsi14, ref_m_rsi14, size);

    return g1_days.length;
}

update_graphe_buttons = function(bt) {
    c2 = '<?= $bt_grey_colr ?>';
    addCN(bt, 'loading');
    if (bt == 'graphe_1T_bt' || bt == 'graphe_1Y_bt' || bt == 'graphe_3Y_bt' || bt == 'graphe_all_bt') {
        c1 = '<?= $bt_period_colr ?>';
        ['graphe_1T_bt', 'graphe_1Y_bt', 'graphe_3Y_bt', 'graphe_all_bt'].forEach((bt) => { replaceCN(bt, c1, c2); });
    }
    if (bt == 'graphe_D_bt' || bt == 'graphe_W_bt' || bt == 'graphe_M_bt') {
        c1 = '<?= $bt_interval_colr ?>';
        ['graphe_D_bt', 'graphe_W_bt', 'graphe_M_bt'].forEach((bt) => { replaceCN(bt, c1, c2); });
    }
    switchCN(bt, c1, c2);
}

update_graph_chart = function(c, ctx, opts, lbls, dtsts, plg) {
    if (c) c.destroy();
    c = new Chart(ctx, { type: 'line', data: { labels: lbls, datasets: dtsts }, options: opts, plugins: plg });
    c.update();

    return c;
}

update_all_charts = function(bt) {    

    // Ajustement des buttons
    update_graphe_buttons(bt);

    // Ajustement des données
    var nb_items = update_data(interval_period_days[getIntervalStatus()][getPeriodStatus()]);

    // Update Chart 1
    var datasets1 = [];
    datasets1.push(getDatasetVals(g1_vals));
    if (isCN('graphe_mm7_bt',   '<?= $bt_mmx_colr ?>'))  datasets1.push(getDatasetMMX(g1_mm7,   'MM7'));
    if (isCN('graphe_mm20_bt',  '<?= $bt_mmx_colr ?>'))  datasets1.push(getDatasetMMX(g1_mm20,  'MM20'));
    if (isCN('graphe_mm50_bt',  '<?= $bt_mmx_colr ?>'))  datasets1.push(getDatasetMMX(g1_mm50,  'MM50'));
    if (isCN('graphe_mm200_bt', '<?= $bt_mmx_colr ?>'))  datasets1.push(getDatasetMMX(g1_mm200, 'MM200'));
    if (isCN('graphe_volume_bt', '<?= $bt_volume_colr ?>')) datasets1.push(getDatasetVols(g1_vols, 'VOLUME', g1_colors));
    myChart1 = update_graph_chart(myChart1, ctx1, options1, g1_days, datasets1, [{}]);

    // Update Chart 2
    var datasets2 = [];
    datasets2.push(getDatasetRSI14(g2_rsi14));
    myChart2 = update_graph_chart(myChart2, ctx2, options2, g2_days, datasets2, [horizontalLines]);

    rmCN(bt, 'loading');
}

// Initialisation des graphes
update_all_charts('graphe_all_bt');

var p = loadPrompt();

<? if (!$readonly) { ?>
getFormValues = function() {
    params = attrs(['f_isin', 'f_provider', 'f_frais', 'f_actifs', 'f_gf_symbol', 'f_categorie', 'f_distribution', 'f_link1', 'f_link2' ])+'&pea='+(valof('f_pea') == 0 ? 0 : 1);
    return params;
}

Dom.addListener(Dom.id('stock_edit_bt'),  Dom.Event.ON_CLICK, function(event) { p = getFormValues(); go({ action: 'update', id: 'main', url: 'stock_action.php?action=upt&symbol=<?= $symbol ?>'+p, loading_area: 'stock_edit_bt' }); });
Dom.addListener(Dom.id('stock_sync_bt'),  Dom.Event.ON_CLICK, function(event) { p = getFormValues(); go({ action: 'update', id: 'main', url: 'stock_action.php?action=sync&symbol=<?= $symbol ?>'+p, loading_area: 'stock_sync_bt' }); });
Dom.addListener(Dom.id('stock_indic_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'update', id: 'main', url: 'stock_action.php?action=indic&symbol=<?= $symbol ?>', loading_area: 'stock_indic_bt' }); });
<? } ?>
Dom.addListener(Dom.id('stock_back_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'home', id: 'main', url: 'home_content.php', loading_area: 'main' }); });

Dom.addListener(Dom.id('graphe_mm7_bt'),    Dom.Event.ON_CLICK, function(event) { toogleMMX(myChart1, 'MM7');   });
Dom.addListener(Dom.id('graphe_mm20_bt'),   Dom.Event.ON_CLICK, function(event) { toogleMMX(myChart1, 'MM20');  });
Dom.addListener(Dom.id('graphe_mm50_bt'),   Dom.Event.ON_CLICK, function(event) { toogleMMX(myChart1, 'MM50');  });
Dom.addListener(Dom.id('graphe_mm200_bt'),  Dom.Event.ON_CLICK, function(event) { toogleMMX(myChart1, 'MM200'); });
Dom.addListener(Dom.id('graphe_volume_bt'), Dom.Event.ON_CLICK, function(event) { toogleMMX(myChart1, 'VOLUME'); });

Dom.addListener(Dom.id('graphe_all_bt'), Dom.Event.ON_CLICK, function(event) { update_all_charts('graphe_all_bt'); });
Dom.addListener(Dom.id('graphe_3Y_bt'),  Dom.Event.ON_CLICK, function(event) { update_all_charts('graphe_3Y_bt'); });
Dom.addListener(Dom.id('graphe_1Y_bt'),  Dom.Event.ON_CLICK, function(event) { update_all_charts('graphe_1Y_bt'); });
Dom.addListener(Dom.id('graphe_1T_bt'),  Dom.Event.ON_CLICK, function(event) { update_all_charts('graphe_1T_bt'); });

Dom.addListener(Dom.id('graphe_D_bt'),  Dom.Event.ON_CLICK, function(event) { update_all_charts('graphe_D_bt'); });
Dom.addListener(Dom.id('graphe_W_bt'),  Dom.Event.ON_CLICK, function(event) { update_all_charts('graphe_W_bt'); });
Dom.addListener(Dom.id('graphe_M_bt'),  Dom.Event.ON_CLICK, function(event) { update_all_charts('graphe_M_bt'); });
/* Dom.addListener(Dom.id('graphe_L_bt'),  Dom.Event.ON_CLICK, function(event) {
    if (isCN('graphe_L_bt', 'grey'))
        p.error('Les graphes sont liés (pas encore implémenté)');
    else
        p.inform('Les graphes ne sont plus liés');

    switchCN('graphe_L_bt', 'grey', 'blue');
    switchCN('graphe_L_bt_icon', 'unlink', 'linkify');
}); */

Dom.addListener(Dom.id('symbol_refresh_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol=<?= $symbol ?>', loading_area: 'main' }); });

changeState = function(item) {
    switchColorElement(item.id, '<?= $bt_interval_colr ?>', '<?= $bt_grey_colr ?>');
}
Dom.find('button.bt_tags').forEach(function(item) {
    Dom.addListener(item, Dom.Event.ON_CLICK, function(event) { changeState(item); });
});

<? if ($sess_context->isSuperAdmin()) { ?>
	Dom.addListener(Dom.id('stock_delete_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'delete', id: 'main', url: 'stock_action.php?action=del&symbol=<?= $symbol ?>)', loading_area: 'main', confirmdel: 1 }); });
<? } ?>

</script>

