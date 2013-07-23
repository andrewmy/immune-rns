<?php

require_once '../include/config.php';

$dimensions = @$_GET['dimensions'];

extract($_SESSION['rns']);

if(empty($dimensions)
		|| empty($detectors) || empty($space) || empty($self) || empty($tests)
		|| count($dimensions) != 2)
	die('Invalid data');

$w = 600;
$h = 600;
$ratio = min(array(
	$w / ($space[$dimensions[0]]['max'] - $space[$dimensions[0]]['min']),
	$h / ($space[$dimensions[1]]['max'] - $space[$dimensions[1]]['min'])
));

//var_dump(get_defined_vars()); die;

$im = imagecreatetruecolor($w, $h);
$bg = imagecolorallocate($im, 255, 255, 255);
imagefilledrectangle($im, 0, 0, $w, $h, $bg);
$colorBlack = imagecolorallocate($im, 0, 0, 0);
$colorRed = imagecolorallocate($im, 255, 0, 0);
$colorGreen = imagecolorallocate($im, 0, 192, 0);
$colorBlue = imagecolorallocate($im, 0, 0, 255);
imagerectangle($im, 0, 0, $w - 1, $h - 1, $colorBlack);

foreach($detectors as $d) {
	imagesetpixel($im,
		$ratio * $d->centre->coords[$dimensions[0]], $ratio * $d->centre->coords[$dimensions[1]],
		$colorBlue);
	imageellipse($im,
		$ratio * $d->centre->coords[$dimensions[0]], $ratio * $d->centre->coords[$dimensions[1]],
		$ratio * $d->radius, $ratio * $d->radius, $colorBlue);
}

foreach($self as $s) {
	imagesetpixel($im,
		$ratio * $s->coords[$dimensions[0]], $ratio * $s->coords[$dimensions[1]],
		$colorGreen);
	imageellipse($im,
		$ratio * $s->coords[$dimensions[0]], $ratio * $s->coords[$dimensions[1]],
		3, 3, $colorGreen);
}

foreach($tests as $t) {
	imagesetpixel($im,
		$ratio * $t['antigen']->coords[$dimensions[0]], $ratio * $t['antigen']->coords[$dimensions[1]],
		$colorRed);
	imageellipse($im,
		$ratio * $t['antigen']->coords[$dimensions[0]], $ratio * $t['antigen']->coords[$dimensions[1]],
		3, 3, $colorRed);
	if($t['result'] == false)
		imageellipse($im,
			$ratio * $t['antigen']->coords[$dimensions[0]], $ratio * $t['antigen']->coords[$dimensions[1]],
			6, 6, $colorGreen);
}

header('Content-type: image/png');
imagepng($im);