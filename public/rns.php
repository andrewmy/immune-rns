<?php

$startTime = microtime(true);

require_once '../include/config.php';

/**
 * TODO:
 * - profile (currently 0)
 */

$generations = array();
$detections = array();
$tests = array();

Detector::generateList($space, $self, PAD_DETECTORS);
$generations[] = Detector::$D;

for($i = 0; $i < MAX_TESTS; $i++) {
	$tests[$i] = array(
		'antigen' => null, 'result' => false, 'generation' => count($generations), 'detector_n' => 0
	);
	$antigen = new Point(Point::randomCoords($space));
	$tests[$i]['antigen'] = $antigen;
	
	foreach(Detector::$D as $n => &$d) {
		if($d->isActivatedBy($antigen)) {
			$d->score++;
			$detections[$i][] = array('antigen' => $antigen, 'detector' => $d, 'detector_n' => $n);
			$tests[$i]['detector_n'] = $n;
			$tests[$i]['result'] = true;
		}
	}
	unset($d);
	
	if($i > 0 && $i % NEXT_GEN_AFTER == 0) {
		foreach(Detector::$D as &$c) // $candidates
			$c->overlap = $c->allOverlaps(Detector::$D);
		unset($c);
		Detector::sortByField(DETECTOR_SORT_FIELD);
		for($j = 0; $j < TOP_TOCLONE; $j++)
			Detector::$D[MAX_POPULATION - 1 - $j] = Detector::$D[$j]->makeClone();
		$generations[] = Detector::$D;
	}
}

$_SESSION['rns'] = array(
	'space' => $space,
	'detectors' => Detector::$D,
	'self' => $self,
	'tests' => $tests,
);

$runTime = microtime(true) - $startTime;

require '../template/rns.phtml';