<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 8-ott-2010
 * Time: 15.10.21
 * To change this template use File | Settings | File Templates.
 */

class Model_Address_Mapper extends Maco_Model_Mapper_Abstract
{
    protected $_modelName = 'Model_Address';

    protected $_dbTableName = 'Model_DbTables_Addresses';

    public function fetch($where)
    {
        $db = $this->getDbAdapter();
        $select = $db->select();
        $select->from('addresses', '*');

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
            $obj = new Model_Address();
            $obj->setData($item);
            $ret[] = $obj;
        }

        return $ret;
    }
}
