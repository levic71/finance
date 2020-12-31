<!DOCTYPE html>
<html>
	<head>
		<meta charset=utf-8 />
		<title>Flux Slider Demo</title>
		<!--[if lte IE 8]>
 			<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		<link rel="stylesheet" href="css/demo.css" type="text/css" media="screen" title="no title" charset="utf-8">

<? if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE' ) !== FALSE ) { ?>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<? } else { ?>
		<script src="js/zepto/zepto.js" type="text/javascript" charset="utf-8"></script>
<? } ?>


		<script src="js/flux.min.js" type="text/javascript" charset="utf-8"></script>
		<script type="text/javascript" charset="utf-8">
			$(function(){
				if(false && !flux.browser.supportsTransitions)
					alert("Flux Slider requires a browser that supports CSS3 transitions");

				window.f = new flux.slider('#slider', {
					pagination: true
				});
			});
		</script>
	</head>
	<body>
		<section class="container">
			<div id="slider">
				<img src="img/avatar.jpg" alt="" />
				<img src="img/ironman.jpg" alt="" />
				<img src="img/tron.jpg" alt="" />
				<img src="img/greenhornet.jpg" alt="" />
			</div>
			<footer>
			</footer>
		</section>
	</body>
</html>