<?php

interface Maco_DaGrid_Source_Interface
{
	public function getRows();

	public function getCount();

	public function getColumns();

	public function setGrid(Maco_DaGrid_Grid &$grid);

	public function setPaginator(&$paginator);
}
