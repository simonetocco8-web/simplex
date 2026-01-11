<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 6-set-2010
 * Time: 14.38.37
 * To change this template use File | Settings | File Templates.
 */

class Maco_Input_Filter_DateTime implements Zend_Filter_Interface
{
    public function filter($value)
    {
        //$d = new DateTime($value);
        //return $d->format('Y-m-d h:i:s');
        
		if($value == '')
		{
			return null; //date("Y-m-d H:i:s");
		}

        $pos = strpos($value, '-');
        if($pos == 4)
        {
            //db date
            return $value;
        }
        
        $day = substr($value, 0, 2);
        $month = substr($value, 3, 2);
        $year = substr($value, 6, 4);

        $hour = substr($value, 11, 2);
        $minute = substr($value, 14, 2);

        return $year . '-' .$month . '-' . $day . ' ' . $hour . ':' . $minute . ':00';
    }
}