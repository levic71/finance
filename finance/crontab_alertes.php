<?

// //////////////////////////////////////////
// NE PAS METTRE DE SESSION SINON PB CRONTAB
// //////////////////////////////////////////

include "include.php";
include "indicators.php";
include "googlesheet/sheet.php";

ini_set('max_execution_time', '300'); //300 seconds = 5 minutes

// Overwrite include value
$dbg = false;

if (!is_dir("cache/")) mkdir("cache/");

$db = dbc::connect();

// Afficher en page d'accueil CAC, SP500, ...
// Alerte si perf actif > +/- 5% dans la journee, sur 1 semaine, sur 1 mois, 1 an
// => Remonter les stops ?

// Alerte si valo actif proche MM200 ou si passement MM200 (MM100, MM50 ?)

// Alerte si CAC > 6500, < 6500, < 6300, > 6800, > 7000, > 7200
// Range CAC - 6000-7000
// Suivre cassure MM200 (CAC, SP, VIX, NASDAQ)



?>