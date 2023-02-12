<?php

// $db = mysqli_connect("127.0.0.1", "root", "root", "finance") or die("Error connexion db" . mysqli_connect_errno() . ' ; ' . mysqli_connect_error());

// $db= mysqli_connect("jorkersfinance.mysql.db", "jorkersfinance", "Rnvubwi2021", "jorkersfinance") or die("Error connexion db" . mysqli_connect_errno() . ' ; ' . mysqli_connect_error());

// $db->set_charset("utf8");


// $req = "SELECT *, s.symbol symbol FROM stocks s LEFT JOIN quotes q ON s.symbol = q.symbol LEFT JOIN trend_following t ON s.symbol = t.symbol AND t.user_id=".$sess_context->getUserId()." WHERE s.symbol='" . $symbol . "'";
// $res = mysqli_query($db, $req) or die("Error on request : " . $req);
    

try {
    $db_name     = 'finance';
    $db_user     = 'root';
    $db_password = 'root';
    $db_host     = 'localhost';

    $pdo = new PDO('mysql:host=localhost;dbname= finance ','root','root');;
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);               
		
} catch (PDOException $ex) {
    echo $ex->getMessage(); 
}
?>