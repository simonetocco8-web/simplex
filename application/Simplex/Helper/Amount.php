<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 13-ott-2010
 * Time: 16.08.27
 * To change this template use File | Settings | File Templates.
 */
 
class Simplex_Helper_Amount
{
    public static function withDiscount($sum, $discount)
    {
        if($discount != 0 && $discount != '')
        {
            $sum -= $sum * $discount / 100;
        }
        return $sum;
    }
}