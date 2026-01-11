<?php

interface Maco_DaGrid_Render_Interface
{
	/**
	 * Sets the columns for this grid renderer
	 *
	 * @param array $columns
	 */
	public function setColumns($columns);

	/**
	 * Sets the source for this grid renderer
	 *
	 * @param Maco_DaGrid_Source_Interface $source
	 */
	public function setSource(&$source);

	/**
	 * Render the grid
	 *
	 * @param string $template
	 */
	public function render($template);

	/**
	 * Return the sort field label for the request data
	 *
	 * @return string
	 */
	public function getSortFieldLabel();

	/**
	 * Return the sort fir label for the request data
	 *
	 * @return string
	 */
	public function getSortDirLabel();

	/**
	 * Clean the Uri and returns it
	 *
	 * @param string $uri
	 * @return string
	 */
	public function getCleanUri($uri);

	/**
	 * Sets the paginator object
	 *
	 * @var Maco_DaGrid_Paginator_Default $paginator
	 */
	public function setPaginator(&$paginator);

	public function setGrid(Maco_DaGrid_Grid &$grid);
}
