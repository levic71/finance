<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_)
	FXList::FXHTLMExportBegin();
else
{
	if (isset($sess_context) && $sess_context->isChampionnatValide())
	{
		$menu = new menu("full_access");
		$menu->debut($sess_context->getChampionnatNom());
	}
	else
	{
		$menu = new menu("forum_access");
		$menu->debut("");
	}
}

?>

<FORM ACTION=whois.php METHOD=POST>

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700>

<?

echo "<TR><TD>";
$fxlist = new FXListForumWhois($sess_context->getRealChampionnatId());
$fxlist->FXDisplay();
echo "</TD>";

?>

</TABLE>
</FORM>

<? if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_) FXList::FXHTLMExportEnd(); else $menu->end(); ?>
