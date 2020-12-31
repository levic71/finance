<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$ids = Wrapper::getRequest('ids', '');

// Vérification nom championnat unique
$req = "SELECT count(*) total FROM jb_championnat c, jb_saisons s WHERE c.id=".$sess_context->getRealChampionnatId()." AND s.id=".$ids;
$res = dbc::execSQL($req);
$row = mysqli_fetch_array($res);

if ($row['total'] > 0) $sess_context->changeSaison($ids);

?>
<div>
<span class="hack_ie">_HACK_IE_</span>
<script>
mm({action: 'dashboard'});
$cMsg({ msg: 'Changement de saison effectuée' });
</script>
</div>