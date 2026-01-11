<?php

class Model_AddressesMapper
{
    public function __construct()
    {

    }

    public function getEmptyDetail()
    {
        $model = new Model_Addresses();
        return $model->getEmptyDetail();
    }
    
    public function fetchByContactId($id)
    {
        $db = $this->_getDbAdapter();
        $select = $db->select();
        
        $select->from('addresses_contacts')
            ->joinLeft('addresses', 'id_address = addresses.id', array('via', 'numero', 'cap', 'localita', 'description', 'id'))
            ->joinLeft('province', 'addresses.provincia = province.id', array('provincia' => 'nomeprovincia', 'idprovincia' => 'id'))
            ->where('id_contact = ?', $id);

        if(!empty($where))
        {
            foreach($search as $k => $s)
            {
                $select->where($k . ' like ' . $db->quote('%' . $s . '%'));
            }
        }
            
        $data = $db->fetchAll($select);
        
        if(empty($data))
        {
            return array($this->getEmptyDetail());
        }
        
        return $data;
    }
    
	public function fetchByCompanyId($id)
    {
        $db = $this->_getDbAdapter();
        $select = $db->select();
        
		$select->from('addresses_companies', array())
            ->joinLeft('addresses', 'id_address = addresses.id', array('via', 'numero', 'cap', 'localita', 'description', 'id'))
            ->joinLeft('province', 'addresses.provincia = province.id', array('provincia' => 'nomeprovincia', 'idprovincia' => 'id'))
            ->where('id_company = ?', $id);
                
        $data = $db->fetchAll($select);
        
        if(empty($data))
        {
            return array($this->getEmptyDetail());
        }
        
        return $data;
    }
    
    public function getDetail($id)
    {
        $model = new Model_Addresses();
        
        $data = $model->getDetail($id);
        
        if(empty($data))
        {
            return $this->getEmptyDetail();
        }
        
        return $data;
    }
    
    /**
     * Delete a contact from the database with all dependent data
     *
     * @param int $id
     * @return bool
     */
    public function delete($ids)
    {
        
        $db = $this->_getDbAdapter();
        $db->beginTransaction();
        
        try
        {
            if(is_array($ids))
            {
                foreach($ids as $id)
                {
                    $this->_delete($id);
                }
            }
            else
            {
                $this->_delete($ids);
            }
            
            return $db->commit();
        }
        catch(Exception $e)
        {
            $db->rollBack();
            return false;
        }
    }
    
    /**
    * Internal delete
    * 
    * @param int $id
    */
    protected function _delete($id)
    {
        $model = new Model_Addresses();
        
        return $model->delete($id);
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
}
