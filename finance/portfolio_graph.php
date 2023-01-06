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

// Recuperation des infos du portefeuille
$req = "SELECT pv.*, p.name FROM portfolios p, portfolio_valo pv WHERE pv.portfolio_id=".$portfolio_id." AND p.id=pv.portfolio_id AND p.user_id=".$sess_context->getUserId();
$res = dbc::execSql($req);
$name = "";

// Bye bye si inexistant
while ($row = mysqli_fetch_assoc($res)) {

    $data = json_decode($row['data']);
    // var_dump($data);
    $data_ptf[$row['date']]['day']  = $row['date'];
    $data_ptf[$row['date']]['valo']  = Round($data->valo_ptf);
    $data_ptf[$row['date']]['depot_acc'] = Round($data->depot);
    $name = $row['name'];

}

// Ajout de la valo temps reel
$data_ptf_now = calc::aggregatePortfolioById($portfolio_id);
$data_ptf[date("Y-m-d")]['day']   = date("Y-m-d");
$data_ptf[date("Y-m-d")]['valo']  = Round($data_ptf_now['valo_ptf']);
$data_ptf[date("Y-m-d")]['depot_acc'] = Round($data_ptf_now['depot']);

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

    $pricing = $val['price'] * $val['quantity'] * $val['taux_change'];

    if ($val['action'] == 1) {
        $data_ptf[$val['date']]['achat'] = (isset($tab_orders[$val['date']]['achat']) ? $tab_orders[$val['date']]['achat'] : 0) + $pricing;
    } if ($val['action'] == 2) {
        $data_ptf[$val['date']]['depot_j'] = (isset($tab_orders[$val['date']]['depot_j']) ? $tab_orders[$val['date']]['depot_j'] : 0) + $pricing;
    } if ($val['action'] == 4 || $val['action'] == 6) {
        $data_ptf[$val['date']]['dividende'] = (isset($tab_orders[$val['date']]['dividende']) ? $tab_orders[$val['date']]['dividende'] : 0) + $pricing;
    } if ($val['action'] == -1) {
        $data_ptf[$val['date']]['vente'] = (isset($tab_orders[$val['date']]['vente']) ? $tab_orders[$val['date']]['vente'] : 0) + $pricing;
    } if ($val['action'] == -2) {
        $data_ptf[$val['date']]['retrait'] = (isset($tab_orders[$val['date']]['retrait']) ? $tab_orders[$val['date']]['retrait'] : 0) + $pricing;
    }

}

?>

<h2 class="ui left floated"><i class="inverted briefcase icon"></i><?= $name." <small>Nb ordres=".$nb_orders." Commissions=".$sum_commission."&euro;</small>" ?></h2>

<div id="canvas_area" class="ui container inverted segment">
    <canvas id="portfolio_canvas" height="100"></canvas>
</div>

<script>

var myChart = null;

var mydata = [<?
    $i = 1;
    $count = count($data_ptf);
    ksort($data_ptf);

    foreach($data_ptf as $key => $val) {
        echo sprintf("{ d: '%s', vl: %.2f, da: %.2f, ha: %.2f, dj: %.2f, dd: %.2f, vt: %.2f, rt: %.2f }%s",
            $key,
            isset($val["valo"])      ? $val["valo"]      : 0,
            isset($val["depot_acc"]) ? $val["depot_acc"] : 0,
            isset($val["achat"])     ? $val["achat"]     : 0,
            isset($val["depot_j"])   ? $val["depot_j"]   : 0,
            isset($val["dividende"]) ? $val["dividende"] : 0,
            isset($val["vente"])     ? $val["vente"]     : 0,
            isset($val["retrait"])   ? $val["retrait"]   : 0,
            $i++ == $count ? '' : ','
        );
    }
?>];

var mydays = [<?
    $i = 1;
    $count = count($data_ptf);
    ksort($data_ptf);

    foreach($data_ptf as $key => $val) {
        echo sprintf("'%s'%s",
            $key,
            $i++ == $count ? '' : ','
        );
    }
?>];

newDataset = function(mydata, mytype, yaxeid, yaxekey, mylabel, mycolor, bg, myfill, myborderwith = 0.5, mytension = 0.4, myradius = 0) {

    var ret = {
        type: mytype,
        data: mydata,
        label: mylabel,
        borderColor: mycolor,
        borderWidth: myborderwith,
        yAxisID: yaxeid,
        parsing: {
            xAxisKey: 'd',
            yAxisKey: yaxekey
        },
        cubicInterpolationMode: 'monotone',
        tension: mytension,
        backgroundColor: bg,
        fill: myfill,
        pointRadius: myradius,
        normalized: true
    };

    return ret;
}

getDatasetVals = function(label, type, vals, yaxekey, colr, bgcolr) {
    return newDataset(vals, type, 'y', yaxekey, label, colr, bgcolr, true);
}

getDatasetVals2 = function(label, type, vals, yaxekey, colr, bgcolr, stack) {
    var ds = newDataset(vals, type, 'y2', yaxekey, label, colr, bgcolr, true);
    ds.stack = stack;
    return ds;
}

var graphe_size_days = 0;

var ctx1 = document.getElementById('portfolio_canvas').getContext('2d');
el("portfolio_canvas").height = document.body.offsetWidth > 700 ? 140 : 300;

// Filtre des labels de l'axes des x (date) (on ne garde que les premieres dates du mois)
var array_years = extractFirstDateYear(mydays);

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
    var ds= [];
    ds.push(getDatasetVals('D\u00e9pot Acc', 'line', mydata, 'vl', '<?= $sess_context->getSpectreColor(2) ?>', '<?= $sess_context->getSpectreColor(2, 0.15) ?>'));
    ds.push(getDatasetVals('Valo',           'line', mydata, 'da', '<?= $sess_context->getSpectreColor(3) ?>', '<?= $sess_context->getSpectreColor(3, 0.3) ?>'));
    ds.push(getDatasetVals2('Achat',         'bar',  mydata, 'ha', 'rgba(150, 238, 44, 1)', 'rgba(150, 238, 44, 1)', 'In'));
    ds.push(getDatasetVals2('Vente',         'bar',  mydata, 'vt', 'rgba(236, 3, 59, 1)',   'rgba(236, 3, 59, 1)',   'Out'));
    ds.push(getDatasetVals2('D\u00e9pot J',  'bar',  mydata, 'dt', 'rgba(3, 130, 236, 1)',  'rgba(3, 130, 236, 1)',  'In'));
    ds.push(getDatasetVals2('Retrait',       'bar',  mydata, 'rt', 'rgba(238, 229, 44, 1)', 'rgba(238, 229, 44, 1)', 'Out'));
    ds.push(getDatasetVals2('Dividende',     'bar',  mydata, 'dd', 'rgba(0, 236, 193, 1)',  'rgba(0, 236, 193, 1)',  'In'));
    myChart = update_graph_chart(myChart, ctx1, options_Valo_Graphe, null, ds, []);

}

// Initialisation des graphes
update_all_charts();

</script>