<?

require_once "sess_context.php";

session_start();

include "common.php";
include "simulator_fct.php";

$strategie_id = -1;
$f_compare_to = "SPY";

foreach(['strategie_id', 'f_compare_to'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

// Test existence stratégie
$req = "SELECT count(*) total FROM strategies WHERE id=".$strategie_id;
$res = dbc::execSql($req);
$row = mysqli_fetch_assoc($res);

if ($row['total'] != 1) {
    echo '<div class="ui container inverted segment"><h2>Strategies not found !!!</h2></div>';
    exit(0);
}

// Recupération infos strategie
$req = "SELECT * FROM strategies WHERE id=".$strategie_id;
$res = dbc::execSql($req);
$row = mysqli_fetch_assoc($res);

// Initialisation
$f_invest = $row['cycle'] * 1000;
$f_cycle_invest = $row['cycle'];
$f_capital_init = 0;
$f_date_start = "0000-00-00";
$f_date_end = date("Y-m-d");
$f_retrait = 0;
$f_montant_retrait = 500;
$f_delai_retrait = 1;

foreach(['f_retrait', 'f_montant_retrait', 'f_delai_retrait', 'strategie_id', 'f_invest', 'f_cycle_invest', 'f_date_start', 'f_date_end', 'f_capital_init'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$lst_symbols = array();
$lst_decode_symbols = json_decode($row['data'], true);

// Recherche de la date min qui contient le max de data pour tous les actifs de la strategie
foreach($lst_decode_symbols['quotes'] as $key => $val) {
    $lst_symbols[] = $key;
    $d = calc::getMaxDailyHistoryQuoteDate($key);
    if ($d > $f_date_start) $f_date_start = $d;
}

// Initialisation des parametres pour la simulation
$params = array();
$params['strategie_data']    = $row['data'];
$params['strategie_methode'] = $row['methode'];
$params['montant_retrait']   = $f_montant_retrait;
$params['delai_retrait']     = $f_delai_retrait;
$params['compare_to']   = $f_compare_to;
$params['capital_init'] = $f_capital_init;
$params['date_start']   = $f_date_start;
$params['date_end']     = $f_date_end;
$params['retrait']      = $f_retrait;
$params['invest']       = $f_invest;
$params['cycle_invest'] = $f_cycle_invest;

// Lancement de la simulation
$sim = strategieSimulator($params);
// var_dump($sim);

$infos1 = '
<table id="sim_input_card">
    <tr>
        <td>
            <div class="ui inverted fluid right labeled input">
                <div class="ui label">Capital</div>
                <input type="text" id="f_capital_init" value="'.$f_capital_init.'" size="8" placeholder="0">
                <div class="ui basic label">&euro;</div>
            </div>
        </td>
        <td rowspan="5" style="vertical-align: bottom; text-align: right">
            <button id="sim_go_bt1" class="ui icon pink float right small button"><i class="inverted play icon"></i></button>
        </td>
    </tr>
    <tr>
        <td>
            <div class="ui inverted fluid right labeled input">
                <div class="ui label">Invest. en &euro;</div>
                <input type="text" id="f_invest" value="'.$f_invest.'" placeholder="0" size="10">
                <div id="sim_par" class="ui floated right label" style="margin-left: 5px;">par</div>
                <div class="ui inverted labeled input">
                    <select id="f_cycle_invest" class="ui selection">
                        <option value="1"  '.($f_cycle_invest == 1  ? "selected=\"selected\"" : "").'>mois</option>
                        <option value="3"  '.($f_cycle_invest == 3  ? "selected=\"selected\"" : "").'>trimestre</option>
                        <option value="6"  '.($f_cycle_invest == 6  ? "selected=\"selected\"" : "").'>semestre</option>
                        <option value="12" '.($f_cycle_invest == 12 ? "selected=\"selected\"" : "").'>an</option>
                    </select>
                </div>
            </div>
        </td>
        <td class="rowspanned"></td>
    </tr>
    <tr>
        <td>
            <div class="ui right icon inverted left labeled fluid input">
                <div class="ui label">Période</div>
                <input type="text" size="10" id="f_date_start" value="'.$f_date_start.'" placeholder="0000-00-00">
                <input type="text" size="10" id="f_date_end" value="'.$f_date_end.'" placeholder="0000-00-00" style="margin-left: 10px">
                <i class="inverted black calendar alternate outline icon"></i>

            </div>
        </td>
        <td class="rowspanned"></td>
    </tr>
</table>
';

$infos2 = '
<table id="sim_input_card">
    <tr>
        <td>
            <div class="ui inverted left labeled fluid input">
                <div class="ui label">Benchmark</div>

                <div class="ui inverted labeled input">
                    <select id="f_compare_to" class="ui selection">
                        <option value="SPY"  '.($f_compare_to == "SPY"  ? "selected=\"selected\"" : "").'>SPY</option>
                        <option value="TLT"  '.($f_compare_to == "TLT"  ? "selected=\"selected\"" : "").'>TLT</option>
                        <option value="SCZ"  '.($f_compare_to == "SCZ"  ? "selected=\"selected\"" : "").'>SCZ</option>
                    </select>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="ui inverted left labeled fluid input">
                <div class="ui label">Retrait progressif</div>
                <div class="ui fitted toggle checkbox" style="padding: 8px 0px;">
                    <input id="f_retrait" type="checkbox" '.($f_retrait == 1 ? 'checked="checked"' : '').' />
                    <label></label>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="ui inverted left labeled fluid input">
                <div id="retrait_option1" style="width: 100%">
                    <div class="ui label">Montant</div>
                    <div class="ui inverted labeled input">
                        <input type="text" id="f_montant_retrait" value="'.$f_montant_retrait.'" placeholder="0" size="6">
                    </div>
                    <div class="ui label">Délai</div>
                    <div class="ui inverted labeled input">
                        <input type="text" id="f_delai_retrait" value="'.$f_delai_retrait.'" placeholder="0" size="3">
                    </div>
                    <div class="ui basic label">An(s)</div>
                </div>
            </div>
        </td>
    </tr>
</table>
';

?>

<input type="hidden" id="strategie_id" value="<?= $strategie_id ?>" />

<div class="ui container inverted segment">
    <h2>Informations</h2>
    <div class="ui stackable grid container">
          <div class="row">
          <div class="eight wide column">
                <?= uimx::genCard('sim_card2', '<i style="margin-right: 10px;" class="inverted '.($row['methode'] == 2 ? 'cubes' : 'diamond').' blurely line icon"></i>'.$row['title'], '', $infos1); ?>
            </div>
            <div class="eight wide column">
                <?= uimx::genCard('sim_card2', implode(', ', $lst_symbols), '', $infos2); ?>
            </div>

            <div class="center aligned sixteen wide column" id="sim_card_bt">
                <button id="sim_go_bt2" class="ui pink float right button">Go</button>
            </div>

<?

$final_info = '
    <table id="sim_final_info">
    <tr>
        <th>Portfolio</th>
        <th>Valorisation</th>
        <th>Capital investit</th>
        <th>Performance</th>
        <th>Max DD</th>
        <th>Retrait</th>
        <th>Duree</th>
    </tr>
    <tr>
        <td>'.$row['title'].'</td>
        <td>'.sprintf("%.2f", $sim['valo_pf']).' &euro;</td>
        <td>'.sprintf("%.2f", $sim['sum_invest']).' &euro;</td>
        <td class="'.($sim['perf_pf'] >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $sim['perf_pf']).' %</td>
        <td class="'.($sim['maxdd'] >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $sim['maxdd']).' %</td>
        <td>'.sprintf("%.2f", $sim['retrait_sum']).' &euro;</td>
        <td>'.count(tools::getMonth($f_date_start, $f_date_end)).' mois</td>
    </tr>
    <tr>
        <td>Benchmark</td>
        <td>'.sprintf("%.2f", $sim['valo_pf_RC']).' &euro;</td>
        <td>'.sprintf("%.2f", $sim['sum_invest']).' &euro;</td>
        <td class="'.($sim['perf_pf_RC'] >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $sim['perf_pf_RC']).' %</td>
        <td class="'.($sim['maxdd_RC'] >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $sim['maxdd_RC']).' %</td>
        <td>'.sprintf("%.2f", $sim['retrait_sum']).' &euro;</td>
        <td>'.count(tools::getMonth($f_date_start, $f_date_end)).' mois</td>
    </tr>
    </table>
';

$final_info2 = '
    <table class="ui selectable inverted striped single line very compact unstackable table" id="sim_final_info2">
    <tr>
        <td></td>
        <td>'.$row['title'].'</td>
        <td>Benchmark</td>
    </tr>
    <tr>
        <td>Valorisation</td>
        <td>'.sprintf("%.2f", $sim['valo_pf']).' &euro;</td>
        <td>'.sprintf("%.2f", $sim['valo_pf_RC']).' &euro;</td>
    </tr>
    <tr>
        <td>Capital investit</td>
        <td>'.sprintf("%.2f", $sim['sum_invest']).' &euro;</td>
        <td>'.sprintf("%.2f", $sim['sum_invest']).' &euro;</td>
    </tr>
    <tr>
        <td>Performance</td>
        <td class="'.($sim['perf_pf'] >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $sim['perf_pf']).' %</td>
        <td class="'.($sim['perf_pf_RC'] >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $sim['perf_pf_RC']).' %</td>
    </tr>
    <tr>
        <td>Max DD</td>
        <td class="'.($sim['maxdd'] >= 0    ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $sim['maxdd']).' %</td>
        <td class="'.($sim['maxdd_RC'] >= 0 ? "aaf-positive" : "aaf-negative").'">'.sprintf("%.2f", $sim['maxdd_RC']).' %</td>
    </tr>
    <tr>
        <td>Retrait</td>
        <td>'.sprintf("%.2f", $sim['retrait_sum']).' &euro;</td>
        <td>'.sprintf("%.2f", $sim['retrait_sum']).' &euro;</td>
    </tr>
    <tr>
        <td>Duree</td>
        <td>'.count(tools::getMonth($f_date_start, $f_date_end)).' mois</td>
        <td>'.count(tools::getMonth($f_date_start, $f_date_end)).' mois</td>
    </tr>
    </table>
';

?>
            <div class="sixteen wide column" style="margin-top: 15px;" id="synthese_bloc1">
                <?= uimx::genCard('sim_card1', 'Synthèse', '', $final_info); ?>
            </div>
            <div class="sixteen wide column" style="margin-top: 15px;" id="synthese_bloc2">
                <?= uimx::genCard('sim_card12', 'Synthèse', '', $final_info2); ?>
            </div>

        </div>
    </div>
</div>


<!-- GRAPHE 1 -->

<div class="ui container inverted segment">
    <h2>Evolution du portefeuille</h2>
    <canvas id="sim_canvas1" height="100"></canvas>
</div>
<script>
// Our labels along the x-axis
var dates    = [<?= '"'.implode('","', $sim['tab_date']).'"' ?>];
// For drawing the lines
var valos    = [<?= implode(',', $sim['tab_valo'])    ?>];
var valos_RC = [<?= implode(',', $sim['tab_valo_RC']) ?>];
var invts    = [<?= implode(',', $sim['tab_invt'])    ?>];

var ctx = document.getElementById('sim_canvas1').getContext('2d');
el("sim_canvas1").height = document.body.offsetWidth > 700 ? 100 : 300;

var myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: dates,
        datasets: [
            { 
            data: invts,
            label: "Investissement",
            borderColor: "rgba(97, 194, 97, 0.75)",
            backgroundColor: "rgba(97, 194, 97, 0.3)",
            cubicInterpolationMode: 'monotone',
            pointRadius: 1,
            tension: 0.4,
            borderWidth: 0.5,
            fill: false
        },
        { 
            data: valos_RC,
            label: "<?= $sim['sym_RC'] ?>",
            borderColor: "rgba(23, 109, 181, 0.75)",
            backgroundColor: "rgba(23, 109, 181, 0.3)",
            cubicInterpolationMode: 'monotone',
            pointRadius: 1,
            tension: 0.4,
            borderWidth: 0.5,
            fill: false
        },
        { 
            data: valos,
            label: "<?= $row['title'] ?>",
            borderColor: "rgba(238, 130, 6, 0.75)",
            backgroundColor: "rgba(238, 130, 6, 0.05)",
            cubicInterpolationMode: 'monotone',
            pointRadius: 1,
            tension: 0.4,
            borderWidth: 0.5,
            fill: true
        }
    ]},
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:true
                }
            }]
        }
    }
});
</script>


<!-- GRAPHE 2 : EVOLUTION DM -->

<? if ($row['methode'] == 1) { ?>

<div class="ui container inverted segment">
    <h2>Evolution DM</h2>
    <canvas id="sim_canvas2" height="100"></canvas>
</div>

<script>
// For drawing the lines
<?
    $x = 0; 
    foreach($lst_symbols as $key => $val) {
        echo "var dataset_".$x." = [ ".(isset($sim['tab_perf'][$val]) ? implode(',', $sim['tab_perf'][$val]) : '')." ];";
        $x++;
    }
?>

var ctx2 = document.getElementById('sim_canvas2').getContext('2d');
el("sim_canvas2").height = document.body.offsetWidth > 700 ? 100 : 300;

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
        ctx.moveTo(left, h);
        ctx.lineTo(right, h);
        ctx.stroke();
        ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
        ctx.restore();
    }
};

var data2 = {
    labels: dates,
    datasets: [
<?
    $x = 0; 
    foreach($lst_symbols as $key => $val) {
        echo "{ data: dataset_".$x.", label: \"".$val."\", borderColor: \"".$sess_context->getSpectreColor($x)."\", cubicInterpolationMode: 'monotone', pointRadius: 1, tension: 0.4, borderWidth: 0.5, fill: false },";
        $x++;
    }
?>
    ]
}

var options2 = {
    responsive: true,
    maintainAspectRatio: true,
    scales: {
        xAxes: [{
            gridLines: {
                color: "red"
            }
        }],
        yAxes: [{
            gridLines: {
                color: "red"
            }
        }]
    }
};

var myChart2 = new Chart(ctx2, { type: 'line', data: data2, options: options2, plugins: [horizontalLines] } );

</script>

<? } ?>


<!-- TABLEAU DETAIL -->

<div class="ui container inverted segment">
    <h2>Détail</h2>
    <table id="lst_sim" class="ui selectable inverted single line very compact unstackable table lst_sim_<?= $row['methode'] ?>">
        <thead>
            <tr>
                <th>Date</th>
                <th>Cash</th>

<? if ($row['methode'] == 1)
    echo '<th>Vente</th><th>Nb</th><th>PU</th><th>Perf</th><th>Achat</th><th>Nb</th><th>PU</th>';
else
    echo '<th>Actifs en portefeuille</th><th></th><th></th><th></th><th></th><th></th><th></th>';
?>
                <th>Valorisation</th>
                <th>Perf</th>
            </tr>
        </thead>
        <tbody>
<?
foreach($sim['tab_detail'] as $key => $val) {
    echo "<tr class=\"".($val[$row['methode'] == 1 ? "td_perf_vendu_val" : "td_perf_glob_val"] >= 0 ? "aaf-positive" : "aaf-negative")."\" onclick=\"".$val['tr_onclick']."\">";
    if ($row['methode'] == 1) {
        foreach(['td_day', 'td_cash', 'td_symbol_vendu', 'td_nb_vendu', 'td_pu_vendu', 'td_perf_vendu', 'td_symbol_achat', 'td_nb_achat', 'td_pu_achat', 'td_valo_pf', 'td_perf_glob'] as $ind)
           echo "<td ".($ind == 'td_perf_vendu' || $ind == 'td_perf_glob' ? "class=\"".($val[$ind."_val"] >= 0 ? "aaf-positive" : "aaf-negative")."\"" : "" ).">".$val[$ind]."</td>";
    } else {
        foreach(['td_day', 'td_cash', 'td_ordres', 'td_valo_pf', 'td_perf_glob'] as $ind)
            echo "<td ".($ind == 'td_ordres' ? "colspan=\"7\"" : "" )." ".($ind == 'td_perf_glob' ? "class=\"".($val[$ind."_val"] >= 0 ? "aaf-positive" : "aaf-negative")."\"" : "" ).">".$val[$ind]."</td>";
    }
    echo "</tr>";
}
?>
        </tbody>
    </table>
</div>

<div class="ui container inverted segment">
    <h2>Ordres boursiers</h2>
    <table id="lst_ordres" class="ui striped selectable inverted single line very compact unstackable table">
        <thead><tr><th>Date</th><th><div>Action</div></th><th>Symbole</th><th>Nb</th><th>Prix</th></tr></thead>
        <tbody>
<?
foreach($sim['ordres'] as $key => $val) {
    $o = json_decode($val);
    echo "<tr class=\"".$o->{"action"}."\"><td>".$o->{"date"}."</td><td><div>".$o->{"action"}."</div></td><td>".$o->{"symbol"}."</td><td>".$o->{"quantity"}."</td><td>".sprintf("%.2f", $o->{"price"}).$o->{"currency"}."</td></tr>";
}
?>
        </tbody>
    </table>
</div>

<script>

    launcher = function() {
		params = attrs(['f_delai_retrait', 'f_montant_retrait', 'strategie_id', 'f_capital_init', 'f_invest', 'f_cycle_invest', 'f_date_start', 'f_date_end', 'f_compare_to' ]);
        go({ action: 'sim', id: 'main', url: 'simulator.php?'+params+'&f_retrait='+(valof('f_retrait') == 0 ? 0 : 1), loading_area: 'sim_go_bt' });
    }
    
    Dom.addListener(Dom.id('sim_go_bt1'), Dom.Event.ON_CLICK, function(event) { launcher(); });
    Dom.addListener(Dom.id('sim_go_bt2'), Dom.Event.ON_CLICK, function(event) { launcher(); });
    Dom.addListener(Dom.id('f_retrait'),  Dom.Event.ON_CHANGE, function(event) { toogle('retrait_option1'); });

    hide('retrait_option1');
    <? if ($f_retrait == 1) { ?>
    toogle('retrait_option1');
    <? } ?>

    const datepicker1 = new TheDatepicker.Datepicker(el('f_date_start'));
    datepicker1.options.setInputFormat("Y-m-d")
    datepicker1.render();
    const datepicker2 = new TheDatepicker.Datepicker(el('f_date_end'));
    datepicker2.options.setInputFormat("Y-m-d")
    datepicker2.render();

</script>