<?php

set_time_limit(0);

$startTime = microtime(true);

require_once '../include/config.php';

/**
 * TODO:
 * - 
 */

$generations = array();
$detections = array();
$tests = array();

$db = new PDO("mysql:host=localhost;dbname=rtu_rns", 'root', '');
$db->query('SET NAMES utf8');

$db->query("INSERT INTO `runs`
	(date_created, pad_detectors, max_variation, max_overlap, dimensions,
	max_population, max_radius, max_tests, next_gen_after, top_toclone,
	detector_sortfield, max_dim, autoradius, start_padding, runtime, finished)
	VALUES (NOW(), '".PAD_DETECTORS."', '".MAX_VARIATION."', '".MAX_OVERLAP."', '".DIMENSIONS."',
		'".MAX_POPULATION."', '".MAX_RADIUS."', '".MAX_TESTS."', '".NEXT_GEN_AFTER."', '".TOP_TOCLONE."',
		'".DETECTOR_SORT_FIELD."', '".Detector::$max_dim."', '1', '1', '', '')");
$runId = $db->lastInsertId();

foreach($space as $n => $dim) {
	$db->query("INSERT INTO dimensions (run_id, `min`, `max`, description)
		VALUES ('$runId', '{$dim['min']}', '{$dim['max']}', ".$db->quote($dim['desc']).")");
	$space[$n]['id'] = $db->lastInsertId();
}

$pointInsertStmt = $db->prepare("INSERT INTO points (run_id, type) VALUES ('$runId', ?)");
$pointDimInsertStmt = $db->prepare("INSERT INTO point_dimensions (point_id, dimension_id, `value`)
	VALUES (?, ?, ?)");

foreach($self as $point) {
	//$db->query("INSERT INTO points (run_id, type) VALUES ('$runId', 'self')");
	$pointInsertStmt->execute(array('self'));
	$pointId = $db->lastInsertId();
	foreach($space as $n => $dim) {
		//$db->query("INSERT INTO point_dimensions (point_id, dimension_id, `value`)
		//	VALUES ('$pointId', '{$dim['id']}', '{$point->coords[$n]}')");
		$pointDimInsertStmt->execute(array($pointId, $dim['id'], $point->coords[$n]));
	}
}

Detector::generateList($space, $self, PAD_DETECTORS);
$generations[] = Detector::$D;
$generationN = 0;

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
	
	//$db->query("INSERT INTO points (run_id, type) VALUES ('$runId', 'antigen')");
	$pointInsertStmt->execute(array('antigen'));
	$antigenId = $db->lastInsertId();
	foreach($space as $n => $dim) {
		//$db->query("INSERT INTO point_dimensions (point_id, dimension_id, `value`)
		//	VALUES ('$antigenId', '{$dim['id']}', '{$antigen->coords[$n]}')");
		$pointDimInsertStmt->execute(array($antigenId, $dim['id'], $antigen->coords[$n]));
	}
	
	foreach(Detector::$D as &$d) {
		//$db->query("INSERT INTO points (run_id, type) VALUES ('$runId', 'detector')");
		$pointInsertStmt->execute(array('detector'));
		$pointId = $db->lastInsertId();
		foreach($space as $n => $dim) {
			//$db->query("INSERT INTO point_dimensions (point_id, dimension_id, `value`)
			//	VALUES ('$pointId', '{$dim['id']}', '{$d->centre->coords[$n]}')");
			$pointDimInsertStmt->execute(array($pointId, $dim['id'], $d->centre->coords[$n]));
		}
		$db->query("INSERT INTO detectors (run_id, generation, parent_id, centre_point_id,
				radius, score, overlap)
			VALUES ('$runId', '$generationN', '{$d->parentDbId}', '{$pointId}',
				'{$d->radius}', '{$d->score}', '{$d->overlap}')");
		$d->dbId = $db->lastInsertId();
	}
	unset($d);
	
	$db->query("INSERT INTO tests (run_id, antigen_point_id, result, generation,
			detector_id)
		VALUES ('$runId', '$antigenId', '".(int)$tests[$i]['result']."', '$generationN',
			'".Detector::$D[$tests[$i]['detector_n']]->dbId."')");
	
	if($i > 0 && $i % NEXT_GEN_AFTER == 0) {
		foreach(Detector::$D as &$c) // $candidates
			$c->overlap = $c->allOverlaps(Detector::$D);
		unset($c);
		Detector::sortByField(DETECTOR_SORT_FIELD);
		for($j = 0; $j < TOP_TOCLONE; $j++)
			Detector::$D[MAX_POPULATION - 1 - $j] = Detector::$D[$j]->makeClone();
		$generations[] = Detector::$D;
		$generationN++;
	}
}

$_SESSION['rns'] = array(
	'space' => $space,
	'detectors' => Detector::$D,
	'self' => $self,
	'tests' => $tests,
);

$runTime = microtime(true) - $startTime;

$db->query("UPDATE runs SET runtime = '$runTime', finished = '1' WHERE id = '$runId'");

require '../template/rns.phtml';