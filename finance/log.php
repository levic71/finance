<?

require_once "sess_context.php";

session_start();

include "common.php";

if (!$sess_context->isSuperAdmin()) tools::do_redirect("index.php");

$nb_lignes = 200;

foreach(['nb_lignes'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

if (!is_dir("cache/")) mkdir("cache/");

?>

<div class="ui container inverted segment">
    <pre style="width: 100%; height: 500px; overflow: scroll;">

<? echo shell_exec( 'tail -n '.$nb_lignes.' ./finance.log'); ?>

    </pre>
</div>

