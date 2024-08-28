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
if (!tools::isLocalHost() && date("N") >= 6) exit(0);

if (!is_dir("cache/")) mkdir("cache/");

$db = dbc::connect();

// Pour l'instant que pour moi
$user_email = 'vmlf71@gmail.com';

// Tableau des notifs
$notifs = [];

// Pour l'instant que pour moi
$req = "SELECT * FROM users WHERE email='".$user_email."'";
$res = dbc::execSql($req);
if (!($row = mysqli_fetch_array($res))) exit(0);

$user_id = $row['id'];

// Calcul prediction
calc::prediction_update($user_id);

// Récupération des devises
$devises = cacheData::readCacheData("cache/CACHE_GS_DEVISES.json");

// Recuperation des DM en BD
$quotes = calc::getIndicatorsLastQuote();

// Calcul synthese de tous les porteuilles de l'utilisateur (on recupere les PRU globaux)
$aggregate_ptf   = calc::getAggregatePortfoliosByUser($user_id);

// Computing portfolio/quotes
$sc = new StockComputing($quotes, $aggregate_ptf, $devises);

$positions       = $sc->getPositions();
$trend_following = $sc->getTrendFollowing();

// 0 - Liste des actifs en portefeuille ou en surveillance
// 1 - Liste des alertes de franchissemement des alertes et mm200 à hausse et à la baisse
// 2 - Liste des actifs cours proches de la MM200 (2%)
// 3 - Baisse ou hausse >= 5% en intraday/weekly/monthly
// 4 - Si cours dépasse objectif et pas de stoploss ou stoploss trop bas (5% ?)
// 4 - Si cours dépasse stop profit et pas de stoploss ou stoploss trop bas (5% ?)
// 5 - Si croissement H/B cours/MM200

// Parcours de tous les actifs
foreach($quotes['stocks'] as $key => $val) {

	// var_dump($val);

	$qc = new QuoteComputing($sc, $key);

	// Si actif pas suivi dans trendfollowing ou pas dans le portefeuille, bye bye
	if (!isset($trend_following[$key]) && !isset($positions[$key])) continue;

	// Si actif suivi mais alerte desactive
	if (isset($trend_following[$key]) && $trend_following[$key]['active'] == 0) continue;

	$symbol = $key;
	$alerts = [];
	
	$alerts[] = $qc->depassementJournalierSeuils();         // Recherche dépassement de seuils

	if ($qc->isInPtf()) {
		$alerts[] = $qc->depassementJournalierMM();             // Recherche depassement MM
		$alerts[] = $qc->hausseJournaliere(10);                 // Hausse journaliere +10% par rapport a la veille
		$alerts[] = $qc->performancePRU(20);                    // Depassement +20% PRU
		$alerts[] = $qc->depassementALaBaissePRU();             // Depassement a la baisse PRU
		$alerts[] = $qc->depassementJournalier('objectif');     // Depassement hausse/baisse Objectif avec ctrl StopLoss
		$alerts[] = $qc->depassementJournalier('stopprofit');   // Depassement hausse/baisse Stopprofil avec ctrl StopLoss
		$alerts[] = $qc->depassementJournalier('stoploss');     // Depassement hausse/baisse Stoploss avec ctrl StopLoss
	}

	// Reformat in one array
	foreach($alerts as $kk => $tt)
		foreach($tt as $xx => $ret)
			$notifs[] =  [ 'user_id' => $user_id, 'actif' => $symbol, 'type' => $ret[1], 'sens' => $ret[0], 'seuil' => $ret[2], 'colr' => $qc->getColorAlert($ret[0]), 'icon' => $qc->getIconAlert($ret[0]) ];
		
}

$file_cache = 'cache/TMP_ALERTES.json';
cacheData::writeCacheData($file_cache, $notifs);

foreach($notifs as $key => $val) {

	$d   = substr($val['type'], 0, 3) == "PRU" ? date("Y-m-d", strtotime('monday this week')) : date('Y-m-d');
	$req = "INSERT INTO alertes (user_id, date, actif, mail, lue, type, sens, couleur, icone, seuil) VALUES (".$val['user_id'].", '".$d."', '".$val['actif']."', 0, 0, '".$val['type']."', ".$val['sens'].", '".$val['colr']."', '".$val['icon']."', '".$val['seuil']."') ON DUPLICATE KEY UPDATE sens='".$val['sens']."', couleur='".$val['colr']."', icone='".$val['icon']."', seuil='".$val['seuil']."'";
	$res = dbc::execSql($req);

}

echo count($notifs)." item(s)<br />";
echo "Done";

?>