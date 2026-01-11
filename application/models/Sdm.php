<?php

class Model_Sdm extends Maco_Model_Abstract
{
    protected $sdm_id;

    protected $created_by;

    protected $creator;

    protected $date_created;

    protected $modified_by;

    protected $modifier;

    protected $date_modified;

    protected $code;

    protected $year;

    protected $id_status;

    protected $problem;

    protected $date_problem;

    protected $cause;

    protected $area;

    protected $id_responsible;

    protected $responsible;

    protected $date_feedback;

    protected $date_set_responsible;

    protected $treatment;

    protected $id_solver;

    protected $solver;

    protected $date_expected_resolution;

    protected $date_set_solver;

    protected $resolution;

    protected $date_resolution;

    protected $verification;

    protected $date_verification;

    protected $responsibles;

    private static $_status_codes = array(
     //   0 => 'bozza',
        //1 => 'bozza',
        2 => 'nuova',
        3 => 'in lavorazione',
        4 => 'risolta',
        5 => 'verificata',
    );
    
    public static function getStatoDescription($status_code)
    {
        if(array_key_exists($status_code, self::$_status_codes))
        {
            return self::$_status_codes[$status_code];
        }
        return 'stato sconosciuto!';
    }
    
    public static function getStati()
    {
        return self::$_status_codes;
    }
}