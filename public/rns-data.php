<?php

require_once './config.php';

// points' coords
$space = array(
	array('min'=>0,    'max'=>100,  'desc'=>'Train speed, km/h'),
	array('min'=>0,    'max'=>150,  'desc'=>'Crossing car speed, km/h'),
	array('min'=>-100, 'max'=>1000, 'desc'=>'Distance from the train to the rendezvous, m'),
	array('min'=>-10,  'max'=>1000, 'desc'=>'Distance from the car to the rendezvous, m'),
);
Detector::$max_dim = 10;

$self = array(
	new Point(0,   5, 1000, 0),
	new Point(50, 30, 900,  10),
	new Point(20, 30, 600,  2)
);

$generations = array();
$detections = array();
$tests = array();

Detector::generateList($space, $self);
$generations[] = Detector::$D;

for($i = 0; $i < MAX_TESTS; $i++) {
	$tests[$i] = array('antigen' => null, 'result' => false, 'generation' => count($generations));
	$antigen = new Point(Point::randomCoords($space));
	$tests[$i]['antigen'] = $antigen;
	
	foreach(Detector::$D as $n => &$d) {
		if($d->isActivatedBy($antigen)) {
			$d->score++;
			$detections[$i][] = array('antigen' => $antigen, 'detector' => $d, 'detector_n' => $n);
			$tests[$i]['result'] = true;
		}
	}
	unset($d);
	
	if($i>0 && $i % NEXT_GEN_AFTER == 0) {
		foreach(Detector::$D as &$c) // $candidates
			$c->overlap = $c->allOverlaps(Detector::$D);
		unset($c);
		Detector::sortByOverlap();
		for($j=0; $j < TOP_TOCLONE; $j++)
			Detector::$D[MAX_POPULATION - 1 - $j] = Detector::$D[$j]->makeClone();
		$generations[] = Detector::$D;
	}
}

if(isset($_GET['graph'])) {
	$w = 100;
	$h = 100;
	$xr = $w / ($space[0][1] - $space[0][0]);
	$yr = $h / ($space[1][1] - $space[1][0]);

	$im = imagecreatetruecolor($w, $h);
	$bg = imagecolorallocate($im, 255, 255, 255);
	imagefilledrectangle($im, 0, 0, $w, $h, $bg);
	$colorBlack = imagecolorallocate($im, 0, 0, 0);
	$colorRed = imagecolorallocate($im, 255, 0, 0);
	imagerectangle($im, 0, 0, $w - 1, $h - 1, $colorBlack);

	foreach(Detector::$D as $d) {
		imagesetpixel($im, $xr * $d->centre->coords[0], $yr * $d->centre->coords[1], $colorBlack);
		imageellipse($im, $xr * $d->centre->coords[0], $yr * $d->centre->coords[1],
			$xr * $d->radius, $yr * $d->radius, $colorRed);
	}

	header('Content-type: image/png');
	imagepng($im);
} else
	 require './rns.tpl.php';