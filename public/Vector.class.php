<?php

class Vector {
    public
		$point1,
		$point2,
		$basis=array(),
		$norm=0;


	/**
	 * Construct a vector from 2 points
	 * @param Point $p1
	 * @param Point $p2
	 */
	function __construct($p1,$p2)
	{
		$this->point1 = $p1;
		$this->point2 = $p2;
		$this->_recalc();
	}


	function __toString()
	{
		return "($this->point1, $this->point2)";
	}


	private function _recalc()
	{
		for($i=0; $i < DIMENSIONS; $i++)
			$this->basis[$i] = $this->point2->coords[$i] - $this->point1->coords[$i];
		$this->norm = $this->point1->distanceFrom($this->point2);
	}


	public function multiply($n)
	{
		$coords = $this->point2->coords;
		for($i=0; $i < $this->point2->dimensions; $i++)
			$coords[$i] = $this->point1->coords[$i] + $this->basis[$i] * $n;
		$this->point2 = new Point($coords);
		$this->_recalc();
		return $this;
	}
}
