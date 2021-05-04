<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/journeebuilder.php";

if (!$sess_context->isAdmin()) ToolBox::do_redirect("grid.php");

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$link_to_player = Wrapper::getRequest('link_to_player', '0');
$idu = Wrapper::getRequest('idu', '0');

$auto_create_team = Wrapper::getRequest('auto_create_team', '2');
$nom       = Wrapper::getRequest('nom',       '');
$prenom    = Wrapper::getRequest('prenom',    1);
$date_nais = ToolBox::date2mysqldate(Wrapper::getRequest('date_nais', date('d/m/Y')));
$pseudo    = Wrapper::getRequest('pseudo',    '');
$email     = Wrapper::getRequest('email',     '');
$tel1      = Wrapper::getRequest('tel1',      '');
$tel2      = Wrapper::getRequest('tel2',      '');
$photo     = Wrapper::getRequest('photo',     '');
$presence  = Wrapper::getRequest('presence',  '1');
$sexe      = Wrapper::getRequest('sexe',      '0');
$etat      = Wrapper::getRequest('etat',      '0');
$del       = Wrapper::getRequest('del',       0);
$idp       = Wrapper::getRequest('idp',       0);
$upd       = Wrapper::getRequest('upd',       0);
$old_pseudo = Wrapper::getRequest('old_pseudo',    '');

if (($upd == 1 || $del == 1) && !is_numeric($idp)) ToolBox::do_redirect("grid.php");

if ($link_to_player == 1 && !is_numeric($idu)) ToolBox::do_redirect("grid.php");
if ($link_to_player == 1) {
	$sql = "SELECT * FROM jb_users WHERE id=".$idu;
	$res = dbc::execSQL($sql);
	$row = mysqli_fetch_array($res);
	$nom    = $row['nom'];
	$prenom = $row['prenom'];
	$pseudo = $row['pseudo'];
	$date_nais = $row['date_nais'];
	$email = $row['email'];
	$tel1 = $row['tel'];
	$tel2 = $row['mobile'];
	$photo = $row['photo'];
	$sexe = $row['sexe'];
}

if ($del == 1)
{
	$err = true;
	$sql = "SELECT count(*) total FROM jb_joueurs WHERE id=".$idp." AND id_champ=".$sess_context->getRealChampionnatId();
	$res = dbc::execSQL($sql);
	$row = mysqli_fetch_array($res);
	if ($row['total'] == 1) {

		// Récupération des infos de l'ancienne image pour la supprimer
		$sjs = new SQLJoueursServices($sess_context->getRealChampionnatId());
		$joueur = $sjs->getJoueur($idp);
		if ($joueur['photo'] != "" && file_exists($joueur['photo'])) unlink($joueur['photo']);

		// On détruit les équipes de 2 dont ce joueur fait parti pour les championnats libres seulement
		if ($sess_context->isFreeXDisplay())
		{
			$delete = "DELETE FROM jb_equipes WHERE nb_joueurs=2 AND (joueurs LIKE '%|".$idp."' OR joueurs LIKE '".$idp."|%') AND id_champ=".$sess_context->getRealChampionnatId();
			$res = dbc::execSQL($delete);
		}

		// On met à jour les équipes de + de 2 qui contiennent ce joueurs
		$req = "SELECT * FROM jb_equipes WHERE nb_joueurs > ".($sess_context->isFreeXDisplay() ? 2 : 0)." AND (joueurs LIKE '%|".$idp."' OR joueurs LIKE '".$idp."|%' OR joueurs LIKE '%|".$idp."|%') AND id_champ=".$sess_context->getRealChampionnatId();
		$res = dbc::execSQL($req);
		while($row = mysqli_fetch_array($res))
		{
			$tab = explode('|', $row['joueurs']);
			$selection = "";
			foreach($tab as $item)
			{
				if ($item != $idp)
				{
					$selection .= ($selection == "" ? "" : "|").$item;
				}
			}
			$update = "UPDATE jb_equipes SET joueurs='".$selection."', nb_joueurs=".($row['nb_joueurs']-1)." WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$row['id'];
			$res2 = dbc::execSQL($update);
		}

		// Suppression du lien avec joueur inscrit si besoin
		$delete = "DELETE FROM jb_user_player WHERE id_player=".$idp." AND id_champ=".$sess_context->getRealChampionnatId();
		$res = dbc::execSQL($delete);

		// Suppression du joueur
		$delete = "DELETE FROM jb_joueurs WHERE id=".$idp." AND id_champ=".$sess_context->getRealChampionnatId();
		$res = dbc::execSQL($delete);

		$err = false;
	}

?><span class="hack_ie">_HACK_IE_</span><script>mm({action:'players'}); $<?= $err ? "dMsg" : "cMsg" ?>({ msg: 'Joueur <?= $err ? "non" : "" ?> supprimé' });</script><?
	exit(0);
}



function getPlayersNameForInsert($championnat, $regulier)
{
	$req = "SELECT * FROM jb_joueurs WHERE id_champ=".$championnat.($regulier == 1 ? " AND presence=1;" : "");
	$res = dbc::execSQL($req);

	if ($res)
	{
		while($row = mysqli_fetch_array($res))
			$players_name[$row['id']] = strlen($row['pseudo']) > 0 ? $row['pseudo'] : $row['nom']." ".$row['prenom'];
	}

	mysqli_free_result($res);

	return $players_name;
}


function getPlayersNameForUpdate($championnat, &$last_id_insert)
{
    global $nom, $prenom, $pseudo, $presence;

	$req = "SELECT * FROM jb_joueurs WHERE id_champ=".$championnat;
	$res = dbc::execSQL($req);

	if ($res)
	{
		while($row = mysqli_fetch_array($res))
		{
			$players_name[$row['id']] = strlen($row['pseudo']) > 0 ? $row['pseudo'] : $row['nom']." ".$row['prenom'];
            if ($row['nom'] == $nom && $row['prenom'] == $prenom && $row['pseudo'] == $pseudo && $row['presence'] == $presence)
               $last_id_insert = $row['id'];
		}
	}

	mysqli_free_result($res);

	return $players_name;
}


$modifier = $upd == 1 ? true : false;


if ($modifier)
{
	if ($link_to_player == 1)
	{
		// Création du lien avec le joueur déjà inscrit
		$sql = "INSERT INTO jb_user_player (id_champ, id_user, id_player, status, date_request) VALUES (".$sess_context->getRealChampionnatId().", ".$idu.", ".$idp.", 1, NOW());";
		$res = dbc::execSQL($sql);
	}

	// Récupération des infos de l'ancienne image pour la supprimer
	$sjs = new SQLJoueursServices($sess_context->getRealChampionnatId());
	$joueur = $sjs->getJoueur($idp);
	if ($joueur['photo'] != "" && $joueur['photo'] != $photo && file_exists($joueur['photo'])) unlink($joueur['photo']);

	// Modification du joueur
	$update = "UPDATE jb_joueurs SET sexe=".$sexe.", etat=".$etat.", nom='".$nom."', prenom='".$prenom."', dt_naissance='".$date_nais."', pseudo='".$pseudo."', photo='".$photo."', presence=".$presence.", email='".$email."', tel1='".$tel1."', tel2='".$tel2."' WHERE id_champ=".$sess_context->getRealChampionnatId()." AND id=".$idp;
	$res = dbc::execSQL($update);

	if ($sess_context->isFreeXDisplay()) {
		// On récupère les noms+id des joueurs
		$players_name = getPlayersNameForUpdate($sess_context->getRealChampionnatId(), $last_id_insert);

		// Si le pseudo du joueur change, il faut changer le pseudo des équipes
		if ($old_pseudo != $pseudo)
		{
			$select = "SELECT * FROM jb_equipes WHERE (joueurs LIKE '".$idp."|%' AND nom LIKE '".$old_pseudo."-%') OR (joueurs LIKE '%|".$idp."' AND nom LIKE '%-".$old_pseudo."')";
			$res = dbc::execSQL($select);
			while($row = mysqli_fetch_array($res))
			{
				$item = explode('|', $row['joueurs']);
				$defenseur = $item[0];
				$attaquant = $item[1];

				// On ne change que si le nom de l'équipe n'a pas été créé automatiquement. S'il y a eu personnalisation du
				// nom de l'équipe, alors on garde l'ancien nom
				if ($defenseur == $idp && $row['nom'] == $old_pseudo."-".$players_name[$attaquant])
				{
					$update = "UPDATE jb_equipes set nom='".$pseudo."-".$players_name[$attaquant]."' WHERE id=".$row['id']." AND id_champ=".$sess_context->getRealChampionnatId();
					$res2 = dbc::execSQL($update);
				}
				if ($attaquant == $idp && $row['nom'] == $players_name[$defenseur]."-".$old_pseudo)
				{
					$update = "UPDATE jb_equipes set nom='".$players_name[$defenseur]."-".$pseudo."' WHERE id=".$row['id']." AND id_champ=".$sess_context->getRealChampionnatId();
					$res3 = dbc::execSQL($update);
				}
			}
		}
	}
}
else
{
	$ses = new SQLEquipesServices($sess_context->getRealChampionnatId());

	// Insertion du nouveau joueurs
	$insert = "INSERT INTO jb_joueurs (id_champ, nom, prenom, sexe, dt_naissance, pseudo, photo, presence, email, tel1, tel2, etat) VALUES (".$sess_context->getRealChampionnatId().", '".$nom."', '".$prenom."', ".$sexe.", '".$date_nais."', '".$pseudo."', '".$photo."', ".$presence.", '".$email."', '".$tel1."', '".$tel2."', ".$etat.");";
	$res = dbc::execSQL($insert);

	// Récuparation de l'id du joueur
	$sjs = new SQLJoueursServices($sess_context->getRealChampionnatId());
	$joueur = $sjs->getJoueurByPseudo($pseudo);
	$last_id_insert = $joueur['id'];

	// Mise à jour de la saison
	$sss = new SQLSaisonsServices($sess_context->getRealChampionnatId(), $sess_context->getChampionnatId());
	$saison = $sss->getSaison();
	$saison['joueurs'] .= ($saison['joueurs'] == "" ? "" : ",").$joueur['id'];
	$update = "UPDATE jb_saisons SET joueurs='".SQLServices::cleanIN($saison['joueurs'])."' WHERE id=".$saison['id'];
	$res = dbc::execSQL($update);

	// Création du lien avec le joueur déjà inscrit
	if ($link_to_player == 1) {
		$sql = "INSERT INTO jb_user_player (id_champ, id_user, id_player, status, date_request) VALUES (".$sess_context->getRealChampionnatId().", ".$idu.", ".$joueur['id'].", 1, NOW());";
		$res = dbc::execSQL($sql);
	}

	// Création des équipes si souhaité
	if ($auto_create_team != 2)
	{
		// On récupère les noms+id des joueurs
		$players_name = getPlayersNameForInsert($sess_context->getRealChampionnatId(), $auto_create_team);

		// On créé automatiquement toutes les équipes avec ce nouveau joueurs
		foreachch($players_name as $cle => $match)
		{
			if ($cle != $last_id_insert)
			{
				$ses->checkTeam($players_name, $cle, $last_id_insert);
				$ses->checkTeam($players_name, $last_id_insert, $cle);
			}
		}
	}
}

JKCache::delCache("../cache/stats_champ_".$sess_context->getRealChampionnatId()."_".$sess_context->getChampionnatId().".txt", "_FLUX_STATS_CHAMP_");

?><span class="hack_ie"><span class="hack_ie">_HACK_IE_</span></span><script>mm({action:'players'}); $cMsg({ msg: 'Joueur <?= $modifier ? "modifié" : "ajouté" ?>' });</script>