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
$runtime = array();
$memory = array();

if(DB_RECORD) {
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
	$detectorInsertStmt = $db->prepare("INSERT INTO detectors (start_id, run_id, generation, parent_id,
			centre_point_id, radius, score, overlap)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
	$testInsertStmt = $db->prepare("INSERT INTO tests (run_id, antigen_point_id, result, generation,
			detector_id)
		VALUES (?, ?, ?, ?, ?)");
	$detectorUpdStartIdStmt = $db->prepare("UPDATE detectors SET start_id = ? WHERE id = ?");
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
	
	if(($i > 0 && $i % NEXT_GEN_AFTER == 0) || $i == MAX_TESTS - 1) {
		foreach(Detector::$D as &$candidate) { // $candidates
			$candidate->overlap = $candidate->allOverlaps(Detector::$D);
		}
		unset($candidate);
		if($i != MAX_TESTS - 1) {
			Detector::sortByField(DETECTOR_SORT_FIELD);
			for($j = 0; $j < TOP_TOCLONE; $j++)
				Detector::$D[MAX_POPULATION - 1 - $j] = Detector::$D[$j]->makeClone();
			$generations[] = Detector::$D;
			$generationN++;
		}
	}
}

$runTime[] = microtime(true) - $startTime;
$memory[] = memory_get_usage();

$generationStats = array();
foreach($generations as $generationN => $generation) {
	$generationStats[$generationN]['meanCentre'] = $sumCentre = array();
	$generationStats[$generationN]['meanRadius'] =
		$generationStats[$generationN]['meanOverlap'] =
		$generationStats[$generationN]['meanScore'] = 
		$sumRadius = $sumOverlap = $sumScore = 0;
	foreach($generation as $d) {
		foreach($d->centre->coords as $n => $coord) {
			if(empty($sumCentre[$n]))
				$sumCentre[$n] = 0;
			$sumCentre[$n] += $coord;
		}
		$sumRadius += $d->radius;
		$sumOverlap += $d->overlap;
		$sumScore += $d->score;
	}
	$dCount = count($generation);
	foreach($sumCentre as $n => $coord)
		$generationStats[$generationN]['meanCentre'][$n] = $coord / $dCount;
	$generationStats[$generationN]['meanCentre'] = new Point($generationStats[$generationN]['meanCentre']);
	$generationStats[$generationN]['meanRadius'] = $sumRadius / $dCount;
	$generationStats[$generationN]['meanOverlap'] = $sumOverlap / $dCount;
	$generationStats[$generationN]['meanScore'] = $sumScore / $dCount;
}

$runTime[] = microtime(true) - $startTime;
$memory[] = memory_get_usage();


if(DB_RECORD) {
	foreach($self as $point) {
		$pointInsertStmt->execute(array('self'));
		$pointId = $db->lastInsertId();
		foreach($space as $n => $dim)
			$pointDimInsertStmt->execute(array($pointId, $dim['id'], $point->coords[$n]));
	}

	foreach($generations as $generationN => $generation) {
		foreach($generation as &$candidate) { // $candidates
			$pointInsertStmt->execute(array('detector'));
			$pointId = $db->lastInsertId();
			foreach($space as $n => $dim) {
				$pointDimInsertStmt->execute(array(
					$pointId, $dim['id'], $candidate->centre->coords[$n]));
			}
			if($candidate->parentStaticId > 0) {
				$parent = Detector::findByField('staticId', $candidate->parentStaticId);
				if(!empty($parent))
					$candidate->parentDbId = $parent->dbId;
			}
			$detectorInsertStmt->execute(array(
				$candidate->startDbId, $runId, $generationN, $candidate->parentDbId, $pointId,
				$candidate->radius, $candidate->score, $candidate->overlap
			));
			$candidate->dbId = $db->lastInsertId();
			if($candidate->startDbId == 0) {
				$candidate->startDbId = $candidate->dbId;
				$detectorUpdStartIdStmt->execute(array($candidate->dbId, $candidate->dbId));
			}
		}
		unset($candidate);
	}

	foreach($tests as $testN => $test) {
		$pointInsertStmt->execute(array('antigen'));
		$antigenId = $db->lastInsertId();
		foreach($space as $n => $dim)
			$pointDimInsertStmt->execute(array($antigenId, $dim['id'], $test['antigen']->coords[$n]));

		$testInsertStmt->execute(array(
			$runId, $antigenId, (int)$test['result'], $test['generation'],
			$generations[$test['generation']][$test['detector_n']]->dbId
		));
	}
}

$_SESSION['rns'] = array(
	'space' => $space,
	'detectors' => Detector::$D,
	'self' => $self,
	'tests' => $tests,
);

$runTime[] = microtime(true) - $startTime;
$memory[] = memory_get_usage();

if(DB_RECORD) {
	$db->query("UPDATE runs SET
		runtime_nodb = '{$runTime[0]}', memory_nodb = '{$memory[0]}',
		runtime = '{$runTime[2]}', memory = '{$memory[2]}',
		finished = '1'
		WHERE id = '$runId'");
}

foreach($memory as $n => $m)
	$memory[$n] = number_format($m / 1024, 3);

require '../template/rns.phtml';