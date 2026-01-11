<?php
/**
 * This is the DbTable class for the Private Messages table.
 */
class Model_DbTables_Messages extends Zend_Db_Table_Abstract
{
    /**
     * Table name
     */
    protected $_name = 'messages';

    /**
     * Primary key
     */
    protected $_primary = 'message_id';

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
        if(!isset($data['created_by']) || $data['created_by'] == '')
        {
            $aut = Zend_Auth::getInstance()->getIdentity();
            $data['created_by'] = $aut->user_id;
        }
        if(!isset($data['type']) || !$data['type'])
        {
            $data['type'] = 0;
        }
        $data['read'] = 0;
        $data['deleted'] = 0;

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
        return parent::update($data, $where);
    }
}