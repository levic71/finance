<?

require_once "sess_context.php";

session_start();

include "common.php";

$symbol = "";
$rsi_choice = 0;
$mmx = 8;
$display_wm = false; // Display weekly/monthly graph si superadmin user

foreach(['symbol', 'mmx'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

$req = "SELECT * FROM stocks s, quotes q WHERE s.symbol = q.symbol AND s.symbol='".$symbol."'";
$res = dbc::execSql($req);

if ($row = mysqli_fetch_array($res)) {

    $c = calc::processDataDM($row['symbol'], date("Y-m-d"));

    $curr = $row['currency'] == "EUR" ? "&euro;" : "$";

?>

<input type="hidden" id="mmx" value="<?= $mmx ?>" />

<div class="ui container inverted segment">
    
    <h2 class="ui inverted left floated header"><?= utf8_decode($row['name']) ?> <div id="symbol_refresh_bt" class="ui floated right label button"><?= $row['symbol'] ?></div></h2>

    <table id="detail_stock" class="ui selectable inverted single line table">
        <thead>
            <tr><?
                foreach(['Devise', 'Type', 'Région', 'Marché', 'TZ', 'Dernière cotation', 'Prix' , 'DM flottant', 'DM TKL', 'MM200', 'MM20', 'MM7'] as $key)
                    echo "<th>".$key."</th>";
            ?></tr>
        </thead>
        <tbody>
<?
        echo "<tr>
                <td data-label=\"Devise\">".$row['currency']."</td>
                <td data-label=\"Région\">".$row['type']."</td>
                <td data-label=\"Région\">".$row['region']."</td>
                <td data-label=\"Marché\">".$row['marketopen']."-".$row['marketclose']."</td>
                <td data-label=\"TZ\">".$row['timezone']."</td>
                <td data-label=\"Dernière Cotation\">".$row['day']."</td>
                <td data-label=\"Prix\">".sprintf("%.2f", $row['price']).$curr."</td>
                <td data-label=\"DM flottant\">".$c['MMFDM']."%</td>
                <td data-label=\"DM TKL\">".$c['MMZDM']."%</td>
                <td data-label=\"M200\">".sprintf("%.2f", $c['MM200']).$curr."</td>
                <td data-label=\"MM20\">".sprintf("%.2f", $c['MM20']).$curr."</td>
                <td data-label=\"MM7\">".sprintf("%.2f", $c['MM7']).$curr."</td>
            </tr>";
}

?>
        </tbody>
    </table>
</div>


<?
// /////////////////////
// GRAPHES COURS
// //////////////////////
function getTimeSeriesData($table_name, $period, $sym) {

    $req = "SELECT * FROM ".$table_name." dtsa, indicators indic WHERE dtsa.symbol=indic.symbol AND dtsa.day=indic.day AND indic.period='".$period."' AND dtsa.symbol='".$sym."' ORDER BY dtsa.day ASC";
    $res = dbc::execSql($req);
    
    $t_rows = array();
    $t_cols = array();
    while ($row = mysqli_fetch_array($res)) {
        $t_rows[] = $row;
        $t_colrs[] = $row['close'] >= $row['open'] ? 1 : 0;
    }

    return array("rows" => $t_rows, "colrs" => $t_colrs);
}

$data_daily   = getTimeSeriesData("daily_time_series_adjusted",   "DAILY",   $symbol);
$data_weekly  = getTimeSeriesData("weekly_time_series_adjusted",  "WEEKLY",  $symbol);
$data_monthly = getTimeSeriesData("monthly_time_series_adjusted", "MONTHLY", $symbol);

?>


<div id="canvas_area" class="ui container inverted segment">
    <p>Cours
        <button id="graphe_1T_bt"    class="mini ui grey button">1T</button>
        <button id="graphe_1Y_bt"    class="mini ui grey button">1Y</button>
        <button id="graphe_3Y_bt"    class="mini ui grey button">3Y</button>
        <button id="graphe_all_bt"   class="mini ui blue button">All</button>
        <button id="graphe_mm7_bt"   class="mini ui <?= ($mmx & 1) == 1 ? "purple" : "grey" ?> button">MM7</button>
        <button id="graphe_mm20_bt"  class="mini ui <?= ($mmx & 2) == 2 ? "purple" : "grey" ?> button">MM20</button>
        <button id="graphe_mm50_bt"  class="mini ui <?= ($mmx & 4) == 4 ? "purple" : "grey" ?> button">MM50</button>
        <button id="graphe_mm200_bt" class="mini ui <?= ($mmx & 8) == 8 ? "purple" : "grey" ?> button">MM200</button>
    </p>
    <canvas id="stock_canvas1" height="100"></canvas>
    <canvas id="stock_canvas2" height="20"></canvas>
    <p>
        RSI14
        <button id="graphe_D_bt"    class="mini ui <?= $rsi_choice == 0  ? "blue" : "grey" ?> button">Daily</button>
        <button id="graphe_W_bt"    class="mini ui <?= $rsi_choice == 1  ? "blue" : "grey" ?> button">Weekly</button>
        <button id="graphe_M_bt"    class="mini ui <?= $rsi_choice == 2  ? "blue" : "grey" ?> button">Monthly</button>
    </p>
<? if ($display_wm && $sess_context->isSuperAdmin()) { ?>
    <canvas id="stock_canvas3" height="100"></canvas>
    <canvas id="stock_canvas4" height="20"></canvas>
    <canvas id="stock_canvas5" height="100"></canvas>
    <canvas id="stock_canvas6" height="20"></canvas>
<? } ?>
</div>


<? if ($sess_context->isSuperAdmin()) { ?>

<div class="ui container inverted grid segment">
    <div class="column">

        <div class="ui inverted stackable two column grid container">
            <div class="wide column">
                <table id="detail2_stock" class="ui selectable inverted single line table">
                    <tbody>
                    <?  echo '
                            <tr><td>Ref date MMZ1M</td><td>'.$c["MMZ1MDate"].'</td></tr>
                            <tr><td>Ref date MMZ3M</td><td>'.$c["MMZ3MDate"].'</td></tr>
                            <tr><td>Ref date MMZ6M</td><td>'.$c["MMZ6MDate"].'</td></tr>
                            <tr><td>PEA</td><td>
                                <div class="ui fitted toggle checkbox">
                                    <input id="f_pea" type="checkbox" '.($row["pea"] == 1 ? 'checked="checked"' : '').'>
                                    <label></label>
                                </div>
                            </td></tr>
                    '; ?>
                    </tbody>
                </table>
            </div>

            <div class="wide column">
                <table id="detail3_stock" class="ui selectable inverted single line table">
                    <tbody>
                    <?
                        foreach(cacheData::$lst_cache as $key)
                        echo "<tr><td>Cache ".$key."_".$symbol.".json</td><td>".(file_exists("cache/".$key."_".$symbol.".json") ? "<i class=\"ui icon inverted green check\"></i>" : "<i class=\"ui icon inverted red x\"></i>")."</td></tr>";
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<div class="ui container inverted segment">
    <h2 class="ui inverted right aligned header"><button id="stock_edit_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white edit icon"></i> Modifier</button></h2>
</div>

<? } ?>


<script>

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
getDatasetVols  = function(vals, cols) { return newDataset(vals, 'bar',  'y2', 'Volume', cols, cols, true); }
getDatasetMMX   = function(vals, l, c) { return newDataset(vals, 'line', 'y1', l, c, '', false); }
getDatasetRSI14 = function(vals) { return newDataset(vals, 'line', 'y', "RSI14", 'violet', '', false); }

var graphe_size_days = 0;

var ref_days = [<?= '"'.implode('","', array_column($data_daily["rows"], "day")).'"' ?>];
var ref_vals   = [<?= implode(',', array_column($data_daily["rows"], "close")) ?>];
var ref_vols   = [<?= implode(',', array_column($data_daily["rows"], "volume")) ?>];
var ref_mm7    = [<?= implode(',', array_column($data_daily["rows"], "MM7")) ?>];
var ref_mm20   = [<?= implode(',', array_column($data_daily["rows"], "MM20")) ?>];
var ref_mm50   = [<?= implode(',', array_column($data_daily["rows"], "MM50")) ?>];
var ref_mm200  = [<?= implode(',', array_column($data_daily["rows"], "MM200")) ?>];
var ref_rsi14  = [<?= implode(',', array_column($data_daily["rows"], "RSI14")) ?>];
var ref_colors = [<?= '"'.implode('","', $data_daily["colrs"]).'"' ?>];

var days    = ref_days;
var vals    = ref_vals;
var vols    = ref_vols;
var mm7     = ref_mm7;
var mm20    = ref_mm20;
var mm50    = ref_mm50;
var mm200   = ref_mm200;
var colors  = ref_colors;
var d_days  = ref_days;
var d_rsi14 = ref_rsi14;

for (var i = 0; i < colors.length; i++) {
    colors[i] = (colors[i] == 1) ? "green" : "red";
}

var ctx1 = document.getElementById('stock_canvas1').getContext('2d');
el("stock_canvas1").height = document.body.offsetWidth > 700 ? 100 : 300;

var datasets1 = [];
datasets1.push(getDatasetVals(vals));

<? foreach([1, 2, 4, 8] as $x) if (($mmx & $x) == $x) { ?>
datasets1.push(getDatasetMMX(
    <?= $x == 1 ? "mm7" : ($x == 2 ? "mm20" : ($x == 4 ? "mm50" : "mm200")) ?>,
    '<?= $x == 1 ? "MM7" : ($x == 2 ? "MM20" : ($x == 4 ? "MM50" : "MM200")) ?>',
    '<?= $sess_context->getSpectreColor($x == 1 ? "4" : ($x == 2 ? "2" : ($x == 4 ? "1" : "6"))) ?>',
));
<? } ?>
datasets1.push(getDatasetVols(vols, colors));

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

var myChart1 = new Chart(ctx1, { type: 'line', data: { labels: days, datasets: datasets1 }, options: options1 });



var ctx2 = document.getElementById('stock_canvas2').getContext('2d');
el("stock_canvas2").height = document.body.offsetWidth > 700 ? 30 : 60;

var datasets2 = [];
datasets2.push(getDatasetRSI14(d_rsi14));

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
        console.log('h:'+height+'y:'+y+'b:'+bottom+'t:'+top);
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

var myChart2 = new Chart(ctx2, { type: 'line', data: { labels: d_days, datasets: datasets2 }, options: options2, plugins: [horizontalLines] });

var ref_w_days   = [<?= '"'.implode('","', array_column($data_weekly["rows"], "day")).'"' ?>];
var ref_w_rsi14  = [<?= implode(',', array_column($data_weekly["rows"], "RSI14")) ?>];
var w_days   = ref_w_days;
var w_rsi14  = ref_w_rsi14;

var ref_m_days   = [<?= '"'.implode('","', array_column($data_monthly["rows"], "day")).'"' ?>];
var ref_m_rsi14  = [<?= implode(',', array_column($data_monthly["rows"], "RSI14")) ?>];
var m_days   = ref_m_days;
var m_rsi14  = ref_m_rsi14;



<? if ($display_wm && $sess_context->isSuperAdmin()) { ?>

var ref_w_vals   = [<?= implode(',', array_column($data_weekly["rows"], "close")) ?>];
var ref_w_vols   = [<?= implode(',', array_column($data_weekly["rows"], "volume")) ?>];
var ref_w_colors = [<?= '"'.implode('","', $data_weekly["colrs"]).'"' ?>];

var w_vals   = ref_w_vals;
var w_vols   = ref_w_vols;
var w_colors = ref_w_colors;

for (var i = 0; i < w_colors.length; i++) {
    w_colors[i] = (w_colors[i] == 1) ? "green" : "red";
}

var ref_m_vals   = [<?= implode(',', array_column($data_monthly["rows"], "close")) ?>];
var ref_m_vols   = [<?= implode(',', array_column($data_monthly["rows"], "volume")) ?>];
var ref_m_colors = [<?= '"'.implode('","', $data_monthly["colrs"]).'"' ?>];

var m_vals   = ref_m_vals;
var m_vols   = ref_m_vols;
var m_colors = ref_m_colors;

for (var i = 0; i < m_colors.length; i++) {
    m_colors[i] = (m_colors[i] == 1) ? "green" : "red";
}


var ctx3 = document.getElementById('stock_canvas3').getContext('2d');
el("stock_canvas3").height = document.body.offsetWidth > 700 ? 100 : 300;
var datasets3 = [];
datasets3.push(getDatasetVals(w_vals));
datasets3.push(getDatasetVols(w_vols, w_colors));
var myChart3 = new Chart(ctx3, { type: 'line', data: { labels: w_days, datasets: datasets3 }, options: options1 });

var ctx4 = document.getElementById('stock_canvas4').getContext('2d');
el("stock_canvas4").height = document.body.offsetWidth > 700 ? 30 : 60;
var datasets4 = [];
datasets4.push(getDatasetRSI14(w_rsi14));
var myChart4 = new Chart(ctx4, { type: 'line', data: { labels: w_days, datasets: datasets4 }, options: options2, plugins: [horizontalLines] });

var ctx5 = document.getElementById('stock_canvas5').getContext('2d');
el("stock_canvas5").height = document.body.offsetWidth > 700 ? 100 : 300;
var datasets5 = [];
datasets5.push(getDatasetVals(m_vals));
datasets5.push(getDatasetVols(m_vols, m_colors));
var myChart5 = new Chart(ctx5, { type: 'line', data: { labels: m_days, datasets: datasets5 }, options: options1 });

var ctx6 = document.getElementById('stock_canvas6').getContext('2d');
el("stock_canvas6").height = document.body.offsetWidth > 700 ? 30 : 60;
var datasets6 = [];
datasets6.push(getDatasetRSI14(m_rsi14));
var myChart6 = new Chart(ctx6, { type: 'line', data: { labels: m_days, datasets: datasets6 }, options: options2, plugins: [horizontalLines] });

<? } ?>



toogleMMX = function(chart, bt, label, option, color, data) {
    if (isCN(bt, 'purple')) {
        addCN(bt, 'loading');
        chart.data.datasets.forEach((dataset) => {
            if (dataset.label == label) {
                dataset.data=null;
            }
        });
        chart.update();
        rmCN(bt, 'loading');
        switchCN(bt, 'grey', 'purple');
        el('mmx').value = parseInt(valof('mmx')) - option;
    } else {
        addCN(bt, 'loading');
        chart.data.datasets.push(newDataset(data, 'line', 'y1', label, color, '', false));
        chart.update();
        rmCN(bt, 'loading');
        switchCN(bt, 'grey', 'purple');
        el('mmx').value = parseInt(valof('mmx')) + option;
    }
}

update_data = function(size) {
    days   = size == 0 ? ref_days   : get_slice(ref_days, size);
    vals   = size == 0 ? ref_vals   : get_slice(ref_vals, size);
    vols   = size == 0 ? ref_vols   : get_slice(ref_vols, size);
    mm7    = size == 0 ? ref_mm7    : get_slice(ref_mm7,  size);
    mm20   = size == 0 ? ref_mm20   : get_slice(ref_mm20, size);
    mm50   = size == 0 ? ref_mm50   : get_slice(ref_mm50, size);
    mm200  = size == 0 ? ref_mm50   : get_slice(ref_mm200, size);
    colors = size == 0 ? ref_colors : get_slice(ref_colors, size);
    size = size == 0 ? 0 : (isCN('graphe_W_bt', 'blue') ? Math.ceil(graphe_size_days / 5) : Math.ceil(graphe_size_days / 12));
    d_days  = size == 0 ? ref_days   : get_slice(ref_days, size);
    d_rsi14 = size == 0 ? ref_rsi14  : get_slice(ref_rsi14, size);
}

update_rsi14_data = function() {
    size = graphe_size_days == 0 ? 0 : (isCN('graphe_W_bt', 'blue') ? Math.ceil(graphe_size_days / 5) : Math.ceil(graphe_size_days / 12));
    d_days  = size == 0 ? ref_days    : get_slice(ref_days, size);
    d_rsi14 = size == 0 ? ref_rsi14   : get_slice(ref_rsi14, size);
    w_days  = size == 0 ? ref_w_days  : get_slice(ref_w_days, size);
    w_rsi14 = size == 0 ? ref_w_rsi14 : get_slice(ref_w_rsi14, size);
    m_days  = size == 0 ? ref_m_days  : get_slice(ref_m_days, size);
    m_rsi14 = size == 0 ? ref_m_rsi14 : get_slice(ref_m_rsi14, size);
}

update_all_charts = function(bt, c1, c2, size) {    

    // On memorize le nb de days afficher
    graphe_size_days = size;

    addCN(bt, 'loading');
    ['graphe_1T_bt', 'graphe_1Y_bt', 'graphe_3Y_bt', 'graphe_all_bt'].forEach((bt) => { replaceCN(bt, c2, c1); });
    switchCN(bt, c1, c2);
    update_data(size);

    // Update Chart 1
    myChart1.destroy();
    var datasets1 = [];
    datasets1.push(getDatasetVals(vals));
    if (isCN('graphe_mm7_bt',   'purple')) datasets1.push(getDatasetMMX(mm7,   'MM7',   '<?= $sess_context->getSpectreColor(4) ?>'));
    if (isCN('graphe_mm20_bt',  'purple')) datasets1.push(getDatasetMMX(mm20,  'MM20',  '<?= $sess_context->getSpectreColor(2) ?>'));
    if (isCN('graphe_mm50_bt',  'purple')) datasets1.push(getDatasetMMX(mm50,  'MM50',  '<?= $sess_context->getSpectreColor(1) ?>'));
    if (isCN('graphe_mm200_bt', 'purple')) datasets1.push(getDatasetMMX(mm200, 'MM200', '<?= $sess_context->getSpectreColor(6) ?>'));
    datasets1.push(getDatasetVols(vols, colors));
    myChart1 = new Chart(ctx1, { type: 'line', data: { labels: days, datasets: datasets1 }, options: options1 });
    myChart1.update();

    // Update Chart 2
    myChart2.destroy();
    var datasets2 = [];
    datasets2.push(getDatasetRSI14(isCN('graphe_D_bt', 'blue') ? d_rsi14 : (isCN('graphe_W_bt', 'blue') ? w_rsi14 : m_rsi14)));
    d = isCN('graphe_D_bt', 'blue') ? d_days : (isCN('graphe_W_bt', 'blue') ? w_days : m_days);
    myChart2 = new Chart(ctx2, { type: 'line', data: { labels: d, datasets: datasets2 }, options: options2, plugins: [horizontalLines] });
    myChart2.update();

    rmCN(bt, 'loading');
}

update_rsi14_charts = function(bt, c1, c2) {
    
    addCN(bt, 'loading');
    ['graphe_D_bt', 'graphe_W_bt', 'graphe_M_bt'].forEach((bt) => { replaceCN(bt, c2, c1); });
    switchCN(bt, c1, c2);
    update_rsi14_data();

    // Update Chart 2
    myChart2.destroy();
    var datasets2 = [];
    datasets2.push(getDatasetRSI14(bt == 'graphe_D_bt' ? d_rsi14 : (bt == 'graphe_W_bt' ? w_rsi14: m_rsi14)));
    var d = bt == 'graphe_D_bt' ? d_days : (bt == 'graphe_W_bt' ? w_days: m_days);
    myChart2 = new Chart(ctx2, { type: 'line', data: { labels: d, datasets: datasets2 }, options: options2, plugins: [horizontalLines] });
    myChart2.update();

    rmCN(bt, 'loading');
}

min_slice = function(tab, size) { return (tab.length-size-1) > 0 ? (tab.length-size-1) : 0; }
max_slice = function(tab) { return tab.length-1 > 0 ? tab.length-1 : 0; }
get_slice = function(tab, size) { return tab.slice(min_slice(tab, size), max_slice(tab));}

<? if ($sess_context->isSuperAdmin()) { ?>
Dom.addListener(Dom.id('stock_edit_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'update', id: 'main', url: 'stock_action.php?action=upt&symbol=<?= $symbol ?>&pea='+(valof('f_pea') == 0 ? 0 : 1), loading_area: 'stock_edit_bt' }); });
<? } ?>

Dom.addListener(Dom.id('graphe_mm7_bt'),   Dom.Event.ON_CLICK, function(event) { toogleMMX(myChart1, 'graphe_mm7_bt',   'MM7',   1, '<?= $sess_context->getSpectreColor(4) ?>', mm7);   });
Dom.addListener(Dom.id('graphe_mm20_bt'),  Dom.Event.ON_CLICK, function(event) { toogleMMX(myChart1, 'graphe_mm20_bt',  'MM20',  2, '<?= $sess_context->getSpectreColor(2) ?>', mm20);  });
Dom.addListener(Dom.id('graphe_mm50_bt'),  Dom.Event.ON_CLICK, function(event) { toogleMMX(myChart1, 'graphe_mm50_bt',  'MM50',  4, '<?= $sess_context->getSpectreColor(1) ?>', mm50);  });
Dom.addListener(Dom.id('graphe_mm200_bt'), Dom.Event.ON_CLICK, function(event) { toogleMMX(myChart1, 'graphe_mm200_bt', 'MM200', 8, '<?= $sess_context->getSpectreColor(6) ?>', mm200); });

Dom.addListener(Dom.id('graphe_all_bt'), Dom.Event.ON_CLICK, function(event) { update_all_charts('graphe_all_bt', 'grey', 'blue', 0); });
Dom.addListener(Dom.id('graphe_3Y_bt'),  Dom.Event.ON_CLICK, function(event) { update_all_charts('graphe_3Y_bt',  'grey', 'blue', 280*3); });
Dom.addListener(Dom.id('graphe_1Y_bt'),  Dom.Event.ON_CLICK, function(event) { update_all_charts('graphe_1Y_bt',  'grey', 'blue', 280); });
Dom.addListener(Dom.id('graphe_1T_bt'),  Dom.Event.ON_CLICK, function(event) { update_all_charts('graphe_1T_bt',  'grey', 'blue', 70); });

Dom.addListener(Dom.id('graphe_D_bt'),  Dom.Event.ON_CLICK, function(event) { update_rsi14_charts('graphe_D_bt', 'grey', 'blue'); });
Dom.addListener(Dom.id('graphe_W_bt'),  Dom.Event.ON_CLICK, function(event) { update_rsi14_charts('graphe_W_bt', 'grey', 'blue'); });
Dom.addListener(Dom.id('graphe_M_bt'),  Dom.Event.ON_CLICK, function(event) { update_rsi14_charts('graphe_M_bt', 'grey', 'blue'); });

Dom.addListener(Dom.id('symbol_refresh_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol=<?= $symbol ?>', loading_area: 'main' }); });

</script>

