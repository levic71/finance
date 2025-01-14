<?

class sess_context
{
	public $connected;
	public $user;
	public $favoris;

	const charset      = "ISO-8859-1";
	const xhr_charset  = "ISO-8859-1";
	const mail_charset = "ISO-8859-1";
	const xml_charset  = "ISO-8859-1";

	const email_admin = "vmlf71@gmail.com";

	public $colors_spectre = [
        0 => "238, 130, 6",		// Orange
        1 => "97, 194, 97",		// Green
        2 => "252, 237, 34",	// Yellow
        3 => "23, 109, 181",	// Bleu
        4 => "255, 153, 255",	// Fushia
        5 => "153, 51, 51",		// Marron
        6 => "204, 230, 255",	// Cyan
        7 => "209, 179, 255"	// Violet
    ];

	public function __construct()
	{
		$this->connected = 0; // 0: Utilisateur non connect� 1: Utilisateur connect�
		$this->user = "";
	}

	public static function isSuperUser() {
		$sn = strtolower(getenv('SERVER_NAME'));
		$ra = strtolower(getenv('REMOTE_ADDR'));
		return ($sn == "localhost" || strtolower($sn) == "r7.jorkers.com" || substr(strtolower($sn), 0, 3) == "r7-" || $rn == "91.164.59.145" || $rn == "127.0.0.1" || $rn == "localhost" ? true : false);
	}
	public static function isR7Host() {
		$sn = strtolower(getenv('SERVER_NAME'));
		$ra = strtolower(getenv('REMOTE_ADDR'));
		return (strtolower($sn) == "r7.jorkers.com" || substr(strtolower($sn), 0, 3) == "r7-" ? true : false);
	}
	public static function isLocalHost() {
		$sn = strtolower(getenv('SERVER_NAME'));
		$ra = strtolower(getenv('REMOTE_ADDR'));
		return ($sn == "localhost" || $ra == "127.0.0.1" || $ra == "localhost" ? true : false);
	}

	public static function getUserIp() {

		$ipkeys = array(
			'REMOTE_ADDR',
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP'
		);

		// Now we check each key against $_SERVER if containing such value
		$ip = array();

		foreach ($ipkeys as $keyword) {
			if (isset($_SERVER[$keyword])) {
				$ip[] = $_SERVER[$keyword];
			}
		}

		$ip = ( empty($ip) ? 'Unknown' : implode(", ", $ip) );
		return $ip;
	}

	public function getSpectreColor($i, $transparency = 0.75) {
		return "rgba(".(isset($this->colors_spectre[$i]) ? $this->colors_spectre[$i] : $this->colors_spectre[0]).", ".$transparency.")";
	}

	public function getUser() {
		return $this->user;
	}

	public function getUserId() {
		return $this->isUserConnected() ? $this->user['id'] : -1;
	}

	public function setUserConnection($user) {
		$this->connected = 1;
		$this->user = $user;
	}

	public function resetUserConnection() {
		$this->connected = 0;
		$this->user = "";
	}
	
	public function isUserConnected() {
		return ($this->connected == 1);
	}

	public function isSuperAdmin() {
		return ($this->isUserConnected() && $this->user['super_admin'] == 1);
	}

	public function resetSuperAdmin() {
		$this->user['super_admin'] = 0;
	}

	public static function getSiteVersion()
	{
		return "2.0.0" . (sess_context::isLocalHost() ? "." . time() : ""); // Ne pas mettre dans le construct sinon pas dynamique, il faut vider le cache browser
	}

}