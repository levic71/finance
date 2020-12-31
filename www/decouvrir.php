<?

include "../include/sess_context.php";

session_start();

$jorkyball_redirect_exception = 1;

include "common.php";
include "ManagerFXList.php";
TemplateBox :: htmlBegin(false, false, "09");

$lib_accueil = (isset($faq) && $faq == 0) ? "<A HREF=home.php CLASS=white> >> Retour à l'accueil << </A>" : "&nbsp;";

$decouvrir_item = isset($decouvrir_item) ? $decouvrir_item : 1;

?>

<center>

<table id="decouvrir" style="background:#EEEEEE; width: 720px; height: 95%;" summary="" border="0" cellpadding="0" cellspacing="0">

<tr>
	<td height="40" colspan="2">
		<table style="background: url('../images/panneau.jpg'); height: 100%; width: 100%;" summary="" border="0">
			<tr><td align="center"> <font class="big" size="3" color="white"> A LA DECOUVERTE DU JORKERS.COM </font></td>
		</table>
	</td>

<tr valign="top">
<td style="width: 200px; border-right: 1px dashed #999999; background:#DDDDDD;">
	<table border="0" style="margin: 5 5 0 5;" summary="">
		<tr onmouseover="this.style.background='#EEEEEE';" onmouseout="this.style.background='';"><td align="left" class="item"><p class="p"><a href="decouvrir.php?decouvrir_item=1"  class="blue"> Le Jorkers.com : C'est Quoi ? </a></p></td>
		<tr onmouseover="this.style.background='#EEEEEE';" onmouseout="this.style.background='';"><td align="left" class="item"><p class="p"><a href="decouvrir.php?decouvrir_item=2"  class="blue"> Comment s'inscrire ? </a></p></td>
		<tr onmouseover="this.style.background='#EEEEEE';" onmouseout="this.style.background='';"><td align="left" class="item"><p class="p"><a href="decouvrir.php?decouvrir_item=4"  class="blue"> Accès à un championnat </a></p></td>
		<tr onmouseover="this.style.background='#EEEEEE';" onmouseout="this.style.background='';"><td align="left" class="item"><p class="p"><a href="decouvrir.php?decouvrir_item=5"  class="blue"> Gestion des joueurs </a></p></td>
		<tr onmouseover="this.style.background='#EEEEEE';" onmouseout="this.style.background='';"><td align="left" class="item"><p class="p"><a href="decouvrir.php?decouvrir_item=6"  class="blue"> Gestion des journées </a></p></td>
		<tr onmouseover="this.style.background='#EEEEEE';" onmouseout="this.style.background='';"><td align="left" class="item"><p class="p"><a href="decouvrir.php?decouvrir_item=7"  class="blue"> Gestion des matchs </a></p></td>
		<tr onmouseover="this.style.background='#EEEEEE';" onmouseout="this.style.background='';"><td align="left" class="item"><p class="p"><a href="decouvrir.php?decouvrir_item=10" class="blue"> Gestion des saisons </a></p></td>
		<tr onmouseover="this.style.background='#EEEEEE';" onmouseout="this.style.background='';"><td align="left" class="item"><p class="p"><a href="decouvrir.php?decouvrir_item=8"  class="blue"> Statistiques </a></p></td>
		<tr onmouseover="this.style.background='#EEEEEE';" onmouseout="this.style.background='';"><td align="left"><p class="p"><a href="decouvrir.php?decouvrir_item=9" class="blue"> Divers </a></p></td>
	</table>
</td>

<td align="left"><table border="0" cellpadding="0" cellspacing="0" summary="" style="width: 100%; margin: 5 5 0 5;">

<?

function display($titre, $section)
{
	echo "<tr><td style=\"width: 50px;\"><table border=\"0\" summary=\"\"><tr><td style=\"width: 40px;\" align=\"left\"><table border=0 style=\"background: #DDDDDD; height: 30px; width: 30px; border: 1px solid #AAAAAA;\" summary=\"\"><tr><td> </td></table></td><td><font size=\"2\"><u> ".$titre." </u></font></td></table></td>";
	echo "<tr><td><table border=\"0\" summary=\"\"><tr><td width=\"40\"> </td><td>".$section."</td></table>";
}

if ($decouvrir_item == 1)
{
$tab = "
<p style=\"text-align: justify;\"><font class=\"classic\">
Bienvenue sur le site du Jorkers.com. Ce site s'adresse aux personnes qui pratiquent
le <a class=\"blue\" href=\"#\">Foot 2x2</a>, Futsal, Football, ... et qui souhaitent gérer un championat ou tournoi de manière à pouvoir
suivre les matchs joués par journées et pouvoir ainsi établir des statisques amusantes et sérieuses.
<br /><br />
Pour cela, il est nécessaire de jouer régulièrement au Foot 2x2 et d'être au moins 4 joueurs ou 2 équipes.
<br /><br />
Vous pouvez gérer des championnats de type libre, de type championnat de France et des tournois. Cette différenciation de ces type de championnat
permet de prendre en compte les spécificités de chacun.
<br /><br />
Ce site est totalement gratuit et ne vous engage à rien.
<br /><br />
Pour en savoir plus sur ce site, je vous invite soit à consulter les championnats démos (accessibles depuis la page d'accueil) et
à vous inscrire directement pour mieux vous rendre compte des possibités offertes.
<br /><br />
<u>Attention</u>: <b>Les cookies doivent être activés et autorisés sur votre navigateur web.</b>
</font></p>";

display("Le Jorkers.com : C'est Quoi ?", $tab);

}


if ($decouvrir_item == 2)
{
$tab = "
<font class=\"classic\">
Pour débuter un championnat ou un tournoi, c'est simple, il suffit de retourner sur la page d'accueil et cliquer sur 's'inscrire' et
de remplir le formulaire d'inscription.
</font>
<ul compact type=circle>
<li>Nom du championnat (Très important)
<li>Description du championnat
<li>Type du championnat<BR />
	<ul>
		<li><b>Gestion Libre</b> : Pas de contraintes particulières sur les équipes (rencontres au fil de l'eau)</li>
		<li><b>Gestion championnat</b> : Affrontement de toutes les équipes du championnats sur une journée</li>
		<li><b>Tournoi</b> : Affrontement de plusieurs équipes sur une journée avec poules de qualification + phase finale</li>
	</ul>
<li>Lieu de pratique
<li>Nom du gestionnaire du championnat
<li>Email du gestionnaire du championnat
<li>Edito de news du championnat
<li>Mode de visualisation des journées du championnat
	<ul>
		<li><b>Calendrier</b> : Les journées joués du championnat apparaissent dans un calendrier (6 mois affichés)</li>
		<li><b>Liste</b> : Les journées joués du championnat apparaissent dans une liste classée par ordre chronologique</li>
	</ul>
<li>Affection des points pour une victoire et une défaite
<li>Informations en page d'accueil: Les cases à cocher permettent de personnaliser l'affichage de la page d'accueil du championnat
<li>Accès classement joueurs: Permet de proposer ou pas l'affichage du claseement des joueurs (s'il n'est pas pertinent)
<li>Nom première saison : Comme dans tous championnats il existe une notion de saison, il faut donc donner le nom de la première
<li> Championnats amis : Sélection des championnts dits 'amis'. Un accès est positionner sur votre championnat pour en faciliter l'accès.
<li>Login d'administration du championnat
<li>Mot de passe d'administration du championnat
</ul>
<font class=\"classic\">
Le login et le mot de passe d'administration servent à réaliser des opérations particulières (gestion joueurs, journées, ...) uniquement
accessibles au gestionnaire du championnat. Les autres joueurs ne pourront que consulter les informations mise à leurs
dispositions.
<br />
<br />
Après la création, il faut créér les joueurs et les équipes du championnat.
</font>";

display("Création d'un championnat", $tab);

}





if ($decouvrir_item == 4)
{
$tab = "
<font class=\"classic\">
Après avoir créer votre championnat, il faut vous connecter à votre championnat. Lors de votre inscription, vous êtes
automatiquement rediger sur votre championnat en mode 'administration'. Vous pouvez alors mettre à jour votre championnat.<BR />
Par la suite, l'accès à votre championnat se fait toujours par la page d'accueil en saisissant le nom de votre championnat + [ENTER].
Une fois connecté, vous avez accès à toutes les informations de base de votre championnat : liste des joueurs, des équipes, des journées jouées, des statistiques.<br /></font>
<table cellspacing=\"0\" cellpadding=\"0\"><tr><td><font class=\"classic\">Pour passer en mode administration, vous devez cliquer sur </font></td><td><img src=\"../images/jorkyball_adm.gif\" border=\"0\" alt=\"\" /></td><td><font class=\"classic\"> et saisir votre login et mot de passe.</font></td></table>
";

display("Connexion à un championnat", $tab);

}




if ($decouvrir_item == 5)
{
$tab = "
<p style=\"text-align: justify;\"><font class=\"classic\">
L'ajout des joueurs est indispensable pour la gestion de votre championnat. Cliquer sur le menu 'joueurs' pour faire apparaître
la liste des joueurs qui normalement est vide. Pour ajouter un nouveau joueur, vous devez passer en mode administration et
cliquez sur le bouton 'Ajouter'.
<br /><br />
Saisissez les données du formulaire pour chaque joueur de votre championnat. Le champ 'pseudo' est très utilisé, il sert de
diminutif dans l'ensemble des écrans. Si vous ne savez pas quoi mettre, reporter le prénom en évitant les doublons. Un joueur
est considéré comme 'régulier' lorsqu'il participe souvent aux journées du championnat, sinon il est préférable de le
déclarer 'non régulier' c'est à dire occasionel. Cette information est utile pour les statistiques.
<br /><br />
A chaque fois qu'un joueur est ajouté dans le championnat, le site créé automatiquement toutes les équipes possible
qui lient ce nouveau aux joueurs déjà existant. Grâce au menu 'Equipes' vous pouvez personnaliser ces équipes.
</font>
</p>";

display("Gestion des joueurs", $tab);

}




if ($decouvrir_item == 6)
{
$tab = "
<p STYLE=\"text-align: justify;\"><font class=\"classic\">
En cliquant sur 'Journées', un calendrier apparait. En mode 'administration', vous cliquer sur les numéros des dates pour
ajouter une nouvelle journée à votre championnat.
<br /><br />
Une journée correspond à session de jeu entre plusieurs joueurs qui se sont affrontés lors d'une journée de Foot 2x2.
Cette journée est donc composée de matchs disputés entre ces joueurs, les équipes pouvant être différentes pour chaque
match. Il n'y aucune contrainte particulière.
<br /><br />
Le formulaire de saisie d'une journée vous propose la liste des joueurs potentiels qui ont participé (ou participeront)
à cette journée. Il n'est pas obligatoire que tous les joueurs participent à toutes journées. Il ne faut pas mettre les
joueurs qui n'ont pas participé, cela influence leurs statistiques.
<br /><br />
Le site vous propose la création automatique des matchs pour la journée que vous êtes en train de créer. Cette option est
facultative, mais permet de générer des rencontres aléatoires. L'expérience montre qu'il y a souvent des imprévus et que
de ce fait, les matchs joués sont souvent différents de ceux qui ont été planifiés. Peu importe, les matchs qui ne sont pas
joués peuvent supprimés. Si vous pratiquez une gestion des matchs sur le 'tas', alors n'utiliser pas cette option.
<br /><br />
Une fois que votre journée est planifiée, elle est repérable dans le calendrier grâce à un ballon. En cliquant sur cette
icône, vous accédez à la liste des matchs de cette journée.
<br /><br />
Si vous êtes un grand nombre de joueurs et que vous étalez les rencontres d'une journée sur plusieurs jours, il
faut créér une journée pivot qui rassemblera toutes ces rencontres.
</font>
</p>";

display("Gestion des journées", $tab);

}



if ($decouvrir_item == 7)
{
$tab = "
<font class=\"classic\">
En cliquant sur 'Ajouter', vous accédez à un formulaire qui vous propose de saisir un match, c'est à dire 2 équipes et
le score. Automatiquement le classement de cette journée se met à jour, mettant en évidence le meilleur joueur.
<br /><br />
Un match peut se jouer en un seul set gagnant ou en 3 sets gagnants. Cette gestion est libre, les statistiques sont basées sur
les victoires/défaites. On peut très bien mixer les 2.
<br /><br />
</font>
<table cellspacing=\"0\" cellpadding=\"0\"><tr><td><font class=\"classic\">Un sous menu </font></td><td><img src=\"../images/submenu.gif\" border=\"0\" alt=\"\" /></td><td><font class=\"classic\"> vous donne accès à certaines fonctionnalités particulières :</font></td></table>
<ul compact type=circle>
<li>Ajout automatique : Cette option vous permet de générer des matchs automatiquement.
<li>Saisie de tous les résultats : Cette option vous facilite la saisie de l'ensemble des résultats d'une journée.
<li>Synchronisation des joueurs avec la journée : Cette option ne s'utilise que lorsqu'il y a des problèmes de BD.
<li>Suppression d'un joueur de la journée.
<li>Suppression de tous les matchs : Cette option supprime tous les matchs de la journée.
<li>Suppression de la journée : Cette option supprime tous les matchs et la journée.
</ul>
";

display("Gestion des matchs", $tab);

}



if ($decouvrir_item == 8)
{
$tab = "
<font class=\"classic\">
Le suivi des statistiques permet d'établir des statistiques globales au championnat, permettant ainsi de connaître
les meilleurs joueurs, l'état de forme de chaque joueur et un tas d'autres indices. Des statistiques individuelles
sont aussi visualisables.
<br /><br />
Quelques explications :
</font>
<ol type=\"I\" start=\"1\">
<li>On sépare les statistiques des joueurs réguliers et occasionnels, sinon il y aurait des risques de statistiques faussées.
Il est souvent plus difficile de faire de bonnes stats régulièrement que de faire un coup d'éclat ponctuel.
<li>% de matchs joués = Nb de matchs joués / Nb de matchs joués au total (réguliers+occasionnels)
<li>% de matchs gagnés = Nb de matchs gagnés / Nb de matchs joués
<li>Forme - Etat = Analyse des écarts de la courbe des victoires sur les 4 dernières journées
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche0.gif\" border=\"0\" alt=\"\" /> </td><td>&nbsp; : N'a pas participé à au moins 3 des 4 dernières journées.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche1.gif\" border=\"0\" alt=\"\" /> </td><td>&nbsp; : Courbe des victoires en chute libre.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche2.gif\" border=\"0\" alt=\"\" /> </td><td>&nbsp; : Courbe des victoires en baisse.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche3.gif\" border=\"0\" alt=\"\" /> </td><td>&nbsp; : Courbe des victoires stable.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche4.gif\" border=\"0\" alt=\"\" /> </td><td>&nbsp; : Courbe des victoires en hausse.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche5.gif\" border=\"0\" alt=\"\" /> </td><td>&nbsp; : Courbe des victoires en forte hausse.</td></table></td>
</table>
<li>Forme - Last = % de matchs gagnés sur la dernière journée jouée
<table border=0 cellpadding=0 cellspacing=0>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche1.gif\" alt=\"\" /> </td><td>&nbsp; : % de matchs gagnés très inférieur à sa moyenne globale.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche2.gif\" alt=\"\" /> </td><td>&nbsp; : % de matchs gagnés inférieur à sa moyenne globale.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche3.gif\" alt=\"\" /> </td><td>&nbsp; : % de matchs gagnés conforme à sa moyenne globale.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche4.gif\" alt=\"\" /> </td><td>&nbsp; : % de matchs gagnés supérieur à sa moyenne globale.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche5.gif\" alt=\"\" /> </td><td>&nbsp; : % de matchs gagnés très supérieur à sa moyenne globale.</td></table></td>
</table>
<li>Podium - 1er = Nb de fois où un joueur est premier au classement d'une journée
<li>Podium - 2me = Nb de fois où un joueur est second au classement d'une journée
<li><img src=\"../images/etoiles/etoile_or.gif\" alt=\"\" /> pour le meilleur médaillé du championnat
<li><img src=\"../images/etoiles/etoile_argent.gif\" alt=\"\" /> pour le meilleur performeur du championnat (meilleur % de matchs gagnés)
</ol>
";

display("Statistiques", $tab);

}



if ($decouvrir_item == 9)
{
$tab = "
<p style=\"text-align: justify;\"><font class=classic>
Un forum par championnat est à votre disposition.
<br /><br />
Bon Jorky à tous ...
</font>
</p>";

display("Divers", $tab);

}

if ($decouvrir_item == 10)
{
$tab = "
<p style=\"text-align: justify;\"><font class=classic>
A Faire
</font>
</p>";

display("Gestion des saisons", $tab);

}


?>


</table></td>
</table>

</center>

<? TemplateBox :: htmlEnd(); ?>
