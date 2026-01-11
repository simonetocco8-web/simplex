<?php

class Model_MessagesMapper
{
	public function getMessagesForUser($user_id)
	{
		$model = new Model_Messages();

		$db = $this->_getDbAdapter();
		
		$where = array('id_receiver = ?' => $user_id,
			 		   'read_ = ?' => 0);
        
		$messages = $model->getMessages($where);
        
        return $messages;
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