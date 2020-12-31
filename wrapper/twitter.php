<?

require_once "../include/sess_context.php";

session_start();

// require_once "common.php";

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

$twitter_user  = isset($sess_context->championnat['twitter']) ? $sess_context->championnat['twitter'] : "Jorkers";
$twitter_init  = false;
$twitter_array = array();
$twitter_limit = 7;
$twitter_cache = '../cache/twitter_'.$twitter_user.'.txt';
$twitter_followers_count = 0;

// Pas très beau mais ça va servir dans les 2 cas ...
include('mytwit.inc.php');
$twitter = new myTwit();
$twitter->user = $twitter_user;
$twitter->cacheFile = $twitter_cache;
$twitter->postLimit = $twitter_limit;


if ($twitter_user == "") exit(0);


if (getenv('REMOTE_ADDR') == "localhost" || getenv('REMOTE_ADDR') == "localhost:8088" || getenv('REMOTE_ADDR') == "127.0.0.1") {
	$twitter_init  = $twitter->initMyTwit();
	$twitter_array = $twitter->jsonArray;
	$twitter_followers_count = $twitter->followers_count;
}
else
{

	require_once('twitteroauth.php');

	$consumer_key       = 'lLptrnYOlTWDwKEKSDfiAQ'; //Provide your application consumer key
	$consumer_secret    = 'bRnrvgXfug6Mi58V6QJgEgJ0UIj2kaMBiKCeXHpfqlA'; //Provide your application consumer secret
	$oauth_token        = '1032401858-1kynYL8pyHX5gM13Dj9eb0Tuya9ksbrYo2kzOMp'; //Provide your oAuth Token
	$oauth_token_secret = 'iZiG3rSVqJxLQoP6AchM3FhIr9o4iqu9Rj1Nbi2e8s'; //Provide your oAuth Token Secret

	$cachExpire = 600;

	if ( (@filemtime($twitter_cache) < (time() - $cachExpire) ) || (!is_file($twitter_cache)) ) {

		$connection = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);

		$content = $connection->get('account/verify_credentials');

		$query = 'https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name='.$twitter_user.'&count='.$twitter_limit; //Your Twitter API query
		$content = $connection->get($query);

		$handle = @fopen($twitter_cache, 'w');
		fwrite($handle, json_encode($content));
		fclose($handle);

	}

	if ($json = @file_get_contents( $twitter_cache )) {
		$twitter_array = json_decode($json, true);
		$twitter_init  = true;
		if (isset($twitter_array[0]['user']['followers_count'])) $twitter_followers_count = $twitter_array[0]['user']['followers_count'];
	}
}

?>

<h3>Twitter</h3>

<?

// print_r($twitter_array);

if ($twitter_init) {

for($x=0; $x < count($twitter_array) && $x < $twitter_limit; $x++) {

	// print_r($twitter_array[$x]);

	$seconds_ago = time() - strtotime($twitter_array[$x]['created_at']);
	$ts = strtotime($twitter_array[$x]['created_at'])+$twitter_array[$x]['user']['utc_offset'];
	$cur_ts = time();
	echo '<div>';
	echo '<h4><a href="http://twitter.com/'.$twitter_array[$x]['user']['screen_name'].'" target="_blank">'.$twitter->linkURLs(utf8_decode($twitter_array[$x]['text'])).'</a></h4>';
	echo '<span class="twhen">by <a href="http://twitter.com/'.$twitter_array[$x]['user']['screen_name'].'">'.$twitter_array[$x]['user']['screen_name'].'</a> '.$twitter->intoRelativeTime($seconds_ago).'</span>';
	echo "<hr />";
	echo '</div>';
}

?>

<br />
<button id="b55" class="btn btn-large btn-primary"><?= $twitter_followers_count  ?> Followers</button>

<? } ?>