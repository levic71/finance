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

$data_ptf = [ 'days' => [ "0" ], 'valo' => [ "0" ], 'depot' => [ "0" ] ];

// Recuperation des infos du portefeuille
$req = "SELECT pv.*, p.name FROM portfolios p, portfolio_valo pv WHERE pv.portfolio_id=".$portfolio_id." AND p.id=pv.portfolio_id AND p.user_id=".$sess_context->getUserId();
$res = dbc::execSql($req);
$name = "";

// Bye bye si inexistant
while ($row = mysqli_fetch_assoc($res)) {

    $data = json_decode($row['data']);
    // var_dump($data);
    $data_ptf['days'][]  = $row['date'];
    $data_ptf['valo'][]  = Round($data->valo_ptf);
    $data_ptf['depot'][] = Round($data->depot);
    $name = $row['name'];

}

// Ajout de la valo TR
$data_ptf_now = calc::aggregatePortfolioById($portfolio_id);
$data_ptf['days'][]  = date("Y-m-d");
$data_ptf['valo'][]  = Round($data_ptf_now['valo_ptf']);
$data_ptf['depot'][] = Round($data_ptf_now['depot']);

?>

<h2 class="ui left floated"><i class="inverted briefcase icon"></i><?= utf8_decode($name) ?></h2>

<div id="canvas_area" class="ui container inverted segment">
    <canvas id="stock_canvas1" height="100"></canvas>
</div>

<div class="ui container inverted segment">
    <h2 class="ui inverted right aligned header">
        <button id="portfolio_back_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white reply icon"></i> Back</button>
    </h2>
</div>


<script>

var myChart1 = null;

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

getDatasetVals = function(label, vals, colr, bgcolr) {
    return newDataset(vals, 'line', 'y', label, colr, bgcolr, true);
}

var graphe_size_days = 0;

// Ref Daily data
var g1_days   = [<?= '"' . implode('","', $data_ptf["days"]) . '"' ?>];
var g1_vals   = [<?= implode(',', $data_ptf["valo"])  ?>];
var g2_vals   = [<?= implode(',', $data_ptf["depot"])  ?>];

var ctx1 = document.getElementById('stock_canvas1').getContext('2d');
el("stock_canvas1").height = document.body.offsetWidth > 700 ? 140 : 300;

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

    // Update Chart Stock
    var datasets1 = [];
    datasets1.push(getDatasetVals('Valorisation', g1_vals, '<?= $sess_context->getSpectreColor(0) ?>', '<?= $sess_context->getSpectreColor(0, 0.2) ?>'));
    datasets1.push(getDatasetVals('Dépot', g2_vals, '<?= $sess_context->getSpectreColor(1) ?>', '<?= $sess_context->getSpectreColor(1, 0.2) ?>'));
    myChart1 = update_graph_chart(myChart1, ctx1, options_Valo_Graphe, g1_days, datasets1, [{}]);

}

// Initialisation des graphes
update_all_charts();

Dom.addListener(Dom.id('portfolio_back_bt'), Dom.Event.ON_CLICK, function(event) {
    go({ action: 'home', id: 'main', url: 'portfolio_dashboard.php?portfolio_id=<?= $portfolio_id ?>', loading_area: 'main' });
});

</script>