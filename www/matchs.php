<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "ManagerFXList.php";
include "StatsBuilder.php";

// On récupère le pkeys_where de la page de journee pour avoir l'id de la journee
$pkeys  = ToolBox::get_global("pkeys_where_jb_journees");
if ($pkeys != "") $sess_context->setJourneeId(str_replace(" WHERE id=", "", $pkeys));

$db = dbc::connect();

// Si on vient de page suivante/précédente, alors on recherche la journée à afficher
if ((isset($journee_prev) && $journee_prev == 1) || (isset($journee_next) && $journee_next == 1))
{
    $select = "select id from jb_journees WHERE id_champ=".$sess_context->getChampionnatId()." ORDER BY date ASC";
    $res = dbc::execSQL($select);
    while($row = mysql_fetch_array($res)) $id_journee[] = $row['id'];

    $index_selected = -1;
    if (count($id_journee) > 0)
    {
        while(list($cle, $valeur) = each($id_journee))
        {
            if ($valeur == $sess_context->getJourneeId())
            {
                $index_selected = $cle;
                break;
            }
        }
    }

    if ($index_selected != -1 && isset($journee_prev) && $journee_prev == 1)
        if ($index_selected != 0) $sess_context->setJourneeId($id_journee[--$index_selected]);

    if ($index_selected != -1 && isset($journee_next) && $journee_next == 1)
        if ($index_selected != (count($id_journee) - 1)) $sess_context->setJourneeId($id_journee[++$index_selected]);
}

// Traitement des erreurs
if (isset($errno) && $errno == 1) ToolBox::alert("Il n'y a plus d'équipes possible à ajouter.");
if (isset($errno) && $errno == 2) ToolBox::alert("Il n'y a pas de matchs à saisir.");
if (isset($errno) && $errno == 3) ToolBox::alert("Il n'y a plus de joueurs possible à ajouter.");

// Affichage du menu
$menu = new menu("full_access");
$menu->debut($sess_context->getChampionnatNom(), "12");

// On récupère les infos de la journée
$select = "SELECT * FROM jb_journees WHERE id=".$sess_context->getJourneeId();
$res = dbc::execSQL($select);
$row = mysql_fetch_array($res);

$classement_joueurs = "";
// Si le champ 'joueurs' est renseigné alors on affiche les stats des joueurs (en principe, jamais vide)
if ($row['joueurs'] != "")
{
   	// On récupères les infos des joueurs (avec init classement vierge si besoin)
   	$req = "SELECT * FROM jb_joueurs WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id IN (".ereg_replace(",$", "", str_replace(',,', ',', $row['joueurs'])).") ORDER BY pseudo ASC";
   	$res = dbc::execSql($req);
   	while($j = mysql_fetch_array($res))
   	{
   		if ($classement_joueurs != "") $classement_joueurs .= "|";
   		$joueurs[$j['id']] = strlen($j['pseudo']) > 0 ? $j['pseudo'] : $j['nom']." ".$j['prenom'];
   		$classement_joueurs .= $j['id']."@".StatJourneeJoueur::vierge();
   	}

   	if ($row['classement_joueurs'] != "") $classement_joueurs = $row['classement_joueurs'];
}

$classement_equipes = "";
// Si le champ 'equipes' est renseigné alors on affiche les stats des équipes
if ($row['equipes'] != "")
{
   	// On récupères les infos des joueurs (avec init classement vierge si besoin)
   	$req = "SELECT * FROM jb_equipes WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id IN (".$row['equipes'].") ORDER BY nom ASC";
   	$res = dbc::execSql($req);
   	while($eq = mysql_fetch_array($res))
    {
   		if ($classement_equipes != "") $classement_equipes .= "|";
   		$equipes[$eq['id']] = $eq['nom'];
   		$classement_equipes .= $eq['id']."@".StatJourneeTeam::vierge();
   	}

   	if ($row['classement_equipes'] != "") $classement_equipes = $row['classement_equipes'];
}

?>

<FORM ACTION=matchs.php METHOD=POST ENCTYPE="multipart/form-data">
<INPUT TYPE=HIDDEN NAME=type_action VALUE="">
<INPUT TYPE=HIDDEN NAME=pkeys_where VALUE="">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=700 SUMMARY="tab central" STYLE="margin-bottom: 5px;">

<?
	echo "<TR><TD>";
	$fxlist = new FXListMatchs($sess_context->getChampionnatId(), $sess_context->getJourneeId(), $sess_context->isAdmin());
	$libelle_journee = ToolBox::conv_lib_journee($row['nom']);
	$lib  = "<div class=\"tc_box\">";
	$lib .= "<div class=\"box1\"><A HREF=matchs.php?journee_prev=1><IMG SRC=../images/journee_prv.gif BORDER=0 ALT=\"Journée précédente\" /></A></div>";
	$lib .= "<div class=\"box2\">".ToolBox::mysqldate2date($row['date'])." : ".$libelle_journee."</div>";
	$lib .= "<div class=\"box3\"><A HREF=matchs.php?journee_next=1><IMG SRC=../images/journee_nxt.gif BORDER=0 ALT=\"Journée suivante\" /></A></div>";
	$lib .= "</div>";
	$fxlist->FXSetTitle($lib);
	if ($sess_context->isFreeXDisplay())
		$fxlist->FXSetColumnsName($sess_context->isAdmin() ? array("Equipe1 (Attaquant/Défenseur)", "Score", "Equipe2 (Attaquant/Défenseur)", "Action") : array("Equipe1 (Attaquant/Défenseur)", "Score", "Equipe2 (Attaquant/Défenseur)"));
	$fxlist->FXDisplay();
	echo "</TD>";
?>

<TR><TD HEIGHT=10> </TD>

<?
	// Stats Joueurs
	if ($sess_context->isFreeXDisplay())
	{
		if ($classement_joueurs != "")
		{
			echo "<TR><TD>";
			$fxlist = new FXListMatchsStatsJoueurs($classement_joueurs, $joueurs);
			$fxlist->FXDisplay();
			echo "</TD>";
		}
	}

	// Stats Equipes (pas vraiment utile !!!!!!)
	if ($sess_context->isChampionnatXDisplay() && false)
	{
		if ($classement_equipes != "")
		{
			echo "<TR><TD>";
			$fxlist = new FXListMatchsStatsEquipes($classement_equipes, $equipes);
			$fxlist->FXDisplay();
			echo "</TD>";
		}
	}

?>

<? if ($sess_context->isAdmin()) { ?>
<TR><TD ALIGN=RIGHT><INPUT TYPE=SUBMIT onClick="javascript:ajouter_match();" VALUE="Ajouter un match"></TD>
<? } ?>

</TABLE>

<? if ($sess_context->isAdmin()) { ?>
<DIV CLASS=cmdbox>
<? if ($sess_context->isFreeXDisplay()) { ?>
<div><a CLASS=cmd href="#" onClick="javascript:window.open('journees_mail2players.php', 'mail2players', 'width=460, height=350, screenX=100, screenY=100, pageXOffset=100, pageYOffset=100, alwaysRaised=yes, toolbar=no, location=no, personnalBar=no, status=no, menuBar=no');">Envoi d'un email aux participants</a></div>
<HR>
<div><a CLASS=cmd href="journees_ajouter_joueur.php?again=1">Ajout d'un joueur à cette journée</a></div>
<div><a CLASS=cmd href="journees_ajouter_do.php?again=1">Ajout automatique de matchs</a></div>
<? } ?>
<? if ($sess_context->isChampionnatXDisplay()) { ?>
<div  class="item"><a CLASS=cmd href="journees_ajouter_equipe.php?again=1">Ajout d'une équipe à cette journée</a></div>
<? } ?>
<div><a CLASS=cmd href="matchs_saisie_auto1.php">Saisie des résultats de tous les matchs</a></div>
<HR>
<div><a CLASS=cmd href="matchs_sync_joueurs.php">Synchronisation joueurs/equipes/classement avec les matchs joués</a></div>
<HR>
<div><a CLASS=cmd href="journees_renommer.php">Renommer le nom de la journée</a></div>
<HR>
<? if ($sess_context->isFreeXDisplay()) { ?>
<div><a CLASS=cmd href="journees_supprimer_joueur.php">Suppression d'un joueur de cette journée</a></div>
<? } ?>
<? if ($sess_context->isChampionnatXDisplay()) { ?>
<div><a CLASS=cmd href="journees_supprimer_equipe.php">Suppression d'une équipe de cette journée</a></div>
<? } ?>
<div><a CLASS=cmd href="#" onClick="javascript:delAllMatchs();">Suppression de tous les matchs</a></div>
<div><a CLASS=cmd href="#" onClick="javascript:delJournee();">Suppression de la journée entière</a></div>
</DIV>
<? } ?>

<SCRIPT>
function delAllMatchs()
{
	if (confirm("Etes-vous sur de vouloir supprimer tout les matchs enregistrés ?"))
	{
	    document.forms[0].action = 'matchs_suppression.php';
		document.forms[0].submit();

		return true;
	}

	return false;
}
function delJournee()
{
	if (confirm("Etes-vous sûr de vouloir supprimer cette journée ?"))
	{
	    document.forms[0].action = 'journees_supprimer_do2.php';
		document.forms[0].submit();

		return true;
	}

	return false;
}
function ajouter_match()
{
    document.forms[0].action = 'matchs_ajouter.php';
}
function modifier_match(pkeys, action)
{
	document.forms[0].type_action.value=action;
	document.forms[0].pkeys_where.value=pkeys;
    document.forms[0].action = 'matchs_ajouter.php';

	document.forms[0].submit();
}
function supprimer_match(pkeys, action)
{
	if (!confirm('Etes-vous de vouloir supprimer ce match ?'))
		return false;

	document.forms[0].type_action.value=action;
	document.forms[0].pkeys_where.value=pkeys;
    document.forms[0].action = 'matchs_supprimer_do.php';

	document.forms[0].submit();
}
</SCRIPT>
</FORM>

<br />

<!-- AddToAny BEGIN -->
<a class="a2a_dd" href="http://www.addtoany.com/share_save="><img src="http://static.addtoany.com/buttons/share_save_171_16.png" width="171" height="16" border="0" alt="Share"/></a>
<script type="text/javascript">
var a2a_config = a2a_config || {};
a2a_config.linkname = "Jorkers - <?= $sess_context->getChampionnatNom() ?> - Resultats journee";
a2a_config.desc = "Jorkers - <?= $sess_context->getChampionnatNom() ?> - Resultats journee";
a2a_config.title = "Jorkers - <?= $sess_context->getChampionnatNom() ?> - Resultats journee";
a2a_config.show_title = 1;
a2a_config.linkurl = "http://www.jorkers.com/www/journees_redirect.php?champ=<?= $sess_context->getRealChampionnatId() ?>&journee=<?= $row['id'] ?>";
</script>
<script type="text/javascript" src="http://static.addtoany.com/menu/page.js"></script>
<!-- AddToAny END -->


<? $menu->end(); ?>
