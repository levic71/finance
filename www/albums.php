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
	$menu = new menu("full_access");
	$menu->debut($sess_context->getChampionnatNom());
}

if (!isset($id_theme))
{
	$tmp = new SQLAlbumsThemesServices($sess_context->getRealChampionnatId(), -1);
	$id_theme = $tmp->getFirstTheme();
}

?>

<SCRIPT SRC="../js/flashobject.js" type="text/javascript"></SCRIPT>

<FORM ACTION=albums.php METHOD=post>
<INPUT TYPE=HIDDEN NAME=type_action VALUE="" />
<INPUT TYPE=HIDDEN NAME=pkeys_where VALUE="" />
<INPUT TYPE=HIDDEN NAME=id_theme VALUE="<?= $id_theme ?>" />

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central">

<?

echo "<TR><TD align=center>";
if ($sess_context->isAdmin())
{
	$fxlist = new FXListAlbums($sess_context->getRealChampionnatId(), $id_theme, $sess_context->isAdmin());
	$fxlist->FXSetPagination("albums.php");
	$fxlist->FXDisplay();
}
else
{
	$sas = new SQLAlbumsThemesServices($sess_context->getRealChampionnatId(), $id_theme);
	$themes = $sas->getAllThemes();
	$photos = $sas->getPhotos();
	echo "<table border=0 cellpadding=0 cellspacing=0 SUMMARY=\"\">";

	echo "<TR><TD ALIGN=center><FONT SIZE=4>Thèmes : </FONT><SELECT style=\"padding: 0px; margin: 0px;\" NAME=id_theme onChange=\"document.forms[0].submit();\">";
	$i = 0;
	foreach($themes as $item)
	{
		echo "<OPTION VALUE=".$item['id']." ".($item['id'] == $id_theme ? "SELECTED" : "").">".$item['nom']."</OPTION>";
		$i++;
	}
	if ($i == 0) echo "<OPTION>Aucun thème</OPTION>";
	echo "</SELECT></TD></TR>";
	
	if ($i > 0)
	{
		$xml_file = $sas->getXMLFilename();
		$xml_cache = JKCache::getCache($xml_file, -1, "_FLUX_XML_ALBUM_");

?>
		<TR><TD><DIV style="text-align: center;" id="swfviewer">

		<script type="text/javascript">
			// <![CDATA[
			var airness_fo = new FlashObject("../swf/viewer.swf", "swfviewer", "700", "500", "0", "#DDDDDD");
			airness_fo.addParam("quality", "best");
			airness_fo.addParam("wmode", "transparent");
			airness_fo.addParam("salign", "t");
			airness_fo.addParam("scale", "noscale");
			airness_fo.addVariable("xmlDataPath", "<?= $xml_file ?>");
			airness_fo.write("swfviewer");
			// ]]>
		</script>
	
		</DIV></TD></TR>
<?
	}
	
	echo "</table>";
}
echo "</TD>";

if ($sess_context->isAdmin() && !(isset($FXOption) && $FXOption == _FXLIST_EXPORT_))
{
	$sas = new SQLAlbumsThemesServices($sess_context->getRealChampionnatId(), $id_theme);
	$album = $sas->getAlbumTheme();
	
	if ($album['nb_photos'] < 16)
	{
		echo "<TR><TD><TABLE BORDER=0 WIDTH=100% SUMMARY=\"\">";
		echo "<TD ALIGN=RIGHT><INPUT TYPE=SUBMIT NAME=bouton VALUE=\"Ajouter une photo\" onClick=\"javascript:ajouter_photo();\" /></TD>";
		echo "</TABLE></TD>";

	}

	echo "<TR><TD><TABLE SUMMARY=\"\" BORDER=0 WIDTH=100% STYLE=\"border: 1px dashed navy;\">";
	echo "<TD ALIGN=CENTER> Cet espace est dédié aux images relatives à l'activité du jorkyball (16 images maximum). Les images vulgaires ou pornographiques sont interdites et l'administrateur de ce site se réserve le droit de supprimer tout contenu illicite.</TD>";
	echo "</TABLE></TD>";
}

?>

</TABLE>

<SCRIPT type="text/javascript">
function ajouter_photo()
{
    document.forms[0].action = 'albums_ajouter.php';
}
function modifier_photo(pkeys, action)
{
	document.forms[0].type_action.value=action;
	document.forms[0].pkeys_where.value=pkeys;
    document.forms[0].action = 'albums_ajouter.php';

	document.forms[0].submit();
}
function supprimer_photo(pkeys, action)
{
	if (!confirm('Etes-vous de vouloir supprimer cette photo ?'))
		return false;

	document.forms[0].type_action.value=action;
	document.forms[0].pkeys_where.value=pkeys;
    document.forms[0].action = 'albums_supprimer_do.php';

	document.forms[0].submit();
}
</SCRIPT>

</FORM>

<? if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_) FXList::FXHTLMExportEnd(); else $menu->end(); ?>
