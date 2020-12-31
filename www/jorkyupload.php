<?

if (isset($_FILES['userfile']['tmp_name']))
{
	copy($_FILES['userfile']['tmp_name'], $_FILES['userfile']['name']);
}

?>

<HTML>
<BODY BGCOLOR=#DDDDDD>

<FORM ENCTYPE="multipart/form-data" ACTION="jorkyupload.php" METHOD="POST">
<INPUT TYPE="hidden" name="MAX_FILE_SIZE" value="2000000">

<TABLE BORDER=0 WIDTH=100% HEIGHT=100%>
<TR VALIGN=CENTER><TD ALIGN=CENTER><TABLE BORDER=0 HEIGHT=100%>
<TR><TD>Envoyez ce fichier : </TD>
    <TD><INPUT NAME="userfile" TYPE="file"></TD>
	<TD><INPUT TYPE="submit" VALUE="Send File"></TD>
</TABLE></TD>
</TABLE>

</FORM>

</BODY>
</HTML>