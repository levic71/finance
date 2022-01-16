<?

require_once "sess_context.php";

session_start();

include "common.php";

foreach(['action', 'f_email', 'f_pwd'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

$doublon    = false;
$en_attente = false;
$connected  = false;

if ($action == "logout") {
    $sess_context->resetUserConnection();
    tools::do_redirect("index.php");
}

if ($action == "login") {

    $req = "SELECT * FROM users WHERE email='".strtolower($f_email)."'";
    $res = dbc::execSql($req);
    if ($row = mysqli_fetch_array($res)) {
        if (password_verify($f_pwd, $row['pwd'])) {
            if ($row['status'] == 1 && $row['confirmation'] == 1) {
                $connected = true;
                $tab = [ "id" => $row['id'], "email" => $row['email'], "favoris" => $row['favoris'], "super_admin" => $row['super_admin'], "status" => $row['status'] ];
                $sess_context->setUserConnection($tab);
            }
            else
                $en_attente = true;
        }
    }

    $req = "INSERT INTO connexions (email, ip, status) VALUES ('".strtolower($f_email)."', '".$sess_context->getUserIp()."', ".($connected ? 1 : 0).")";
    $res = dbc::execSql($req);
}

if ($action == "signup") {

    $req = "SELECT * FROM users WHERE email='".strtolower($f_email)."'";
    $res = dbc::execSql($req);
    if ($row = mysqli_fetch_array($res))
        $doublon = true;
    else {
        $user_token = bin2hex(random_bytes(30));

        $req = "INSERT INTO users (email, pwd, super_admin, status, token) VALUES ('".strtolower($f_email)."', '".password_hash($f_pwd, PASSWORD_DEFAULT)."', 0, 1, '".$user_token."')";
        $res = dbc::execSql($req);

        $mail_sender = "contact@jorkers.com";
        $mail_header = "From: ".$mail_sender."\n";
        $mail_header.= "MIME-Version: 1.0\n";
        $mail_header.= "X-Sender: <".$mail_sender.">\n";
        $mail_header.= "X-Mailer: PHP/".phpversion()."\n";
        $mail_header.= "X-Priority: 1\n";
        $mail_header.= "Return-Path: <no-reply@jorkers.com>\n";
        $mail_header.= "Content-Type: text/html; charset=".sess_context::mail_charset."\n";

        $mail_sujet  = "[finance.jorkers.com] - Confirmation email";
        $mail_corps  = "Bonjour,\n\nPour valider votre email, cliquez sur ce <a href=\"https://finance.jorkers.com/user_action.php?action=confirm&token=".$user_token."\">lien</a>.\n\nBonne réception.\n";
        $res = @mail($f_email, stripslashes($mail_sujet), nl2br($mail_corps), $mail_header);

        $mail_sujet  = "[finance.jorkers.com] - Demande de création compte";
        $mail_corps  = "Bonjour,\n\nPour invalider le compte, cliquez sur ce <a href=\"https://finance.jorkers.com/user_action.php?action=status&token=".$user_token."\">lien</a>.\n\nBonne réception.\n";
        $res = @mail(sess_context::email_admin, stripslashes($mail_sujet), nl2br($mail_corps), $mail_header);
    }
}

if ($action == "upt" && isset($strategie_id) && $strategie_id != "") {

    // Recuperation des infos des assets
    $req = "UPDATE strategies SET title='".$f_name."', data='".$data."', methode='".$f_methode."' WHERE id=".$strategie_id;
    $res = dbc::execSql($req);

}

if ($action == "del" && isset($strategie_id) && $strategie_id != "") {

    $req = "DELETE FROM strategies WHERE id=".$strategie_id;
    $res = dbc::execSql($req);
    $row = mysqli_fetch_array($res);

}

?>

<?  if ($action == "signup") { ?>
        <script>
        Swal.fire({ title: '', icon: '<?= $doublon ? "error" : "success" ?>', html: "<?= $action == "signup" ? ($doublon ? "Email déjà enregistré !" : "Demande d'inscription enregistrée") : ($action == "upt" ? "Profil utilisateur modifié" : "Utilisateur supprimé") ?>" });
        go({ action: 'home', id: 'main', url: '<?= $doublon ? "login.php" : "home_content.php" ?>' });
        </script>
<?  } else if ($action == "upt") { ?>
<?  } else {
        if (!$connected) { ?> 
            <script>
    	    Swal.fire({ title: 'Connexion NOK', icon: 'error', html: '<?= $en_attente ? "Compte en attente de validation" : "Email ou mot de passe invalide !" ?>' });
            go({ action: 'home', id: 'main', url: 'login.php?f_email=<?= $f_email ?>' });
            </script>
<?      } else {
            tools::do_redirect("index.php");
        }
    }
?>