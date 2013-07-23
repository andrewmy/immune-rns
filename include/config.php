<?php

error_reporting(E_ALL);

$getSettings = !empty($_GET['settings']) ? $_GET['settings'] : array();
$settings = array(
	'max_variation'  => empty($getSettings) ? 0.2 : @$getSettings['max_variation'],
	'max_overlap'    => empty($getSettings) ? 0.1 : @$getSettings['max_overlap'],
	'dimensions'     => empty($getSettings) ? 4   : @$getSettings['dimensions'],
	'max_population' => empty($getSettings) ? 20  : @$getSettings['max_population'],
	'max_radius'     => empty($getSettings) ? 300 : @$getSettings['max_radius'],
	'max_tests'      => empty($getSettings) ? 20  : @$getSettings['max_tests'],
	'next_gen_after' => empty($getSettings) ? 5   : @$getSettings['next_gen_after'],
	'top_toclone'    => empty($getSettings) ? 4   : @$getSettings['top_toclone'],
	'detector_sort_field' => empty($getSettings) ? 'score' : @$getSettings['detector_sort_field'],
);

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

// points' coords
$space = array(
	array('min' => 0,    'max' => 100,  'desc' => 'Train speed, km/h'),
	array('min' => 0,    'max' => 150,  'desc' => 'Crossing car speed, km/h'),
	array('min' => -100, 'max' => 1000, 'desc' => 'Distance from the train to the rendezvous, m'),
	array('min' => -10,  'max' => 1000, 'desc' => 'Distance from the car to the rendezvous, m'),
);
Detector::$max_dim = 10;

$self = array(
	new Point(0,   5, 1000, 0),
	new Point(50, 30, 900,  10),
	new Point(20, 30, 600,  2)
);


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