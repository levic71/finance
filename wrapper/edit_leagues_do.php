<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$tri_classement_general = Wrapper::getRequest('tri_classement_general', 1);
$ch_nom            = Wrapper::getRequest('ch_nom',              '');
//$ch_gestionnaire   = Wrapper::getRequest('ch_gestionnaire',   '');
$ch_controle       = Wrapper::getRequest('ch_controle',         '');
$selected_friends  = Wrapper::getRequest('selected_friends',    '');
$gestion_fanny     = Wrapper::getRequest('gestion_fanny',       1);
$gestion_sets      = Wrapper::getRequest('gestion_sets',        1);
$gestion_buteurs   = Wrapper::getRequest('gestion_buteurs',     0);
$type_sport        = Wrapper::getRequest('type_sport',          1);
$gestion_nul       = Wrapper::getRequest('gestion_nul',         0);
$visu_journee      = Wrapper::getRequest('visu_journee',        0);
$valeur_victoire   = Wrapper::getRequest('valeur_victoire_zip', 3);
$valeur_defaite    = Wrapper::getRequest('valeur_defaite_zip',  0);
$valeur_nul        = Wrapper::getRequest('valeur_nul_zip',      1);
$type              = Wrapper::getRequest('type',                _TYPE_CHAMPIONNAT_);
$news              = Wrapper::getRequest('news',                '');
$options           = Wrapper::getRequest('options',             '1|1|1|1|1|1|1|0|0|0|0|0');
$ch_description    = Wrapper::getRequest('ch_description',      '');
$type_lieu         = Wrapper::getRequest('type_lieu',           _LIEU_VILLE_);
$lieu_pratique     = Wrapper::getRequest('lieu_pratique',       '');
$type_gestionnaire = Wrapper::getRequest('type_gestionnaire',   0);
$genre             = Wrapper::getRequest('genre',               '');
$zoom              = Wrapper::getRequest('zoom',                '10');
$lat               = Wrapper::getRequest('lat',                 '');
$lng               = Wrapper::getRequest('lng',                 '');
$twitter           = Wrapper::getRequest('twitter',             '');
$theme             = Wrapper::getRequest('theme',               _THEME_CLASSIQUE_);
$logo_font         = Wrapper::getRequest('logo_font',           8);
$logo_photo        = Wrapper::getRequest('photo',           	'');
$headcount         = Wrapper::getRequest('headcount',         	7);
$forfait_penalite_bonus = Wrapper::getRequest('forfait_penalite_bonus', 0);
$forfait_penalite_malus = Wrapper::getRequest('forfait_penalite_malus', 0);
$del               = Wrapper::getRequest('del',                 0);
$idl               = Wrapper::getRequest('idl',                 0);
$upd               = Wrapper::getRequest('upd',                 0);

if (($upd == 1 || $del == 1) && !is_numeric($idl)) ToolBox::do_redirect("grid.php");

if (!isset($cookie_admin))
{
	$cookie_admin = Toolbox::sessionId();
	setcookie("cookie_admin", $cookie_admin, time()+(3600*24*30*12*10));
}

if ($del == 1)
{
	// Non permis
	exit(0);
}

$modifier = $upd == 1 ? true : false;

if ($modifier)
{
	$vars_update = "nom='".$ch_nom."'";
	if (isset($type)) $vars_update .= ", type=".$type;
	if (isset($gestion_fanny)) $vars_update .= ", gestion_fanny=".$gestion_fanny;
	if (isset($gestion_sets)) $vars_update .= ", gestion_sets=".$gestion_sets;
	if (isset($gestion_buteurs)) $vars_update .= ", gestion_buteurs=".$gestion_buteurs;
	if (isset($tri_classement_general)) $vars_update .= ", tri_classement_general=".$tri_classement_general;
	if (isset($type_sport)) $vars_update .= ", type_sport=".$type_sport;
	if (isset($gestion_nul)) $vars_update .= ", gestion_nul=".$gestion_nul;
	if (isset($selected_friends)) $vars_update .= ", friends='".$selected_friends."'";
	if (isset($visu_journee)) $vars_update .= ", visu_journee=".$visu_journee;
	if (isset($valeur_victoire)) $vars_update .= ", valeur_victoire=".$valeur_victoire;
	if (isset($valeur_nul)) $vars_update .= ", valeur_nul=".$valeur_nul;
	if (isset($valeur_defaite)) $vars_update .= ", valeur_defaite=".$valeur_defaite;
	if (isset($type_lieu)) $vars_update .= ", type_lieu=".$type_lieu;
	if (isset($lieu_pratique)) $vars_update .= ", lieu='".$lieu_pratique."'";
	if (isset($options)) $vars_update .= ", options='".$options."'";
	if (isset($ch_description)) $vars_update .= ", description='".$ch_description."'";
	if (isset($ta)) $vars_update .= ", news='".$news."'";
	if (isset($type_gestionnaire)) $vars_update .= ", type_gestionnaire='".$type_gestionnaire."'";
	if (isset($zoom)) $vars_update .= ", zoom=".$zoom;
	if (isset($lat)) $vars_update .= ", lat='".$lat."'";
	if (isset($lng)) $vars_update .= ", lng='".$lng."'";
	if (isset($theme)) $vars_update .= ", theme=".$theme."";
	if (isset($theme)) $vars_update .= ", twitter='".$twitter."'";
	if (isset($logo_font)) $vars_update .= ", logo_font='".$logo_font."'";
	if (isset($headcount)) $vars_update .= ", home_list_headcount=".$headcount;
	if (isset($forfait_penalite_bonus)) $vars_update .= ", forfait_penalite_bonus=".$forfait_penalite_bonus;
	if (isset($forfait_penalite_malus)) $vars_update .= ", forfait_penalite_malus=".$forfait_penalite_malus;
	$vars_update .= ", logo_photo='".$logo_photo."'";
	$update = "UPDATE jb_championnat SET ".$vars_update." WHERE id=".$sess_context->getRealChampionnatId();
	$res = dbc::execSQL($update);

	$chp = Wrapper::getChampionnat($sess_context->getRealChampionnatId());
	$sess_context->setChampionnat($chp);

	JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");
	JKCache::delCache("../cache/info_champ_".$sess_context->getRealChampionnatId()."_.txt", "_FLUX_INFO_CHAMP_");
}
else
{
	$scs = new SQLChampionnatsServices();
	$row = $scs->getChampionnatByNom($ch_nom);

	// Si le championnat existe déjà, alors on retourne sur la page d'inscription
	if ($row)
	{
		echo "-1||Nom championnat déjà existant"; exit(0);
	}
	else if (false && $ch_controle != $_SESSION['antispam'])
	{
		echo "-1||Vérification robot non valide"; exit(0);
	}
	else
	{
		// Vérification nom championnat unique
		$req = "SELECT count(*) total FROM jb_championnat WHERE nom='".$ch_nom."'";
		$res = dbc::execSQL($req);
		$row = mysqli_fetch_array($res);
		if ($row['total'] > 0) { echo "-1||Nom championnat existant"; exit(0); }

		unset($_SESSION['antispam']);

		// Insertion du championnat
		$insert = "INSERT INTO jb_championnat (twitter, forfait_penalite_bonus, forfait_penalite_malus, home_list_headcount, theme, logo_font, logo_photo, zoom, lat, lng, type_gestionnaire, friends, gestion_fanny, gestion_sets, gestion_buteurs, tri_classement_general, type_sport, gestion_nul, visu_journee, valeur_victoire, valeur_nul, valeur_defaite, news, options, genre, dt_creation, nom, description, type, type_lieu, lieu, cookie) VALUES ('".$twitter."', ".$forfait_penalite_bonus.", ".$forfait_penalite_malus.", ".$headcount.", ".$theme.", '".$logo_font."', '".$logo_photo."', ".$zoom.", '".$lat."', '".$lng."', ".$type_gestionnaire.", '".$selected_friends."', ".$gestion_fanny.", ".$gestion_sets.", ".$gestion_buteurs.", ".$tri_classement_general.", ".$type_sport.", ".$gestion_nul.", ".$visu_journee.", ".$valeur_victoire.", ".$valeur_nul.", ".$valeur_defaite.", '".$news."', '".$options."', '', '".date("Y")."-".date("m")."-".date("d")."', '".$ch_nom."', '".$ch_description."', ".$type.", ".$type_lieu.", '".$lieu_pratique."', '".$cookie_admin."');";
		$res = dbc::execSQL($insert);

		// Récupération infos championnat
		$req = "SELECT c.entity entity, c.gestion_fanny, c.gestion_sets, c.gestion_buteurs, c.tri_classement_general, c.type_sport, c.demo, c.gestion_nul, c.friends friends, c.type_lieu type_lieu, c.email email, c.login login, c.pwd pwd, c.description description, c.lieu lieu, c.gestionnaire gestionnaire, c.dt_creation dt_creation, c.valeur_victoire valeur_victoire, c.valeur_defaite valeur_defaite, c.valeur_nul valeur_nul, c.visu_journee visu_journee, c.news news, c.options options, c.id championnat_id, s.id saison_id, c.type type, c.nom championnat_nom, s.nom saison_nom FROM jb_championnat c, jb_saisons s WHERE c.nom='".$ch_nom."'";
		$res = dbc::execSQL($req);
		$row = mysqli_fetch_array($res);

		// Insertion du role
		$insert = "INSERT INTO jb_roles (id_champ, id_user, role) VALUES (".$row['championnat_id'].", ".$sess_context->user['id'].", "._ROLE_ADMIN_.");";
		$res = dbc::execSQL($insert);

		// Insertion d'une saison
		$insert = "INSERT INTO jb_saisons (id_champ, nom, date_creation, active) VALUES (".$row['championnat_id'].", '".$ch_nom_saison."', '".date("Y")."-".date("m")."-".date("d")."', 1);";
		$res = dbc::execSQL($insert);

		// Suppression des caches
		JKCache::delCache("../cache/tdb_home.txt", "_FLUX_TDB_HOME_");
		JKCache::delCache("../cache/most_active_home.txt", "_FLUX_MOST_ACTIVE_");
		JKCache::delCache("../cache/access_home.txt", "_FLUX_ACCESS_");
		JKCache::delCache("../cache/last_created_home.txt", "_FLUX_LAST_CREATED_");

		// Envoi mail info
//		$mail_to     = $ch_email;
		$mail_to     = $sess_context->user['email'];
		$mail_sujet  = "[Jorkers.com] Information";
		$mail_corps  = "Bonjour et bienvenue,\n\nMerci d'avoir creer un tournoi/championnat sur le jorkers.com, si vous rencontrez des difficultes ou si vous avez des questions, n'hesitez pas a me <a href=\"http://www.jorkers.com\">contacter</a>.\n\n";
		$mail_corps  .= "Votre championnat est directement accessible a l'adresse suivante <a href=\"http://".Wrapper::string2DNS($ch_nom).".jorkers.com\">http://".Wrapper::string2DNS($ch_nom).".jorkers.com</a>.\n\nCordialement\nVictor";
		$mail_header = "From: contact@jorkers.com\n";
		$mail_header .= "Bcc: contact@jorkers.com\n";
		$mail_header .= "MIME-Version: 1.0\n";
		$mail_header .= "X-Mailer: PHP/".phpversion()."\n";
		$mail_header .= "X-Priority: 1\n";
		$mail_header .= "Content-Type: text/html; charset=".sess_context::mail_charset."\n";

		if (!$sess_context->isSuperUser())
		{
			$res = @mail($mail_to,  utf8_decode(stripslashes($mail_sujet)), utf8_decode(nl2br(stripslashes($mail_corps))), $mail_header);
		}

		unset($_SESSION['autologonadmin']);
		$_SESSION['autologonadmin'] = 1;

		?><span class="hack_ie">_HACK_IE_</span><script>mm({action:'home', idc:<?= $row['championnat_id'] ?>});</script><?
		exit(0);
	}
}

?><span class="hack_ie">_HACK_IE_</span><script>removejscssfile('theme', 'css'); addcssfile('css/theme<?= $theme ?>.css');<?= false && $logo_photo != "" ? "el('inner').style.backgroundImage='url(".$logo_photo.")';" : "" ?> <?= $logo_font != "" ? "el('logo').className='logo".$logo_font."';" : "" ?> mm({action:'dashboard'}); $cMsg({ msg: 'Championnat <?= $modifier ? "modifié" : "créé" ?>' });</script>