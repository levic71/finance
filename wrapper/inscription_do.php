<?

require_once "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$db = dbc::connect();

$modifier = $sess_context->isUserConnected() && $upd == 1 ? true : false;

$confidentialite = Wrapper::getRequest('confidentialite', 0);
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
$tel       = Wrapper::getRequest('tel',       '');
$mobile    = Wrapper::getRequest('mobile',    '');
$ville     = Wrapper::getRequest('ville',     '');
$date_nais = ToolBox::date2mysqldate(Wrapper::getRequest('date_nais', date('d/m/Y')));
$photo     = Wrapper::getRequest('photo',     '');
$pwd       = Wrapper::getRequest('pwd',       '');
$controle  = Wrapper::getRequest('controle',  '');
$del       = Wrapper::getRequest('del',       0);
$upd       = Wrapper::getRequest('upd',       0);

if ($modifier)
{
	$select = "SELECT * FROM jb_users WHERE removed=0 AND id=".$sess_context->user['id'];
	$res = dbc::execSQL($select);
	if ($row = mysqli_fetch_array($res))
	{
		// Vérification email unique
		if (strtolower($email) != strtolower($row['email'])) {
			$select = "SELECT count(*) total FROM jb_users WHERE lower(email)='".strtolower($email)."'";
			$res2 = dbc::execSQL($select);
			$row2 = mysqli_fetch_array($res);
			if ($row2['total'] > 0) { echo "-1||Email déjà utilisé"; exit(0); }
		}

		if ($row['photo'] != "" && $row['photo'] != $photo && file_exists($row['photo'])) unlink($row['photo']);

		$update = "UPDATE jb_users SET mobile='".$mobile."', ville='".$ville."', sexe=".$sexe.", confidentialite=".$confidentialite.", activite=".$activite.", morpho=".$morpho.", taille='".$taille."', poignet=".$poignet.", poids=".$poids.", email='".strtolower($email)."', date_nais='".$date_nais."', pwd='".$pwd."', nom='".$nom."', prenom='".$prenom."', photo='".$photo."', pseudo='".$pseudo."' WHERE id=".$sess_context->user['id'];
		$res = dbc::execSQL($update);

		$select = "SELECT * FROM jb_users WHERE id=".$sess_context->user['id'];
		$res = dbc::execSQL($select);
		$row = mysqli_fetch_array($res);
		$sess_context->setUserConnection($row);
	}
}
else
{
	// Vérification email user unique
	$select = "SELECT count(*) total FROM jb_users WHERE lower(email)='".strtolower($email)."'";
	$res = dbc::execSQL($select);
	$row = mysqli_fetch_array($res);
	if ($row['total'] > 0) { echo "-1||Email déjà utilisé"; exit(0); }

	$insert = "INSERT INTO jb_users (pseudo, email, pwd, status, date_nais, date_inscription) VALUES ('".$pseudo."', '".strtolower($email)."', '".$pwd."', 1, '".date("Y")."-".date("m")."-".date("d")."', '".date("Y")."-".date("m")."-".date("d")."');";
	$res = dbc::execSQL($insert);

	$select = "SELECT * FROM jb_users WHERE lower(email)='".strtolower($email)."' AND pwd='".$pwd."'";
	$res = dbc::execSQL($select);
	$row = mysqli_fetch_array($res);
	$sess_context->setUserConnection($row);
}

?><span class="hack_ie">_HACK_IE_</span>
<script>
mm({action:'myprofile'});
go({ action: 'login_panel', id: 'login_panel', url: 'login_panel.php' });
$cMsg({ msg: '<?= $modifier ? "Compte modifié" : "Inscription validée" ?>' });
</script>