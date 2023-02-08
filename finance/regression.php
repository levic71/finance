<?php

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
include "TALib/Core/StatisticFunctions.php";

use LupeCode\phpTraderNative\TraderFriendly;
use LupeCode\phpTraderNative\TALib\Enum\MovingAverageType;
use LupeCode\phpTraderNative\Trader;

require_once "Regression/AbstractRegression.php";
require_once "Regression/ExponentialRegression.php";
require_once "Regression/InterfaceRegression.php";
require_once "Regression/LinearRegression.php";
require_once "Regression/LogarithmicRegression.php";
require_once "Regression/PowerRegression.php";
require_once "Regression/RegressionException.php";
require_once "Regression/RegressionFactory.php";
require_once "Regression/RegressionModel.php";

use Regression\ExponentialRegression;
use Regression\LinearRegression;
use Regression\LogarithmicRegression;
use Regression\PowerRegression;
use Regression\RegressionFactory;
use Regression\RegressionModel;

$db = mysqli_connect("localhost", "root", "root", "finance") or die("Error connexion db" . mysqli_connect_errno() . ' ; ' . mysqli_connect_error());

$test_regression = true;
$test_talib      = true;

$testData = [];
$labels = [];

$req = "SELECT * FROM daily_time_series_adjusted dtsa, indicators indic WHERE dtsa.symbol=indic.symbol AND dtsa.day=indic.day AND indic.period='DAILY' AND dtsa.symbol='GLE.PAR' ORDER BY dtsa.day ASC";
$res = mysqli_query($db, $req) or die("Error on request : " . $req);
$x = 1;
while ($row = mysqli_fetch_assoc($res)) {
	$labels[] = $row['day'];
	$testData[] = [ floatval($x++), floatval(sprintf("%.2f", $row['adjusted_close']))];
}

$testData2 = [];
foreach($testData as $key => $val) {
	$testData2[] = $val['1'];
}

if ($test_talib) {

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
function ComputeChandeMomentumOscillator($data, $size) {
	$t = TraderFriendly::ChandeMomentumOscillator($data, $size);
	return fullFillArray($data, $t);
}
function ComputeLinearRegression($data, $size) {
	$t = TraderFriendly::linearRegression($data, $size);
	return fullFillArray($data, $t);
}
function ComputeMomentum($data, $size) {
	$t = TraderFriendly::momentum($data, $size);
	return fullFillArray($data, $t);
}
function ComputestandardDeviation($data, $size) {
	$t = TraderFriendly::standardDeviation($data, $size);
	return fullFillArray($data, $t);
}

$talib_MM200 = computeMMX($testData2, 200);
$talib_RSI14 = computeRSIX($testData2, 14);
$talib_CMO   = ComputeChandeMomentumOscillator($testData2, 14);
$talib_LR    = ComputeLinearRegression($testData2, 14);
$talib_MOM   = ComputeMomentum($testData2, 10);
$talib_STDD  = ComputestandardDeviation($testData2, 5);


function Stand_Deviation($arr)
{
	$num_of_elements = count($arr);
	$variance = 0.0;
		
	// calculating mean using array_sum() method
	$average = array_sum($arr)/$num_of_elements;
		
	foreach($arr as $i)
	{
		// sum of squares of differences between 
		// all numbers and means.
		$variance += pow(($i - $average), 2);
	}
		
	return (float)sqrt($variance/$num_of_elements);
}

$stdd = Stand_Deviation($testData2);
echo "<div>".$stdd."</div>";

}


if ($test_regression) {
/**
 * Calculate linear regression call calculate()
 */
$linear = new LinearRegression();
$linear->setSourceSequence($testData);
$linear->calculate();
$regressionModel_lin = $linear->getRegressionModel();

/**
 * Calculate Exponential regression call calculate()
 */
$exponential = new ExponentialRegression();
$exponential->setSourceSequence($testData);
$exponential->calculate();
$regressionModel_exp = $exponential->getRegressionModel();

/**
 * Calculate logarithmic regression call calculate()
 */
$logarithmic = new LogarithmicRegression();
$logarithmic->setSourceSequence($testData);
$logarithmic->calculate();
$regressionModel_log = $logarithmic->getRegressionModel();

/**
 * Calculate power regression call calculate()
 */
$powerReg = new PowerRegression();
$powerReg->setSourceSequence($testData);
$powerReg->calculate();
$regressionModel_pow = $powerReg->getRegressionModel();

}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Graphique</title>
	<script type="text/javascript" src="js/chart_options.js"></script>
	<script type="text/javascript" src="js/chart.min.js"></script>
	<style>
		#tooltip_stock_graphe_div div {
			font-size: 11px !important;
    		font-family: sans-serif;
		}
		#tooltip_stock_graphe_div {
			opacity: 0;
			position: absolute;
			top: 0px;
			right: 100px;
		}
	</style>
</head>
<body>
<h1>Chart</h1>

<canvas id="myChart1" height="100%"></canvas>
<canvas id="myChart2" height="20%"></canvas>
<canvas id="myChart3" height="20%"></canvas>

<script>

const data = {
	labels: [ 0, <? foreach($labels as $key => $value) echo ','.$value; ?> ],
	datasets: [
		{
			label: 'Cours',
			data: [ 0, <? foreach($testData as $key => $value) echo ','.$value[1]; ?>],
			fill: true,
			backgroundColor: 'rgba(75, 192, 192, 0.2)',
			borderColor: 'rgb(75, 192, 192)',
			borderWidth: 1,
			cubicInterpolationMode: 'monotone',
			tension: 0.1
		},
<? if ($test_talib) { ?>
		{
			label: 'MM200',
			data: [ 0, <? foreach($talib_MM200 as $key => $value) echo ','.$value; ?>],
			fill: false,
			borderColor: 'rgb(255, 0, 0)',
			borderWidth: 1,
			cubicInterpolationMode: 'monotone',
			tension: 0.1
		},
		{
			label: 'RL',
			data: [ 0, <? foreach($talib_LR as $key => $value) echo ','.$value; ?>],
			fill: false,
			borderColor: 'rgb(255, 0, 255)',
			borderWidth: 1,
			cubicInterpolationMode: 'monotone',
			tension: 0.1
		},
<? } ?>
<? if ($test_regression) { ?>
	{
			label: 'Reg lin',
			data: [ 0, <? foreach($regressionModel_lin->getResultSequence() as $key => $value) echo ','.$value[1]; ?>],
			fill: false,
			borderColor: 'rgb(75, 192, 192)',
			borderWidth: 1,
			cubicInterpolationMode: 'monotone',
			tension: 0.1
		},
		{
			label: 'Reg lin',
			data: [ 0, <? foreach($regressionModel_lin->getResultSequence() as $key => $value) echo ','.$value[1]; ?>],
			fill: false,
			borderColor: 'rgb(75, 192, 192)',
			borderWidth: 1,s
			cubicInterpolationMode: 'monotone',
			tension: 0.1
		},
		{
			label: 'Reg exp',
			data: [ 0, <? foreach($regressionModel_exp->getResultSequence() as $key => $value) echo ','.$value[1]; ?>],
			fill: false,
			borderColor: 'rgb(75, 192, 192)',
			borderWidth: 1,
			cubicInterpolationMode: 'monotone',
			tension: 0.1
		},
		{
			label: 'Reg pow',
			data: [ 0, <? foreach($regressionModel_pow->getResultSequence() as $key => $value) echo ','.$value[1]; ?>],
			fill: false,
			borderColor: 'rgb(75, 192, 192)',
			borderWidth: 1,
			cubicInterpolationMode: 'monotone',
			tension: 0.1
		},
		{
			label: 'Reg log',
			data: [ 0, <? foreach($regressionModel_log->getResultSequence() as $key => $value) echo ','.$value[1]; ?>],
			fill: false,
			borderColor: 'rgb(255, 0, 0)',
			borderWidth: 1,
			cubicInterpolationMode: 'monotone',
			tension: 0.1
		},
<? } ?>
	]
};

const data2 = {
	labels: [ 0, <? foreach($testData as $key => $value) echo ','.$value[0]; ?> ],
	datasets: [
<? if ($test_talib) { ?>
		{
			label: 'RSI14',
			data: [ 0, <? foreach($talib_RSI14 as $key => $value) echo ','.$value; ?>],
			fill: false,
			borderColor: 'rgb(0, 255, 0)',
			borderWidth: 1,
			cubicInterpolationMode: 'monotone',
			tension: 0.1
		},
		{
			label: 'CMO',
			data: [ 0, <? foreach($talib_CMO as $key => $value) echo ','.$value; ?>],
			fill: false,
			borderColor: 'rgb(255, 0, 0)',
			borderWidth: 1,
			cubicInterpolationMode: 'monotone',
			tension: 0.1
		},
<? } ?>
	]
};

const data3 = {
	labels: [ 0, <? foreach($testData as $key => $value) echo ','.$value[0]; ?> ],
	datasets: [
<? if ($test_talib) { ?>
		{
			label: 'MOM',
			data: [ 0, <? foreach($talib_MOM as $key => $value) echo ','.$value; ?>],
			fill: false,
			borderColor: 'rgb(0, 0, 255)',
			borderWidth: 1,
			cubicInterpolationMode: 'monotone',
			tension: 0.1
		},
		{
			label: 'STDD',
			data: [ 0, <? foreach($talib_STDD as $key => $value) echo ','.$value; ?>],
			fill: false,
			borderColor: 'rgb(0, 0, 100)',
			borderWidth: 1,
			cubicInterpolationMode: 'monotone',
			tension: 0.1
		},
<? } ?>
	]
};

const config = {
	type: 'line',
	data: data,
	options: options_Stock_Graphe
};
const config2 = {
	type: 'line',
	data: data2,
	options: options_Stock_Graphe
};
const config3 = {
	type: 'line',
	data: data3,
	options: options_Stock_Graphe
};

var ctx1 = document.getElementById('myChart1').getContext('2d');
chart1 = new Chart(ctx1, config);
chart1.update();

var ctx2 = document.getElementById('myChart2').getContext('2d');
chart2 = new Chart(ctx2, config2);
chart2.update();

var ctx3 = document.getElementById('myChart3').getContext('2d');
chart3 = new Chart(ctx3, config3);
chart3.update();

</script> 

</body>
</html>
