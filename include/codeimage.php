<?

	session_start();

	require_once '../artichow/AntiSpam.class.php';

	if (isset($_SESSION['antispam'])) 
		$antispam = $_SESSION['antispam'];
	else
		$antispam = "ERROR";

	// On cr�� l'image anti-spam
	$object = new AntiSpam($antispam);

	// La valeur affich�e sur l'image aura 5 lettres
	// $object->setRand(5);

	// On assigne un nom � cette image pour v�rifier
	// ult�rieurement la valeur fournie par l'utilisateur
	$object->save('exemple');

	// On affiche l'image � l'�cran
	$object->draw();

?>