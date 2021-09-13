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
    // On complete le tableau avec la premiere valeur du tableau
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
function isComputeDoneToday($symbol) {

    $searchthis = date("Ymd").":".$symbol.":daily=";
    $matches = array();

    $handle = @fopen("./finance.log", "r");
    fseek($handle, -81920, SEEK_END); // +/- 900 lignes 
    if ($handle)
    {
        while (!feof($handle))
        {
            $buffer = fgets($handle);
            if(strpos($buffer, $searchthis) !== FALSE)
                $matches[] = $buffer;
        }
        fclose($handle);
    }

    return count($matches) > 0 ? true : false;
}

function computeIndicators($filter_symbol, $filter_limited, $reset = 0) {

    // Parcours des actifs suivis
    $req = "SELECT * FROM stocks WHERE symbol LIKE \"%".$filter_symbol."%\"";
    $res = dbc::execSql($req);
    while($row = mysqli_fetch_array($res)) {

        // Si limited=1 calcul une fois par jour
        if ($reset == 0 && isComputeDoneToday($row['symbol'])) continue;

        logger::info("INDS", $row['symbol'], "BEGIN COMPUTE INDICATORS");

        $tab_daily_day     = array();
        $tab_daily_close   = array();
        $tab_weekly_vol    = array();
        $tab_weekly_open   = array();
        $tab_weekly_high   = array();
        $tab_weekly_low    = array();
        $tab_weekly_close  = array();
        $tab_monthly_vol   = array();
        $tab_monthly_open  = array();
        $tab_monthly_high  = array();
        $tab_monthly_low   = array();
        $tab_monthly_close = array();
        $tab_days_weeks_counter = array();
        $tab_days_weeks_lastday = array();
        $tab_days_months_counter = array();
        $tab_days_months_lastday = array();

        // On stocke les cours indexés avec la date pour utilisation calcul
        $tab_daily_close_by_day = array();

        if ($filter_limited == 0)
            $req2 = "SELECT * FROM daily_time_series_adjusted WHERE symbol=\"".$row['symbol']."\"";
        else
            $req2 = "SELECT * FROM (SELECT * FROM daily_time_series_adjusted WHERE symbol=\"".$row['symbol']."\" ORDER BY day DESC LIMIT 210) subq ORDER BY day ASC";
        $res2= dbc::execSql($req2);

        while($row2 = mysqli_fetch_array($res2)) {

            // On prend la valeur de cloture ajustée pour avoir les courbes cohérentes
            $close_value = isset($row2['adjusted_close']) && is_numeric($row2['adjusted_close']) ? $row2['adjusted_close'] : $row2['close'];

            $tab_daily_close_by_day[$row2['day']] = $close_value;
            $tab_daily_close[] = $close_value;

            $tab_daily_day[]   = $row2['day'];

            $week  = date("Y-W", strtotime($row2['day']));
            $month = date("Y-m", strtotime($row2['day']));

            // Cummul weekly et monthly pour calcul RSI14 weekly et monthly
            $tab_weekly_vol[$week]     = cumulTabVal($tab_weekly_vol,    $week,  $row2['volume']);
            $tab_weekly_open[$week]    = cumulTabVal($tab_weekly_open,   $week,  $row2['open']);
            $tab_weekly_high[$week]    = cumulTabVal($tab_weekly_high,   $week,  $row2['high']);
            $tab_weekly_low[$week]     = cumulTabVal($tab_weekly_low,    $week,  $row2['low']);
            $tab_weekly_close[$week]   = cumulTabVal($tab_weekly_close,  $week,  $close_value);
            $tab_monthly_vol[$month]   = cumulTabVal($tab_monthly_vol,   $month, $row2['volume']);
            $tab_monthly_open[$month]  = cumulTabVal($tab_monthly_open,  $month, $row2['open']);
            $tab_monthly_high[$month]  = cumulTabVal($tab_monthly_high,  $month, $row2['high']);
            $tab_monthly_low[$month]   = cumulTabVal($tab_monthly_low,   $month, $row2['low']);
            $tab_monthly_close[$month] = cumulTabVal($tab_monthly_close, $month, $close_value);

            // On compte le nb de jours par week/month
            $tab_days_weeks_counter[$week]   = isset($tab_days_weeks_counter[$week])   ? $tab_days_weeks_counter[$week] + 1   : 1;
            $tab_days_months_counter[$month] = isset($tab_days_months_counter[$month]) ? $tab_days_months_counter[$month] + 1 : 1;

            // on garde le dernier par week/month
            $tab_days_weeks_lastday[$week]  = $row2['day'];
            $tab_days_months_lastday[$month] = $row2['day'];
        }

        // Calcul des moyennes weekly/monthly en divisant par le nb de dates
        foreach($tab_weekly_close as $key => $val) {
            $tab_weekly_open[$key]  = round($tab_weekly_open[$key]  / $tab_days_weeks_counter[$key], 8);
            $tab_weekly_high[$key]  = round($tab_weekly_high[$key]  / $tab_days_weeks_counter[$key], 8);
            $tab_weekly_low[$key]   = round($tab_weekly_low[$key]   / $tab_days_weeks_counter[$key], 8);
            $tab_weekly_close[$key] = round($tab_weekly_close[$key] / $tab_days_weeks_counter[$key], 8);
        }
        foreach($tab_monthly_close as $key => $val) {
            $tab_monthly_open[$key]  = round($tab_monthly_open[$key]  / $tab_days_months_counter[$key], 8);
            $tab_monthly_high[$key]  = round($tab_monthly_high[$key]  / $tab_days_months_counter[$key], 8);
            $tab_monthly_low[$key]   = round($tab_monthly_low[$key]   / $tab_days_months_counter[$key], 8);
            $tab_monthly_close[$key] = round($tab_monthly_close[$key] / $tab_days_months_counter[$key], 8);
        }


        // INSERT WEEKLY AND MONTHLY DATA
        foreach($tab_days_weeks_lastday as $key => $val) {
            $open   = $tab_weekly_open[$key];
            $high   = $tab_weekly_high[$key];
            $low    = $tab_weekly_low[$key];
            $close  = $tab_weekly_close[$key];
            $volume = $tab_weekly_vol[$key];

            $insert = "INSERT INTO weekly_time_series_adjusted (symbol, day, open, high, low, close, volume) VALUES('".$row['symbol']."', '".$val."', '".$open."', '".$high."', '".$low."', '".$close."', '".$volume."') ON DUPLICATE KEY UPDATE open='".$open."', high='".$high."', low='".$low."', close='".$close."', volume='".$volume."'";
            $res3= dbc::execSql($insert);
        }
        foreach($tab_days_months_lastday as $key => $val) {
            $open   = $tab_monthly_open[$key];
            $high   = $tab_monthly_high[$key];
            $low    = $tab_monthly_low[$key];
            $close  = $tab_monthly_close[$key];
            $volume = $tab_monthly_vol[$key];

            $insert = "INSERT INTO monthly_time_series_adjusted (symbol, day, open, high, low, close, volume) VALUES('".$row['symbol']."', '".$val."', '".$open."', '".$high."', '".$low."', '".$close."', '".$volume."') ON DUPLICATE KEY UPDATE open='".$open."', high='".$high."', low='".$low."', close='".$close."', volume='".$volume."'";
            $res3= dbc::execSql($insert);
        }
        // ///////////////////////


        // DAILY

        if (count($tab_daily_day) == 0) { logger::info("INDS", $row['symbol'], "NO DAILY DATA !!!!"); continue; }

        $tab_daily_MM7   = computeMMX($tab_daily_close, 7);
        $tab_daily_MM20  = computeMMX($tab_daily_close, 20);
        $tab_daily_MM50  = computeMMX($tab_daily_close, 50);
        $tab_daily_MM200 = computeMMX($tab_daily_close, 200);
        $tab_daily_RSI14 = computeRSIX($tab_daily_close, 14);

        foreach($tab_daily_day as $key => $val) {
            $MM7   = currentnext($tab_daily_MM7);
            $MM20  = currentnext($tab_daily_MM20);
            $MM50  = currentnext($tab_daily_MM50);
            $MM200 = currentnext($tab_daily_MM200);
            $RSI14 = currentnext($tab_daily_RSI14);

            $insert = "INSERT INTO indicators (symbol, day, period, MM7, MM20, MM50, MM200, RSI14) VALUES('".$row['symbol']."', '".$val."', 'DAILY', '".$MM7."', '".$MM20."', '".$MM50."', '".$MM200."', '".$RSI14."') ON DUPLICATE KEY UPDATE MM7='".$MM7."', MM20='".$MM20."', MM50='".$MM50."', MM200='".$MM200."', RSI14='".$RSI14."'";
            $res3= dbc::execSql($insert);
        }


        // WEEKLY

        if (count($tab_weekly_close) == 0) { logger::info("INDS", $row['symbol'], "NO WEEKLY DATA !!!!"); continue; }

        $tab_weekly_MM7   = computeMMX($tab_weekly_close, 7);
        $tab_weekly_MM20  = computeMMX($tab_weekly_close, 20);
        $tab_weekly_MM50  = computeMMX($tab_weekly_close, 50);
        $tab_weekly_MM200 = computeMMX($tab_weekly_close, 200);
        $tab_weekly_RSI14 = computeRSIX($tab_weekly_close, 14);

        foreach($tab_weekly_close as $key => $val) {
            $MM7   = currentnext($tab_weekly_MM7);
            $MM20  = currentnext($tab_weekly_MM20);
            $MM50  = currentnext($tab_weekly_MM50);
            $MM200 = currentnext($tab_weekly_MM200);
            $RSI14 = currentnext($tab_weekly_RSI14);

            $insert = "INSERT INTO indicators (symbol, day, period, MM7, MM20, MM50, MM200, RSI14) VALUES('".$row['symbol']."', '".$tab_days_weeks_lastday[$key]."', 'WEEKLY', '".$MM7."', '".$MM20."', '".$MM50."', '".$MM200."', '".$RSI14."') ON DUPLICATE KEY UPDATE MM7='".$MM7."', MM20='".$MM20."', MM50='".$MM50."', MM200='".$MM200."', RSI14='".$RSI14."'";
            $res3= dbc::execSql($insert);
        }

        
        // MONTHLY

        if (count($tab_weekly_close) == 0) { logger::info("INDS", $row['symbol'], "NO WEEKLY DATA !!!!"); continue; }

        $tab_monthly_MM7   = computeMMX($tab_monthly_close, 7);
        $tab_monthly_MM20  = computeMMX($tab_monthly_close, 20);
        $tab_monthly_MM50  = computeMMX($tab_monthly_close, 50);
        $tab_monthly_MM200 = computeMMX($tab_monthly_close, 200);
        $tab_monthly_RSI14 = computeRSIX($tab_monthly_close, 14);

        foreach($tab_monthly_close as $key => $val) {
            $MM7   = currentnext($tab_monthly_MM7);
            $MM20  = currentnext($tab_monthly_MM20);
            $MM50  = currentnext($tab_monthly_MM50);
            $MM200 = currentnext($tab_monthly_MM200);
            $RSI14 = currentnext($tab_monthly_RSI14);

            $insert = "INSERT INTO indicators (symbol, day, period, MM7, MM20, MM50, MM200, RSI14) VALUES('".$row['symbol']."', '".$tab_days_months_lastday[$key]."', 'MONTHLY', '".$MM7."', '".$MM20."', '".$MM50."', '".$MM200."', '".$RSI14."') ON DUPLICATE KEY UPDATE MM7='".$MM7."', MM20='".$MM20."', MM50='".$MM50."', MM200='".$MM200."', RSI14='".$RSI14."'";
            $res3= dbc::execSql($insert);
        }

        logger::info("INDS", $row['symbol'], date("Ymd").":".$row['symbol'].":daily=".count($tab_daily_day).":weekly=".count($tab_days_weeks_lastday).":monthly=".count($tab_days_months_lastday));
    }
}

// FORCE Computing
$force = 0;
$limited = 0;
$filter = "";
$reset = 0;

foreach(['force', 'limited', 'filter', 'reset'] as $key)
    $$key = isset($_GET[$key]) ? $_GET[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if ($reset == 1) {
    $sql = "TRUNCATE TABLE indicators";
    $res= dbc::execSql($sql);
}

if ($force == 1) {
    computeIndicators($filter, $limited, $reset);
    echo "Done";
}

