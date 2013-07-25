<?php

class Point
{
	public
		$dimensions = DIMENSIONS,
		$coords = array();
	

	function __construct($coords)
	{
		if(!is_array($coords))
			$coords = func_get_args();
		if(count($coords) != DIMENSIONS)
			throw new Exception('Invalid dimension count');
		$this->coords = $coords;
	}


	public function __toString() {
		return '['.implode(', ', $this->coords).']';
	}
	
	
	public function formatted() {
		return '['.implode(', ',
			array_map(
				function($coord) { return number_format($coord, 3); },
				$this->coords)
			).']';
	}
	

	public function distanceFrom($point, $p=2)
	{
		if($point->dimensions != $this->dimensions)
			throw new Exception('Dimensions do not match');
		$d = 0;
		for($i = 0; $i < $this->dimensions; $i++)
			$d += pow(abs($this->coords[$i] - $point->coords[$i]), $p);
		return pow($d, 1 / $p);
	}
	

	public function moveByVector($v)
	{
		for($i=0; $i < $this->dimensions; $i++)
			$this->coords[$i] += $v->basis[$i];
		return $this;
	}


	public static function randomCoords($space, $decimals = 2, $padding = 0)
	{
		$coords = array();
		$f = pow(10, $decimals);
		for($j = 0; $j < DIMENSIONS; $j++)
			$coords[$j] = mt_rand(
				($space[$j]['min'] + $padding) * $f,
				($space[$j]['max'] + $padding) * $f) / $f;
		return $coords;
	}
}
