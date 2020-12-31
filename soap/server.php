<?

require_once('lib/nusoap.php');
require_once("json.php");
include "../include/inc_db.php";
include "../include/cache_manager.php";
include "../include/constantes.php";
include "../wrapper/entities.php";

$db = dbc::connect();
$debug = true;

$json = new Services_JSON();

// Create the server instance
$server = new soap_server;

// Register the method to expose
$server->register('CreateChampionship');
$server->register('DeleteChampionship');
$server->register('EnableChampionship');
$server->register('DisableChampionship');
$server->register('UpdateMatch');

// Define the method as a PHP function
function cleanStr($str) {
	return utf8_decode(trim($str));
}

function CreateChampionship($options) {

	global $json, $entities_autorised, $debug;

	$idc = 0;
	$log = "";
	$tmp = $json->decode($options);

	if (isset($tmp->entity) && strlen(trim($tmp->entity)) > 3 && isset($entities_autorised[$tmp->entity]))
		$entity = trim($tmp->entity);
	else
		return "-1:Entity not valid";

	// Créer le championnat
	if (isset($tmp->name) && strlen(trim($tmp->name)) > 3)
		$name = cleanStr($tmp->name);
	else
		return "-2:Championship name not valid, 3 caracters minimum";

	$desc    = isset($tmp->desc) ? cleanStr($tmp->desc) : "";
	$manager = isset($tmp->manager) ? trim($tmp->manager) : "";

	if (isset($tmp->login) && strlen(trim($tmp->login)) > 3)
		$login = trim($tmp->login);
	else
		return "-3:Invalid login";

	if (isset($tmp->pwd) && strlen(trim($tmp->pwd)) > 3)
		$pwd = trim($tmp->pwd);
	else
		return "-4:Invalid password";

	if (isset($tmp->type))
		$type = trim($tmp->type);
	else
		return "-5:Invalid type";

	if (isset($tmp->sport))
		$sport = trim($tmp->sport);
	else
		return "-5:Invalid sport";

	if (isset($tmp->email))
		$email = trim($tmp->email);
	else
		return "-6:Invalid email";

	if (isset($tmp->saison_name))
		$saison_name = trim($tmp->saison_name);
	else
		return "-7:Invalid saison name";

	$place = isset($tmp->place) ? trim($tmp->place) : "Paris";
	$win   = isset($tmp->win)   ? trim($tmp->win)   : "3";
	$drawn = isset($tmp->drawn) ? trim($tmp->drawn) : "1";
	$lost  = isset($tmp->lost)  ? trim($tmp->lost)  : "0";
	$drawn_enable = isset($tmp->drawn_enable) ? trim($tmp->drawn_enable) : "1";
	$sets_enable  = isset($tmp->sets_enable)  ? trim($tmp->sets_enable)  : "0";

	// Tester si on a deja creer un championnat avec le meme nom et meme entity et meme login !
	$req = "SELECT COUNT(*) total FROM jb_championnat WHERE nom='".$name."' AND entity='".$entity."' AND login='".$login."'";
	if ($debug) $log .= $req."\n";
	$res = mysql_query($req);
	if ($row = mysql_fetch_array($res))
	{
		if ($row['total'] > 0)
		{
			return "-10:Championship name already exists";
		}
	}

	// Insertion du championnat
	$insert = "INSERT INTO jb_championnat (entity, options, gestion_sets, type_sport, gestion_nul, valeur_victoire, valeur_nul, valeur_defaite, gestionnaire, login, pwd, email, dt_creation, nom, description, type, lieu) VALUES ('".$entity."', '1|1|1|1|1|1|1|0|0|0|0|1|', ".$sets_enable.", ".$sport.", ".$drawn_enable.", ".$win.", ".$drawn.", ".$lost.", '".$manager."', '".$login."', '".$pwd."', '".$email."', '".date("Y")."-".date("m")."-".date("d")."', '".$name."', '".$desc."', ".$type.", '".$place."');";
	if ($debug) $log .= $insert."\n";
	$res = mysql_query($insert);

	// Récupération infos championnat
	$select = "SELECT * FROM jb_championnat WHERE nom='".$name."' AND entity='".$entity."' AND login='".$login."'";
	if ($debug) $log .= $select."\n";
	$res = mysql_query($select);
	$row = mysql_fetch_array($res);
	$idc = $row['id'];

	// Insertion d'une saison
	$insert = "INSERT INTO jb_saisons (id_champ, nom, date_creation, active) VALUES (".$idc.", '".$saison_name."', '".date("Y")."-".date("m")."-".date("d")."', 1);";
	if ($debug) $log .= $insert."\n";
	$res = mysql_query($insert);

	// Insérer les équipes
	$teams_inserted = 0;
    if (isset($tmp->teams) && is_array($tmp->teams))
    {
        foreach($tmp->teams as $team)
        {
			$insert = "INSERT INTO jb_equipes (id_champ, nom, photo, commentaire, external_id) VALUES (".$idc.", '".$team->name."', '".$team->photo."', '".$team->comment."', ".$team->external_id.");";
			if ($debug) $log .= $insert."\n";
			$res = mysql_query($insert);


			// Pour les tournois et les championnats, ajouter l'équipe à la saison en cours
			if ($type != _TYPE_LIBRE_)
			{
				$select = "SELECT * FROM jb_equipes WHERE nom='".$team->name."' AND id_champ=".$idc;
				if ($debug) $log .= $select."\n";
				$res = dbc::execSQL($select);
				if ($eq = mysql_fetch_array($res))
				{
					$select = "SELECT * FROM jb_saisons WHERE id_champ=".$idc." AND active=1";
					if ($debug) $log .= $select."\n";
					$res = dbc::execSQL($select);
					if ($row = mysql_fetch_array($res))
					{
						$equipes = $row['equipes'].($row['equipes'] == "" ? "" : ",").$eq['id'];
						$update  = "UPDATE jb_saisons SET equipes='".$equipes."' WHERE id_champ=".$idc." AND id=".$row['id'];
						if ($debug) $log .= $update."\n";
						$res = dbc::execSQL($update);
					}
				}
			}

			$teams_inserted++;
        }
	}

	// Insérer les joueurs
	$players_inserted = 0;
    if (isset($tmp->players) && is_array($tmp->players))
    {
        foreach($tmp->players as $player)
        {
			$players_inserted++;
        }
	}

	// Si nb joueurs > 0 et nb memoriser !!!!
	$update = "UPDATE jb_championnat SET sync_player=".($players_inserted > 0 ? "1" : "0").", sync_team=".($teams_inserted > 0 ? "1" : "0")." WHERE id=".$idc.";";
	if ($debug) $log .= $update."\n";
	$res = mysql_query($update);

	// Envoi mail info
	$mail_to     = "victor.ferreira@laposte.net";
	$mail_sujet  = "[Jorkers.com] Information";
	$mail_corps  = "Bonjour et bienvenue,\n\nMerci d'avoir créer un tournoi/championnat sur le jorkers.com, si vous rencontrez des difficultés ou si vous avez des questions, n'hésitez pas à me contacter.\n\nCordialement\nVictor\n\n";
	$mail_corps  .= $entity.":".$name.":http://www.jorkers.com/www/championnat_redirect.php?champ=".$idc;
	$mail_header = "From: contact@jorkers.com\n";
	$res = @mail($mail_to,  $mail_sujet, $mail_corps, $mail_header);

	DeleteCache($idc);
	if ($debug) $log .= "Cache clean\n";

    $log .= $idc.":Created";

    return $log;
}

function DeleteChampionship($options) {

	global $json, $entities_autorised, $debug;

	$idc = 0;
	$log = "";
	$tmp = $json->decode($options);

	if (isset($tmp->entity) && strlen(trim($tmp->entity)) > 3 && isset($entities_autorised[$tmp->entity]))
		$entity = trim($tmp->entity);
	else
		return "-1:Entity not valid";

	if (isset($tmp->id))
		$idc = trim($tmp->id);
	else
		return "-2:Id championship is mandatory";

	if (isset($tmp->login) && strlen(trim($tmp->login)) > 3)
		$login = trim($tmp->login);
	else
		return "-3:Invalid login";

	if (isset($tmp->pwd) && strlen(trim($tmp->pwd)) > 3)
		$pwd = trim($tmp->pwd);
	else
		return "-4:Invalid password";

	// Tester si parametres coherents
	$req = "SELECT COUNT(*) total FROM jb_championnat WHERE id=".$idc." AND entity='".$entity."' AND login='".$login."' AND pwd='".$pwd."'";
	if ($debug) $log .= $req."\n";
	$res = mysql_query($req);
	if ($row = mysql_fetch_array($res))
	{
		if ($row['total'] != 1)
		{
			return $log."-10:Wrong championship parameters";
		}
	}

	$delete = "DELETE FROM jb_forum WHERE id_champ=".$idc.";";
	if ($debug) $log .= $delete."\n";
	$res = mysql_query($delete);

	$req = "SELECT * FROM jb_saisons WHERE id_champ=".$idc.";";
	if ($debug) $log .= $req."\n";
	$res = mysql_query($req);
	while($row = mysql_fetch_array($res))
	{
		$delete = "DELETE FROM jb_matchs WHERE id_champ=".$row['id'].";";
		if ($debug) $log .= $delete."\n";
		$res2 = mysql_query($delete);
		$delete = "DELETE FROM jb_journees WHERE id_champ=".$row['id'].");";
		if ($debug) $log .= $delete."\n";
		$res3 = mysql_query($delete);
	}

	$delete = "DELETE FROM jb_equipes WHERE id_champ=".$idc.";";
	if ($debug) $log .= $delete."\n";
	$res = mysql_query($delete);

	$delete = "DELETE FROM jb_joueurs WHERE id_champ=".$idc.";";
	if ($debug) $log .= $delete."\n";
	$res = mysql_query($delete);

	$delete = "DELETE FROM jb_saisons WHERE id_champ=".$idc.";";
	if ($debug) $log .= $delete."\n";
	$res = mysql_query($delete);

	$delete = "DELETE FROM jb_championnat WHERE id=".$idc.";";
	if ($debug) $log .= $delete."\n";
	$res = mysql_query($delete);

	DeleteCache($idc);
	if ($debug) $log .= "Cache clean\n";

	$log .= $idc.":Deleted";

    return $log;
}

function EnableChampionship($options) { return SetChampionship($options, 1); }
function DisableChampionship($options) { return SetChampionship($options, 0); }

function SetChampionship($options, $actif) {

	global $json, $entities_autorised, $debug;

	$idc = 0;
	$log = "";
	$tmp = $json->decode($options);

	if (isset($tmp->entity) && strlen(trim($tmp->entity)) > 3 && isset($entities_autorised[$tmp->entity]))
		$entity = trim($tmp->entity);
	else
		return "-1:Entity not valid";

	if (isset($tmp->id))
		$idc = trim($tmp->id);
	else
		return "-2:Id championship is mandatory";

	if (isset($tmp->login) && strlen(trim($tmp->login)) > 3)
		$login = trim($tmp->login);
	else
		return "-3:Invalid login";

	if (isset($tmp->pwd) && strlen(trim($tmp->pwd)) > 3)
		$pwd = trim($tmp->pwd);
	else
		return "-4:Invalid password";

	// Tester si parametres coherents
	$req = "SELECT COUNT(*) total FROM jb_championnat WHERE id=".$idc." AND entity='".$entity."' AND login='".$login."' AND pwd='".$pwd."'";
	if ($debug) $log .= $req."\n";
	$res = mysql_query($req);
	if ($row = mysql_fetch_array($res))
	{
		if ($row['total'] != 1)
		{
			return "-10:Wrong championship parameters";
		}
	}

	$update = "UPDATE jb_championnat SET actif=".$actif." WHERE id=".$idc.";";
	if ($debug) $log .= $update."\n";
	$res = mysql_query($update);

	DeleteCache($idc);
	if ($debug) $log .= "Cache clean\n";

	$log .= $idc.":Updated";

    return $log;
}


function UpdateMatch($options) {

	global $json, $entities_autorised, $debug;

	$idc = 0;
	$log = "";
	$tmp = $json->decode($options);

	if (isset($tmp->entity) && strlen(trim($tmp->entity)) > 3 && isset($entities_autorised[$tmp->entity]))
		$entity = trim($tmp->entity);
	else
		return "-1:Entity not valid";

	if (isset($tmp->idc))
		$idc = trim($tmp->idc);
	else
		return "-2:Id championship is mandatory";

	if (isset($tmp->login) && strlen(trim($tmp->login)) > 3)
		$login = trim($tmp->login);
	else
		return "-3:Invalid login";

	if (isset($tmp->pwd) && strlen(trim($tmp->pwd)) > 3)
		$pwd = trim($tmp->pwd);
	else
		return "-4:Invalid password";

	if (isset($tmp->idm))
		$idm = trim($tmp->idm);
	else
		return "-5:Id match is mandatory";

	// Tester si parametres coherents
	$req = "SELECT COUNT(*) total FROM jb_championnat WHERE id=".$idc." AND entity='".$entity."' AND login='".$login."' AND pwd='".$pwd."'";
	if ($debug) $log .= $req."\n";
	$res = mysql_query($req);
	if ($row = mysql_fetch_array($res))
	{
		if ($row['total'] != 1)
		{
			return "-10:Wrong championship parameters";
		}
	}

	$req = "SELECT COUNT(*) total FROM jb_saisons s, jb_matchs m WHERE s.id_champ=".$idc." AND s.id=m.id_champ AND m.id=".$idm."";
	if ($debug) $log .= $req."\n";
	$res = mysql_query($req);
	if ($row = mysql_fetch_array($res))
	{
		if ($row['total'] != 1)
		{
			return "-11:Wrong championship parameters";
		}
	}

	$play_date    = isset($tmp->play_date)    ? $tmp->play_date    : "";
	$play_time    = isset($tmp->play_time)    ? $tmp->play_time    : "";
	$penaltys     = isset($tmp->penaltys)     ? $tmp->penaltys     : "";
	$prolongation = isset($tmp->prolongation) ? $tmp->prolongation : 0;
	$match_joue   = isset($tmp->match_joue)   ? $tmp->match_joue   : 0;
	$resultat     = isset($tmp->resultat)     ? $tmp->resultat     : "";
	$nbset        = isset($tmp->nbset)        ? $tmp->nbset        : 0;
	$fanny        = 0;

	if ($resultat != -1 && $resultat != -2)
		$fanny = ($nbset == 1 && ($resultat == '7/0' || $resultat == '0/7')) ? 1 : 0;

	$update = "UPDATE jb_matchs SET play_date='".$play_date."', play_time='".$play_time."', penaltys='".$penaltys."', prolongation=".$prolongation.", match_joue=".$match_joue.", resultat='".$resultat."', fanny=".$fanny.", nbset=".$nbset." WHERE id=".$idm.";";

	if ($debug) $log .= $update."\n";
	$res = mysql_query($update);

	DeleteCache($idc);
	if ($debug) $log .= "Cache clean\n";

	$log .= $idc.":Updated";

    return $log;
}

function DeleteCache($idc) {
	JKCache::delCache("../cache/tdb_home.txt", "_FLUX_TDB_HOME_");
	JKCache::delCache("../cache/most_active_home.txt", "_FLUX_MOST_ACTIVE_");
	JKCache::delCache("../cache/access_home.txt", "_FLUX_ACCESS_");
	JKCache::delCache("../cache/last_created_home.txt", "_FLUX_LAST_CREATED_");
}

// Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

?>