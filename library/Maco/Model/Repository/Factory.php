<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 8-ott-2010
 * Time: 15.22.33
 * To change this template use File | Settings | File Templates.
 */
 
class Maco_Model_Repository_Factory
{
    protected static $_repositories = array();

    static function getRepository($name)
    {
        if(isset(self::$_repositories[$name]))
        {
            return self::$_repositories[$name];
        }
        //$class = 'Model_' . ucfirst(strtolower($name)) . '_Repository';
        $class = 'Model_' . ucfirst(($name)) . '_Repository';
        self::$_repositories[$name] = new $class;
        return self::$_repositories[$name];
    }
}