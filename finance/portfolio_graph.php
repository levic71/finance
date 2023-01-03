<?

require_once "sess_context.php";

session_start();

include "common.php";

$portfolio_id = 0;

foreach (['portfolio_id'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) {
	uimx::staticInfoMsg("VOUS DEVEZ ETRE CONNECTE POUR UTILISER CETTE FONCTIONNALITE", "comment outline", "blue");
	exit(0);
}

// $data_ptf = [ 'days' => [ "0" ], 'valo' => [ "0" ], 'depot' => [ "0" ] ];

// Recuperation des infos du portefeuille
$req = "SELECT pv.*, p.name FROM portfolios p, portfolio_valo pv WHERE pv.portfolio_id=".$portfolio_id." AND p.id=pv.portfolio_id AND p.user_id=".$sess_context->getUserId();
$res = dbc::execSql($req);
$name = "";

// Bye bye si inexistant
while ($row = mysqli_fetch_assoc($res)) {

    $data = json_decode($row['data']);
    // var_dump($data);
    $data_ptf['days'][$row['date']]  = $row['date'];
    $data_ptf['valo'][$row['date']]  = Round($data->valo_ptf);
    $data_ptf['depot'][$row['date']] = Round($data->depot);
    $name = $row['name'];

}

// Ajout de la valo TR
$data_ptf_now = calc::aggregatePortfolioById($portfolio_id);
$data_ptf['days'][]  = date("Y-m-d");
$data_ptf['valo'][]  = Round($data_ptf_now['valo_ptf']);
$data_ptf['depot'][] = Round($data_ptf_now['depot']);
$my_portfolio  = $data_ptf_now['infos'];
$lst_positions = $data_ptf_now['positions'];
$lst_orders    = $data_ptf_now['orders'];
$lst_trend_following = $data_ptf_now['trend_following'];

$nb_orders = 0;
$sum_commission = 0;
$tab_orders = [];
foreach($lst_orders as $key => $val) {

    if ($val['confirme'] == 0) continue;

    $sum_commission += $val['commission'];
    $tab_orders['days'][$val['date']] = $val['date'];

    if ($val['action'] == 1 || $val['action'] == -1) $nb_orders++;

    if ($val['action'] == 1) {
        $tab_orders['achat'][$val['date']] = (isset($tab_orders['achat'][$val['date']]) ? $tab_orders['achat'][$val['date']] : 0) + ($val['price'] * $val['quantity'] * $val['taux_change']);
    } if ($val['action'] == 2) {
        $tab_orders['depot'][$val['date']] = (isset($tab_orders['depot'][$val['date']]) ? $tab_orders['depot'][$val['date']] : 0) + ($val['price'] * $val['quantity'] * $val['taux_change']);
    } if ($val['action'] == 4 || $val['action'] == 6) {
        $tab_orders['dividende'][$val['date']] = (isset($tab_orders['dividende'][$val['date']]) ? $tab_orders['dividende'][$val['date']] : 0) + ($val['price'] * $val['quantity'] * $val['taux_change']);
    } if ($val['action'] == -1) {
        $tab_orders['vente'][$val['date']] = (isset($tab_orders['vente'][$val['date']]) ? $tab_orders['vente'][$val['date']] : 0) + ($val['price'] * $val['quantity'] * $val['taux_change']);
    } if ($val['action'] == -2) {
        $tab_orders['retrait'][$val['date']] = (isset($tab_orders['retrait'][$val['date']]) ? $tab_orders['retrait'][$val['date']] : 0) + ($val['price'] * $val['quantity'] * $val['taux_change']);
    }

}

// Completion a zero pour les jours vide
foreach($data_ptf["days"] as $key => $val) {
    foreach([ 'achat', 'vente', 'depot', 'retrait', 'dividende'] as $key2 => $elt)
        if (!isset($tab_orders[$elt][$val])) $tab_orders[$elt][$val] = 0;
}
foreach([ 'achat', 'vente', 'depot', 'retrait', 'dividende'] as $key2 => $elt) ksort($tab_orders[$elt]);

// Completion a zero pour les jours vide
foreach($tab_orders["days"] as $key => $val) {
    foreach([ 'days', 'valo', 'depot'] as $key2 => $elt)
        if (!isset($data_ptf["days"][$val])) $data_ptf[$elt][$val] = $elt == 'days' ? $val : "";
}
foreach([ 'days', 'valo', 'depot'] as $key2 => $elt)
    ksort($data_ptf[$elt]);

?>

<h2 class="ui left floated"><i class="inverted briefcase icon"></i><?= $name." <small>Nb ordres=".$nb_orders." Commissions=".$sum_commission."&euro;</small>" ?></h2>

<div id="canvas_area" class="ui container inverted segment">
    <canvas id="portfolio_canvas" height="100"></canvas>
    <canvas id="orders_canvas"    height="100"></canvas>
</div>

<script>

var myChart1 = null;
var myChart2 = null;

newDataset = function(mydata, mytype, yaxeid, mylabel, mycolor, bg, myfill, myborderwith = 0.5, mytension = 0.4, myradius = 0) {

    var ret = {
        type: mytype,
        data: mydata,
        label: mylabel,
        borderColor: mycolor,
        borderWidth: myborderwith,
        yAxisID: yaxeid,
        cubicInterpolationMode: 'monotone',
        tension: mytension,
        backgroundColor: bg,
        fill: myfill,
        pointRadius: myradius,
    };

    return ret;
}

getDatasetVals = function(label, type, vals, colr, bgcolr) {
    return newDataset(vals, type, 'y', label, colr, bgcolr, true);
}

getDatasetVals2 = function(label, type, vals, colr, bgcolr, stack) {
    var ds = newDataset(vals, type, 'y', label, colr, bgcolr, true);
    ds.stack = stack;
    return ds;
}

var graphe_size_days = 0;

// Ref Daily data
var g1_days   = [<?= '"' . implode('","', $data_ptf["days"]) . '"' ?>];
var g1_vals   = [<?= implode(',', $data_ptf["valo"])  ?>];
var g2_vals   = [<?= implode(',', $data_ptf["depot"]) ?>];
var g3_vals   = [<?= implode(',', $tab_orders["achat"]) ?>];
var g4_vals   = [<?= implode(',', $tab_orders["vente"]) ?>];
var g5_vals   = [<?= implode(',', $tab_orders["depot"]) ?>];
var g6_vals   = [<?= implode(',', $tab_orders["retrait"])   ?>];
var g7_vals   = [<?= implode(',', $tab_orders["dividende"]) ?>];

var ctx1 = document.getElementById('portfolio_canvas').getContext('2d');
el("portfolio_canvas").height = document.body.offsetWidth > 700 ? 80 : 200;

var ctx2 = document.getElementById('orders_canvas').getContext('2d');
el("orders_canvas").height = document.body.offsetWidth > 700 ? 40 : 100;

update_graph_chart = function(c, ctx, opts, lbls, dtsts, plg) {
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

    return c;
}

update_all_charts = function() {

    // Update Chart Portfolio
    var datasets1 = [];
    datasets1.push(getDatasetVals('D\u00e9pot', 'line', g2_vals, '<?= $sess_context->getSpectreColor(2) ?>', '<?= $sess_context->getSpectreColor(2, 0.15) ?>'));
    datasets1.push(getDatasetVals('Valo',       'line', g1_vals, '<?= $sess_context->getSpectreColor(3) ?>', '<?= $sess_context->getSpectreColor(3, 0.3) ?>'));
    myChart1 = update_graph_chart(myChart1, ctx1, options_Valo_Graphe, g1_days, datasets1, []);

    // Update Chart Orders
    var datasets2 = [];
    datasets2.push(getDatasetVals2('Achat', 'bar', g3_vals,     'rgba(150, 238, 44, 1)', 'rgba(150, 238, 44, 1)', 'In'));
    datasets2.push(getDatasetVals2('Vente', 'bar', g4_vals,     'rgba(236, 3, 59, 1)', 'rgba(236, 3, 59, 1)', 'Out'));
    datasets2.push(getDatasetVals2('Depot', 'bar', g5_vals,     'rgba(3, 130, 236, 1)', 'rgba(3, 130, 236, 1)', 'In'));
    datasets2.push(getDatasetVals2('Retrait', 'bar', g6_vals,   'rgba(238, 229, 44, 1)', 'rgba(238, 229, 44, 1)', 'Out'));
    datasets2.push(getDatasetVals2('Dividende', 'bar', g7_vals, 'rgba(0, 236, 193, 1)', 'rgba(0, 236, 193, 1)', 'In'));
    options_Orders_Graphe.scales['x'].ticks.display = true;
    myChart2 = update_graph_chart(myChart2, ctx2, options_Orders_Graphe, g1_days, datasets2, []);

}

// Initialisation des graphes
update_all_charts();

</script>