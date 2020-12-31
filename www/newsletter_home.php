<?

include "../include/templatebox.php";

$tbox = new TemplateBox();
$tbox->htmlBeginWithKeyPressedAction();

?>
<style type="text/css">
.nospace, body, form {
	background   : #E5E5E5;
}
</style>
<form action="../www/newsletter_do.php" method=post target="_parent">
<input name="email" type="text" size="24" maxlength="32" value="Saisissez votre email"><button onclick="submit();"><img src="../images/templates/defaut/bt_ok.gif" border="0" alt="bouton valider" /></button>
</form>

<? TemplateBox::htmlEnd(false); ?>
