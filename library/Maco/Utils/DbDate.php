<?php

class Maco_Utils_DbDate
{
    const DBDATE_FULL = 1;
    const DBDATE_DATE = 2;
    const DBDATE_TIME = 4;

	/**
    * Modifica una stringa data proveniente dal db in una stringa leggibile
    *
    * @param string $date la data da convertire
    * @return string
    */
    public static function fromDb($date, $type = self::DBDATE_FULL)
    {
        if(strlen($date) == 10)
        {
            if($date == '' || $date == '0000-00-00' || $type == self::DBDATE_TIME)
            {
                return '';
            }

            return implode('/',array_reverse(explode('-', $date)));
        }
        else if(strlen($date) == 19)
        {
            $d = substr($date, 0, 10);
            $t = substr($date, 11);

            if($type == self::DBDATE_TIME)
            {
                return $t;
            }

            if($d == '' || $d == '0000-00-00')
            {
                return '';
            }

            $fdate = implode('/',array_reverse(explode('-', $d)));

            if($type == self::DBDATE_DATE)
            {
                return $fdate;
            }

            return $t . ' del ' . $fdate;
        }
        else
            return '';
    }

    /**
    * Modifica una stringa data proveniente dal input in una stringa Date per il DB
    *
    * @param string $date la data da convertire
    * @return string
    */
    public static function toDb($date = null)
    {
        
		if(!isset($date))
            return '0000-00-00';

        if(strlen($date) == 10)
        {
            if($date == '')
                return  '0000-00-00';
            return implode('-', array_reverse(explode('/', $date)));
        }
        else if(strlen($date) == 16)
        {
            $d = substr($date, 0, 10);
            $t = substr($date, 11);
            
            return implode('-', array_reverse(explode('/', $d))) . ' ' . implode(':', explode('.', $t)) . ':00';
        }
        else
            return '';
    }
    
    /**
    * Diff Db date
    * 
    * @param string $dbDate1
    * @param string $dbDate2 if null then today
    * @return int
    */
    public function dbDiffInDays($dbDate1, $dbDate2 = null, $suffix = '', $zero = true, $zend = false)
    {
        
        if($dbDate1 == '' || substr($dbDate1, 0, 10) == '0000-00-00' ||
            $dbDate2 == '' || substr($dbDate2, 0, 10) == '0000-00-00')
        {
            return '';
        }
        
        $d1 = new DateTime($dbDate1);
        $d2 = ($dbDate2 !== null) ? new DateTime($dbDate2) : new DateTime();

        $di = $d1->diff($d2);
        
        if($di->d != 0 || $zero)
        {
            return $di->format('%R%a' . $suffix);
        }
    }
}