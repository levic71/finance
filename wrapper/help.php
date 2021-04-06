<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

Wrapper::template_box_start(10);

?>

<style>

/*Vertical Tabs*/
.vertical-mdl-tabs {
	margin-top: 30px;
}
.vertical-mdl-tabs .mdl-tabs__tab-bar {
	-webkit-flex-direction: column;
	-ms-flex-direction: column;
	flex-direction: column;
	padding-bottom: 35px;
	height: inherit;
	border-bottom: none;
	border-right: 1px solid rgba(10, 11, 49, 0.20);
}

.vertical-mdl-tabs .mdl-tabs__tab {
	width: 100%;
	height: 35px;
	line-height: 35px;
	box-sizing: border-box;
	letter-spacing: 2px;
}

.vertical-mdl-tabs.mdl-tabs.is-upgraded a.mdl-tabs__tab.is-active {
	border-right: 2px solid #ED462F;
}
.vertical-mdl-tabs.mdl-tabs.is-upgraded .mdl-tabs__tab.is-active:after {
	content: inherit;
	height: 0;
}

.vertical-mdl-tabs.mdl-tabs.is-upgraded .mdl-tabs__panel.is-active, .mdl-tabs__panel {
	padding: 0 30px;
}

.vertical-mdl-tabs.mdl-tabs .mdl-tabs__tab {
	text-align: left;
}

.mdl-tabs__tab {
	font-size: 12px;
	text-transform: none;
}

</style>

<div class="mdl-card__title mdl-color--primary mdl-color-text--white">
	<h2 class="mdl-cell mdl-cell--12-col mdl-card__title-text mdl-color--primary">A LA DECOUVERTE DU JORKERS.COM</h2>
</div>
<div class="mdl-tabs vertical-mdl-tabs mdl-js-tabs mdl-js-ripple-effect">
	<div class="mdl-grid mdl-grid--no-spacing">
		<div class="mdl-cell mdl-cell--4-col">
			<div class="mdl-tabs__tab-bar">
				<a href="#tab1-panel" class="mdl-tabs__tab is-active"><span class="hollow-circle"></span>Le Jorkers.com : C'est Quoi ?</a>
				<a href="#tab2-panel" class="mdl-tabs__tab"><span class="hollow-circle"></span>Comment s'inscrire ?</a>
				<a href="#tab3-panel" class="mdl-tabs__tab"><span class="hollow-circle"></span>Acc�s � un championnat</a>
				<a href="#tab4-panel" class="mdl-tabs__tab"><span class="hollow-circle"></span>Gestion des joueurs</a>
				<a href="#tab5-panel" class="mdl-tabs__tab"><span class="hollow-circle"></span>Gestion des journ�es</a>
				<a href="#tab6-panel" class="mdl-tabs__tab"><span class="hollow-circle"></span>Gestion des matchs</a>
				<a href="#tab7-panel" class="mdl-tabs__tab"><span class="hollow-circle"></span>Gestion des saisons</a>
				<a href="#tab8-panel" class="mdl-tabs__tab"><span class="hollow-circle"></span>Statistiques</a>
				<a href="#tab9-panel" class="mdl-tabs__tab"><span class="hollow-circle"></span>Divers</a>
			</div>
		</div>
		<div class="mdl-cell mdl-cell--8-col">
			<div class="mdl-tabs__panel is-active" id="tab1-panel">
				<p>
					Bienvenue sur le site du Jorkers.com. Ce site s'adresse aux personnes qui pratiquent
					le <a href="#">Foot 2x2</a>, Futsal, Football, ... et qui souhaitent g�rer un championat ou tournoi de mani�re � pouvoir
					suivre les matchs jou�s par journ�es et pouvoir ainsi �tablir des statisques amusantes et s�rieuses.
					</p><p>
					Pour cela, il est n�cessaire de jouer r�guli�rement au Foot 2x2 et d'?tre au moins 4 joueurs ou 2 �quipes.
					</p><p>
					Vous pouvez g�rer des championnats de type libre, de type championnat de France et des tournois. Cette diff�renciation de ces type de championnat
					permet de prendre en compte les sp�cificit�s de chacun.
					</p><p>
					Ce site est totalement gratuit et ne vous engage � rien.
					</p><p>
					Pour en savoir plus sur ce site, je vous invite soit � consulter les championnats d�mos (accessibles depuis la page d'accueil) et
					� vous inscrire directement pour mieux vous rendre compte des possibit�s offertes.
					</p><p>
					<u>Attention</u>: <b>Les cookies doivent �tre activ�s et autoris�s sur votre navigateur web.</b>
				</p>
			</div>
			<div class="mdl-tabs__panel" id="tab2-panel">
				<p>
					Pour d�buter un championnat ou un tournoi, c'est simple, il suffit de retourner sur la page d'accueil et cliquer sur 's'inscrire' et
					de remplir le formulaire d'inscription.
				</p>
				<ul compact type=circle>
					<li>Nom du championnat (Tr�s important)
					<li>Description du championnat
					<li>Type du championnat<BR />
						<ul>
							<li><b>Gestion Libre</b> : Pas de contraintes particuli�res sur les �quipes (rencontres au fil de l'eau)</li>
							<li><b>Gestion championnat</b> : Affrontement de toutes les �quipe du championnats sur une journ�e</li>
							<li><b>Tournoi</b> : Affrontement de plusieurs �quipe sur une journ�e avec poules de qualification + phase finale</li>
						</ul>
					<li>Lieu de pratique
					<li>Nom du gestionnaire du championnat
					<li>Email du gestionnaire du championnat
					<li>Edito de news du championnat
					<li>Mode de visualisation des journ�es du championnat
						<ul>
							<li><b>Calendrier</b> : Les journ�es jou�s du championnat apparaissent dans un calendrier (6 mois affich�s)</li>
							<li><b>Liste</b> : Les journ�es jou�s du championnat apparaissent dans une liste class?e par ordre chronologique</li>
						</ul>
					<li>Affection des points pour une victoire et une d�faite
					<li>Informations en page d'accueil: Les cases � cocher permettent de personnaliser l'affichage de la page d'accueil du championnat
					<li>acc�s classement joueurs: Permet de proposer ou pas l'affichage du claseement des joueurs (s'il n'est pas pertinent)
					<li>Nom premi�re saison : Comme dans tous championnats il existe une notion de saison, il faut donc donner le nom de la premi�re
					<li> Championnats amis : S�lection des championnats dits 'amis'. Un acc�s est positionner sur votre championnat pour en faciliter l'acc�s.
					<li>Login d'administration du championnat
					<li>Mot de passe d'administration du championnat
				</ul>
				<p>
					Le login et le mot de passe d'administration servent � r�aliser des op�rations particuli�res (gestion joueurs, journ�es, ...) uniquement
					accessibles au gestionnaire du championnat. Les autres joueurs ne pourront que consulter les informations mise � leurs
					dispositions.
					<br />
					Apr�s la cr�ation, il faut cr�er les joueurs et les �quipe du championnat.
				</p>
			</div>
			<div class="mdl-tabs__panel" id="tab3-panel">
				<p>
					Apr�s avoir cr�er votre championnat, il faut vous connecter � votre championnat. Lors de votre inscription, vous �tes
					automatiquement rediger sur votre championnat en mode 'administration'. Vous pouvez alors mettre � jour votre championnat.<BR />
					Par la suite, l'acc�s � votre championnat se fait toujours par la page d'accueil en saisissant le nom de votre championnat + [ENTER].
					Une fois connect�, vous avez acc�s � toutes les informations de base de votre championnat : liste des joueurs, des �quipe, des journ�es jou�es, des statistiques.<br />
				</p>
				<p>Pour passer en mode administration, vous devez cliquer sur ??? et saisir votre login et mot de passe.</p>
			</div>
			<div class="mdl-tabs__panel" id="tab4-panel">
				<p>
					L'ajout des joueurs est indispensable pour la gestion de votre championnat. Cliquer sur le menu 'joueurs' pour faire appara�tre
					la liste des joueurs qui normalement est vide. Pour ajouter un nouveau joueur, vous devez passer en mode administration et
					cliquez sur le bouton 'Ajouter'.
					<br /><br />
					Saisissez les donn�es du formulaire pour chaque joueur de votre championnat. Le champ 'pseudo' est tr�s utilis�, il sert de
					diminutif dans l'ensemble des �crans. Si vous ne savez pas quoi mettre, reporter le pr?nom en �vitant les doublons. Un joueur
					est consid�r� comme 'r�gulier' lorsqu'il participe souvent aux journ�es du championnat, sinon il est pr�f�rable de le
					d�clarer 'non r�gulier' c'est � dire occasionel. Cette information est utile pour les statistiques.
					<br /><br />
					A chaque fois qu'un joueur est ajout� dans le championnat, le site cr�� automatiquement toutes les �quipe possible
					qui lient ce nouveau aux joueurs d�j� existant. gr�ce au menu 'Equipes' vous pouvez personnaliser ces �quipes.
				</p>
			</div>
			<div class="mdl-tabs__panel" id="tab5-panel">
				<p>
					En cliquant sur 'journ�es', un calendrier apparait. En mode 'administration', vous cliquer sur les num�ros des dates pour
					ajouter une nouvelle journ�e � votre championnat.
				</p><p>
					Une journ�e correspond � session de jeu entre plusieurs joueurs qui se sont affront�s lors d'une journ�e de Foot 2x2.
					Cette journ�e est donc compos?e de matchs disput�s entre ces joueurs, les �quipe pouvant �tre diff�rentes pour chaque
					match. Il n'y aucune contrainte particuli�re.
				</p><p>
					Le formulaire de saisie d'une journ�e vous propose la liste des joueurs potentiels qui ont particip� (ou participeront)
					? cette journ�e. Il n'est pas obligatoire que tous les joueurs participent � toutes journ�es. Il ne faut pas mettre les
					joueurs qui n'ont pas particip�, cela influence leurs statistiques.
				</p><p>
					Le site vous propose la cr�ation automatique des matchs pour la journ�e que vous �tes en train de cr�er. Cette option est
					facultative, mais permet de g�n�rer des rencontres al�atoires. L'exp�rience montre qu'il y a souvent des impr�vus et que
					de ce fait, les matchs jou�s sont souvent diff�rents de ceux qui ont �t� planifi�s. Peu importe, les matchs qui ne sont pas
					jou�s peuvent supprim�s. Si vous pratiquez une gestion des matchs sur le 'tas', alors n'utiliser pas cette option.
				</p><p>
					Une fois que votre journ�e est planifi�e, elle est rep�rable dans le calendrier gr�ce � un ballon. En cliquant sur cette
					ic�ne, vous acc�dez � la liste des matchs de cette journ�e.
				</p><p>
					Si vous �tes un grand nombre de joueurs et que vous �talez les rencontres d'une journ�e sur plusieurs jours, il
					faut cr�er une journ�e pivot qui rassemblera toutes ces rencontres.
				</p>
			</div>
			<div class="mdl-tabs__panel" id="tab6-panel">
				<p>
					En cliquant sur 'Ajouter', vous acc�dez � un formulaire qui vous propose de saisir un match, c'est � dire 2 �quipes et
					le score. Automatiquement le classement de cette journ�e se met � jour, mettant en �vidence le meilleur joueur.
				</p><p>
					Un match peut se jouer en un seul set gagnant ou en 3 sets gagnants. Cette gestion est libre, les statistiques sont bas�es sur
					les victoires/d�faites. On peut tr�s bien mixer les 2.
				</p>
				<p>Un sous menu <img src="../images/submenu.gif" border="0" alt="" /> vous donne acc�s � certaines fonctionnalit�s particuli�res :</p>
				<ul compact type=circle>
					<li>Ajout automatique : Cette option vous permet de g�n�rer des matchs automatiquement.
					<li>Saisie de tous les r�sultats : Cette option vous facilite la saisie de l'ensemble des r�sultats d'une journ�e.
					<li>Synchronisation des joueurs avec la journ�e : Cette option ne s'utilise que lorsqu'il y a des probl�mes de BD.
					<li>Suppression d'un joueur de la journ�e.
					<li>Suppression de tous les matchs : Cette option supprime tous les matchs de la journ�e.
					<li>Suppression de la journ�e : Cette option supprime tous les matchs et la journ�e.
				</ul>
			</div>
			<div class="mdl-tabs__panel" id="tab7-panel">
				A faire ...
			</div>
			<div class="mdl-tabs__panel" id="tab8-panel">
				<p>
					Le suivi des statistiques permet d'�tablir des statistiques globales au championnat, permettant ainsi de conna�tre
					les meilleurs joueurs, l'?tat de forme de chaque joueur et un tas d'autres indices. Des statistiques individuelles
					sont aussi visualisables.
					<br />
					Quelques explications :
				</p>
				<ol type="I" start="1">
					<li>On s�pare les statistiques des joueurs r�guliers et occasionnels, sinon il y aurait des risques de statistiques fauss�es.
						Il est souvent plus difficile de faire de bonnes stats r�guli�rement que de faire un coup d'�clat ponctuel.
					<li>% de matchs jou�s = Nb de matchs jou�s / Nb de matchs jou�s au total (r�guliers+occasionnels)
					<li>% de matchs gagn�s = Nb de matchs gagn�s / Nb de matchs jou�s
					<li>Forme - Etat = Analyse des �carts de la courbe des victoires sur les 4 derni�res journ�es
						<table border="0" cellpadding="0" cellspacing="0">
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche0.gif" border="0" alt="" /> </td><td>&nbsp; : N'a pas particip� � au moins 3 des 4 derni�res journ�es.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche1.gif" border="0" alt="" /> </td><td>&nbsp; : Courbe des victoires en chute libre.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche2.gif" border="0" alt="" /> </td><td>&nbsp; : Courbe des victoires en baisse.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche3.gif" border="0" alt="" /> </td><td>&nbsp; : Courbe des victoires stable.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche4.gif" border="0" alt="" /> </td><td>&nbsp; : Courbe des victoires en hausse.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche5.gif" border="0" alt="" /> </td><td>&nbsp; : Courbe des victoires en forte hausse.</td></table></td>
						</table>
					<li>Forme - Last = % de matchs gagn�s sur la derni�re journ�e jou�e
						<table border=0 cellpadding=0 cellspacing=0>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche1.gif" alt="" /> </td><td>&nbsp; : % de matchs gagn�s tr�s inf�rieur � sa moyenne globale.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche2.gif" alt="" /> </td><td>&nbsp; : % de matchs gagn�s inf�rieur � sa moyenne globale.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche3.gif" alt="" /> </td><td>&nbsp; : % de matchs gagn�s conforme � sa moyenne globale.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche4.gif" alt="" /> </td><td>&nbsp; : % de matchs gagn�s sup�rieur � sa moyenne globale.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche5.gif" alt="" /> </td><td>&nbsp; : % de matchs gagn�s tr�s sup�rieur � sa moyenne globale.</td></table></td>
						</table>
					<li>Podium - 1er = Nb de fois o� un joueur est premier au classement d'une journ�e
					<li>Podium - 2me = Nb de fois o� un joueur est second au classement d'une journ�e
					<li><img src="../images/etoiles/etoile_or.gif" alt="" /> pour le meilleur m�daill� du championnat
					<li><img src="../images/etoiles/etoile_argent.gif" alt="" /> pour le meilleur performeur du championnat (meilleur % de matchs gagn�s)
				</ol>
			</div>
			<div class="mdl-tabs__panel" id="tab9-panel">
				<p>
					Un forum par championnat est � votre disposition.
					<br /><br />
					Bon Jorky � tous ...
				</p>
			</div>
		</div>
	</div>
</div>

<? Wrapper::template_box_end(); ?>