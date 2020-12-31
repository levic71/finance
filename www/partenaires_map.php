<?

include "../include/sess_context.php";

ini_set("url_rewriter.tags","input=src");
ini_set('arg_separator.output', '&amp;');

session_start();

$jorkyball_redirect_exception = 1;

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";

if (!isset($configuration)) $configuration = 0;

$db = dbc::connect();

if (isset($sess_context) && $sess_context->isChampionnatValide())
{
	$menu = new menu("full_access");
	$menu->debut($sess_context->getChampionnatNom(), "", $configuration == 0 ? "loadmap()" : "");
}
else
{
	$menu = new menu("forum_access");
	$menu->debut("", "",  $configuration == 0 ? "loadmap()" : "");
}

if (!isset($id_part)) $id_part = 1;

if ($id_part == 0)
	Toolbox::trackUser(isset($sess_context) && $sess_context->isChampionnatValide() ? $sess_context->getRealChampionnatId() : 0, _TRACK_PARTENAIRE_);

?>

<form action="../www/devenir_partenaire.php" method="post">

<div id="pageint" style="margin-bottom: 0px">

<h2>Partenaires</h2>

<?
	$infos = "";
	$infos .= "
<div class=\"part_main\">
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
	</div>";
	
if ($configuration == 0) 
{
	$infos .= "
	<div style=\"width: 600px;\">
		Acces direct :
		<select id=\"speedaccess\" onchange=\"javascript:zoompoint(document.getElementById('speedaccess'));\">
			<option /> 
		</select>
	</div>
	<div id=\"map\" style=\"width: 600px; height: 500px; border: 4px solid #AAAAAA;\"></div>
	<br />
	<div class=\"titre\"><a class=\"cmd\" href=\"#\">Cliquer sur les ballons pour zoomer</a></div>
	<div class=\"titre\"><a class=\"cmd\" href=\"#\">Double cliquer sur les ballons pour revenir au zoom initial</a></div>
	<div class=\"titre\"><a class=\"cmd\" href=\"javascript:zoom('paris');\">Zoom Paris</a></div>
	<div class=\"titre\"><a class=\"cmd\" href=\"javascript:zoom('france');\">Zoom France</a></div>
	";
}
else
{
	$infos .= "<div id=\"config\"><img style=\"width: 400px; height: 260px;\" src=\"".$partenaire[$id_part]['config']."\" /></div>";
}

	$infos .= "</div>";
	
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

