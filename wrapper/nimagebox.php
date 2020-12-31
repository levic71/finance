<?

class NImageBox
{

function squareresize($img, $s, $newfilename) {

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

function resize($img, $w, $h, $newfilename) {

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


