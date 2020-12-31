<?

include "../include/sess_context.php";

session_start();

$jorkyball_redirect_exception = 1;

include "../www/common.php";
include "../include/inc_db.php";
include "../www/ManagerFXList.php";

$db = dbc::connect();

if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_)
	FXList::FXHTLMExportBegin();
else
{
	$menu = new menu("forum_access");
	$menu->debut("");
}

if (isset($bouton_valid))
{
	while(list($cle, $val) = each($_POST))
	{
		if (strstr($cle, "c_"))
		{
			$exp = explode("_", $cle);
			$id_champ = $exp[1];
			$req = "UPDATE jb_championnat SET actif=".$val." WHERE id=".$id_champ;
			$res = dbc::execSQL($req);
		}
		if (strstr($cle, "s_"))
		{
			$exp = explode("_", $cle);
			$id_champ = $exp[1];
			$req = "UPDATE jb_championnat SET special=".$val." WHERE id=".$id_champ;
			$res = dbc::execSQL($req);
		}
		if (strstr($cle, "p_"))
		{
			$exp = explode("_", $cle);
			$id_champ = $exp[1];
			$req = "UPDATE jb_championnat SET pronostic=".$val." WHERE id=".$id_champ;
			$res = dbc::execSQL($req);
		}
		if (strstr($cle, "r_"))
		{
			$exp = explode("_", $cle);
			$id_champ = $exp[1];
			$req = "UPDATE jb_championnat SET ref_champ='".$val."' WHERE id=".$id_champ;
			$res = dbc::execSQL($req);
		}
	}
	JKCache::delCache("../cache/most_active_home.txt", "_FLUX_MOST_ACTIVE_");
}

?>

<form action="gestion_championnat_actif.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="type_action" value="" />
<input type="hidden" name="pkeys_where" value="" />
<input type="hidden" name="only_pronostics" value="<?= isset($only_nul_points) && $only_nul_points == 1 ? 1 : 0 ?>" />
<input type="hidden" name="only_speciaux" value="<?= isset($only_speciaux) && $only_speciaux == 1 ? 1 : 0 ?>" />
<input type="hidden" name="only_pronostics" value="<?= isset($only_pronostics) && $only_pronostic == 1 ? 1 : 0 ?>" />

<table border="0" cellpadding="0" cellspacing="0" width="700" summary="">

<?

echo "<tr><td>";
$fxlist = new FXListChampionnatsActifs(isset($only_nul_points) ? true : false, isset($only_speciaux) ? true : false, isset($only_pronostics) ? true : false);
$fxlist->FXSetPagination("gestion_championnat_actif.php");
$fxlist->FXDisplay();
echo "</td>";

if (!(isset($FXOption) && $FXOption == _FXLIST_EXPORT_))
{
	echo "<tr><td align=right width=100%><table border=\"0\" summary=\"\">";
	echo "	<td><input type=\"submit\" name=\"only_nul_points\" value=\"Filtre zéro points\" /></td>";
	echo "	<td><input type=\"submit\" name=\"only_speciaux\" value=\"Filtre spéciaux\" /></td>";
	echo "	<td><input type=\"submit\" name=\"only_pronostics\" value=\"Filtre pronostics\" /></td>";
	echo "	<td><input type=\"submit\" name=\"refresh\" value=\"Refresh\" /></td>";
	echo "	<td><input type=\"submit\" name=\"bouton_valid\" value=\"Valider modifications\" /></td>";
	echo "</table></td>";
}

?>

</table>

<script type="text/javascript">
function ajouter_item()
{
}
function modifier_item(pkeys, action)
{
}
function supprimer_item(pkeys, action)
{
}
</script>

</form>

<? if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_) FXList::FXHTLMExportEnd(); else $menu->end(); ?>
