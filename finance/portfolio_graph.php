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
$req = "SELECT pv.* FROM portfolios p, portfolio_valo pv WHERE pv.portfolio_id=".$portfolio_id." AND p.id=pv.portfolio_id AND p.user_id=".$sess_context->getUserId();
$res = dbc::execSql($req);

// Bye bye si inexistant
while ($row = mysqli_fetch_assoc($res)) {
    var_dump(json_decode($row['data']));
}

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

    getDatasetVals = function(vals) {
        return newDataset(vals, 'line', 'y1', 'Cours', '<?= $sess_context->getSpectreColor(0) ?>', '<?= $sess_context->getSpectreColor(0, 0.2) ?>', true);
    }
    getDatasetVols = function(vals, l, c) {
        return newDataset(vals, 'bar', 'y2', l, c, c, true);
    }


    var graphe_size_days = 0;

    // Ref Daily data
/*     var ref_d_days   = [<?= '"' . implode('","', array_column($data_daily["rows"], "day")) . '"' ?>];
    var ref_d_vals   = [<?= implode(',', array_column($data_daily["rows"], "adjusted_close"))  ?>];
    var ref_d_vols   = [<?= implode(',', array_column($data_daily["rows"], "volume")) ?>];
 */
    var g1_days   = null;
    var g1_vals   = null;
    var g1_vols   = null;

    var ctx1 = document.getElementById('stock_canvas1').getContext('2d');
    el("stock_canvas1").height = document.body.offsetWidth > 700 ? 300 : 300;

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

    update_all_charts = function(bt) {

        // Update Chart Stock
        var datasets1 = [];
        datasets1.push(getDatasetVals(g1_vals));
        datasets1.push(getDatasetVols(g1_vols, 'VOLUME', g1_colors));
        myChart1 = update_graph_chart(myChart1, ctx1, options_Stock_Graphe, g1_days, datasets1, [{}]);

        rmCN(bt, 'loading');
    }

    // Initialisation des graphes
    // update_all_charts('graphe_all_bt');

    var p = loadPrompt();

    Dom.addListener(Dom.id('portfolio_back_bt'), Dom.Event.ON_CLICK, function(event) {
        go({ action: 'home', id: 'main', url: 'portfolio_dashboard.php?portfolio_id=<?= $portfolio_id ?>', loading_area: 'main' });
    });

</script>