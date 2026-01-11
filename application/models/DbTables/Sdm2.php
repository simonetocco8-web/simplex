<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marcello
 * Date: 26/04/13
 * Time: 16.39
 * To change this template use File | Settings | File Templates.
 */

class Model_DbTables_Sdm2 extends Zend_Db_Table_Abstract
{
    /**
     * Table name
     */
    protected $_name = 'sdm2';

    /**
     * Primary key
     */
    protected $_primary = 'sdm_id';

    /**
     * Dependencies
     */


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
