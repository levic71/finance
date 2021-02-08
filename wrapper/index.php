<?

require_once "../include/sess_context.php";

session_start();

include "../include/toolbox.php";
include "../include/inc_db.php";
include "wrapper_fcts.php";

$db = dbc::connect();

$dns = explode('.', $_SERVER['SERVER_NAME']);

if (isset($dns[0]) && $dns[0] != "www") {

	$r7_dns = explode('.', $dns);
	$r7 = isset($r7_dns[0]) && $r7_dns == "r7" ? true : false;

	$redirect_idc = "";
	$sql = "SELECT id, nom FROM jb_championnat WHERE entity='_NATIF_' AND actif = 1 AND nom != '' AND nom='".($r7 ? $r7_dns : $dns)."' ORDER BY dt_creation DESC";
	$res = dbc::execSQL($sql);
	if ($row = mysqli_fetch_array($res))
		$redirect_idc = "?idc=".$row['id'];

	ToolBox::do_redirect("www.jorkers.com/wrapper/jk.php".$redirect_idc);

			if (false) {

				$idc = 0;

				$sql = "SELECT id, nom FROM jb_championnat WHERE entity='_NATIF_' AND actif = 1 AND nom != '' ORDER BY dt_creation DESC";
				$res = dbc::execSQL($sql);
				while($row = mysqli_fetch_array($res)) {
					if (Wrapper::string2DNS($row['nom']) == $dns[0]) { $idc = $row['id']; break; }
				}

				ToolBox::do_redirect("jk.php".($idc == 0 ? "" : "?idc=".$idc));
			}

} else
	ToolBox::do_redirect("../wrapper/jk.php");

?>