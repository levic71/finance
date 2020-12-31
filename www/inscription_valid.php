<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

if (!isset($cookie_admin))
{
	$cookie_admin = Toolbox::sessionId();
	setcookie("cookie_admin", $cookie_admin, time()+(3600*24*30*12*10));
}

if (!isset($options))
{
	$options  = "";
	$options .= isset($chk_news)   && $chk_news   == "on" ? "1|" : "0|";
	$options .= isset($chk_forum)  && $chk_forum  == "on" ? "1|" : "0|";
	$options .= isset($chk_fannys) && $chk_fannys == "on" ? "1|" : "0|";
	$options .= isset($chk_prev)   && $chk_prev   == "on" ? "1|" : "0|";
	$options .= isset($chk_next)   && $chk_next   == "on" ? "1|" : "0|";
	$options .= isset($chk_focus)  && $chk_focus  == "on" ? "1|" : "0|";
	$options .= isset($chk_clt_joueurs)  && $chk_clt_joueurs  == "on" ? "1|" : "0|";
	$options .= isset($chk_poule_lettre) && $chk_poule_lettre == "on" ? "1|" : "0|";
	$options .= isset($chk_all_matchs)   && $chk_all_matchs   == "on" ? "1|" : "0|";
	$options .= isset($chk_matchs)       && $chk_matchs       == "on" ? "1|" : "0|";
	$options .= isset($chk_team)         && $chk_team         == "on" ? "1|" : "0|";
}

$db = dbc::connect();
$scs = new SQLChampionnatsServices();
$row = $scs->getChampionnatByNom($ch_nom);

// Si le championnat existe déjà, alors on retourne sur la page d'inscription
if ($row)
	ToolBox::do_redirect("championnat_details.php?inscription=1&inscription_valid=no&ch_nom=".$ch_nom."&ch_gestionnaire=".$ch_gestionnaire."&ch_login=".$ch_login."&ch_email=".$ch_email."&ch_description=".$ch_description."&ch_news=".$ta."&type=".$type."&options=".$options."&type_lieu=".$type_lieu."&lieu_pratique=".$lieu_pratique."&ch_pwd=".$ch_pwd);
else if ($ch_controle != $_SESSION['antispam'])
{
	unset($_SESSION['antispam']);
	ToolBox::do_redirect("championnat_details.php?etape=3&inscription=1&inscription_antispam=no&ch_nom=".$ch_nom."&ch_gestionnaire=".$ch_gestionnaire."&ch_login=".$ch_login."&ch_email=".$ch_email."&ch_description=".$ch_description."&ch_news=".$ta."&type=".$type."&options=".$options."&type_lieu=".$type_lieu."&lieu_pratique=".$lieu_pratique."&ch_pwd=".$ch_pwd);
}
else
{
	unset($_SESSION['antispam']);

	// Insertion du championnat
	$insert = "INSERT INTO jb_championnat (friends, gestion_fanny, gestion_sets, tri_classement_general, type_sport, gestion_nul, visu_journee, valeur_victoire, valeur_nul, valeur_defaite, news, options, genre, gestionnaire, login, pwd, email, dt_creation, nom, description, type, type_lieu, lieu, cookie) VALUES ('".$selected_friends."', ".$gestion_fanny.", ".$gestion_sets.", ".$tri_classement_general.", ".$type_sport.", ".$gestion_nul.", ".$visu_journee.", ".$valeur_victoire.", ".$valeur_nul.", ".$valeur_defaite.", '".$ta."', '".$options."', '', '".$ch_gestionnaire."', '".$ch_login."', '".$ch_pwd."', '".$ch_email."', '".date("Y")."-".date("m")."-".date("d")."', '".$ch_nom."', '".$ch_description."', ".$type.", ".$type_lieu.", '".$lieu_pratique."', '".$cookie_admin."');";
	$res = dbc::execSQL($insert);

	// Récupération infos championnat
	$select = "SELECT * FROM jb_championnat WHERE NOM='".$ch_nom."';";
	$res = dbc::execSQL($select);
	$row = mysql_fetch_array($res);

	// Insertion d'une saison
	$insert = "INSERT INTO jb_saisons (id_champ, nom, date_creation, active) VALUES (".$row['id'].", '".$ch_nom_saison."', '".date("Y")."-".date("m")."-".date("d")."', 1);";
	$res = dbc::execSQL($insert);


	$dns = trim($ch_nom, "-");
	$dns = trim($dns, " ");
	$dns = strtr($dns, 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
	$dns = strtr($dns, '+.,|!"£$%&/()=?^*ç°§;:_>][@);', '                             ');
	$dns = str_replace('\\', '', $dns);
	$dns = str_replace('\'', '', $dns);
	$dns = str_replace('-', ' ', $dns);
	while(substr_count($dns,"  ") != 0) $dns = str_replace("  ", " ", $dns);
	$dns = str_replace(' ', '-', strtolower($dns));


	// Suppression des caches
	JKCache::delCache("../cache/tdb_home.txt", "_FLUX_TDB_HOME_");
	JKCache::delCache("../cache/most_active_home.txt", "_FLUX_MOST_ACTIVE_");
	JKCache::delCache("../cache/access_home.txt", "_FLUX_ACCESS_");
	JKCache::delCache("../cache/last_created_home.txt", "_FLUX_LAST_CREATED_");

	// Envoi mail info
	$mail_to     = $ch_email;
	$mail_sujet  = "[Jorkers.com] Information";
	$mail_corps  = "Bonjour et bienvenue,\n\nMerci d'avoir créer un tournoi/championnat sur le jorkers.com, si vous rencontrez des difficultés ou si vous avez des questions, n'hésitez pas à me contacter.\n\nCordialement\nVictor";
	$mail_corps  .= "\n\nUne nouvelle version du jorkers.com est en cours de développement, vous pouvez la tester à l'url suivante : http://".$dns.".jorkers.com";
	$mail_header = "From: contact@jorkers.com\n";
	$mail_header .= "Bcc: contact@jorkers.com\n";
	$res = @mail($mail_to,  $mail_sujet, $mail_corps, $mail_header);


	Toolbox::trackUser($row['id'], _TRACK_ADMIN_);

	ToolBox::do_redirect("championnat_acces.php?inscription_valid=yes&ref_champ=".$row['id']);
}

mysql_close ($db);

?>
