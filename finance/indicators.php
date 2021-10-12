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

    $t = TraderFriendly::momentum(array_column($data, "close"), $size);

// Pas sur que ce soit bon, a revoir si on veut reprendre l'ancienne methode
/*     $tab = array();
    $x = array_reverse($data);

    while(count($x) > $size) {
        $removed = array_shift($x);
        $item = current($data);
        $tab[] = calc::processDataDM($item['day'], array("quote" => array(), "data" => $x));
    }

    $t2 = fullFillArray($data, array_column($tab, "MMZDM"));
    tools::pretty($t2);

    tools::pretty(fullFillArray($data, $t));
 */
    return fullFillArray($data, $t);
}

function insertIntoTimeSeries($symbol, $data, $table) {

    foreach($data['lastday'] as $key => $val) {
        $open   = $data["open"][$key];
        $high   = $data["high"][$key];
        $low    = $data["low"][$key];
        $close  = $data["close"][$key];
        $volume = $data["volume"][$key];

        $req = "INSERT INTO ".$table." (symbol, day, open, high, low, close, volume) VALUES('".$symbol."', '".$val."', '".$open."', '".$high."', '".$low."', '".$close."', '".$volume."') ON DUPLICATE KEY UPDATE open='".$open."', high='".$high."', low='".$low."', close='".$close."', volume='".$volume."'";
        $res = dbc::execSql($req);
    }

    return $res;
    
}

function insertIntoIndicators($symbol, $data, $period) {

    $tab_days  = array_column($data, "day");
    $tab_close = array_column($data, "close");

    if (count($tab_days) == 0) { logger::info("INDS", $symbol, "NO ".$period." DATA !!!!"); return; }

    $tab_MM7   = computeMMX($tab_close, 7);
    $tab_MM20  = computeMMX($tab_close, 20);
    $tab_MM50  = computeMMX($tab_close, 50);
    $tab_MM200 = computeMMX($tab_close, 200);
    $tab_RSI14 = computeRSIX($tab_close, 14);
    $tab_DM132 = computeDMX($data, 132);

    foreach($tab_days as $key => $val) {
        $MM7   = currentnext($tab_MM7);
        $MM20  = currentnext($tab_MM20);
        $MM50  = currentnext($tab_MM50);
        $MM200 = currentnext($tab_MM200);
        $RSI14 = currentnext($tab_RSI14);
        $DM    = currentnext($tab_DM132);

        $req = "INSERT INTO indicators (symbol, day, period, DM, MM7, MM20, MM50, MM200, RSI14) VALUES('".$symbol."', '".$val."', '".$period."', '".$DM."', '".$MM7."', '".$MM20."', '".$MM50."', '".$MM200."', '".$RSI14."') ON DUPLICATE KEY UPDATE DM='".$DM."', MM7='".$MM7."', MM20='".$MM20."', MM50='".$MM50."', MM200='".$MM200."', RSI14='".$RSI14."'";
        $res = dbc::execSql($req);
    }

}

function calculMoyenne($tab_data) {

    foreach($tab_data['close'] as $key => $val) {
        $tab_data['open'][$key]  = round($tab_data['open'][$key]  / $tab_data['counter'][$key], 8);
        $tab_data['high'][$key]  = round($tab_data['high'][$key]  / $tab_data['counter'][$key], 8);
        $tab_data['low'][$key]   = round($tab_data['low'][$key]   / $tab_data['counter'][$key], 8);
        $tab_data['close'][$key] = round($tab_data['close'][$key] / $tab_data['counter'][$key], 8);
    }

    return $tab_data;

}

/* function cumulValuesAndRSI($tab_data, $ind, $row) {

    $tab_data["volume"][$ind] = cumulTabVal($tab_data["volume"], $ind,  $row['volume']);
    $tab_data["open"][$ind]   = cumulTabVal($tab_data["open"],  $ind,   $row['open']);
    $tab_data["high"][$ind]   = cumulTabVal($tab_data["high"],  $ind,   $row['high']);
    $tab_data["low"][$ind]    = cumulTabVal($tab_data["low"],   $ind,   $row['low']);
    $tab_data["close"][$ind]  = cumulTabVal($tab_data["close"], $ind,   $row['close_value']);

    return $tab_data;
}
 */

// //////////////////////////////////////////////////////////////
// Cumul des Daily en Weekly/Monthly
// //////////////////////////////////////////////////////////////
function aggregateWeeklyMonthlySymbol($symbol, $filter_limited) {

    $tab_weekly  = [ "counter" => array(), "lastdays" => array(), "volume" => array(), "open" => array(), "high" => array(), "low" => array(), "close" => array() ];
    $tab_monthly = [ "counter" => array(), "lastdays" => array(), "volume" => array(), "open" => array(), "high" => array(), "low" => array(), "close" => array() ];

    $req = "SELECT * FROM daily_time_series_adjusted WHERE symbol=\"".$symbol."\"".($filter_limited == 1 ? " ORDER BY day DESC LIMIT 210) subq ORDER BY day ASC" : "");
    $res= dbc::execSql($req);
    while($row = mysqli_fetch_assoc($res)) {

        // On prend la valeur de cloture ajustée pour avoir les courbes cohérentes
        $row['close'] = isset($row['adjusted_close']) && is_numeric($row['adjusted_close']) ? $row['adjusted_close'] : $row['close'];

        $week  = date("Y-W", strtotime($row['day']));
        $month = date("Y-m", strtotime($row['day']));

        // Cummul weekly et monthly pour calcul RSI14 weekly et monthly
        foreach(['volume', 'open', 'high', 'low', 'close'] as $key)
            $tab_weekly[$key][$week] = cumulTabVal($tab_weekly[$key], $week, $row[$key]);

        foreach(['volume', 'open', 'high', 'low', 'close'] as $key)
            $tab_monthly[$key][$month] = cumulTabVal($tab_monthly[$key], $month, $row[$key]);

        // On compte le nb de jours par week/month
        $tab_weekly['counter'][$week]   = isset($tab_weekly['counter'][$week])   ? $tab_weekly['counter'][$week] + 1   : 1;
        $tab_monthly['counter'][$month] = isset($tab_monthly['counter'][$month]) ? $tab_monthly['counter'][$month] + 1 : 1;

        // on garde le dernier par week/month
        $tab_weekly['lastday'][$week]   = $row['day'];
        $tab_monthly['lastday'][$month] = $row['day'];
    }

    // Calcul des moyennes weekly/monthly en divisant par le nb de dates
    $tab_weekly  = calculMoyenne($tab_weekly);
    $tab_monthly = calculMoyenne($tab_monthly);

    // INSERT WEEKLY AND MONTHLY DATA
    insertIntoTimeSeries($symbol, $tab_weekly,  'weekly_time_series_adjusted');
    insertIntoTimeSeries($symbol, $tab_monthly, 'monthly_time_series_adjusted');

    logger::info("AGGR", $symbol, "[weekly=".count($tab_weekly['lastday'])."] [monthly=".count($tab_monthly['lastday'])."]");
}

// //////////////////////////////////////////////////////////////
// Calcul MM7, MM20, MM50, MM200, RSI14 en Daily/Weekly/Monthly
// //////////////////////////////////////////////////////////////
function computePeriodIndicatorsSymbol($symbol, $filter_limited, $period) {

    $table = strtolower($period)."_time_series_adjusted";

    $data = array();

    $req = "SELECT * FROM ".$table." WHERE symbol=\"".$symbol."\"".($filter_limited == 1 ? " ORDER BY day DESC LIMIT 210) subq ORDER BY day ASC" : "");
    $res= dbc::execSql($req);
    while($row = mysqli_fetch_assoc($res)) {

        // On prend la valeur de cloture ajustée pour avoir les courbes cohérentes
        $row['close'] = isset($row['adjusted_close']) && is_numeric($row['adjusted_close']) ? $row['adjusted_close'] : $row['close'];
        $data[] = $row;
    }

    // INSERT ALL INDICATORS
    insertIntoIndicators($symbol, $data, $period);
    
    logger::info("INDIC", $symbol, "[".$period."] [count=".count($data)."]");
}

// //////////////////////////////////////////////////////////////
// Calcul MM7, MM20, MM50, MM200, RSI14 en Daily/Weekly/Monthly
// //////////////////////////////////////////////////////////////
function computeIndicatorsSymbol($symbol, $filter_limited, $aggregate = false) {

    if ($aggregate) aggregateWeeklyMonthlySymbol($symbol, $filter_limited);

    foreach(['DAILY', 'WEEKLY', 'MONTHLY'] as $key)
        computePeriodIndicatorsSymbol($symbol, $filter_limited, $key);

}

function computeIndicators($filter_symbol, $filter_limited) {

    // Selection du/des actif(s) à prendre en charge
    $req = "SELECT * FROM stocks WHERE symbol LIKE \"%".$filter_symbol."%\"";
    $res = dbc::execSql($req);
    while($row = mysqli_fetch_assoc($res))
        computeIndicatorsSymbol($row['symbol'], $filter_limited);

}

function resetData($filter) {
    $sql = "DELETE FROM indicators WHERE symbol LIKE \"%".$filter."%\"";
    $res= dbc::execSql($sql);
    $sql = "DELETE FROM weekly_time_series_adjusted WHERE symbol LIKE \"%".$filter."%\"";
    $res= dbc::execSql($sql);
    $sql = "DELETE FROM monthly_time_series_adjusted WHERE symbol LIKE \"%".$filter."%\"";
    $res= dbc::execSql($sql);
}

// FORCE Computing
$force   = 0;
$reset   = 0;
$limited = 0;
$filter  = "";

foreach(['force', 'reset', 'limited', 'filter'] as $key)
    $$key = isset($_GET[$key]) ? $_GET[$key] : (isset($$key) ? $$key : "");

if ($reset == 1) resetData($filter);

if ($force == 1) {

    $db = dbc::connect();

    logger::info("DIRECT", "---------", "---------------------------------------------------------");

    computeIndicators($filter, $limited);

    logger::info("DIRECT", "---------", "---------------------------------------------------------");

    echo "Done";
}

