<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marcello
 * Date: 26/04/13
 * Time: 16.33
 * To change this template use File | Settings | File Templates.
 */

class Model_Sdm2 extends Maco_Model_Abstract
{
    const STATUS_NEW = 2;
    const STATUS_WORKING = 3;
    const STATUS_SENDBACK = 7;
    const STATUS_REJECTED = 8;
    const STATUS_SOLVED = 4;
    const STATUS_REWORKING = 9;
    const STATUS_VERIFIED = 5;
    const STATUS_VERIFIED_AND_PREVENTION = 6;

    const PREVENTION_NEW = 10;
    const PREVENTION_DONE = 11;
    const PREVENTION_VERIFIED = 12;

    protected $sdm_id;

    protected $created_by;

    protected $creator;

    protected $date_created;

    protected $modified_by;

    protected $modifier;

    protected $date_modified;

    protected $internal_code;

    protected $code;

    protected $year;

    protected $id_status;

    protected $with_prevention;

    protected $stories = array();
    protected $newStories = array();

    private static $_status_codes = array(
        //   0 => 'bozza',
        //1 => 'bozza',
        self::STATUS_NEW => 'nuova',
        self::STATUS_WORKING => 'in lavorazione',
        self::STATUS_SOLVED => 'risolta',
        self::STATUS_SENDBACK => 'rimandata',
        self::STATUS_REJECTED => 'rifiutata',
        self::STATUS_VERIFIED => 'verificata',
        // self::STATUS_VERIFIED_AND_PREVENTION => 'verificata e azione preventiva',

        self::PREVENTION_NEW => 'azione preventiva avviata',
        self::PREVENTION_DONE => 'azione preventiva eseguita',
        self::PREVENTION_VERIFIED => 'azione preventiva verificata',
    );

    public static function getStatoDescription($status_code, $default = null)
    {
        if(array_key_exists($status_code, self::$_status_codes))
        {
            return self::$_status_codes[$status_code];
        }
        if($default === null)
        {
            return 'stato sconosciuto!';
        }

        return $default;
    }

    public static function getStati()
    {
        return self::$_status_codes;
    }
}