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
	$wSpace = abs($space[$dimensions[0]]['max'] - $space[$dimensions[0]]['min']);
	$hSpace = abs($space[$dimensions[1]]['max'] - $space[$dimensions[1]]['min']);
	$wRatio = $w / $wSpace;
	$hRatio = $h / $hSpace;
	$ratio = min($wRatio, $hRatio);

	$im = imagecreatetruecolor($w, $h);
	$bg = imagecolorallocate($im, 255, 255, 255);
	imagefilledrectangle($im, 0, 0, $w, $h, $bg);
	$colorBlack = imagecolorallocate($im, 0, 0, 0);
	$colorRed = imagecolorallocate($im, 255, 0, 0);
	$colorGreen = imagecolorallocate($im, 0, 255, 0);
	$colorBlue = imagecolorallocate($im, 224, 224, 255);
	$colorDarkBlue = imagecolorallocate($im, 0, 0, 255);
	$colorGray = imagecolorallocate($im, 224, 224, 224);
	$colorYellow = imagecolorallocate($im, 255, 255, 0);

	foreach($detectors as $d) {
		imagefilledellipse($im,
			$ratio * $d->centre->coords[$dimensions[0]], $ratio * $d->centre->coords[$dimensions[1]],
			$ratio * $d->radius, $ratio * $d->radius, $colorBlue);
		/* imagesetpixel($im,
			$ratio * $d->centre->coords[$dimensions[0]], $ratio * $d->centre->coords[$dimensions[1]],
			$colorDarkBlue); */
		imagefilledellipse($im,
			$ratio * $d->centre->coords[$dimensions[0]], $ratio * $d->centre->coords[$dimensions[1]],
				6, 6, $colorDarkBlue);
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
		/* if($t['result'] == false) {
			imagefilledellipse($im,
				$ratio * $t['antigen']->coords[$dimensions[0]], $ratio * $t['antigen']->coords[$dimensions[1]],
				16, 16, $colorGreen);
		} */
		/* imagesetpixel($im,
			$ratio * $t['antigen']->coords[$dimensions[0]], $ratio * $t['antigen']->coords[$dimensions[1]],
			$colorRed); */
		imagefilledellipse($im,
			$ratio * $t['antigen']->coords[$dimensions[0]], $ratio * $t['antigen']->coords[$dimensions[1]],
				8, 8, $t['result'] ? $colorRed : $colorYellow);
	}
	
	// grey out limits
	if($wRatio < $hRatio) { // x axis is full, draw limit on y
		imagefilledrectangle($im, 0, $hSpace * $ratio, $w - 1, $h - 1, $colorGray);
	} else { // y axis is full, draw limit on x
		imagefilledrectangle($im, $wSpace * $ratio, 0, $w - 1, $h - 1, $colorGray);
	}
	
	// axis
	for($pixelsPerUnit = 15; $pixelsPerUnit <= 40; $pixelsPerUnit++) {
		$tmp = floor($wSpace * $ratio / $pixelsPerUnit);
		if($tmp == 0)
			$tmp = 1;
		$wUnitsPerUnit = $wSpace / $tmp;
		$tmp = floor($hSpace * $ratio / $pixelsPerUnit);
		if($tmp == 0)
			$tmp = 1;
		$hUnitsPerUnit = $hSpace / $tmp;
		if(floor($wUnitsPerUnit * 10) % 50 == 0
				&& floor($hUnitsPerUnit * 10) % 50 == 0)
			break;
	}
	$wUnitsPerUnit = floor($wUnitsPerUnit);
	$hUnitsPerUnit = floor($hUnitsPerUnit);
	$yAxisX = $xAxisY = 0;
	if($space[$dimensions[0]]['min'] < 0) { // y
		$yAxisX = $space[$dimensions[0]]['min'] * $ratio * -1;
		imageline($im, $yAxisX, 0, $yAxisX, $h - 1, $colorBlack);
	}
	if($space[$dimensions[1]]['min'] < 0) { // x
		$xAxisY = $space[$dimensions[1]]['min'] * $ratio * -1;
		imageline($im, 0, $xAxisY, $w - 1, $xAxisY, $colorBlack);
	}
	for($x = $yAxisX, $i = 0; $x < $w; $x += $pixelsPerUnit, $i++) {
		imageline($im, $x, $xAxisY - 3, $x, $xAxisY + 3, $colorBlack);
		imagestring($im, 1, $x, $xAxisY >= 13 ? $xAxisY - 13 : $xAxisY + 6,
			($i * $wUnitsPerUnit), $colorBlack);
	}
	for($x = $yAxisX, $i = 0; $x > 0; $x -= $pixelsPerUnit, $i--) {
		imageline($im, $x, $xAxisY - 3, $x, $xAxisY + 3, $colorBlack);
		imagestring($im, 1, $x, $xAxisY >= 13 ? $xAxisY - 13 : $xAxisY + 6,
			($i * $wUnitsPerUnit), $colorBlack);	
	}
	for($y = $xAxisY, $i = 0; $y < $h; $y += $pixelsPerUnit, $i++) {
		imageline($im, $yAxisX - 3, $y, $yAxisX + 3, $y, $colorBlack);
		if($i > 0)
			imagestring($im, 1, $yAxisX <= $w - 16 ? $yAxisX + 6 : $yAxisX - 16, $y - 4,
				($i * $hUnitsPerUnit), $colorBlack);
	}
	for($y = $xAxisY, $i = 0; $y > 0; $y -= $pixelsPerUnit, $i--) {
		imageline($im, $yAxisX - 3, $y, $yAxisX + 3, $y, $colorBlack);
		if($i < 0)
			imagestring($im, 1, $yAxisX <= $w - 16 ? $yAxisX + 6 : $yAxisX - 16, $y - 4,
				($i * $hUnitsPerUnit), $colorBlack);
	}
	
	// border
	imagerectangle($im, 0, 0, $w - 1, $h - 1, $colorBlack);

	header('Content-type: image/png');
	imagepng($im);
}