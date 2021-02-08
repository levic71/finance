<?

ini_set('default_charset', 'iso8859-1');

require_once "../include/sess_context.php";

session_start();

include "../include/toolbox.php";
include "../include/inc_db.php";
include "../wrapper/wrapper_fcts.php";

$db = dbc::connect();

$dns = explode('.', $_SERVER['SERVER_NAME']);

// Accès direct au championnat via sous domaine
if (isset($dns[0]) && strtolower($dns[0]) != "www" && strtolower($dns[0]) != "www") {

	$r7_dns = explode('-', $dns[0]);
	$r7 = isset($r7_dns[0]) && strtolower($r7_dns[0]) == "r7" ? true : false;
  $protocole = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? 'https://' : 'http://';

	$sql = "SELECT id, nom FROM jb_championnat WHERE entity='_NATIF_' AND actif = 1 AND nom != '' AND lower(nom)='".strtolower($r7 ? $r7_dns['1'] : $dns['0'])."' ORDER BY dt_creation DESC";
	$res = dbc::execSQL($sql);
	if ($row = mysqli_fetch_array($res)) {
  	ToolBox::do_redirect($protocole.($r7 ? "r7" : "www").".jorkers.com/wrapper/jk.php?idc=".$row['id']);
  }
}

unset($_SESSION['antispam']);
$_SESSION['antispam'] = ToolBox::getRand(5);

$sess_context = isset($_SESSION['sess_context']) ? $_SESSION['sess_context'] : null;

?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <title>Jorkers.com</title>
    <meta name="keywords"       content="jorkers,gratuit,gestion,championnat,tournoi,jorker,gestionnaire,multi sport,foot 2x2,jorky,championship,classement,statistique,joueur,équipe,journée,football,sport,compétition,futsal,tournaments,management" />
    <meta name="description"    content="Gestionnaire de Championnats et de Tournois multi sports gratuit pour PC, Smartphaone et Tablette" />
    <meta name="robots"         content="index, follow" />
    <meta name="rating"         content="General" />
    <meta name="distribution"   content="Global" />
    <meta name="author"         content="contact@jorkers.com" />
    <meta name="reply-to"       content="contact@jorkers.com" />
    <meta name="owner"          content="contact@jorkers.com" />
    <meta name="copyright"      content="&copy;Copyright : jorkers.com" />
    <meta name="identifier-url" content="http://www.jorkers.com/" />
    <meta name="category"       content="Sport, Football, Soccer, Foot 2x2, Futsal, Sport de balles et ballon, loisirs" />
    <meta name="publisher"      content="Jorkers.com" />
    <meta name="location"       content="Région parisienne" />
    <meta name="revisit-after"  content="7 days" />
    <meta http-equiv="Content-Language" content="fr-FX" />
    <meta http-equiv="Content-Type"     content="text/html; charset=ISO-8859-1" />
    <meta http-equiv="pragma"           content="no-cache" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Le styles -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-responsive.min.css" rel="stylesheet">
    <link href="css/home.css" rel="stylesheet">
   
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="../wrapper/img/webclip114.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="../wrapper/img/webclip114.png">
      <link rel="apple-touch-icon-precomposed" sizes="72x72" href="../wrapper/img/webclip72.png">
                    <link rel="apple-touch-icon-precomposed" href="../wrapper/img/webclip.png">
                                   <link rel="shortcut icon" href="../wrapper/img/webclip.png">
  </head>

  <body>

    <!-- NAVBAR
    ================================================== -->
    <div class="navbar-wrapper">
      <!-- Wrap the .navbar in .container to center it within the absolutely positioned parent. -->
      <div class="container">

        <div class="navbar navbar-inverse">
          <div class="navbar-inner">
            <!-- Responsive Navbar Part 1: Button for triggering responsive navbar (not covered in tutorial). Include responsive CSS to utilize. -->
            <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="brand" href="#">Jorkers.com</a>
            <!-- Responsive Navbar Part 2: Place all navbar contents you want collapsed withing .navbar-collapse.collapse. -->
            <div class="nav-collapse collapse">
              <ul class="nav">
                <li><a href="#marketing">Fonctionnalités</a></li>
                <li><a href="#" class="btndemo">Démos</a></li>
                <li><a href="#contact">Contact</a></li>
              </ul>
              <a href="../wrapper/jk.php?<?= isset($sess_context) && $sess_context->isUserConnected() ? "myprofile" : "auth" ?>" class="btn btn-primary pull-right" style="margin: 10px 0px 0px;"><?= isset($sess_context) && $sess_context->isUserConnected() ? "Bienvenue ".$sess_context->user['pseudo'] : "S'incrire | Se connecter" ?></a>
            </div><!--/.nav-collapse -->
          </div><!-- /.navbar-inner -->
        </div><!-- /.navbar -->

      </div> <!-- /.container -->
    </div><!-- /.navbar-wrapper -->

    <!-- Carousel
    ================================================== -->
    <div id="myCarousel" class="carousel slide stdPanel">
      <div class="carousel-inner">
        <div class="item active">
          <img src="img/slide-01.jpg" alt="">
          <div class="container">
            <div class="carousel-caption">
              <h1>JORKERS</h1>
              <p class="lead">
              Solution gratuite en ligne de gestion de championnats et tournois sportifs individuels et collectifs.
              <br />
              Espace idéal pour partager entre amis sa passion avec fair play !
              <br />
              Réalisez des stats de PRO.
              </p>
              <a class="btn btn-large btn-primary" href="../wrapper/jk.php?idc=<?= sess_context::INVALID_CHAMP_ID_LOGIN ?>">Créer ton championnat</a>
            </div>
          </div>
        </div>
        <div class="item">
          <img src="img/slide-02.jpg" alt="">
          <div class="container">
            <div class="carousel-caption">
              <h1>Application complète et multicanal</h1>
              <p class="lead">
                Le Jorkers.com est application complète, simple et rapide.
                <br />
                Fonctionne sur tous les appareils (PC, Smartphone, Tablette) pour vous offrir des services toujours plus innovants.
              </p>
              <a class="btn btn-large btn-primary" href="../wrapper/jk.php?idc=<?= sess_context::INVALID_CHAMP_ID_LOGIN ?>">Créer ton championnat</a>
            </div>
          </div>
        </div>
      </div>
      <a class="left carousel-control" href="#myCarousel" data-slide="prev">&lsaquo;</a>
      <a class="right carousel-control" href="#myCarousel" data-slide="next">&rsaquo;</a>
    </div><!-- /.carousel -->

    <!-- Marketing messaging and featurettes
    ================================================== -->
    <!-- Wrap the rest of the page in another container to center all the content. -->

    <div class="container stdPanel" id="marketing">

      <!-- Three columns of text below the carousel -->
      <div class="row">
        <div class="span4">
          <img class="img-circle" src="img/search.png">
          <h2>Retrouver vos Amis</h2>
          <p class="text">Accéder à  l'ensemble des championnats et tournois existants et partager vos émotions</p>
          <p><a class="btn" href="../wrapper/jk.php" id="btnFind">Chercher un championnat&raquo;</a></p>
        </div><!-- /.span4 -->
        <div class="span4">
          <img class="img-circle" src="img/qs.png">
          <h2>Quantified Self</h2>
          <p class="text">Exclusivité du Jorkers.com, cumuler vos statistiques personnelles afin de mieux vous connaitre</p>
          <p><a class="btn" href="#">En savoir plus &raquo;</a></p>
        </div><!-- /.span4 -->
        <div class="span4">
          <img  src="img/demo.png">
          <h2>Essayer = Adopter</h2>
          <p class="text">Envie de savoir comment cela fonctionne avant de vous lancer, n'hésiter pas !!!</p>
          <p><a class="btn btndemo" href="#">Espaces démos &raquo;</a></p>
        </div><!-- /.span4 -->
      </div><!-- /.row -->

      <hr />

      <div class="row">
        <div class="span4">
          <img class="img-circle" src="img/print.png">
          <h2>Impression Résultats</h2>
          <p class="text">Imprimer tous vos classements sur différents formats A3, A4, A5 grâce aux exports pdf</p>
          <p><a class="btn" href="#" id="btnimp" onclick="alert('En cours de création ...');">En savoir plus &raquo;</a></p>
        </div><!-- /.span4 -->
        <div class="span4">
          <img class="img-circle" src="img/stats.png">
          <h2>Statistiques/Classements</h2>
          <p class="text">Des statistiques personnelles et collectives pour mieux évaluer vos performances</p>
          <p><a class="btn" href="#" id="btnstat" onclick="alert('En cours de création ...');">En savoir plus &raquo;</a></p>
        </div><!-- /.span4 -->
        <div class="span4">
          <img  src="img/rs.png">
          <h2>Réseaux Sociaux</h2>
          <p class="text">Partager avec vos amis vos résultats et statistiques via les réseaux sociaux</p>
          <p><a class="btn" href="#" id="btnrs" onclick="alert('En cours de création ...');">En savoir plus &raquo;</a></p>
        </div><!-- /.span4 -->
      </div><!-- /.row -->



      <!-- START THE FEATURETTES -->
      <hr class="featurette-divider" style="display: none;">

      <div class="row">
        <div class="span4" id="twitter">
        </div>
        <div class="span8" id="footrss">
        </div>
      </div>

      <!-- /END THE FEATURETTES -->

      <hr class="featurette-divider"  style="display: none;">
      <br />
      
      <form class="form-horizontal" id="contact">
      <fieldset>
        <legend>Formulaire de contact</legend>
        <div class="control-group">
          <label class="control-label" for="nom">Nom</label>
          <div class="controls">
			  <div class="input-prepend">
			  	<span class="add-on">&#9786;</span><input type="text" class="input-xlarge" id="nom">
			  </div>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="email">Email</label>
          <div class="controls">
            <div class="input-prepend">
              <span class="add-on">@</span><input class="span2 input-xlarge focused" id="email" type="text">
            </div>
            <span class="help-inline">*</span>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="sujet">Sujet</label>
          <div class="controls">
			  <div class="input-prepend">
				  <span class="add-on">&#9187;</span><input type="text" class="input-xlarge" id="sujet">
			  </div>
            <span class="help-inline">*</span>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="message">Message</label>
          <div class="controls">
            <textarea id="message" rows="6" cols="64"></textarea>
            <span class="help-inline">*</span>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="controle">Controle</label>
          <div class="controls">
            <input type="text" class="input-xlarge" id="controle">
            <img style="float: left;" src="../include/codeimage.php?<?= ToolBox::getRand(5) ?>" />
            <span class="help-inline">*</span>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary" id="sendmail">Envoyer</button>
          <p class="help-block" style="float: right;">(*) Champs obligatoires</p>
        </div>
      </fieldset>
      </form>

    </div><!-- /.container -->



    <div class="container oPanel" id="findChampionnat"></div>


    <div class="modal hide fade in" id="demoModal">
      <div class="modal-header">
        <button class="close" data-dismiss="modal">à—</button>
        <h3>Essayer = Adopter</h3>
      </div>
      <div class="modal-body">
        <table>
          <tr><td><img alt="" src="img/demo.png"></td><td>&nbsp;</td>
              <td><div class="caption">
                <h5>Tournoi</h5>
                <p>Vous organisez un tournoi dans lequel les équipes vont se rencontrer d'abord en poule, les meilleurs disputeront la phase finale en élimination directe pour déterminer le vainqueur.</p>
              </div>
          </td><td><p><a href="http://demo-tournoi.jorkers.com" class="btn btn-primary">Démo</a></p></td></tr>
          <tr><td><img alt="" src="img/demo.png"></td><td>&nbsp;</td>
              <td><div class="caption">
                <h5>Championnat classique</h5>
                <p>Vous êtes organisés comme un véritable championnat avec un nombre d'équipes fini qui se rencontrent les unes contre les autres tout au long d'une saison sur plusieurs journées.</p>
              </div>
          </td><td><p><a href="http://demo-championnat.jorkers.com" class="btn btn-primary">Démo</a></p></td></tr>
          <tr><td><img alt="" src="img/demo.png"></td><td>&nbsp;</td>
              <td><div class="caption">
                <h5>Championnat Libre</h5>
                <p>Vous jouez entre amis sans vraiment de contraintes, toutes les équipes sont possibles et varient au fil des journées, chaque joueur est de mesurer ces statistiques de progression.</p>
              </div>
          </td><td><p><a href="http://demo-championnat-libre.jorkers.com" class="btn btn-primary">Démo</a></p></td></tr>
        </table>
      </div>
      <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Close</a>
      </div>
    </div>


    <hr />

<!-- FOOTER -->
    <footer>
      <p class="pull-right"><a href="#">Top</a></p>
      <p>&copy; 2021 &middot; <a href="#">Privacy</a> &middot; <a href="#">Terms</a></p>
    </footer>

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/holder.js"></script>
    <script src="js/home.js?20210119"></script>
  </body>
</html>