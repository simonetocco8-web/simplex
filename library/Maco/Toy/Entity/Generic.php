<?php

class Maco_Toy_Entity_Generic extends Maco_Toy_Entity_Abstract
{
	/**
	 * the label for this entity
	 *
	 * @var string
	 */
	protected $_label = "";

	/**
	 * The data
	 *
	 * @var array
	 */
	protected $_data;

	public function renderDetail()
	{
		// load the data
		$this->loadDataFromSource();

		$output = '';

		if($this->_label != "")
		{
			$output .= '<h3>' . $this->_label . '</h3>';
		}

		foreach($this->_items as $i => $item)
		{
			$output .= '<dl class="detail">' . $item->renderDetail() . '</dl>';
		}

		return $output;
	}

	public function renderInList()
	{
		$output = '';
		$output .= '<tr>';

		$itms = (!empty($this->_itemsInList)) ? $this->_itemsInList : $this->_items;

		foreach($itms as $i => $item)
		{
			$output .= '<td>' . $item->renderInList() . '</td>';
		}

		$output .= '</tr>';
	}

	protected function loadDataFromSource()
	{
		if($this->_source === NULL)
		{
			throw new Exception('A source need to be supplied!');
		}

		if($this->_data === NULL)
		{
			$this->_data = $this->_source->load();

			foreach($this->_data as $label => $value)
			{
				// if $label is numeric we suppose is not an associative array
				// item so we don't create a label for it
				// TODO: check if it's the right thing to do
				$label = is_int($label) ? '' : $label;
				$this->_items[] = new Maco_Toy_Item_Generic($value, $label);
			}
		}
	}

	public function setLabel($label)
	{
		$this->_label = $label;
	}

	public function renderEdit()
	{
		throw new Exception('TODO');
	}

	public function __toString()
	{
		return $this->renderDetail();
	}
}
