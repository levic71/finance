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
$req = "SELECT * FROM portfolios WHERE id=".$portfolio_id." AND user_id=".$sess_context->getUserId();
$res = dbc::execSql($req);

// Bye bye si inexistant
if (!$row = mysqli_fetch_assoc($res)) exit(0);

// Recuperation de tous les actifs
$quotes = calc::getIndicatorsLastQuote();

// Calcul synthese portefeuille
$portfolio_data = calc::aggregatePortfolio($portfolio_id, $quotes);

// On recupere les eventuelles saisies de cotation manuelles
$save_quotes = array();
$t = explode(',', $portfolio_data['infos']['quotes']);
if ($t[0] != '') {
	foreach($t as $key => $val) {
		$x = explode('|', $val);
		$save_quotes[$x[0]] = $x[1];
	}
}

// On récupère les infos du portefeuille + les positions et les ordres
$my_portfolio  = $portfolio_data['infos'];
$lst_positions = $portfolio_data['positions'];
$lst_orders    = $portfolio_data['orders'];

$date_start = date('Y-m-01', strtotime(date('Y-m-d')  . ' -'.$portfolio_data['interval_year'].' year -'.$portfolio_data['interval_month'].' month'));

function getTimeSeriesData($table_name, $period, $symbol)
{

    $ret = array('rows' => array(), 'colrs' => array());

    $file_cache = 'cache/TMP_TIMESERIES_' . $symbol . '_' . $period . '.json';

    if (cacheData::refreshCache($file_cache, 600)) { // Cache de 5 min

        $req = "SELECT * FROM " . $table_name . " dtsa, indicators indic WHERE dtsa.symbol=indic.symbol AND dtsa.day=indic.day AND indic.period='" . $period . "' AND dtsa.symbol='" . $symbol . "' ORDER BY dtsa.day ASC";
        $res = dbc::execSql($req);
        while ($row = mysqli_fetch_assoc($res)) {
            $row['adjusted_close'] = sprintf("%.2f", $row['adjusted_close']);
            $ret['rows'][] = $row;
        }

        cacheData::writeCacheData($file_cache, $ret);
    } else {
        $ret = cacheData::readCacheData($file_cache);
    }

    return $ret;
}

// Recuperation de tous les indicateurs DAILY de l'actif
// $data_daily = getTimeSeriesData("daily_time_series_adjusted", "DAILY", $symbol);

// On ajoute la cotation du jour
/* $data_daily_today = array("symbol" => $row["symbol"], "day" => $row["day"], "open" => $row["open"], "high" => $row["high"], "low" => $row["low"], "close" => $row["price"], "adjusted_close" => $row["price"], "volume" => $row["volume"], "period" => "DAILY", "DM" => $data['DM'], "MM7" => $data['MM7'], "MM20" => $data['MM20'], "MM50" => $data["MM50"], "MM200" => $data['MM200'], "RSI14" => $data["RSI14"]);
$data_daily["rows"][]  = $data_daily_today;
$data_daily["colrs"][] = 1;
 */

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
        go({ action: 'home', id: 'main', url: 'portfolio_graph.php?portfolio_id=<?= $portfolio_id ?>', loading_area: 'main' });
    });

</script>