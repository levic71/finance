<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$ids = isset($_REQUEST['ids']) && is_numeric($_REQUEST['ids']) && $_REQUEST['ids'] > 0 ? $_REQUEST['ids'] : 0;
$modifier = $ids > 0 ? true : false;

if ($modifier) {
	$sql = "SELECT * FROM jb_saisons WHERE id=".$ids;
	$res = dbc::execSQL($sql);
	$row = mysqli_fetch_array($res);
}

$nom     = $modifier ? $row['nom']     : "Saison ".date("Y")."-".(date("Y")+1);
$active  = $modifier ? $row['active']  : "1";

// Règles de gestion :
// Pour les championnats libres   : on récupère tous les joueurs du championnat et on propose de reconduire ceux de la saison en cours par défaut
// Pour les championnats+tournois : on récupère toutes les équipes du championnat/tournoi et on propose de reconduire celle de la saison en cours par défaut

// On récupère tous les joueurs du championnat
$sjs = new SQLJoueursServices($sess_context->getRealChampionnatId());
$joueurs = $sjs->getListeJoueurs();

$ses = new SQLEquipesServices($sess_context->getRealChampionnatId());
$equipes = $ses->getListeEquipes();

// Récupération des joueurs de la saison en cours
if ($modifier == 1)
{
	$lst_joueurs = $row['joueurs'];
	$lst_equipes = $row['equipes'];
}
else
{
	// On récupère les infos de la saison active
	$scs = new SQLChampionnatsServices($sess_context->getRealChampionnatId());
	$saison_active = $scs->getSaisonActive();
	$lst_joueurs = $saison_active['joueurs'];
	$lst_equipes = $saison_active['equipes'];
}


	$title = ($modifier ? "Modification" : "Ajout")." d'une saison";

	$items = array();
	array_push($items, array("func" => "textfield_form",        "id" => "nom", "value" => $nom, "icon" => "text_fields", "libelle" => "Nom", "nb_col" => 12, "required" => 1, "autofocus" => 1));
	array_push($items, array("func" => "choice_component_form", "id" => "selecteditems", "icon" => "group", "libelle" => $sess_context->isFreeXDisplay() ? "Joueurs" : "Equipes", "nb_col" => 10));
	array_push($items, array("func" => "freezone_form",         "id" => "add_item", "freezone" => "<button onclick=\"choices.picker('selecteditems');\" class=\"mdl-button mdl-js-button mdl-button--fab mdl-button--colored\"><i class=\"material-icons\">add</i></button>", "nb_col" => 2));
	array_push($items, array("func" => "choice_component_form", "id" => "active", "icon" => "power_settings_new", "libelle" => "Active", "nb_col" => 12, "grouped" => 1));

	$actions = array(0 => array("onclick" => "return annuler();"), 1 => array("onclick" => "return validate_and_submit();"));

	Wrapper::build_form(array('title' => $title, 'items' => $items, 'actions' => $actions));
?>


<script>
<?

$selected = array();

$values = "";
if ($sess_context->isFreeXDisplay())
{
	$res_joueurs = explode(',', $lst_joueurs);
	foreach($res_joueurs as $j) $tab_joueurs[$j] = $j;

	// Répartition des joueurs dans les listes adéquates
	foreach($joueurs as $j)
		$values .= ($values == "" ? "" : ",")."{ v: ".$j['id'].", l: '".Wrapper::stringEncode4JS($j['nom']." ".$j['prenom'])."', s: ".(isset($tab_joueurs[$j['id']]) ? "true" : "false")." }";
}
else
{
	$res_equipes = explode(',', $lst_equipes);
	foreach($res_equipes as $e) $tab_equipes[$e] = $e;

	// Répartition des joueurs dans les listes adéquates
	foreach($equipes as $e)
		$values .= ($values == "" ? "" : ",")."{ v: ".$e['id'].", l: '".Wrapper::stringEncode4JS($e['nom'])."', s: ".(isset($tab_equipes[$e['id']]) ? "true" : "false")." }";
}

?>

choices.build({ name: 'selecteditems', c1: 'orange', removable: true, multiple: true, values: [<?= $values ?>] });
choices.build({ name: 'active', c1: 'blue', c2: 'white', values: [ {v: 0, l: 'Non', s: <?= $active == 0 ? "true" : "false" ?>}, {v: 1, l: 'Oui', s: <?= $active == 1 ? "true" : "false" ?>} ] });

validate_and_submit = function()
{
    if (!check_alphanumext(valof('nom'), 'Nom', -1))
		return false;

	params = '?active='+choices.getSelection('active')+'&selecteditems='+choices.getSelection('selecteditems')+attrs(['nom']);
	xx({id:'main', url:'edit_seasons_do.php'+params+'&ids='+<?= $ids ?>+'&upd=<?= $modifier ? 1 : 0 ?>'});

	return true;
}
annuler = function()
{
	mm({action: 'seasons'});
	return true;
}

</script>
