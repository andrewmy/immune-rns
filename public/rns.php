<?php

error_reporting(E_ALL);

define('MAX_VARIATION',  0.2);
define('MAX_OVERLAP',    0.1);
define('DIMENSIONS',     2);
define('MAX_POPULATION', 10);
define('MAX_TESTS',      20);
define('NEXT_GEN_AFTER', 5);
define('TOP_TOCLONE',    2);

include('./Point.class.php');
include('./Vector.class.php');
include('./Detector.class.php');

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


// points' coords: error kV, brake reservoire pressure
$space=array(
	array('min'=>0, 'max'=>1,  'desc'=>'error, kV'),
	array('min'=>0, 'max'=>10, 'desc'=>'air pressure, kgf/cm2')
);
Detector::$max_dim = 10;

$self=array(
	new Point(0,   5),
	new Point(0.1, 6),
	new Point(0.2, 7)
);

$generations=array();
$detections=array();
$tests=array();

Detector::generateList($space, $self);
$generations[]=Detector::$D;

for($i=0; $i < MAX_TESTS; $i++) {
	$tests[$i]=array('antigen'=>null, 'result'=>false, 'generation'=>count($generations));
	$antigen = new Point(Point::randomCoords($space));
	$tests[$i]['antigen'] = $antigen;
	foreach(Detector::$D as $n=>&$d) {
		if($d->isActivatedBy($antigen)) {
			$d->score++;
			$detections[$i][]=array('antigen'=>$antigen, 'detector'=>$d, 'detector_n'=>$n);
			$tests[$i]['result'] = true;
		}
	}
	unset($d); // reference
	if($i>0 && $i % NEXT_GEN_AFTER == 0) {
		foreach(Detector::$D as &$c) // $candidates
			$c->overlap=$c->allOverlaps(Detector::$D);
		unset($c); // reference
		Detector::sortByOverlap();
		for($j=0; $j<TOP_TOCLONE; $j++)
			Detector::$D[MAX_POPULATION-1-$j] = Detector::$D[$j]->makeClone();
		$generations[]=Detector::$D;
	}
}

include('./rns.tpl.php');

/* $w=100; $h=100;
$xr=$w/($space[0][1]-$space[0][0]);
$yr=$h/($space[1][1]-$space[1][0]);

$im = imagecreatetruecolor($w,$h);
$bg=imagecolorallocate($im,255,255,255);
imagefilledrectangle($im,0,0,$w,$h,$bg);
$fg=imagecolorallocate($im,0,0,0);
imagerectangle($im,0,0,$w-1,$h-1,$fg);

foreach(Detector::$D as $d) {
	$fg=imagecolorallocate($im,0,0,0);
	imagesetpixel($im, $xr*$d->centre->coords[0], $yr*$d->centre->coords[1], $fg);
	$fg=imagecolorallocate($im,255,0,0);
	imageellipse($im, $xr*$d->centre->coords[0], $yr*$d->centre->coords[1], $xr*$d->radius, $yr*$d->radius, $fg);
}

header('Content-type: image/png');
imagepng($im);
*/

?>