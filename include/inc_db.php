<?

class dbc
{

public $cfg;
public $num_cfg;
public static $link;

function __construct( )
{
	$this->cfg[1]['host'] = 'sql';
	$this->cfg[1]['db']   = 'lajojofa';
	$this->cfg[1]['user'] = 'lajojofa';
	$this->cfg[1]['pwd']  = 'xh0jYnhl';

	$this->cfg[2]['host'] = 'sql2';
	$this->cfg[2]['db']   = 'lescvdb';
	$this->cfg[2]['user'] = 'lescvlescv';
	$this->cfg[2]['pwd']  = 'P8Ub2R4F';

	$this->cfg[3]['host'] = 'localhost';
	$this->cfg[3]['db']   = 'jorkyball2';
	$this->cfg[3]['user'] = 'root';
	$this->cfg[3]['pwd']  = '';

	$this->num_cfg = 3;
}

public static function connect()
{
//	self::$link = mysqli_connect("mysql4.6", "jorkersdb", "NZSHvruG", "jorkersdb") or die("Error connexion db" . mysqli_connect_errno().' ; '.mysqli_connect_error());

	if (sess_context::isLocalHost())
		self::$link = mysqli_connect("localhost", "root", "root", "jorkyball") or die("Error connexion db" . mysqli_connect_errno().' ; '.mysqli_connect_error());
	else
		self::$link = mysqli_connect("jorkersdb5.mysql.db", "jorkersdb5", "1Azertyu", "jorkersdb5") or die("Error connexion db" . mysqli_connect_errno().' ; '.mysqli_connect_error());

//	mysqldump -h jorkersdb.mysql.db -u jorkersdb -pNZSHvruG --lock-tables=false --single-transaction -r dump.sql jorkersdb jb_actualites
//	mysqldump -h jorkersdb.mysql.db -u jorkersdb -pNZSHvruG --lock-tables=false --single-transaction -r dump.sql jorkersdb
//	mysqldump -h jorkersdb5.mysql.db -u jorkersdb5 -p1Azertyu -r dump.sql jorkersdb5
//  mysql -h jorkersdb5.mysql.db -u jorkersdb5 -p1Azertyu jorkersdb5 < dump.sql

//	printf("Jeu de caractère initial : %s\n", self::$link->character_set_name());
	self::$link->set_charset("latin1");
//	printf("Jeu de caractère initial : %s\n", self::$link->character_set_name());

	return self::$link;
}

public static function getDBName()
{
//	static::dbc();

	return $this->cfg[$this->num_cfg]['db'];
}

public static function die_script($requete, $sqlerror)
{
	global $sess_context;

	$msg1 = "Une erreur est survenue, le webmaster va ?tre inform? automatiquement";
	$msg2 = "Envoyer lui le sc?nario qui a aboutit a cette anomalie, merci";
	$msg3 = "---------------------------------------------------------------------------------------------";

	if ($sess_context->isSuperUser())
	{
//		ToolBox::alert($msg1."\\n".$msg2."\\n".$msg3."\\n".$requete."\\n".$msg3."\\n".$sqlerror."\\n".$msg3."\\n"."[".__LINE__."][".__FUNCTION__."][".__FILE__."]");
	}
	else
	{
//		$today       = date("F j, Y, g:i a");
//		$mail_to     = "contact@jorkers.com";
//		$mail_sujet  = "[Msg][Administrateur][JORKERS FATAL ERROR]";
//		$mail_corps  = $msg1."\n".$msg2."\n".$msg3."\n".$requete."\n".$msg3."\n".$sqlerror."\n".$msg3."\n"."[".__LINE__."][".__FUNCTION__."][".__FILE__."]\n".$msg3.getenv('REMOTE_ADDR')."\n\n".$today;
//		$mail_header = "From: contact@jorkers.com";
//		$res = @mail($mail_to,  $mail_sujet, nl2br($mail_corps), $mail_header);
	}
}

public static function execSQL($requete)
{
//	error_log(date('Y.m.d | H:i:s')." | ".$_SERVER['REMOTE_ADDR']." | ".$requete."\n", 3, "../my.log");
//  $res = mysqli_query($requete) or dbc::die_script($requete, mysqli_error());

    $res = mysqli_query(self::$link, $requete) or dbc::die_script($requete, mysqli_error(self::$link));
	
	return $res;
}

}

?>
