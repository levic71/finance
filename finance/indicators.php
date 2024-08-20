<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
// //////////////////////////////////////////


// Calculer les cours WEEKLY et MONTHLY 
// Ajouter RSI14 DAILY/WEEKDY/MONTHLY
// Ajouter à la crontab avec calcul sur les jours non calculé ou sur la veille

require_once "include.php";

include "Trader.php";
include "TraderFriendly.php";
include "TALib/Enum/Compatibility.php";
include "TALib/Enum/MovingAverageType.php";
include "TALib/Enum/ReturnCode.php";
include "TALib/Enum/UnstablePeriodFunctionID.php";
include "TALib/Enum/RangeType.php";
include "TALib/Enum/CandleSettingType.php";
include "TALib/Classes/CandleSetting.php";
include "TALib/Classes/MoneyFlow.php";
include "TALib/Core/Core.php";
include "TALib/Core/Lookback.php";
include "TALib/Core/OverlapStudies.php";
include "TALib/Core/MomentumIndicators.php";

use LupeCode\phpTraderNative\TraderFriendly;

function cumulTabVal($tab, $key, $val) {
    if (!is_numeric($val)) var_dump(debug_backtrace());
    return isset($tab[$key]) ? $tab[$key] + $val : $val;
}
function getFirstTabVal($tab, $key, $val) {
    if (!is_numeric($val)) var_dump(debug_backtrace());
    return isset($tab[$key]) ? $tab[$key] : $val;
}
function getLastTabVal($tab, $key, $val) {
    if (!is_numeric($val)) var_dump(debug_backtrace());
    return $val;
}
function currentnext(&$t) { $res = current($t); next($t); return $res; }
function fullFillArray($t1, $t2) {
    // On complete le tableau t2 avec la premiere valeur du tableau t2 pour qu'il ait la meme longueur que le tableau t1
    $val = count($t2) > 0 ? $t2[array_key_first($t2)] : 0;

    if (count($t1) > count($t2)) 
        $t2 = array_merge(array_fill(0, (count($t1) - count($t2)), $val), $t2);

    return $t2;
}
function fullFillArrayAvg($t1, $t2) {
    $r = count($t1)-count($t2);
    for($i=0; $i < $r; $i++) {
        $t2[$i] = round(array_sum(array_slice($t1, 0, $i+1))/($i+1), 8);
    }
    ksort($t2);

    return $t2;
}
function ComputeMMX($data, $size) {
    $t = TraderFriendly::simpleMovingAverage($data, $size);
    return fullFillArrayAvg($data, $t);
}
function ComputeRSIX($data, $size) {
    $t = TraderFriendly::relativeStrengthIndex($data, $size);
    return fullFillArray($data, $t);
}
function ComputeDMX($data, $size) {

    // Indexation du tableau avec la date comme cle
    foreach(array_reverse($data) as $key => $val)
        $x[$val['day']] = $val;

    // Calcul DM/YTD/...
    $tab = calc::processDataDM($x);

    // Tri date ascendente pour revenir en nominal
    $z = array_reverse($tab);

    return array(
        "YTD"  => fullFillArray($data, array_column($z, "var_YTD")),
        "1W"   => fullFillArray($data, array_column($z, "var_1W")),
        "1M"   => fullFillArray($data, array_column($z, "var_1M")),
        "1Y"   => fullFillArray($data, array_column($z, "var_1Y")),
        "3Y"   => fullFillArray($data, array_column($z, "var_3Y")),
        "DM"   => fullFillArray($data, array_column($z, "MMZDM")),
        "DMD1" => fullFillArray($data, array_column($z, "MMZ1MDate")),
        "DMD2" => fullFillArray($data, array_column($z, "MMZ3MDate")),
        "DMD3" => fullFillArray($data, array_column($z, "MMZ6MDate"))
    );
}

function insertIntoTimeSeries($symbol, $data, $table) {

    $res = true;

    foreach($data['lastday'] as $key => $val) {
        $open   = $data["open"][$key];
        $high   = $data["high"][$key];
        $low    = $data["low"][$key];
        $close  = $data["close"][$key];
        $adjusted_close  = $data["adjusted_close"][$key];
        $volume = $data["volume"][$key];

        $req = "INSERT INTO ".$table." (symbol, day, open, high, low, close, adjusted_close, volume) VALUES('".$symbol."', '".$val."', '".$open."', '".$high."', '".$low."', '".$close."', '".$adjusted_close."', '".$volume."') ON DUPLICATE KEY UPDATE open='".$open."', high='".$high."', low='".$low."', close='".$close."', adjusted_close='".$adjusted_close."', volume='".$volume."'";
        $res = dbc::execSql($req);
    }

    return $res;  
}

function insertIntoIndicators($symbol, $day, $period, $item) {

    $item["DMD1"] = $item["DMD1"] == 0 ? $day : $item["DMD1"];
    $item["DMD2"] = $item["DMD2"] == 0 ? $day : $item["DMD2"];
    $item["DMD3"] = $item["DMD3"] == 0 ? $day : $item["DMD3"];

    $req = "INSERT INTO indicators (symbol, day, period, DM, DMD1, DMD2, DMD3, MM7, MM20, MM50, MM100, MM200, RSI14, ytd, 1w, 1m, 1y, 3y) VALUES('".$symbol."', '".$day."', '".strtoupper($period)."', '".$item["DM"]."', '".$item["DMD1"]."', '".$item["DMD2"]."', '".$item["DMD3"]."', '".$item["MM7"]."', '".$item["MM20"]."', '".$item["MM50"]."', '".$item["MM100"]."', '".$item["MM200"]."', '".$item["RSI14"]."', '".$item["YTD"]."', '".$item["1W"]."', '".$item["1M"]."', '".$item["1Y"]."', '".$item["3Y"]."') ON DUPLICATE KEY UPDATE DM='".$item["DM"]."', DMD1='".$item["DMD1"]."', DMD2='".$item["DMD2"]."', DMD3='".$item["DMD3"]."', MM7='".$item["MM7"]."', MM20='".$item["MM20"]."', MM50='".$item["MM50"]."', MM100='".$item["MM100"]."', MM200='".$item["MM200"]."', RSI14='".$item["RSI14"]."', ytd='".$item["YTD"]."', 1w='".$item["1W"]."', 1m='".$item["1M"]."', 1y='".$item["1Y"]."', 3y='".$item["3Y"]."'";
    $res = dbc::execSql($req);

    // Mise à jour de la date de rafraichissement des indicators
    $req = "UPDATE stocks SET last_indicators_update='".date('Y-m-d')."' WHERE symbol='".$symbol."'";
    $res = dbc::execSql($req);

}

// Si all = 0, on insert tout, sinon on insert le nb indiqué
function computeAndInsertIntoIndicators($symbol, $data, $period, $all = 0) {

    $ret = 0;

    $tab_days  = array_column($data, "day");
    $tab_close = array_column($data, "close");

    if (count($tab_days) == 0) { logger::info("INDS", $symbol, "NO ".$period." DATA !!!!"); return; }

    $tab_MM7   = computeMMX($tab_close, 7);
    $tab_MM20  = computeMMX($tab_close, 20);
    $tab_MM50  = computeMMX($tab_close, 50);
    $tab_MM100 = computeMMX($tab_close, 100);
    $tab_MM200 = computeMMX($tab_close, 200);
    $tab_RSI14 = computeRSIX($tab_close, 14);
    $tab_DM132 = computeDMX($data, 132);

    // Si all > 0, on ne retient que les derniers calculs souhaites
    if ($all > 0) {
        $tab_days  = array_slice($tab_days,  count($tab_days)  - $all);
        $tab_close = array_slice($tab_close, count($tab_close) - $all);
        $tab_MM7   = array_slice($tab_MM7,   count($tab_MM7)   - $all);
        $tab_MM20  = array_slice($tab_MM20,  count($tab_MM20)  - $all);
        $tab_MM50  = array_slice($tab_MM50,  count($tab_MM50)  - $all);
        $tab_MM100 = array_slice($tab_MM100, count($tab_MM100) - $all);
        $tab_MM200 = array_slice($tab_MM200, count($tab_MM200) - $all);
        $tab_RSI14 = array_slice($tab_RSI14, count($tab_RSI14) - $all);
        $tab_DM132['DM']   = array_slice($tab_DM132['DM'],   count($tab_DM132['DM'])   - $all);
        $tab_DM132['DMD1'] = array_slice($tab_DM132['DMD1'], count($tab_DM132['DMD1']) - $all);
        $tab_DM132['DMD2'] = array_slice($tab_DM132['DMD2'], count($tab_DM132['DMD2']) - $all);
        $tab_DM132['DMD3'] = array_slice($tab_DM132['DMD3'], count($tab_DM132['DMD3']) - $all);
        $tab_DM132['YTD']  = array_slice($tab_DM132['YTD'],  count($tab_DM132['YTD'])  - $all);
        $tab_DM132['1W']   = array_slice($tab_DM132['1W'],   count($tab_DM132['1W'])   - $all);
        $tab_DM132['1M']   = array_slice($tab_DM132['1M'],   count($tab_DM132['1M'])   - $all);
        $tab_DM132['1Y']   = array_slice($tab_DM132['1Y'],   count($tab_DM132['1Y'])   - $all);
        $tab_DM132['3Y']   = array_slice($tab_DM132['3Y'],   count($tab_DM132['3Y'])   - $all);
    }


    $item = array();
    foreach($tab_days as $key => $val) {
        $item["MM7"]   = Round(currentnext($tab_MM7), 9);
        $item["MM20"]  = Round(currentnext($tab_MM20), 9);
        $item["MM50"]  = Round(currentnext($tab_MM50), 9);
        $item["MM100"] = Round(currentnext($tab_MM100), 9);
        $item["MM200"] = Round(currentnext($tab_MM200), 9);
        $item["RSI14"] = Round(currentnext($tab_RSI14), 9);
        $item["DM"]    = Round(currentnext($tab_DM132['DM']), 9);
        $item["DMD1"]  = currentnext($tab_DM132['DMD1']);
        $item["DMD2"]  = currentnext($tab_DM132['DMD2']);
        $item["DMD3"]  = currentnext($tab_DM132['DMD3']);
        $item["YTD"]   = Round(currentnext($tab_DM132['YTD']), 9);
        $item["1W"]    = Round(currentnext($tab_DM132['1W']), 9);
        $item["1M"]    = Round(currentnext($tab_DM132['1M']), 9);
        $item["1Y"]    = Round(currentnext($tab_DM132['1Y']), 9);
        $item["3Y"]    = Round(currentnext($tab_DM132['3Y']), 9);

        insertIntoIndicators($symbol, $val, $period, $item);

        $ret++;
    }

    return $ret;
}

function computeAndInsertIndicatorsAllDates($symbol, $data, $period, $all = 0) {
    return computeAndInsertIntoIndicators($symbol, $data, $period, $all);
}

function computeAndInsertIndicatorsLastDate($symbol, $data, $period) {
    return computeAndInsertIntoIndicators($symbol, $data, $period, 1);
}

function calculMoyenne($tab_data) {

    foreach($tab_data['close'] as $key => $val) {
        $tab_data['open'][$key]  = round($tab_data['open'][$key]  / $tab_data['counter'][$key], 8);
        $tab_data['close'][$key] = round($tab_data['close'][$key] / $tab_data['counter'][$key], 8);
        $tab_data['adjusted_close'][$key] = round($tab_data['adjusted_close'][$key] / $tab_data['counter'][$key], 8);
    }

    return $tab_data;

}

// //////////////////////////////////////////////////////////////
// Cumul des Daily en Weekly/Monthly
// //////////////////////////////////////////////////////////////
function aggregateWeeklyMonthlySymbol($symbol, $limited = 0) {

    $tab_weekly  = [ "counter" => array(), "lastdays" => array(), "volume" => array(), "open" => array(), "high" => array(), "low" => array(), "close" => array(), "adjusted_close" => array() ];
    $tab_monthly = [ "counter" => array(), "lastdays" => array(), "volume" => array(), "open" => array(), "high" => array(), "low" => array(), "close" => array(), "adjusted_close" => array() ];

    // Requete a revoir sur le subq (300 car il m'en faut 30 + 200 = 230 mim pour calcul MM200) => plutot 800 pour 3Y
    $req = "SELECT * FROM daily_time_series_adjusted WHERE symbol=\"".$symbol."\"".($limited == 1 ? " ORDER BY day DESC LIMIT 800) subq ORDER BY day ASC" : "");
    $res= dbc::execSql($req);
    while($row = mysqli_fetch_assoc($res)) {

        // On prend la valeur de cloture ajustée pour avoir les courbes cohérentes
        if (isset($row['adjusted_close']) && is_numeric($row['adjusted_close']) && $row['close'] != $row['adjusted_close']) {

            // Ajustement proportionnel des valeurs high, low, open
            $row['open'] = ($row['open'] * $row['adjusted_close']) / $row['close'];
            $row['high'] = ($row['high'] * $row['adjusted_close']) / $row['close'];
            $row['low']  = ($row['low']  * $row['adjusted_close']) / $row['close'];

            // Ajustement close sur adjusted_close
            $row['close'] = $row['adjusted_close'];
        }

        $week  = date("Y-W", strtotime($row['day']));
        $month = date("Y-m", strtotime($row['day']));

        // Cummul volume
        foreach(['volume'] as $key) {
            $tab_weekly[$key][$week]   = cumulTabVal($tab_weekly[$key], $week, $row[$key]);
            $tab_monthly[$key][$month] = cumulTabVal($tab_monthly[$key], $month, $row[$key]);
        }

        // Selection open
        foreach(['open'] as $key) {
            $tab_weekly[$key][$week]   = getFirstTabVal($tab_weekly[$key], $week, $row[$key]);
            $tab_monthly[$key][$month] = getFirstTabVal($tab_monthly[$key], $month, $row[$key]);
        }

        // Selection close
        foreach(['close', 'adjusted_close'] as $key) {
            $tab_weekly[$key][$week]   = getLastTabVal($tab_weekly[$key], $week, $row[$key]);
            $tab_monthly[$key][$month] = getLastTabVal($tab_monthly[$key], $month, $row[$key]);
        }

        // Max/min
        $tab_weekly['high'][$week] = isset($tab_weekly['high'][$week]) ? max($tab_weekly['high'][$week], $row['high']) : $row['high'];
        $tab_weekly['low'][$week]  = isset($tab_weekly['low'][$week])  ? min($tab_weekly['low'][$week],  $row['low'])  : $row['low'];
        
        $tab_monthly['high'][$month] = isset($tab_monthly['high'][$month]) ? max($tab_monthly['high'][$month], $row['high']) : $row['high'];
        $tab_monthly['low'][$month]  = isset($tab_monthly['low'][$month])  ? min($tab_monthly['low'][$month],  $row['low'])  : $row['low'];


        // On compte le nb de jours par week/month
        $tab_weekly['counter'][$week]   = isset($tab_weekly['counter'][$week])   ? $tab_weekly['counter'][$week] + 1   : 1;
        $tab_monthly['counter'][$month] = isset($tab_monthly['counter'][$month]) ? $tab_monthly['counter'][$month] + 1 : 1;

        // on garde le dernier par week/month
        $tab_weekly['lastday'][$week]   = $row['day'];
        $tab_monthly['lastday'][$month] = $row['day'];
    }

    // INSERT WEEKLY AND MONTHLY DATA
    insertIntoTimeSeries($symbol, $tab_weekly,  'weekly_time_series_adjusted');
    insertIntoTimeSeries($symbol, $tab_monthly, 'monthly_time_series_adjusted');

    logger::info("AGGR", $symbol, "[weekly=".count($tab_weekly['lastday'])."] [monthly=".count($tab_monthly['lastday'])."]");
}

// //////////////////////////////////////////////////////////////
// Calcul DM, MM7, MM20, MM50, MM100, MM200, RSI14 en Daily/Weekly/Monthly
// //////////////////////////////////////////////////////////////
function computePeriodIndicatorsSymbol($symbol, $limited, $period) {

    $table = strtolower($period)."_time_series_adjusted";

    $data = array();

    if ($limited == 0)
        $req = "SELECT * FROM ".$table." WHERE symbol='".$symbol."'";
    else
        $req = "SELECT * FROM (SELECT * FROM ".$table." WHERE symbol='".$symbol."' ORDER BY day DESC LIMIT 800) subq ORDER BY day ASC";

    $res= dbc::execSql($req);
    while($row = mysqli_fetch_assoc($res)) {
        // On prend la valeur de cloture ajustée pour avoir les courbes cohérentes
        $row['close'] = isset($row['adjusted_close']) && is_numeric($row['adjusted_close']) ? $row['adjusted_close'] : $row['close'];
        $data[] = $row;
    }

    // INSERT INDICATORS
    $ret = computeAndInsertIndicatorsAllDates($symbol, $data, $period, $limited == 1 ? 30 : 0);
    
    logger::info("INDIC", $symbol, "[".$period."] [insert=".$ret.", data=".count($data)."]");
}

// //////////////////////////////////////////////////////////////
// Calcul DM, MM7, MM20, MM50, MM100, MM200, RSI14 du jour (table quotes)
// //////////////////////////////////////////////////////////////
function computeDailyIndicatorsSymbol($symbol) {

    $data = array();
    $add_today = true;

    $req2 = "SELECT * FROM quotes WHERE symbol=\"".$symbol."\"";
    $res2 = dbc::execSql($req2);

    if ($row2 = mysqli_fetch_assoc($res2)) {

        // price -> close et adjusted_close
        $row2['close'] = $row2['price'];
        $row2['adjusted_close'] = $row2['price'];

        $d1 = $row2['day'];

        $req = "SELECT * FROM (SELECT * FROM daily_time_series_adjusted WHERE symbol=\"".$symbol."\" ORDER BY day DESC LIMIT 800) subq ORDER BY day ASC";
        $res = dbc::execSql($req);
        while($row = mysqli_fetch_assoc($res)) {

            // On prend la valeur de cloture ajustée pour avoir les courbes cohérentes
            $row['close'] = isset($row['adjusted_close']) && is_numeric($row['adjusted_close']) ? $row['adjusted_close'] : $row['close'];
            $data[] = $row;

            if ($d1 == $row['day']) $add_today = false;

        }

        // On ajoute la last quote
        if ($add_today) $data[] = $row2;

        // INSERT ALL INDICATORS
        $ret = computeAndInsertIndicatorsLastDate($symbol, $data, "DAILY");
        
        logger::info("INDIC", $symbol, "[QUOTES] [insert=".$ret.", data=".count($data)."]");
    }
}

// //////////////////////////////////////////////////////////////
// Calcul MM7, MM20, MM50, MM100, MM200, RSI14 en Daily/Weekly/Monthly
// //////////////////////////////////////////////////////////////
function computeIndicatorsForSymbolWithOptions($symbol, $options = array("aggregate" => false, "limited" => 0, "periods" => ['DAILY', 'WEEKLY', 'MONTHLY'])) {

    if ($options['aggregate']) aggregateWeeklyMonthlySymbol($symbol, $options['limited']);

    foreach($options['periods'] as $key)
        computePeriodIndicatorsSymbol($symbol, $options['limited'], $key);

}

function resetData($filter) {
    $sql = "DELETE FROM indicators WHERE symbol LIKE \"%".$filter."%\"";
    $res= dbc::execSql($sql);
    $sql = "DELETE FROM weekly_time_series_adjusted WHERE symbol LIKE \"%".$filter."%\"";
    $res= dbc::execSql($sql);
    $sql = "DELETE FROM monthly_time_series_adjusted WHERE symbol LIKE \"%".$filter."%\"";
    $res= dbc::execSql($sql);
}

ini_set('max_execution_time', '300'); //300 seconds = 5 minutes

// FORCE Computing
$indicators_force   = 0;
$indicators_reset   = 0;
$indicators_limited = 0;
$indicators_filter  = "";
$indicators_aggregate = false;

foreach(['indicators_force', 'indicators_reset', 'indicators_limited', 'indicators_filter', 'indicators_aggregate'] as $key)
    $$key = isset($_GET[$key]) ? $_GET[$key] : (isset($$key) ? $$key : "");

if ($indicators_aggregate == 1) $indicators_aggregate = true;

$db = dbc::connect();

if ($indicators_reset == 1) resetData($indicators_filter);

if ($indicators_force == 1) {

    logger::info("DIRECT", "---------", "---------------------------------------------------------");

    // All indicators All preriods (D/W/M)
    $req = "SELECT * FROM stocks WHERE symbol LIKE \"%".$indicators_filter."%\"";
    $res = dbc::execSql($req);
    while($row = mysqli_fetch_assoc($res)) {
        computeDailyIndicatorsSymbol($row['symbol']);
        // computeIndicatorsForSymbolWithOptions($row['symbol'], $options = array("aggregate" => false, "limited" => 0, "periods" => ['DAILY']));
        // computeIndicatorsForSymbolWithOptions($row['symbol'], array("aggregate" => $indicators_aggregate, "limited" => 0, "periods" => $indicators_periods));         
    }

    cacheData::deleteTMPFiles();
    
    logger::info("DIRECT", "---------", "---------------------------------------------------------");

    echo "Done";
}

