<?

include "../include/sess_context.php";

ini_set("url_rewriter.tags","input=src");
ini_set('arg_separator.output', '&amp;');

session_start();

$jorkyball_redirect_exception = 1;
$timeline_page = 1;

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

Toolbox::trackUser(0, _TRACK_SONDAGE_);

?>

<div id="pageint" style="margin-bottom: 0px; padding-bottom: 20px;">

<h2>Jorkers Time Line (Attention le chargement peu prendre quelque secondes)</h2>

<div id="my-timeline" style="height: 500px; border: 1px solid #aaa; font-size: 10px;">Service momentan�ment indisponible</div>
<div class="controls" id="controls"></div>

<div>
<br />
Ce module permet de visualiser toutes les journ�es saisies dans le Jorkers. Pour naviguer dans le temps, on utilise la souris en faisant un drag horizontal (clic bouton gauche maintenu + mouvement gauche/droite).
<br />
<br />
Pour avoir plus de d�tail sur un �v�nement, il suffit de cliquer dessus. Un filtre est �galement disponible pour filtrer ou de mettre en valeur (avec une couleur) certains �v�nements.
</div>

</div>

<? $menu->end(); ?>
