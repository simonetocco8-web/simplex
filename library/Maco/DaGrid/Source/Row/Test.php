<?php

class Maco_DaGrid_Source_Row_Test implements Maco_DaGrid_Source_Row_Interface
{
	public function getCells()
	{
		return array(0, 1, 2, 3, 4);
	}
}