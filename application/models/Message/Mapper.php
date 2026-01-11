<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 8-ott-2010
 * Time: 15.02.04
 * To change this template use File | Settings | File Templates.
 */

class Model_Message_Mapper extends Maco_Model_Mapper_Abstract
{
    protected $_modelName = 'Model_Message';

    protected $_dbTableName = 'Model_DbTables_Messages';

    public function delete($id)
    {
        $table = $this->getTable();

        $primaryKey = $table->info(Zend_Db_Table_Abstract::PRIMARY);
        $primaryKey = reset($primaryKey);

        return $this->_delete($primaryKey . ' = ' . $table->getAdapter()->quote($id));
    }

    public function deleteByToAndUid($to, $uid)
    {
        $table = $this->getTable();
        $adapter = $table->getAdapter();

        return $this->_delete($adapter->quoteIdentifier('to') . ' = '
        . $adapter->quote($to) . ' and uid = ' . $adapter->quote($uid));
    }

    public function deleteByUid($uid)
    {
        $table = $this->getTable();
        $adapter = $table->getAdapter();

        return $this->_delete('uid = ' . $adapter->quote($uid));
    }

    protected function _delete($where)
    {
        $table = $this->getTable();
        $primaryKey = $table->info(Zend_Db_Table_Abstract::PRIMARY);
        $primaryKey = reset($primaryKey);

        $messages = $table->fetchAll($where);

        foreach($messages as $message)
        {
            $deleteWhere = $primaryKey . ' = ' . $table->getAdapter()->quote($message['message_id']);

            if($message['type'] == Model_Message_Types::TODO)
            {
                $table->update(array(
                    'deleted' => 1,
                    'date_deleted' => date('Y-m-d H:i:s')
                ), $deleteWhere);
            }
            elseif($message['type'] == Model_Message_Types::INFO)
            {
                $table->delete($deleteWhere);
            }
        }

        return count($messages);
    }
}
