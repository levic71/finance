<?

class JKStaticHTMLCache
{
	var $cache_file;
	var $delai;
	var $hasExpired;

	function __construct($cache_file, $delai)
	{
		$this->cache_file  = $cache_file;
		$this->delai       = $delai;
		$this->hasExpired  = false;

		if ((@filemtime($this->cache_file) < time()-$this->delai) || isset($_GET['rebuild']))
		{
			$this->hasExpired = true;
			ob_start();
		}
	}

	public static function closeStaticHTMLCache()
	{
		if ($this->hasExpired)
		{
			$contenuCache = ob_get_contents();
			ob_end_flush();
			$fd = fopen("$this->cache_file", "w");
			if ($fd)
			{
				fwrite($fd, $contenuCache);
				fclose($fd);
			}
		}
		else
		{
			include($this->cache_file);
		}
	}
}

class JKCache
{
	public static function buildCache($filename, $type_cache)
	{
		if ($type_cache == "_FLUX_MOST_ACTIVE_")
		{
				if (file_exists($filename))
				{
					if (!file_exists($filename.".0"))
						copy($filename, $filename.".0");
					$fp = fopen($filename.".0", "r");
					$fstat = fstat($fp);
					if ((date("U")-$fstat['ctime']) > (24*60*60))
					{
						if (file_exists($filename.".6")) copy($filename.".6", $filename.".7");
						if (file_exists($filename.".5")) copy($filename.".5", $filename.".6");
						if (file_exists($filename.".4")) copy($filename.".4", $filename.".5");
						if (file_exists($filename.".3")) copy($filename.".3", $filename.".4");
						if (file_exists($filename.".2")) copy($filename.".2", $filename.".3");
						if (file_exists($filename.".1")) copy($filename.".1", $filename.".2");
						if (file_exists($filename.".0")) copy($filename.".0", $filename.".1");
						copy($filename, $filename.".0");
					}
					fclose($fp);
				}
		}

		$fichier = fopen($filename, "w");

		if (flock($fichier, LOCK_EX))
		{
			$flux = "";

			if ($type_cache == "_FLUX_OPTIONS_JORKERS_")
			{
				$flux = array();
				$flux['photo_icon']   = "0";
				$flux['video_icon']   = "0";
				$flux['sondage_home'] = "0";
				$flux['sondage_question_home'] = "0";
				$flux['photos_home']  = "1";
				$flux['partenariat']  = "1";
				$flux['zone_libre']   = "0";
			}

			if ($type_cache == "_FLUX_XML_ALBUM_")
			{
				$vars = explode('_', $filename);
				$sas  = new SQLAlbumsThemesServices($vars[1], $vars[2]);
				$flux = $sas->getXMLPhotos();
			}

			if ($type_cache == "_FLUX_INFO_CHAMP_")
			{
				$vars = explode('_', $filename);
				$scs = new SQLChampionnatsServices($vars[2]);
				$saison = $scs->getSaisonActive();
				$sss = new SQLSaisonsServices($vars[2], $saison['id']);

				$flux = array();
				$flux['nb_saisons']  = $scs->getNbSaisons();
				$flux['nb_joueurs']  = $scs->getNbJoueurs();
				$flux['nb_equipes']  = $scs->getNbEquipes();
				$flux['nb_journees'] = $scs->getNbJournees();
				$flux['nb_matchs']   = $scs->getNbMatchs();
				$flux['nb_messages'] = $scs->getNbMessages();
				$liste_joueurs = $sss->getListeJoueurs();
				$nb = 0;
				foreach($liste_joueurs as $cle => $val) $nb++;				
				$flux['nb_joueurs_saisonactive'] = $nb;
			}

			if ($type_cache == "_FLUX_TUX_")
			{
				$flux = array();
				$dir = "../tux/";
				if (is_dir($dir))
				{
				   if ($dh = opendir($dir))
				   {
				       while (($file = readdir($dh)) !== false)
				       {
				           if (is_file($dir.$file))
				           {
				        		$flux[] = $file;
							}
				       }
				       closedir($dh);
				   }
				}
			}

			if ($type_cache == "_FLUX_TDB_HOME_")
			{
			    $scs = new SQLChampionnatsServices();
				$flux = array();
				$flux['nb_tournois'] = $scs->getNbChampionnatsParType(_TYPE_TOURNOI_);
				$flux['nb_championnats'] = $scs->getNbChampionnatsParType(_TYPE_CHAMPIONNAT_);
				$flux['nb_libres']   = $scs->getNbChampionnatsParType(_TYPE_LIBRE_);
				$flux['nb_joueurs']  = $scs->getNbJoueurs();
				$flux['nb_equipes']  = $scs->getNbEquipes();
				$flux['nb_journees'] = $scs->getNbJournees();
				$flux['nb_matchs']   = $scs->getNbMatchs();
				$flux['nb_messages'] = $scs->getNbMessages();
			}

			if ($type_cache == "_FLUX_NEWSFOOT_")
			{
//				$flux = fetch_rss("http://www.lamoooche.com/getRSS.php?idnews=3328");
				$flux = @fetch_rss("http://www.lequipe.fr/Xml/Football/Titres/actu_rss.xml");
//				$flux = @fetch_rss("http://www.lfp.fr/rss/index.xml");
			}

			if ($type_cache == "_FLUX_VIDEOS_")
			{
				$svs  = new SQLVideossServices(-1);
				$flux = $svs->getVideos();
			}

			if ($type_cache == "_FLUX_LE_SAVIEZ_VOUS_")
			{
			    $sfs  = new SQLForumServices(-1);
				$flux = $sfs->getListeMessagesLeSaviezVous("LIMIT 0,9");
			}

			if ($type_cache == "_FLUX_PHOTO_HOME_")
			{
			    $sfs  = new SQLForumServices(-1);
				$flux = $sfs->getLastPhoto();
			}

			if ($type_cache == "_FLUX_CHRONIQUE_HOME_")
			{
			    $sfs  = new SQLForumServices(-1);
				$flux = $sfs->getLastChronique();
			}

			if ($type_cache == "_FLUX_FORUM_HOME_")
			{
			    $sfs  = new SQLForumServices(-1);
				$flux = $sfs->getListeMessagesForumHome("LIMIT 0,4");
			}

			if ($type_cache == "_FLUX_FORUM_")
			{
				$vars = explode('_', $filename);
			    $sfs  = new SQLForumServices($vars[2]);
				$flux = $sfs->getListeLastMessages("LIMIT 0,4");
			}

			if ($type_cache == "_FLUX_LAST_CREATED_")
			{
			    $scs  = new SQLChampionnatsServices();
				$flux = $scs->getLastChampionnatsCrees();
			}

			if ($type_cache == "_FLUX_MOST_ACTIVE_")
			{
			    $scs  = new SQLChampionnatsServices();
				$flux = $scs->getMostActiveChampionnats();
			}

			if ($type_cache == "_FLUX_ACCESS_")
			{
			    $scs  = new SQLChampionnatsServices();
				$flux = $scs->getAllChampionnats(true);
			}

			if ($type_cache == "_FLUX_STATS_CHAMP_")
			{
				$tmp = explode('_', basename($filename, ".txt"));
			    $scs  = new SQLChampionnatsServices($tmp[2]);
				$row = $scs->getChampionnat();
				$flux = new StatsGlobalBuilder($tmp[3], $row['type'], $row['type_sport']);
			}

			// Enregistrement des donnees brutes pour le XML et serialisees pour le reste
			if ($type_cache == "_FLUX_XML_ALBUM_" || $type_cache == "_FLUX_ZONE_LIBRE_")
			{
				fputs($fichier, $flux);
			}
			else
			{
				$objet_chaine = serialize($flux);
				fputs($fichier, $objet_chaine);
			}

			flock($fichier, LOCK_UN);
		}
		fclose($fichier);
	}

	public static function delCache($filename, $type_cache)
	{
	    if (file_exists($filename))
			unlink($filename);
	}

	public static function getCache($filename, $timeout, $type_cache)
	{
		if (!file_exists($filename) || filesize($filename) == 0)
			JKCache::buildCache($filename, $type_cache);
		else
		{
			$fp = fopen($filename, "r");
			$fstat = fstat($fp);
			if ($timeout != -1 && (date("U")-$fstat['mtime']) > $timeout)
				JKCache::buildCache($filename, $type_cache);
			fclose($fp);
		}

		// Pour les flux XML, on retourne le fichier tel quel, alors que pour le reste on transforme
		// le fichier en variable.
		if ($type_cache == "_FLUX_XML_ALBUM_" || $type_cache == "_FLUX_ZONE_LIBRE_")
			$cache = file($filename);
		else
		{
			$objet_chaine = implode("", file($filename));
			$cache = unserialize($objet_chaine);
		}

		return $cache;
	}
}

?>
