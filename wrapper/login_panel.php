<?

require_once "../include/sess_context.php";
session_start();
include "common.php";

header('Content-Type: text/html; charset='.sess_context::charset);


if ($sess_context->isUserConnected()) { ?>
	<span class="mdl-chip">	
		<span class="mdl-chip__text"><?= $sess_context->user['pseudo'] ?></span>
		<button id="accbtn" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
			<i class="material-icons connected">account_circle</i>
			<span class="visuallyhidden">Accounts</span>
		</button>
	</span>
	<ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="accbtn">

		<li class="mdl-menu__item" onclick="mm({action: 'myprofile', mobile: 0});">
			<button id="accbtn" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
				<i class="material-icons mdl-list__item-icon mdl-color-text--blue-grey-400">account_circle</i>
			</button>
			<span class="mdl-list__item-primary-content">Mon profil</span>
		</li>

		<li class="mdl-menu__item" onclick="mm({action: 'logout', mobile: 0});">
			<button id="accbtn" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
				<i class="material-icons mdl-list__item-icon mdl-color-text--blue-grey-400">lock_open</i>
			</button>
			<span class="mdl-list__item-primary-content">Se déconnecter</span>
		</li>

	</ul>

<? } else { ?>
	<button id="accbtn" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon" onclick="mm({action: 'login', mobile: 0});">
		<i class="material-icons" role="presentation">account_circle</i>
		<span class="visuallyhidden">Se connecter</span>
	</button>
<? } ?>