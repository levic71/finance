<?

require_once "sess_context.php";

session_start();

include "common.php";

$symbol = "";
$ptf_id = "";
$debug  = 0;

$default_button_choice = ['rsi' => 0, 'volume' => 1, 'alarm' => 1, 'av' => 1, 'reg' => 0, 'scale' => 0];

$strat_ptf = isset($_COOKIE["strat_ptf"]) ? $_COOKIE["strat_ptf"] :  1;

foreach (['symbol', 'edit', 'ptf_id', 'debug'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

// Affichage par defaut des MMX
$mmx = 8;

// Conseillers to tags
$tags_conseillers = [];
foreach (uimx::$conseillers as $key => $val) $tags_conseillers[] = ["tag" => $val, "desc" => ""];

// Couleurs boutons
$bt_interval_colr = "green"; // D/W/M
$bt_period_colr   = "blue";  // ALL/3Y/1Y/1T
$bt_mmx_colr      = "purple";
$bt_volume_colr   = "yellow";
$bt_alarm_colr    = "pink";
$bt_av_colr       = "olive";
$bt_filter_colr   = "teal";
$bt_grey_colr     = "grey";

$readonly = $sess_context->isSuperAdmin() && $edit == 1 ? false : true;

$db = dbc::connect();

// Recuperation des infos de l'actif selectionne
$req = "SELECT *, s.symbol symbol FROM stocks s LEFT JOIN quotes q ON s.symbol = q.symbol LEFT JOIN trend_following t ON s.symbol = t.symbol AND t.user_id=" . $sess_context->getUserId() . " WHERE s.symbol='" . $symbol . "'";
$res = dbc::execSql($req);

// Bye bye si inexistant
if (!$row_stock = mysqli_fetch_assoc($res)) exit(0);

// Calcul synthese de tous les porteuilles de l'utilisateur (on recupere les PRU globaux)
$aggregate_ptf = $sess_context->isUserConnected() ? calc::getAggregatePortfoliosByUser($sess_context->getUserId()) : array();

// R�cup�ration des devises
$devises = cacheData::readCacheData("cache/CACHE_GS_DEVISES.json");

// Recuperation de tous les actifs
$quotes = calc::getIndicatorsLastQuote();

// Gestion des tags
$tags = array_flip(explode("|", tools::UTF8_encoding($row_stock['tags'])));

// Computing portfolio/quotes
$sc = new StockComputing($quotes, $aggregate_ptf, $devises);
$sc->setStratPtf($strat_ptf);

$ptf_nb_positions = $sc->getCountPositionsInPtf();

// Computing specific quote
$qc = new QuoteComputing($sc, $symbol);
$qc->refreshQuote($row_stock);

$data           = $qc->getQuote();
$name           = $qc->getName();
$currency       = $qc->getCurrency();
$position_pru   = $qc->getPru();
$curr_graphe    = $qc->isTypeIndice() ? "" : uimx::getGraphCurrencySign($currency);
$links          = json_decode($qc->getQuoteAttr('links'), true);
$link1          = isset($links['link1']) ? $links['link1'] : "";
$link2          = isset($links['link2']) ? $links['link2'] : "";

// Necessaire pour combo choix actif
$lst_trend_following = $sc->getTrendFollowing();

?>

<div class="ui container inverted segment" style="padding-bottom: 0px !important;">

    <h2 class="ui left">
        <span>
            <?= tools::UTF8_encoding($name); // VFE - EX UT8_ENCODE 
            ?>
        </span>
        <? if ($sess_context->isSuperAdmin()) { ?>
            <i style="float: right; margin-top: 5px;" id="stock_delete_bt" class="ui inverted right float small trash icon"></i>
        <? } ?>
        <?
        if ($readonly && $ptf_nb_positions > 0) {
            echo '<select id="ptf_select_bt" style="float: right; top: -4px; right: 10px;" class="ui dropdown"><option />';
            ksort($aggregate_ptf['positions']);
            foreach ($aggregate_ptf['positions'] as $key => $val)
                if (!$val['other_name']) echo "<option " . ($key == $symbol ? "selected=\"selected\"" : "") . " value=\"".$key."\">".QuoteComputing::getQuoteNameWithoutExtension($key)."</option>";
            echo "</select>";
        }
        ?>
    </h2>
    <table class="ui selectable inverted single line unstackable very compact sortable-theme-minimal table" id="lst_position" data-sortable>
        <thead><? echo QuoteComputing::getHtmlTableHeader(); ?></thead>
        <tbody><? echo $qc->getHtmlTableLine(1); ?></tbody>
    </table>

</div>


<?
// /////////////////////
// GRAPHES COURS
// //////////////////////

function getTimeSeriesData($table_name, $period, $sym)
{

    $i = 0;
    $ret = array();

    $file_cache = 'cache/TMP_TIMESERIES_' . $sym . '_' . $period . '.json';

    if (cacheData::refreshCache($file_cache, 6000)) { // 1h de cache

        $req = "SELECT * FROM " . $table_name . " dtsa, indicators indic WHERE dtsa.symbol=indic.symbol AND dtsa.day=indic.day AND indic.period='" . $period . "' AND dtsa.symbol='" . $sym . "' ORDER BY dtsa.day ASC";
        $res = dbc::execSql($req);
        while ($row = mysqli_fetch_assoc($res)) {
            $row['i'] = $i++;
            $row['adjusted_close'] = sprintf("%.2f", $row['adjusted_close']);
            $row['MM7']   = sprintf("%.2f", $row['MM7']);
            $row['MM20']  = sprintf("%.2f", $row['MM20']);
            $row['MM50']  = sprintf("%.2f", $row['MM50']);
            $row['MM200'] = sprintf("%.2f", $row['MM200']);
            $row['RSI14'] = sprintf("%.1f", $row['RSI14']);
            $row['DM']    = sprintf("%.2f", $row['DM']);
            $row['colr']  = $row['close'] >= $row['open'] ? 1 : 0; // Pour le choix de la couleur on ne prend pas le adjusted_close car le adjusted_open n'existe pas
            $ret[] = $row;
        }

        cacheData::writeCacheData($file_cache, $ret);
    } else {
        $ret = cacheData::readCacheData($file_cache);
    }

    return $ret;
}

// Format data
function format_data($data, $period)
{
?>
    new_data_<?= $period ?> = [
    <?
    $i = 1;
    $count = count($data);

    foreach ($data as $key => $val) {
        echo sprintf(
            "{ x: '%s', y: %.2f, v: %d, mom: %.2f, c: '%s' }%s",
            $val["day"],
            (float)$val["adjusted_close"],
            $val["volume"] == "" ? 0 : round($val["volume"] / 1000),
            $val["DM"],
            $val["colr"],
            $i++ == $count ? '' : ','
        );
    }
    ?>
    ];
<?
}

function perpendicular_distance(array $pt, array $line)
{
    // Calculate the normalized delta x and y of the line.
    $dx = $line[1]['i'] - $line[0]['i'];
    $dy = $line[1]['adjusted_close'] - $line[0]['adjusted_close'];
    $mag = sqrt($dx * $dx + $dy * $dy);
    if ($mag > 0) {
        $dx /= $mag;
        $dy /= $mag;
    }

    // Calculate dot product, projecting onto normalized direction.
    $pvx = $pt['i'] - $line[0]['i'];
    $pvy = $pt['adjusted_close'] - $line[0]['adjusted_close'];
    $pvdot = $dx * $pvx + $dy * $pvy;

    // Scale line direction vector and subtract from pv.
    $dsx = $pvdot * $dx;
    $dsy = $pvdot * $dy;
    $ax = $pvx - $dsx;
    $ay = $pvy - $dsy;

    return sqrt($ax * $ax + $ay * $ay);
}

function ramer_douglas_peucker(array $points, $epsilon)
{
    if (count($points) < 2) {
        throw new InvalidArgumentException('Not enough points to simplify');
    }

    // Find the point with the maximum distance from the line between start/end.
    $dmax = 0;
    $index = 0;
    $end = count($points) - 1;
    $start_end_line = [$points[0], $points[$end]];
    for ($i = 1; $i < $end; $i++) {
        $dist = perpendicular_distance($points[$i], $start_end_line);
        if ($dist > $dmax) {
            $index = $i;
            $dmax = $dist;
        }
    }

    // If max distance is larger than epsilon, recursively simplify.
    if ($dmax > $epsilon) {
        $new_start = ramer_douglas_peucker(array_slice($points, 0, $index + 1), $epsilon);
        $new_end = ramer_douglas_peucker(array_slice($points, $index), $epsilon);
        array_pop($new_start);
        return array_merge($new_start, $new_end);
    }

    // Max distance is below epsilon, so return a line from with just the
    // start and end points.
    return [$points[0], $points[$end]];
}


// Recuperation de tous les indicateurs DAILY de l'actif
$data_daily = getTimeSeriesData("daily_time_series_adjusted", "DAILY", $symbol);

// Recuperation de tous les indicateurs WEEKLY/MONTHLY de l'actif
$data_weekly  = getTimeSeriesData("weekly_time_series_adjusted",  "WEEKLY",  $symbol);
$data_monthly = getTimeSeriesData("monthly_time_series_adjusted", "MONTHLY", $symbol);

$js_bubbles_data = "";

// D�termination du range des dates de cotation (date de d�but et de fin de cotation)
$first_date_quote_daily = date('Y-m-d');
$last_date_quote_daily  = date('Y-m-d');

if (count($data_daily) > 0) {
    $first_date_quote_daily = $data_daily[0]['day'];
    $last_date_quote_daily  = $data_daily[array_key_last($data_daily)]['day'];
}

// D�termination de la valeur max de volume pour fixer le range des volumes dans le graphe
$top_value_volume = 0;
foreach ($data_daily as $key => $val) if ($val['volume'] > $top_value_volume) $top_value_volume = $val['volume'];

?>

<style>
    table td {
        padding: 5px 20px !important;
    }

    table div.checkbox {
        padding: 8px 0px !important;
    }
</style>


<? if ($readonly) { ?>

    <div id="canvas_area" class="ui container inverted segment" style="padding-top: 0px !important; margin-top: 0px !important;">
        <span class="graph_bts">
            <span class="ui buttons">
                <button id="graphe_D_bt" class="mini ui <?= $default_button_choice['rsi'] == 0  ? $bt_interval_colr : $bt_grey_colr ?> button">D</button>
                <button id="graphe_W_bt" class="mini ui <?= $default_button_choice['rsi'] == 1  ? $bt_interval_colr : $bt_grey_colr ?> button">W</button>
                <button id="graphe_M_bt" class="mini ui <?= $default_button_choice['rsi'] == 2  ? $bt_interval_colr : $bt_grey_colr ?> button">M</button>
            </span>
            <span class="ui buttons">
                <button id="graphe_all_bt" class="mini ui <?= $bt_period_colr ?> button">All</button>
                <button id="graphe_3Y_bt" class="mini ui <?= $bt_grey_colr ?> button">3Y</button>
                <button id="graphe_1Y_bt" class="mini ui <?= $bt_grey_colr ?> button">1Y</button>
                <button id="graphe_1T_bt" class="mini ui <?= $bt_grey_colr ?> button">1T</button>
            </span>
            <span class="ui buttons">
                <button id="graphe_reg_bt" class="mini ui <?= $default_button_choice['reg']    == 1 ? $bt_mmx_colr    : $bt_grey_colr ?> button"><i style="margin-left: 5px;" class="icon inverted chart line"></i></button>
                <button id="graphe_scale_bt" class="mini ui <?= $default_button_choice['scale']  == 1 ? $bt_mmx_colr    : $bt_grey_colr ?> button"><i style="margin-left: 5px;" class="icon inverted text height"></i></button>
            </span>
            <span class="ui buttons">
                <button id="graphe_mm" class="mini ui <?= $bt_grey_colr ?> button">MM</button>
                <button id="graphe_mm7_bt" class="mini ui <?= ($mmx & 1) == 1 ? $bt_mmx_colr : $bt_grey_colr ?> button">7</button>
                <button id="graphe_mm20_bt" class="mini ui <?= ($mmx & 2) == 2 ? $bt_mmx_colr : $bt_grey_colr ?> button">20</button>
                <button id="graphe_mm50_bt" class="mini ui <?= ($mmx & 4) == 4 ? $bt_mmx_colr : $bt_grey_colr ?> button">50</button>
                <button id="graphe_mm200_bt" class="mini ui <?= ($mmx & 8) == 8 ? $bt_mmx_colr : $bt_grey_colr ?> button">200</button>
            </span>
            <span class="ui buttons">
                <button id="graphe_volume_bt" class="mini ui <?= $default_button_choice['volume'] == 1 ? $bt_volume_colr : $bt_grey_colr ?> button"><i style="margin-left: 5px;" class="icon inverted signal"></i></button>
                <? if ($sess_context->isUserConnected()) { ?>
                    <button id="graphe_alarm_bt" class="mini ui <?= $default_button_choice['alarm'] == 1 ? $bt_alarm_colr : $bt_grey_colr ?> button"><i style="margin-left: 5px;" class="icon inverted flag"></i></button>
                    <button id="graphe_av_bt" class="mini ui <?= $default_button_choice['av']    == 1 ? $bt_av_colr    : $bt_grey_colr ?> button"><i style="margin-left: 5px;" class="icon inverted dollar"></i></button>
                <? } ?>
            </span>
        </span>
        <canvas id="stock_canvas1" height="100"></canvas>
        <canvas id="stock_canvas2" height="30"></canvas>
        <canvas id="stock_canvas3" height="30"></canvas>
    </div>

<? } ?>

<div class="ui container inverted segment">
    <form class="ui inverted form <?= $readonly ? "readonly" : "" ?>">
        <h4 class="ui inverted dividing header">Asset Informations (<?= $qc->getQuoteAttr('engine') ?>)</h4>
        <div class="field">
            <div class="three fields">
                <div class="field">
                    <label>Provider</label>
                    <input type="text" id="f_provider" value="<?= $qc->getQuoteAttr('provider') ?>" placeholder="Provider">
                </div>
                <div class="field">
                    <label>ISIN</label>
                    <input type="text" id="f_isin" value="<?= $qc->getQuoteAttr('ISIN') ?>" placeholder="ISIN">
                </div>
                <div class="field">
                    <label>Risque</label>
                    <? if (!$readonly) { ?>
                        <select class="ui fluid search dropdown" id="f_rating">
                            <?
                            foreach ([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7] as $key => $val)
                                echo '<option value="' . $key . '" ' . ($qc->getQuoteAttr('rating') == $key ? 'selected="selected"' : '') . '>' . $val . '</option>';
                            ?>
                        </select>
                    <? } else { ?>
                        <input type="text" id="f_rating" value="<?= $qc->getQuoteAttr('rating') ?>" placeholder="Rating">
                    <? } ?>
                </div>
            </div>
            <div class="three fields">
                <div class="field">
                    <label>Frais de gestion (%)</label>
                    <input type="text" id="f_frais" value="<?= $qc->getQuoteAttr('frais') ?>" placeholder="Frais de gestion">
                </div>
                <div class="field">
                    <label>Actifs (Million)</label>
                    <input type="text" id="f_actifs" value="<?= $qc->getQuoteAttr('actifs') ?>" placeholder="Actifs">
                </div>
                <div class="field">
                    <label>Politique de distribution</label>
                    <? if (!$readonly) { ?>
                        <select class="ui fluid search dropdown" id="f_distribution">
                            <option value="">Choisir</option>
                            <?
                            foreach (uimx::$invest_distribution as $key => $val)
                                echo '<option value="' . $key . '" ' . ($qc->getQuoteAttr('distribution') == $key ? 'selected="selected"' : '') . '>' . $val . '</option>';
                            ?>
                        </select>
                    <? } else { ?>
                        <input type="text" id="f_distribution" value="<?= $qc->getQuoteAttr('distribution') == "" ? "" : uimx::$invest_distribution[$qc->getQuoteAttr('distribution')] ?>" placeholder="Distribution">
                    <? } ?>
                </div>
            </div>
            <? if (!$readonly) { ?>
                <div class="three fields">
                    <div class="field">
                        <label>Type actif</label>
                        <select class="ui fluid search dropdown" id="f_type">
                            <?
                            foreach (array_merge(uimx::$type_actif, uimx::$type_turbo) as $key => $val)
                                echo '<option value="' . $val . '" ' . ($qc->getQuoteAttr('type') == $val ? 'selected="selected"' : '') . '>' . $val . '</option>';
                            ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Morning Star</label>
                        <input type="text" id="f_link1" value="<?= $link1 ?>" placeholder="Lien http">
                    </div>
                    <div class="field">
                        <label>JustETF</label>
                        <input type="text" id="f_link2" value="<?= $link2 ?>" placeholder="Lien http">
                    </div>
                </div>
            <? } ?>
            <div class="four fields">
                <div class="field">
                    <label>GF Symbole</label>
                    <input type="text" id="f_gf_symbol" value="<?= $qc->getQuoteAttr('gf_symbol') ?>" placeholder="Google finance symbole">
                </div>
                <div class="field">
                    <? if (!$readonly) { ?>
                        <label>&nbsp;</label>
                        <div class="ui toggle inverted checkbox" onclick="toogleCheckBox('f_pea');">
                            <input type="checkbox" id="f_pea" <?= $qc->getQuoteAttr('pea') == 1 ? 'checked="checked' : '' ?> tabindex="0" class="hidden">
                            <label>Eligible PEA</label>
                        </div>
                    <? } else { ?>
                        <label>Eligible PEA</label>
                        <input type="text" id="f_pea" value="<?= $qc->getQuoteAttr('pea') == 0 ? "Non" : "Oui" ?>" placeholder="">
                    <? } ?>
                </div>
                <div class="field">
                    <label>Dividende annualis�</label>
                    <input type="text" id="f_dividende" value="<?= $qc->getQuoteAttr('dividende_annualise') > 0 ? $qc->getQuoteAttr('dividende_annualise') : "" ?>" placeholder="0">
                </div>
                <div class="field">
                    <label>Date dividende</label>
                    <div class="ui right icon inverted left labeled fluid input">
                        <input type="text" size="10" id="f_date_dividende" value="<?= $qc->getQuoteAttr('date_dividende') ?>" placeholder="0000-00-00">
                        <i class="inverted black calendar alternate outline icon"></i>
                    </div>
                </div>
            </div>
            <div class="seven fields" id="turbo_area">
                <div class="field">
                    <label>Emetteur</label>
                    <? if (!$readonly) { ?>
                        <select class="ui fluid search dropdown" id="f_emetteur">
                            <? foreach(uimx::$invest_emetteur as $key => $val) echo "<option ".($qc->getQuoteAttr('pc_emetteur') == $val ? "selected=\"selected\"" : "")." value=\"".$val."\">".$val."</option>"; ?>
                        </select>
                    <? } else { ?>
                        <input type="text" id="f_emetteur" value="<?= $qc->getQuoteAttr('pc_emetteur') ?>" placeholder="">
                    <? } ?>
                </div>
                <div class="field">
                    <label>Ticker</label>
                    <input type="text" id="f_ticker" value="<?= $qc->getQuoteAttr('pc_ticker') ?>" placeholder="Ticker">
                </div>
                <div class="field">
                    <label>Levier</label>
                    <? if (!$readonly) { ?>
                        <select class="ui fluid search dropdown" id="f_levier">
                            <? foreach(range(1, 10) as $key => $val) echo "<option ".($qc->getQuoteAttr('pc_levier') == $val ? "selected=\"selected\"" : "")." value=\"".$val."\">".$val."</option>"; ?>
                        </select>
                    <? } else { ?>
                        <input type="text" id="f_levier" value="<?= $qc->getQuoteAttr('pc_levier') ?>" placeholder="">
                    <? } ?>
                </div>
                <div class="field">
                    <label>Sous jacent</label>
                    <input type="text" id="f_sousjacent" value="<?= $qc->getQuoteAttr('pc_sousjacent') ?>" placeholder="0">
                </div>
                <div class="field">
                    <label>Valeur</label>
                    <input type="text" size="10" id="f_val_init" value="<?= $qc->getQuoteAttr('price') ?>">
                </div>
                <div class="field">
                    <label>Previous</label>
                    <input type="text" size="10" id="f_val_prev" value="<?= $qc->getQuoteAttr('previous') ?>">
                </div>
                <div class="field">
                    <? if (!$readonly) { ?>
                        <label>&nbsp;</label>
                        <div class="ui toggle inverted checkbox" onclick="toogleCheckBox('f_expire');">
                            <input type="checkbox" id="f_expire" <?= $qc->getQuoteAttr('pc_expire') == 1 ? 'checked="checked' : '' ?> tabindex="0" class="hidden">
                            <label>Expir�</label>
                        </div>
                    <? } else { ?>
                        <label>Expir�</label>
                        <input type="text" id="f_expire" value="<?= $qc->getQuoteAttr('pc_expire') == 0 ? "Non" : "Oui" ?>" placeholder="">
                    <? } ?>
                </div>

            </div>

        </div>
    </form>
</div>

<div id="canvas_area2" class="ui container inverted segment">
    <? if ($readonly) { ?>
        <h4 class="ui inverted dividing header">Tags</h4>
        <div class="ui horizontal list">
        <? } ?>
        <? foreach ([
            "Classe d'actif"      => uimx::$invest_classe,
            "Secteur"             => uimx::$invest_secteur,
            "March�"              => uimx::$invest_market,
            "Zone g�ographique"   => uimx::$invest_zone_geo,
            "Crit�re factoriel"   => uimx::$invest_factorielle,
            "Taille"              => uimx::$invest_taille,
            "Th�me"               => uimx::$invest_theme,
            "Conseill�e par"      => $tags_conseillers
        ] as $lib => $tab) { ?>

            <? if (!$readonly) { ?>
                <h4 class="ui inverted dividing header"><?= $lib ?></h4>
                <div class="ui horizontal list">
                <? } ?>

                <? foreach ($tab as $key => $val) {
                    if ($readonly) {

                        if (isset($tags[$val['tag']])) { ?>
                            <div class="item"><button class="item very small ui bt_tags <?= $bt_interval_colr ?> button"><?= $val['tag'] ?></button></div>
                        <? } ?>

                    <? } else { ?>
                        <div class="item">
                            <button <?= isset($val['desc']) && $val['desc'] != "" ? "data-tootik-conf=\"multiline\" data-tootik=\"" . $val['desc'] . "\"" : "" ?> id="bt_<?= strtoupper(substr($lib, 0, 3)) . "_" . $key ?>" class="item very small ui bt_tags <?= isset($tags[$val['tag']]) ? $bt_interval_colr : $bt_grey_colr ?> button"><?= $val['tag'] ?></button>
                        </div>
                    <? } ?>

                <? } ?>

                <? if (!$readonly) { ?>
                </div><? } // Pour la div ui horizontal list en mode edit (une pour chaque type de tags) 
                        ?>
        <? } ?>

        <? if ($readonly) { ?>
        </div><? } // Pour la div ui horizontal list en mode readonly (une pour tous les tags) 
                ?>
</div>

<? if ($readonly) { ?>
    <div id="canvas_area3" class="ui container inverted segment form">
        <h4 class="ui inverted dividing header">Links</h4>
        <div class="field">
            <div class="two fields">
                <div class="field">
                    <i class="ui icon inverted external"></i><a href="<?= $link1 ?>">Morning Star</a>
                </div>
                <div class="field">
                    <i class="ui icon inverted external"></i><a href="<?= $link2 ?>">JustETF</a>
                </div>
            </div>
        </div>
    </div>
<? } ?>

<?

if ($debug == 1) {
    $infos = calc::getDirectDM($data);
?>

    <div class="ui container inverted grid segment">
        <form class="ui inverted form">
            <h4 class="ui inverted dividing header">Cache Informations</h4>
        </form>
    </div>

    <div class="ui container inverted grid segment">
        <div class="column">

            <div class="ui inverted stackable two column grid container">
                <div class="wide column">
                    <table id="detail2_stock" class="ui selectable inverted single line table">
                        <tbody>
                            <tr>
                                <td>Price</td>
                                <td><?= $data["day"] ?> [<?= sprintf("%.2f", $infos['price']) ?>] [<?= sprintf("%2.2f", $infos['dm']) ?>%]</td>
                            </tr>
                            <tr>
                                <td>DMD1</td>
                                <td><?= isset($data["DMD1"]) ? $data["DMD1"] : "N/A" ?> [<?= sprintf("%.2f", $infos['close']['DMD1']) ?>] [<?= sprintf("%2.2f", $infos['perf']['DMD1']) ?>%]</td>
                            </tr>
                            <tr>
                                <td>DMD2</td>
                                <td><?= isset($data["DMD2"]) ? $data["DMD2"] : "N/A" ?> [<?= sprintf("%.2f", $infos['close']['DMD2']) ?>] [<?= sprintf("%2.2f", $infos['perf']['DMD2']) ?>%]</td>
                            </tr>
                            <tr>
                                <td>DMD3</td>
                                <td><?= isset($data["DMD3"]) ? $data["DMD3"] : "N/A" ?> [<?= sprintf("%.2f", $infos['close']['DMD3']) ?>] [<?= sprintf("%2.2f", $infos['perf']['DMD3']) ?>%]</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div style="height: 140px; width: 450px; overflow: scroll;"><?= var_dump($data) ?></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="wide column">
                    <table id="detail3_stock" class="ui selectable inverted single line table">
                        <tbody>
                            <?
                            foreach (cacheData::$lst_cache as $key) {
                                $fn = "cache/" . $key . "_" . $symbol . ".json";
                                echo "<tr data-tootik=\"" . $key . "_" . $symbol . ".json\"><td style=\"padding: 0px 0px 0px 10px !important;\">" . (file_exists($fn) ? "<i class=\"ui icon inverted green check\"></i>" : "<i class=\"ui icon inverted red x\"></i>") . "</td><td>[" . (file_exists($fn) ? date("Y-m-d H:i", filemtime($fn)) : "") . "] " . str_replace("_TIME_SERIES_ADJUSTED_", "::", $key) . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

<? } ?>

<? if ($sess_context->isUserConnected() && $readonly) { ?>

    <div id="canvas_area3" class="ui container inverted segment form">
        <h4 class="ui inverted dividing header">Historique Ordres
            <button id="order_add_bt" class="circular ui icon very small right floated pink button label"><i class="inverted white add icon"></i></button>
        </h4>
        <div class="field">
            <table class="ui striped selectable inverted single line unstackable very compact table sortable-theme-minimal" id="lst_order" data-sortable>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Ptf</th>
                        <th>Actif</th>
                        <th>Action</th>
                        <th class="center aligned">Qt�</th>
                        <th class="center aligned">Prix</th>
                        <th class="center aligned">Total(&euro;)</th>
                        <th class="center aligned">Comm</th>
                        <th>+/-</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?

                    $sum_comm = 0;
                    $sum_orders = 0;
                    $nb_orders = 0;
                    $qte = 0;
                    $req_option = "AND o.product_name='" . $symbol . "'";
                    $req = "SELECT *, p.shortname, o.id id_order FROM orders o, portfolios p WHERE o.portfolio_id=p.id AND p.user_id=" . $sess_context->getUserId() . " " . $req_option . " ORDER BY date DESC, datetime  DESC";
                    $res = dbc::execSql($req);

                    // Bye bye si inexistant 
                    while ($row = mysqli_fetch_assoc($res)) {
                        $row = calc::formatDataOrder($row);
                        $td_gain = '<td>-</td>';
                        if ($row['action'] == -1 && $row['pru']) {
                            $order_gain = ($row['price'] - $row['pru']) * $row['quantity'];
                            $td_gain = '<td data-value="' . $order_gain . '" class="' . ($row['price'] > $row['pru'] ? "aaf-positive" : "aaf-negative") . '">' . ($row['price'] > $row['pru'] ? "+" : "") . sprintf("%.2f", $order_gain) . uimx::getCurrencySign($row['devise']) . '</td>';
                        }
                        echo '<tr>
                            <td><i class="inverted ' . str_replace(["left", "right"], ["sign out", "sign in"], $row['icon']) . ' icon"></i> ' . $row['date'] . '</td>
                            <td>' . $row['shortname'] . '</td>
                            <td>' . QuoteComputing::getQuoteNameWithoutExtension($row['product_name']) . '</td>
                            <td>' . $row['action_lib'] . '</td>
                            <td>' . $row['quantity'] . '</td>
                            <td>' . $row['price_signed'] . '</td>
                            <td class="' . $row['action_colr'] . '">' . $row['valo_signed'] . '</td>
                            <td>' . sprintf("%.2f", $row['commission']) . '&euro;</td>
                            ' . $td_gain . '
                            <td><i id="order_edit_' . $row['id_order'] . '_' . $row['portfolio_id'] . '_bt" class="edit inverted icon"></i></td>
                        </tr>';
                        $sum_comm += $row['commission'];
                        $sum_orders += ($row['valo'] * ($row['action'] >= 0 ? 1 : -1));
                        $nb_orders++;
                        if ($row['action'] == 1 || $row['action'] == -1) {
                            // On n'affiche pas les ordres qui ne sont pas dans le range affich� du graphe
                            if ($row['date'] >= $first_date_quote_daily && $row['date'] <= $last_date_quote_daily)
                                $js_bubbles_data .= "bubbles_data.push({ valueX: '" . $row['date'] . "', valueY: " . floatval($row['price']) . ", rayon: " . (max(3, 5 * ($row['valo'] / 2000))) . ", rgb: " . $row['action'] . " }); ";
                            $qte += $row['quantity'] * $row['action'];
                        }
                    }

                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4"></td>
                        <td style="text-align: right"><?= $qte ?></td>
                        <td style="text-align: right"><?= sprintf("%.2f&euro;", $qte == 0 ? 0 : $sum_orders / $qte) ?></td>
                        <td style="text-align: right"><?= sprintf("%.2f&euro;", $sum_orders) ?></td>
                        <td style="text-align: right"><?= sprintf("%.2f&euro;", $sum_comm) ?></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <div id="pagination_box"></div>
        </div>
    </div>
<? } ?>


<div class="ui container inverted segment">
    <h2 class="ui inverted right aligned header foot_buttons">
        <? if (!$readonly) { ?>
            <button id="nav_menu_bt" class="dropbtn circular ui right floated grey button icon_action"><i class="inverted white ellipsis vertical icon"></i></button>
            <div class="ui vertical menu nav nav_menu_top" id="nav_menu">
                <a class="item" id="stock_indic_bt"><span>Rebuild Indicators</span></a>
                <a class="item" id="stock_reload_bt"><span>Reload Data</span></a>
            </div>
            <button id="stock_edit_bt" class="circular ui icon very small right floated pink labelled button"><i class="inverted white edit icon"></i> Modifier</button>
        <? } else if ($sess_context->isSuperAdmin()) { ?>
            <button id="stock_edit_bt" class="circular ui icon very small right floated primary labelled button"><i class="inverted white edit icon"></i></button>
        <? } ?>
        <button id="stock_back_bt" class="circular ui icon very small right floated grey labelled button"><i class="inverted white reply icon"></i></button>

    </h2>
</div>



<script>
    const datepicker1 = new TheDatepicker.Datepicker(el('f_date_dividende'));
    datepicker1.options.setInputFormat("Y-m-d");
    datepicker1.render();

    // init variables utiles pour le graphe
    var position_pru      = <?= $position_pru ?>;
    var is_user_connected = <?= $sess_context->isUserConnected() ? 'true' : 'false' ?>;
    var stock_currency    = '<?= $curr_graphe ?>';
    var bubbles_data = [];
    <?= $js_bubbles_data ?>

    <? if ($readonly) { ?>

        var myChart1 = null;
        var myChart2 = null;
        var myChart3 = null;

        // 1y=280d, 55w, 12m
        var interval_period_days = {
            'D': {
                'ALL': 0,
                '3Y': 840,
                '1Y': 280,
                '1T': 70
            },
            'W': {
                'ALL': 0,
                '3Y': 165,
                '1Y': 55,
                '1T': 14
            },
            'M': {
                'ALL': 0,
                '3Y': 36,
                '1Y': 12,
                '1T': 3
            }
        };

        var mm_bts = {
            'graphe_mm7_bt': 'MM7',
            'graphe_mm20_bt': 'MM20',
            'graphe_mm50_bt': 'MM50',
            'graphe_mm200_bt': 'MM200',
            'graphe_volume_bt': 'VOLUME',
            'graphe_reg_bt': 'REG',
            'graphe_scale_bt': 'SCALE'
        };

        if (is_user_connected) {
            mm_bts.graphe_alarm_bt = 'ALARM';
            mm_bts.graphe_av_bt = 'AV';
        }

        // Couleurs des MMX
        var mmx_colors = {
            'REG': '<?= $sess_context->getSpectreColor(4) ?>',
            'MM7': '<?= $sess_context->getSpectreColor(4) ?>',
            'MM20': '<?= $sess_context->getSpectreColor(2) ?>',
            'MM50': '<?= $sess_context->getSpectreColor(1) ?>',
            'MM200': '<?= $sess_context->getSpectreColor(6) ?>'
        };

        // Fonction de gestoion des tableaux de valeurs
        min_slice = function(tab, size) {
            var ret = (tab.length - size - 1) > 0 ? (tab.length - size - 1) : 0;
            return ret;
        }
        max_slice = function(tab) {
            var ret = tab.length > 0 ? tab.length : 0;
            return ret;
        }
        getSlicedData = function(tab, size) {
            var ret = size == 0 ? tab : tab.slice(min_slice(tab, size), max_slice(tab));
            return ret;
        }
        getSlicedData2 = function(interval, t_d, t_w, t_m, size) {
            var tab = interval == 'D' ? t_d : (interval == 'W' ? t_w : t_m);
            var ret = size == 0 ? tab : tab.slice(min_slice(tab, size), max_slice(tab));
            return ret;
        }

        // Creation de Dataset generique
        newDataset2 = function(mydata, mytype, yaxeid, yaxekey, mylabel, mycolor, bg, myfill, myborderwith = 0.5, mytension = 0.4, myradius = 0) {

            var ret = {
                type: mytype,
                data: mydata,
                label: mylabel,
                borderColor: mycolor,
                backgroundColor: bg,
                borderWidth: myborderwith,
                yAxisID: yaxeid,
                parsing: {
                    yAxisKey: yaxekey
                },
                cubicInterpolationMode: 'monotone',
                tension: mytension,
                fill: myfill,
                pointRadius: myradius,
                borderDash: yaxekey == 'log' ? [2, 2] : [0, 0],
                normalized: true
            };

            return ret;
        }

        // Creation de Dataset generique
        newDatasetVols = function(mydata, mytype, yaxeid, yaxekey, mylabel) {

            var ret = {
                type: mytype,
                data: mydata,
                label: mylabel,
                borderColor: 0,
                backgroundColor: mydata.map(function(item) {
                    return item.c == 1 ? 'rgba(0, 255, 0, 0.5)' : 'rgba(255, 0, 0, 0.5)';
                }),
                borderWidth: 0,
                yAxisID: yaxeid,
                parsing: {
                    yAxisKey: yaxekey
                },
                fill: true,
                normalized: true
            };

            return ret;
        }

        // Creation de Datasets specifiques
        getDatasetVals = function(vals) {
            return newDataset2(vals, 'line', 'y1', 'y', 'Cours', '<?= $sess_context->getSpectreColor(0) ?>', '<?= $sess_context->getSpectreColor(0, 0.2) ?>', true);
        }
        getDatasetVols = function(vals, l) {
            return newDatasetVols(vals, 'bar', 'y2', 'v', l);
        }
        getDatasetMMX = function(vals, k, l) {
            var colr = l.substr(0, 3) == 'REG' ? mmx_colors['REG'] : mmx_colors[l];
            var ds = newDataset2(vals, 'line', 'y1', k, l, colr, '', false);

            if (l == 'REG')
                options_Stock_Graphe.plugins.insiderText = [{
                    title: 'R2:' + vals[0]['r2'],
                    colr: '#e77fe8',
                    bgcolr: '#1b1c1d',
                    alignX: 'left',
                    alignY: 'top'
                }];

            // Pour tous les traces REG
            if (l.substr(0, 3) == 'REG') {
                ds.borderDash = l == 'REG' ? [5, 2] : [1, 3];
                ds.borderWidth = l == 'REG' ? 2 : 1;
            }

            return ds;
        }
        getDatasetRSI14 = function(vals) {
            return newDataset2(vals, 'line', 'y', 'rsi14', "RSI14", 'violet', 'violet', false);
        }
        getDatasetDM = function(vals) {
            return newDataset2(vals, 'line', 'y', 'mom', "DM", 'rgba(255, 255, 0, 0.5)', 'rgba(255, 255, 0, 0.75)', false, 2, 0.4, 0);
        }

        // Javascript program to calculate the standard deviation of an array
        dev = function(arr) {
            // Creating the mean with Array.reduce
            let mean = arr.reduce((acc, curr) => {
                return acc + curr;
            }, 0) / arr.length;

            // Assigning (value - mean) ^ 2 to every array item
            arr = arr.map((k) => {
                return (k - mean) ** 2;
            });

            // Calculating the sum of updated array
            let sum = arr.reduce((acc, curr) => acc + curr, 0);

            // Calculating the variance
            let variance = sum / arr.length;

            // Returning the standard deviation
            return Math.sqrt(sum / arr.length);
        }

        var graphe_size_days = 0;
        var new_data_daily = [];
        var new_data_weekly = [];
        var new_data_monthly = [];

        // Data stock prices : Initialisation des tableau new_data_daily, new_data_weekly, new_data_monthly
        <?
        format_data($data_daily,   "daily");
        format_data($data_weekly,  "weekly");
        format_data($data_monthly, "monthly");
        ?>

        // Initialisation des tableaux ref_x_days
        var ref_d_days = [], ref_w_days = [], ref_m_days = [];
        var min_day = 99999999;
        var max_day = 0;

        try {

            // Ref Day Data
            new_data_daily.forEach(function(elt) {
                if (elt.y < min_day) min_day = elt.y;
                if (elt.y > max_day) max_day = elt.y;
                ref_d_days.push(elt.x);
            });
            new_data_weekly.forEach(function(elt) {
                ref_w_days.push(elt.x);
            });
            new_data_monthly.forEach(function(elt) {
                ref_m_days.push(elt.x);
            });

            let elt = Dom.find('#lst_position tbody tr td:nth-child(6)')[0];
            let reg_type = Dom.attribute(elt, 'data-reg-type');
            let reg_period = Dom.attribute(elt, 'data-reg-period');

            // //////////////////////////////////////////////
            // Calcul mmxxx/rsixxx en D/W/M
            // //////////////////////////////////////////////
            [new_data_daily, new_data_weekly, new_data_monthly].forEach(function(tab_item) {

                var tmp_mm = [];
                var tmp_rsi = [];

                if (tab_item.length == 0) return;

                // Recup des data dans tmp
                tab_item.forEach(function(item) {
                    tmp_mm.push(item.y);
                    tmp_rsi.push({
                        c: item.y
                    });
                });

                // mmxxx
                [7, 20, 50, 200].forEach(function(mm_item) {

                    // Calcul mm
                    if (tab_item.length >= mm_item) {
                        let ind = 0;
                        // Trendways
                        // Trendways
                        tw.ma(tmp_mm, mm_item).forEach(function(item) {
                            tab_item[(mm_item - 1) + ind++]['mm' + mm_item] = item;
                        });
                    }

                    // Completion des data inferieures a mm_item
                    len_tab = Math.min(tab_item.length, mm_item) - 1;
                    [...Array(len_tab).keys()].forEach(function(i) {
                        let output2 = tw.ma(tmp_mm.slice(0, len_tab - i), len_tab - i);
                        if (output2.length > 0) {
                            let q = len_tab - i - output2.length + 1;
                            output2.forEach(function(z) {
                                tab_item[q--]['mm' + mm_item] = z;
                            });
                        }
                    });
                });

                // rsixxx
                [14].forEach(function(rsi_item) {
                    let output_rsi = tw.rsi(tmp_rsi, rsi_item);
                    ind = 0;
                    output_rsi.forEach(function(item) {
                        tab_item[ind++]['rsi' + rsi_item] = item.rsi;
                    });
                });

                // Momemtum
                //let output_mom = tw.momentum(tmp_rsi, 60);
                //ind = 0;
                //output_mom.forEach(function(item) { tab_item[ind++]['mom'] = item.mom; });

                // Formattage data et calcul regression logarythmique et/ou lineaire
                // beginAt proportionnel � la ref en daily
                let beginAt = Math.round((reg_period * tab_item.length) / new_data_daily.length);

                // Controle de non depassement de la taille du tableau des data
                beginAt = Math.max(beginAt > tab_item.length - 100 ? tab_item.length - 100 : beginAt, 0);

                // Recuperation et reformatage des data 
                let i = 1;
                var tmp = [];
                var d_data_reg = [];
                tab_item.slice(beginAt).forEach(function(item) {
                    d_data_reg.push([i++, item.y]);
                    tmp.push(item.y);
                });

                // Liste des fcts
                var fct_name = ['', 'linear', 'exponential', 'logarithmic', 'polynomial', 'power'];

                // Calcul de la regression
                let result = regression[fct_name[reg_type]](d_data_reg, {
                    order: 1
                });

                // Calcul de la standard deviation (ecart type)
                var ec = dev(tmp) / 2;

                // Remise en conformite pour affichage dans graphe
                let j = beginAt;
                result.points.forEach(function(item) {
                    tab_item[j]['reg'] = item[1];
                    tab_item[j]['r2'] = result.r2 + '/EC:' + ec.toFixed(2);
                    j++;
                });

                // Ajout compl�ment si linear et beginAt > 0
                [...Array(beginAt).keys()].forEach(function(x) {
                    tab_item[x]['reg'] = result.predict((-1 * beginAt) + x)[1];
                    tab_item[x]['r2'] = result.r2 + '/EC:' + ec.toFixed(2);
                });

                // Regression lin�aire +11 ecart type 
                [...Array(tab_item.length).keys()].forEach(function(x) {
                    v = result.predict(-1 * beginAt + x)[1];
                    tab_item[x]['reg1'] = v - (2 * ec);
                    tab_item[x]['reg2'] = v - ec;
                    tab_item[x]['reg3'] = v + ec;
                    tab_item[x]['reg4'] = v + (2 * ec);
                });

            });

            // Check s'il y une date de cotation pour la date de l'ordre (pour le bug si saisie mauvaise date en dehors range cotation par exmple)
            bubbles_data.forEach(function(elt) {
                // rectification de la couleur rgb
                elt.rgb = elt.rgb >= 0 ? "97, 194, 97" : "255, 0, 0";
                checked = false;
                new_date = "";
                ref_d_days.forEach(function(d_d) {
                    d1 = new Date(d_d);
                    d2 = new Date(elt.valueX);
                    if (d1 < d2) new_date = d_d;
                    if (d_d == elt.valueX) {
                        checked = true;
                        return;
                    }
                });
                if (!checked) elt.valueX = new_date;
            });

            // Filtre des labels de l'axes des x (date) (on ne garde que les premieres dates de marche cote du mois)
            var array_years = extractFirstDateYear(ref_d_days);

            // On retire le premier label (premier mois cote) pour qu'il n'empiete pas sur la gauche du graphe
            if (array_years.length > 2) array_years.shift();

            // Data pour les infos sur l'axe Y (stop loss/objectif/stop profit)
            var axe_infos = [];
            var h_lines = [];

            <?
            $pki_colr = ['stop_loss' => 'rgba(247,143,3, 0.6)', 'stop_profit' => 'rgba(181, 87, 87, 0.6)', 'objectif' => 'rgba(58, 48, 190, 0.6)'];
            foreach ($pki_colr as $key => $val)
                if (isset($lst_trend_following[$symbol][$key]) && $lst_trend_following[$symbol][$key] > 0) {
                    echo sprintf("axe_infos.push({ title: '%.1f'+stock_currency, colr: 'white', bgcolr: '%s', valueY: '%s' });", $lst_trend_following[$symbol][$key], $val, $lst_trend_following[$symbol][$key]);
                }
            ?>

            // Current data
            var g_new_data = null;
            var g_days = null;

            var ctx1 = document.getElementById('stock_canvas1').getContext('2d');
            el("stock_canvas1").height = document.body.offsetWidth > 700 ? 100 : 300;

            var ctx2 = document.getElementById('stock_canvas2').getContext('2d');
            el("stock_canvas2").height = document.body.offsetWidth > 700 ? 30 : 90;

            var ctx3 = document.getElementById('stock_canvas3').getContext('2d');
            el("stock_canvas3").height = document.body.offsetWidth > 700 ? 30 : 120;

        } catch (e) {
            alert('stock_detail.php: Graphe data error : ' + e);
            ref_d_days = [];
            ref_w_days = [];
            ref_m_days = [];
        }

        updateAlarmAVDisplay = function(chart) {

            chart.options.plugins.vertical = [];
            chart.options.plugins.horizontal = [];
            chart.options.plugins.rightAxeText = [];
            options_Stock_Graphe.plugins.bubbles = [];

            if (is_user_connected) {
                if (isCN('graphe_alarm_bt', '<?= $bt_alarm_colr ?>')) {
                    chart.options.plugins.horizontal = h_lines;
                    chart.options.plugins.rightAxeText = axe_infos;
                }

                if (isCN('graphe_av_bt', '<?= $bt_av_colr ?>')) {
                    options_Stock_Graphe.plugins.bubbles = bubbles_data;
                }
            }
        }

        toogleMMX = function(chart, label) {

            ref_colr = label.toLowerCase() == "volume" ? "<?= $bt_volume_colr ?>" : (label.toLowerCase() == "alarm" ? "<?= $bt_alarm_colr ?>" : (label.toLowerCase() == "av" ? "<?= $bt_av_colr ?>" : "<?= $bt_mmx_colr ?>"));
            bt = 'graphe_' + label.toLowerCase() + '_bt'
            addCN(bt, 'loading');

            // Pour alarm et av on ne fait rien pas gerer dans les datasets
            if (isCN(bt, ref_colr)) {
                // On retire zone texte R2
                if (label == 'REG')
                    options_Stock_Graphe.plugins.insiderText = [];

                // On retire les data de la courbe ou volume du bouton selectionne
                let new_array = [];
                chart.data.datasets.forEach((dataset) => {
                    if (!(dataset.label == label || (label.substr(0, 3) == 'REG' && dataset.label.substr(0, 3) == 'REG')))
                        new_array.push(dataset);
                });
                chart.data.datasets = new_array;
                setCookie('status_stock_bt_' + label.toLowerCase(), 0, 10000);
            } else {
                if (label.toLowerCase() == "volume")
                    chart.data.datasets.push(getDatasetVols(g_new_data, 'VOLUME'));
                else {
                    chart.data.datasets.push(getDatasetMMX(g_new_data, label.toLowerCase(), label));
                    // Pour REG on rajoute les traces complementaires
                    if (label == 'REG') {
                        [1, 2, 3, 4].forEach(function(i) {
                            chart.data.datasets.push(getDatasetMMX(g_new_data, label.toLowerCase() + i, label + i));
                        });
                    }
                }
                setCookie('status_stock_bt_' + label.toLowerCase(), 1, 10000);
            }

            // Changement couleur bt
            switchCN(bt, '<?= $bt_grey_colr ?>', ref_colr);

            // Prise en compte changement scale log ou lineaire
            options_Stock_Graphe.scales.y1.type = isCN('graphe_scale_bt', '<?= $bt_mmx_colr ?>') ? 'logarithmic' : 'linear';

            // Update sinon pas ok pour bt alarm et bt achatvente
            updateAlarmAVDisplay(chart);

            chart.update();

            rmCN(bt, 'loading');
        }

        getIntervalStatus = function() {
            return isCN('graphe_D_bt', '<?= $bt_interval_colr ?>') ? 'D' : (isCN('graphe_W_bt', '<?= $bt_interval_colr ?>') ? 'W' : 'M');
        }
        getPeriodStatus = function() {
            return isCN('graphe_all_bt', '<?= $bt_period_colr ?>') ? "ALL" : (isCN('graphe_3Y_bt', '<?= $bt_period_colr ?>') ? "3Y" : (isCN('graphe_1Y_bt', '<?= $bt_period_colr ?>') ? "1Y" : "1T"));
        }

        update_data = function(size) {

            interval = getIntervalStatus();

            g_new_data = getSlicedData2(interval, new_data_daily, new_data_weekly, new_data_monthly, size);
            g_days = getSlicedData2(interval, ref_d_days, ref_w_days, ref_m_days, size);

            // Bug que je n'arrive pas � corriger autrement !
            if (g_days.length > g_new_data.length) g_days.pop();

            array_years = extractFirstDateYear(g_days);

            return g_days.length;
        }

        update_graphe_buttons = function(bt) {
            c2 = '<?= $bt_grey_colr ?>';
            addCN(bt, 'loading');
            if (bt == 'graphe_1T_bt' || bt == 'graphe_1Y_bt' || bt == 'graphe_3Y_bt' || bt == 'graphe_all_bt') {
                c1 = '<?= $bt_period_colr ?>';
                ['graphe_1T_bt', 'graphe_1Y_bt', 'graphe_3Y_bt', 'graphe_all_bt'].forEach((bt) => {
                    replaceCN(bt, c1, c2);
                });
            }
            if (bt == 'graphe_D_bt' || bt == 'graphe_W_bt' || bt == 'graphe_M_bt') {
                c1 = '<?= $bt_interval_colr ?>';
                ['graphe_D_bt', 'graphe_W_bt', 'graphe_M_bt'].forEach((bt) => {
                    replaceCN(bt, c1, c2);
                });
            }
            switchCN(bt, c1, c2);
        }

        update_graph_chart = function(c, ctx, opts, lbls, dtsts, plg) {

            try {
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

            } catch (e) {
                alert('update_graphe_chart error ! : ' + e);
            }

            return c;
        }

        update_all_charts = function(bt) {

            // Si aucune donn�e, pas d'affichage de graphe
            if (!new_data_daily || new_data_daily.length == 0) {
               hide('canvas_area');
               return;
            }
            
            // Ajustement des buttons
            update_graphe_buttons(bt);

            // Ajustement des donn�es
            var nb_items = update_data(interval_period_days[getIntervalStatus()][getPeriodStatus()]);

            options_Stock_Graphe.scales.y1.type = isCN('graphe_scale_bt', '<?= $bt_mmx_colr ?>') ? 'logarithmic' : 'linear';

            // Animation dynamique retiree dans option graphe pour plus de fluidite
            if (g_new_data.length > 2000) {
                options_Stock_Graphe.animation = false;
                options_RSI_Graphe.animation = false;
                options_DM_Graphe.animation = false;
            }

            // En attendant d'avoir un plugin ou de corriger le pb de volumetrie on limite le daily a max 3000 inputs
            max_data = 100000;
            if (false && g_new_data.length > max_data) {
                g_days = g_days.slice(min_slice(g_days, max_data), max_slice(g_days));
                g_new_data = g_new_data.slice(min_slice(g_new_data, max_data), max_slice(g_new_data));
            }

            // Update Chart Stock
            var datasets1 = [];
            options_Stock_Graphe.plugins.vertical = [];
            options_Stock_Graphe.plugins.insiderText = [];

            // Ajout des data valeurs de cotation
            datasets1.push(getDatasetVals(g_new_data));
            // Ajout des MM & VOLUME en fct si bouton allume
            if (isCN('graphe_mm7_bt', '<?= $bt_mmx_colr ?>')) datasets1.push(getDatasetMMX(g_new_data, 'mm7', 'MM7'));
            if (isCN('graphe_mm20_bt', '<?= $bt_mmx_colr ?>')) datasets1.push(getDatasetMMX(g_new_data, 'mm20', 'MM20'));
            if (isCN('graphe_mm50_bt', '<?= $bt_mmx_colr ?>')) datasets1.push(getDatasetMMX(g_new_data, 'mm50', 'MM50'));
            if (isCN('graphe_mm200_bt', '<?= $bt_mmx_colr ?>')) datasets1.push(getDatasetMMX(g_new_data, 'mm200', 'MM200'));
            if (isCN('graphe_reg_bt', '<?= $bt_mmx_colr ?>')) {
                ['', 1, 2, 3, 4].forEach(function(i) {
                    datasets1.push(getDatasetMMX(g_new_data, 'reg' + i, 'REG' + i));
                });
            }
            if (isCN('graphe_volume_bt', '<?= $bt_volume_colr ?>')) datasets1.push(getDatasetVols(g_new_data, 'VOLUME'));

            // MIN/MAX/PRU/STOPLOSS/STOPPROFIT/OBJECTIF (pour verifier si axe y ok)
            limits_ctrl = [];
            <?
            foreach (['stop_loss', 'stop_profit', 'objectif'] as $key => $val)
                if (isset($lst_trend_following[$symbol][$val]) && $lst_trend_following[$symbol][$val] > 0)
                    echo sprintf("limits_ctrl.push(%s);", $lst_trend_following[$symbol][$val]);
            ?>;

            // MIN/MAX values cotations
            min_val = 999999999;
            max_val = 0;
            g_new_data.forEach(function(item) {
                if (item.y > max_val) max_val = item.y;
                if (item.y < min_val) min_val = item.y;
            });
            l_min = min_val;
            l_max = max_val;
            limits_ctrl.forEach(function(item) {
                if (l_min > item) l_min = item;
                if (l_max < item) l_max = item;
            });


            // Data pour les lignes horizontales
            h_lines = [];

            if (position_pru > 0) {
                h_lines.push({
                    lineColor: 'orange',
                    yPosition: position_pru,
                    text: 'PRU',
                    lineDash: [2, 2]
                });
            }

            if (min_val > 0) {
                h_lines.push({
                    lineColor: 'red',
                    yPosition: min_val,
                    text: 'MIN',
                    lineDash: [2, 2]
                });
            }

            if (max_val < 999999999) {
                h_lines.push({
                    lineColor: 'green',
                    yPosition: max_val,
                    text: 'MAX',
                    lineDash: [2, 2]
                });
            }

            // Options des alertes
            options_Stock_Graphe.plugins.horizontal = is_user_connected && isCN('graphe_alarm_bt', '<?= $bt_alarm_colr ?>') ? h_lines : [];
            options_Stock_Graphe.plugins.rightAxeText = axe_infos;
            options_Stock_Graphe.plugins.bubbles = is_user_connected && isCN('graphe_av_bt', '<?= $bt_av_colr ?>') ? bubbles_data : [];
            options_Stock_Graphe.scales['y1'].max = l_max * 1.1;
            options_Stock_Graphe.scales['y1'].min = l_min * 0.7;
            coef = getIntervalStatus() == 'D' ? 1000 : (getIntervalStatus() == 'W' ? 100 : 25);
            options_Stock_Graphe.scales['y2'].max = <?= round($top_value_volume / 1.5, 0) ?> / coef;
            options_Stock_Graphe.scales['y2'].min = 0;
            options_Stock_Graphe.scales['y2'].stepSize = <?= round($top_value_volume / (1.5 * 5), 0) ?> / coef;
            myChart1 = update_graph_chart(myChart1, ctx1, options_Stock_Graphe, g_days, datasets1, [insiderText, rightAxeText, horizontal, vertical, bubbles]);

            // Update Chart RSI
            var datasets2 = [];
            datasets2.push(getDatasetRSI14(g_new_data));
            options_RSI_Graphe.plugins.insiderText = [{
                title: 'RSI14',
                colr: '#e77fe8',
                bgcolr: '#1b1c1d',
                alignX: 'left',
                alignY: 'top'
            }];
            myChart2 = update_graph_chart(myChart2, ctx2, options_RSI_Graphe, g_days, datasets2, [insiderText, horizontalLines_RSI_Graphe]);

            // Update Chart DM
            var datasets3 = [];
            datasets3.push(getDatasetDM(g_new_data));
            options_DM_Graphe.scales.y.position = 'right';
            options_DM_Graphe.plugins.insiderText = [{
                title: 'Evolution DM',
                colr: 'yellow',
                bgcolr: '#1b1c1d',
                alignX: 'left',
                alignY: 'top'
            }];
            myChart3 = update_graph_chart(myChart3, ctx3, options_DM_Graphe, g_days, datasets3, [insiderText, horizontalLines_DM_Graphe]);

            rmCN(bt, 'loading');
        }

        if (getCookie('status_stock_bt_reg', 0) == 1) switchCN('graphe_reg_bt', '<?= $bt_grey_colr ?>', '<?= $bt_mmx_colr ?>');
        if (getCookie('status_stock_bt_scale', 0) == 1) switchCN('graphe_scale_bt', '<?= $bt_grey_colr ?>', '<?= $bt_mmx_colr ?>');

        // Initialisation des graphes
        update_all_charts('graphe_all_bt');

        var p = loadPrompt();

        // Listener sur bt MMX + volume + alarm
        Object.entries(mm_bts).forEach(([key, val]) => {
            Dom.addListener(Dom.id(key), Dom.Event.ON_CLICK, function(event) {
                toogleMMX(myChart1, val);
            });
        });

        // Listener sur bt 1T, 1Y, 3Y, ALL, D, W, M
        ['graphe_1T_bt', 'graphe_1Y_bt', 'graphe_3Y_bt', 'graphe_all_bt', 'graphe_D_bt', 'graphe_W_bt', 'graphe_M_bt'].forEach((item) => {
            Dom.addListener(Dom.id(item), Dom.Event.ON_CLICK, function(event) {
                update_all_charts(item);
            });
        });

    <? } ?>






    <? if (!$readonly) { ?>

        getFormValues = function() {
            params = attrs(['f_isin', 'f_provider', 'f_frais', 'f_actifs', 'f_gf_symbol', 'f_rating', 'f_distribution', 'f_type', 'f_link1', 'f_link2', 'f_dividende', 'f_date_dividende', 'f_emetteur', 'f_ticker', 'f_levier', 'f_sousjacent', 'f_val_init', 'f_val_prev']) + '&pea=' + (valof('f_pea') == 0 ? 0 : 1) + '&f_expire=' + (valof('f_expire') == 0 ? 0 : 1);

            var tags = '';
            Dom.find('button.bt_tags').forEach(function(item) {
                if (isCN(item.id, '<?= $bt_interval_colr ?>')) tags += item.innerHTML + '|';
            });
            params += '&f_tags=' + tags;

            return params;
        }

        // Listener sur bt edit
        Dom.addListener(Dom.id('stock_edit_bt'), Dom.Event.ON_CLICK, function(event) {

            [{
                key: 'f_dividende',
                label: 'Dividende'
            }].forEach(function(item) {
                if (valof(item.key) != '' && !format_and_check_num(item.key, item.label, 0, 999999999999)) return false;
            });

            p = getFormValues();
            go({
                action: 'update',
                id: 'main',
                url: 'stock_action.php?action=upt&symbol=<?= $symbol ?>&ptf_id=<?= $ptf_id ?>' + p,
                loading_area: 'main'
            });
            toogleCN('nav_menu', 'on');
            scroll(0, 0);
        });

        // Listener sur indicators
        Dom.addListener(Dom.id('stock_indic_bt'), Dom.Event.ON_CLICK, function(event) {
            go({
                action: 'update',
                id: 'main',
                url: 'stock_action.php?action=indic&symbol=<?= $symbol ?>',
                loading_area: 'main'
            });
            toogleCN('nav_menu', 'on');
            scroll(0, 0);
        });

        // Listener sur bt reload
        Dom.addListener(Dom.id('stock_reload_bt'), Dom.Event.ON_CLICK, function(event) {
            go({
                action: 'update',
                id: 'main',
                url: 'stock_action.php?action=reload&symbol=<?= $symbol ?>',
                loading_area: 'main'
            });
            toogleCN('nav_menu', 'on');
            scroll(0, 0);
        });

        // Gestion menu secondaire
        Dom.addListener(Dom.id('nav_menu_bt'), Dom.Event.ON_CLICK, function(event) {
            el('nav_menu').style.top = "-150px";
            toogleCN('nav_menu', 'on');
        });

    <? } ?>


    // Choix actif dans select actif ptf
    <? if ($readonly && $ptf_nb_positions > 0) { ?>
        Dom.addListener(Dom.id('ptf_select_bt'), Dom.Event.ON_CHANGE, function(event) {
            element = Dom.id('ptf_select_bt');
            var selection = "";
            for (i = 0; i < element.length; i++)
                if (element[i].selected) selection = element[i].value;
            if (selection != "") go({
                action: 'stock_detail',
                id: 'main',
                url: 'stock_detail.php?symbol=' + selection,
                loading_area: 'main'
            });
        });
    <? } ?>

    <? if (!$readonly) { ?>
    showHideTurbo = function() {
        element = Dom.id('f_type');
        if (element) {
            var selection = "";
            for (i = 0; i < element.length; i++)
                if (element[i].selected) selection = element[i].value;        
            hide('turbo_area');
            if (selection == "PUT" || selection == "CALL") showflex('turbo_area');
        }
    };
    Dom.addListener(Dom.id('f_type'), Dom.Event.ON_CHANGE, function(event) { showHideTurbo(); });
    <? } ?>

    <? if ($qc->getQuoteAttr('type') != 'CALL' && $qc->getQuoteAttr('type') != 'PUT') { ?>
        hide('turbo_area');
    <? } ?> 

    // Changement etat bouttons tags
    changeState = function(item) {
        switchColorElement(item.id, '<?= $bt_interval_colr ?>', '<?= $bt_grey_colr ?>');
    }

    // Listener sur boutons tags
    Dom.find('button.bt_tags').forEach(function(item) {
        Dom.addListener(item, Dom.Event.ON_CLICK, function(event) {
            changeState(item);
        });
    });

    // Listener sur boutons edit orders
    Dom.find('#lst_order i.edit').forEach(function(item) {
        Dom.addListener(item, Dom.Event.ON_CLICK, function(event) {
            let order_attributes = Dom.attribute(item, 'id').split("_");
            if (order_attributes.length > 4)
                go({
                    action: 'order',
                    id: 'main',
                    url: 'order_detail.php?action=upt&from_stock_detail=1&portfolio_id=' + order_attributes[3] + '&order_id=' + order_attributes[2],
                    loading_area: 'main'
                });
        });
    });

    // Listener sur bt back
    Dom.addListener(Dom.id('stock_back_bt'), Dom.Event.ON_CLICK, function(event) {
        go({
            action: 'home',
            id: 'main',
            url: '<?= $ptf_id == "" ?  "home_content.php" : ($ptf_id == -1 ? "watchlist.php" : "portfolio_dashboard.php?portfolio_id=" . $ptf_id) ?>',
            loading_area: 'main'
        });
    });

    <? if ($readonly && $sess_context->isSuperAdmin()) { ?>
        // Listener sur bt edit
        Dom.addListener(Dom.id('stock_edit_bt'), Dom.Event.ON_CLICK, function(event) {
            go({
                action: 'home',
                id: 'main',
                url: 'stock_detail.php?edit=1&symbol=<?= $symbol ?>&ptf_id=<?= $ptf_id ?>',
                loading_area: 'main'
            });
        });

        // Listener sur bt add order
        Dom.addListener(Dom.id('order_add_bt'), Dom.Event.ON_CLICK, function(event) {
            go({
                action: 'order',
                id: 'main',
                url: 'order_detail.php?action=new&symbol=<?= $symbol ?>&from_stock_detail=1&portfolio_id=<?= $ptf_id ?>',
                loading_area: 'main'
            });
        });
    <? } ?>

    <? if ($sess_context->isSuperAdmin()) { ?>
        // Listener sur bt delete
        Dom.addListener(Dom.id('stock_delete_bt'), Dom.Event.ON_CLICK, function(event) {
            go({
                action: 'delete',
                id: 'main',
                url: 'stock_action.php?action=del&symbol=<?= $symbol ?>',
                loading_area: 'main',
                confirmdel: 1
            });
        });
    <? } ?>


    // On parcours les lignes du tableau positions pour calculer valo, perf, gain, atio et des tooltip du tableau des positions
    updateDataPage = function(opt) {

        if (opt == 'init') {}
        trendfollowing_ui.computePositionsTable('lst_position', <?= $ptf_id ?>);

    }('init');

    // Pagination
    if (Dom.id('lst_order')) {
        paginator({
            table: document.getElementById("lst_order"),
            box: document.getElementById("pagination_box")
        });

        // Tri sur tableau
        Sortable.initTable(el("lst_order"));
    }

    scroll(0, 0); // Top de page
</script>