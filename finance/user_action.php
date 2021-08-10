<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isSuperAdmin()) tools::do_redirect("index.php");

foreach(['action', 'item_id', 'f_email', 'f_status', 'token'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

$db = dbc::connect();

if ($action == "del" && isset($item_id) && $item_id != "") {

    $req = "SELECT * FROM users WHERE id=".$item_id;
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {
        $f_email = $row['email'];
        $req = "DELETE FROM users WHERE id=".$item_id;
        $res = dbc::execSql($req);
    }

}

$doublon = false;
if ($action == "new") {

    $req = "SELECT * FROM users WHERE email='".strtolower($f_email)."'";
    $res = dbc::execSql($req);
    if ($row = mysqli_fetch_array($res)) {
        $doublon = true;
        $f_email = $row['email'];
    } else {
        $req = "INSERT INTO users (email, status) VALUES ('".$f_email."', ".$f_status.")";
        $res = dbc::execSql($req);
    }
}

if ($action == "upt" && isset($item_id) && $item_id != "") {

    $req = "SELECT * FROM users WHERE email='".strtolower($f_email)."' AND id <> ".$item_id;
    $res = dbc::execSql($req);
    if ($row = mysqli_fetch_array($res)) {
        $doublon = true;
    } else {
        $req = "SELECT * FROM users WHERE id=".$item_id;
        $res = dbc::execSql($req);

        if ($row = mysqli_fetch_array($res)) {
            $req = "UPDATE users SET email='".$f_email."', status='".$f_status."' WHERE id=".$item_id;
            $res = dbc::execSql($req);
        }
    }
}

if ($action == "confirm" && isset($token) && $token != "") {

    $req = "SELECT * FROM users WHERE token='".$token."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {
        $req = "UPDATE users SET confirmation=1 WHERE token='".$token.'"';
        $res = dbc::execSql($req);
    }

    exit(0);
}

if ($action == "status" && isset($token) && $token != "") {

    $req = "SELECT * FROM users WHERE token='".$token."'";
    $res = dbc::execSql($req);

    if ($row = mysqli_fetch_array($res)) {
        $req = "UPDATE users SET status=0 WHERE token='".$token.'"';
        $res = dbc::execSql($req);
    }
}

?>

<script>
<? if ($action == "del" || $action == "upt" || $action == "new") { ?>

<? if ($doublon) { ?>
	Swal.fire({ title: '', icon: 'error', html: "Utilisateur '<?= $f_email."' déjà existant" ?>" });
<? } else { ?>
	Swal.fire({ title: '', icon: 'success', html: "Utilisateur '<?= $f_email."' ".($action == "new" ? "ajouté": ($action == "upt" ? "modifié" : "supprimé")) ?>" });
<? } ?>
    go({ action: 'home_content', id: 'main', url: 'user_list.php' });

<? } else { ?>

<? if ($action == "confirm") { ?>
	Swal.fire({ title: '', icon: 'success', html: "Utilisateur déactivé" });
<? } else { ?>
	Swal.fire({ title: '', icon: 'success', html: "Email confirmé" });
<? } 
    tools::do_redirect("index.php");

} ?>
</script>