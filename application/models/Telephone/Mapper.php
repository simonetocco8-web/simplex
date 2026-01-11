<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 8-ott-2010
 * Time: 14.48.38
 * To change this template use File | Settings | File Templates.
 */

class Model_Telephone_Mapper extends Maco_Model_Mapper_Abstract
{
    protected $_modelName = 'Model_Telephone';

    protected $_dbTableName = 'Model_DbTables_Telephones';

    public function findByContact($id_contact)
    {
        $db = $this->_getDbAdapter();
        $select = $db->select();
        $select->from('telephones', '*')
        ->where('id_contact = ?', $id_contact);

        $items = $db->fetchAll($select);

        $ret = array();
        foreach ($items as $item)
        {
            $itemObj = new Model_Telephone();
            $itemObj->setData($item);
            $ret[] = $itemObj;
        }

        return $ret;
    }

    public function fetch($where)
    {
        $db = $this->getDbAdapter();
        $select = $db->select();
        $select->from('telephones', '*');

        if(is_array($where))
        {
            foreach($where as $field => $value)
            {
                $select->where($field . ' = ?', $db->quote($value));
            }
        }
        elseif(is_string($where))
        {

        }

        $items = $db->fetchAll($select);

        $ret = array();
        foreach ($items as $item)
        {
            $obj = new Model_Telephone();
            $obj->setData($item);
            $ret[] = $obj;
        }

        return $ret;
    }
}
