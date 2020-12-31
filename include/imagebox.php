<?

class ImageBox
{

/* Redimentionne une image (gif/jpg) en jpg avec égalisation de la hauteur et de la largeur
 */
function resizeToFile($sourcefile, $targetfile, $jpegqual = 100, $max_cote = 9999, $min_cote = -1)
{
	return ImageBox::imageSquareResize($sourcefile, $targetfile, $jpegqual, $max_cote, $min_cote);
}
function thumbImageSquareResize($sourcefile, $targetfile, $jpegqual = 100, $new_cote)
{
	$filename = basename($targetfile);
	$props = explode('.', $filename);
	$fichier   = $props[0];
	$extention = $props[1];

	if (strcasecmp($extention, "png") == 0) {
		ImageBox::mysquareresize($sourcefile, $new_cote, $targetfile);
		return $targetfile;
	}

	if (!(strcasecmp($extention, "gif") == 0 || strcasecmp($extention, "jpg") == 0))
	{
		copy($sourcefile, $targetfile);
		return;
	}

	// Conversion gif->jpg
	if (strcasecmp($extention, "gif") == 0)
	{
		$source_id = imageCreateFromGIF("$sourcefile");

		$dir = dirname($targetfile);
		$fic = basename($targetfile);
		$items = explode('.', $fic);
		$targetfile = $dir."/".$items[0].".jpg";
	}
	else
		$source_id = imageCreateFromJPEG("$sourcefile");

	// Get the dimensions of the source picture
	$picsize=getimagesize("$sourcefile");
	$source_x  = $picsize[0];
	$source_y  = $picsize[1];
	if ($source_x > $source_y)
	{
		$cote = $source_y;
		$dx = floor(($source_x - $source_y) / 2);
		$dy = 0;
	}
	else
	{
		$cote = $source_x;
		$dx = 0;
		$dy = floor(($source_y - $source_x) / 2);
	}

	// Nouveau canvas de l'image
	$img  = imagecreatetruecolor($cote, $cote);
	$white = imagecolorallocate($img, 255, 255, 255);
	imagefill($img, 0, 0, $white);
	imagecopy($img, $source_id, 0, 0, $dx, $dy, $source_x, $source_y);

	// Create image JGP
	$img2  = imagecreatetruecolor($new_cote, $new_cote);
	imagecopyresampled($img2, $img, 0, 0, 0, 0, $new_cote, $new_cote, $cote, $cote);
	imagejpeg($img2, "$targetfile", $jpegqual);

	return $targetfile;
}
function imageSquareResize($sourcefile, $targetfile, $jpegqual = 100, $max_cote = 9999, $min_cote = -1)
{
	$filename = basename($targetfile);
	$props = explode('.', $filename);
	$fichier   = $props[0];
	$extention = $props[1];

	if (!(strcasecmp($extention, "gif") == 0 || strcasecmp($extention, "jpg") == 0))
	{
		copy($sourcefile, $targetfile);
		return;
	}

	// Conversion gif->jpg
	if (strcasecmp($extention, "gif") == 0)
	{
		$source_id = imageCreateFromGIF("$sourcefile");

		// Pb création du au format de l'image
		if (!$source_id)
		{
	       $source_id = imagecreate (150, 30); /* Création d'une image vide */
	       $bgc = imagecolorallocate ($source_id, 255, 255, 255);
	       $tc = imagecolorallocate ($source_id, 0, 0, 0);
	       imagefilledrectangle ($source_id, 0, 0, 150, 30, $bgc);
	       /* Affichage d'un message d'erreur */
	       imagestring ($source_id, 1, 5, 5, "Erreur au chargement de l'image $imgname", $tc);
		}

		$dir = dirname($targetfile);
		$fic = basename($targetfile);
		$items = explode('.', $fic);
		$targetfile = $dir."/".$items[0].".jpg";
	}
	else
		$source_id = imageCreateFromJPEG("$sourcefile");

	// Get the dimensions of the source picture
	$picsize=getimagesize("$sourcefile");
	$source_x  = $picsize[0];
	$source_y  = $picsize[1];
	if ($source_x > $source_y)
	{
		$dx = 0;
		$dy = floor(($source_x - $source_y) / 2);
		$cote = $source_x;
	}
	else
	{
		$dx = floor(($source_y - $source_x) / 2);
		$dy = 0;
		$cote = $source_y;
	}

	// Nouveau canvas de l'image
	$img  = imagecreatetruecolor($cote, $cote);
	$white = imagecolorallocate($img, 255, 255, 255);
	imagefill($img, 0, 0, $white);
	imagecopy($img, $source_id, $dx, $dy, 0, 0, $source_x, $source_y);

	// Create image JGP
	if ($cote < $min_cote)
	{
		$img2  = imagecreatetruecolor($min_cote, $min_cote);
		imagecopyresized($img2, $img, 0, 0, 0, 0, $min_cote, $min_cote, $cote, $cote);
		imagejpeg($img2, "$targetfile", $jpegqual);
	}
	else if ($cote > $max_cote)
	{
		$img2  = imagecreatetruecolor($max_cote, $max_cote);
		imagecopyresized($img2, $img, 0, 0, 0, 0, $max_cote, $max_cote, $cote, $cote);
		imagejpeg($img2, "$targetfile", $jpegqual);
	}
	else
		imagejpeg($img, "$targetfile", $jpegqual);

	return $targetfile;
}

function imageWidthResize($sourcefile, $targetfile, $jpegqual = 100, $max)
{
	$jpegqual = 80;
	$filename = basename($targetfile);
	$props = explode('.', $filename);
	$fichier   = $props[0];
	$extention = $props[1];

	if (!(strcasecmp($extention, "gif") == 0 || strcasecmp($extention, "jpg") == 0))
	{
		copy($sourcefile, $targetfile);
		return;
	}

	// Conversion gif->jpg
	if (strcasecmp($extention, "gif") == 0)
	{
		$source_id = imageCreateFromGIF("$sourcefile");

		$dir = dirname($targetfile);
		$fic = basename($targetfile);
		$items = explode('.', $fic);
		$targetfile = $dir."/".$items[0].".jpg";
	}
	else
		$source_id = imageCreateFromJPEG("$sourcefile");

	// Get the dimensions of the source picture
	$picsize=getimagesize("$sourcefile");
	$source_x  = $picsize[0];
	$source_y  = $picsize[1];

	$new_h = $source_y;
	$new_w = $source_x;

	if ($source_x > $max)
	{
	       $r = $source_y/$source_x;
	       $new_h = ($source_y > $source_x) ? $max : $max*$r;
	       $new_w = $new_h/$r;
	}
	// note TrueColor does 256 and not.. 8
	$img = ImageCreateTrueColor($new_w, $new_h);
	ImageCopyResized($img, $source_id, 0,0,0,0, $new_w, $new_h, $source_x, $source_y);
	imagejpeg($img, "$targetfile", $jpegqual);

	return $targetfile;
}


function mysquareresize($img, $s, $newfilename) {

	//Check if GD extension is loaded
	if (!extension_loaded('gd') && !extension_loaded('gd2')) { return false; }


	//Get Image size info
	$imgInfo = getimagesize($img);
	$source_x  = $imgInfo[0];
	$source_y  = $imgInfo[1];
	switch ($imgInfo[2]) {
		case 1: $im = imagecreatefromgif($img); break;
		case 2: $im = imagecreatefromjpeg($img);  break;
		case 3: $im = imagecreatefrompng($img); break;
		default: break;
	}

	// ////////////////////////////////////////////////////////
	// CENTRAGE DE L IMAGE AVEC CANVAS RESIZE POUR AVOIR UN CARRE

	if ($source_x < $source_y)
	{
		$dx = 0;  $dy = floor(($source_x - $source_y) / 2);  $ds = $source_x;
	}
	else
	{
		$dx = floor(($source_y - $source_x) / 2); $dy = 0; $ds = $source_y;
	}

	$newSquareImg = imagecreatetruecolor($ds, $ds);

	/* Check if this image is PNG or GIF, then set if Transparent*/
	if(($imgInfo[2] == 1) OR ($imgInfo[2]==3)){
		imagealphablending($newSquareImg, false);
		imagesavealpha($newSquareImg,true);
		$transparent = imagecolorallocatealpha($newSquareImg, 255, 255, 255, 127);
		imagefilledrectangle($newSquareImg, 0, 0, $ds, $ds, $transparent);
	}

	imagecopy($newSquareImg, $im, $dx, $dy, 0, 0, $source_x, $source_y);

	// ////////////////////////////////////////////////////////
	// REDIMENTIONNEMENT DE L IMAGE

	$newImg = imagecreatetruecolor($s, $s);

	/* Check if this image is PNG or GIF, then set if Transparent*/
	if(($imgInfo[2] == 1) OR ($imgInfo[2]==3)){
		imagealphablending($newImg, false);
		imagesavealpha($newImg,true);
		$transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
		imagefilledrectangle($newImg, 0, 0, $s, $s, $transparent);
	}

	if ($source_x <= $s && $source_y <= $s) {
		imagecopy($newImg, $newSquareImg, round(($s - $ds) / 2), round(($s - $ds) / 2), 0, 0, $ds, $ds);
	} else {
		imagecopyresized($newImg, $newSquareImg, 0, 0, 0, 0, $s, $s, $ds, $ds);
	}

	//Generate the file, and rename it to $newfilename
	switch ($imgInfo[2]) {
		case 1: imagegif($newImg,$newfilename); break;
		case 2: imagejpeg($newImg,$newfilename);  break;
		case 3: imagepng($newImg,$newfilename); break;
		default: break;
	}

	return $newfilename;
}

function myresize($img, $w, $h, $newfilename) {

	//Check if GD extension is loaded
	if (!extension_loaded('gd') && !extension_loaded('gd2')) { return false; }

	//Get Image size info
	$imgInfo = getimagesize($img);
	switch ($imgInfo[2]) {
		case 1: $im = imagecreatefromgif($img); break;
		case 2: $im = imagecreatefromjpeg($img);  break;
		case 3: $im = imagecreatefrompng($img); break;
		default: break;
	}

	//If image dimension is smaller, do not resize
	if ($imgInfo[0] <= $w && $imgInfo[1] <= $h) {
		$nHeight = $imgInfo[1];
		$nWidth  = $imgInfo[0];
	}else{
		//yeah, resize it, but keep it proportional
		if ($w/$imgInfo[0] > $h/$imgInfo[1]) {
			$nWidth  = $w;
			$nHeight = $imgInfo[1]*($w/$imgInfo[0]);
		}else{
			$nWidth  = $imgInfo[0]*($h/$imgInfo[1]);
			$nHeight = $h;
		}
	}
	$nWidth = round($nWidth);
	$nHeight = round($nHeight);

	$newImg = imagecreatetruecolor($nWidth, $nHeight);

	/* Check if this image is PNG or GIF, then set if Transparent*/
	if(($imgInfo[2] == 1) OR ($imgInfo[2]==3)){
		imagealphablending($newImg, false);
		imagesavealpha($newImg,true);
		$transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
		imagefilledrectangle($newImg, 0, 0, $nWidth, $nHeight, $transparent);
	}
	imagecopyresampled($newImg, $im, 0, 0, 50, 50, $nWidth, $nHeight, $imgInfo[0], $imgInfo[1]);

	//Generate the file, and rename it to $newfilename
	switch ($imgInfo[2]) {
		case 1: imagegif($newImg,$newfilename); break;
		case 2: imagejpeg($newImg,$newfilename);  break;
		case 3: imagepng($newImg,$newfilename); break;
		default: break;
	}

	return $newfilename;
}

}


