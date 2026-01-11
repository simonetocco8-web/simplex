<?php
/**
 * This is the DbTable class for the Orders table.
 */
class Model_DbTables_Orders extends Zend_Db_Table_Abstract
{
    /**
     * Table name
     */
    protected $_name = 'orders';

    /**
     * Primary key
     */
    protected $_primary = 'order_id';

    /**
     * Insert new row
     *
     * Ensure that a timestamp is set for the created field.
     *
     * @param array $data
     * @return int
     */
    public function insert(array $data)
    {
        unset($data['id']);
        $data['date_created'] = new Zend_Db_Expr('now()');
        $aut = Zend_Auth::getInstance()->getIdentity();
        $data['created_by'] = $aut->user_id;
        
        return parent::insert($data);
    }

    /**
     * Update a row
     *
     * Ensure that a timestamp is set for the created field.
     *
     * @param array $data
     * @return int
     */
    public function update(array $data, $where)
    {
        $data['date_modified'] = new Zend_Db_Expr('now()');
        $aut = Zend_Auth::getInstance()->getIdentity();
        $data['modified_by'] = $aut->user_id;

        return parent::update($data, $where);
    }
}
