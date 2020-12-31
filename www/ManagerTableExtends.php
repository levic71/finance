<?

include "../include/ManagerTable.php";

// ///////////////////////////////////////////////////////////////////////////
//								MATCHS
// ///////////////////////////////////////////////////////////////////////////
class ManagerTableMatchs extends ManagerTable
{
	function ManagerTableMatchs($isAdmin, $idChampionnat, $idJournee, $nomJournee)
	{
?>
<SCRIPT>
linkset='<div  class="menuitems"><a href="#" onClick="javascript:window.open(\'journees_mail2players.php\', \'mail2players\', \'width=460, height=350, screenX=100, screenY=100, pageXOffset=100, pageYOffset=100, alwaysRaised=yes, toolbar=no, location=no, personnalBar=no, status=no, menuBar=no\');">Envoi d\'un email aux participants</a></div>';
linkset+='<div  class="menuitems"><a href="journees_ajouter_do.php?again=1">Ajout automatique</a></div>';
linkset+='<div class="menuitems"><a href="matchs_saisie_auto1.php">Saisie de tous les résultats</a></div>';
linkset+='<div class="menuitems"><a href="matchs_sync_joueurs.php">Synchronisation des joueurs avec la journée</a></div>';
linkset+='<HR>';
linkset+='<div class="menuitems"><a href="#" onClick="javascript:window.open(\'matchs_suppression_joueur.php\', \'mail2players\', \'width=460, height=350, screenX=100, screenY=100, pageXOffset=100, pageYOffset=100, alwaysRaised=yes, toolbar=no, location=no, personnalBar=no, status=no, menuBar=no\');">Suppression d\'un joueur de cette journée</a></div>';
linkset+='<div class="menuitems"><a href="matchs_suppression.php"    onClick="return confirm(\'Etes-vous sûr de vouloir supprimer tout les matchs enregistrés ?\');">Suppression de tous les matchs</a></div>';
linkset+='<div class="menuitems"><a href="journees_supprimer_do2.php" onClick="return confirm(\'Etes-vous sûr de vouloir supprimer cette journée ?\');">Suppression de la journée entière</a></div>';
</SCRIPT>
<?
		$this->ManagerTable("jb_matchs");
        $this->setColsDisplay(array("id_equipe1", "resultat", "id_equipe2"));
        $this->setColsOrderBy(array("id"));
        $this->setColsOblig(array("id_equipe1", "id_equipe2"));
        //$this->setColsLink(array("id_equipe1"=>"matchs.php?code_action=".ACTION_EDITER));
        $this->setColsHidden(array("id_champ", "id_journee"));
        $this->setIndexLists(array(	"id_equipe1"=>"id|nom|jb_equipes|id_champ=".$idChampionnat,
        							"id_equipe2"=>"id|nom|jb_equipes|id_champ=".$idChampionnat)
        							);
        $this->setValues(array("id_champ"=>$idChampionnat, "id_journee"=>$idJournee));
        $this->setColsFilter(array("id_champ"=>$idChampionnat, "id_journee"=>$idJournee));
        $this->setColsName(array("resultat"=>"Score", "id_equipe1"=>"Equipe 1 (Défenseur/Attaquant)", "id_equipe2"=>"Equipe 2 (Défenseur/Attaquant)"));
        $this->setColsAlign(array("RIGHT", "CENTER", "LEFT"));
        $this->setColsLength(array(280, 50, 320));
        $this->setPrimaryKeys(array("id"));
        $this->setColsOrderBy(array("id"));
        $this->setTitles(array(_LIST_LIB_ => "<TABLE BORDER=0 WIDTH=100%><TR><TD ALIGN=LEFT><A HREF=matchs.php?journee_prev=1><IMG SRC=../images/journee_prv.gif BORDER=0 ALT=\"Journée précédente\"></A></TD><TD ALIGN=CENTER><FONT CLASS=big>[".$nomJournee."] Matchs </FONT></TD><TD ALIGN=RIGHT><A HREF=matchs.php?journee_next=1><IMG SRC=../images/journee_nxt.gif BORDER=0 ALT=\"Journée suivante\"></A></TD></TABLE>", _ADD_LIB_ => "[".$nomJournee."] Ajout match", _DEL_LIB_ => "[".$nomJournee."] Suppression match", _UPDATE_LIB_ => "[".$nomJournee."] Maj match", _SEARCH_LIB_ => "[".$nomJournee."] Recherche match", _DETAIL_LIB_ => "[".$nomJournee."] Détail match"));
        $this->setTarget("matchs.php");
        $this->setAjouterJSCode("ajouter_match();");
        $this->setModifierJSCode("modifier_match");
        $this->setSupprimerJSCode("supprimer_match");
        if ($isAdmin)
        {
            $this->setAdmTools();
            $this->setStatusHtmlCode("<A href=\"#\" onMouseover=\"showmenu2(event,linkset, 280)\" onMouseout=\"delayhidemenu()\"><IMG SRC=../images/submenu.gif BORDER=0></A>");
        }
	}
}

// ///////////////////////////////////////////////////////////////////////////
//								JOURNEES
// ///////////////////////////////////////////////////////////////////////////
class ManagerTableJournees extends ManagerTable
{
	function ManagerTableJournees($isAdmin, $idChampionnat)
	{
?>
<SCRIPT>
function ajouter_journee()
{
    document.forms[0].action = 'journees_ajouter.php';
}
</SCRIPT>
<?
        $this->ManagerTable("jb_journees");
//        $this->setColsInSelect("*, \"<IMG BORDER=0 SRC=../images/small_team.gif></IMG>\" joueurs, \"<IMG BORDER=0 SRC=../images/small_ball.gif ALT='Gestion des matchs'></IMG>\" matchs");
        $this->setColsInSelect("*, CONCAT(nom, 'ème journée') nom, \"<IMG BORDER=0 SRC=../images/small_ball.gif ALT='Gestion des matchs'></IMG>\" matchs");
        $this->setColsDisplay(array("nom", "date", "heure", "duree", "matchs"));
        $this->setColsOrderBy(array("date desc"));
        $this->setColsOblig(array("nom"));
        $this->setColsLink(array("joueurs"=>"#\" onClick=\"javascript:alert('toto');\"", "matchs"=>"matchs.php"));
        $this->setColsHidden(array("id_champ", "joueurs", "classement_joueurs", "equipes", "classement_equipes", "pref_saisie"));
        $this->setValues(array("id_champ"=>$idChampionnat, "nom"=>"Journée du ".date("d/m/Y"), "heure"=>"21h00"));
        $this->setColsFilter(array("id_champ"=>$idChampionnat));
        $this->setColsName(array("nom" => "Nom", "date"=>"Date", "heure"=>"Heure", "duree"=>"Durée", "joueurs"=>"Joueurs", "matchs"=>"Matchs"));
        $this->setColsDefSelBox(array("duree" => array("135|2h15", "90|1h30", "45|45min")));
        $this->setColsLength(array(200, 100, 100, 100, 100));
        $this->setColsAlign(array("LEFT", "CENTER", "CENTER", "CENTER", "CENTER"));
        $this->setPrimaryKeys(array("id"));
        $this->setTitles(array(_LIST_LIB_ => "Liste des journées", _ADD_LIB_ => "Ajout d'une journée", _DEL_LIB_ => "Suppression d'une journée", _UPDATE_LIB_ => "Mise à jour d'une journée", _SEARCH_LIB_ => "Recherche d'une journée", _DETAIL_LIB_ => "Détail d'une journée"));
        $this->setTarget("journees.php");
        $this->setAjouterJSCode("ajouter_journee();");
        $this->setSupprimerJSCode("supprimer_journee");
        if ($isAdmin) $this->setAdmTools();
	}
}

// ///////////////////////////////////////////////////////////////////////////
//								CHAMPIONNATS
// ///////////////////////////////////////////////////////////////////////////
class ManagerTableChampionnats extends ManagerTable
{
	function ManagerTableChampionnats($isAdmin)
	{
        $this->ManagerTable("jb_championnat");
        $this->setColsDisplay(array("nom", "gestionnaire", "description"));
        $this->setColsOrderBy(array("nom"));
        $this->setColsName(array("nom"=>"Nom", "gestionnaire"=>"Gestionnaire", "description"=>"Description"));
        $this->setColsLink(array("nom"=>"championnat_aide.php"));
        $this->setColsLength(array(250, 250, 250));
        $this->setPrimaryKeys(array("id"));
        $this->setTitles(array(_LIST_LIB_ => "Liste des championnats", _SEARCH_LIB_ => "Recherche d'un championnat"));
        $this->setTarget("championnat_aide.php");
        $this->setColsHidden(array("login", "pwd", "dt_creation", "email", "description"));
        if ($isAdmin) $this->setAdmTools();
	}
}

// ///////////////////////////////////////////////////////////////////////////
//								JOUEURS
// ///////////////////////////////////////////////////////////////////////////
class ManagerTableJoueurs extends ManagerTable
{
	function ManagerTableJoueurs($isAdmin, $idChampionnat)
	{
?>
<SCRIPT>
linkset='<div class="menuitems"><a href="equipes_create_all.php">Création de toutes les équipes possibles</a></div>';
function ajouter_joueur()
{
    document.forms[0].action = 'joueurs_ajouter.php';
}
</SCRIPT>
<?
        $this->ManagerTable("jb_joueurs");
        $this->setColsInSelect("*, CONCAT(nom, ' ', prenom) nom, '<IMG SRC=../images/stats.gif BORDER=0>' stats");
        $this->setColsLink(array("stats"=>"stats_detail_joueur.php"));
        $this->setColsDisplay(array("photo", "nom", "pseudo", "presence", "stats"));
        $this->setColsAlign(array("CENTER", "LEFT", "LEFT", "CENTER", "CENTER"));
        $this->setColsYesNo(array("presence", "admin"));
        $this->setColsOrderBy(array("nom"));
        $this->setColsImage(array("photo"));
        $this->setColsOblig(array("nom", "pseudo"));
        $this->setColsHidden(array("id_champ"));
        $this->setValues(array("id_champ"=>$idChampionnat));
        $this->setColsFilter(array("id_champ"=>$idChampionnat));
        $this->setColsName(array("stats"=>"Statistiques", "email"=>"Email", "admin" => "Administrateur", "login" => "Login", "pwd" => "Mot de passe", "photo"=>"Photo", "nom"=>"Nom", "prenom"=>"Prénom", "pseudo"=>"Pseudo", "photo"=>"Photo", "dt_naissance"=>"Date de naissance", "presence"=>"Régulier"));
        $this->setColsLength(array(80, 230, 170, 80, 80));
        $this->setPrimaryKeys(array("id"));
        $this->setTitles(array(_LIST_LIB_ => "Liste des joueurs", _ADD_LIB_ => "Ajout d'un joueur", _DEL_LIB_ => "Suppression d'un joueur", _UPDATE_LIB_ => "Mise à jour d'un joueur", _SEARCH_LIB_ => "Recherche d'un joueur", _DETAIL_LIB_ => "Détail d'un joueur"));
        $this->setTarget("joueurs.php");
        $this->setAjouterJSCode("ajouter_joueur();");
        $this->setModifierJSCode("modifier_joueur");
        $this->setSupprimerJSCode("supprimer_joueur");
        if ($isAdmin)
        {
            $this->setAdmTools();
            $this->setStatusHtmlCode("<A href=\"#\" onMouseover=\"showmenu2(event,linkset, 280)\" onMouseout=\"delayhidemenu()\"><IMG SRC=../images/submenu.gif BORDER=0></A>");
        }
	}
}

// ///////////////////////////////////////////////////////////////////////////
//								EQUIPES
// ///////////////////////////////////////////////////////////////////////////
class ManagerTableEquipes extends ManagerTable
{
	function ManagerTableEquipes($isAdmin, $idChampionnat)
	{
?>
<SCRIPT>
linkset='<div class="menuitems"><a href="equipes_create_forone.php">Création de toutes les équipes pour un joueur</a></div>';
linkset+='<HR>';
linkset+='<div class="menuitems"><a href="equipes_create_all.php">Création de toutes les équipes possibles</a></div>';
function ajouter_equipe()
{
    document.forms[0].action = 'equipes_ajouter.php';
}
</SCRIPT>
<?
        $this->ManagerTable("jb_equipes");
        $this->setColsDisplay(array("nom", "nb_joueurs"));
        $this->setColsOrderBy(array("nom"));
        $this->setColsOblig(array("nom"));
//        $this->setColsInSelect("nom, MAKE_SET(1, 1, REPLACE(joueurs, '|', ',')) id_joueur1, MAKE_SET(2, 1, REPLACE(joueurs, '|', ',')) id_joueur2");
//        $this->setColsLink(array("nom"=>"equipes.php?code_action=".ACTION_EDITER));
        $this->setColsHidden(array("id_champ"));
        $this->setValues(array("id_champ"=>$idChampionnat));
        $this->setColsFilter(array("id_champ"=>$idChampionnat));
//        $this->setIndexLists(array(	"id_joueur1"=>"id|CONCAT(nom, \" \", prenom) |jb_joueurs|id_champ=".$idChampionnat,
//        							"id_joueur2"=>"id|CONCAT(nom, \" \", prenom) |jb_joueurs|id_champ=".$idChampionnat)
//        							);
        $this->setColsName(array("nom" => "Nom", "nb_joueurs"=>"Nb Joueurs"));
        $this->setColsAlign(array("LEFT", "CENTER"));
        $this->setColsLength(array(520, 80));
        $this->setPrimaryKeys(array("id"));
        $this->setTitles(array(_LIST_LIB_ => "Liste des équipes", _ADD_LIB_ => "Ajout d'une équipe", _DEL_LIB_ => "Suppression d'une équipe", _UPDATE_LIB_ => "Mise à jour d'une équipe", _SEARCH_LIB_ => "Recherche d'une équipe", _DETAIL_LIB_ => "Détail d'une équipe"));
        $this->setTarget("equipes.php");
        $this->setAjouterJSCode("ajouter_equipe();");
        $this->setModifierJSCode("modifier_equipe");
        $this->setSupprimerJSCode("supprimer_equipe");
        if ($isAdmin)
        {
            $this->setAdmTools();
            $this->setStatusHtmlCode("<A href=\"#\" onMouseover=\"showmenu2(event,linkset, 280)\" onMouseout=\"delayhidemenu()\"><IMG SRC=../images/submenu.gif BORDER=0></A>");
        }
	}
}

?>