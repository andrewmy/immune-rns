<?php

class Detector
{
	public 
		$centre,
		$radius = 0,
		$score = 0,
		$overlap = 0,
		$staticId = 0,
		$parentStaticId = 0,
		$dbId = 0,
		$startDbId = 0,
		$parentDbId = 0;

	public static
		$D = array(),
		$S = array(),
		$max_dim = 0,
		$count = 0;
	

	/**
	 * Construct a Detector antibody
	 * @param Point/array $centre
	 * @param bool $autoradius
	 */
	function __construct($centre, $autoradius=true)
	{
		if(!($centre instanceof Point)) {
			if(!is_array($centre))
				$centre = func_get_args();
			$centre = new Point($centre);
		}
		$this->centre = $centre;
		if($autoradius)
			$this->setAutoRadius();
		self::$count++;
		$this->staticId = self::$count;
	}


	function  __toString() {
		return "(c: {$this->centre->formatted()}, ".
			"R: $this->radius, overlap: $this->overlap, score: $this->score)";
	}


	private function setAutoRadius()
	{
		$radius = $this->getNearestPoint()->distanceFrom($this->centre) - MAX_VARIATION;
		$this->radius = MAX_RADIUS > 0
			? min(array(MAX_RADIUS, $radius))
			: $radius;
		return $this;
	}
	

	public function moveFrom($nearest)
	{
		$nearest_centre = ($nearest instanceof Detector)
			? $nearest->centre
			: $nearest; // instanceof Point
		$v = new Vector($this->centre, $nearest_centre);
		$v->multiply(-1);
		$this->centre = $this->centre->moveByVector($v->multiply(self::varMovement() / $v->norm));
	}
	

	public function makeClone()
	{
		$new_centre = $this->getNearestDetector()->centre;
		$v = new Vector($this->centre, $new_centre);
		$v->multiply(-1);
		if($v->norm == 0) // FIXME
			throw new Exception('Cloned detector is in the same location as parent');
		$new_centre = $new_centre->moveByVector($v->multiply($this->radius / $v->norm));
		$d = new Detector($new_centre);
		$d->radius = $this->radius;
		$d->parentStaticId = $this->staticId;
		$d->parentDbId = $this->dbId;
		return $d;
	}
	

	public function allOverlaps($list)
	{
		$sum=0;
		//  FIXME: detector equality
		foreach($list as $item) {
			$p = ($item instanceof Detector)
				? $item->centre
				: $item;
			$radius=($item instanceof Detector)
				? $item->radius
				: MAX_VARIATION;
			if($p != $this->centre && $this->radius != $radius) // $d->r ?
				$sum += $this->overlap($p, $radius);
		}
		return $sum;
	}
	

	public function overlap($p, $r)
	{
		return pow(
			exp(
				($this->radius + $r - $this->centre->distanceFrom($p)) /
				(2*$this->radius)
			) - 1,
			$this->centre->dimensions
		);
	}
	

	public function isActivatedBy($p)
	{
		$d = $this->centre->distanceFrom($p);
		return ($d < $this->radius + MAX_VARIATION);
	}


	public function getNearestPoint()
	{
		$lowest = pow(self::$max_dim, DIMENSIONS); //0;
		$lowest_i = 0;
		foreach(self::$S as $i=>$p)
			if($this->centre->distanceFrom($p) < $lowest && $this->centre->distanceFrom($p) > 0) {
				$lowest = $this->centre->distanceFrom($p);
				$lowest_i = $i;
			}
		return self::$S[$lowest_i];
	}


	public function getNearestDetector()
	{
		$lowest = pow(self::$max_dim, DIMENSIONS); // 0
		$lowest_i = 0;
		foreach(self::$D as $i=>$d)
			if($this->centre->distanceFrom($d->centre) < $lowest
					&& $this->centre->distanceFrom($d->centre) > 0) {
				$lowest = $this->centre->distanceFrom($d->centre);
				$lowest_i=$i;
			}
		return self::$D[$lowest_i];
	}
	

	public static function varMovement()
	{
		return mt_rand(0, 5) / 10;
		// TODO: exponential decay function
	}
	

	public static function generateList($space, $self, $padding = false)
	{
		self::$S = $self;
		$candidates = array();
		for($i = 0; count($candidates) < MAX_POPULATION; $i++) {
			$d = new Detector(new Point(Point::randomCoords($space)));
			//echo "new detector $d<br>";
			if($d->radius < 0)
				continue; // discard
			if($padding)
				foreach($space as $n => $dimension) {
					if(abs($dimension['max'] - $dimension['min']) >= $d->radius * 2) {
						if($d->centre->coords[$n] - $d->radius < $dimension['min'])
							$d->centre->coords[$n] = $dimension['min'] + $d->radius;
						if($d->centre->coords[$n] + $d->radius > $dimension['max'])
							$d->centre->coords[$n] = $dimension['max'] - $d->radius;
					}
				}
			if(!empty($candidates)) foreach($candidates as $c) {
				if($d->overlap($c->centre, $c->radius) > MAX_OVERLAP) {
					//echo "overlap by ".$d->overlap($c->centre, $c->radius).", moving from $c -> ";
					$d->moveFrom($c);
					//echo "$d<br>";
				}
			}
			$candidates[] = $d;
		}
		foreach($candidates as $c)
			$c->overlap = $c->allOverlaps($candidates);
		self::$D = $candidates;
	}
	
	
	public static function sortByField($field = 'overlap')
	{
		$arr = array();
		foreach(self::$D as $n => $d) {
			$fieldName = (string)$d->$field;
			// prevent duplicates. if value is the same, priority to the first
			if($field == 'score')
				$fieldName .= '.'.$n;
			$arr[$fieldName] = $d;
		}
		if($field == 'score')
			krsort($arr, SORT_NUMERIC);
		else
			ksort($arr, SORT_NUMERIC);
		self::$D = array();
		foreach($arr as $d)
			self::$D[] = $d;
	}
	
	
	public static function findByField($field, $value)
	{
		foreach(self::$D as $d) {
			if($d->$field == $value)
				return $d;
		}
		return false;
	}
}
