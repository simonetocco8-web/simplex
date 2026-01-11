<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 12-ott-2010
 * Time: 13.17.18
 * To change this template use File | Settings | File Templates.
 */
 

class Model_Moment_Mapper extends Maco_Model_Mapper_Abstract
{
    protected $_modelName = 'Model_Moment';

    protected $_dbTableName = 'Model_DbTables_Moments';

    public function fetch($where)
    {
        $db = $this->getDbAdapter();
        $select = $db->select();
        $select->from('moments', '*');

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
            $obj = new Model_Moment();
            $obj->setData($item);
            $ret[] = $obj;
        }

        return $ret;
    }
}
