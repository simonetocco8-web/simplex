<?php

class Simplex_Importer_Abstract
{
    protected $_config;
    protected $_db;
    protected $_reader;
    
    public function setConfig(&$config)
    {
        $this->_config = $config;
    }
    
    public function setDb(&$db)
    {
        $this->_db = $db;
    }
    
     public function setReader(&$reader)
    {
        $this->_reader = $reader;
    }
    
    protected function _getValue($sheet, $col, $row)
    {
        $val = trim($sheet->getCell($col . $row)->getValue());
        $val = str_replace('@@@', '\'', $val);
        $val = str_replace('^', '\'', $val);
        return $val;
    }
    
    protected function _getIdByValue($def, $value)
    {
        if($value == '')
        {
            return null;
        }
        
        $cached = $this->_loadCached($def);
        
        $key = array_search($value, $cached);
        
        if(!$key)
        {
            $key = $this->_pushValue($def, $value);
        }
        return $key;
    }
    
    protected function _loadCached($def)
    {
        $table = $def['table'];
        if(!isset($this->$table) || $this->$table === false)
        {
            $id = $def['id'];
            $field = $def['field'];
            $this->$table = $this->_db->fetchPairs('select ' . $id . ', ' . $field . ' from ' . $table);
        }
        return $this->$table;
    }
    
    protected function _pushValue($def, $value)
    {
        $table = $def['table'];
        $field = $def['field'];

        $this->_db->insert($table, array(
            'created_by' => 1,
            'date_created' => new Zend_Db_Expr('now()'),
            $field => $value
        ));
        
        $id = $this->_db->lastInsertId();
        $arr = & $this->$table;
        $arr[$id] = $value;
        //unset($this->$table);
        return $id;
        
        
        $this->$table[$id] = $value;
        
        return $id;
    }
}
