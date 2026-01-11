<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 11-ott-2010
 * Time: 14.15.17
 * To change this template use File | Settings | File Templates.
 */

class Model_Website_Mapper extends Maco_Model_Mapper_Abstract
{
    protected $_modelName = 'Model_Website';

    protected $_dbTableName = 'Model_DbTables_Websites';

    public function fetch($where)
    {
        $db = $this->getDbAdapter();
        $select = $db->select();
        $select->from('websites', '*');

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
            $obj = new Model_Website();
            $obj->setData($item);
            $ret[] = $obj;
        }

        return $ret;
    }
}
