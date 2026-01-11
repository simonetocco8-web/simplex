<?php

class Maco_DaGrid_Source_Test implements Maco_DaGrid_Source_Interface
{
	public function getRows()
	{
		return array(
		new Maco_DaGrid_Source_Row_Test(),
		new Maco_DaGrid_Source_Row_Test(),
		);
	}
}
