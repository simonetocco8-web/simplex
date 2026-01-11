<?php

abstract class Maco_Toy_Item_Abstract
{
	/**
	 * the value for this item
	 *
	 * @var string
	 */
	protected $_label = '';

	/**
	 * the label for this item
	 *
	 * @var string
	 */
	protected $_value = '';

	abstract public function renderDetail();

	abstract public function renderEdit();

	public function renderInList()
	{
		$this->renderDetail();
	}
}

