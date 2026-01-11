<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 9-ott-2010
 * Time: 17.50.04
 * To change this template use File | Settings | File Templates.
 */

class Maco_Model_TransactionManager
{
    /**
     * Current Transaction Level
     *
     * @var int
     */
    protected static $_transactionLevel = 0;

    protected static function _getDbAdapter()
    {
        return Zend_Registry::get('dbAdapter');
    }

    /**
     * Begin new DB transaction for connection
     *
     * @return void
     */
    public static function beginTransaction()
    {
        if (self::$_transactionLevel === 0)
        {
            self::_getDbAdapter()->beginTransaction();
        }
        self::$_transactionLevel++;
    }

    /**
     * Commit DB transaction
     *
     * @return void
     */
    public static function commit()
    {
        if (self::$_transactionLevel === 1)
        {
            self::_getDbAdapter()->commit();
        }
        self::$_transactionLevel--;
    }

    /**
     * Rollback DB transaction
     *
     * @return void
     */
    public static function rollback()
    {
        if (self::$_transactionLevel === 1)
        {
            self::_getDbAdapter()->rollback();
        }
        self::$_transactionLevel--;
    }

    /**
     * Get adapter transaction level state. Return 0 if all transactions are complete
     *
     * @return int
     */
    public static function getTransactionLevel()
    {
        return self::$_transactionLevel;
    }
}