<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

// type_mail = 0 : Envoi mail au webmaster du Jorkers
// type_mail = 1 : Envoi mail au gérant d un championnat
// type_mail = 2 : Envoi mail aux joueurs ayant participés à une journée donnée d un championnat
// type_mail = 3 : Envoi mail à tous les joueurs d un championnat
// type_mail = 4 : Envoi mail pour rejoindre le staff
// type_mail = 5 : Envoi mail pour se rattacher à un joueur

$_action_  = Wrapper::getRequest('_action_',  0);
$type_mail = Wrapper::getRequest('type_mail', 0);
$idd       = Wrapper::getRequest('idd',       0);
$idp       = Wrapper::getRequest('idp',       0);
$name      = Wrapper::getRequest('name',      0);
$date      = Wrapper::getRequest('date',      0);

unset($_SESSION['antispam']);
$_SESSION['antispam'] = ToolBox::getRand(5);

$test  = false;
$nom   = $test ? "Victor's - éééé" : "";
$email = $test ? "victor.ferreira@laposte.net" : "";
$sujet = $test ? "héhéhé's @+#'!" : "";
$msg   = $test ? "Cool,\n\nhéhéhé#hoho's\n\n@+" : "";
$ctrl  = $test ? $_SESSION['antispam'] : "";

$str = $sess_context->championnat['type'] == 2 ? "tournoi" : "championnat";

if ($type_mail == 0) {
    $lib = "Contacter le  webmaster du Jorkers.com";
} else if ($type_mail == 1) {
    $lib = "Contacter le g?rant du ".$str." '".$sess_context->getChampionnatNom()."'";
} else if ($sess_context->isAdmin() && $type_mail == 2) {
    $lib   = $date.": ".ToolBox::conv_lib_journee($name)." => Invitation";
    $nom   = $sess_context->championnat['gestionnaire'];
    $email = $sess_context->championnat['email'];
    $sujet = $lib;
    $msg   = "Bonjour,\n\nVous ?tes invit? ? participer ? la ".ToolBox::conv_lib_journee($name)." du ".$str." '".$sess_context->championnat['championnat_nom']."' qui aura lieu le ".$date.".\n\nMerci de me confirmer votre pr?sence.\n\nCordialement\n".$sess_context->championnat['gestionnaire'];
} else if ($sess_context->isAdmin() && $type_mail == 3) {
    $lib   = "Message aux joueurs";
    $nom   = $sess_context->championnat['gestionnaire'];
    $email = $sess_context->championnat['email'];
    $sujet = $lib;
    $msg   = "Bonjour,\n\nMessage ? l'intention de tous les joueurs du ".$str." '".$sess_context->championnat['championnat_nom']."'\n\nCordialement\n".$sess_context->championnat['gestionnaire'];
} else if ($sess_context->isUserConnected() && $type_mail == 4) {
    $lib   = "Rejoindre le staff du ".$str;
    $sujet = $lib;
    $msg   = "Bonjour,\n\nJ'aimerais rejoindre le staff du ".$str." '".$sess_context->championnat['championnat_nom']."', pouvez-vous me donner les droits d'administration.\n\nCordialement\n".$sess_context->user['pseudo'];
} else if ($sess_context->isUserConnected() && $type_mail == 5) {
    $sps   = new SQLJoueursServices($sess_context->getRealChampionnatId());
    $p     = $sps->getJoueur($idp);
    $lib   = "Rattachement ? un joueur du ".$str;
    $sujet = $lib;
    $msg   = "Bonjour,\n\nJe suis le joueur '".$p['nom']." ".$p['prenom']."' du ".$str." '".$sess_context->championnat['championnat_nom']."', pouvez-vous associer mon compte ? ce joueur.\n\nCordialement\n".$sess_context->user['pseudo'];
} else {
}

if ($sess_context->isUserConnected()) {
    $nom   = $sess_context->user['pseudo'];
    $email = $sess_context->user['email'];
}

$menu = '<button class="mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect mdl-color-text--white"><i class="mdl-textfield__icon material-icons">message</i></button>';

$items = array();
array_push($items, array("func" => "textfield_form", "id" => "nom",        "value" => $nom ,  "icon" => "account_circle", "libelle" => "Nom",     "nb_col" => 12, "required" => 1, "autofocus" => 1));
array_push($items, array("func" => "textfield_form", "id" => "email",      "value" => $email, "icon" => "mail",           "libelle" => "Email",   "nb_col" => 12, "required" => 1));
array_push($items, array("func" => "textfield_form", "id" => "sujet",      "value" => $sujet, "icon" => "label_outline",  "libelle" => "Sujet",   "nb_col" => 12, "required" => 1));
array_push($items, array("func" => "textarea_form",  "id" => "message",    "value" => $msg,   "icon" => "subject",        "libelle" => "Message", "nb_col" => 12, "required" => 1));
array_push($items, array("func" => "captcha_form",   "id" => "controle", "icon" => "border_color", "libelle" => "Je ne suis pas un robot", "nb_col" => 6));

$actions = array(0 => array("onclick" => "return annuler();"), 1 => array("onclick" => "return validate_and_submit();"));

Wrapper::build_form(array("title" => $lib, "menu" => $menu, "nb_col" => 8, "items" => $items, "actions" => $actions));

?>

<script>
validate_and_submit = function()
{
	if (!check_alphanumext(valof('nom'), 'Nom', -1))
		return false;
	if (!check_alphanumext(valof('email'), 'Email', -1))
		return false;
	if (!check_email(valof('email')))
		return false;
	if (!check_alphanumext(valof('sujet'), 'Sujet', 6))
		return false;
	if (!check_alphanumext(valof('message'), 'Message', 6))
		return false;
	if (!check_slide_value(slider))
		return false;
	params = '?type_mail=<?= $type_mail ?>&idp=<?= $idp ?>&idd=<?= $idd ?>&name=<?= $name ?>&date=<?= $date ?>'+attrs(['nom', 'email', 'sujet', 'message', 'controle']);
	xx({action:'leagues', id:'main', url:'contacter_do.php'+params});

	return true;
}
annuler = function()
{
	<? if ($type_mail == 2) { ?>
	mm({action:'matches', idj:'<?= $idd ?>', name:'<?= $name ?>', date:'<?= $date ?>'});
	<? } else if ($type_mail == 3) { ?>
	mm({action: 'players'});
	<? } else { ?>
	mm({action: '<?= $sess_context->isChampionnatValide() ? "dashboard" : "leagues" ?>' });
	<? } ?>
	return true;
}
var slider = unlocker_slider('controle');
</script>
