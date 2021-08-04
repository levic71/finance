<?

class sess_context
{
	public $connected;
	public $user;

	const charset = "ISO-8859-1";
	const xhr_charset = "ISO-8859-1";
	const mail_charset = "ISO-8859-1";
	const xml_charset = "ISO-8859-1";

	public function __construct()
	{
		$this->connected = 0; // 0: Utilisateur non connecté 1: Utilisateur connecté
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