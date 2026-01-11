<?php

abstract class Maco_Toy_Entity_Abstract
{
	/**
	 * List of items for thie entity
	 *
	 * @var array
	 */
	protected $_items = array();

	/**
	 * The source adapter
	 *
	 * @var Maco_Toy_Entity_Source_Interface
	 */
	protected $_source;

	/**
	 * Items in list
	 *
	 * @var array
	 */
	protected $_itemsInList = array();

	abstract public function renderDetail();

	abstract public function renderEdit();

	abstract protected function loadDataFromSource();

	public function renderInList()
	{
		$this->renderDetail();
	}

	public function setSource(Maco_Toy_Entity_Source_Interface $source)
	{
		$this->_source = $source;
	}
}
