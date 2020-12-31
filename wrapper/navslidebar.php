<?

require_once "../include/sess_context.php";

session_start();

include "common.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

?>

        <header class="demo-drawer-header mdl-color--blue-grey-800">

<? if ($sess_context->isUserConnected()) { ?>
          <img src="<?= Wrapper::formatPhotoJoueur(file_exists($sess_context->user['photo']) ? $sess_context->user['photo'] : "img/user-icon.png") ?>" class="demo-avatar">
          <div class="demo-avatar-dropdown">
            <span><?= $sess_context->user['prenom']." ".$sess_context->user['nom'] == " " ? $sess_context->user['pseudo'] : $sess_context->user['prenom']." ".$sess_context->user['nom'] ?></span>
            <div class="mdl-layout-spacer"></div>
            <button id="accbtn" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
              <i class="material-icons" role="presentation">arrow_drop_down</i>
              <span class="visuallyhidden">Accounts</span>
            </button>
            <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="accbtn">
              <li class="mdl-menu__item" id="id2" onclick="mm({action: 'myprofile', mobile: 0});"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">account_circle</i>Mon profil</li>
              <li class="mdl-menu__item" id="id3" onclick="mm({action: 'login', mobile: 0});"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">lock_open</i>Se déconnecter</li>
            </ul>
          </div>
<? } else { ?>
            <img src="img/logo.png" class="demo-avatar">
            <div class="demo-avatar-dropdown">
                <span>Welcome to Jorkers.com</span>
                <div class="mdl-layout-spacer"></div>
                <button id="accbtn" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon" onclick="mm({action: 'login', mobile: 0});">
                    <i class="material-icons" role="presentation">lock_outline</i>
                    <span class="visuallyhidden">Se connecter</span>
                </button>
            </div>
<? } ?>
        </header>

        <nav class="demo-navigation mdl-navigation">
          <? if ($sess_context->getRealChampionnatId() > 0) { ?>
          <a class="mdl-navigation__link" href="#" onclick="mm({action: 'dashboard'});"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">dashboard</i>Dashboard</a>
          <a class="mdl-navigation__link" href="#" onclick="mm({action: 'players'});"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">person</i>Joueurs</a>
          <a class="mdl-navigation__link" href="#" onclick="mm({action: 'teams'});"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">group</i>Equipes</a>
          <a class="mdl-navigation__link" href="#" onclick="mm({action: 'days', grid: -1, tournoi: <?= $sess_context->isTournoiXDisplay() ? 1 : 0 ?>});"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">today</i>Journées</a>
          <a class="mdl-navigation__link" href="#" onclick="mm({action: 'tables'});"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">timeline</i>Classements</a>
          <a class="mdl-navigation__link" href="#" onclick="mm({action: 'tchat'});"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">sms</i>Tchat</a>
          <a class="mdl-navigation__link" href="#" onclick="mm({action: 'photos'});"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">photo</i>Photos</a>
          <? } ?>
          <a id="home" class="mdl-navigation__link" href="../home/index.php"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">home</i>Jorkers.com</a>
        </nav>
