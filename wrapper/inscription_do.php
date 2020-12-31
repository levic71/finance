<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";
include "../www/journeebuilder.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$confidentialite = Wrapper::getRequest('confidentialite', '');
$sexe      = Wrapper::getRequest('sexe',      '');
$activite  = Wrapper::getRequest('activite',  3);
$morpho    = Wrapper::getRequest('morpho',    1);
$nom       = Wrapper::getRequest('nom',       '');
$prenom    = Wrapper::getRequest('prenom',    '');
$pseudo    = Wrapper::getRequest('pseudo',    '');
$taille    = Wrapper::getRequest('taille',    '');
$poids     = Wrapper::getRequest('poids',     '');
$poignet   = Wrapper::getRequest('poignet',   16);
$email     = Wrapper::getRequest('email',     '');
$tel       = Wrapper::getRequest('tel',     '');
$mobile    = Wrapper::getRequest('mobile',     '');
$ville     = Wrapper::getRequest('ville',     '');
$date_nais = ToolBox::date2mysqldate(Wrapper::getRequest('date_nais', date('d/m/Y')));
$photo     = Wrapper::getRequest('photo',     '');
$login     = Wrapper::getRequest('login',     '');
$pwd       = Wrapper::getRequest('pwd',       '');
$controle  = Wrapper::getRequest('controle',  '');
$del       = Wrapper::getRequest('del',       0);
$upd       = Wrapper::getRequest('upd',       0);

$modifier = $sess_context->isUserConnected() && $upd == 1 ? true : false;

// Attention pas de login ni de pseudo en double

if ($modifier)
{
	$select = "SELECT * FROM jb_users WHERE id=".$sess_context->user['id'];
	$res = dbc::execSQL($select);
	if ($row = mysqli_fetch_array($res))
	{

		// Vérification pseudo user unique
		if ($pseudo != $row['pseudo']) {
			$select = "SELECT count(*) total FROM jb_users WHERE pseudo='".$pseudo."'";
			$res = dbc::execSQL($select);
			$row = mysqli_fetch_array($res);
			if ($row['total'] > 0) { echo "-1||Pseudo déjà existant"; exit(0); }
		}

		if ($row['photo'] != "" && $row['photo'] != $photo && file_exists($row['photo'])) unlink($row['photo']);

		$update = "UPDATE jb_users SET tel='".$tel."', mobile='".$mobile."', ville='".$ville."', sexe=".$sexe.", confidentialite=".$confidentialite.", activite=".$activite.", morpho=".$morpho.", taille='".$taille."', poignet=".$poignet.", poids=".$poids.", email='".$email."', date_nais='".$date_nais."', login='".$login."', pwd='".$pwd."', nom='".$nom."', prenom='".$prenom."', photo='".$photo."', pseudo='".$pseudo."' WHERE id=".$sess_context->user['id'];
		$res = dbc::execSQL($update);

		$select = "SELECT * FROM jb_users WHERE id=".$sess_context->user['id'];
		$res = dbc::execSQL($select);
		$row = mysqli_fetch_array($res);
		$sess_context->setUserConnection($row);
	}
}
else
{
	// Vérification pseudo et login user unique
	$select = "SELECT count(*) total FROM jb_users WHERE pseudo='".$pseudo."'";
	$res = dbc::execSQL($select);
	$row = mysqli_fetch_array($res);
	if ($row['total'] > 0) { echo "-1||Pseudo déjà existant"; exit(0); }

	$select = "SELECT count(*) total FROM jb_users WHERE login='".$login."'";
	$res = dbc::execSQL($select);
	$row = mysqli_fetch_array($res);
	if ($row['total'] > 0) { echo "-1||Login déjà existant"; exit(0); }

	$insert = "INSERT INTO jb_users (confidentialite, activite, morpho, sexe, taille, poignet, poids, photo, pseudo, nom, prenom, email, tel, mobile, ville, date_nais, login, pwd, status, date_inscription) VALUES (".$confidentialite.", ".$activite.", ".$morpho.", ".$sexe.", ".$taille.", ".$poignet.", ".$poids.", '".$photo."', '".$pseudo."', '".$nom."', '".$prenom."', '".$email."', '".$tel."', '".$mobile."', '".$ville."', '".$date_nais."', '".$login."', '".$pwd."', 1, '".date("Y")."-".date("m")."-".date("d")."');";
	$res = dbc::execSQL($insert);

	$select = "SELECT * FROM jb_users WHERE pseudo='".$pseudo."' AND login='".$login."'";
	$res = dbc::execSQL($select);
	$row = mysqli_fetch_array($res);
	$sess_context->setUserConnection($row);
}

?><span class="hack_ie">_HACK_IE_</span>
<script>
mm({action:'myprofile'});
$cMsg({ msg: 'Inscription <?= $modifier ? "modifiée" : "validée" ?>' });
</script>