<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 7-set-2010
 * Time: 18.25.09
 * To change this template use File | Settings | File Templates.
 */
 
class Maco_Model_Mapper_Abstract
{
    /**
     * @var Model_DbTables_Tasks
     */
    protected $_dbTable;

    /**
     * @var Model_DbTables_Tasks
     */
    protected $_dbTableName;

    /**
     * @var Model_DbTables_Tasks
     */
    protected $_modelName;
    
    public function find($id)
    {
        $item = new $this->_modelName();

        $table = $this->getTable();
        $itemRow = $table->find($id)->current();

        if($itemRow)
        {
            $item->setData($itemRow->toArray());
        }

        return $item;
    }

    public function delete($id)
    {
        $table = $this->getTable();

        $primaryKey = $table->info(Zend_Db_Table_Abstract::PRIMARY);
        $primaryKey = reset($primaryKey);

        return $table->delete($primaryKey . ' = ' . $table->getAdapter()->quote($id));
    }

    public function save($item)
    {
		$data = $item->getValid();

        $table = $this->getTable();

        // todo: supponiamo che la chiave sia unica (non array)
        $primaryKey = $table->info(Zend_Db_Table_Abstract::PRIMARY);
        $primaryKey = reset($primaryKey);

        $id = $data[$primaryKey];
        unset($data[$primaryKey]);

        if(isset($id) && $id != '')
        {
            $table->update($data, array($primaryKey . ' = ?' => $id));

            return $id;
        }
        else
        {
            return $table->insert($data);
        }
    }
    
     public function saveWithId($item)
    {
        $data = $item->getValid();

        $table = $this->getTable();
        
        return $table->insert($data);
    }

    /**
     * @return Model_DbTables_Tasks
     */
    public function getTable()
    {
        if(!isset($this->_dbTable))
        {
            $this->_dbTable = new $this->_dbTableName();
        }
        return $this->_dbTable;
    }

    /**
     * Returns the db adapter
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDbAdapter()
    {
        return Zend_Registry::get('dbAdapter');
    }
}