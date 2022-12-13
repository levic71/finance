<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
// //////////////////////////////////////////

// Alerte (id, date YYY-MM-JJ, user_id, actif, mail, lue, type, sens, couleur, icone, libelle, seuil)

include "include.php";
include "indicators.php";
include "googlesheet/sheet.php";

ini_set('max_execution_time', '300'); //300 seconds = 5 minutes

// Overwrite include value
$dbg = false;

// Si on n'est pas en semaine
if (date("N") >= 6) exit(0);

if (!is_dir("cache/")) mkdir("cache/");

$db = dbc::connect();

// Recuperation des DM en BD
$data2 = calc::getIndicatorsLastQuote();

$stocks = $data2['stocks'];
$perfs = $data2['perfs'];

$positions       = [];
$trend_following = [];
$user_email = 'vmlf71@gmail.com';

// Tableau des notifs
$notifs = [];

// Pour l'instant que pour moi
$req = "SELECT * FROM users WHERE email='".$user_email."'";
$res = dbc::execSql($req);
$row = mysqli_fetch_array($res);

$user_id = $row['id'];

// Calcul synthese de tous les porteuilles de l'utilisateur (on recupere les PRU globaux)
$aggregate_ptf   = calc::getAggregatePortfoliosByUser($user_id);
$positions       = $aggregate_ptf['positions'];
$trend_following = $aggregate_ptf['trend_following'];

// 0 - Liste des actifs en portefeuille ou en surveillance
// 1 - Liste des alertes de franchissemement des alertes et mm200 à hausse et à la baisse
// 2 - Liste des actifs cours proches de la MM200 (2%)
// 3 - Baisse ou hausse >= 5% en intraday/weekly/monthly
// 4 - Si cours dépasse objectif et pas de stoploss ou stoploss trop bas (5% ?)
// 4 - Si cours dépasse stop profit et pas de stoploss ou stoploss trop bas (5% ?)
// 5 - Si croissement H/B cours/MM200

function pourcentagevariation($vi, $vf) {
	return $vi == 0 ? 0 : (($vf / $vi) - 1) * 100;
}

function depassementALaHausse($vi, $vf, $s) {
	$ret = false;

	if ($vi < $s && $vf > $s) $ret = true;

	return $ret;
}

function depassementALaBaisse($vi, $vf, $s) {
	$ret = false;

	if ($vi > $s && $vf < $s) $ret = true;

	return $ret;
}


// Parcours de tous les actifs
// On regarde si actif est sous surveillance de l'utilisateur (alarme activé dans home_content => champ seuils dans trend_following)
// On regarde si actif dans le portefeuille utilisateur (tous les champs de trend_following si positionnes )

// Nouveau version tableau valeurs trend_following
$trend_following = [];

// Recuperation trend_following user
$req = "SELECT * FROM trend_following WHERE user_id='".$user_id."'";
$res = dbc::execSql($req);
while($row = mysqli_fetch_assoc($res)) $trend_following[$row['symbol']] = $row;

// Parcours de tous les actifs
foreach($stocks as $key => $val) {

	// var_dump($val);

	if (!isset($trend_following[$key]) && !isset($positions[$key])) continue;

	$symbol     = $val['symbol'];
	$price      = $val['price'];
	$previous   = $val['previous'];
	$pru        = isset($positions[$key]) ? $positions[$key]['pru'] : 0;
	$objectif   = isset($trend_following[$key]['objectif'])    && $trend_following[$key]['objectif']    != '' ? $trend_following[$key]['objectif']    : 0;
	$stoploss   = isset($trend_following[$key]['stop_loss'])   && $trend_following[$key]['stop_loss']   != '' ? $trend_following[$key]['stop_loss']   : 0;
	$stopprofit = isset($trend_following[$key]['stop_profit']) && $trend_following[$key]['stop_profit'] != '' ? $trend_following[$key]['stop_profit'] : 0;
	$seuils     = explode(';', isset($trend_following[$key]['seuils']) ? $trend_following[$key]['seuils'] : '');

	$colr_up   = "green";
	$icon_up   = "arrow up";
	$sens_up   = 1;
	$colr_down = "red";
	$icon_down = "arrow down";
	$sens_down = -1;

	// Parcours du cours de chaque seuil ?
	foreach($seuils as $i => $v) {
		$tmp = explode('|', $v);
		$inverse = isset($tmp[1]) ? $tmp[1] : 1;
		if ($inverse == -1) {
			$colr_up = "red";
			$colr_down = "green";
		}
		if (depassementALaHausse($previous, $price, $tmp[0]))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'seuil', 'sens' => $sens_up, 'seuil' => $tmp[0], 'colr' => $colr_up, 'icon' => $icon_up ];
		if (depassementALaBaisse($previous, $price, $tmp[0]))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'seuil', 'sens' => $sens_down, 'seuil' => $tmp[0], 'colr' => $colr_down, 'icon' => $icon_down ];
	}

	// Depassement hausse/baisse MM
	$tab_mm = [ 'MM200'];
	foreach ($tab_mm as $i => $mm) {
		if (depassementALaHausse($previous, $price, $val[$mm]))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => $mm, 'sens' => $sens_up, 'seuil' => $val[$mm], 'colr' => 'green', 'icon' => 'arrow up' ];	
		if (depassementALaBaisse($previous, $price, $val[$mm]))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => $mm, 'sens' => $sens_down, 'seuil' => $val[$mm], 'colr' => 'red', 'icon' => 'arrow down' ];
	}

	// Hausse journaliere +10% par rapport a la veille
	if (pourcentagevariation($previous, $price) >= 10)
		$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => '10%', 'sens' => $sens_up, 'seuil' => $previous, 'colr' => 'green', 'icon' => 'arrow up' ];

	// Depassement +20% PRU
	if ($pru > 0 && depassementALaHausse($previous, $price, $pru * 1.2)) {
		$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'pru20%', 'sens' => $sens_up, 'seuil' => $pru, 'colr' => 'green', 'icon' => 'arrow up' ];
		if ($stop_loss == 0)
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'no_stoploss', 'sens' => 0, 'seuil' => 'No Stoploss', 'colr' => 'orange', 'icon' => 'bell' ];
	}

	// Depassement a la baisse PRU
	if ($pru > 0 && depassementALaBaisse($previous, $price, $pru)) {
		$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'pru', 'sens' => $sens_down, 'seuil' => $pru, 'colr' => 'green', 'icon' => 'arrow up' ];
		if ($stop_loss == 0)
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'no_stoploss', 'sens' => 0, 'seuil' => 'No Stoploss', 'colr' => 'orange', 'icon' => 'bell' ];
	}

	// Depassement hausse/baisse Objectif avec ctrl StopLoss
	if ($objectif > 0) {
		if (depassementALaHausse($previous, $price, $objectif)) {
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'objectif', 'sens' => 1, 'seuil' => $objectif, 'colr' => 'green', 'icon' => 'trophy' ];
			if ($stop_loss == 0)
				$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'no_stoploss', 'sens' => 0, 'seuil' => 'No Stoploss', 'colr' => 'orange', 'icon' => 'bell' ];
		}
		if (depassementALaBaisse($previous, $price, $objectif))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'objectif', 'sens' => -1, 'seuil' => $objectif, 'colr' => 'red', 'icon' => 'arrow down' ];
	}

	// Depassement hausse/baisse StopProfit avec ctrl StopLoss
	if ($stopprofit > 0) {
		if (depassementALaHausse($previous, $price, $stopprofit)) {
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'stopprofit', 'sens' => 1, 'seuil' => $stopprofit, 'colr' => 'green', 'icon' => 'trophy' ];
			if ($stop_loss == 0)
				$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'no_stoploss', 'sens' => 0, 'seuil' => 'No Stoploss', 'colr' => 'orange', 'icon' => 'bell' ];
		}
		if (depassementALaBaisse($previous, $price, $stopprofit))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'stopprofit', 'sens' => -1, 'seuil' => $stopprofit, 'colr' => 'red', 'icon' => 'arrow down' ];
	}
	
	// Depassement hausse/baisse StopLoss
	if ($stoploss > 0) {
		if (depassementALaHausse($previous, $price, $stopprofit))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'stopprofit', 'sens' => 1, 'seuil' => $stopprofit, 'colr' => 'green', 'icon' => 'trophy' ];
		if (depassementALaBaisse($previous, $price, $stopprofit))
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => 'stopprofit', 'sens' => -1, 'seuil' => $stopprofit, 'colr' => 'red', 'icon' => 'arrow down' ];
	}
	
}

// tools::pretty($notifs);

$file_cache = 'cache/TMP_ALERTES.json';
cacheData::writeCacheData($file_cache, $notifs);

foreach($notifs as $key => $val) {

	$req = "INSERT INTO alertes (user_id, date, actif, mail, lue, type, sens, couleur, icone, seuil) VALUES (".$val['user_id'].", '".date('Y-m-d')."', '".$val['actif']."', 0, 0, '".$val['type']."', ".$val['sens'].", '".$val['colr']."', '".$val['icon']."', '".$val['seuil']."') ON DUPLICATE KEY UPDATE sens='".$val['sens']."', couleur='".$val['colr']."', icone='".$val['icon']."', seuil='".$val['seuil']."'";
	$res = dbc::execSql($req);

}

echo count($notifs)." item(s)<br />";
echo "Done";

?>