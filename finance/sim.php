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

} else {


    // On recree la valeur data
    $tab_sym = array();
    $data = '{ "quotes" : { '.implode(', ', $tab_sym).' } }';

    // Initialisation des donnees de la strategie
    $row = [ 'methode' => 1, 'title' => 'toto', 'cycle' => 1, 'data' => $data ];
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
$lst_decode_symbols = $sim['lst_decode_symbols'];
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

<div class="ui inverted grid container segment">
    <? if ($option_sim != "backtest") { ?>
        <div class="sixteen wide column">
            <h2><i class="inverted <?= uimx::$invest_methode_icon[$row['methode']] ?> icon"></i><?= utf8_decode($row['title']) ?></h2>
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

</div>


<script>

launcher = function(option) {

    params = attrs(['f_delai_retrait', 'f_montant_retrait', 'strategie_id', 'f_capital_init', 'f_invest', 'f_cycle_invest', 'f_date_start', 'f_date_end', 'f_compare_to' ]);
    setRequestHeader("Access-Control-Allow-Origin", "*");
    go({ action: 'sim', id: option == 'backtest' ? 'simulation_area2' : 'main', url: '/sim.php?option_sim='+option+params+'&f_retrait='+(valof('f_retrait') == 0 ? 0 : 1), no_chg_cn: option == 'backtest' ? 1 : 0 });
}

Dom.addListener(Dom.id('sim_go_bt1'), Dom.Event.ON_CLICK, function(event) { launcher('<?= $option_sim ?>'); });
Dom.addListener(Dom.id('sim_go_bt2'), Dom.Event.ON_CLICK, function(event) { launcher('<?= $option_sim ?>'); });

</script>