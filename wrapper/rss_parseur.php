<?php

require_once "../include/sess_context.php";

session_start();

header('Content-Type: text/html; charset='.sess_context::xhr_charset);

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


$items = array();

$limit = 30;
$cache = '../cache/rss_actus.txt';
$cachExpire = 150;

if (getenv('REMOTE_ADDR') == "localhost" || getenv('REMOTE_ADDR') == "localhost:8088" || getenv('REMOTE_ADDR') == "127.0.0.1") {

	// Nothing, only get a copy of prod file

} else {

	if ( (@filemtime($cache) < (time() - $cachExpire) ) || (!is_file($cache)) ) {

		$tab = array();

		$url = "http://mix.chimpfeedr.com/43138-Jorkers";
		$rss = simplexml_load_file($url);
		$items = $rss->entry;

		foreach($items as $item)
		{
			$id = (string)$item->id;
			$title = (string)$item->title;
			$link = (string)$item->link['href'];
			$published_on = (string)$item->updated;
			$description = (string)$item->summary;
			$content = (string)$item->content;
			$d1 = new DateTime((string)$published_on);
			$date = $d1->format("j M Y H:i");

			preg_match_all('/<img[^>]+>/i', $description, $result); 

			$img = isset($result[0][0]) ? $result[0][0] : "";
			for($x=0; $x < count($result[0]); $x++)
				if (isset($result[0][$x])) $description = str_replace($result[0][$x], "", $description);

			preg_match_all('/<br [^>]+>/i', $description, $result); 
			if (isset($result[0][0])) $description = str_replace($result[0][0], "", $description);

			preg_match_all('/<div[^>]+>Pub.*<\/div>/i', $description, $result); 
			if (isset($result[0][0])) $description = str_replace($result[0][0], "", $description);

			$tab[] = array("id" => $id, "img" => $img, "link" => $link, "title" => $title, "description" => $description, "date" => $date);
		}

		$handle = @fopen($cache, 'w');
		fwrite($handle, json_encode($tab));
		fclose($handle);

	}

}

if ($rss = json_decode(@file_get_contents( $cache ))) {
	$items = $rss;
}

echo "<h3>News</h3>";

$i = 0;
foreach($items as $item) { ?>

    <h4><a href="<?= $item->link ?>" target="_blank"><?= $item->title ?></a></h4>
    <div class="row-fluid <? if (strstr($item->link, 'number5')) echo 'number5' ?>">
	    <div class="span2 date"><?= $item->date ?></div>
   		<div class="span10 desc">
   			<?= $item->img ?>
			<?= $item->description ?>
		</div>
   	</div>
   	<hr />

<?
	if ($i++ > 3) break;
}

?>

<button class="btn btn-large btn-primary" style="float: right">Get More</button>
