<?
function ombre($image)
{
	if(!is_readable($image))
	{
		die("image introuvable");
	}

	$taille = GetImageSize($image);
	$html = "<table border='0' cellpadding='0' cellspacing='0'>
			<tr>
				<td rowspan='2' colspan='2'><img src='$image'></td>
				<td><img src='ombre_1.gif'></td>
			</tr>
			<tr>
				<td><img src='ombre_2.gif' height='".($taille[1]-8)."' width='6'></td>
			</tr>
			<tr>
				<td><img src='ombre_3.gif'></td>
				<td><img src='ombre_4.gif' height='8' width='".($taille[0]-7)."'></td>
				<td><img src='ombre_5.gif'></td>
			</tr>
		</table>";

	return $html;
}



?>

