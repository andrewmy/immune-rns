<?php

error_reporting(E_ALL);

define('MAX_VARIATION',  0.2);
define('MAX_OVERLAP',    0.1);
define('DIMENSIONS',     4);
define('MAX_POPULATION', 20);
define('MAX_RADIUS',     300);
define('MAX_TESTS',      20);
define('NEXT_GEN_AFTER', 5);
define('TOP_TOCLONE',    4);
define('DETECTOR_SORT_FIELD', 'score');

require_once '../include/Point.class.php';
require_once '../include/Vector.class.php';
require_once '../include/Detector.class.php';

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