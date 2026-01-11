<?php

class Model_Messages
{
	protected $_table; 
	
	public function getMessages($where = array())
	{
		$table = $this->_getTable();
		if(!empty($where))
		{
			$select = $table->select();
			
			foreach($where as $k => $w)
			{
				$select->where($k, $w);
			}
		}
		
		return $table->fetchAll($select)->toArray();
	}
	
	/**
     * Returns the db adapter
     *
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _getDbAdapter()
    {
        return Zend_Registry::get('dbAdapter');
    }
    
	/**
	 * Returns the table db adapter
	 *
	 * @return Model_DbTables_Messages
	 */
	protected function _getTable()
	{
		if (null === $this->_table)
		{
			// since the dbTable is not a library item but an application item,
			// we must require it to use it
			$this->_table = new Model_DbTables_Messages(array('db' => 'dbAdapter'));
		}
		return $this->_table;
	}
}