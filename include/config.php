<?php

error_reporting(E_ALL);

$getSettings = !empty($_GET['settings']) ? $_GET['settings'] : array();
$getSpace = !empty($_GET['space']) ? $_GET['space'] : array();
$getSelf = !empty($_GET['self']) ? $_GET['self'] : array();

// points' coords
$space = array(
	array('min' => 0,    'max' => 100,  'desc' => 'Train speed, km/h'),
	array('min' => 0,    'max' => 150,  'desc' => 'Crossing car speed, km/h'),
	array('min' => -20,  'max' => 20,   'desc' => 'Railway slope, %'),
	array('min' => -20,  'max' => 20,   'desc' => 'Road slope, %'),
	array('min' => -100, 'max' => 1000, 'desc' => 'Distance from the train to the rendezvous, m'),
	array('min' => -10,  'max' => 1000, 'desc' => 'Distance from the car to the rendezvous, m'),
	array('min' => -50,  'max' => 60,   'desc' => 'Air temperature, °C'),
	array('min' => 0,    'max' => 100,  'desc' => 'Relative humidity, %'),
);

if(!empty($getSpace))
	foreach($getSpace as $n => $dim) {
		$space[$n]['min'] = (int)@$dim['min'];
		$space[$n]['max'] = (int)@$dim['max'];
	}

$settings = array(
	'pad_detectors'  => empty($getSettings) ? true : (int)@$getSettings['pad_detectors'],
	'db_record'      => empty($getSettings) ? true : (int)@$getSettings['db_record'],
	'max_variation'  => empty($getSettings) ? 0.2 : @$getSettings['max_variation'],
	'max_overlap'    => empty($getSettings) ? 0.1 : @$getSettings['max_overlap'],
	'dimensions'     => count($space),
	'max_population' => empty($getSettings) ? 20  : @$getSettings['max_population'],
	'max_radius'     => empty($getSettings) ? 300 : @$getSettings['max_radius'],
	'max_tests'      => empty($getSettings) ? 20  : @$getSettings['max_tests'],
	'next_gen_after' => empty($getSettings) ? 5   : @$getSettings['next_gen_after'],
	'top_toclone'    => empty($getSettings) ? 4   : @$getSettings['top_toclone'],
	'detector_sort_field' => empty($getSettings) ? 'score' : @$getSettings['detector_sort_field'],
);

define('PAD_DETECTORS',  $settings['pad_detectors']);
define('DB_RECORD',      $settings['db_record']);
define('MAX_VARIATION',  $settings['max_variation']);
define('MAX_OVERLAP',    $settings['max_overlap']);
define('DIMENSIONS',     $settings['dimensions']);
define('MAX_POPULATION', $settings['max_population']);
define('MAX_RADIUS',     $settings['max_radius']);
define('MAX_TESTS',      $settings['max_tests']);
define('NEXT_GEN_AFTER', $settings['next_gen_after']);
define('TOP_TOCLONE',    $settings['top_toclone']);
define('DETECTOR_SORT_FIELD', $settings['detector_sort_field']);

require_once '../include/Point.class.php';
require_once '../include/Vector.class.php';
require_once '../include/Detector.class.php';

Detector::$max_dim = 10;

$selfData = empty($getSelf)
	? array(
		array(0,   5, 0,  0,  1000, 0,   20, 50),
		array(50, 30, 0,  0,  900,  100, 20, 50),
		array(20, 30, 0,  0,  600,  100, 20, 50),
		array(0,   5, 5,  0, 1000, 0,    20, 50),
		array(40, 25, 5,  0, 900,  100,  20, 50),
		array(20, 30, 5,  0, 600,  100,  20, 50),
		array(0,   5, -5, 0, 1000, 0,    20, 50),
		array(60, 35, -5, 0, 900,  100,  20, 50),
		array(20, 30, -5, 0, 600,  100,  20, 50),
	)
	: $getSelf;
$self = array();
foreach($selfData as $row) {
	foreach($row as $n => &$value) {
		if($value < $space[$n]['min'])
			$value = $space[$n]['min'];
		elseif($value > $space[$n]['max'])
			$value = $space[$n]['max'];
	}
	unset($value);
	$self[] = new Point($row);
}


if(have_xdebug()) {
	ini_set('html_errors',1);
	ini_set('xdebug.collect_vars', 'on');
	ini_set('xdebug.collect_params', '4');
	ini_set('xdebug.dump_globals', 'on');
	ini_set('xdebug.dump.GET', '*');
	ini_set('xdebug.dump.POST', '*');
	ini_set('xdebug.dump.SESSION', '');
	ini_set('xdebug.show_local_vars', 'on');
	ini_set('xdebug.var_display_max_depth', '999');
	ini_set('xdebug.profiler_enable_trigger', 1); // request XDEBUG_PROFILE
}

session_start();


function have_xdebug()
{
	return function_exists('xdebug_var_dump');
}


function printr($var,$h='',$return=false)
{
	$str='';
	if($h)
		$str.='<h1>'.$h.'</h1>';
	$str.=PHP_EOL.'<pre>';
	if(have_xdebug()) {
		ob_start();
		xdebug_var_dump($var);
		$str .= ob_get_clean();
	}
	else
		$str .= print_r($var,true);
	$str .= '</pre>'.PHP_EOL.PHP_EOL;
	if($return)
		return $str;
	else
		echo $str;
}