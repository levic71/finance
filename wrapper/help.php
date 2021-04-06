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
				<a href="#tab3-panel" class="mdl-tabs__tab"><span class="hollow-circle"></span>Accès à un championnat</a>
				<a href="#tab4-panel" class="mdl-tabs__tab"><span class="hollow-circle"></span>Gestion des joueurs</a>
				<a href="#tab5-panel" class="mdl-tabs__tab"><span class="hollow-circle"></span>Gestion des journées</a>
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
					le <a href="#">Foot 2x2</a>, Futsal, Football, ... et qui souhaitent gérer un championat ou tournoi de manière à pouvoir
					suivre les matchs joués par journées et pouvoir ainsi établir des statisques amusantes et sérieuses.
					</p><p>
					Pour cela, il est nécessaire de jouer régulièrement au Foot 2x2 et d'?tre au moins 4 joueurs ou 2 équipes.
					</p><p>
					Vous pouvez gérer des championnats de type libre, de type championnat de France et des tournois. Cette différenciation de ces type de championnat
					permet de prendre en compte les spécificités de chacun.
					</p><p>
					Ce site est totalement gratuit et ne vous engage à rien.
					</p><p>
					Pour en savoir plus sur ce site, je vous invite soit à consulter les championnats démos (accessibles depuis la page d'accueil) et
					à vous inscrire directement pour mieux vous rendre compte des possibités offertes.
					</p><p>
					<u>Attention</u>: <b>Les cookies doivent étre activés et autorisés sur votre navigateur web.</b>
				</p>
			</div>
			<div class="mdl-tabs__panel" id="tab2-panel">
				<p>
					Pour débuter un championnat ou un tournoi, c'est simple, il suffit de retourner sur la page d'accueil et cliquer sur 's'inscrire' et
					de remplir le formulaire d'inscription.
				</p>
				<ul compact type=circle>
					<li>Nom du championnat (Très important)
					<li>Description du championnat
					<li>Type du championnat<BR />
						<ul>
							<li><b>Gestion Libre</b> : Pas de contraintes particulières sur les équipes (rencontres au fil de l'eau)</li>
							<li><b>Gestion championnat</b> : Affrontement de toutes les équipe du championnats sur une journée</li>
							<li><b>Tournoi</b> : Affrontement de plusieurs équipe sur une journée avec poules de qualification + phase finale</li>
						</ul>
					<li>Lieu de pratique
					<li>Nom du gestionnaire du championnat
					<li>Email du gestionnaire du championnat
					<li>Edito de news du championnat
					<li>Mode de visualisation des journées du championnat
						<ul>
							<li><b>Calendrier</b> : Les journées joués du championnat apparaissent dans un calendrier (6 mois affichés)</li>
							<li><b>Liste</b> : Les journées joués du championnat apparaissent dans une liste class?e par ordre chronologique</li>
						</ul>
					<li>Affection des points pour une victoire et une défaite
					<li>Informations en page d'accueil: Les cases à cocher permettent de personnaliser l'affichage de la page d'accueil du championnat
					<li>accès classement joueurs: Permet de proposer ou pas l'affichage du claseement des joueurs (s'il n'est pas pertinent)
					<li>Nom première saison : Comme dans tous championnats il existe une notion de saison, il faut donc donner le nom de la première
					<li> Championnats amis : Sélection des championnats dits 'amis'. Un accès est positionner sur votre championnat pour en faciliter l'accès.
					<li>Login d'administration du championnat
					<li>Mot de passe d'administration du championnat
				</ul>
				<p>
					Le login et le mot de passe d'administration servent à réaliser des opérations particulières (gestion joueurs, journées, ...) uniquement
					accessibles au gestionnaire du championnat. Les autres joueurs ne pourront que consulter les informations mise à leurs
					dispositions.
					<br />
					Après la création, il faut créer les joueurs et les équipe du championnat.
				</p>
			</div>
			<div class="mdl-tabs__panel" id="tab3-panel">
				<p>
					Après avoir créer votre championnat, il faut vous connecter à votre championnat. Lors de votre inscription, vous êtes
					automatiquement rediger sur votre championnat en mode 'administration'. Vous pouvez alors mettre à jour votre championnat.<BR />
					Par la suite, l'accès à votre championnat se fait toujours par la page d'accueil en saisissant le nom de votre championnat + [ENTER].
					Une fois connecté, vous avez accès à toutes les informations de base de votre championnat : liste des joueurs, des équipe, des journées jouées, des statistiques.<br />
				</p>
				<p>Pour passer en mode administration, vous devez cliquer sur ??? et saisir votre login et mot de passe.</p>
			</div>
			<div class="mdl-tabs__panel" id="tab4-panel">
				<p>
					L'ajout des joueurs est indispensable pour la gestion de votre championnat. Cliquer sur le menu 'joueurs' pour faire apparaître
					la liste des joueurs qui normalement est vide. Pour ajouter un nouveau joueur, vous devez passer en mode administration et
					cliquez sur le bouton 'Ajouter'.
					<br /><br />
					Saisissez les données du formulaire pour chaque joueur de votre championnat. Le champ 'pseudo' est très utilisé, il sert de
					diminutif dans l'ensemble des écrans. Si vous ne savez pas quoi mettre, reporter le pr?nom en évitant les doublons. Un joueur
					est considéré comme 'régulier' lorsqu'il participe souvent aux journées du championnat, sinon il est préférable de le
					déclarer 'non régulier' c'est à dire occasionel. Cette information est utile pour les statistiques.
					<br /><br />
					A chaque fois qu'un joueur est ajouté dans le championnat, le site créé automatiquement toutes les équipe possible
					qui lient ce nouveau aux joueurs déjà existant. grâce au menu 'Equipes' vous pouvez personnaliser ces équipes.
				</p>
			</div>
			<div class="mdl-tabs__panel" id="tab5-panel">
				<p>
					En cliquant sur 'journées', un calendrier apparait. En mode 'administration', vous cliquer sur les numéros des dates pour
					ajouter une nouvelle journée à votre championnat.
				</p><p>
					Une journée correspond à session de jeu entre plusieurs joueurs qui se sont affrontés lors d'une journée de Foot 2x2.
					Cette journée est donc compos?e de matchs disputés entre ces joueurs, les équipe pouvant être différentes pour chaque
					match. Il n'y aucune contrainte particulière.
				</p><p>
					Le formulaire de saisie d'une journée vous propose la liste des joueurs potentiels qui ont participé (ou participeront)
					? cette journée. Il n'est pas obligatoire que tous les joueurs participent à toutes journées. Il ne faut pas mettre les
					joueurs qui n'ont pas participé, cela influence leurs statistiques.
				</p><p>
					Le site vous propose la création automatique des matchs pour la journée que vous êtes en train de créer. Cette option est
					facultative, mais permet de générer des rencontres aléatoires. L'expérience montre qu'il y a souvent des imprévus et que
					de ce fait, les matchs joués sont souvent différents de ceux qui ont été planifiés. Peu importe, les matchs qui ne sont pas
					joués peuvent supprimés. Si vous pratiquez une gestion des matchs sur le 'tas', alors n'utiliser pas cette option.
				</p><p>
					Une fois que votre journée est planifiée, elle est repérable dans le calendrier grâce à un ballon. En cliquant sur cette
					icône, vous accèdez à la liste des matchs de cette journée.
				</p><p>
					Si vous êtes un grand nombre de joueurs et que vous étalez les rencontres d'une journée sur plusieurs jours, il
					faut créer une journée pivot qui rassemblera toutes ces rencontres.
				</p>
			</div>
			<div class="mdl-tabs__panel" id="tab6-panel">
				<p>
					En cliquant sur 'Ajouter', vous accèdez à un formulaire qui vous propose de saisir un match, c'est à dire 2 équipes et
					le score. Automatiquement le classement de cette journée se met à jour, mettant en évidence le meilleur joueur.
				</p><p>
					Un match peut se jouer en un seul set gagnant ou en 3 sets gagnants. Cette gestion est libre, les statistiques sont basées sur
					les victoires/défaites. On peut très bien mixer les 2.
				</p>
				<p>Un sous menu <img src="../images/submenu.gif" border="0" alt="" /> vous donne accès à certaines fonctionnalités particulières :</p>
				<ul compact type=circle>
					<li>Ajout automatique : Cette option vous permet de générer des matchs automatiquement.
					<li>Saisie de tous les résultats : Cette option vous facilite la saisie de l'ensemble des résultats d'une journée.
					<li>Synchronisation des joueurs avec la journée : Cette option ne s'utilise que lorsqu'il y a des problèmes de BD.
					<li>Suppression d'un joueur de la journée.
					<li>Suppression de tous les matchs : Cette option supprime tous les matchs de la journée.
					<li>Suppression de la journée : Cette option supprime tous les matchs et la journée.
				</ul>
			</div>
			<div class="mdl-tabs__panel" id="tab7-panel">
				A faire ...
			</div>
			<div class="mdl-tabs__panel" id="tab8-panel">
				<p>
					Le suivi des statistiques permet d'établir des statistiques globales au championnat, permettant ainsi de connaître
					les meilleurs joueurs, l'?tat de forme de chaque joueur et un tas d'autres indices. Des statistiques individuelles
					sont aussi visualisables.
					<br />
					Quelques explications :
				</p>
				<ol type="I" start="1">
					<li>On sépare les statistiques des joueurs réguliers et occasionnels, sinon il y aurait des risques de statistiques faussées.
						Il est souvent plus difficile de faire de bonnes stats régulièrement que de faire un coup d'éclat ponctuel.
					<li>% de matchs joués = Nb de matchs joués / Nb de matchs joués au total (réguliers+occasionnels)
					<li>% de matchs gagnés = Nb de matchs gagnés / Nb de matchs joués
					<li>Forme - Etat = Analyse des écarts de la courbe des victoires sur les 4 dernières journées
						<table border="0" cellpadding="0" cellspacing="0">
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche0.gif" border="0" alt="" /> </td><td>&nbsp; : N'a pas participé à au moins 3 des 4 dernières journées.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche1.gif" border="0" alt="" /> </td><td>&nbsp; : Courbe des victoires en chute libre.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche2.gif" border="0" alt="" /> </td><td>&nbsp; : Courbe des victoires en baisse.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche3.gif" border="0" alt="" /> </td><td>&nbsp; : Courbe des victoires stable.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche4.gif" border="0" alt="" /> </td><td>&nbsp; : Courbe des victoires en hausse.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche5.gif" border="0" alt="" /> </td><td>&nbsp; : Courbe des victoires en forte hausse.</td></table></td>
						</table>
					<li>Forme - Last = % de matchs gagnés sur la dernière journée jouée
						<table border=0 cellpadding=0 cellspacing=0>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche1.gif" alt="" /> </td><td>&nbsp; : % de matchs gagnés très inférieur à sa moyenne globale.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche2.gif" alt="" /> </td><td>&nbsp; : % de matchs gagnés inférieur à sa moyenne globale.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche3.gif" alt="" /> </td><td>&nbsp; : % de matchs gagnés conforme à sa moyenne globale.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche4.gif" alt="" /> </td><td>&nbsp; : % de matchs gagnés supérieur à sa moyenne globale.</td></table></td>
							<tr><td><table cellpadding="0" cellspacing="0" border="0"><tr><td width="50"> </td><td><img src="img/fleches/fleche5.gif" alt="" /> </td><td>&nbsp; : % de matchs gagnés très supérieur à sa moyenne globale.</td></table></td>
						</table>
					<li>Podium - 1er = Nb de fois où un joueur est premier au classement d'une journée
					<li>Podium - 2me = Nb de fois où un joueur est second au classement d'une journée
					<li><img src="../images/etoiles/etoile_or.gif" alt="" /> pour le meilleur médaillé du championnat
					<li><img src="../images/etoiles/etoile_argent.gif" alt="" /> pour le meilleur performeur du championnat (meilleur % de matchs gagnés)
				</ol>
			</div>
			<div class="mdl-tabs__panel" id="tab9-panel">
				<p>
					Un forum par championnat est à votre disposition.
					<br /><br />
					Bon Jorky à tous ...
				</p>
			</div>
		</div>
	</div>
</div>

<? Wrapper::template_box_end(); ?>