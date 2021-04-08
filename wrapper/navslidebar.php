<?

require_once "../include/sess_context.php";
session_start();
include "common.php";
header('Content-Type: text/html; charset='.sess_context::xhr_charset);

?>

<header class="mdl-layout__header" style="justify-align: center; padding: 0px; overflow: hidden;">
  <img src="img/mylogo.png">
</header>

<nav class="demo-navigation mdl-navigation">
  <? if ($sess_context->getRealChampionnatId() > 0) { ?>
      <a class="mdl-navigation__link" href="#" onclick="mm({action: 'dashboard'});"><i class="material-icons" role="presentation">dashboard</i>Dashboard</a>
      <a class="mdl-navigation__link" href="#" onclick="mm({action: 'players'});"><i class="material-icons" role="presentation">person</i>Joueurs</a>
      <a class="mdl-navigation__link" href="#" onclick="mm({action: 'teams'});"><i class="material-icons" role="presentation">group</i>Equipes</a>
      <a class="mdl-navigation__link" href="#" onclick="mm({action: 'days', grid: -1, tournoi: <?= $sess_context->isTournoiXDisplay() ? 1 : 0 ?>});"><i class="material-icons" role="presentation">today</i>Journées</a>
      <a class="mdl-navigation__link" href="#" onclick="mm({action: 'tables'});"><i class="material-icons" role="presentation">timeline</i>Classements</a>
      <a class="mdl-navigation__link" href="#" onclick="mm({action: 'tchat'});"><i class="material-icons" role="presentation">sms</i>Tchat</a>
      <a class="mdl-navigation__link" href="#" onclick="mm({action: 'photos'});"><i class="material-icons" role="presentation">photo</i>Photos</a>
  <? } ?>
      <a id="home" class="mdl-navigation__link" href="#" onclick="mm({action: 'leagues'});"><i class="material-icons" role="presentation">list</i>Annuaire</a>
      <a class="mdl-navigation__link" href="#" onclick="go({action: 'leagues', id:'main', url:'edit_leagues.php'});"><button id="new-champ" class="mdl-button mdl-js-button"><i class="material-icons" role="presentation">add_box</i><small>New Competition</small></button></a>
  <? if (!($sess_context->getRealChampionnatId() > 0)) { ?>
  <? } ?>
</nav>
  
<footer class="mdl-mega-footer">
<div class="mdl-mega-footer__top-section">
  <div class="mdl-mega-footer__left-section">
      <button class="mdl-button mdl-button--icon"><i class="material-icons" onclick="mm({action: 'home'});">home_outline</i></button>
      <button class="mdl-button mdl-button--icon"><i class="material-icons" onclick="go({action: 'help', id:'main', url:'help.php'});">help_outline</i></button>
      <button class="mdl-button mdl-button--icon"><i class="material-icons" onclick="go({action: 'contact', id:'main', url:'contacter.php'});">mail_outline</i></button>		    
  </div>
</footer>

