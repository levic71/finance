<?

include "../include/sess_context.php";

session_start();

$jorkyball_redirect_exception = 1;

include "common.php";
include "../include/inc_db.php";

$db = dbc::connect();

$scs = new SQLChampionnatsServices(-1);

// On vient de authentification admin
$row = $scs->getChampionnat(isset($ref_champ) ? $ref_champ : $sess_context->getRealChampionnatId());
if (!$row)
{
	$sess_context->setChampionnatNonValide();
    ToolBox::do_redirect("home.php");
}

// On mémorise les infos du championnat
$row['login'] = "";
$row['pwd']   = "";
$sess_context->setChampionnat($row);

// Sur un championnat démo on est admin
if ($row['demo'] == 1)
	$sess_context->setAdmin();

// On vient de inscription
$options = "";
if (isset($inscription_valid) && $inscription_valid == "yes")
{
	$sess_context->setAdmin();
	$options = "?msgno=1";
}

Toolbox::trackUser($row['championnat_id'], _TRACK_ACCES_HOME_);

ToolBox::do_redirect("championnat_home.php".$options);

?>
