<?php
/*
 * This work is hereby released into the Public Domain.
 * To view a copy of the public domain dedication,
 * visit http://creativecommons.org/licenses/publicdomain/ or send a letter to
 * Creative Commons, 559 Nathan Abbott Way, Stanford, California 94305, USA.
 *
 */

require_once dirname(__FILE__)."/Image.class.php";

/**
 * AntiSpam
 * String printed on the images are case insensitive.
 *
 * @package Artichow
 */
class awAntiSpam extends awImage {

	/**
	 * Anti-spam string
	 *
	 * @var string
	 */
	var $string;

	/**
	 * Noise intensity
	 *
	 * @var int
	 */
	var $noise = 0;

	/**
	 * Construct a new awAntiSpam image
	 *
	 * @param string $string A string to display
	 */
	 function awAntiSpam($string = '') {

		parent::awImage();

		$this->string = (string)$string;

	}

	/**
	 * Create a random string
	 *
	 * @param int $length String length
	 * @return string String created
	 */
	 function setRand($length) {

		$length = (int)$length;

		$this->string = '';

		$letters = 'aAbBCDeEFgGhHJKLmMnNpPqQRsStTuVwWXYZz2345679';
		$number = strlen($letters);

		for($i = 0; $i < $length; $i++) {
			$this->string .= $letters{mt_rand(0, $number - 1)};
		}

		return $this->string;

	}

	/**
	 * Set noise on image
	 *
	 * @param int $nois Noise intensity (from 0 to 10)
	 */
	 function setNoise($noise) {
		if($noise < 0) {
			$noise = 0;
		}
		if($noise > 10) {
			$noise = 10;
		}
		$this->noise = (int)$noise;
	}

	/**
	 * Save string value in session
	 * You can use check() to verify the value later
	 *
	 * @param string $qName A name that identify the anti-spam image
	 */
	 function save($qName) {
		$this->session();
		$session = 'artichow_'.(string)$qName;
		$_SESSION[$session] = $this->string;
	}

	/**
	 * Verify user entry
	 *
	 * @param string $qName A name that identify the anti-spam image
	 * @param string $value User-defined value
	 * @param bool $case TRUE for case insensitive check, FALSE for case sensitive check ? (default to TRUE)
	 * @return bool TRUE if the value is correct, FALSE otherwise
	 */
	 function check($qName, $value, $case = TRUE) {

		$this->session();

		$session = 'artichow_'.(string)$qName;

		return (
			array_key_exists($session, $_SESSION) === TRUE and
			$case ?
				(strtolower($_SESSION[$session]) === strtolower((string)$value)) :
				($_SESSION[$session] === (string)$value)
		);

	}

	/**
	 * Draw image
	 */
	 function draw() {

		$fonts = array(
			ARTICHOW_FONT.DIRECTORY_SEPARATOR.'arial.ttf'
		);

		$sizes = array(14);

		$widths = array();
		$heights = array();
		$texts = array();

		for($i = 0; $i < strlen($this->string); $i++) {

			$fontKey = array_rand($fonts);
			$sizeKey = array_rand($sizes);

			$font = new awTTFFont(
				$fonts[$fontKey], $sizes[$sizeKey]
			);

			$text = new awText(
				$this->string{$i},
				$font,
				NULL,
				mt_rand(-15, 15)
			);

			$widths[] = $font->getTextWidth($text);
			$heights[] = $font->getTextHeight($text);
			$texts[] = $text;

		}

		$width = array_sum($widths);
		$height = array_max($heights);

		$totalWidth = $width + 10 + count($texts) * 10;
		$totalHeight = $height + 20;

		$this->setSize($totalWidth, $totalHeight);

		$this->create();

		for($i = 0; $i < strlen($this->string); $i++) {

			$this->drawer->string(
				$texts[$i],
				new awPoint(
					5 + array_sum(array_slice($widths, 0, $i)) + $widths[$i] / 2 + $i * 10,
					10 + ($height - $heights[$i]) / 2
				)
			);

		}

		$this->drawNoise($totalWidth, $totalHeight);

		$this->send();

	}

	 function drawNoise($width, $height) {

		$points = $this->noise * 30;
		$color = new awColor(0, 0, 0);

		for($i = 0; $i < $points; $i++) {
			$this->drawer->point(
				$color,
				new awPoint(
					mt_rand(0, $width),
					mt_rand(0, $height)
				)
			);
		}

	}

	 function session() {

		// Start session if needed
		if(!session_id()) {
			session_start();
		}

	}

}

registerClass('AntiSpam');
?>
