<?php

require_once '../include/config.php';

$dimensions = @$_GET['dimensions'];

extract($_SESSION['rns']);

if(empty($dimensions)
		|| empty($detectors) || empty($space) || empty($self) || empty($tests)
		|| (count($dimensions) != 2 && count($dimensions) != 3))
	die('Invalid data');

$w = 600;
$h = 600;

if(count($dimensions) == 2) {
	$ratio = min(array(
		$w / ($space[$dimensions[0]]['max'] - $space[$dimensions[0]]['min']),
		$h / ($space[$dimensions[1]]['max'] - $space[$dimensions[1]]['min'])
	));

	$im = imagecreatetruecolor($w, $h);
	$bg = imagecolorallocate($im, 255, 255, 255);
	imagefilledrectangle($im, 0, 0, $w, $h, $bg);
	$colorBlack = imagecolorallocate($im, 0, 0, 0);
	$colorRed = imagecolorallocate($im, 255, 0, 0);
	$colorGreen = imagecolorallocate($im, 0, 255, 0);
	$colorBlue = imagecolorallocate($im, 224, 224, 255);
	$colorDarkBlue = imagecolorallocate($im, 0, 0, 255);

	foreach($detectors as $d) {
		imagesetpixel($im,
			$ratio * $d->centre->coords[$dimensions[0]], $ratio * $d->centre->coords[$dimensions[1]],
			$colorDarkBlue);
		imagefilledellipse($im,
			$ratio * $d->centre->coords[$dimensions[0]], $ratio * $d->centre->coords[$dimensions[1]],
			$ratio * $d->radius, $ratio * $d->radius, $colorBlue);
		imageellipse($im,
			$ratio * $d->centre->coords[$dimensions[0]], $ratio * $d->centre->coords[$dimensions[1]],
			$ratio * $d->radius, $ratio * $d->radius, $colorDarkBlue);
	}

	foreach($self as $s) {
		/* imagesetpixel($im,
			$ratio * $s->coords[$dimensions[0]], $ratio * $s->coords[$dimensions[1]],
			$colorGreen); */
		imagefilledellipse($im,
			$ratio * $s->coords[$dimensions[0]], $ratio * $s->coords[$dimensions[1]],
				10, 10, $colorGreen);
	}

	foreach($tests as $t) {
		if($t['result'] == false) {
			imagefilledellipse($im,
				$ratio * $t['antigen']->coords[$dimensions[0]], $ratio * $t['antigen']->coords[$dimensions[1]],
				16, 16, $colorGreen);
		}
		imagesetpixel($im,
			$ratio * $t['antigen']->coords[$dimensions[0]], $ratio * $t['antigen']->coords[$dimensions[1]],
			$colorRed);
		imagefilledellipse($im,
			$ratio * $t['antigen']->coords[$dimensions[0]], $ratio * $t['antigen']->coords[$dimensions[1]],
				8, 8, $colorRed);
	}
	
	imagerectangle($im, 0, 0, $w - 1, $h - 1, $colorBlack);

	header('Content-type: image/png');
	imagepng($im);
}