<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 12-ott-2010
 * Time: 13.19.01
 * To change this template use File | Settings | File Templates.
 */
 
class Model_DbTables_Moments extends Zend_Db_Table_Abstract
{
    /**
     * Table name
     */
    protected $_name = 'moments';

    /**
     * Primary key
     */
    protected $_primary = 'moment_id';

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
        $data['fatturato'] = 0;
        $data['closed'] = 0;

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