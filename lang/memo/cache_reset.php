<HTML>
<BODY>

<CENTER>


<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=400>

<?

if (!isset($reset)) $reset = 0;

$dir = "../cache/";

// Ouvre un dossier bien connu, et liste tous les fichiers
if (is_dir($dir))
{
   if ($dh = opendir($dir))
   {
       while (($file = readdir($dh)) !== false)
       {
           if (is_file($dir.$file))
           {
				$fp = fopen($dir.$file, "r");
				$fstat = fstat($fp);
				fclose($fp);
				echo "<tr><td>".$file." </td><td align=right>".$fstat['size']." octets</td>";
				if ($reset == 1)
				{
					unlink($dir.$file);
					echo "<tr><td>vidé</td>";
				}
			}
       }
       closedir($dh);
   }
}

?> 

</TABLE>


<FORM ACTION=cache_reset.php METHOD=POST>
<INPUT TYPE=HIDDEN NAME=reset VALUE=0>

<INPUT TYPE=SUBMIT VALUE="lister">

</FORM>

<FORM ACTION=cache_reset.php METHOD=POST>
<INPUT TYPE=HIDDEN NAME=reset VALUE=1>

<INPUT TYPE=SUBMIT VALUE="vider">

</FORM>

</CENTER>
</BODY>
</HTML>