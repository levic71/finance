<?php
/* Class to embed twitter comments for a user into a website page (PHP) */
class myTwit{
	var $user = false; // User to show public posts
	var $cacheFile = 'twittercache.txt'; // File to save local cache of Twitter update
	var $cachExpire = 600; // Seconds that cache is classed as "old"
	var $myTwitHeader = true;
	var $postLimit = 20; // 20 = max
	var $debug = false;
	var $targetBlank = true;
	var $targetAppend;
	var $postClass = false;
	var $twitter_url = "#";
	var $twitter_profile_image = "";
	var $followers_count = 0;

	function printError($message){ if ($this->debug == true) echo htmlspecialchars($message); }

	function debugMsg($message){if ($this->debug == true) echo htmlspecialchars($message).'<br />';}

	function formatPlural($val, $qty){
		if ($val > 1) return $val.' '.$qty.'s';
		else return $val.' '.$qty;
	}

	function intoRelativeTime($seconds){
		if (($seconds / 60 / 60 / 24) > 1) return $this->formatPlural(round($seconds / 60 / 60 / 24), ' day').' ago';
		elseif (($seconds / 60 / 60) > 1) return 'about '.$this->formatPlural(round($seconds / 60 / 60), 'hour').' ago';
		else if (($seconds / 60 ) > 1) return 'about '.$this->formatPlural(round($seconds / 60), 'minute').' ago';
		else return 'about '.round($seconds).' seconds ago';
	}

	function linkURLs($text){
		$in=array( '`((?:https?|ftp)://\S+[[:alnum:]]/?)`si', '`((?<!//)(www\.\S+[[:alnum:]]/?))`si' );
		$out=array( '<a href="$1"'.$this->targetAppend.'>$1</a> ', '<a href="http://$1" target="_blank">$1</a>' );
		$text = preg_replace($in,$out,$text);
		$text = preg_replace('/@([a-zA-Z0-9-_]+)/','@<a href="http://twitter.com/$1"'.$this->targetAppend.'>$1</a>',$text);
		return $text;
	}

	function checkCacheFile(){
		if ( (@filemtime($this->cacheFile) < (time() - $this->cachExpire) ) || (!is_file($this->cacheFile)) ){
			$this->debugMsg('Cache file outdated');
			return $this->updateCache();
		} else {
			$this->debugMsg('Cache file still valid');
			return true;
		}
	}

	function updateCache(){
		$uri = 'http://api.twitter.com/1/statuses/user_timeline.json?screen_name='.$this->user;
		$req = new HTTPRequest($uri);
		$tmpdata = $req->DownloadToString();
		$resp = json_decode($tmpdata, true);

		if (isset($resp['error'])) {
			$this->printError('Error getting information from Twitter ['.$resp['error'].']. Please check the username ('.$this->user.')');
			return false;
		}
		else if (!is_array($resp)) {
			$this->printError('Error getting information from Twitter. File is not JSON.');
			return false;
		}

		$handle = @fopen($this->cacheFile, 'w');
		if (!$handle) $this->printError('Could not write to cache file: '.$this->cacheFile.'. Please check read/write permissions.');
		fwrite($handle, $tmpdata);
		fclose($handle);
		$this->debugMsg('Updated cache file: '.$this->cacheFile);

		return true;
	}

	function readCache(){
		if( false == ($this->jsonData = @file_get_contents( $this->cacheFile ))) {
			$this->printError('Could not read cache file: '.$this->cacheFile);
			return false;
		}

		return true;
	}

	function initMyTwit(){
		if (!is_string($this->user)) $this->printError('Please set a user.');
		$this->targetAppend = ($this->targetBlank) ? ' target="_blank"' : '';
		$this->postClassAppend = ($this->postClass) ? ' class="'.$this->postClass.'"' : '';

		if (!$this->checkCacheFile()) return false;
		if (!$this->readCache()) return false;

		$this->jsonArray = json_decode($this->jsonData, true);
		$output = '<ul class="twitbox">';
		if ($this->myTwitHeader && isset($this->jsonArray[0])){
			$this->twitter_url = 'http://twitter.com/'.$this->user.'"'.$this->targetAppend;
			$this->twitter_profile_image = $this->jsonArray[0]['user']['profile_image_url'];
			$this->followers_count = $this->jsonArray[0]['user']['followers_count'];
			$output .= '<li class="mytwitHead"><a href="http://twitter.com/'.$this->user.'"'.$this->targetAppend.'><img src="'.$this->jsonArray[0]['user']['profile_image_url'].'" style="border:0" alt="'.$this->user.'" /></a>'.
			'<div><a href="http://twitter.com/'.$this->user.'"'.$this->targetAppend.'>'.$this->user.'</a><br />'.
			$this->formatPlural($this->jsonArray[0]['user']['followers_count'], 'follower').'</div></li>';
		}
		for($x=0; $x < count($this->jsonArray) && $x < $this->postLimit; $x++){
			$seconds_ago = time() - strtotime($this->jsonArray[$x]['created_at']);
			$ts = strtotime($this->jsonArray[$x]['created_at'])+$this->jsonArray[$x]['user']['utc_offset'];
			$cur_ts = time();
			$output .= '<li'.$this->postClassAppend.'>'.$this->linkURLs($this->jsonArray[$x]['text']).
			' <span class="twhen">by <a href="http://twitter.com/'.$this->jsonArray[$x]['user']['screen_name'].'"'.$this->targetAppend.'>'.$this->jsonArray[$x]['user']['screen_name'].'</a> '.
			$this->intoRelativeTime($seconds_ago)."</span></li>\n";
		}
		$output .= '</ul>';
		$this->myTwitData = $output;

		return true;
	}
}


class HTTPRequest{
	var $_fp;		// HTTP socket
	var $_url;		// full URL
	var $_host;		// HTTP host
	var $_protocol;	// protocol (HTTP/HTTPS)
	var $_uri;		// request URI
	var $_port;		// port
	// scan url
	function _scan_url(){
		$req = $this->_url;
		$pos = strpos($req, '://');
		$this->_protocol = strtolower(substr($req, 0, $pos));
		$req = substr($req, $pos+3);
		$pos = strpos($req, '/');
		if($pos === false)
				$pos = strlen($req);
		$host = substr($req, 0, $pos);
		if(strpos($host, ':') !== false)        {
				list($this->_host, $this->_port) = explode(':', $host);
		}else{
				$this->_host = $host;
				$this->_port = ($this->_protocol == 'https') ? 443 : 80;
		}
		$this->_uri = substr($req, $pos);
		if($this->_uri == '')
			$this->_uri = '/';
	}
	// constructor
	function __construct($url){
		$this->_url = $url;
		$this->_scan_url();
	}
	// download URL to string
	function DownloadToString(){
		$crlf = "\r\n";
		// generate request
		$req = 'GET ' . $this->_uri . ' HTTP/1.0' . $crlf
				.    'Host: ' . $this->_host . $crlf
				.	   'User-Agent: PHP' . $crlf
				.    $crlf;
		// fetch
		$this->_fp = @fsockopen(($this->_protocol == 'https' ? 'ssl://' : '') . $this->_host, $this->_port);
		if ($this->_fp){
			fwrite($this->_fp, $req);
			$response = "";
			while(is_resource($this->_fp) && $this->_fp && !feof($this->_fp))
					$response .= fread($this->_fp, 1024);
			fclose($this->_fp);
			// split header and body
			$pos = strpos($response, $crlf . $crlf);
			if($pos === false)
					return($response);
			$header = substr($response, 0, $pos);
			$body = substr($response, $pos + 2 * strlen($crlf));
			// parse headers
			$headers = array();
			$lines = explode($crlf, $header);
			foreach($lines as $line)
					if(($pos = strpos($line, ':')) !== false)
							$headers[strtolower(trim(substr($line, 0, $pos)))] = trim(substr($line, $pos+1));
			// redirection?
			if(isset($headers['location'])){
					$http = new HTTPRequest($headers['location']);
					return($http->DownloadToString($http));
			} else{
					return($body);
			}
		} else {
			return false;
		}
	}
}


if ( !function_exists('json_decode') ){
	require_once (dirname(__FILE__).'/JSON.php');
    function json_decode($content, $assoc=false){
		if ( $assoc ){
			$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		} else {
			$json = new Services_JSON;
		}
		return $json->decode($content);
	}
}

if ( !function_exists('json_encode') ){
	require_once (dirname(__FILE__).'/JSON.php');
	function json_encode($content){
		$json = new Services_JSON;
		return $json->encode($content);
    }
}



?>