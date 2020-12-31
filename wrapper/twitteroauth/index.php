<?php
/**
 * @file
 * User has successfully authenticated with Twitter. Access tokens saved to session and DB.
 */

/* Load required lib files. */
session_start();
require_once('twitteroauth/twitteroauth.php');




$consumer_key       = 'lLptrnYOlTWDwKEKSDfiAQ'; //Provide your application consumer key
$consumer_secret    = 'bRnrvgXfug6Mi58V6QJgEgJ0UIj2kaMBiKCeXHpfqlA'; //Provide your application consumer secret
$oauth_token        = '1032401858-1kynYL8pyHX5gM13Dj9eb0Tuya9ksbrYo2kzOMp'; //Provide your oAuth Token
$oauth_token_secret = 'iZiG3rSVqJxLQoP6AchM3FhIr9o4iqu9Rj1Nbi2e8s'; //Provide your oAuth Token Secret

$connection = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);

$content = $connection->get('account/verify_credentials');
print_r($content);


echo "--------------------------------------------<br />\n";

$query = 'https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=Jorkers&count=1'; //Your Twitter API query
$content = $connection->get($query);
print_r($content);

exit(0);




require_once('config.php');

/* If access tokens are not available redirect to connect page. */
if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
    header('Location: ./clearsessions.php');
}
/* Get user access tokens out of the session. */
$access_token = $_SESSION['access_token'];

/* Create a TwitterOauth object with consumer/user tokens. */
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);

/* If method is set change API call made. Test is called by default. */

/* Some example calls */
//$connection->get('users/show', array('screen_name' => 'abraham'));
//$connection->post('statuses/update', array('status' => date(DATE_RFC822)));
//$connection->post('statuses/destroy', array('id' => 5437877770));
//$connection->post('friendships/create', array('id' => 9436992));
//$connection->post('friendships/destroy', array('id' => 9436992));

?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>Twitter OAuth in PHP</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <style type="text/css">
      img {border-width: 0}
      * {font-family:'Lucida Grande', sans-serif;}
    </style>
  </head>
  <body>
    <div>
      <h2>Welcome to a Twitter OAuth PHP example.</h2>


      <?php if (isset($menu)) { ?>
        <?php echo $menu; ?>
      <?php } ?>
    </div>
    <?php if (isset($status_text)) { ?>
      <?php echo '<h3>'.$status_text.'</h3>'; ?>
    <?php } ?>
    <p>
      <pre>
        <?php

$content = $connection->get('account/verify_credentials');
        print_r($content);
$content = $connection->get('statuses/user_timeline', array('screen_name' => 'Jorkers', 'count' => '5'));
        print_r($content);

        ?>
      </pre>
    </p>

  </body>
</html>
