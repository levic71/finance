<?

require_once "sess_context.php";

session_start();

include "common.php";

$symbol = "";
$range  = 0;
$mmx    = 8;

foreach(['symbol', 'range', 'mmx'] as $key)
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


<?  // GRAPHE COURS

$req2 = "SELECT * FROM daily_time_series_adjusted dtsa, indicators indic WHERE dtsa.symbol=indic.symbol AND dtsa.day=indic.day AND indic.period='DAILY' AND dtsa.symbol='".$symbol."' AND dtsa.day >= DATE_SUB(NOW(), INTERVAL ".($range == 0 ? 100 : $range)." YEAR) ORDER BY dtsa.day ASC";
$res2 = dbc::execSql($req2);

$tab_days = array();
$tab_vals = array();
$tab_vols = array();
$tab_mm7  = array();
$tab_mm20 = array();
$tab_mm50 = array();
$tab_mm200 = array();
$tab_rsi14 = array();
$tab_colors = array();
while ($row2 = mysqli_fetch_array($res2)) {
    $tab_days[] = $row2['day'];
    $tab_vals[] = $row2['close'];
    $tab_vols[] = $row2['volume'];
    $tab_mm7[]    = sprintf("%.2f", $row2['MM7']);
    $tab_mm20[]   = sprintf("%.2f", $row2['MM20']);
    $tab_mm50[]   = sprintf("%.2f", $row2['MM50']);
    $tab_mm200[]  = sprintf("%.2f", $row2['MM200']);
    $tab_rsi14[]  = sprintf("%.2f", $row2['RSI14']);
    $tab_colors[] = $row2['close'] >= $row2['open'] ? 1 : 0;
}

?>

<div class="ui container inverted segment">
    <h2>Cours
        <button id="graphe_1Y_bt"    class="mini ui <?= $range == 1  ? "blue" : "grey" ?> button">1Y</button>
        <button id="graphe_3Y_bt"    class="mini ui <?= $range == 3  ? "blue" : "grey" ?> button">3Y</button>
        <button id="graphe_all_bt"   class="mini ui <?= $range == 0  ? "blue" : "grey" ?> button">All</button>
        <button id="graphe_mm7_bt"   class="mini ui <?= ($mmx & 1) == 1 ? "purple" : "grey" ?> button">MM7</button>
        <button id="graphe_mm20_bt"  class="mini ui <?= ($mmx & 2) == 2 ? "purple" : "grey" ?> button">MM20</button>
        <button id="graphe_mm50_bt"  class="mini ui <?= ($mmx & 4) == 4 ? "purple" : "grey" ?> button">MM50</button>
        <button id="graphe_mm200_bt" class="mini ui <?= ($mmx & 8) == 8 ? "purple" : "grey" ?> button">MM200</button>
    </h2>
    <canvas id="stock_canvas1" height="100"></canvas>
    <canvas id="stock_canvas2" height="20"></canvas>
</div>
<script>
// Our labels along the x-axis
var days = [<?= '"'.implode('","', $tab_days).'"' ?>];
// For drawing the lines
var vals   = [<?= implode(',', $tab_vals) ?>];
var vols   = [<?= implode(',', $tab_vols) ?>];
var mm7    = [<?= implode(',', $tab_mm7) ?>];
var mm20   = [<?= implode(',', $tab_mm20) ?>];
var mm50   = [<?= implode(',', $tab_mm50) ?>];
var mm200  = [<?= implode(',', $tab_mm200) ?>];
var rsi14  = [<?= implode(',', $tab_rsi14) ?>];
var colors = [<?= '"'.implode('","', $tab_colors).'"' ?>];

for (var i = 0; i < colors.length; i++) {
    colors[i] = (colors[i] == 1) ? "green" : "red";
}

var ctx = document.getElementById('stock_canvas1').getContext('2d');
el("stock_canvas1").height = document.body.offsetWidth > 700 ? 100 : 300;

var ds = [{ 
        data: vals,
        label: "Cours",
        yAxisID: 'y1',
        borderColor: "<?= $sess_context->getSpectreColor(0) ?>",
        backgroundColor: "<?= $sess_context->getSpectreColor(0, 0.2) ?>",
        cubicInterpolationMode: 'monotone',
        tension: 0.4,
        borderWidth: 0.5,
        fill: true
    },
    { 
        data: vols,
        type: 'bar',
        label: "Volume",
        yAxisID: 'y2',
        borderColor: colors,
        backgroundColor: colors,
        borderWidth: 0.5,
        fill: true,
        order : 0
    }
];

<? foreach([1, 2, 4, 8] as $x) if (($mmx & $x) == $x) { ?>
ds[ds.length] = { 
        data: <?= $x == 1 ? "mm7" : ($x == 2 ? "mm20" : ($x == 4 ? "mm50" : "mm200")) ?>,
        label: "<?= $x == 1 ? "MM7" : ($x == 2 ? "MM20" : ($x == 4 ? "MM50" : "MM200")) ?>",
        yAxisID: 'y1',
        borderColor: "<?= $sess_context->getSpectreColor($x == 1 ? "4" : ($x == 2 ? "2" : ($x == 4 ? "1" : "6"))) ?>",
        backgroundColor: "<?= $sess_context->getSpectreColor($x == 1 ? "4" : ($x == 2 ? "2" : ($x == 4 ? "1" : "6"))) ?>",
        cubicInterpolationMode: 'monotone',
        tension: 0.4,
        borderWidth: 0.5,
        fill: false
    };
<? } ?>

var myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: days,
        datasets: ds
    },
    options: {
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
                ticks: {
                    minRotation: 90,
                    maxRotation: 90,
                    beginAtZero: true
                }
            },
            y1: {
                type: 'linear',
                position: 'left',
                ticks : {
                }
            },
            y2: {
                type: 'linear',
                position: 'right',
                display: false,
                ticks : {
                    max: 100000000,
                    min: 0,
                    stepSize: 20000000 
                }
            }
        }
    }
});



var ctx2 = document.getElementById('stock_canvas2').getContext('2d');
el("stock_canvas2").height = document.body.offsetWidth > 700 ? 30 : 60;

var myChart2 = new Chart(ctx2, {
    type: 'line',
    legend: {
        display: false
    },
    data: {
        labels: days,
        datasets: [
        { 
            data: rsi14,
            label: "RSI14",
            borderColor: colors,
            backgroundColor: colors,
            cubicInterpolationMode: 'monotone',
            tension: 0.4,
            borderWidth: 0.5,
            fill: false,
            order : 0
        }
    ]},
    options: {
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
                ticks: {
                    display: false
                }
            },
            y: {
                ticks: {
                    crossAlign: 'start',
                    display: true
                },
                afterSetDimensions: (scale) => {
                    scale.maxWidth = 300;
                }
            }
        }
    }
});

</script>



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
<? if ($sess_context->isSuperAdmin()) { ?>
Dom.addListener(Dom.id('stock_edit_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'update', id: 'main', url: 'stock_action.php?action=upt&symbol=<?= $symbol ?>&pea='+(valof('f_pea') == 0 ? 0 : 1), loading_area: 'stock_edit_bt' }); });
<? } ?>
addDataSet = function(chart, label, color, bg, data) {
	chart.data.datasets.push({
	    label: label,
        borderColor: color,
        backgroundColor: bg,
        yAxisID: 'y1',
        cubicInterpolationMode: 'monotone',
        tension: 0.4,
        borderWidth: 0.5,
        fill: false,
        data: data
    });
    chart.update();
}
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
        rmCN(bt, 'purple');
        addCN(bt, 'grey');
        el('mmx').value = parseInt(valof('mmx')) - option;
    } else {
        addCN(bt, 'loading');
        addDataSet(chart, label, color, '', data);
        rmCN(bt, 'loading');
        rmCN(bt, 'grey');
        addCN(bt, 'purple');
        el('mmx').value = parseInt(valof('mmx')) + option;
    }
}

Dom.addListener(Dom.id('graphe_mm7_bt'),   Dom.Event.ON_CLICK, function(event) { toogleMMX(myChart, 'graphe_mm7_bt',   'MM7',   1, '<?= $sess_context->getSpectreColor(4) ?>', mm7);   });
Dom.addListener(Dom.id('graphe_mm20_bt'),  Dom.Event.ON_CLICK, function(event) { toogleMMX(myChart, 'graphe_mm20_bt',  'MM20',  2, '<?= $sess_context->getSpectreColor(2) ?>', mm20);  });
Dom.addListener(Dom.id('graphe_mm50_bt'),  Dom.Event.ON_CLICK, function(event) { toogleMMX(myChart, 'graphe_mm50_bt',  'MM50',  4, '<?= $sess_context->getSpectreColor(1) ?>', mm50);  });
Dom.addListener(Dom.id('graphe_mm200_bt'), Dom.Event.ON_CLICK, function(event) { toogleMMX(myChart, 'graphe_mm200_bt', 'MM200', 8, '<?= $sess_context->getSpectreColor(6) ?>', mm200); });

Dom.addListener(Dom.id('graphe_all_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?range=0&mmx='+valof('mmx')+'&symbol=<?= $symbol ?>', loading_area: 'main' }); });
Dom.addListener(Dom.id('graphe_3Y_bt'),  Dom.Event.ON_CLICK, function(event) { go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?range=3&mmx='+valof('mmx')+'&symbol=<?= $symbol ?>', loading_area: 'main' }); });
Dom.addListener(Dom.id('graphe_1Y_bt'),  Dom.Event.ON_CLICK, function(event) { go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?range=1&mmx='+valof('mmx')+'&symbol=<?= $symbol ?>', loading_area: 'main' }); });

Dom.addListener(Dom.id('symbol_refresh_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'stock_detail', id: 'main', url: 'stock_detail.php?symbol=<?= $symbol ?>', loading_area: 'main' }); });

</script>

