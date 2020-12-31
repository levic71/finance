<?

include "../include/sess_context.php";

ini_set("url_rewriter.tags","input=src");
ini_set('arg_separator.output', '&amp;');

session_start();

$jorkyball_redirect_exception = 1;

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

?>

<script language="javascript">
function request(affiche) 
{
	var XHR = null;
	
	//DECLARATION DES OBJETS XMLHTTPRequest
	if(window.XMLHttpRequest) // Firefox
	{
		XHR = new XMLHttpRequest();
	}
	else if(window.ActiveXObject) // Internet Explorer
	{
		XHR = new ActiveXObject("Microsoft.XMLHTTP");
	}
	else 
	{ // XMLHttpRequest non supporté par le navigateur
		alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest");
		return;
	}
	
	// envoie de la requête, methode GET et de l'url
	XHR.open("POST", "../www/track_click.php?champ=-"+affiche+"&admin=6", true);

	// on guette les changements d'état de l'objet
	XHR.onreadystatechange = function attente() 
	{
		// l'état est à 4, requête reçu !
		if(XHR.readyState == 4)     
		{
			// ecriture de la réponse : on modifie le contenue de cadre(panier)
			// document.getElementById(cadre).innerHTML = XHR.responseText;
			// alert(XHR.responseText);
		}
	}
	XHR.send(null);        // le travail est terminé
	return;
}
</script>

<style>
.affiche {
	height: 150px;
	width: 150px;
	float: left;
	margin: 10px 10px 30px 10px;
	text-align: center;
}
.affiche .photo img {
	border: 2px solid #CCCCCC;
	padding: 2px;
}
</style>



<div id="pageint" style="margin-bottom: 0px;">



<h2>Affiches</h2>

<p><b>Vous pouvez télécharger et imprimer ces posters pour les afficher dans vos clubs et invitez vos joueurs à consulter le Jorkers.com</b></p>

<table border="0"><tr><td>
<div style="display: inline;">

<div class="affiche">
	<div class="titre">Affiche n°1</div>
	<div class="photo"><img src="../images/affiches/xaffiche1.jpg" onmouseover="show_info_upleft('<img src=../images/affiches/affiche1.jpg height=421 width=300>', event);" onmouseout="close_info();" /></div>
	<div>&raquo; <a href="#" onclick="request(1); window.open('../images/affiches/affiche1.jpg');">Télécharger</a> &laquo;</div>
</div>

<div class="affiche">
	<div class="titre">Affiche n°2</div>
	<div class="photo"><img src="../images/affiches/xaffiche2.jpg" onmouseover="show_info_upleft('<img src=../images/affiches/affiche2.jpg height=421 width=300>', event);" onmouseout="close_info();" /></div>
	<div>&raquo; <a href="#" onclick="request(2); window.open('../images/affiches/affiche2.jpg');">Télécharger</a> &laquo;</div>
</div>

<div class="affiche">
	<div class="titre">Affiche n°3</div>
	<div class="photo"><img src="../images/affiches/xaffiche3.jpg" onmouseover="show_info_upleft('<img src=../images/affiches/affiche3.jpg height=421 width=300>', event);" onmouseout="close_info();" /></div>
	<div>&raquo; <a href="#" onclick="request(3); window.open('../images/affiches/affiche3.jpg');">Télécharger</a> &laquo;</div>
</div>

<div class="affiche">
	<div class="titre">Affiche n°4</div>
	<div class="photo"><img src="../images/affiches/xaffiche4.jpg" onmouseover="show_info_upleft('<img src=../images/affiches/affiche4.jpg height=300 width=421>', event);" onmouseout="close_info();" /></div>
	<div>&raquo; <a href="#" onclick="request(4); window.open('../images/affiches/affiche4.jpg');">Télécharger</a> &laquo;</div>
</div>

</div>


</td></td></table>

<p>Si vous avez des idées d'affiches ou de slogan n'hésitez pas à me les <a href="../www/contacter.php">envoyer</a>.</p>

</div>


<? $menu->end(); ?>

