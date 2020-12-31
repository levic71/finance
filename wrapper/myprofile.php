<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

if (!$sess_context->isUserConnected()) {
	echo "Pb session utilisateur ...";
	exit(0);
}

$t = array();
array_push($t, array("id" => "sb_upd",    "onclick" => "mm({action: 'updprofile'});", "tooltip" => "Editer profil"));
array_push($t, array("id" => "sb_weight", "onclick" => "alert('Coming soon');", "tooltip" => "Gérer son poids"));
array_push($t, array("id" => "sb_runner", "onclick" => "alert('Coming soon');", "tooltip" => "Gérer ses activités"));
Wrapper::fab_button_menu($t);

?>

<div id="myprofile" class="mdl-grid demo-content">

	<div class="mdl-cell mdl-cell--12-col mdl-card mdl-shadow--2dp">
		<div class="mdl-card__title mdl-color--primary mdl-color-text--white">
			<h2 class="mdl-card__title-text"><?= $sess_context->user['prenom']." ".$sess_context->user['nom'] == " " ? $sess_context->user['pseudo'] : $sess_context->user['prenom']." ".$sess_context->user['nom'] ?></h2>
		</div>
		<div class="mdl-card__supporting-text">
			<div style="width: auto; height: 175px;">
				<img style="height: 150px; width: 150px;" src="<?= Wrapper::formatPhotoJoueur(file_exists($sess_context->user['photo']) ? $sess_context->user['photo'] : "img/user-icon.png") ?>" />
<? if ($sess_context->user['prenom']." ".$sess_context->user['nom'] != " ") { ?>
				<div><?= $sess_context->user['pseudo'] ?></div>
<? } ?>
			</div>

<?
	$largeur = 250;
	$tier = floor($largeur / 3);
	$val = $sess_context->user['poids'];
	$h   = $sess_context->user['taille'];
	$cp  = $sess_context->user['poignet'];
	$a   = Wrapper::formatNumber(Toolbox::date2age($sess_context->user['date_nais']));

	$pfm     = ($val < 30 || $val > 200 || $a < 3 || $a > 100) ? $val - 5 : round(($h-100+4*$cp)/2, 1);
	$pfm_max = ($val < 30 || $val > 200 || $a < 3 || $a > 100) ? $val + 5 : round((($h-100+4*$cp)/2)+(($h-100+4*$cp)/2)*0.1, 1);
	$min = $pfm;
	$max = $pfm_max;

	$x = ($val - $min) / ($max - $min);
	$pos_curseur = $tier + floor($tier * $x);
	if ($pos_curseur < 0) $pos_curseur = -5;
	if ($pos_curseur > $largeur) $pos_curseur = $largeur-15;
?>

			<button id="b6" class="button <?= $sess_context->user['sexe'] == 1 ? "blue" : "rosy" ?>"><div class="box"><div class="cnt"><?= $a ?></div><div class="txt">Ans</div></div></button>
			<button id="b7" class="button blue"><div class="box"><div class="cnt"><?= Wrapper::formatNumber($sess_context->user['taille']) ?></div><div class="txt">Cm</div></div></button>
			<button id="b8" class="button blue"><div class="box"><div id="imcval" class="cnt"></div><div class="txt">IMC</div></div></button>
			<button id="b11" class="button blue"><div class="box"><div id="imgval" class="cnt"></div><div class="txt">IMG</div></div></button>

<? if (false) { ?>
<div class="gradientweight" style="float: left; margin-left: 5px;">
	<div class="box box-wrapper" style="float: left; width: <?= $largeur ?>px; margin-top: 5px; padding-left: 10px;">
		<div style="float: left; clear: both; height: 15px; width: <?= $largeur ?>px; background: url('img/dir.png') no-repeat <?= $pos_curseur ?>px -30px;"></div>

		<div style="float: left; clear: both;">
			<div id="leftg" style="float: left; width: <?= $tier ?>px; height: 15px;"></div>
			<div id="leftc" style="float: left; width: <?= $tier ?>px; height: 15px;"></div>
			<div id="leftd" style="float: left; width: <?= $tier ?>px; height: 15px;"></div>
		</div>

		<div style="float: left; clear: both;">
			<div style="float: left; margin-left: <?= floor($tier/2) ?>px; width: <?= $tier ?>px; height: 16px; text-align: center; font-weight: bold; color: #2d6000;"><?= $pfm ?></div>
			<div style="float: left; width: <?= $tier ?>px; height: 16px; text-align: center; font-weight: bold; color: #2d6000;"><?= $pfm_max ?></div>
		</div>
	</div>
	<button id="b10" onclick="alert('Coming soon');" class="button blue" style="margin: 5px 0px 0px 10px; padding-left: 5px !important;"><div class="box" style="width: 50px; height: 45px;"></div></button>
	<button id="b9" class="button blue" style="margin: 5px 0px 0px 10px; padding-left: 5px !important;"><div class="box"><div class="cnt"><?= Wrapper::formatNumber($sess_context->user['poids']) ?></div><div class="txt">Kg</div></div></button>
</div>
<? } ?>

		</div>
		<div class="mdl-card__actions mdl-card--border">
			<a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" onclick="mm({action: 'updprofile'});">
				Editer
			</a>
		</div>
		<div class="mdl-card__menu">
			<button class="mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect">
				<i class="material-icons">share</i>
			</button>
		</div>
	</div>

<!--
		<div style="float: left; padding: 0px 5px 0px 10px; font-size: 11px; color: #666;">Dépenses calorique quotidienne en calories par jour (cal/j)</div>
		<button class="button purple calorie"><div id="dcqvval" class="cnt"></div><div class="txt">Energie vitale</div></button>
		<button class="button purple calorie"><div id="dcqmval" class="cnt"></div><div class="txt">Pour maintien poids</div></button>
		<button class="button purple calorie"><div id="dcqxval" class="cnt"></div><div class="txt">A ne pas dépasser</div></button>
-->

<?
	$tier = 33.33; // (100% / 3)
	$pos_curseur = $tier + floor($tier * $x);
	if ($pos_curseur < 0) $pos_curseur = 0;
	if ($pos_curseur > 100) $pos_curseur = 100;
?>


		<!-- div class="barweight" style="margin-top: 25px;">
			<div class="percent">
				<span style="width: 100%;"></span>
			</div>
			<div class="circle" id="bulle">
				<span><?= $sess_context->user['poids'] ?>kg</span>
			</div>
		</div -->
	<!--
		<div style="clear: both; float: left; width: 100%; height: 20px; color: #666;">
			<div style="float: left; width: 35%; text-align: right;"><?= $pfm ?></div>
			<div style="float: left; width: 30%;">&nbsp;</div>
			<div style="float: left; width: 34%;"><?= $pfm_max ?></div>
		</div>

		<div style="float: left; text-align: center; font-size: 10px; color: #777; width: 100%;">(Informations à titre indicatif, la consultation d'un spécialiste est nécessaire)</div>
-->


	<div class="mdl-cell mdl-cell--12-col mdl-card mdl-card__list mdl-shadow--2dp">
		<div class="mdl-card__title mdl-color--primary mdl-color-text--white">
			<h2 class="mdl-card__title-text">Championnats administrés</h2>
		</div>
		<div class="mdl-card__list-table">
			<table border="0" cellpadding="0" cellspacing="0" class="jkgrid lstroles" id="roles">
			<thead><tr><th class="c1"></th><th class="c2"><div>Championnat</div></th><th class="c3"><div>Role</div></th><th class="c4"><div>Actif</div></th><th class="c4"><div>Administration</div></th><th class="c5"><div>&nbsp;</div></th></tr></thead>
			<tbody><?

			$i = 0;
			$select = "SELECT r.role role, ELT(r.status+1, '<img src=\"img/block_16.png\" />', '<img src=\"img/tick_16.png\" />') status, c.nom nom, c.id FROM jb_roles r, jb_championnat c WHERE r.id_champ=c.id AND r.id_user=".$sess_context->user['id'].";";
			$res = dbc::execSQL($select);
			while ($row = mysqli_fetch_array($res))
			{
				echo "<tr><td class=\"c1\"></td><td class=\"c2\"><div>".$row['nom']."</div></td><td class=\"c3\"><div>".$libelle_role[$row['role']]."</div></td><td class=\"c4\"><div>".$row['status']."</div></td><td class=\"c4\"><div><a href=\"jk.php?idc=".$row['id']."\" class=\"full-circle\"><span class=\"bullet\"></span></a></div></td><td class=\"c5\"><div>&nbsp;</div></td></tr>";
				$i++;
			}
			if (!$sess_context->isSuperAdmin() && $i == 0) echo "<tr><td class=\"c1\"></td><td colspan=\"4\" style=\"width: 100%;\">Aucun droits</td></tr>";
			if ($sess_context->isSuperAdmin()) echo "<tr><td class=\"c1\"></td><td colspan=\"4\" style=\"width: 100%;\">Tous les droits sur tous les championnats !!!</td></tr>";
			?>
			</tbody></table>
		</div>
	</div>

	<div class="mdl-cell mdl-cell--12-col mdl-card mdl-card__list mdl-shadow--2dp">
		<div class="mdl-card__title mdl-color--primary mdl-color-text--white">
			<h2 class="mdl-card__title-text">Joueur dans championnats</h2>
		</div>
		<div class="mdl-card__list-table">

			<table border="0" cellpadding="0" cellspacing="0" class="jkgrid lstroles" id="roles">
			<thead><tr><th class="c1"></th><th class="c2"><div>Championnat</div></th><th class="c3"><div>Joueur</div></th><th class="c4"><div>Rattachement</div></th><th class="c5"><div>&nbsp;</div></th></tr></thead>
			<tbody><?

			$i = 0;
			$select = "SELECT up.id id, c.nom nom, j.pseudo joueur, ELT(up.status+1, '<img src=\"img/block_16.png\" />', '<img src=\"img/tick_16.png\" />') status FROM jb_championnat c, jb_users u, jb_joueurs j, jb_user_player up WHERE u.id=up.id_user AND j.id=up.id_player AND j.id_champ=c.id AND u.id=".$sess_context->user['id'].";";
			$res = dbc::execSQL($select);
			while ($row = mysqli_fetch_array($res))
			{
				echo "<tr><td class=\"c1\"></td><td class=\"c2\"><div>".$row['nom']."</div></td><td class=\"c3\"><div>".$row['joueur']."</div></td><td class=\"c4\"><div>".$row['status']."</div></td><td class=\"c5\"><div>&nbsp;</div></td></tr>";
				$i++;
			}
			if ($i == 0) echo "<tr><td class=\"c1\"></td><td colspan=\"4\" style=\"width: 100%;\">Aucun rattachement</td></tr>";
			?>
			</tbody></table>
		</div>
	</div>

<!-- script>
ftj.setJorkersProfile({ h: <?= $sess_context->user['taille'] ?>, w: <?= $sess_context->user['poids'] ?>, a: <?= $a ?>, s: <?= $sess_context->user['sexe'] ?>, act: <?= $sess_context->user['activite'] ?>, cp: <?= $sess_context->user['poignet'] ?>, morph: <?= $sess_context->user['morpho'] ?> });
var val = <?= $pos_curseur ?>;
addCN('bulle', 'move');
el('bulle').style.left = val+'%';
</script -->

</div>
