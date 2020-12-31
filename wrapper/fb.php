<?

require_once "../include/toolbox.php";
require_once "../include/inc_db.php";
require_once "wrapper_fcts.php";

$_REQUEST['idc'] = isset($_REQUEST['idc']) ? $_REQUEST['idc'] : 85;

// Si redirect fb ou mail
$tmp = explode("_", $_REQUEST['idc']);

$idc = isset($tmp[0]) && is_numeric($tmp[0]) ? $tmp[0] : 85;

$db = dbc::connect();
$chp = Wrapper::getChampionnat($idc);

if ($chp)
	ToolBox::do_redirect("http://".Wrapper::string2DNS($chp['championnat_nom']).".jorkers.com/wrapper/jk.php?idc=".$_REQUEST['idc']);
else
	ToolBox::do_redirect("http://wwww.jorkers.com/wrapper/jk.php?idc=".$idc);

?>
