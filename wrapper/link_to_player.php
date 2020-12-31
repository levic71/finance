<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$idp = isset($_REQUEST['idp']) && is_numeric($_REQUEST['idp']) && $_REQUEST['idp'] > 0 ? $_REQUEST['idp'] : 0;
$modifier = $idp > 0 ? true : false;

?>

<div id="edit_roles" class="edit">

<h2 class="grid"><?= $modifier ? "Lier à un joueur déjà inscrit" : "Créer un nouveau joueur et le rattacher un joueur déjà inscrit" ?></h2>

<table cellspacing="0" cellpadding="0" class="jkgrid">
<tbody>
<tr><td class="c1"><div><label for="status">Rechercher un joueur<br /><small>(nom, pseudo, ville)</small></label><div class="col2"><input type="text" id="search_player" /></div><div class="col3"><button onclick="go({action: 'players', id:'liste_inscrits', url:'link_to_player_search.php?search='+valof('search_player')});" class="button blue small">Rechercher</button></div></div></td></tr>
<? if ($sess_context->isFreeXDisplay()) { ?>
<tr><td class="c1"><div><label for="presence">Régulier</label><div id="presence" class="grouped" style="float:left; width: 200px;"></div></td></tr>
<tr><td class="c1"><div><label for="auto_create_team" class="dbline">Création automatique<br />des équipes</label><div id="auto_create_team" class="grouped" style="float:left; width: 460px;"></div></div></td></tr>
<? } else { ?>
<input type="hidden" id="presence" value="1" />
<input type="hidden" id="auto_create_team" value="2" />
<? } ?>
</tbody></table>

<table cellspacing="0" cellpadding="0" class="jkgrid">
<thead>
<tr><th><div>Liste des joueurs inscrits trouvés (nom - pseudo - ville)</div></th></tr>
</thead>
<tbody>
</tbody></table>

<div id="liste_inscrits" style="width: 100%;"></div>

<div class="actions grouped_inv">
<button onclick="return validate_and_submit();" class="button green"><?= $modifier ? "Valider" : "Ajouter" ?></button>
<button onclick="return annuler();" class="button gray">Annuler</button>
</div>

<script>
<? if ($sess_context->isFreeXDisplay()) { ?>
choices.build({ name: 'presence', c1: 'blue', c2: 'white', values: [ {v: 0, l: 'Non', s: false}, {v: 1, l: 'Oui', s: true} ] });
choices.build({ name: 'auto_create_team', c1: 'blue', c2: 'white', values: [{ v: 0, l: 'Oui', s: true}, { v: 1, l: 'Oui pour les joueurs réguliers'}, { v: 2, l: 'Non'}] });
<? } ?>
validate_and_submit = function()
{
	idu = valof('selected_player');
	if (idu.length == 0) {
		alert('Vous devez sélectionner un joueur inscrit !');
		return true;
	}

	var auto_create_team = <? if ($sess_context->isFreeXDisplay()) { ?>choices.getSelection('auto_create_team');<? } else { ?>valof('auto_create_team');<? } ?>
	var presence = <? if ($sess_context->isFreeXDisplay()) { ?>choices.getSelection('presence');<? } else { ?>valof('presence');<? } ?>

	go({id:'main', url:'edit_players_do.php?link_to_player=1&upd=<?= $modifier ? 1 : 0 ?>&del=0&idp=<?= $idp ?>&idu='+idu+'&auto_create_team='+auto_create_team+'&presence='+presence});

	return true;
}
annuler = function()
{
	mm({action: 'players'});
	return true;
}
</script>

</div>





