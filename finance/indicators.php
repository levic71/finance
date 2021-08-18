<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
// //////////////////////////////////////////

include "include.php";

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

if (!is_dir("cache/")) mkdir("cache/");

$db = dbc::connect();

?>

<div class="ui container inverted segment">

    <pre style="width: 100%; height: 500px; overflow: scroll;">
<?

// Parcours des actifs suivis
$req = "SELECT * FROM stocks WHERE symbol=\"GLE.PAR\"";
$req = "SELECT * FROM stocks";
$res = dbc::execSql($req);
while($row = mysqli_fetch_array($res)) {

    logger::info("INDICATORS", $row['symbol'], "BEGIN COMPUTE INDICATORS");

    $tab_day = array();
    $tab_close = array();

    $req2 = "SELECT * FROM daily_time_series_adjusted WHERE symbol=\"".$row['symbol']."\" AND day > \"2021-07-01\"";
    $req2 = "SELECT * FROM daily_time_series_adjusted WHERE symbol=\"".$row['symbol']."\"";
    $res2= dbc::execSql($req2);
    while($row2 = mysqli_fetch_array($res2)) {

        $tab_close[] = $row2['close'];
        $tab_day[] = $row2['day'];
    
    }

    if (count($tab_day) == 0) { logger::info("INDICATORS", $row['symbol'], "NO DATA !!!!"); continue; }

    $tab_MM7=TraderFriendly::simpleMovingAverage($tab_close, 7);
    $tab_MM20=TraderFriendly::simpleMovingAverage($tab_close, 20);
    $tab_MM50=TraderFriendly::simpleMovingAverage($tab_close, 50);
    $tab_MM200=TraderFriendly::simpleMovingAverage($tab_close, 200);
    $tab_RSI14=TraderFriendly::relativeStrengthIndex($tab_close, 14);
/*     
    var_dump($tab_MM7);
    var_dump($tab_RSI14);
    var_dump($tab_day);
    var_dump($tab_close);
 */

    foreach($tab_day as $key => $val) {
        $MM7 = isset($tab_MM7[$key]) ? $key : array_key_first($tab_MM7);
        $MM20 = isset($tab_MM20[$key]) ? $key : array_key_first($tab_MM20);
        $MM50 = isset($tab_MM50[$key]) ? $key : array_key_first($tab_MM50);
        $MM200 = isset($tab_MM200[$key]) ? $key : array_key_first($tab_MM200);
        $RSI14 = isset($tab_RSI14[$key]) ? $key : array_key_first($tab_RSI14);

        $insert = "INSERT INTO indicators (symbol, day, period, MM7, MM20, MM50, MM200, RSI14) VALUES('".$row['symbol']."', '".$val."', 'DAILY', '".$tab_MM7[$MM7]."', '".$tab_MM20[$MM20]."', '".$tab_MM50[$MM50]."', '".$tab_MM200[$MM200]."', '".$tab_RSI14[$RSI14]."') ON DUPLICATE KEY UPDATE MM7='".$tab_MM7[$MM7]."', MM20='".$tab_MM20[$MM20]."', MM50='".$tab_MM50[$MM50]."', MM200='".$tab_MM200[$MM200]."', RSI14='".$tab_RSI14[$RSI14]."'";
        $res3= dbc::execSql($insert);
//        echo $val.":".$tab_MM7[$MM7].":".$tab_MM20[$MM20].":".$tab_MM200[$MM200].":".$tab_RSI14[$RSI14].":"."<br />";
    }

    logger::info("INDICATORS", $row['symbol'], "END TRT");
}


?>
    </pre>
</div>