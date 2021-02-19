<?

function _sc_register_globals($order = 'egpcs')
{
    // define a subroutine
    if(!function_exists('register_global_array'))
    {
        function register_global_array(array $superglobal)
        {
            foreach($superglobal as $varname => $value)
            {
                global $$varname;
                $$varname = $value;
            }
        }
    }
    
    $order = explode("\r\n", trim(chunk_split($order, 1)));
    foreach($order as $k)
    {
        switch(strtolower($k))
        {
            case 'e':    register_global_array($_ENV);       break;
            case 'g':    register_global_array($_GET);       break;
            case 'p':    register_global_array($_POST);      break;
            case 'c':    register_global_array($_COOKIE);    break;
            case 's':    register_global_array($_SERVER);    break;
        }
    }
}

_sc_register_globals();

class sess_context
{
	var $championnat;
	var $saisons;
	var $friends;
	var $nb_saisons;
	var $valide;
	var $connected;
	var $user;
	var $admin;
	var $role;
	var $xdisplay;
	var $id_journee_encours;
	var $langue;
	var $options_generales;
	const default_photo = "img/user-img.png";
	const INVALID_CHAMP_ID_HOME   = -999; // si idc = INVALID_CHAMP_ID_HOME   accès limiter à  la liste championnats + connexion
	const INVALID_CHAMP_ID_LOGIN  = -998; // si idc = INVALID_CHAMP_ID_LOGIN  accès page login/inscription
	const INVALID_CHAMP_ID_PROFIL = -997; // si idc = INVALID_CHAMP_ID_PROFIL accès page mon profil
	const charset = "ISO-8859-1";
	const xhr_charset = "ISO-8859-1";
	const mail_charset = "ISO-8859-1";
	const xml_charset = "ISO-8859-1";

	function __construct() {

		$this->valide    = -1;				// 0: Championnat non valide, 1: Championnat valide, -1: non défini
		$this->connected = 0;				// 0: Utilisateur non connecté 1: Utilisateur connecté
		$this->user      = "";
		$this->admin     = 0;				// 0: Mode normal, 1: Mode administration championnat
		$this->role      = _ROLE_ANONYMOUS_;
		$this->xdisplay  = _XDISPLAY_FREE_;	// Modalité d'affichage (par défaut, affichage en mode gestion libre)
		$this->id_journee_encours = 0;		// Id de la journée sur laquelle on travaille

		// Récupération des options généréles du Jorkers
		$this->options_generales = JKCache::getCache("../cache/flux_options.txt", -1, "_FLUX_OPTIONS_JORKERS_");
	}

	function setChampionnat($championnat) {
		$this->championnat = $championnat;
		unset($this->championnat['news']);
		unset($this->championnat['description']);
		unset($this->championnat['login']);
		unset($this->championnat['pwd']);

		if ($this->championnat['type'] == _TYPE_LIBRE_)		  $this->xdisplay = _XDISPLAY_FREE_;
		if ($this->championnat['type'] == _TYPE_CHAMPIONNAT_) $this->xdisplay = _XDISPLAY_CHAMPIONNAT_;
		if ($this->championnat['type'] == _TYPE_TOURNOI_)	  $this->xdisplay = _XDISPLAY_TOURNOI_;

		// Retranscription des options
		$options = (!isset($this->championnat['options']) || $this->championnat['options'] == "") ? "0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0" : $this->championnat['options'];
		$opt = explode('|', $options);
		$this->championnat['option_poule_lettre'] = isset($opt[7]) ? $opt[7] : 0;
		$this->championnat['option_display_all_matchs'] = isset($opt[8]) ? $opt[8] : 0;
		$this->championnat['option_gavgp'] = isset($opt[11]) ? $opt[11] : 0;

		$this->valide = ($this->championnat['championnat_id'] == 0) ? 0 : 1;	// Ne pas appeler la méthode !!!!

		if ($this->championnat['championnat_id'] != 0)
		{
		  $this->setSaisons();
		  $this->setFriends();
		}
	}

	function setFriends() {
		unset($this->friends);
		$this->friends = array();

		if ($this->championnat['friends'] != "")
		{
			$req = "SELECT * FROM jb_championnat WHERE id IN (".$this->championnat['friends'].") ORDER BY nom";
			$res = dbc::execSql($req);
			while($row = mysqli_fetch_array($res))
				$this->friends[$row['id']] = $row['nom'];
		}
   	}

	function setSaisons() {
		$this->nb_saisons = 0;
		unset($this->saisons);
		$this->saisons = array();

		$req = "SELECT * FROM jb_saisons WHERE id_champ=".$this->getRealChampionnatId();
		$res = dbc::execSql($req);
		while($row = mysqli_fetch_array($res))
		{
			$this->nb_saisons++;
			$this->saisons[] = $row;
		}
	}

	// Changement logique de la saison en cours d'affichage
	function changeSaison($new_id_saison) {
		$this->championnat['saison_id'] = $new_id_saison;
		$tab = array();
		reset($this->saisons);
		foreach($this->saisons as $saison)
		{
			if ($saison['id'] == $new_id_saison)
				$saison['active'] = 1;
			else
				$saison['active'] = 0;
			$tab[] = $saison;
		}
		$this->saisons = $tab;
	}

	function isOptionGeneraleSet($option) {
		global $sess_context;
		if (isset($sess_context->options_generales))
		{
			$ret = $sess_context->options_generales[$option] == 1 ? true : false;
		}
		else
		{
			// Récupération des options généréles du Jorkers
			$options_generales = JKCache::getCache("../cache/flux_options.txt", -1, "_FLUX_OPTIONS_JORKERS_");
			$ret = $options_generales[$option] == 1 ? true : false;
		}

		return $ret;
	}
	function isNewVideoIconSet()			{ return sess_context::isOptionGeneraleSet('video_icon'); }
	function isNewPhotoIconSet()			{ return sess_context::isOptionGeneraleSet('photo_icon'); }
	function isHomeSondageSet()				{ return sess_context::isOptionGeneraleSet('sondage_home'); }
	function isHomeSondageQuestionSet()		{ return sess_context::isOptionGeneraleSet('sondage_question_home'); }
	function isHomePhotosSet()				{ return sess_context::isOptionGeneraleSet('photos_home'); }
	function isHomePartenariat()			{ return sess_context::isOptionGeneraleSet('partenariat'); }
	function isHomeZoneLibre()				{ return sess_context::isOptionGeneraleSet('zone_libre'); }

	function setLangue($langue)			{ $this->langue = $langue; }
	function getLangue()				{ return $this->langue == "" ? "fr" : $this->langue; }
	function getChampionnatId()			{ return $this->championnat['saison_id']; }
	function getRealChampionnatId()		{ return $this->championnat['championnat_id']; }
	function getChampionnatNom()		{ return $this->championnat['championnat_nom']; }
	function getSaisonNom()				{ return $this->championnat['saison_nom']; }
	function setSaisonNom($nom)			{ $this->championnat['saison_nom'] = $nom; }
	function getSaisonId()				{ return $this->championnat['saison_id']; }
	function setSaisonId($id)			{ $this->championnat['saison_id'] = $id; }
	function getChampionnatType()		{ return $this->championnat['type']; }
	function getChampionnatOptions()	{ return $this->championnat['options']; }

	function getJourneeId()				{ return $this->id_journee_encours; }
	function setJourneeId($id_journee)	{ $this->id_journee_encours = $id_journee; }


	function isFreeXDisplay()			{ return (($this->xdisplay == _XDISPLAY_FREE_        || $this->xdisplay == _XDISPLAY_ALL_) ? true : false); }
	function isChampionnatXDisplay()	{ return (($this->xdisplay == _XDISPLAY_CHAMPIONNAT_ || $this->xdisplay == _XDISPLAY_ALL_) ? true : false); }
	function isTournoiXDisplay()		{ return (($this->xdisplay == _XDISPLAY_TOURNOI_     || $this->xdisplay == _XDISPLAY_ALL_) ? true : false); }
	function isAllXDisplay()			{ return (($this->xdisplay == _XDISPLAY_ALL_) ? true : false); }
	function getXDisplay()				{ return $this->xdisplay; }

	public static function isSuperUser() { $sn = strtolower(getenv('SERVER_NAME')); $ra = strtolower(getenv('REMOTE_ADDR')); return ($sn == "localhost" || $sn == "r7.jorkers.com" || $rn == "81.57.60.63" || $rn == "127.0.0.1" || $rn == "localhost" ? true : false); }
	public static function isR7Host()    { $sn = strtolower(getenv('SERVER_NAME')); $ra = strtolower(getenv('REMOTE_ADDR')); return ($sn == "r7.jorkers.com" ? true : false); }
	public static function isLocalHost() { $sn = strtolower(getenv('SERVER_NAME')); $ra = strtolower(getenv('REMOTE_ADDR')); return ($sn == "localhost" || $rn == "127.0.0.1" || $rn == "localhost" ? true : false); }

	function setChampionnatValide()		{ $this->valide = 1; }
	function setChampionnatNonValide()	{ $this->valide = 0; }
	function setChampionnatNonDefini()	{ $this->valide = -1; }
	function isChampionnatValide()		{ return ($this->valide == 1 && $this->getRealChampionnatId() > 0); }
	function isChampionnatNonValide()	{ return ($this->valide == 0); }
	function isChampionnatNonDefini()	{ return ($this->valide == -1); }

	function getUser()	 				{ return $this->user; }
	function setUserConnection($user) 	{ $this->connected = 1; $user['pwd'] = ""; 	if ($user['photo'] == "") $user['photo'] = "img/user-img.png"; $this->user = $user; }
	function resetUserConnection() 		{ $this->connected = 0; $this->user = ""; }
	function isUserConnected()			{ return ($this->connected == 1); }
	function isSuperAdmin()				{ return ($this->isUserConnected() && $this->user['super_admin'] == 1); }

	function setAdmin() 				{ $this->admin = 1; }
	function resetAdmin() 				{ $this->admin = 0; }
	function isAdmin()					{ return ($this->isSuperAdmin() || $this->admin == 1); }
	function setRole($role) 			{ $this->role = $role; }
	function isOnlyDeputy()				{ return ($this->isAdmin() && $this->role == _ROLE_DEPUTY_); }

	function checkChampionnatValidity() {
		$id = $this->_getRealChampionnatId();
		if ($id == sess_context::INVALID_CHAMP_ID_HOME || $id == sess_context::INVALID_CHAMP_ID_LOGIN || $id == sess_context::INVALID_CHAMP_ID_PROFIL)
			$this->setChampionnatNonDefini();
	}

	public static function getJorkersVersion() {
		return "3.0.3".(sess_context::isLocalHost() ? ".".time() : ""); // Ne pas mettre dans le construct sinon pas dynamique, il faut vider le cache browser
	}
	public static function _getChampionnatId() {
		global $sess_context;
		return ($sess_context->championnat['saison_id']);
	}
	public static function _getRealChampionnatId() {
		global $sess_context;
		return ($sess_context->championnat['championnat_id']);
	}
	public static function _getChampionnatNom() {
		global $sess_context;
		return ($sess_context->championnat['championnat_nom']);
	}
	public static function _getChampionnatLogo() {
		global $sess_context;
		return (isset($sess_context->championnat['logo_photo']) && $sess_context->championnat['logo_photo'] != "" ? $sess_context->championnat['logo_photo'] : "img/soccer-larger.jpg");
	}
	public static function _isTournoi() {
		global $sess_context;
		return ($sess_context->championnat['type'] == _TYPE_TOURNOI_);
	}
	public static function _isChampionnat() {
		global $sess_context;
		return ($sess_context->championnat['type'] == _TYPE_CHAMPIONNAT_);
	}
	public static function _isFreeChampionnat() {
		global $sess_context;
		return ($sess_context->championnat['type'] == _TYPE_LIBRE_);
	}
	public static function isGoalAverageParticulier() {
		global $sess_context;
		return ($sess_context->championnat['option_gavgp'] == 1);
	}
	public static function getValeurVictoireMatch() {
		global $sess_context;
		return isset($sess_context->championnat['valeur_victoire']) ? $sess_context->championnat['valeur_victoire'] : 3;
	}
	public static function getValeurNulMatch() {
		global $sess_context;
		return isset($sess_context->championnat['valeur_nul']) ? $sess_context->championnat['valeur_nul'] : 1;
	}
	public static function getForfaitPenaliteBonus() {
		global $sess_context;
		return isset($sess_context->championnat['forfait_penalite_bonus']) ? $sess_context->championnat['forfait_penalite_bonus'] : 0;
	}
	public static function getForfaitPenaliteMalus() {
		global $sess_context;
		return isset($sess_context->championnat['forfait_penalite_malus']) ? $sess_context->championnat['forfait_penalite_malus'] : 0;
	}
	public static function getValeurDefaiteMatch() {
		global $sess_context;
		return isset($sess_context->championnat['valeur_defaite']) ? $sess_context->championnat['valeur_defaite'] : 1;
	}
	public static function getGestionMatchsNul() {
		global $sess_context;
		return isset($sess_context->championnat['gestion_nul']) ? $sess_context->championnat['gestion_nul'] : 0;
	}
	public static function getGestionFanny() {
		global $sess_context;
		return isset($sess_context->championnat['gestion_fanny']) ? $sess_context->championnat['gestion_fanny'] : 1;
	}
	public static function getGestionSets() {
		global $sess_context;
		return isset($sess_context->championnat['gestion_sets']) ? $sess_context->championnat['gestion_sets'] : 1;
	}
	public static function getTriClassementGeneral() {
		global $sess_context;
		return isset($sess_context->championnat['tri_classement_general']) ? $sess_context->championnat['tri_classement_general'] : 1;
	}
	public static function getTypeSport() {
		global $sess_context;
		return isset($sess_context->championnat['type_sport']) ? $sess_context->championnat['type_sport'] : 1;
	}
	public static function getLibelleTypeSport() {
		global $sess_context;

		$type = isset($sess_context->championnat['type_sport']) ? $sess_context->championnat['type_sport'] : 1;

		if ($type == 1) return "Jorkyball";
		if ($type == 2) return "Futsal";
		if ($type == 3) return "Football";
	}
	public static function getHomeListHeadcount() {
		global $sess_context;
		return isset($sess_context->championnat['home_list_headcount']) ? $sess_context->championnat['home_list_headcount'] : 10;
	}
}

?>
