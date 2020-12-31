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
	$menu->debut($sess_context->getChampionnatNom(), "03");
}

if (!isset($tromb) || $sess_context->isAdmin()) $tromb = 0;

?>

<form action="joueurs.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="type_action" value="" />
<input type="hidden" name="pkeys_where" value="" />

<?

$options = explode('|', $sess_context->getChampionnatOptions());

$fxlist = new FXListPlayers($sess_context->getRealChampionnatId(), $sess_context->getChampionnatType(), $sess_context->getChampionnatId(), $sess_context->isAdmin(), (isset($options[6]) && $options[6] == 1) ? 1 : 0, $tromb == 0 ? "" : _FXLIST_FULL_);
$fxlist->FXSetPagination("joueurs.php");

if ($tromb == 0)
{
?>

<table border="0" cellpadding="0" cellspacing="0" width="700" summary="tab central">
<tr><td>
<? $fxlist->FXDisplay(); ?>
</td></tr>

<?
if ($sess_context->isAdmin() && !(isset($FXOption) && $FXOption == _FXLIST_EXPORT_))
{
	echo "<tr><td><table border=\"0\" width=\"100%\" summary=\"\">";
	echo "	<tr><td align=\"right\"><input type=\"submit\" name=\"bouton\" value=\"Ajouter un joueur\" onclick=\"javascript:ajouter_joueur();\" /></td></tr>";
	echo "</table></td></tr>";
}
?>
</table>

<?
} else {
?>
<style>
.thumbnail {
	float: left;
	padding: 7px;
	margin: 5px;
	border: 1px solid #ddd;
	height: 105px;
	width: 90px;
}
.caption {
	font-size: 0.9em;
	padding-top: 0.2em;
}
#pageint:after {
	content: "."; 
	display: block; 
	clear: both; 
}
</style>
<div id="pageint">
<h2> Trombinoscope joueurs </h2>
<?
		foreach($fxlist->body->tab as $player)
		{
			$crash = explode('"', $player['photo']);
			$pseudo = $player['pseudo'];

/*			echo "<div>";
			echo ToolBox::ombre($crash[7], 100, 100);
			echo "<br />".$player['pseudo']."</div>";
*/			
echo <<<EOF
			<div class="thumbnail">
				<img src="$crash[7]" width="90" height="90" alt="" />
				<div class="caption">$pseudo</div>
			</div>
EOF;
		}
?>
</div>
<? } ?>

<div class="cmdbox">
<div><a class="cmd" href="joueurs.php?tromb=1">Trombinoscope</a></div>
<? if ($sess_context->isAdmin()) { ?>
<div><a class="cmd" href="joueurs_get_from_saisons.php">Récupérer des joueurs de saisons différentes</a></div>
<? if ($sess_context->getChampionnatType() == _TYPE_LIBRE_) { ?>
<div><a class="cmd" href="equipes_create_all.php">Créer toutes les équipes possibles</a></div>
<? } ?>
<? } ?>
</div>

<script type="text/javascript">
function ajouter_joueur()
{
    document.forms[0].action = 'joueurs_ajouter.php';
}
function modifier_joueur(pkeys, action)
{
	document.forms[0].type_action.value=action;
	document.forms[0].pkeys_where.value=pkeys;
    document.forms[0].action = 'joueurs_ajouter.php';

	document.forms[0].submit();
}
function supprimer_joueur(pkeys, action)
{
	if (!confirm('Cette suppression s\'applique sur toutes saisons, êtes-vous de vouloir supprimer ce joueur ?'))
		return false;

	document.forms[0].type_action.value=action;
	document.forms[0].pkeys_where.value=pkeys;
    document.forms[0].action = 'joueurs_supprimer_do.php';

	document.forms[0].submit();
}
</script>

</form>

<? if (isset($FXOption) && $FXOption == _FXLIST_EXPORT_) FXList::FXHTLMExportEnd(); else $menu->end(); ?>
