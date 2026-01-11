<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 7-ott-2010
 * Time: 10.39.55
 * To change this template use File | Settings | File Templates.
 */

class Maco_Utils_Log_Firebug
{
    /**
     * Metodo statico per la stampa di un log (FIREBUG)
     *
     * @param string il messaggio
     * @param string etichetta
     * @return void
     */
    public static function log($message, $label = null)
    {
        //todo: recuperare le info da config o altro?
        if (APPLICATION_ENV != 'production')
        {
            if ($label != null)
            {
                $message = array($label, $message);
            }
            Zend_Registry::get('logger')->debug($message);
        }
    }
}