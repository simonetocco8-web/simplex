<?php

class Simplex_Importer_Users extends Simplex_Importer_Abstract
{
    protected $_def = array(
        'table' => 'users',
        'id' => 'user_id',
        'field' => 'username'
    );
    
    protected function _pushValue($def, $value)
    {
        $table = $def['table'];
        $field = $def['field'];

        // create the dummy contact
        $this->_db->insert('contacts', array(
            'created_by' => 1,
            'date_created' => new Zend_Db_Expr('now()'),
            'deleted' => 0,
            'cognome' => $value,
        ));
        
        $id_contact = $this->_db->lastInsertId();
        
        $this->_db->insert($table, array(
            'created_by' => 1,
            'date_created' => new Zend_Db_Expr('now()'),
            'deleted' => 0,
            $field => $value,
            'password' => md5($value . '1324576809'),
            'password_salt' => '1324576809',
            'id_role' => 1,
            'active' => 1,
            'id_contact' => $id_contact
        ));
        
        $id = $this->_db->lastInsertId();
        
        unset($this->$table);
        return $id;
        
        
        $this->$table[$id] = $value;
        
        return $id;
    }
    
    public function getUserId($username)
    {
        return $this->_getIdByValue($this->_def, $username);
    }
}
