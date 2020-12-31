<markers>
<?
include "../include/constantes.php";

while(list($cle, $val) = each($partenaire))
{
	echo "
<marker
	lat=\"".$val['lat']."\"
	lng=\"".$val['lng']."\"
	nom=\"".$val['nom']."\"
	ville=\"".$val['ville']."\"
	cp=\"".$val['cp']."\"
	adresse=\"".$val['adresse']."\"
	tel=\"".$val['tel']."\"
	email=\"".$val['email']."\"
	web=\"".$val['web']."\"
	icon=\"".$val['icon']."\"
	plan=\"".$val['plan']."\"
	config=\"".$val['config']."\"
/>
	";
}

?>
</markers> 
