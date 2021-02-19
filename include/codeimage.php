<?

	session_start();

	require_once '../artichow/AntiSpam.class.php';

	if (isset($_SESSION['antispam'])) 
		$antispam = $_SESSION['antispam'];
	else
		$antispam = "ERROR";

	// On créé l'image anti-spam
	$object = new AntiSpam($antispam);

	// La valeur affichée sur l'image aura 5 lettres
	// $object->setRand(5);

	// On assigne un nom à cette image pour vérifier
	// ultérieurement la valeur fournie par l'utilisateur
	$object->save('exemple');

	// On affiche l'image à l'écran
	$object->draw();

?>