<?php

class Maco_Db_Helper
{
    /**
    * Zend_Db_Adapter_Abstract object
    * 
    * @var Zend_Db_Adapter_Abstract
    */
    protected $_db;
    
    protected $_inputUtils;
    
    /**
    * Costruttore
    * 
    * @param Zend_Db_Adapter_Abstract $db
    * @return Maco_Db_LinkerNN
    */
    public function __construct(Zend_Db_Adapter_Abstract &$db)
    {
        $this->_db = $db;
    }
    
    public function linkNN($table, $id1, $id2, $extras = null)
    {
        $field1 = $id1['field'];
        $value1 = $id1['value'];
        $field2 = $id2['field'];
        $value2 = $id2['value'];

        $data = array($field1 => $value1, $field2 => $value2);
        
        if(isset($extras))
        {
            if(is_array($extras))
            {
                $data += $extras;
            }
        }
        
        $this->_db->insert($table, $data);
    }
    
    /**
    * Remove one or more links.
    * 
    * If $id2 is not given this method will remove all the links that refers
    * to $id1. If $id2 is given then will remove just one link (if exists)
    * 
    * @param string $table
    * @param array $id1 array(
    *   'field' => the name of the field of the foreign key
    *   'value' => the value of the foreign key
    * )
    * @param array|null $id2 array(
    *   'field' => the name of the field of the foreign key
    *   'value' => the value of the foreign key
    * ) or null
    */
    public function removeLinkNN($table, $id1, $id2 = null)
    {
        $field1 = $id1['field'];
        $value1 = $this->_db->quote($id1['value']);
        $where = $field1 . ' = ' . $value1;
        if($id2 !== NULL)
        {
            $field2 = $id2['field'];
            $value2 = $this->_db->quote($id2['value']);
            $where .= ' AND ' . $field2 . ' = ' . $value2;
        }
        
        $this->_db->delete($table, $where);
    }
    
    public function linkNN_deleteFirst($table, $id1, $id2, $extras = null)
    {
        $this->removeLinkNN($table, $id1, $id2);
        $this->linkNN($table, $id1, $id2, $extras);
    }
    
    public function saveDependencies($fields, $inputNamePrefix, &$data, &$model, $table_main, $table_dep, $field_dep, $field_parent, $parent_id)
    {
        // todo: ricontrolliamolo questo.
        
        $utils = $this->_getInputUtils();
        // Inserisco i diversi possibili indirizzi
        $partials = $utils->formatDataForMultipleFields($fields, $inputNamePrefix, $data);
        
        $this->_db->beginTransaction();
        try
        {
            $presents = array();
            if(!empty($partials))
            {
                foreach($partials as $k => $p)
                {               
                    $id = $model->save($p);

                    $edit = !empty($p['id']);

                    $this->linkNN_deleteFirst($table_dep, 
                        array('field' => $field_dep, 'value' => $id), 
                        array('field' => $field_parent, 'value' => $parent_id));
                    
                    $presents[] = $id;
                }
            }

            // eliminiamo le non pi� presenti
            $this->_db->query('delete ' . $table_main . ', ' . $table_dep . ' ' .
                'from ' . $table_dep . ', ' . $table_main . ' ' .
                'where ' . $table_main . '.id = ' . $table_dep . '.' . $field_dep . ' ' .
                'and ' . $field_parent . ' = ' . $this->_db->quote($parent_id) .  
                ((empty($presents)) ? '' : ' and ' . $field_dep . ' not in (' . implode(', ', $presents) . ')'));
                
            $this->_db->commit();
            return true;
        }
        catch (Exception $e)
        {
            $this->_db->rollBack();
            return false;
        }
    }
    
    protected function _getInputUtils()
    {
        if($this->_inputUtils === NULL)
        {
            $this->_inputUtils = new Maco_Input_Utils();
        }
        return $this->_inputUtils;
    }
}
