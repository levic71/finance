<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/journeebuilder.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$zone_calendar    = Wrapper::getRequest('zone_calendar', date('d/m/Y'));
$nom              = Wrapper::getRequest('nom',           '');
$photo            = Wrapper::getRequest('photo',         '');
$del              = Wrapper::getRequest('del',           0);
$idg              = Wrapper::getRequest('idg',           0);
$upd              = Wrapper::getRequest('upd',           0);

if (($upd == 1 || $del == 1) && !is_numeric($idg)) ToolBox::do_redirect("grid.php");

if ($del == 1)
{
	$sas = new SQLAlbumsThemesServices($sess_context->getRealChampionnatId(), $idg);
	$photos = $sas->getPhotos();
	foreach($photos as $p)
	{
		if ($p['photo'] != ""  && file_exists($p['photo'])) unlink($p['photo']);
		$delete = "DELETE FROM jb_albums WHERE id=".$p['id']." AND id_champ=".$sess_context->getRealChampionnatId();
		$res = dbc::execSQL($delete);
	}

	// Suppression du theme
	$delete = "DELETE FROM jb_albums_themes WHERE id=".$idg." AND id_champ=".$sess_context->getRealChampionnatId();
	$res = dbc::execSQL($delete);
?>
<span class="hack_ie">_HACK_IE_</span><script>journees=""; mm({action: 'photos'}); $cMsg({ msg: 'Album supprimé' });</script>
<?
	exit(0);
}

$modifier = $upd == 1 ? true : false;

$new_date = substr($zone_calendar, 6, 4) . "-" . substr($zone_calendar, 3, 2) . "-" . substr($zone_calendar, 0, 2);

if ($modifier)
{
	$sas = new SQLAlbumsThemesServices($sess_context->getRealChampionnatId(), $idg);
	$photos = $sas->getPhotos();
	$initials = array();
	foreach($photos as $p) $initials[$p['photo']] = $p['id'];

	$tab = explode(',', $photo);
	$nb_photos = $tab == '' ? 0 : count($tab);

	foreach($tab as $p)
	{
		if ($p == '') continue;
		$sql = "SELECT count(*) total from jb_albums WHERE id_champ=".$sess_context->getRealChampionnatId()." AND photo='".$p."'";
		$res = dbc::execSQL($sql);
		$row = mysqli_fetch_array($res);

		if ($row['total'] > 0)
		{
			if (isset($initials[$p])) unset($initials[$p]);
		}
		else
		{
			$sql = "INSERT INTO jb_albums (id_champ, id_saison, id_theme, commentaire, date, photo) VALUES (".$sess_context->getRealChampionnatId().", ".$sess_context->getChampionnatId().", ".$idg.", '', SYSDATE(), '".$p."');";
			$res = dbc::execSQL($sql);
		}
	}

	while(list($photo, $id) = each($initials))
	{
		if (file_exists($photo)) unlink($photo);
		$sql = "DELETE FROM jb_albums WHERE id=".$id." AND id_theme=".$idg." AND id_champ=".$sess_context->getRealChampionnatId();
		$res = dbc::execSQL($sql);
	}

 	$sql = "UPDATE jb_albums_themes SET last_modif=SYSDATE(), id_champ=".$sess_context->getRealChampionnatId().", id_saison=".$sess_context->getChampionnatId().", nom='".$nom."', date='".$new_date."', nb_photos=".$nb_photos." WHERE id=".$idg.";";
	$res = dbc::execSQL($sql);
}
else
{
	$tab = explode(',', $photo);
	$nb_photos = $tab == '' ? 0 : count($tab);

	$sql = "INSERT INTO jb_albums_themes (id_champ, id_saison, nom, date, nb_photos, last_modif) VALUES (".$sess_context->getRealChampionnatId().", ".$sess_context->getChampionnatId().", '".$nom."', '".$new_date."', ".$nb_photos.", SYSDATE());";
	$res = dbc::execSQL($sql);
	$sql = "SELECT * from jb_albums_themes WHERE id_champ=".$sess_context->getRealChampionnatId()." AND nom='".$nom."' ORDER BY id DESC;";
	$res = dbc::execSQL($sql);
	$row = mysqli_fetch_array($res);
	$idg = $row['id'];

	foreach($tab as $p)
	{
		if ($p == '') continue;
		$insert = "INSERT INTO jb_albums (id_champ, id_saison, id_theme, commentaire, date, photo) VALUES (".$sess_context->getRealChampionnatId().", ".$sess_context->getChampionnatId().", ".$idg.", '', SYSDATE(), '".$p."');";
		$res = dbc::execSQL($insert);
	}
}

?>

<span class="hack_ie">_HACK_IE_</span><script>mm({action:'photos', idg:'<?= $idg ?>'}); $cMsg({ msg: 'Album <?= $modifier ? "modifié" : "ajouté" ?>' });</script></div>
