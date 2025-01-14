<?

require_once "sess_context.php";

session_start();

include "common.php";
include "simulator_fct.php";

$option_sim = "simulator";
$strategie_id = -1;
$f_compare_to = "SPY";

foreach(['option_sim', 'strategie_id', 'f_compare_to'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if ($option_sim != "backtest") {

    // Test existence strat�gie
    $req = "SELECT count(*) total FROM strategies WHERE id=".$strategie_id;
    $res = dbc::execSql($req);
    $row = mysqli_fetch_assoc($res);

    if ($row['total'] != 1) {
        echo '<div class="ui container inverted segment"><h2>Strategies not found !!!</h2></div>';
        exit(0);
    }

    // Recup�ration infos strategie
    $req = "SELECT * FROM strategies WHERE id=".$strategie_id;
    $res = dbc::execSql($req);
    $row = mysqli_fetch_assoc($res);

} else {

    // On recupere les donnees du formulaire
    foreach(['criteres', 'f_common', 'strategie_id', 'f_name', 'f_methode', 'f_cycle', 'f_nb_symbol_max', 'f_symbol_choice_1', 'f_symbol_choice_pct_1', 'f_symbol_choice_2', 'f_symbol_choice_pct_2', 'f_symbol_choice_3', 'f_symbol_choice_pct_3', 'f_symbol_choice_4', 'f_symbol_choice_pct_4', 'f_symbol_choice_5', 'f_symbol_choice_pct_5', 'f_symbol_choice_6', 'f_symbol_choice_pct_6', 'f_symbol_choice_7', 'f_symbol_choice_pct_7'] as $key)
        $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

    // On recree la valeur data
    $tab_sym = array();
    foreach(range(1, $f_nb_symbol_max) as $number) {
        if ($f_methode != 3) {
            $v1 = "f_symbol_choice_".$number;
            $v2 = "f_symbol_choice_pct_".$number;
            $tab_sym[] = '"'.$$v1.'" : '.($f_methode == 1 ? 1 : $$v2);
        }
    }
    $data = '{ "quotes" : { '.(count($tab_sym) == 0 ? "" : implode(', ', $tab_sym)).' }, "criteres" : "'.$criteres.'" }';

    // Initialisation des donnees de la strategie
    $row = [ 'methode' => $f_methode, 'title' => $f_name, 'cycle' => $f_cycle, 'data' => $data ];
}

// Initialisation
$f_invest          = $row['cycle'] * 1000;
$f_cycle_invest    = $row['cycle'];
$f_capital_init    = 0;
$f_date_start      = date("2000-01-01");
$f_date_end        = date("Y-m-d");
$f_retrait         = 0;
$f_montant_retrait = 500;
$f_delai_retrait   = 1;

foreach(['f_retrait', 'f_montant_retrait', 'f_delai_retrait', 'strategie_id', 'f_invest', 'f_cycle_invest', 'f_date_start', 'f_date_end', 'f_capital_init'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

// Initialisation des parametres pour la simulation
$params = array();
$params['strategie_data']    = $row['data'];
$params['strategie_methode'] = $row['methode'];
$params['montant_retrait']   = $f_montant_retrait;
$params['delai_retrait']     = $f_delai_retrait;
$params['compare_to']        = $f_compare_to;
$params['capital_init']      = $f_capital_init;
$params['date_start']        = $f_date_start;
$params['date_end']          = $f_date_end;
$params['retrait']           = $f_retrait;
$params['invest']            = $f_invest;
$params['cycle_invest']      = $f_cycle_invest;

// Lancement de la simulation
$sim = strategieSimulator($params);

// Si dates recalculees
$f_date_start = $sim['date_start'];
$f_date_end   = $sim['date_end'];

// On recupere des infos sur les actifs
$data_decode = $sim['data_decode'];
$lst_symbols = $sim['lst_symbols'];

// Donnees d'affichage
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
                <div class="ui label">P�riode</div>
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
                    <div class="ui label">D�lai</div>
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

$final_info = '
    <table class="ui selectable inverted striped single line very compact unstackable table" id="sim_final_info">
    <tr>
        <th>Portfolio</th>
        <th>Valorisation</th>
        <th>Capital investi</th>
        <th>Performance</th>
        <th>Max DD</th>
        <th>Retrait</th>
        <th>Duree</th>
    </tr>
    <tr>
        <td>'.tools::UTF8_encoding($row['title']).'</td>
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

<input type="hidden" id="strategie_id" value="<?= $strategie_id ?>" />

<div class="ui inverted grid container segment">
    <? if ($option_sim != "backtest") { ?>
        <div class="sixteen wide column">
            <h2><i class="inverted <?= uimx::$invest_methode_icon[$row['methode']] ?> icon"></i><?= ($row['title']) ?></h2>
        </div>
    <? } ?>

    <div class="ui eight wide column inverted">
        <?= uimx::genCard('sim_card2', implode(', ', $lst_symbols), '', $infos1); ?>
    </div>

    <div class="ui eight wide column inverted">
        <?= uimx::genCard('sim_card2', '&nbsp;', '', $infos2); ?>
    </div>

    <div class="ui center aligned sixteen wide column inverted" id="sim_card_bt">
        <button id="sim_go_bt2" class="ui pink float right button">Go</button>
    </div>

    <div class="ui sixteen wide column inverted" id="synthese_bloc1">
        <?= uimx::genCard('sim_card1', '', '', $final_info); ?>
    </div>

    <div class="ui sixteen wide column inverted" id="synthese_bloc2">
        <?= uimx::genCard('sim_card12', '', '', $final_info2); ?>
    </div>

    <div class="ui sixteen wide column inverted">
        <h2><i class="inverted money icon"></i>Valorisation du portefeuille</h2>
        <canvas id="sim_canvas1" height="100"></canvas>
    </div>

    <? if ($row['methode'] == 1  || $row['methode'] == 3) { ?>
    <div class="ui sixteen wide column inverted">
        <h2><i class="inverted line graph icon"></i>Evolution DM</h2>
        <canvas id="sim_canvas2" height="100"></canvas>
    </div>
    <? } ?>

    <div class="ui sixteen wide column inverted">
        <h2><i class="inverted grid layout icon"></i>Composition portefeuille</h2>
        <table id="lst_sim" class="ui striped selectable inverted very compact unstackable table lst_sim_<?= $row['methode'] ?>">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Cash</th>
                    <? if ($row['methode'] == 1  || $row['methode'] == 3)
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
        echo "<tr class=\"".($val[$row['methode'] == 1 || $row['methode'] == 3 ? "td_perf_vendu_val" : "td_perf_glob_val"] >= 0 ? "aaf-positive" : "aaf-negative")."\" onclick=\"".$val['tr_onclick']."\">";
        if ($row['methode'] == 1 || $row['methode'] == 3) {
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
        <div id="lst_sim_nav"></div>
    </div>

    <div class="ui sixteen wide column inverted">
        <h2><i class="inverted exchange icon"></i>Ordres boursiers</h2>
        <table id="lst_ordres" class="ui striped selectable inverted single line very compact unstackable table">
            <thead><tr><th style="width: 50px;"></th><th>Date</th><th><div>Action</div></th><th>Symbole</th><th>Nb</th><th>Prix</th></tr></thead>
            <tbody>
                <?
                foreach($sim['ordres'] as $key => $val) {
                    $o = json_decode($val);
                    echo "<tr class=\"".$o->{"action"}."\"><td><i class=\"inverted long arrow alternate ".($o->{"action"} == 'Achat' ? "right green" : "left orange")." icon\"></i></td><td>".$o->{"date"}."</td><td><div>".$o->{"action"}."</div></td><td>".$o->{"symbol"}."</td><td>".$o->{"quantity"}."</td><td>".sprintf("%.2f", $o->{"price"}).$o->{"currency"}."</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <div id="lst_ordres_nav"></div>
    </div>

</div>


<script>

// Our labels along the x-axis
var dates    = [<?= '"'.implode('","', $sim['tab_date']).'"' ?>];

// For drawing the lines
var valos    = [<?= implode(',', $sim['tab_valo'])    ?>];
var valos_RC = [<?= implode(',', $sim['tab_valo_RC']) ?>];
var invts    = [<?= implode(',', $sim['tab_invt'])    ?>];

getDataset = function(mydata, mylabel, bc, bg, myfill) {

    var ret = {
        type: 'line',
        data: mydata,
        label: mylabel,
        borderColor: bc,
        backgroundColor: bg,
        borderWidth: 0,
        cubicInterpolationMode: 'monotone',
        pointRadius: 1,
        tension: 0.4,
        borderWidth: 0.5,
        fill: myfill
    };

    return ret;
}

var mydatasets = [];
mydatasets.push(getDataset(invts,    'Investissement',        'rgba(97, 194, 97, 0.75)',  'rgba(97, 194, 97, 1)',    false));
mydatasets.push(getDataset(valos_RC, '<?= $sim['sym_RC'] ?>', 'rgba(23, 109, 181, 0.75)', 'rgba(23, 109, 181, 1)',   false));
mydatasets.push(getDataset(valos,    '<?= tools::UTF8_encoding($row['title']) ?>',  'rgba(238, 130, 6, 0.75)',  'rgba(238, 130, 6, 0.05)', true));

var ctx = document.getElementById('sim_canvas1').getContext('2d');
el("sim_canvas1").height = document.body.offsetWidth > 700 ? 100 : 300;

var myChart = new Chart(ctx, { type: 'line', data: { labels: dates, datasets: mydatasets }, options: options_simulator_graphe });


<? if ($row['methode'] == 1  || $row['methode'] == 3) { ?>

<?
    $x = 0; 
    foreach($lst_symbols as $key => $val) {
        echo "var dataset_".$x." = [ ".(isset($sim['tab_perf'][$val]) ? implode(',', $sim['tab_perf'][$val]) : '')." ];";
        $x++;
    }
?>

var ctx2 = document.getElementById('sim_canvas2').getContext('2d');
el("sim_canvas2").height = document.body.offsetWidth > 700 ? 100 : 300;

var data2 = {
    labels: dates,
    datasets: [
<?
    $x = 0; 
    foreach($lst_symbols as $key => $val) {
        echo "{ data: dataset_".$x.", label: \"".$val."\", borderColor: \"".$sess_context->getSpectreColor($x, 0.9)."\", backgroundColor: \"".$sess_context->getSpectreColor($x, 0.75)."\", cubicInterpolationMode: 'monotone', pointRadius: 1, tension: 0.4, borderWidth: 0.5, fill: false },";
        $x++;
    }
?>
    ]
}

// Changement valeur dynamique option graphe
options_DM_Graphe.plugins.legend.display = true;

// Creation graphe
var myChart2 = new Chart(ctx2, { type: 'line', data: data2, options: options_DM_Graphe, plugins: [horizontalLines_DM_Graphe] } );

<? } ?>

launcher = function(option) {
    params = attrs(['f_delai_retrait', 'f_montant_retrait', 'strategie_id', 'f_capital_init', 'f_invest', 'f_cycle_invest', 'f_date_start', 'f_date_end', 'f_compare_to' ]);
    go({ action: 'sim', id: option == 'backtest' ? 'simulation_area' : 'main', url: 'simulator.php?option_sim='+option+params+'&f_retrait='+(valof('f_retrait') == 0 ? 0 : 1), no_chg_cn: option == 'backtest' ? 1 : 0 });
}

Dom.addListener(Dom.id('sim_go_bt1'), Dom.Event.ON_CLICK, function(event) { launcher('<?= $option_sim ?>'); });
Dom.addListener(Dom.id('sim_go_bt2'), Dom.Event.ON_CLICK, function(event) { launcher('<?= $option_sim ?>'); });
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

paginator({
    table: document.getElementById("lst_sim"),
    box: document.getElementById("lst_sim_nav")
});

paginator({
    table: document.getElementById("lst_ordres"),
    box: document.getElementById("lst_ordres_nav")
});

<? if ($option_sim == "backtest") { ?>
rmCN('strategie_backtest_bt', 'loading');
<? } ?>

</script>