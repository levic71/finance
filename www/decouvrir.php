<?

include "../include/sess_context.php";

session_start();

$jorkyball_redirect_exception = 1;

include "common.php";
include "ManagerFXList.php";
TemplateBox :: htmlBegin(false, false, "09");

$lib_accueil = (isset($faq) && $faq == 0) ? "<A HREF=home.php CLASS=white> >> Retour � l'accueil << </A>" : "&nbsp;";

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
		<tr onmouseover="this.style.background='#EEEEEE';" onmouseout="this.style.background='';"><td align="left" class="item"><p class="p"><a href="decouvrir.php?decouvrir_item=4"  class="blue"> Acc�s � un championnat </a></p></td>
		<tr onmouseover="this.style.background='#EEEEEE';" onmouseout="this.style.background='';"><td align="left" class="item"><p class="p"><a href="decouvrir.php?decouvrir_item=5"  class="blue"> Gestion des joueurs </a></p></td>
		<tr onmouseover="this.style.background='#EEEEEE';" onmouseout="this.style.background='';"><td align="left" class="item"><p class="p"><a href="decouvrir.php?decouvrir_item=6"  class="blue"> Gestion des journ�es </a></p></td>
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
le <a class=\"blue\" href=\"#\">Foot 2x2</a>, Futsal, Football, ... et qui souhaitent g�rer un championat ou tournoi de mani�re � pouvoir
suivre les matchs jou�s par journ�es et pouvoir ainsi �tablir des statisques amusantes et s�rieuses.
<br /><br />
Pour cela, il est n�cessaire de jouer r�guli�rement au Foot 2x2 et d'�tre au moins 4 joueurs ou 2 �quipes.
<br /><br />
Vous pouvez g�rer des championnats de type libre, de type championnat de France et des tournois. Cette diff�renciation de ces type de championnat
permet de prendre en compte les sp�cificit�s de chacun.
<br /><br />
Ce site est totalement gratuit et ne vous engage � rien.
<br /><br />
Pour en savoir plus sur ce site, je vous invite soit � consulter les championnats d�mos (accessibles depuis la page d'accueil) et
� vous inscrire directement pour mieux vous rendre compte des possibit�s offertes.
<br /><br />
<u>Attention</u>: <b>Les cookies doivent �tre activ�s et autoris�s sur votre navigateur web.</b>
</font></p>";

display("Le Jorkers.com : C'est Quoi ?", $tab);

}


if ($decouvrir_item == 2)
{
$tab = "
<font class=\"classic\">
Pour d�buter un championnat ou un tournoi, c'est simple, il suffit de retourner sur la page d'accueil et cliquer sur 's'inscrire' et
de remplir le formulaire d'inscription.
</font>
<ul compact type=circle>
<li>Nom du championnat (Tr�s important)
<li>Description du championnat
<li>Type du championnat<BR />
	<ul>
		<li><b>Gestion Libre</b> : Pas de contraintes particuli�res sur les �quipes (rencontres au fil de l'eau)</li>
		<li><b>Gestion championnat</b> : Affrontement de toutes les �quipes du championnats sur une journ�e</li>
		<li><b>Tournoi</b> : Affrontement de plusieurs �quipes sur une journ�e avec poules de qualification + phase finale</li>
	</ul>
<li>Lieu de pratique
<li>Nom du gestionnaire du championnat
<li>Email du gestionnaire du championnat
<li>Edito de news du championnat
<li>Mode de visualisation des journ�es du championnat
	<ul>
		<li><b>Calendrier</b> : Les journ�es jou�s du championnat apparaissent dans un calendrier (6 mois affich�s)</li>
		<li><b>Liste</b> : Les journ�es jou�s du championnat apparaissent dans une liste class�e par ordre chronologique</li>
	</ul>
<li>Affection des points pour une victoire et une d�faite
<li>Informations en page d'accueil: Les cases � cocher permettent de personnaliser l'affichage de la page d'accueil du championnat
<li>Acc�s classement joueurs: Permet de proposer ou pas l'affichage du claseement des joueurs (s'il n'est pas pertinent)
<li>Nom premi�re saison : Comme dans tous championnats il existe une notion de saison, il faut donc donner le nom de la premi�re
<li> Championnats amis : S�lection des championnts dits 'amis'. Un acc�s est positionner sur votre championnat pour en faciliter l'acc�s.
<li>Login d'administration du championnat
<li>Mot de passe d'administration du championnat
</ul>
<font class=\"classic\">
Le login et le mot de passe d'administration servent � r�aliser des op�rations particuli�res (gestion joueurs, journ�es, ...) uniquement
accessibles au gestionnaire du championnat. Les autres joueurs ne pourront que consulter les informations mise � leurs
dispositions.
<br />
<br />
Apr�s la cr�ation, il faut cr��r les joueurs et les �quipes du championnat.
</font>";

display("Cr�ation d'un championnat", $tab);

}





if ($decouvrir_item == 4)
{
$tab = "
<font class=\"classic\">
Apr�s avoir cr�er votre championnat, il faut vous connecter � votre championnat. Lors de votre inscription, vous �tes
automatiquement rediger sur votre championnat en mode 'administration'. Vous pouvez alors mettre � jour votre championnat.<BR />
Par la suite, l'acc�s � votre championnat se fait toujours par la page d'accueil en saisissant le nom de votre championnat + [ENTER].
Une fois connect�, vous avez acc�s � toutes les informations de base de votre championnat : liste des joueurs, des �quipes, des journ�es jou�es, des statistiques.<br /></font>
<table cellspacing=\"0\" cellpadding=\"0\"><tr><td><font class=\"classic\">Pour passer en mode administration, vous devez cliquer sur </font></td><td><img src=\"../images/jorkyball_adm.gif\" border=\"0\" alt=\"\" /></td><td><font class=\"classic\"> et saisir votre login et mot de passe.</font></td></table>
";

display("Connexion � un championnat", $tab);

}




if ($decouvrir_item == 5)
{
$tab = "
<p style=\"text-align: justify;\"><font class=\"classic\">
L'ajout des joueurs est indispensable pour la gestion de votre championnat. Cliquer sur le menu 'joueurs' pour faire appara�tre
la liste des joueurs qui normalement est vide. Pour ajouter un nouveau joueur, vous devez passer en mode administration et
cliquez sur le bouton 'Ajouter'.
<br /><br />
Saisissez les donn�es du formulaire pour chaque joueur de votre championnat. Le champ 'pseudo' est tr�s utilis�, il sert de
diminutif dans l'ensemble des �crans. Si vous ne savez pas quoi mettre, reporter le pr�nom en �vitant les doublons. Un joueur
est consid�r� comme 'r�gulier' lorsqu'il participe souvent aux journ�es du championnat, sinon il est pr�f�rable de le
d�clarer 'non r�gulier' c'est � dire occasionel. Cette information est utile pour les statistiques.
<br /><br />
A chaque fois qu'un joueur est ajout� dans le championnat, le site cr�� automatiquement toutes les �quipes possible
qui lient ce nouveau aux joueurs d�j� existant. Gr�ce au menu 'Equipes' vous pouvez personnaliser ces �quipes.
</font>
</p>";

display("Gestion des joueurs", $tab);

}




if ($decouvrir_item == 6)
{
$tab = "
<p STYLE=\"text-align: justify;\"><font class=\"classic\">
En cliquant sur 'Journ�es', un calendrier apparait. En mode 'administration', vous cliquer sur les num�ros des dates pour
ajouter une nouvelle journ�e � votre championnat.
<br /><br />
Une journ�e correspond � session de jeu entre plusieurs joueurs qui se sont affront�s lors d'une journ�e de Foot 2x2.
Cette journ�e est donc compos�e de matchs disput�s entre ces joueurs, les �quipes pouvant �tre diff�rentes pour chaque
match. Il n'y aucune contrainte particuli�re.
<br /><br />
Le formulaire de saisie d'une journ�e vous propose la liste des joueurs potentiels qui ont particip� (ou participeront)
� cette journ�e. Il n'est pas obligatoire que tous les joueurs participent � toutes journ�es. Il ne faut pas mettre les
joueurs qui n'ont pas particip�, cela influence leurs statistiques.
<br /><br />
Le site vous propose la cr�ation automatique des matchs pour la journ�e que vous �tes en train de cr�er. Cette option est
facultative, mais permet de g�n�rer des rencontres al�atoires. L'exp�rience montre qu'il y a souvent des impr�vus et que
de ce fait, les matchs jou�s sont souvent diff�rents de ceux qui ont �t� planifi�s. Peu importe, les matchs qui ne sont pas
jou�s peuvent supprim�s. Si vous pratiquez une gestion des matchs sur le 'tas', alors n'utiliser pas cette option.
<br /><br />
Une fois que votre journ�e est planifi�e, elle est rep�rable dans le calendrier gr�ce � un ballon. En cliquant sur cette
ic�ne, vous acc�dez � la liste des matchs de cette journ�e.
<br /><br />
Si vous �tes un grand nombre de joueurs et que vous �talez les rencontres d'une journ�e sur plusieurs jours, il
faut cr��r une journ�e pivot qui rassemblera toutes ces rencontres.
</font>
</p>";

display("Gestion des journ�es", $tab);

}



if ($decouvrir_item == 7)
{
$tab = "
<font class=\"classic\">
En cliquant sur 'Ajouter', vous acc�dez � un formulaire qui vous propose de saisir un match, c'est � dire 2 �quipes et
le score. Automatiquement le classement de cette journ�e se met � jour, mettant en �vidence le meilleur joueur.
<br /><br />
Un match peut se jouer en un seul set gagnant ou en 3 sets gagnants. Cette gestion est libre, les statistiques sont bas�es sur
les victoires/d�faites. On peut tr�s bien mixer les 2.
<br /><br />
</font>
<table cellspacing=\"0\" cellpadding=\"0\"><tr><td><font class=\"classic\">Un sous menu </font></td><td><img src=\"../images/submenu.gif\" border=\"0\" alt=\"\" /></td><td><font class=\"classic\"> vous donne acc�s � certaines fonctionnalit�s particuli�res :</font></td></table>
<ul compact type=circle>
<li>Ajout automatique : Cette option vous permet de g�n�rer des matchs automatiquement.
<li>Saisie de tous les r�sultats : Cette option vous facilite la saisie de l'ensemble des r�sultats d'une journ�e.
<li>Synchronisation des joueurs avec la journ�e : Cette option ne s'utilise que lorsqu'il y a des probl�mes de BD.
<li>Suppression d'un joueur de la journ�e.
<li>Suppression de tous les matchs : Cette option supprime tous les matchs de la journ�e.
<li>Suppression de la journ�e : Cette option supprime tous les matchs et la journ�e.
</ul>
";

display("Gestion des matchs", $tab);

}



if ($decouvrir_item == 8)
{
$tab = "
<font class=\"classic\">
Le suivi des statistiques permet d'�tablir des statistiques globales au championnat, permettant ainsi de conna�tre
les meilleurs joueurs, l'�tat de forme de chaque joueur et un tas d'autres indices. Des statistiques individuelles
sont aussi visualisables.
<br /><br />
Quelques explications :
</font>
<ol type=\"I\" start=\"1\">
<li>On s�pare les statistiques des joueurs r�guliers et occasionnels, sinon il y aurait des risques de statistiques fauss�es.
Il est souvent plus difficile de faire de bonnes stats r�guli�rement que de faire un coup d'�clat ponctuel.
<li>% de matchs jou�s = Nb de matchs jou�s / Nb de matchs jou�s au total (r�guliers+occasionnels)
<li>% de matchs gagn�s = Nb de matchs gagn�s / Nb de matchs jou�s
<li>Forme - Etat = Analyse des �carts de la courbe des victoires sur les 4 derni�res journ�es
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche0.gif\" border=\"0\" alt=\"\" /> </td><td>&nbsp; : N'a pas particip� � au moins 3 des 4 derni�res journ�es.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche1.gif\" border=\"0\" alt=\"\" /> </td><td>&nbsp; : Courbe des victoires en chute libre.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche2.gif\" border=\"0\" alt=\"\" /> </td><td>&nbsp; : Courbe des victoires en baisse.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche3.gif\" border=\"0\" alt=\"\" /> </td><td>&nbsp; : Courbe des victoires stable.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche4.gif\" border=\"0\" alt=\"\" /> </td><td>&nbsp; : Courbe des victoires en hausse.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche5.gif\" border=\"0\" alt=\"\" /> </td><td>&nbsp; : Courbe des victoires en forte hausse.</td></table></td>
</table>
<li>Forme - Last = % de matchs gagn�s sur la derni�re journ�e jou�e
<table border=0 cellpadding=0 cellspacing=0>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche1.gif\" alt=\"\" /> </td><td>&nbsp; : % de matchs gagn�s tr�s inf�rieur � sa moyenne globale.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche2.gif\" alt=\"\" /> </td><td>&nbsp; : % de matchs gagn�s inf�rieur � sa moyenne globale.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche3.gif\" alt=\"\" /> </td><td>&nbsp; : % de matchs gagn�s conforme � sa moyenne globale.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche4.gif\" alt=\"\" /> </td><td>&nbsp; : % de matchs gagn�s sup�rieur � sa moyenne globale.</td></table></td>
<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"50\"> </td><td><img src=\"../images/fleches/fleche5.gif\" alt=\"\" /> </td><td>&nbsp; : % de matchs gagn�s tr�s sup�rieur � sa moyenne globale.</td></table></td>
</table>
<li>Podium - 1er = Nb de fois o� un joueur est premier au classement d'une journ�e
<li>Podium - 2me = Nb de fois o� un joueur est second au classement d'une journ�e
<li><img src=\"../images/etoiles/etoile_or.gif\" alt=\"\" /> pour le meilleur m�daill� du championnat
<li><img src=\"../images/etoiles/etoile_argent.gif\" alt=\"\" /> pour le meilleur performeur du championnat (meilleur % de matchs gagn�s)
</ol>
";

display("Statistiques", $tab);

}



if ($decouvrir_item == 9)
{
$tab = "
<p style=\"text-align: justify;\"><font class=classic>
Un forum par championnat est � votre disposition.
<br /><br />
Bon Jorky � tous ...
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
