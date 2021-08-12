<?

require_once "sess_context.php";

session_start();

include "common.php";

$symbol = "";
$range  = 0;

foreach(['symbol', 'range'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

$req = "SELECT * FROM stocks s, quotes q WHERE s.symbol = q.symbol AND s.symbol='".$symbol."'";
$res = dbc::execSql($req);

if ($row = mysqli_fetch_array($res)) {

    $c = calc::processDataDM($row['symbol'], date("Y-m-d"));

?>

<div class="ui container inverted segment">
    
    <h2 class="ui inverted left floated header"><?= utf8_decode($row['name']) ?> <div class="ui floated right label"><?= $row['symbol'] ?></div></h2>

    <table class="ui selectable inverted single line table">
        <thead>
            <tr><?
                foreach(['Devise', 'Type', 'Région', 'Marché', 'TZ', 'Dernière cotation', 'Prix' , 'DM flottant', 'DM TKL', 'MM200', 'MM20', 'MM7'] as $key)
                    echo "<th>".$key."</th>";
            ?></tr>
        </thead>
        <tbody>
<?
            echo "<tr>
                <td>".$row['currency']."</td>
                <td>".$row['type']."</td><td>".$row['region']."</td>
                <td>".$row['marketopen']."-".$row['marketclose']."</td><td>".$row['timezone']."</td>
                <td>".$row['day']."</td><td>".$row['price']."</td>
                <td>".$c['MMFDM']."%</td>
                <td>".$c['MMZDM']."%</td>
                <td>".$c['MM200']."</td>
                <td>".$c['MM20']."</td>
                <td>".$c['MM7']."</td>
                </tr>";
}

?>
        </tbody>
    </table>
</div>


<?

// GRAPHE COURS

$req2 = "SELECT * FROM daily_time_series_adjusted WHERE symbol='".$symbol."' AND day >= DATE_SUB(NOW(), INTERVAL ".($range == 0 ? 100 : $range)." YEAR) ORDER BY day ASC";
$res2 = dbc::execSql($req2);

$tab_days = array();
$tab_vals = array();
$tab_vols = array();
$tab_cols = array();
while ($row2 = mysqli_fetch_array($res2)) {
    $tab_days[] = $row2['day'];
    $tab_vals[] = $row2['close'];
    $tab_vols[] = $row2['volume'];
    $tab_cols[] = $row2['close'] >= $row2['open'] ? "rgba(97, 194, 97, 0.75)": "red";
}

?>

<div class="ui container inverted segment">
    <h2>Cours <button id="graphe_1Y_bt" class="mini ui <?= $range == 1 ? "blue" : "grey" ?> button">1Y</button><button id="graphe_3Y_bt" class="mini ui <?= $range == 3 ? "blue" : "grey" ?> button">3Y</button><button id="graphe_all_bt" class="mini ui <?= $range == 0 ? "blue" : "grey" ?> button">All</button></h2>
    <canvas id="stock_canvas1" height="100"></canvas>
    <canvas id="stock_canvas2" height="20"></canvas>
</div>
<script>
// Our labels along the x-axis
var days = [<?= '"'.implode('","', $tab_days).'"' ?>];
// For drawing the lines
var vals = [<?= implode(',', $tab_vals) ?>];
var vols = [<?= implode(',', $tab_vols) ?>];
var colors = [<?= '"'.implode('","', $tab_cols).'"' ?>];

var ctx = document.getElementById('stock_canvas1').getContext('2d');
el("stock_canvas1").height = document.body.offsetWidth > 700 ? 100 : 300;

var myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: days,
        datasets: [
        { 
            data: vals,
            label: "Cours",
            borderColor: "rgba(238, 130, 6, 0.75)",
            backgroundColor: "rgba(238, 130, 6, 0.1)",
            cubicInterpolationMode: 'monotone',
            tension: 0.4,
            borderWidth: 0.5,
            fill: true
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
                    display: false,
                    beginAtZero: true
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.5)',
                    offset: true
                },
                padding: 100
            },
            y: {
                min : 0,
                beginAtZero: true
            }
        }
    }
});

var ctx2 = document.getElementById('stock_canvas2').getContext('2d');
el("stock_canvas2").height = document.body.offsetWidth > 700 ? 50 : 100;

var myChart = new Chart(ctx2, {
    type: 'bar',
    legend: {
        display: false
    },
    data: {
        labels: days,
        datasets: [
        { 
            data: vols,
            label: "Volume",
            borderColor: colors,
            backgroundColor: colors,
            borderWidth: 0.5,
            fill: true,
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
                    beginAtZero: true
                }
            },
            y: {
                ticks: {
                    display: false
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
                <table class="ui selectable inverted single line table">
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
                <table class="ui selectable inverted single line table">
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

<script>
Dom.addListener(Dom.id('stock_edit_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'update', id: 'main', url: 'stock_action.php?action=upt&symbol=<?= $symbol ?>&pea='+(valof('f_pea') == 0 ? 0 : 1), loading_area: 'stock_edit_bt' }); });
Dom.addListener(Dom.id('graphe_all_bt'), Dom.Event.ON_CLICK, function(event) { go({ action: 'update', id: 'main', url: 'stock_detail.php?range=0&symbol=<?= $symbol ?>', loading_area: 'graphe_all_bt' }); });
Dom.addListener(Dom.id('graphe_3Y_bt'),  Dom.Event.ON_CLICK, function(event) { go({ action: 'update', id: 'main', url: 'stock_detail.php?range=3&symbol=<?= $symbol ?>', loading_area: 'graphe_3Y_bt' }); });
Dom.addListener(Dom.id('graphe_1Y_bt'),  Dom.Event.ON_CLICK, function(event) { go({ action: 'update', id: 'main', url: 'stock_detail.php?range=1&symbol=<?= $symbol ?>', loading_area: 'graphe_1Y_bt' }); });
</script>

<? } ?>