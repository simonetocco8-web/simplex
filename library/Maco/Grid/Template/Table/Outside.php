<?php

/**
 *
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License 2.0
 * It is  available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/gpl-2.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Bvb_Grid
 * @copyright  Copyright (c)  (http://www.petala-azul.com)
 * @license    http://www.opensource.org/licenses/gpl-2.0.php   GNU General Public License 2.0
 * @version    0.1
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */


class Maco_Grid_Template_Table_Outside extends Bvb_Grid_Template_Table_Table
{

	public $ic;

	public $insideLoop;

	public $go = 0;

	protected $_c = 0;
	protected $_oi = -1;

	function globalStart()
	{
		return "This template comes from My/Template/Table/Outside.php
        <table id=\"newGrid\" width=\"100%\"  align=\"center\" cellspacing=\"1\" >";
	}


	function loopStart($values)
	{
		$this->i++;
		return "<tr  >";
	}


	function loopLoop()
	{
		if($this->_oi != $this->i)
		{
			$this->_c = 0;
			$this->_oi = $this->i;
		}

		$class = $this->i % 2 ? "" : "alt";
		$tag = 'td';

		if($this->_c++ == 0)
		{
			$tag = 'th';
			$class = 'spec' . $class;
		}

		return "<$tag  class=\"$class {{class}}\" >{{value}}&nbsp;</$tag>";
	}


}

