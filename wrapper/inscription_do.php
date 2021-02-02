<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$modifier = $sess_context->isUserConnected() && $upd == 1 ? true : false;

$confidentialite = Wrapper::getRequest('confidentialite', '');
$sexe      = Wrapper::getRequest('sexe',      '');
$activite  = Wrapper::getRequest('activite',  3);
$morpho    = Wrapper::getRequest('morpho',    1);
$nom       = Wrapper::getRequest('nom',       '');
$prenom    = Wrapper::getRequest('prenom',    '');
<<<<<<< HEAD
$pseudo    = Wrapper::getRequest($modifier ? 'pseudo' : 'login',    ''); // A l'inscrition pseudo=login, modifiable ensuite ...
=======
$pseudo    = Wrapper::getRequest($modifier ? 'pseudo' : 'login', ''); // pseudo = login à la création 
>>>>>>> develop
$taille    = Wrapper::getRequest('taille',    '');
$poids     = Wrapper::getRequest('poids',     '');
$poignet   = Wrapper::getRequest('poignet',   16);
$email     = Wrapper::getRequest('email',     '');
$mobile    = Wrapper::getRequest('mobile',     '');
$ville     = Wrapper::getRequest('ville',     '');
$date_nais = ToolBox::date2mysqldate(Wrapper::getRequest('date_nais', date('d/m/Y')));
$photo     = Wrapper::getRequest('photo',     '');
$login     = Wrapper::getRequest('login',     '');
$pwd       = Wrapper::getRequest('pwd',       '');
$controle  = Wrapper::getRequest('controle',  '');
$del       = Wrapper::getRequest('del',       0);
$upd       = Wrapper::getRequest('upd',       0);

// Attention pas de login ni de pseudo en double

if ($modifier)
{
	$select = "SELECT * FROM jb_users WHERE id=".$sess_context->user['id'];
	$res = dbc::execSQL($select);
	if ($row = mysqli_fetch_array($res))
	{
		// VÃ¯Â¿Å“rification pseudo user unique
		if ($pseudo != $row['pseudo']) {
			$select = "SELECT count(*) total FROM jb_users WHERE pseudo='".$pseudo."'";
			$res = dbc::execSQL($select);
			$row = mysqli_fetch_array($res);
			if ($row['total'] > 0) { echo "-1||Pseudo dÃ¯Â¿Å“jÃ¯Â¿Å“ existant"; exit(0); }
		}

		if ($row['photo'] != "" && $row['photo'] != $photo && file_exists($row['photo'])) unlink($row['photo']);

<<<<<<< HEAD
		$update = "UPDATE jb_users SET mobile='".$mobile."', ville='".$ville."', sexe=".$sexe.", confidentialite=".$confidentialite.", activite=".$activite.", morpho=".$morpho.", taille='".$taille."', poignet=".$poignet.", poids=".$poids.", email='".$email."', date_nais='".$date_nais."', login='".$login."', pwd='".$pwd."', nom='".$nom."', prenom='".$prenom."', photo='".$photo."', pseudo='".$pseudo."' WHERE id=".$sess_context->user['id'];
=======
		$update = "UPDATE jb_users mobile='".$mobile."', ville='".$ville."', sexe=".$sexe.", confidentialite=".$confidentialite.", activite=".$activite.", morpho=".$morpho.", taille='".$taille."', poignet=".$poignet.", poids=".$poids.", email='".$email."', date_nais='".$date_nais."', login='".$login."', pwd='".$pwd."', nom='".$nom."', prenom='".$prenom."', photo='".$photo."', pseudo='".$pseudo."' WHERE id=".$sess_context->user['id'];
>>>>>>> develop
		$res = dbc::execSQL($update);

		$select = "SELECT * FROM jb_users WHERE id=".$sess_context->user['id'];
		$res = dbc::execSQL($select);
		$row = mysqli_fetch_array($res);
		$sess_context->setUserConnection($row);
	}
}
else
{
	// Verification pseudo et login user unique
	$select = "SELECT count(*) total FROM jb_users WHERE pseudo='".$pseudo."'";
	$res = dbc::execSQL($select);
	$row = mysqli_fetch_array($res);
	if ($row['total'] > 0) { echo "-1||Pseudo déjà  existant"; exit(0); }

	$select = "SELECT count(*) total FROM jb_users WHERE login='".$login."'";
	$res = dbc::execSQL($select);
	$row = mysqli_fetch_array($res);
	if ($row['total'] > 0) { echo "-1||Login déjà existant"; exit(0); }

	$insert = "INSERT INTO jb_users (pseudo, email, login, pwd, status, date_inscription) VALUES ('".$pseudo."', '".$email."', '".$login."', '".$pwd."', 1, '".date("Y")."-".date("m")."-".date("d")."');";
	$res = dbc::execSQL($insert);

	$select = "SELECT * FROM jb_users WHERE pseudo='".$pseudo."' AND login='".$login."'";
	$res = dbc::execSQL($select);
	$row = mysqli_fetch_array($res);
	$sess_context->setUserConnection($row);
}

?><span class="hack_ie">_HACK_IE_</span>
<script>
<<<<<<< HEAD
=======
mm({action:'myprofile'});
>>>>>>> develop
go({ action: 'login_panel', id: 'login_panel', url: 'login_panel.php' });
$cMsg({ msg: '<?= $modifier ? "Compte modifié" : "Inscription validée" ?>' });
</script>