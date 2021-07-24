<?

include_once "include.php";

$nb_lignes = 40;

foreach(['nb_lignes'] as $key)
    $$key = isset($_POST[$key]) ? $_POST[$key] : (isset($$key) ? $$key : "");

if (!is_dir("cache/")) mkdir("cache/");

?>

<pre style="width: 100%; height: 500px; overflow: scroll;"><?php

echo shell_exec( 'tail -n '.$nb_lignes.' ./finance.log');

?></pre>
