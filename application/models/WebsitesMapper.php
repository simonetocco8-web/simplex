<?php

class Model_WebsitesMapper
{
    public function __construct()
    {

    }

    public function getEmptyDetail()
    {
        $model = new Model_Websites();
        return $model->getEmptyDetail();
    }
    
    public function fetchByCompanyId($id)
    {
        $db = $this->_getDbAdapter();
        $select = $db->select();
        
        $select->from('websites_companies')
            ->joinLeft('websites', 'id_website = websites.id', array('url', 'description', 'id'))
            ->where('id_company = ?', $id);
        
        $data = $db->fetchAll($select);
        
        if(empty($data))
        {
            return array($this->getEmptyDetail());
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
        $model = new Model_Websites();
        
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
