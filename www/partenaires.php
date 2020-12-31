<?

include "../include/sess_context.php";

ini_set("url_rewriter.tags","input=src");
ini_set('arg_separator.output', '&amp;');

session_start();

$jorkyball_redirect_exception = 1;

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

$db = dbc::connect();

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

if (!isset($id_part)) $id_part = 0;

if ($id_part == 0)
	Toolbox::trackUser(isset($sess_context) && $sess_context->isChampionnatValide() ? $sess_context->getRealChampionnatId() : 0, _TRACK_PARTENAIRE_);

?>

<form action="../www/devenir_partenaire.php" method="post">

<div id="pageint" style="margin-bottom: 0px">

<h2>Partenaires</h2>

<?

if ($id_part == 0)
{
	$infos = "<table summary=\"\" border=\"0\" class=\"part_main\">";
	
	while(list($cle, $val) = each($partenaire))
	{
		$infos .= "
		<tr><td>
		<div class=\"titre\"><a class=\"cmd\" href=\"../www/partenaires.php?id_part=".$cle."\">".$val['nom']."</a></div>
		<div class=\"corps\">
			<div class=\"detail\">
				<br /><a href=\"mailto:".$val['email']."\">Cliquez ici pour envoyer un mail</a>
				<br />".$val['adresse']."
				<br />".$val['cp']." ".$val['ville']."
				<br />Téléphone : ".$val['tel']."
			</div>
			<div class=\"infos\"><a href=\"../www/partenaires.php?id_part=".$cle."\"><img src=\"../images/admin_plusdinfo.gif\" alt=\"\" /></a></div>
		</div>
		</td></tr>
		";
	}
		
	$infos .= "</table>";
}
else
{
	$infos = "<div class=\"part_main\">";
	$infos .= "
	<div class=\"titre\"><a class=\"cmd\" href=\"#\">".$partenaire[$id_part]['nom']."</a></div>
	<div class=\"corps\" style=\"margin: 10px 0px 20px 50px;\">
		<div class=\"detail2\" style=\"margin: 0px;\">
			<div class=\"item\">
				<div class=\"left\">Site internet : </div>
				<div class=\"right\">
					<a href=\"".$partenaire[$id_part]['web']."\">Cliquez ici pour accéder au site web</a>
				</div>
			</div>
			<div class=\"item\">
				<div class=\"left\">Mail : </div>
				<div class=\"right\">
					<a href=\"mailto:".$partenaire[$id_part]['email']."\">Cliquez ici pour envoyer un mail</a>
				</div>
			</div>
			<div class=\"item\">
				<div class=\"left\">Adresse : </div>
				<div class=\"right\">".$partenaire[$id_part]['adresse']."</div>
				<div class=\"left\">&nbsp;</div>
				<div class=\"right\">".$partenaire[$id_part]['cp']." ".$partenaire[$id_part]['ville']."</div>
			</div>
			<div class=\"item\">
				<div class=\"left\">Téléphone : </div>
				<div class=\"right\">".$partenaire[$id_part]['tel']."</div>
			</div>
		</div>
	</div>
	<div class=\"titre\"><a class=\"cmd\" href=\"#\">Information pratiques</a></div>
	<div class=\"corps\">
			<div class=\"item\">
				<table border=0 width=\"100%\"><tr>
					<td align=\"right\">Plan d'accès : </td>
					<td align=\"left\"><a href=\"partenaires_map.php?id_part=".$id_part."\"><img src=\"".$partenaire[$id_part]['plan']."\" height=\"100\" width=\"100\" alt=\"\" /></a></td>
					<td align=\"right\"><div class=\"left\">Configuration : </div></td>
					<td align=\"left\"><a href=\"partenaires_map.php?configuration=1&amp;id_part=".$id_part."\"><img src=\"".$partenaire[$id_part]['config']."\" height=\"100\" width=\"100\" alt=\"\" /></a></td>
				</tr></table>
			</div>
	</div>
	";

	$scs = new SQLChampionnatsServices();
	$rows = $scs->getAllChampionnatsByTown($id_part == 1 ? "alfortville" : "chatillon");
	if (count($rows) > 0)
	{
		$infos .= "
		<div class=\"titre\"><a class=\"cmd\" href=\"#\">Liste des championnats/tournois</a></div>
		<div class=\"corps\">
		";
		foreach($rows as $ch)
			$infos .= "<table border=0 cellpadding=0 cellspacing=0><tr><td style=\"width: 200px; float: left;\"><a class=\"blue\" href=\"../www/championnat_acces.php?ref_champ=".$ch['id']."\">".htmlspecialchars($ch['nom'])."</a></td><td style=\"width: 200px;\"><img src=\"../images/jorkers/images/".$icon_type[$ch['type']]."\" alt=\"\" />&nbsp;".$libelle_type[$ch['type']]."</td></tr></table>";
		$infos .= "
		</div>
		";
	}
	$infos .= "</div>";
}

 echo $infos;

?>

</div>

<div><table border="0" summary="">
	<tr>
	<? if ($id_part != 0) { ?>
		<td><input onclick="document.forms[0].action='../www/partenaires.php';" type="submit" value="Retour à la liste" /></td>
	<? } ?>
		<td><input type="submit" value="Devenir partenaire" /></td>
	</tr>
</table></div>

</form>

<? $menu->end(); ?>

