<?

ini_set('default_charset', 'iso8859-1');

require_once "../include/sess_context.php";

session_start();

include "../include/toolbox.php";
include "../include/inc_db.php";
include "../wrapper/wrapper_fcts.php";

$db = dbc::connect();

$dns = explode('.', $_SERVER['SERVER_NAME']);

// Accès direct au championnat via sous domaine
if (true ) {

	$r7_dns = explode('-', $dns[0]);
	$r7 = isset($r7_dns[0]) && strtolower($r7_dns[0]) == "r7" ? true : false;

	$sql = "SELECT id, nom FROM jb_championnat WHERE entity='_NATIF_' AND actif = 1 AND nom != '' AND lower(nom)='".strtolower($r7 ? $r7_dns['0'] : $dns['0'])."' ORDER BY dt_creation DESC";
echo $sql;
  $res = dbc::execSQL($sql);
	if ($row = mysqli_fetch_array($res)) {
echo "toto";
    $protocole = stripos($_SERVER['SERVER_PROTOCOL'],'https') == 0 ? 'https://' : 'http://';
  	ToolBox::do_redirect($protocole + "www.jorkers.com/wrapper/jk.php?idc=".$row['id']);
    exit(0);
  }
}

unset($_SESSION['antispam']);
$_SESSION['antispam'] = ToolBox::getRand(5);

$sess_context = isset($_SESSION['sess_context']) ? $_SESSION['sess_context'] : null;

?>
