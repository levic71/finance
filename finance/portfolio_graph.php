<?

require_once "sess_context.php";

session_start();

include "common.php";

$portfolio_id = 0;

foreach (['portfolio_id'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if (!$sess_context->isUserConnected()) uimx::redirectLoginPage('portfolio');

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

$oneyearbefore = date('Y-m-d', strtotime('-1 year'));

$nb_orders[0] = 0; // All
$nb_orders[1] = 0; // 1 an glissant
$sum_commissions[0] = 0;
$sum_commissions[1] = 0;
$tab_orders = [];
foreach($lst_orders as $key => $val) {

    if ($val['confirme'] == 0) continue;

    $tab_orders['days'][$val['date']] = $val['date'];
    $local_year = substr($val['date'], 0, 4);

    // Somme des commissions
    $sum_commissions[0] += $val['commission'];
    $sum_commissions[$local_year] = (isset($sum_commissions[$local_year]) ? $sum_commissions[$local_year] : 0) + $val['commission'];
    if ($val['date'] > $oneyearbefore) $sum_commissions[1] += $val['commission'];

    // Somme du nb d'ordres
    if (!isset($nb_orders[$local_year])) $nb_orders[$local_year] = 0;
    if ($val['action'] == 1 || $val['action'] == -1) {
        $nb_orders[0]++;
        $nb_orders[$local_year]++;
        if ($val['date'] > $oneyearbefore) $nb_orders[1]++;
    }

    // Valorisation de la transaction
    $pricing = $val['price'] * $val['quantity'] * $val['taux_change'];

    // Cumul par type de transaction
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

ksort($data_ptf);
$first_key = array_key_first($data_ptf); // First element's key
$year_creation = substr($first_key, 0, 4);
if (!isset($data_ptf[$first_key]['valo'])) $data_ptf[$first_key]['valo'] = 0;
if (!isset($data_ptf[$first_key]['depot_acc'])) $data_ptf[$first_key]['depot_acc'] = 0;
// var_dump(current($data_ptf)); exit(0);

?>

<h2 class="ui left floated">
    <i class="inverted briefcase icon"></i>
    <?= $name ?>
    <select id="year_select_bt" style="float: right;">
        <option value="0" data-nb-orders="<?= $nb_orders[0] ?>" data-comm="<?= $sum_commissions[0] ?>">All</option>
        <option value="1" data-nb-orders="<?= $nb_orders[1] ?>" data-comm="<?= $sum_commissions[1] ?>">1 Year</option>
        <?
            for($i=date('Y'); $i >= max($year_creation, date('Y') - 9) ; $i--) echo '<option value="'.$i.'" '.($i == $year ? 'selected="selected"' : '').' data-nb-orders="'.(isset($nb_orders[$i]) ? $nb_orders[$i] : 0).'" data-comm="'.(isset($sum_commissions[$i]) ? $sum_commissions[$i] : 0).'">'.$i.'</option>';
        ?>
    </select>
    <?= "<small style=\"float: right; margin-right: 20px; font-size: 12px; color: black;\">Nb ordres=<span id=\"nb_orders\">".$nb_orders[0]."</span>/Frais=<span id=\"comm\">".$sum_commissions[0]."</span>&euro;</small>" ?>
</h2>

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
        echo sprintf("{ d: '%s', da: %.2f, vl: %.2f, ha: %.2f, vt: %.2f, dj: %.2f, rt: %.2f, dd: %.2f }%s",
            $key,
            isset($val["depot_acc"]) ? $val["depot_acc"] : 0,
            isset($val["valo"])      ? $val["valo"]      : 0,
            isset($val["achat"])     ? $val["achat"]     : 0,
            isset($val["vente"])     ? $val["vente"]     : 0,
            isset($val["depot_j"])   ? $val["depot_j"]   : 0,
            isset($val["retrait"])   ? $val["retrait"]   : 0,
            isset($val["dividende"]) ? $val["dividende"] : 0,
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

update_all_charts = function(year) {

    var local_data = mydata;
    var oneyearbefore = new Date('<?= $oneyearbefore ?>').getTime();

    // Filtre sur critere annee
    if (year > 0) {

        local_data = [];
        mydata.forEach(function(item) {
            let y = item.d.split('-')[0];
            let d = new Date(item.d).getTime();
            if (year != 1 && y == year) local_data.push(item);
            if (year == 1 && d > oneyearbefore) local_data.push(item);
        });

    }

    sum_commissions = el('year_select_bt').options[el('year_select_bt').selectedIndex].getAttribute('data-comm');
    nb_orders       = el('year_select_bt').options[el('year_select_bt').selectedIndex].getAttribute('data-nb-orders');

    el('comm').textContent = sum_commissions;
    el('nb_orders').textContent = nb_orders;

    // Update Chart Portfolio
    var ds= [];
    ds.push(getDatasetVals('D\u00e9pot', 'line', local_data, 'da', '<?= $sess_context->getSpectreColor(3) ?>', '<?= $sess_context->getSpectreColor(3, 0.3) ?>'));
    ds.push(getDatasetVals('Valo',       'line', local_data, 'vl', '<?= $sess_context->getSpectreColor(2) ?>', '<?= $sess_context->getSpectreColor(2, 0.15) ?>'));
//    ds.push(getDatasetVals2('Achat',         'bar',  local_data, 'ha', 'rgba(150, 238, 44, 1)', 'rgba(150, 238, 44, 1)', 'In'));
//    ds.push(getDatasetVals2('Vente',         'bar',  local_data, 'vt', 'rgba(236, 3, 59, 1)',   'rgba(236, 3, 59, 1)',   'Out'));
//    ds.push(getDatasetVals2('D\u00e9pot J',  'bar',  local_data, 'dt', 'rgba(3, 130, 236, 1)',  'rgba(3, 130, 236, 1)',  'In'));
//    ds.push(getDatasetVals2('Retrait',       'bar',  local_data, 'rt', 'rgba(238, 229, 44, 1)', 'rgba(238, 229, 44, 1)', 'Out'));
//    ds.push(getDatasetVals2('Dividende',     'bar',  local_data, 'dd', 'rgba(0, 236, 193, 1)',  'rgba(0, 236, 193, 1)',  'In'));

    options_Valo_Graphe.scales.y.type = 'logarithmic';
    myChart = update_graph_chart(myChart, ctx1, options_Valo_Graphe, null, ds, []);

}

// Initialisation des graphes
update_all_charts(0);

Dom.addListener(Dom.id('year_select_bt'), Dom.Event.ON_CHANGE, function(event) {
    update_all_charts(valof('year_select_bt'));
});


</script>