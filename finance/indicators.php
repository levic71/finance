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

    $tab = array();

    // Tri date descendante pour le calcul avec ma methode
    $x = array_reverse($data);

    while(count($x) >= $size) {
        $item = current($data);
        $tab[] = calc::processDataDM($item['day'], array("quote" => array(), "data" => $x));
        array_shift($x);
    }


    // Tri date ascendente pour revenir en nominal
    $z = array_reverse($tab);

/*     foreach($z as $key => $val) tools::pretty($val); */

    return array(
        "DM"   => fullFillArray($data, array_column($z, "MMZDM")),
        "DMD1" => fullFillArray($data, array_column($z, "MMZ1MDate")),
        "DMD2" => fullFillArray($data, array_column($z, "MMZ3MDate")),
        "DMD3" => fullFillArray($data, array_column($z, "MMZ6MDate"))
    );
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

function insertIntoIndicators($symbol, $day, $period, $item) {
    $req = "INSERT INTO indicators (symbol, day, period, DM, DMD1, DMD2, DMD3, MM7, MM20, MM50, MM200, RSI14) VALUES('".$symbol."', '".$day."', '".strtoupper($period)."', '".$item["DM"]."', '".$item["DMD1"]."', '".$item["DMD2"]."', '".$item["DMD3"]."', '".$item["MM7"]."', '".$item["MM20"]."', '".$item["MM50"]."', '".$item["MM200"]."', '".$item["RSI14"]."') ON DUPLICATE KEY UPDATE DM='".$item["DM"]."', DMD1='".$item["DMD1"]."', DMD2='".$item["DMD2"]."', DMD3='".$item["DMD3"]."', MM7='".$item["MM7"]."', MM20='".$item["MM20"]."', MM50='".$item["MM50"]."', MM200='".$item["MM200"]."', RSI14='".$item["RSI14"]."'";
    $res = dbc::execSql($req);
}

// Si all=0 on insert tout, sinon on insert le nb indiqué
function computeAndInsertIntoIndicators($symbol, $data, $period, $all = 0) {

    $ret = 0;

    $tab_days  = array_column($data, "day");
    $tab_close = array_column($data, "close");

    if (count($tab_days) == 0) { logger::info("INDS", $symbol, "NO ".$period." DATA !!!!"); return; }

    $tab_MM7   = computeMMX($tab_close, 7);
    $tab_MM20  = computeMMX($tab_close, 20);
    $tab_MM50  = computeMMX($tab_close, 50);
    $tab_MM200 = computeMMX($tab_close, 200);
    $tab_RSI14 = computeRSIX($tab_close, 14);
    $tab_DM132 = computeDMX($data, 132);

    // On ne retient que le dernier calcul
    if ($all > 0) {
        $tab_days  = array_slice($tab_days,  count($tab_days)  - $all);
        $tab_close = array_slice($tab_close, count($tab_close) - $all);
        $tab_MM7   = array_slice($tab_MM7,   count($tab_MM7)   - $all);
        $tab_MM20  = array_slice($tab_MM20,  count($tab_MM20)  - $all);
        $tab_MM50  = array_slice($tab_MM50,  count($tab_MM50)  - $all);
        $tab_MM200 = array_slice($tab_MM200, count($tab_MM200) - $all);
        $tab_RSI14 = array_slice($tab_RSI14, count($tab_RSI14) - $all);
        $tab_DM132['DM']   = array_slice($tab_DM132['DM'],   count($tab_DM132['DM'])   - $all);
        $tab_DM132['DMD1'] = array_slice($tab_DM132['DMD1'], count($tab_DM132['DMD1']) - $all);
        $tab_DM132['DMD2'] = array_slice($tab_DM132['DMD2'], count($tab_DM132['DMD2']) - $all);
        $tab_DM132['DMD3'] = array_slice($tab_DM132['DMD3'], count($tab_DM132['DMD3']) - $all);
    }
/*  
    tools::pretty($tab_days);
    tools::pretty($tab_close);
    tools::pretty($tab_DM132['DM']);
    exit(0);
  */
    $item = array();
    foreach($tab_days as $key => $val) {
        $item["MM7"]   = currentnext($tab_MM7);
        $item["MM20"]  = currentnext($tab_MM20);
        $item["MM50"]  = currentnext($tab_MM50);
        $item["MM200"] = currentnext($tab_MM200);
        $item["RSI14"] = currentnext($tab_RSI14);
        $item["DM"]    = currentnext($tab_DM132['DM']);
        $item["DMD1"]  = currentnext($tab_DM132['DMD1']);
        $item["DMD2"]  = currentnext($tab_DM132['DMD2']);
        $item["DMD3"]  = currentnext($tab_DM132['DMD3']);

        insertIntoIndicators($symbol, $val, $period, $item);

        $ret++;
    }

    return $ret;
}

function computeAndInsertAllIndicators($symbol, $data, $period, $all = 0) {
    return computeAndInsertIntoIndicators($symbol, $data, $period, $all);
}

function computeAndInsertLastIndicator($symbol, $data, $period) {
    return computeAndInsertIntoIndicators($symbol, $data, $period, 1);
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

// //////////////////////////////////////////////////////////////
// Cumul des Daily en Weekly/Monthly
// //////////////////////////////////////////////////////////////
function aggregateWeeklyMonthlySymbol($symbol, $limited) {

    $tab_weekly  = [ "counter" => array(), "lastdays" => array(), "volume" => array(), "open" => array(), "high" => array(), "low" => array(), "close" => array() ];
    $tab_monthly = [ "counter" => array(), "lastdays" => array(), "volume" => array(), "open" => array(), "high" => array(), "low" => array(), "close" => array() ];

    // Requete a revoir sur le subq (300 car il m'en faut 30 + 200 = 230 mim pour calcul MM200)
    $req = "SELECT * FROM daily_time_series_adjusted WHERE symbol=\"".$symbol."\"".($limited == 1 ? " ORDER BY day DESC LIMIT 300) subq ORDER BY day ASC" : "");
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
// Calcul DM, MM7, MM20, MM50, MM200, RSI14 en Daily/Weekly/Monthly
// //////////////////////////////////////////////////////////////
function computePeriodIndicatorsSymbol($symbol, $limited, $period) {

    $table = strtolower($period)."_time_series_adjusted";

    $data = array();

    if ($limited == 0)
        $req = "SELECT * FROM ".$table." WHERE symbol='".$symbol."'";
    else
        $req = "SELECT * FROM (SELECT * FROM ".$table." WHERE symbol='".$symbol."' ORDER BY day DESC LIMIT 300) subq ORDER BY day ASC";

    $res= dbc::execSql($req);
    while($row = mysqli_fetch_assoc($res)) {
        // On prend la valeur de cloture ajustée pour avoir les courbes cohérentes
        $row['close'] = isset($row['adjusted_close']) && is_numeric($row['adjusted_close']) ? $row['adjusted_close'] : $row['close'];
        $data[] = $row;
    }

    // INSERT INDICATORS
    $ret = computeAndInsertAllIndicators($symbol, $data, $period, $limited == 1 ? 30 : 0);
    
    logger::info("INDIC", $symbol, "[".$period."] [insert=".$ret.", data=".count($data)."]");
}

// //////////////////////////////////////////////////////////////
// Calcul DM, MM7, MM20, MM50, MM200, RSI14 du jour (table quotes)
// //////////////////////////////////////////////////////////////
function computeQuoteIndicatorsSymbol($symbol) {

    $data = array();

    $req2 = "SELECT * FROM quotes WHERE symbol=\"".$symbol."\"";
    $res2 = dbc::execSql($req2);

    if ($row2 = mysqli_fetch_assoc($res2)) {

        // price -> close et adjusted_close
        $row2['close'] = $row2['price'];
        $row2['adjusted_close'] = $row2['price'];

        $req = "SELECT * FROM (SELECT * FROM daily_time_series_adjusted WHERE symbol=\"".$symbol."\" ORDER BY day DESC LIMIT 300) subq ORDER BY day ASC";
        $res = dbc::execSql($req);
        while($row = mysqli_fetch_assoc($res)) {

            // On prend la valeur de cloture ajustée pour avoir les courbes cohérentes
            $row['close'] = isset($row['adjusted_close']) && is_numeric($row['adjusted_close']) ? $row['adjusted_close'] : $row['close'];
            $data[] = $row;
        }

        // On ajoute la last quote
        $data[] = $row2;

//        tools::pretty($data); exit(0);

        // INSERT ALL INDICATORS
        $ret = computeAndInsertLastIndicator($symbol, $data, "DAILY");
        
        logger::info("INDIC", $symbol, "[QUOTES] [insert=".$ret.", data=".count($data)."]");
    }
}

// //////////////////////////////////////////////////////////////
// Calcul MM7, MM20, MM50, MM200, RSI14 en Daily/Weekly/Monthly
// //////////////////////////////////////////////////////////////
function computeIndicatorsForSymbolWithOptions($symbol, $options = array("aggregate" => false, "limited" => 0, "periods" => ['DAILY', 'WEEKLY', 'MONTHLY'])) {

    if ($options['aggregate']) aggregateWeeklyMonthlySymbol($symbol, $options['limited']);

    foreach($options['periods'] as $key)
        computePeriodIndicatorsSymbol($symbol, $options['limited'], $key);

}

function computeAllIndicatorsForSymbol($symbol, $limited, $aggregate = false) {
    computeIndicatorsForSymbolWithOptions($symbol, array("aggregate" => $aggregate, "limited" => $limited, "periods" => ['DAILY', 'WEEKLY', 'MONTHLY']));
}

function computeAllIndicatorsForAllSymbols($filter_symbol, $limited) {

    // Selection du/des actif(s) à prendre en charge
    $req = "SELECT * FROM stocks WHERE symbol LIKE \"%".$filter_symbol."%\"";
    $res = dbc::execSql($req);
    while($row = mysqli_fetch_assoc($res))
        computeAllIndicatorsForSymbol($row['symbol'], $limited);

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

$db = dbc::connect();

if ($reset == 1) resetData($filter);

if ($force == 1) {

    logger::info("DIRECT", "---------", "---------------------------------------------------------");

    computeAllIndicatorsForAllSymbols($filter, $limited);

    logger::info("DIRECT", "---------", "---------------------------------------------------------");

    echo "Done";
}

