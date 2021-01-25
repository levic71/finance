<?

require_once "../include/sess_context.php";
session_start();
include "common.php";

header('Content-Type: text/html; charset='.sess_context::charset);


if ($sess_context->isUserConnected()) { ?>
<!--
	<img src="<?= Wrapper::formatPhotoJoueur(file_exists($sess_context->user['photo']) ? $sess_context->user['photo'] : "img/user-icon.png") ?>" class="demo-avatar">
-->
	<span class="mdl-chip">
	<span class="mdl-chip__text"><?= $sess_context->user['pseudo'] ?></span>
		<button id="accbtn" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
			<i class="material-icons connected">account_circle</i>
			<span class="visuallyhidden">Accounts</span>
		</button>
	</span>
	<ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="accbtn">
		<li class="mdl-menu__item" id="id2" onclick="mm({action: 'myprofile', mobile: 0});"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">account_circle</i>Mon profil</li>
		<li class="mdl-menu__item" id="id3" onclick="mm({action: 'logout', mobile: 0});"><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">lock_open</i>Se déconnecter</li>
	</ul>

<? } else { ?>
	<button id="accbtn" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon" onclick="mm({action: 'login', mobile: 0});">
		<i class="material-icons" role="presentation">account_circle</i>
		<span class="visuallyhidden">Se connecter</span>
	</button>
<? } ?>