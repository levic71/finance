<?

include "../include/sess_context.php";

session_start();

include "common.php";
include "../include/inc_db.php";

$target_upload = Wrapper::getRequest('target_upload', 'image');
$target_image  = Wrapper::getRequest('target_image',  '');

$multi   = Wrapper::getRequest('multi',   0);
$players = Wrapper::getRequest('players', 0);
$users   = Wrapper::getRequest('users',   0);
$teams   = Wrapper::getRequest('teams',   0);
$tchat   = Wrapper::getRequest('tchat',   0);
$album   = Wrapper::getRequest('album',   0);
$logo    = Wrapper::getRequest('logo',    0);
$result  = '0|erreur';

// Edit upload location here
$destination_path = "../uploads/";
$basename = basename( $_FILES['myfile']['name']);

if ($users == 1)
	$target_path = ToolBox::purgeCaracteresWith("_", $destination_path . "_USER_" . ToolBox::getRand(5) . "_" . $basename);
else if ($players == 1)
	$target_path = ToolBox::purgeCaracteresWith("_", $destination_path . "_JOUEUR_" . $sess_context->getRealChampionnatId() . "_" . $basename);
else if ($teams == 1)
	$target_path = ToolBox::purgeCaracteresWith("_", $destination_path . "_EQUIPE_" . $sess_context->getRealChampionnatId() . "_" . $basename);
else if ($tchat == 1)
	$target_path = ToolBox::purgeCaracteresWith("_", $destination_path . "_TCHAT_" . $sess_context->getRealChampionnatId() . "_" . $basename);
else if ($album == 1)
	$target_path = ToolBox::purgeCaracteresWith("_", $destination_path . "_ALBUM_" . $sess_context->getRealChampionnatId() . "_" . $basename);
else if ($logo == 1)
	$target_path = ToolBox::purgeCaracteresWith("_", $destination_path . "_LOGO_" . $sess_context->getRealChampionnatId() . "_" . $basename);
else
	$target_path = $destination_path . $basename;

$thumb_path = str_replace('uploads', 'thumbs', $target_path);

if ($players == 1 || $users == 1) {
	if(@move_uploaded_file($_FILES['myfile']['tmp_name'], $target_path)) {
		NImageBox::squareresize($target_path, 400, $target_path);
		NImageBox::squareresize($target_path, 45, $thumb_path);
		$result = '1|'.addslashes($target_path).'|'.$target_upload.'|'.$target_image.'|'.$multi;
	}
}
else if ($logo == 1) {
	if(@move_uploaded_file($_FILES['myfile']['tmp_name'], $target_path)) {
		NImageBox::squareresize($target_path, 256, $target_path);
		NImageBox::squareresize($target_path, 45, $thumb_path);
		$result = '1|'.addslashes($target_path).'|'.$target_upload.'|'.$target_image.'|'.$multi;
	}
}
else if ($album == 1) {
	if(@move_uploaded_file($_FILES['myfile']['tmp_name'], $target_path)) {
		NImageBox::squareresize($target_path, 600, $target_path);
		NImageBox::squareresize($target_path, 45, $thumb_path);
		$result = '1|'.addslashes($target_path).'|'.$target_upload.'|'.$target_image.'|'.$multi;
	}
}
else if ($teams == 1 || $tchat == 1) {
	if(@move_uploaded_file($_FILES['myfile']['tmp_name'], $target_path)) {
		NImageBox::squareresize($target_path, 400, $target_path);
		NImageBox::squareresize($target_path, 45, $thumb_path);
		$result = '1|'.addslashes($target_path).'|'.$target_upload.'|'.$target_image.'|'.$multi;
	}
}
else {
	if(@move_uploaded_file($_FILES['myfile']['tmp_name'], $target_path)) {
		$result = '1|'.addslashes($target_path).'|'.$target_upload.'|'.$target_image.'|'.$multi;
	}
}

sleep(1);

?>
<script language="javascript" type="text/javascript">window.top.window.stopUpload('<? echo $result; ?>');</script>
