<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marcello
 * Date: 09/04/13
 * Time: 17.00
 * To change this template use File | Settings | File Templates.
 */

class Maco_Utils_Time
{
    /**
     * Build the hours decimal
     *
     * @param int $hours
     * @param int $minutes
     * @return float
     */
    public static function toValue($hours, $minutes = 0)
    {
        $hours = (float) $hours;
        $minutes = (float) $minutes;

        return $hours + $minutes / 60;
    }

    /**
     * Build hours and minutes integers
     *
     * @param float $time
     * @return array
     */
    public static function fromValue($time, $approx = 10)
    {
        if(!$time)
        {
            return array(
                'hours' => false,
                'minutes' => false,
            );
        }

        $hours = floor($time);

        $minutes = ($time - $hours) * 60;

        if($approx > 0)
        {
            $minutes = round($minutes / $approx) * $approx;
        }

        return array(
            'hours' => $hours,
            'minutes' => $minutes
        );
    }

    public static function getFormattedFromValue($time)
    {
        $out = '';
        $and = '';
        if(empty($time))
        {
            return $out;
        }

        $parsed = self::fromValue($time);

        $h = $parsed['hours'];
        $m = $parsed['minutes'];

        if($h > 0)
        {
            $out = $h . (($h == 1) ? ' ora' : ' ore');
            $and = ' e ';
        }

        if($m > 0)
        {
            $out .= $and . $m . (($m > 1) ? ' minuti' : 'minuto');
        }
        return $out;
    }
}